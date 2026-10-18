<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Shipping\CancelShipmentAction;
use App\Actions\Shipping\GenerateShipmentLabelAction;
use App\Actions\Shipping\GetOrderShippingQuoteLinkAction;
use App\Actions\Shipping\PrepareReplacementShipmentAction;
use App\Actions\Shipping\PublishOrderShippingQuoteAction;
use App\Actions\Shipping\RefreshShipmentTrackingAction;
use App\Actions\Shipping\VerifyShipmentAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Models\ShippingAgency;
use App\Domain\Shipping\OrderShipping;
use App\Filament\Forms\Components\BrazilianMoneyInput;
use App\Filament\Pages\StoreSettings;
use App\Filament\Resources\Orders\OrderResource;
use App\Support\BrazilianMoney;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.view-order';

    protected function getHeaderActions(): array
    {
        $order = $this->record;
        $status = $order->status;
        $mode = OrderShipping::mode($order);
        $actions = [];
        $currentShipments = OrderShipping::currentShipments($order);
        $hasPendingShippingVerification = $currentShipments->contains(fn ($shipment) => filled($shipment->operation_phase));
        if ($status === OrderStatus::RECEIVED && $order->shipping_pricing_status !== null && $order->payment_status !== PaymentStatus::APPROVED) {
            $actions[] = Action::make('quoteShipping')->label('Informar cotação de entrega')->modalHeading('Cotação de entrega')
                ->fillForm(fn () => $order->latestShippingQuote?->toArray() ?? ['service_name' => 'Entrega combinada', 'expires_at' => now()->addDays(7)])
                ->schema([Section::make('Frete negociado')->columns(2)->schema([
                    TextInput::make('carrier_name')->label('Transportadora')->required()->maxLength(255),
                    TextInput::make('service_name')->label('Serviço / descrição')->required()->maxLength(255),
                    BrazilianMoneyInput::make('price_cents')->label('Valor do frete')->required(),
                    TextInput::make('estimated_days')->label('Prazo estimado (dias úteis)')->integer()->minValue(0)->maxValue(365),
                    DateTimePicker::make('expires_at')->label('Válida até')->required()->seconds(false),
                    Textarea::make('instructions')->label('Instruções')->maxLength(2000)->columnSpanFull(),
                ])])->action(fn (array $data) => $this->perform(fn () => app(PublishOrderShippingQuoteAction::class)->execute($order, $data, (int) auth()->id()), 'Cotação publicada'));
        }
        if ($order->latestShippingQuote && $order->latestShippingQuote->expires_at->isFuture() && ! $order->latestShippingQuote->superseded_at && $status === OrderStatus::RECEIVED) {
            $actions[] = Action::make('reviewShippingQuote')->label('Abrir link do cliente')->url(app(GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote))->openUrlInNewTab();
        }
        if ($status === OrderStatus::PAID) {
            $actions[] = $this->statusAction('processing', 'Iniciar preparação', OrderStatus::PROCESSING);
        }
        if ($status === OrderStatus::PROCESSING && $mode === 'pickup') {
            $actions[] = $this->statusAction('pickup', 'Pronto para retirada', OrderStatus::READY_FOR_PICKUP);
        }
        if ($status === OrderStatus::PROCESSING && $mode === 'automatic' && ! $hasPendingShippingVerification) {
            $snapshot = OrderShipping::snapshot($order);
            $actions[] = Action::make('generateLabel')->label('Gerar etiqueta')->modalHeading('Preparar envio')->schema([
                Section::make('Entrega contratada')->schema([
                    Text::make(($snapshot['carrier_name'] ?? '').' · '.($snapshot['name'] ?? $order->shipping_name)),
                    Text::make('Volumes: '.count($snapshot['packages'] ?? $snapshot['metadata']['packages'] ?? []).' · Valor pago pelo cliente: '.BrazilianMoney::display($order->shipping_cents)),
                    Text::make('Os dados do remetente são obtidos nas configurações da loja.'),
                ]),
                Section::make('Documento do envio')->description('Escolha o documento fiscal desta postagem. A opção é salva no pedido e não poderá ser trocada depois que o frete for criado no provedor.')->schema([
                    Select::make('shipping_document_type')->label('Tipo de documento')->required()->live()
                        ->options([
                            ShipmentDocumentType::INVOICE->value => ShipmentDocumentType::INVOICE->label(),
                            ShipmentDocumentType::CONTENT_DECLARATION->value => ShipmentDocumentType::CONTENT_DECLARATION->label(),
                        ])
                        ->default($order->shipping_document_type?->value ?? ShipmentDocumentType::INVOICE->value),
                    Text::make('A declaração de conteúdo é destinada a envios que possam legalmente ser realizados sem Nota Fiscal. Os itens, quantidades e valores serão preenchidos automaticamente a partir deste pedido.')
                        ->color('warning')
                        ->visible(fn (Get $get): bool => $get('shipping_document_type') === ShipmentDocumentType::CONTENT_DECLARATION->value),
                    Text::make('Itens que serão declarados: '.$order->items->map(function ($item): string {
                        $variant = filled($item->variant_value) ? ' · '.$item->variant_value : '';

                        return $item->product_name.$variant.' × '.$item->quantity;
                    })->implode(' | '))
                        ->visible(fn (Get $get): bool => $get('shipping_document_type') === ShipmentDocumentType::CONTENT_DECLARATION->value),
                    TextInput::make('invoice_key')->label('Chave da NF-e')->rules(['regex:/^\d{44}$/D'])->length(44)->inputMode('numeric')->default($order->invoice_key)
                        ->required(fn (Get $get): bool => $get('shipping_document_type') === ShipmentDocumentType::INVOICE->value)
                        ->visible(fn (Get $get): bool => $get('shipping_document_type') === ShipmentDocumentType::INVOICE->value)
                        ->helperText(($snapshot['environment'] ?? null) === 'sandbox'
                            ? 'Use uma chave real de NF-e modelo 55. Para testar sem NF-e, selecione Declaração de conteúdo quando o envio puder usar essa modalidade.'
                            : 'Informe a chave de acesso real da NF-e modelo 55 deste pedido.')
                        ->validationMessages(['regex' => 'Informe uma chave de NF-e com 44 dígitos.']),
                    Section::make('Já possui uma DC-e emitida externamente?')->description('Opcional. Deixe fechado e vazio para o fluxo normal de declaração de conteúdo.')
                        ->collapsible()->collapsed()
                        ->visible(fn (Get $get): bool => $get('shipping_document_type') === ShipmentDocumentType::CONTENT_DECLARATION->value)
                        ->schema([
                            TextInput::make('dce_key')->label('Chave da DC-e')->nullable()->rules(['regex:/^\d{44}$/D'])->length(44)->inputMode('numeric')->default($order->dce_key)
                                ->helperText('Preencha somente se a DC-e já tiver sido emitida fora desta integração.'),
                        ]),
                    Select::make('agency_id')->label('Unidade preferida de postagem')->placeholder('Nenhuma')->searchable()
                        ->visible((bool) ($snapshot['metadata']['supports_agency'] ?? $snapshot['metadata']['agency_required'] ?? false))
                        ->required((bool) ($snapshot['metadata']['agency_required'] ?? false))
                        ->helperText(($snapshot['metadata']['agency_required'] ?? false)
                            ? 'Obrigatória para este serviço.'
                            : 'Opcional. A unidade fica registrada para a operação; a API só recebe o ID quando o serviço exigir.')
                        ->default($snapshot['metadata']['agency_id'] ?? null)
                        ->options(fn () => ShippingAgency::query()->where('shipping_integration_id', $snapshot['integration_id'] ?? null)->where('environment', $snapshot['environment'] ?? null)->whereHas('carriers', fn ($query) => $query->where('external_id', $snapshot['carrier_external_id'] ?? null)->where('shipping_integration_id', $snapshot['integration_id'] ?? null)->where('environment', $snapshot['environment'] ?? null))->where('is_active', true)->pluck('name', 'id')),
                ]),
            ])->action(function (array $data) use ($order): void {
                $documentType = ShipmentDocumentType::tryFrom((string) ($data['shipping_document_type'] ?? '')) ?? ShipmentDocumentType::INVOICE;
                $this->perform(
                    fn () => app(GenerateShipmentLabelAction::class)->execute(
                        $order,
                        $documentType->isInvoice() ? ($data['invoice_key'] ?? null) : null,
                        $documentType->isContentDeclaration() ? ($data['dce_key'] ?? null) : null,
                        auth()->id(),
                        isset($data['agency_id']) ? (int) $data['agency_id'] : null,
                        $documentType,
                    ),
                    'Envio preparado',
                );
            });
            $actions[] = Action::make('storeSettings')->label('Configurações da loja')->color('gray')->url(StoreSettings::getUrl());
        }
        if (in_array($status, [OrderStatus::PROCESSING, OrderStatus::READY_TO_SHIP], true) && $mode === 'manual') {
            $actions[] = Action::make('ship')->label('Marcar enviado')->schema([Section::make('Envio manual')->schema([
                TextInput::make('carrier')->label('Transportadora')->required()->maxLength(120)->default($order->carrier),
                TextInput::make('tracking_code')->label('Código de rastreio (opcional)')->maxLength(180),
                TextInput::make('tracking_url')->label('Link de rastreio')->url()->maxLength(2048),
            ])])->action(fn (array $data) => $this->perform(fn () => app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::SHIPPED, auth()->id(), carrier: $data['carrier'], trackingCode: $data['tracking_code'] ?? null, trackingUrl: $data['tracking_url'] ?? null), 'Pedido enviado'));
        }
        foreach ($currentShipments as $index => $shipment) {
            $volumeLabel = $currentShipments->count() > 1 ? ' · Volume '.($index + 1).' de '.$currentShipments->count() : '';

            if ($shipment->generated_at && $shipment->status !== ShipmentStatus::CANCELLED) {
                $actions[] = Action::make('open_label_'.$shipment->id)
                    ->label('Abrir / imprimir etiqueta'.$volumeLabel)
                    ->icon('heroicon-o-printer')
                    ->url(route('admin.shipping.labels.open', ['shipment' => $shipment]))
                    ->openUrlInNewTab();

                $actions[] = ActionGroup::make(array_map(
                    fn ($format) => Action::make('download_'.$shipment->id.'_'.$format)
                        ->label(strtoupper($format))
                        ->url(route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => $format])),
                    ['pdf', 'zpl', 'jpeg'],
                ))
                    ->label('Baixar arquivo'.$volumeLabel)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button();
            }
            if ($shipment->external_id && $shipment->status !== ShipmentStatus::CANCELLED) {
                $actions[] = Action::make('tracking_'.$shipment->id)->label('Atualizar rastreamento'.$volumeLabel)->color('gray')
                    ->action(fn () => $this->perform(fn () => app(RefreshShipmentTrackingAction::class)->execute($shipment), 'Rastreamento atualizado'));
                $actions[] = Action::make('cancel_'.$shipment->id)->label('Cancelar etiqueta'.$volumeLabel)->color('danger')->requiresConfirmation()
                    ->schema([Section::make('Cancelar etiqueta')->description('Isso cancela somente o envio logístico. O pedido de venda continuará existindo.')->schema([Textarea::make('reason')->label('Motivo')->required()->maxLength(500)])])
                    ->action(fn (array $data) => $this->perform(fn () => app(CancelShipmentAction::class)->execute($shipment, $data['reason'], auth()->id()), 'Etiqueta cancelada; o pedido foi preservado'));
            }
            if ($shipment->operation_phase) {
                $actions[] = Action::make('verify_'.$shipment->id)->label('Verificar envio'.$volumeLabel)->color('warning')->requiresConfirmation()
                    ->modalHeading('Verificar o envio')
                    ->modalDescription('O sistema consultará o Melhor Envio para confirmar o que aconteceu na última tentativa. Se localizar a operação com segurança, continuará o preparo sem criar outra etiqueta ou repetir uma compra já confirmada.')
                    ->modalSubmitActionLabel('Verificar agora')
                    ->action(fn () => $this->perform(
                        fn () => app(VerifyShipmentAction::class)->execute($shipment, auth()->id()),
                        'Envio verificado',
                        'Ainda não foi possível confirmar o envio',
                    ));
            }
            if ($shipment->status === ShipmentStatus::CANCELLED && in_array($status, [OrderStatus::PROCESSING, OrderStatus::READY_TO_SHIP], true)) {
                $actions[] = Action::make('replace_'.$shipment->id)->label('Preparar novo envio'.$volumeLabel)->requiresConfirmation()->modalDescription('A substituição poderá gerar uma nova compra de frete ao continuar a preparação.')
                    ->action(fn () => $this->perform(fn () => app(PrepareReplacementShipmentAction::class)->execute($shipment, auth()->id()), 'Nova tentativa preparada'));
            }
        }

        if (in_array($status, [OrderStatus::SHIPPED, OrderStatus::READY_FOR_PICKUP], true) && $mode !== 'automatic') {
            $actions[] = $this->statusAction('delivered', 'Marcar entregue', OrderStatus::DELIVERED);
        }
        if ($mode === 'automatic' && in_array($status, [OrderStatus::READY_TO_SHIP, OrderStatus::SHIPPED], true)) {
            $target = $status === OrderStatus::SHIPPED ? OrderStatus::DELIVERED : OrderStatus::SHIPPED;
            $actions[] = Action::make('shippingContingency')->label('Contingência: '.$target->label())->color('warning')->requiresConfirmation()
                ->schema([Section::make('Intervenção administrativa')->schema([Textarea::make('reason')->label('Justificativa')->required()->minLength(10)->maxLength(1000)])])
                ->action(fn (array $data) => $this->perform(fn () => app(TransitionOrderStatusAction::class)->execute($order, $target, auth()->id(), $data['reason'], carrier: $order->carrier ?: $order->shipping_quote_snapshot['carrier_name'] ?? null, contingency: true), 'Contingência registrada'));
        }
        if (in_array($status, [OrderStatus::RECEIVED, OrderStatus::PAID, OrderStatus::PROCESSING], true)) {
            $actions[] = $this->statusAction('cancelOrder', 'Cancelar pedido', OrderStatus::CANCELLED)->color('danger')->requiresConfirmation();
        }

        return $actions;
    }

    private function statusAction(string $name, string $label, OrderStatus $to): Action
    {
        return Action::make($name)->label($label)->action(fn () => $this->perform(fn () => app(TransitionOrderStatusAction::class)->execute($this->record, $to, auth()->id()), 'Pedido atualizado'));
    }

    private function perform(callable $operation, string $message, string $errorTitle = 'Não foi possível concluir a operação'): void
    {
        try {
            $operation();
            $this->record = $this->record->refresh();
            Notification::make()->success()->title($message)->send();
        } catch (ValidationException $exception) {
            Notification::make()->danger()->title($errorTitle)->body((string) collect($exception->errors())->flatten()->first())->send();
        } catch (\Throwable $exception) {
            Log::warning('Order shipping operation failed', ['order_id' => $this->record->id, 'exception_type' => $exception::class]);
            Notification::make()->danger()->title($errorTitle)->body('Tente novamente em alguns instantes. Se o problema continuar, consulte o suporte técnico.')->send();
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\Order\TransitionOrderStatusAction;
use App\Actions\Shipping\CancelShipmentAction;
use App\Actions\Shipping\GenerateShipmentLabelAction;
use App\Actions\Shipping\GetOrderShippingQuoteLinkAction;
use App\Actions\Shipping\PrepareReplacementShipmentAction;
use App\Actions\Shipping\PublishOrderShippingQuoteAction;
use App\Actions\Shipping\ReconcileShipmentAction;
use App\Actions\Shipping\RefreshShipmentTrackingAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
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
        if ($status === OrderStatus::PROCESSING && $mode === 'automatic') {
            $snapshot = OrderShipping::snapshot($order);
            $actions[] = Action::make('generateLabel')->label('Gerar etiqueta')->modalHeading('Preparar envio')->schema([
                Section::make('Entrega contratada')->schema([
                    Text::make(($snapshot['carrier_name'] ?? '').' · '.($snapshot['name'] ?? $order->shipping_name)),
                    Text::make('Volumes: '.count($snapshot['packages'] ?? $snapshot['metadata']['packages'] ?? []).' · Valor pago pelo cliente: '.BrazilianMoney::display($order->shipping_cents)),
                    Text::make('Os dados do remetente são obtidos nas configurações da loja.'),
                ]),
                Section::make('Dados do envio')->schema([
                    TextInput::make('invoice_key')->label('Chave da NF-e')->required()->rules(['regex:/^\d{44}$/D'])->length(44)->inputMode('numeric')->default($order->invoice_key)->validationMessages(['regex' => 'Informe uma chave de NF-e com 44 dígitos.']),
                    TextInput::make('dce_key')->label('Chave DC-e (se emitida externamente)')->nullable()->rules(['regex:/^\d{44}$/D'])->length(44)->default($order->dce_key),
                    Select::make('agency_id')->label('Unidade de postagem')->placeholder('Nenhuma')->searchable()
                        ->visible((bool) ($snapshot['metadata']['supports_agency'] ?? $snapshot['metadata']['agency_required'] ?? false))
                        ->required((bool) ($snapshot['metadata']['agency_required'] ?? false))
                        ->default($snapshot['metadata']['agency_id'] ?? null)
                        ->options(fn () => ShippingAgency::query()->where('shipping_integration_id', $snapshot['integration_id'] ?? null)->where('environment', $snapshot['environment'] ?? null)->whereHas('carriers', fn ($query) => $query->where('external_id', $snapshot['carrier_external_id'] ?? null)->where('shipping_integration_id', $snapshot['integration_id'] ?? null)->where('environment', $snapshot['environment'] ?? null))->where('is_active', true)->pluck('name', 'id')),
                ]),
            ])->action(fn (array $data) => $this->perform(fn () => app(GenerateShipmentLabelAction::class)->execute($order, $data['invoice_key'], $data['dce_key'] ?? null, auth()->id(), isset($data['agency_id']) ? (int) $data['agency_id'] : null), 'Envio preparado'));
            $actions[] = Action::make('storeSettings')->label('Configurações da loja')->color('gray')->url(StoreSettings::getUrl());
        }
        if (in_array($status, [OrderStatus::PROCESSING, OrderStatus::READY_TO_SHIP], true) && $mode === 'manual') {
            $actions[] = Action::make('ship')->label('Marcar enviado')->schema([Section::make('Envio manual')->schema([
                TextInput::make('carrier')->label('Transportadora')->required()->maxLength(120)->default($order->carrier),
                TextInput::make('tracking_code')->label('Código de rastreio (opcional)')->maxLength(180),
                TextInput::make('tracking_url')->label('Link de rastreio')->url()->maxLength(2048),
            ])])->action(fn (array $data) => $this->perform(fn () => app(TransitionOrderStatusAction::class)->execute($order, OrderStatus::SHIPPED, auth()->id(), carrier: $data['carrier'], trackingCode: $data['tracking_code'] ?? null, trackingUrl: $data['tracking_url'] ?? null), 'Pedido enviado'));
        }
        foreach (OrderShipping::currentShipments($order) as $shipment) {
            if ($shipment->generated_at && $shipment->status !== ShipmentStatus::CANCELLED) {
                $actions[] = ActionGroup::make(array_map(fn ($format) => Action::make('print_'.$shipment->id.'_'.$format)->label(strtoupper($format))->url(route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => $format]))->openUrlInNewTab(), ['pdf', 'zpl', 'jpeg']))->label('Imprimir etiqueta #'.$shipment->id)->icon('heroicon-o-printer')->button();
            }
            if ($shipment->external_id && $shipment->status !== ShipmentStatus::CANCELLED) {
                $actions[] = Action::make('tracking_'.$shipment->id)->label('Sincronizar rastreamento #'.$shipment->id)->color('gray')->action(fn () => $this->perform(fn () => app(RefreshShipmentTrackingAction::class)->execute($shipment), 'Envio sincronizado'));
                $actions[] = Action::make('cancel_'.$shipment->id)->label('Cancelar etiqueta #'.$shipment->id)->color('danger')->requiresConfirmation()
                    ->schema([Section::make('Cancelamento logístico')->schema([Textarea::make('reason')->label('Motivo')->required()->maxLength(500)])])
                    ->action(fn (array $data) => $this->perform(fn () => app(CancelShipmentAction::class)->execute($shipment, $data['reason'], auth()->id()), 'Etiqueta cancelada; pedido comercial preservado'));
            }
            if ($shipment->operation_phase) {
                $actions[] = Action::make('reconcile_'.$shipment->id)->label('Conciliar envio #'.$shipment->id)->color('warning')
                    ->schema([Section::make('Conciliação de operação incerta')->description('Confira o envio no provedor. O identificador será validado contra a operação e o serviço antes de vincular.')->schema([TextInput::make('external_id')->label('ID externo do envio')->required()->maxLength(100)->default($shipment->external_id)])])
                    ->action(fn (array $data) => $this->perform(fn () => app(ReconcileShipmentAction::class)->execute($shipment, $data['external_id']), 'Envio conciliado'));
            }
            if ($shipment->status === ShipmentStatus::CANCELLED && in_array($status, [OrderStatus::PROCESSING, OrderStatus::READY_TO_SHIP], true)) {
                $actions[] = Action::make('replace_'.$shipment->id)->label('Preparar novo envio #'.$shipment->id)->requiresConfirmation()->modalDescription('A substituição poderá gerar uma nova compra de frete ao continuar a preparação.')
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

    private function perform(callable $operation, string $message): void
    {
        try {
            $operation();
            $this->record = $this->record->refresh();
            Notification::make()->success()->title($message)->send();
        } catch (ValidationException $exception) {
            Notification::make()->danger()->title('Não foi possível concluir a operação')->body((string) collect($exception->errors())->flatten()->first())->send();
        } catch (\Throwable $exception) {
            Log::warning('Order shipping operation failed', ['order_id' => $this->record->id, 'exception_type' => $exception::class]);
            Notification::make()->danger()->title('Não foi possível concluir a operação. Verifique o envio e tente novamente.')->send();
        }
    }
}

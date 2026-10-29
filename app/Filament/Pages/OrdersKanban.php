<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Order\TransitionOrderStatusAction;
use App\Domain\Access\AdminPermissions;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\OrderShipping;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final class OrdersKanban extends Page
{
    protected static ?string $title = 'Pedidos Kanban';

    protected static ?string $navigationLabel = 'Pedidos Kanban';

    protected static string|\UnitEnum|null $navigationGroup = 'Vendas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.orders-kanban';

    public string $search = '';

    public string $paymentStatus = '';

    public string $shippingMethod = '';

    public string $fromDate = '';

    public string $toDate = '';

    public bool $showShippingModal = false;

    public ?int $shippingOrderId = null;

    public string $carrier = '';

    public string $trackingCode = '';

    public string $trackingUrl = '';

    public bool $showCancelModal = false;

    public ?int $cancelOrderId = null;

    public string $cancellationReason = '';

    public static function canAccess(): bool
    {
        return AdminPermissions::check(AdminPermissions::SALES_ORDERS);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::query()->whereIn('status', [
            OrderStatus::PENDING->value,
            OrderStatus::PAID->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::READY_FOR_SHIPPING->value,
        ])->count();

        return $count > 0 ? (string) $count : null;
    }

    public function getViewData(): array
    {
        $statuses = [
            OrderStatus::PENDING,
            OrderStatus::PAID,
            OrderStatus::PROCESSING,
            OrderStatus::READY_FOR_SHIPPING,
            OrderStatus::SHIPPED,
            OrderStatus::READY_FOR_PICKUP,
            OrderStatus::DELIVERED,
        ];

        $orders = $this->query()->get()->groupBy(fn (Order $order): string => $order->status->value);
        $columns = [];
        foreach ($statuses as $status) {
            $columns[] = [
                'status' => $status,
                'orders' => $orders->get($status->value, collect()),
            ];
        }

        return [
            'columns' => $columns,
            'cancelledCount' => $this->query()->where('status', OrderStatus::CANCELLED->value)->count(),
            'paymentStatuses' => collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [$status->value => $status->label()])->all(),
        ];
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'paymentStatus', 'shippingMethod', 'fromDate', 'toDate']);
    }

    public function moveOrder(int $orderId, string $target): void
    {
        abort_unless(self::canAccess(), 403);
        $status = OrderStatus::tryFrom($target);
        if (! $status) {
            return;
        }

        if ($status === OrderStatus::SHIPPED) {
            $order = Order::query()->findOrFail($orderId);
            if (OrderShipping::mode($order) === 'automatic') {
                Notification::make()->warning()->title('Postagem automática')->body('A postagem deste pedido é confirmada automaticamente pela transportadora.')->send();

                return;
            }
            $this->openShippingModal($orderId);

            return;
        }

        try {
            $order = Order::query()->findOrFail($orderId);
            app(TransitionOrderStatusAction::class)->execute($order, $status, auth()->id());
            Notification::make()->success()->title('Pedido atualizado')->body($status->label())->send();
        } catch (ValidationException $exception) {
            $this->notifyValidation($exception);
        }
    }

    public function openCancelModal(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);
        if (! $order->status->canBeCancelled()) {
            Notification::make()
                ->warning()
                ->title('Cancelamento indisponível')
                ->body($order->status === OrderStatus::DELIVERED
                    ? 'Este pedido já foi entregue e não pode mais ser cancelado.'
                    : 'Este pedido já foi enviado e não pode mais ser cancelado.')
                ->send();

            return;
        }

        $this->cancelOrderId = $orderId;
        $this->cancellationReason = '';
        $this->resetValidation('cancellationReason');
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->resetValidation('cancellationReason');
        $this->reset(['showCancelModal', 'cancelOrderId', 'cancellationReason']);
    }

    public function confirmCancellation(): void
    {
        abort_unless(self::canAccess(), 403);

        $data = $this->validate([
            'cancelOrderId' => ['required', 'integer', 'exists:orders,id'],
            'cancellationReason' => ['nullable', 'string', 'max:1000'],
        ], [
            'cancellationReason.max' => 'O motivo do cancelamento deve ter no máximo 1.000 caracteres.',
        ]);

        try {
            $order = Order::query()->findOrFail((int) $data['cancelOrderId']);
            if (! $order->status->canBeCancelled()) {
                $this->closeCancelModal();
                Notification::make()
                    ->warning()
                    ->title('Cancelamento indisponível')
                    ->body($order->status === OrderStatus::DELIVERED
                        ? 'Este pedido já foi entregue e não pode mais ser cancelado.'
                        : 'Este pedido já foi enviado e não pode mais ser cancelado.')
                    ->send();

                return;
            }

            app(TransitionOrderStatusAction::class)->execute(
                $order,
                OrderStatus::CANCELLED,
                auth()->id(),
                cancellationReason: filled($data['cancellationReason'] ?? null) ? trim((string) $data['cancellationReason']) : null,
            );
            $this->closeCancelModal();
            Notification::make()->success()->title('Pedido cancelado')->body('O cancelamento foi registrado.')->send();
        } catch (ValidationException $exception) {
            $this->notifyValidation($exception);
        }
    }

    public function openShippingModal(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);
        if (! in_array($order->status, [OrderStatus::PROCESSING, OrderStatus::READY_TO_SHIP], true) || OrderShipping::mode($order) !== 'manual') {
            Notification::make()->danger()->title('Envio indisponível')->body('Este pedido não pode ser marcado como enviado.')->send();

            return;
        }

        $this->shippingOrderId = $orderId;
        $this->carrier = $order->carrier ?? '';
        $this->trackingCode = $order->tracking_code ?? '';
        $this->trackingUrl = $order->tracking_url ?? '';
        $this->showShippingModal = true;
    }

    public function closeShippingModal(): void
    {
        $this->reset(['showShippingModal', 'shippingOrderId', 'carrier', 'trackingCode', 'trackingUrl']);
    }

    public function confirmShipping(): void
    {
        abort_unless(self::canAccess(), 403);
        $data = $this->validate([
            'shippingOrderId' => ['required', 'integer', 'exists:orders,id'],
            'carrier' => ['required', 'string', 'max:120'],
            'trackingCode' => ['nullable', 'string', 'max:180'],
            'trackingUrl' => ['nullable', 'url', 'max:2048'],
        ], [
            'carrier.required' => 'Informe a transportadora.',
            'trackingCode.required' => 'Informe o código de rastreio.',
            'trackingUrl.url' => 'Informe um link de rastreio válido.',
        ]);

        try {
            $order = Order::query()->findOrFail((int) $data['shippingOrderId']);
            app(TransitionOrderStatusAction::class)->execute(
                $order,
                OrderStatus::SHIPPED,
                auth()->id(),
                carrier: $data['carrier'],
                trackingCode: $data['trackingCode'],
                trackingUrl: $data['trackingUrl'] ?: null,
            );
            $this->closeShippingModal();
            Notification::make()->success()->title('Pedido marcado como enviado')->body('O rastreio já está disponível para o cliente.')->send();
        } catch (ValidationException $exception) {
            $this->notifyValidation($exception);
        }
    }

    private function query(): Builder
    {
        return Order::query()
            ->with(['items:id,order_id,quantity', 'shipments'])
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $q) use ($term): void {
                    $q->where('number', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('customer_email', 'like', $term)
                        ->orWhere('customer_document', 'like', $term);
                });
            })
            ->when($this->paymentStatus !== '', fn (Builder $query): Builder => $query->where('payment_status', $this->paymentStatus))
            ->when($this->shippingMethod !== '', fn (Builder $query): Builder => $query->where('shipping_option_id', $this->shippingMethod))
            ->when($this->fromDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->toDate))
            ->latest();
    }

    private function notifyValidation(ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Não foi possível atualizar o pedido')
            ->body((string) (collect($exception->errors())->flatten()->first() ?? 'Transição inválida.'))
            ->send();
    }
}

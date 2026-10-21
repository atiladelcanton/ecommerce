<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Shipping\OrderShipping;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Notification center (Filament database notifications), not transient toasts.
 * Stable IDs make each milestone idempotent per administrator, including when
 * provider webhooks and scheduled reconciliation race each other.
 */
final class NotifyOrderMilestoneAction
{
    public function execute(Order $order, OrderStatus $milestone): void
    {
        if (! in_array($milestone, [OrderStatus::PAID, OrderStatus::READY_TO_SHIP, OrderStatus::SHIPPED, OrderStatus::DELIVERED], true)
            || ! Schema::hasTable('notifications')) {
            return;
        }

        $number = (string) ($order->number ?: '#'.$order->id);
        $mode = OrderShipping::mode($order);
        [$title, $body, $level] = match ($milestone) {
            OrderStatus::PAID => $this->paidMessage($number, $mode, ($order->shipping_document_type instanceof ShipmentDocumentType ? $order->shipping_document_type->value : (string) $order->shipping_document_type)),
            OrderStatus::READY_TO_SHIP => ["Etiqueta pronta — {$number}", 'Todas as etiquetas estão geradas. Imprima-as e prepare a postagem. A postagem ainda não foi confirmada.', 'success'],
            OrderStatus::SHIPPED => ["Pedido postado — {$number}", 'A transportadora confirmou a postagem de todos os volumes.', 'info'],
            OrderStatus::DELIVERED => ["Pedido entregue — {$number}", 'A transportadora confirmou a entrega de todos os volumes.', 'success'],
            default => ['', '', 'info'],
        };

        $notification = Notification::make()
            ->title($title)
            ->body($body)
            ->status($level)
            ->icon(match ($milestone) {
                OrderStatus::PAID => 'heroicon-o-document-text',
                OrderStatus::READY_TO_SHIP => 'heroicon-o-printer',
                OrderStatus::SHIPPED => 'heroicon-o-truck',
                default => 'heroicon-o-check-circle',
            })
            ->actions([
                Action::make('openOrder')
                    ->label('Abrir pedido')
                    ->button()
                    ->url(url('/admin/orders/'.$order->id))
                    ->markAsRead(),
            ]);

        $message = json_encode($notification->getDatabaseMessage(), JSON_THROW_ON_ERROR);
        $now = now();
        // Inserting in the same transaction as the status history ensures
        // rolled-back state transitions cannot leave orphan notifications.
        foreach (User::query()->select('id')->cursor() as $admin) {
            DB::table('notifications')->insertOrIgnore([
                'id' => $this->stableUuid('saba:order:'.$order->id.':'.$milestone->value.':admin:'.$admin->id),
                'type' => 'filament',
                'notifiable_type' => $admin->getMorphClass(),
                'notifiable_id' => $admin->id,
                'data' => $message,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /** @return array{string, string, string} */
    private function paidMessage(string $number, string $mode, string $documentType): array
    {
        if ($mode === 'pickup') {
            return ["Pedido pago — {$number}", 'Pagamento confirmado. Prepare a retirada conforme o fluxo operacional.', 'success'];
        }

        if ($mode !== 'automatic') {
            return ["Pedido pago — {$number}", 'Pagamento confirmado. Separe os produtos e confira a documentação necessária.', 'success'];
        }

        if ($documentType === 'invoice') {
            return ["NF-e a emitir — {$number}", 'Pagamento aprovado. Emita a NF-e, informe a chave no pedido e prepare a etiqueta de transporte.', 'warning'];
        }

        return ["Preparar envio — {$number}", 'Pagamento aprovado. Confira a documentação fiscal aplicável e prepare o envio.', 'success'];
    }

    private function stableUuid(string $seed): string
    {
        $hash = hash('sha256', $seed);

        return substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-5'.substr($hash, 13, 3).'-8'.substr($hash, 17, 3).'-'.substr($hash, 20, 12);
    }
}

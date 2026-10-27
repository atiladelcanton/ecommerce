<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartQuantityAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CartPage extends Component
{
    public function changeQuantity(string $key, int $delta, UpdateCartQuantityAction $action): void
    {
        $this->resetValidation();
        $action->adjust($key, $delta);
        $this->dispatch('cart-updated');
    }

    public function remove(string $key, RemoveCartItemAction $action): void
    {
        $this->resetValidation();
        $action->execute($key);
        $this->dispatch('cart-updated');
    }

    public function clear(ClearCartAction $action): void
    {
        $this->resetValidation();
        $action->execute();
        $this->dispatch('cart-updated');
    }

    public function render(): View
    {
        return view('livewire.store.cart-page', ['cart' => app(GetCartAction::class)->execute()]);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Cart\ClearCartAction;
use App\Actions\Coupon\ApplyCouponAction;
use App\Actions\Coupon\QuoteCouponAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartQuantityAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CartPage extends Component
{
    public string $couponInput = '';

    public function mount(): void
    {
        $this->couponInput = app(ApplyCouponAction::class)->current() ?? '';
    }

    public function applyCoupon(ApplyCouponAction $action): void
    {
        $this->resetValidation();
        $action->apply($this->couponInput);
    }

    public function removeCoupon(ApplyCouponAction $action): void
    {
        $action->remove();
        $this->couponInput = '';
    }

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
        $cart = app(GetCartAction::class)->execute();
        $couponCode = app(ApplyCouponAction::class)->current();
        $discount = 0;
        $couponError = null;
        if ($couponCode && $cart['items'] !== []) {
            try {
                $discount = app(QuoteCouponAction::class)->execute($couponCode, array_values($cart['items']), $cart['subtotal'])['discount'];
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $couponError = $exception->validator->errors()->first();
            }
        }
        return view('livewire.store.cart-page', compact('cart', 'couponCode', 'discount', 'couponError'));
    }
}

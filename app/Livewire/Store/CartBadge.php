<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Cart\GetCartAction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CartBadge extends Component
{
    #[On('cart-updated')]
    public function refreshCount(): void {}

    public function render(): View
    {
        return view('livewire.store.cart-badge', ['count' => app(GetCartAction::class)->execute()['count']]);
    }
}

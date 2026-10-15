<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\GetCartProductAction;
use App\Actions\Catalog\Storefront\GetProductPurchaseDataAction;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ProductPurchase extends Component
{
    #[Locked]
    public int $productId;

    #[Locked]
    public ?string $quoteUrl = null;

    #[Locked]
    public array $variantQuoteUrls = [];

    public ?int $productVariantId = null;

    public array $selectedAttributes = [];

    #[Locked]
    public array $previousAttributes = [];

    public bool $added = false;

    public function add(AddCartItemAction $action): void
    {
        if ($this->selectedAttributes !== []) {
            $data = app(GetProductPurchaseDataAction::class)->execute(app(GetCartProductAction::class)->execute($this->productId), $this->selectedAttributes);
            $this->productVariantId = $data['selectedVariant']?->id;
        }
        $this->added = false;
        $this->resetValidation();
        try {
            $action->execute($this->productId, $this->productVariantId);
        } catch (ValidationException $exception) {
            $variantError = isset($exception->errors()['productVariantId']);
            $this->dispatch('toast',
                type: $variantError ? 'warning' : 'error',
                title: $variantError ? 'Selecione uma opção' : 'Não foi possível adicionar',
                message: $variantError && $this->productVariantId === null
                    ? (str_contains($exception->validator->errors()->first(), 'uma cor') ? 'Escolha a cor antes de adicionar o produto ao carrinho.' : 'Escolha a variação antes de adicionar o produto ao carrinho.')
                    : $exception->validator->errors()->first(),
            );

            throw $exception;
        }
        $this->added = true;
        $this->dispatch('cart-updated');

        $product = app(GetCartProductAction::class)->execute($this->productId);
        $variant = $product->variants->firstWhere('id', $this->productVariantId);
        $description = $product->name.($variant ? ' — '.$variant->name.': '.$variant->value : '');
        $this->dispatch('toast',
            type: 'success',
            title: 'Produto adicionado',
            message: $description.' foi adicionado ao carrinho.',
            actionLabel: 'Ver carrinho',
            actionUrl: route('store.cart', absolute: false),
        );
    }

    public function updatedSelectedAttributes(mixed $value, ?string $key = null): void
    {
        if ($key === null) {
            foreach ($this->selectedAttributes as $index => $selected) {
                if (($this->previousAttributes[$index] ?? null) !== $selected) {
                    $key = (string) $index;
                    break;
                }
            }
        }
        foreach (array_keys($this->selectedAttributes) as $index) {
            if ($key !== null && (int) $index > (int) $key) {
                unset($this->selectedAttributes[$index]);
            }
        }
        $this->previousAttributes = $this->selectedAttributes;
        $data = app(GetProductPurchaseDataAction::class)->execute(app(GetCartProductAction::class)->execute($this->productId), $this->selectedAttributes);
        $this->productVariantId = $data['selectedVariant']?->id;
        $this->resetValidation();
    }

    public function render(): View
    {
        $product = app(GetCartProductAction::class)->execute($this->productId);

        return view('livewire.store.product-purchase', array_merge(['product' => $product], app(GetProductPurchaseDataAction::class)->execute($product, $this->selectedAttributes, $this->productVariantId)));
    }
}

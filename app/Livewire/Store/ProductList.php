<?php

declare(strict_types=1);

namespace App\Livewire\Store;

use App\Actions\Catalog\Storefront\GetStoreContextAction;
use App\Actions\Catalog\Storefront\ListProductsAction;
use App\Domain\Catalog\Enums\ProductAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ProductList extends Component
{
    use WithPagination;

    #[Url(as: 'categoria', history: true)]
    public string $category = '';

    #[Url(as: 'disponibilidade', history: true)]
    public string $availability = '';

    #[Url(as: 'promocao', history: true)]
    public bool $promotion = false;

    #[Url(as: 'busca', history: true)]
    public string $search = '';

    #[Url(as: 'minimo', history: true)]
    public string $minimum = '';

    #[Url(as: 'maximo', history: true)]
    public string $maximum = '';

    #[Url(as: 'ordem', history: true)]
    public string $sort = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['category', 'availability', 'promotion', 'search', 'minimum', 'maximum', 'sort'], true)) {
            $this->resetPage();
            $this->resetValidation();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('category', 'availability', 'promotion', 'search', 'minimum', 'maximum', 'sort');
        $this->resetPage();
        $this->resetValidation();
    }

    public function render(): View
    {
        $filters = $this->only(['category', 'availability', 'promotion', 'search', 'minimum', 'maximum', 'sort']);
        $action = app(ListProductsAction::class);
        try {
            $products = $action->execute($filters);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            $products = $action->execute(array_replace($filters, ['minimum' => '', 'maximum' => '']));
        }

        return view('livewire.store.product-list', [
            'products' => $products,
            'categories' => app(GetStoreContextAction::class)->execute()['navigationCategories'],
            'availabilities' => ProductAvailability::cases(),
        ]);
    }
}

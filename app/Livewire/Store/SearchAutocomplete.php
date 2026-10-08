<?php

declare(strict_types=1);

namespace App\Livewire\Store;
use App\Actions\Catalog\Storefront\SearchProductsAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;
final class SearchAutocomplete extends Component
{
    public string $term = '';
    public function render(): View
    {
        return view('livewire.store.search-autocomplete', ['suggestions' => app(SearchProductsAction::class)->execute($this->term)]);
    }
}

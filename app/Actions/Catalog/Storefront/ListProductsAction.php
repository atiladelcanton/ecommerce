<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use App\Domain\Catalog\Enums\ProductAvailability;
use App\Domain\Catalog\Queries\ProductOfferQuery;
use App\Support\BrazilianMoney;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class ListProductsAction
{
    public function __construct(private readonly PublicProducts $products, private readonly SearchProductsAction $search) {}

    public function execute(array $filters): LengthAwarePaginator
    {
        $query = $this->search->apply($this->products->query(), $filters['search'] ?? '');
        if ($category = ($filters['category'] ?? '')) {
            $query->whereHas('category', fn (Builder $query): Builder => $query->active()->where('slug', $category));
        }
        if ($availability = ProductAvailability::tryFrom($filters['availability'] ?? '')) {
            $query->where('availability', $availability);
        }
        if ($filters['promotion'] ?? false) {
            $query->onPromotion();
        }
        foreach (['minimum' => '>=', 'maximum' => '<='] as $field => $operator) {
            try {
                $price = BrazilianMoney::parse($filters[$field] ?? null);
            } catch (InvalidArgumentException) {
                throw ValidationException::withMessages([$field => 'Informe um valor em reais, como 1.299,90.']);
            }
            if ($price !== null) {
                if ($price < 0) {
                    throw ValidationException::withMessages([$field => 'Informe um valor maior ou igual a zero.']);
                }
                $query->whereRaw(ProductOfferQuery::effectivePrice().' '.$operator.' ?', [$price]);
            }
        }
        match ($filters['sort'] ?? '') {
            'menor-preco' => $query->orderByRaw(ProductOfferQuery::effectivePrice().' ASC'),
            'maior-preco' => $query->orderByRaw(ProductOfferQuery::effectivePrice().' DESC'),
            'nome-az' => $query->orderBy('name'),
            'nome-za' => $query->orderByDesc('name'),
            default => $query->latest(),
        };

        return $query->orderByDesc('id')->paginate(12);
    }
}

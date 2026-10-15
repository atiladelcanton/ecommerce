<?php

declare(strict_types=1);

namespace App\Actions\Catalog\Storefront;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class SearchProductsAction
{
    public function __construct(private readonly PublicProducts $products) {}

    public function apply(Builder $query, string $term): Builder
    {
        $term = mb_substr(trim($term), 0, 100);
        if ($term === '') {
            return $query;
        }
        // Escape user wildcards and use only anchored prefixes on indexed fields.
        $prefix = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
        $slug = Str::slug($term);

        return $query->where(function (Builder $query) use ($prefix, $slug): void {
            $query->whereRaw("products.name LIKE ? ESCAPE '!'", [$prefix])
                ->orWhereRaw("products.sku LIKE ? ESCAPE '!'", [$prefix])
                ->orWhereHas('category', fn (Builder $query): Builder => $query->active()->whereRaw("name LIKE ? ESCAPE '!'", [$prefix]));
            if ($slug !== '') {
                $query->orWhere('products.slug', 'like', $slug.'%');
            }
        });
    }

    public function execute(string $term): Collection
    {
        if (mb_strlen(trim($term)) < 2) {
            return new Collection;
        }

        return $this->apply($this->products->query(), $term)->orderBy('name')->orderBy('id')->limit(5)->get();
    }
}

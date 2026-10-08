<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;
use App\Actions\Catalog\Storefront\GetProductDetailAction;
use Illuminate\Contracts\View\View;
final class ProductController
{
    public function __invoke(string $slug, GetProductDetailAction $action): View { return view('store.product', $action->execute($slug)); }
}

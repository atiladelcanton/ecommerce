<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Catalog\Storefront\GetHomeDataAction;
use Illuminate\Contracts\View\View;

final class HomeController
{
    public function __invoke(GetHomeDataAction $action): View
    {
        return view('store.home', $action->execute());
    }
}

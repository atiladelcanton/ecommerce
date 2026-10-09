<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Settings\Storefront\GetContactPageDataAction;
use Illuminate\Contracts\View\View;

final class InstitutionalController
{
    public function about(): View
    {
        return view('store.about', ['content' => config('storefront.about')]);
    }

    public function contact(GetContactPageDataAction $action): View
    {
        return view('store.contact', $action->execute());
    }
}

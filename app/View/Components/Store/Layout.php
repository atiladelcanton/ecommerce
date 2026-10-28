<?php

declare(strict_types=1);

namespace App\View\Components\Store;

use App\Actions\Catalog\Storefront\GetStoreContextAction;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Layout extends Component
{
    public function __construct(public ?string $title = null, public ?string $description = null, public ?string $canonical = null, public ?string $image = null, public string $ogType = 'website') {}

    public function render(): View
    {
        return view('components.store.layout', app(GetStoreContextAction::class)->execute());
    }
}

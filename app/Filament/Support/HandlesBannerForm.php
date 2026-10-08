<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Actions\Catalog\CreateBannerAction;
use App\Actions\Catalog\UpdateBannerAction;
use App\Domain\Catalog\Models\Banner;
use Livewire\Attributes\Locked;

trait HandlesBannerForm
{
    /** Uploaded paths accepted by Filament, retained for validation retries. */
    #[Locked]
    public array $pendingBannerPaths = [];

    protected function saveBannerForm(array $data, ?Banner $banner = null): Banner
    {
        $this->pendingBannerPaths = array_values(array_filter([$data['desktop_image'] ?? null, $data['mobile_image'] ?? null]));

        return CatalogForm::submit(fn (): Banner => $banner
            ? app(UpdateBannerAction::class)->execute($banner, $data)
            : app(CreateBannerAction::class)->execute($data));
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Banner;

final class DeleteBannerAction
{
    public function execute(Banner $banner): void
    {
        $banner->delete();
    }
}

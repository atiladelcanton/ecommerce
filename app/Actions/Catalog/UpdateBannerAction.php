<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Banner;
use Illuminate\Support\Facades\DB;

final class UpdateBannerAction
{
    public function __construct(private readonly ValidateBannerData $validation) {}

    public function execute(Banner $banner, array $data): Banner
    {
        return DB::transaction(function () use ($banner, $data): Banner {
            $locked = Banner::query()->lockForUpdate()->findOrFail($banner->id);
            $locked->update($this->validation->execute($data, $locked));

            return $locked->refresh();
        });
    }
}

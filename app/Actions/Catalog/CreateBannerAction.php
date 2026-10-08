<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Banner;

final class CreateBannerAction
{
    public function __construct(private readonly ValidateBannerData $validation) {}

    public function execute(array $data): Banner
    {
        return Banner::query()->create($this->validation->execute($data))->refresh();
    }
}

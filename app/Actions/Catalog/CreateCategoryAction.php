<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Category;

final class CreateCategoryAction
{
    public function __construct(private readonly ValidateCatalogData $validation) {}

    public function execute(array $data): Category
    {
        return Category::query()->create($this->validation->category($data))->refresh();
    }
}

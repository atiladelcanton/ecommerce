<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Category;
use Illuminate\Support\Facades\DB;

final class UpdateCategoryAction
{
    public function __construct(private readonly ValidateCatalogData $validation) {}

    public function execute(Category $model, array $data): Category
    {
        return DB::transaction(function () use ($model, $data): Category {
            $locked = Category::query()->lockForUpdate()->findOrFail($model->id);
            // Omitted fields are preserved; an explicit null removes the image association.
            $validated = $this->validation->category($data, $locked);
            $locked->update($validated);

            return $locked->refresh();
        });
    }
}

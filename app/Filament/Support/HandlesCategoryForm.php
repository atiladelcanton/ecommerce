<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Actions\Catalog\CreateCategoryAction;
use App\Actions\Catalog\UpdateCategoryAction;
use App\Domain\Catalog\Models\Category;
use Livewire\Attributes\Locked;

trait HandlesCategoryForm
{
    /** Uploaded paths retained across validation retries, never supplied by the client. */
    #[Locked]
    public array $pendingCategoryPaths = [];

    protected function saveCategoryForm(array $data, ?Category $category = null): Category
    {
        $this->pendingCategoryPaths = array_values(array_filter([$data['image_path'] ?? null]));

        return CatalogForm::submit(fn (): Category => $category
            ? app(UpdateCategoryAction::class)->execute($category, $data)
            : app(CreateCategoryAction::class)->execute($data));
    }
}

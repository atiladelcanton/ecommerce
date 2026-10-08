<?php

declare(strict_types=1);

namespace App\Actions\Catalog;

use App\Domain\Catalog\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteCategoryAction
{
    public function execute(Category $model): void
    {
        DB::transaction(function () use ($model): void {
            $locked = Category::query()->lockForUpdate()->findOrFail($model->id);
            if ($locked->products()->exists()) {
                throw ValidationException::withMessages(['category' => 'Remova ou transfira os produtos antes de excluir a categoria.']);
            }
            $locked->delete();
        });
    }
}

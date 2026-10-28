<?php

declare(strict_types=1);

namespace App\Domain\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Vocabulário reutilizável. BlogPost continua armazenando o nome da categoria
 * e as etiquetas em JSON, preservando o histórico e as views existentes.
 */
final class BlogTerm extends Model
{
    protected $fillable = ['type', 'name', 'slug'];

    public function scopeCategories(Builder $query): Builder
    {
        return $query->where('type', 'category');
    }

    public function scopeTags(Builder $query): Builder
    {
        return $query->where('type', 'tag');
    }
}

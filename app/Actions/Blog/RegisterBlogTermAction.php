<?php

declare(strict_types=1);

namespace App\Actions\Blog;

use App\Domain\Blog\Models\BlogTerm;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class RegisterBlogTermAction
{
    public function execute(string $type, string $name): BlogTerm
    {
        $name = Str::squish($name);

        Validator::make(['type' => $type, 'name' => $name], [
            'type' => ['required', Rule::in(['category', 'tag'])],
            'name' => ['required', 'string', 'max:'.($type === 'category' ? 100 : 50)],
        ])->validate();

        $slug = Str::slug($name);

        if ($slug === '') {
            throw ValidationException::withMessages(['name' => 'Informe um nome com letras ou números.']);
        }

        // A chave por tipo+slug evita duplicatas por caixa, acentos ou espaços.
        // firstOrCreate também trata concorrência quando há índice único.
        return BlogTerm::query()->firstOrCreate(
            ['type' => $type, 'slug' => $slug],
            ['name' => $name],
        );
    }
}

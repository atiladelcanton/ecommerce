<?php

declare(strict_types=1);

namespace App\Actions\Blog;

use App\Domain\Blog\Models\BlogPost;
use App\Support\Catalog\ProductDescription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class SaveBlogPostAction
{
    public function execute(array $data, ?BlogPost $post = null): BlogPost
    {
        $data['title'] = trim((string) ($data['title'] ?? ''));
        $data['slug'] = Str::slug(trim((string) ($data['slug'] ?? '')) ?: ($post?->slug ?: $data['title']));
        foreach (['excerpt', 'cover_image', 'cover_alt', 'category', 'seo_title', 'seo_description'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]) ?: null;
            }
        }
        $data['content'] = ProductDescription::html($data['content'] ?? $post?->content);
        $data['tags'] = array_values(array_unique(array_filter(array_map(
            static fn (mixed $tag): string => is_string($tag) ? trim($tag) : '',
            is_array($data['tags'] ?? null) ? $data['tags'] : [],
        ))));
        $data['status'] ??= 'draft';

        $validated = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_posts', 'slug')->ignore($post?->id)],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255', 'regex:/^blog\/covers\/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$/'],
            'cover_alt' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['array', 'max:15'],
            'tags.*' => ['string', 'max:50'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ], [
            'slug.unique' => 'Este endereço já está sendo usado por outra postagem.',
            'slug.regex' => 'O endereço deve conter apenas letras minúsculas, números e hífens.',
        ])->validate();

        if ($validated['status'] === 'published') {
            if ($validated['content'] === '') {
                throw ValidationException::withMessages(['content' => 'Preencha o conteúdo antes de publicar.']);
            }
            $validated['published_at'] ??= $post?->published_at ?: now();
        } else {
            // A draft is never accessible publicly even if it retains a prior publication date.
            $validated['published_at'] = null;
        }

        return DB::transaction(function () use ($post, $validated): BlogPost {
            // Registra novos termos apenas após a validação, na mesma transação
            // da postagem. Mantém category (string) e tags (JSON) compatíveis.
            $register = app(RegisterBlogTermAction::class);
            if (filled($validated['category'] ?? null)) {
                $validated['category'] = $register->execute('category', $validated['category'])->name;
            }
            $uniqueTags = [];
            foreach ($validated['tags'] ?? [] as $tag) {
                $term = $register->execute('tag', $tag);
                $uniqueTags[$term->id] = $term->name;
            }
            $validated['tags'] = array_values($uniqueTags);

            if ($post) {
                $current = BlogPost::query()->lockForUpdate()->findOrFail($post->getKey());
                $current->update($validated);

                return $current->refresh();
            }

            $validated['author_id'] = auth('web')->id();

            return BlogPost::query()->create($validated)->refresh();
        });
    }
}

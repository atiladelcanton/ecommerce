<?php

declare(strict_types=1);

namespace App\Actions\Blog;

use App\Domain\Blog\Models\BlogPost;
use App\Domain\Settings\Models\StoreSetting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class GetPublicBlogAction
{
    public function enabled(): bool
    {
        return StoreSetting::query()->whereKey(StoreSetting::MAIN_ID)->where('blog_enabled', true)->exists();
    }

    public function ensureEnabled(): void
    {
        abort_unless($this->enabled(), 404);
    }

    public function latest(int $limit = 3): Collection
    {
        return BlogPost::query()->published()->orderByDesc('published_at')->orderByDesc('id')->limit($limit)->get();
    }

    public function index(int $perPage = 9): LengthAwarePaginator
    {
        $this->ensureEnabled();

        return BlogPost::query()->published()->orderByDesc('published_at')->orderByDesc('id')->paginate($perPage);
    }

    public function find(string $slug): BlogPost
    {
        $this->ensureEnabled();

        return BlogPost::query()->published()->where('slug', $slug)->firstOrFail();
    }
}

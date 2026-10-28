<?php

declare(strict_types=1);

namespace App\Actions\Blog;

use App\Domain\Blog\Models\BlogPost;

final class DeleteBlogPostAction
{
    public function execute(BlogPost $post): void
    {
        // Keep the uploaded cover on disk to prevent removing a shared file.
        $post->delete();
    }
}

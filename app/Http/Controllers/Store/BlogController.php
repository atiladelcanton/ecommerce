<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Blog\GetPublicBlogAction;
use Illuminate\Contracts\View\View;

final class BlogController
{
    public function index(GetPublicBlogAction $blog): View
    {
        return view('store.blog.index', ['posts' => $blog->index()]);
    }

    public function show(GetPublicBlogAction $blog, string $slug): View
    {
        return view('store.blog.show', ['post' => $blog->find($slug)]);
    }
}

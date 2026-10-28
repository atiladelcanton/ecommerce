<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_terms', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 16);
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->timestamps();
            $table->unique(['type', 'slug']);
            $table->index(['type', 'name']);
        });

        // Preserva e disponibiliza para seleção todas as categorias e etiquetas
        // previamente utilizadas, inclusive em rascunhos e posts agendados.
        DB::table('blog_posts')->select(['id', 'category', 'tags'])
            ->orderBy('id')->chunkById(200, function ($posts): void {
                $terms = [];
                foreach ($posts as $post) {
                    $this->collectTerm($terms, 'category', $post->category);
                    $tags = json_decode((string) ($post->tags ?? '[]'), true);
                    if (is_array($tags)) {
                        foreach ($tags as $tag) {
                            $this->collectTerm($terms, 'tag', $tag);
                        }
                    }
                }
                if ($terms !== []) {
                    DB::table('blog_terms')->insertOrIgnore(array_values($terms));
                }
            });
    }

    private function collectTerm(array &$terms, string $type, mixed $value): void
    {
        if (! is_string($value)) {
            return;
        }

        $name = Str::squish($value);
        $slug = Str::slug($name);
        if ($name === '' || $slug === '' || mb_strlen($name) > ($type === 'category' ? 100 : 50)) {
            return;
        }

        $terms[$type.'|'.$slug] = [
            'type' => $type,
            'name' => $name,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function down(): void
    {
        // Não altera blog_posts nem o conteúdo existente.
        Schema::dropIfExists('blog_terms');
    }
};

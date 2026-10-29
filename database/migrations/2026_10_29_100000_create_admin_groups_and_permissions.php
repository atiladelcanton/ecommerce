<?php

declare(strict_types=1);

use App\Domain\Access\AdminPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('admin_group_id')->nullable()->constrained('admin_groups')->nullOnDelete();
        });

        $admin = DB::table('admin_groups')->insertGetId([
            'name' => 'Administrador', 'slug' => 'administrador',
            'permissions' => json_encode(AdminPermissions::all(), JSON_THROW_ON_ERROR),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('admin_groups')->insert([
            'name' => 'Operacional', 'slug' => 'operacional',
            'permissions' => json_encode(AdminPermissions::operational(), JSON_THROW_ON_ERROR),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Mantém acesso aos usuários de admin que JÁ existiam antes da instalação.
        // Usuários novos precisam ser atribuídos explicitamente a um grupo.
        DB::table('users')->update(['admin_group_id' => $admin]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('admin_group_id'));
        Schema::dropIfExists('admin_groups');
    }
};

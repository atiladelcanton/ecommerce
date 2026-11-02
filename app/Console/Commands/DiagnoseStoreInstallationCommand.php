<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Appearance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Read-only deployment check for independently deployed stores.
 * Never changes .env, databases, users or filesystem permissions.
 */
final class DiagnoseStoreInstallationCommand extends Command
{
    protected $signature = 'loja:diagnosticar';

    protected $description = 'Diagnostica logo pública, link de storage, banco, sessão e grupos do /admin (somente leitura).';

    public function handle(): int
    {
        $this->components->info('Diagnóstico da instalação (somente leitura)');
        $this->line('Diretório do projeto: '.base_path());
        $this->line('APP_ENV: '.config('app.env'));
        $this->line('APP_URL: '.config('app.url'));
        $this->line('Cookie de sessão: '.config('session.cookie'));
        $this->line('Domínio da sessão: '.(config('session.domain') ?: '[somente o host atual]'));
        $this->line('Driver da sessão: '.config('session.driver'));

        $expected = storage_path('app/public');
        $link = public_path('storage');
        $this->newLine();
        $this->line('Armazenamento de uploads: '.$expected);
        if (is_link($link)) {
            $target = readlink($link);
            $resolved = realpath($link);
            $expectedResolved = realpath($expected);
            $this->line('public/storage é link para: '.($target ?: '[não identificável]'));
            if ($resolved !== false && $expectedResolved !== false && $resolved === $expectedResolved) {
                $this->info('OK: public/storage aponta para esta instalação.');
            } else {
                $this->warn('ATENÇÃO: public/storage está quebrado ou aponta para OUTRA instalação.');
            }
        } elseif (is_dir($link)) {
            $this->warn('ATENÇÃO: public/storage é uma pasta comum, não um link simbólico. Não a apague sem backup.');
        } else {
            $this->warn('ATENÇÃO: o link public/storage não existe.');
        }
        $this->line('Diretório de uploads existe: '.(is_dir($expected) ? 'sim' : 'NÃO'));
        $this->line('Diretório de uploads gravável: '.(is_writable($expected) ? 'sim' : 'NÃO'));

        $this->newLine();
        try {
            $database = DB::connection()->getDatabaseName();
            $this->line('Conexão de banco: '.config('database.default'));
            $this->line('Banco de dados: '.($database ?: '[não identificado]'));

            if (Schema::hasTable('store_settings')) {
                $settings = StoreSetting::query()->find(StoreSetting::MAIN_ID);
                $logo = $settings?->logo_path;
                $this->line('Nome configurado: '.($settings?->store_name ?: '[não configurado]'));
                $this->line('Caminho da logo no banco: '.($logo ?: '[sem logo]'));
                if (is_string($logo) && $logo !== '') {
                    $this->line('Arquivo da logo no disco public: '.(Storage::disk('public')->exists($logo) ? 'ENCONTRADO' : 'NÃO ENCONTRADO'));
                    $this->line('URL pública esperada: '.(Appearance::customLogoUrl($settings) ?: '[caminho inválido]'));
                }
            } else {
                $this->warn('A tabela store_settings não existe neste banco.');
            }

            if (Schema::hasTable('admin_groups') && Schema::hasColumn('users', 'admin_group_id')) {
                $users = DB::table('users');
                $this->line('Usuários sem grupo administrativo: '.(clone $users)->whereNull('admin_group_id')->count());
                $adminGroup = DB::table('admin_groups')->where('slug', 'administrador')->first(['id']);
                $this->line('Grupo Administrador cadastrado: '.($adminGroup ? 'sim' : 'NÃO'));
                if ($adminGroup) {
                    $this->line('Usuários no grupo Administrador: '.(clone $users)->where('admin_group_id', $adminGroup->id)->count());
                }
                $orphans = (clone $users)->whereNotNull('admin_group_id')
                    ->whereNotIn('admin_group_id', DB::table('admin_groups')->select('id'))->count();
                if ($orphans > 0) {
                    $this->warn("ATENÇÃO: há {$orphans} usuários vinculados a grupos inexistentes.");
                }
            } else {
                $this->warn('A migration de grupos administrativos não foi encontrada neste banco.');
            }
        } catch (Throwable $e) {
            $this->error('Falha ao consultar banco/arquivos: '.$e->getMessage());
            $this->warn('O diagnóstico não alterou nada. Verifique .env e conexão do banco.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->comment('Compare APP_URL, banco, cookie e diretórios com a OUTRA instalação.');
        $this->comment('Se o /admin retornar 403, confira o grupo do usuário logado; NÃO desative a autorização.');
        $this->comment('O diagnóstico não altera os arquivos nem o banco.');

        return self::SUCCESS;
    }
}

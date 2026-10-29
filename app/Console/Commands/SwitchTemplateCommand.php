<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Template;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class SwitchTemplateCommand extends Command
{
    protected $signature = 'switch:template
        {--template= : ID numérico do tema}
        {--list : Listar temas disponíveis}
        {--current : Mostrar tema ativo}';

    protected $description = 'Seleciona o visual da loja; preserva catálogo, pedidos e integrações.';

    public function handle(): int
    {
        $available = Template::available();

        if ($this->option('list')) {
            foreach ($available as $id => $label) {
                $this->line(sprintf('%d — %s%s', $id, $label, Template::active() === $id ? ' [ativo]' : ''));
            }
            return self::SUCCESS;
        }

        if ($this->option('current')) {
            $active = Template::active();
            $this->info($active.' — '.$available[$active]);
            return self::SUCCESS;
        }

        $value = $this->option('template');
        if (! is_string($value) || ! ctype_digit($value) || ! array_key_exists((int) $value, $available)) {
            $this->error('Escolha um ID de tema válido. Use --list para consultar.');
            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($value): void {
                $settings = StoreSetting::query()->firstOrCreate(
                    ['id' => StoreSetting::MAIN_ID],
                    ['store_name' => (string) config('app.name', 'Loja')],
                );
                $settings->update(['template_id' => (int) $value]);
            });
        } catch (QueryException $exception) {
            $this->error('Não foi possível gravar o tema. Execute php artisan migrate antes de trocar.');
            return self::FAILURE;
        }

        Template::forgetCurrentRequest();
        $this->info('Tema aplicado: '.$available[(int) $value].'.');
        return self::SUCCESS;
    }
}

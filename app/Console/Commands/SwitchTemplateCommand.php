<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Storefront\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class SwitchTemplateCommand extends Command
{
    protected $signature = 'switch:template
        {--template= : Identificador numérico do template a ativar}
        {--list : Listar os templates disponíveis}
        {--current : Mostrar o template ativo}';

    protected $description = 'Define o visual público desta instalação, sem alterar o banco ou o painel administrativo.';

    public function handle(): int
    {
        $available = config('storefront_templates.available', []);
        if ($this->option('list')) {
            foreach ($available as $id => $label) {
                $this->line(sprintf('%d — %s%s', $id, $label, Template::active() === $id ? ' [ativo]' : ''));
            }

            return self::SUCCESS;
        }

        if ($this->option('current')) {
            $id = Template::active();
            $this->info(sprintf('Template %d — %s', $id, $available[$id] ?? 'Desconhecido'));

            return self::SUCCESS;
        }

        $value = $this->option('template');
        if ($value === null || ! ctype_digit((string) $value) || ! array_key_exists((int) $value, $available)) {
            $this->error('Informe um ID válido: php artisan switch:template --template=1 (ou 2).');

            return self::FAILURE;
        }

        $environment = base_path('.env');
        if (! is_file($environment) || ! is_readable($environment) || ! is_writable($environment)) {
            $this->error('O arquivo .env precisa existir e permitir escrita. Nenhum arquivo foi alterado.');

            return self::FAILURE;
        }

        $contents = file_get_contents($environment);
        if ($contents === false) {
            $this->error('Não foi possível ler o .env.');

            return self::FAILURE;
        }

        $line = 'STOREFRONT_TEMPLATE='.(int) $value;
        if (preg_match('/^\s*STOREFRONT_TEMPLATE\s*=.*$/m', $contents)) {
            $updated = preg_replace('/^\s*STOREFRONT_TEMPLATE\s*=.*$/m', $line, $contents, 1);
            if ($updated === null) {
                $this->error('Falha ao editar o .env.');
                return self::FAILURE;
            }
            $contents = $updated;
        } else {
            $contents = rtrim($contents, "\r\n").PHP_EOL.$line.PHP_EOL;
        }

        if (file_put_contents($environment, $contents, LOCK_EX) === false) {
            $this->error('Não foi possível salvar o .env.');
            return self::FAILURE;
        }

        // Limpa somente o cache de configuração. Não depende de Redis nem do banco.
        Artisan::call('config:clear');
        $this->info('Template '.(int) $value.' — '.$available[(int) $value].' ativado.');
        $this->line('Se houver OPcache ativo, aguarde a atualização ou recarregue o PHP-FPM.');

        return self::SUCCESS;
    }
}

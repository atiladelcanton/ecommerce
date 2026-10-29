<?php

declare(strict_types=1);

namespace App\Domain\Access;

use App\Domain\Access\Models\AdminGroup;

/**
 * Formatação das permissões reais de um grupo para consulta no painel.
 * Não concede nem altera permissões: a autorização continua em AdminPermissions.
 */
final class AdminGroupAccessSummary
{
    /** @return list<string> */
    public static function allowed(AdminGroup $group): array
    {
        $keys = match ($group->slug) {
            'administrador' => AdminPermissions::all(),
            'operacional' => AdminPermissions::operational(),
            default => is_array($group->permissions) ? $group->permissions : [],
        };

        // Não exibir permissões inválidas ou duplicadas, tal como na autorização real.
        return array_values(array_intersect(AdminPermissions::all(), array_unique($keys)));
    }

    public static function count(AdminGroup $group): int
    {
        return count(self::allowed($group));
    }

    public static function type(AdminGroup $group): string
    {
        return in_array($group->slug, ['administrador', 'operacional'], true)
            ? 'Padrão'
            : 'Personalizado';
    }

    public static function description(AdminGroup $group): string
    {
        return match ($group->slug) {
            'administrador' => 'Acesso completo à administração',
            'operacional' => 'Operação da loja sem configurações',
            default => 'Permissões definidas manualmente',
        };
    }

    public static function headline(AdminGroup $group): string
    {
        return match ($group->slug) {
            'administrador' => 'Acesso completo',
            'operacional' => 'Acesso operacional',
            default => self::count($group) === 0 ? 'Nenhum módulo liberado' : 'Acesso personalizado',
        };
    }

    public static function detail(AdminGroup $group): string
    {
        $count = self::count($group);
        $total = count(AdminPermissions::all());

        return $count . ' de ' . $total . ' módulos liberados';
    }

    /**
     * @return array<string, list<array{label: string, granted: bool}>>
     */
    public static function groupedPermissions(AdminGroup $group): array
    {
        $allowed = self::allowed($group);
        $groups = [];

        foreach (AdminPermissions::labels() as $key => $label) {
            [$section, $name] = array_pad(explode(' — ', $label, 2), 2, $label);
            $groups[$section][] = [
                'label' => $name,
                'granted' => in_array($key, $allowed, true),
            ];
        }

        return $groups;
    }
}

<div style="display: grid; gap: 1rem;">
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: .75rem;">
        <x-filament::badge :color="$group->slug === 'administrador' ? 'success' : ($group->slug === 'operacional' ? 'info' : 'gray')">
            {{ $summary }}
        </x-filament::badge>
        @if (in_array($group->slug, ['administrador', 'operacional'], true))
            <span style="font-size: .875rem; opacity: .8;">Grupo padrão protegido contra alterações.</span>
        @endif
    </div>

    @foreach ($sections as $section => $permissions)
        <x-filament::section :heading="$section" compact>
            <div style="display: grid; gap: .25rem;">
                @foreach ($permissions as $permission)
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; padding: .5rem 0;">
                        <span style="font-size: .875rem;">{{ $permission['label'] }}</span>
                        <x-filament::badge :color="$permission['granted'] ? 'success' : 'gray'">
                            {{ $permission['granted'] ? 'Permitido' : 'Bloqueado' }}
                        </x-filament::badge>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endforeach
</div>

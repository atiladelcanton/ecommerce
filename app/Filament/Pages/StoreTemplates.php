<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Access\AdminPermissions;
use App\Domain\Settings\Models\StoreSetting;
use App\Support\Storefront\Template;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StoreTemplates extends Page
{
    protected static ?string $title = 'Temas da loja';
    protected static ?string $slug = 'store-templates';
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.store-templates';

    public int $selectedTemplate = 1;

    public static function canAccess(): bool
    {
        // The visual selector is an owner-only configuration, not an operational tool.
        $user = auth('web')->user();

        return AdminPermissions::check(AdminPermissions::SETTINGS_STORE)
            && $user?->adminGroup?->slug === 'administrador';
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->selectedTemplate = Template::active();
    }

    public function apply(): void
    {
        abort_unless(self::canAccess(), 403);

        if (! array_key_exists($this->selectedTemplate, Template::available())) {
            throw ValidationException::withMessages(['selectedTemplate' => 'Selecione um tema válido.']);
        }

        DB::transaction(function (): void {
            $settings = StoreSetting::query()->firstOrCreate(
                ['id' => StoreSetting::MAIN_ID],
                ['store_name' => (string) config('app.name', 'Loja')],
            );
            $settings->update(['template_id' => $this->selectedTemplate]);
        });

        Template::forgetCurrentRequest();
        Notification::make()->success()->title('Tema aplicado')
            ->body('A aparência pública foi atualizada. Produtos, configurações e pedidos foram preservados.')
            ->send();
    }
}

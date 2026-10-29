<style>.saba-admin-shortcuts{display:inline-flex;align-items:center;gap:.5rem;flex-wrap:wrap}.saba-admin-help-link{white-space:nowrap}@media(max-width:540px){.saba-admin-help-link span{display:none}}</style>
<div class="saba-admin-shortcuts">
    @include('filament.components.view-store-link')
    <a
        href="{{ \App\Filament\Pages\Help::getUrl(['topico' => \App\Filament\Support\AdminHelpContext::topicForRoute(request()->route()?->getName())]) }}"
        class="saba-admin-store-link saba-admin-help-link"
        title="Abrir o tutorial desta tela"
        aria-label="Abrir ajuda desta tela"
    >
        <svg viewBox="0 0 24 24" aria-hidden="true" class="size-5 fill-none stroke-current" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.6 9.3a2.55 2.55 0 0 1 4.8.9c0 1.6-2.4 2-2.4 3.6m0 3.1v.2"/></svg>
        <span>Ajuda desta tela</span>
    </a>
</div>

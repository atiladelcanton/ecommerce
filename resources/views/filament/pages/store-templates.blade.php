<x-filament-panels::page>
    @php
        $activeTheme = \App\Support\Storefront\Template::active();
        $themes = config('storefront_templates.available', []);
        $descriptions = config('storefront_templates.descriptions', []);
    @endphp
    <div class="space-y-5">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">Escolha a aparência da loja pública. A alteração é imediata e não modifica produtos, clientes, pedidos, frete ou pagamentos. Somente administradores podem mudar o tema.</p>
            <p class="mt-3 text-sm font-semibold">Tema em uso: <span class="text-primary-600">{{ $themes[$activeTheme] ?? 'Tema padrão' }}</span></p>
        </div>
        <form wire:submit="apply" class="space-y-5">
            <div class="theme-picker-grid">
                @foreach($themes as $id => $name)
                    <label class="theme-picker-card" @class(['theme-picker-card-selected' => (int) $selectedTemplate === (int) $id])>
                        <input type="radio" class="theme-picker-radio" wire:model.live="selectedTemplate" value="{{ $id }}" name="theme_choice" />
                        <div class="theme-picker-preview preview-{{ $id }}" aria-hidden="true">
                            <div class="preview-topline"></div>
                            <div class="preview-nav"><i></i><span></span><span></span><b></b></div>
                            <div class="preview-hero"><div class="preview-headline"></div><div class="preview-subhead"></div><em></em></div>
                            <div class="preview-products"><i></i><i></i><i></i></div>
                        </div>
                        <div class="theme-picker-info">
                            <div class="flex items-center justify-between gap-3"><strong>{{ $name }}</strong>
                                @if((int) $id === $activeTheme)<span class="theme-pill theme-pill-active">Em uso</span>@endif
                            </div>
                            <p>{{ $descriptions[$id] ?? '' }}</p>
                            <span class="theme-picker-select">{{ (int) $selectedTemplate === (int) $id ? 'Selecionado' : 'Selecionar tema' }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('selectedTemplate')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <div class="flex flex-wrap items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                <x-filament::button type="submit" icon="heroicon-o-check-circle" wire:loading.attr="disabled" wire:target="apply" wire:confirm="Aplicar este tema na loja pública?">
                    Aplicar tema
                </x-filament::button>
                <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-primary-600 hover:underline">Abrir loja ↗</a>
                <span class="text-xs text-gray-500">A escolha fica salva para esta instalação.</span>
            </div>
        </form>
    </div>
    <style>
        .theme-picker-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,250px),1fr));gap:18px}
        .theme-picker-card{display:block;cursor:pointer;min-width:0;overflow:hidden;border:2px solid #e5e7eb;border-radius:15px;background:#fff;transition:border .15s,box-shadow .15s}
        .theme-picker-card:has(input:checked){border-color:#dc2626;box-shadow:0 0 0 3px #dc26261b}
        .theme-picker-radio{position:absolute;width:1px;height:1px;opacity:0}
        .theme-picker-card:has(input:focus-visible){outline:3px solid #2563eb;outline-offset:2px}
        .theme-picker-preview{--p:#dc2626;--bg:#fafafa;--fg:#20242b;height:175px;background:var(--bg);overflow:hidden;padding:9px 12px;color:var(--fg)}
        .preview-topline{height:6px;background:var(--p);opacity:.85;margin:-9px -12px 9px}
        .preview-nav{display:flex;align-items:center;gap:6px;height:24px;background:#fff;padding:4px 7px;margin-bottom:10px}
        .preview-nav i{width:35px;height:11px;background:var(--fg);opacity:.85;display:block;margin-right:auto}
        .preview-nav span{width:22px;height:3px;background:#ccd0d5}
        .preview-nav b{width:13px;height:13px;border:2px solid var(--p);border-radius:50%;margin-left:10px}
        .preview-hero{height:81px;padding:16px;background:linear-gradient(120deg,var(--p),var(--bg) 92%);display:flex;flex-direction:column;align-items:flex-start;gap:7px}
        .preview-headline{height:11px;width:54%;background:var(--fg);opacity:.85}
        .preview-subhead{height:4px;width:40%;background:var(--fg);opacity:.25}
        .preview-hero em{height:10px;width:40px;margin-top:6px;background:var(--fg)}
        .preview-products{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:9px}
        .preview-products i{display:block;height:36px;background:#fff;border:1px solid #e5e7eb}
        .preview-1{--p:#dc2626;--bg:#f3f5f7;--fg:#25252c}
        .preview-2{--p:#156583;--bg:#edf4f7;--fg:#29353e}.preview-2 .preview-hero{margin-left:19%;}.preview-2 .preview-products{grid-template-columns:repeat(4,1fr)}
        .preview-3{--p:#217c9b;--bg:#eaf4f6;--fg:#14384e}.preview-3 .preview-hero{height:91px}
        .preview-4{--p:#8b624b;--bg:#f5f0e9;--fg:#292523}.preview-4 .preview-nav{justify-content:center}.preview-4 .preview-nav i{margin:auto}.preview-4 .preview-hero{background:#e9dfd5}
        .preview-5{--p:#dbe938;--bg:#161616;--fg:#101010}.preview-5 .preview-nav{background:#161616}.preview-5 .preview-nav i{background:#e9e9e9}.preview-5 .preview-hero{background:#dbe938}.preview-5 .preview-products i{background:#252525;border-color:#333}
        .theme-picker-info{padding:16px}.theme-picker-info strong{font-size:16px;color:#222}.theme-picker-info p{min-height:48px;color:#69707b;font-size:12px;line-height:1.6;margin:9px 0 12px}
        .theme-picker-select{font-size:12px;color:#b91c1c;font-weight:700}.theme-pill{font-size:10px;border-radius:99px;padding:4px 8px;font-weight:700}.theme-pill-active{background:#dcfce7;color:#166534}
        @media(prefers-color-scheme:dark){.dark .theme-picker-card{background:#111827;border-color:#374151}.dark .theme-picker-info strong{color:#fafafa}}
    </style>
</x-filament-panels::page>

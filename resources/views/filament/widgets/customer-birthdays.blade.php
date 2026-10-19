<x-filament-widgets::widget>
    <section class="saba-dashboard-card">
        <header class="saba-dashboard-card__header">
            <div>
                <p class="saba-dashboard-card__eyebrow">Relacionamento</p>
                <h2 class="saba-dashboard-card__title">Aniversariantes do mês</h2>
                <p class="saba-dashboard-card__description">{{ $totalBirthdays }} {{ $totalBirthdays === 1 ? 'cliente faz' : 'clientes fazem' }} aniversário em {{ $monthLabel }}.</p>
            </div>

            <a href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl() }}" class="saba-dashboard-link">
                Ver clientes
                <x-filament::icon icon="heroicon-m-arrow-up-right" class="saba-dashboard-link__icon" />
            </a>
        </header>

        <div class="mt-4 divide-y divide-zinc-100">
            @forelse($customers as $customer)
                @php
                    $isToday = $customer->birth_date?->format('m-d') === now()->format('m-d');
                    $digits = preg_replace('/\D+/', '', (string) $customer->phone);
                    $whatsApp = str_starts_with($digits, '55') ? $digits : '55'.$digits;
                @endphp
                <div class="flex flex-wrap items-center gap-3 py-3 first:pt-0 last:pb-0">
                    <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl {{ $isToday ? 'bg-red-50 text-red-700 ring-1 ring-red-100' : 'bg-zinc-100 text-zinc-700' }}">
                        <strong class="text-base leading-none">{{ $customer->birth_date?->format('d') }}</strong>
                        <span class="mt-1 text-[10px] font-semibold uppercase">{{ $customer->birth_date?->locale('pt_BR')->translatedFormat('M') }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <a class="truncate text-sm font-semibold text-zinc-900 hover:text-red-600" href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('view', ['record' => $customer]) }}">{{ $customer->name }}</a>
                            @if($isToday)<span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700">Hoje</span>@endif
                        </div>
                        <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $customer->email }} · {{ \App\Support\BrazilianData::phone($customer->phone) }}</p>
                    </div>

                    <div class="flex items-center gap-1">
                        @if($digits !== '')
                            <a href="https://wa.me/{{ $whatsApp }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900" title="Abrir WhatsApp" aria-label="Abrir WhatsApp de {{ $customer->name }}">
                                <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5" />
                            </a>
                        @endif
                        <a href="mailto:{{ $customer->email }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900" title="Enviar e-mail" aria-label="Enviar e-mail para {{ $customer->name }}">
                            <x-filament::icon icon="heroicon-o-envelope" class="h-5 w-5" />
                        </a>
                    </div>
                </div>
            @empty
                <div class="saba-dashboard-empty">
                    <span class="saba-dashboard-empty__icon"><x-filament::icon icon="heroicon-o-cake" /></span>
                    <div>
                        <strong>Nenhum aniversário informado neste mês</strong>
                        <p>Datas de nascimento opcionais cadastradas pelos clientes aparecerão aqui.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
</x-filament-widgets::widget>

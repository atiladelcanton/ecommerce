<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Saba Equipamentos') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-16">
        <section class="max-w-2xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-zinc-400">{{ config('app.name', 'Saba Equipamentos') }}</p>
            <h1 class="text-4xl font-semibold tracking-tight sm:text-6xl">Base do e-commerce pronta para evoluir por módulos.</h1>
            <p class="mt-6 text-lg leading-8 text-zinc-300">Laravel, Filament, Livewire, Tailwind CSS e Sail, com arquitetura e regras documentadas para desenvolvimento assistido por Codex.</p>
            <div class="mt-8 flex gap-3 text-sm">
                <span class="rounded-full border border-zinc-700 px-3 py-1">Laravel 13</span>
                <span class="rounded-full border border-zinc-700 px-3 py-1">Filament 5</span>
                <span class="rounded-full border border-zinc-700 px-3 py-1">Livewire 4</span>
            </div>
        </section>
    </main>
</body>
</html>

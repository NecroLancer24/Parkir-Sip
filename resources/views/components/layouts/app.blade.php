@props(['title' => 'Parking overview', 'breadcrumb' => 'Overview'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }} · {{ config('app.name', 'Parkir Sip') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-canvas font-sans text-ink antialiased">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-[216px] flex-col bg-white px-[18px] pb-6 lg:flex">
        <a href="{{ route('dashboard') }}" class="mt-7 flex items-center gap-2.5">
            <span class="grid size-9 shrink-0 place-items-center rounded-[10px] bg-brand text-white">
                <x-icon name="parking" :stroke="2" class="size-[19px]" />
            </span>
            <span class="text-[19px] font-semibold tracking-[-0.01em] text-ink">Parkir Sip</span>
        </a>

        <button
            type="button"
            class="mt-7 flex w-full items-center gap-2.5 rounded-xl border border-line-soft bg-subtle p-2 text-left transition-colors hover:border-line"
        >
            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand">
                <x-icon name="building" class="size-4" />
            </span>

            <span class="min-w-0 flex-1">
                <span class="block truncate text-[12.5px] font-semibold leading-4 text-ink">Central Park</span>
                <span class="block truncate text-[11px] leading-4 text-ink-muted">Demo workspace</span>
            </span>

            <x-icon name="chevron-down" class="size-4 shrink-0 text-ink-muted" />
        </button>

        <p class="mt-7 px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-muted">Workspace</p>

        <nav class="mt-2 space-y-2" aria-label="Main">
            <x-nav-item :href="route('dashboard')" icon="dashboard" :active="request()->routeIs('dashboard')">Overview</x-nav-item>
            <x-nav-item :href="route('parking-log')" icon="activity" :active="request()->routeIs('parking-log*')">Parking log</x-nav-item>
        </nav>
    </aside>

    <div class="flex min-h-screen flex-col lg:pl-[216px]">
        <header class="flex h-[76px] shrink-0 items-center gap-4 bg-white px-5 sm:px-7">
            <div class="flex min-w-0 items-center gap-2.5 text-[12.5px]">
                <span class="grid size-6 shrink-0 place-items-center rounded-md border border-line-soft text-ink-muted">
                    <x-icon name="panel" class="size-3.5" />
                </span>
                <span class="text-ink-muted">/</span>
                <span class="truncate font-medium text-ink-soft">{{ $breadcrumb }}</span>
            </div>

            <div class="ml-auto flex items-center gap-5">
                <label class="hidden h-9 w-[232px] items-center gap-2 rounded-lg bg-canvas px-3 sm:flex">
                    <x-icon name="search" class="size-4 shrink-0 text-ink-muted" />
                    <input
                        type="search"
                        placeholder="Search parking log..."
                        class="w-full min-w-0 bg-transparent text-[12px] text-ink placeholder:text-ink-muted focus:outline-none"
                    >
                    <kbd class="shrink-0 rounded border border-line bg-white px-1 py-0.5 text-[10px] leading-none text-ink-muted">⌘K</kbd>
                </label>

                <div class="flex shrink-0 items-center gap-2 text-[12px] text-ink-soft">
                    <x-icon name="clock" class="size-4 text-ink-muted" />
                    <span>10:42 AM <span class="text-ink-muted">-</span> UTC+7</span>
                </div>
            </div>
        </header>

        <main class="flex-1 px-5 pb-10 pt-7 sm:px-7">
            {{ $slot }}
        </main>
    </div>
</body>
</html>

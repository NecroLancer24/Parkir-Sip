@props(['title' => 'Parking overview', 'breadcrumb' => 'Overview'])

@php
    $headerTime = now();
    $offsetMinutes = $headerTime->utcOffset();
    $offsetLabel = $offsetMinutes === 0
        ? 'UTC'
        : 'UTC'.($offsetMinutes < 0 ? '-' : '+').intdiv(abs($offsetMinutes), 60);

    if (abs($offsetMinutes) % 60 !== 0) {
        $offsetLabel .= ':'.str_pad((string) (abs($offsetMinutes) % 60), 2, '0', STR_PAD_LEFT);
    }
@endphp

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
        </a> </span>

        <p class="mt-7 px-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-muted">TAB MENU</p>

        <nav class="mt-2 space-y-2" aria-label="Main">
            <x-nav-item :href="route('dashboard')" icon="dashboard" :active="request()->routeIs('dashboard')">Overview</x-nav-item>
            <x-nav-item :href="route('parking-log')" icon="activity" :active="request()->routeIs('parking-log*')">Parking log</x-nav-item>
        </nav>

        <div class="mt-auto border-t border-line-soft pt-4">
            <div class="flex items-center gap-2.5 px-1">
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-soft text-[11px] font-semibold uppercase text-brand">
                    {{ collect(explode(' ', auth()->user()->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-[11.5px] font-medium text-ink">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10px] text-ink-muted">{{ auth()->user()->email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="grid size-7 place-items-center rounded-md text-ink-muted transition-colors hover:bg-subtle hover:text-ink"
                        title="Log out"
                        aria-label="Log out"
                    >
                        <x-icon name="log-out" class="size-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col lg:pl-[216px]">
        <header class="flex h-[76px] shrink-0 items-center gap-4 bg-white px-5 sm:px-7">
           <div class="flex items-center">
    <img
        src="{{ asset('images/logo-jti-new.svg') }}"
        alt="Logo JTI Polije"
        class="h-11 w-auto object-contain"
    >
    <img
        src="{{ asset('images/trk.svg') }}"
        alt="Logo Teknologi Rekayasa Komputer"
        class="h-16 w-auto object-contain"
    >
</div>
            <div class="ml-auto flex items-center gap-5">
                <label class="hidden h-9 w-[232px] items-center gap-2 rounded-lg bg-canvas px-3 sm:flex">
                    <x-icon name="search" class="size-4 shrink-0 text-ink-muted" />
                    <input
                        type="search"
                        placeholder="Cari..."
                        class="w-full min-w-0 bg-transparent text-[12px] text-ink placeholder:text-ink-muted focus:outline-none"
                    >
                </label>

                <div class="flex shrink-0 items-center gap-2 text-[12px] text-ink-soft">
                    <x-icon name="clock" class="size-4 text-ink-muted" />
                    <time data-live-clock datetime="{{ $headerTime->toIso8601String() }}">
                        <span data-clock-time>{{ $headerTime->format('g:i A') }}</span>
                        <span class="text-ink-muted">-</span>
                        <span data-clock-offset>{{ $offsetLabel }}</span>
                    </time>
                </div>
            </div>
        </header>

        <main class="flex-1 px-5 pb-10 pt-7 sm:px-7">
            {{ $slot }}
        </main>
    </div>
</body>
</html>

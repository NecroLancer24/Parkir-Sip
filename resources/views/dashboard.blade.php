<x-layouts.app title="Parking overview">
    {{-- Page header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink">Parking overview</h1>
            <p class="mt-1.5 text-[12.5px] text-ink-soft">
                Your operations at a glance. All times shown in local time.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                class="inline-flex h-8 items-center gap-2 rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-ink-soft transition-colors hover:bg-subtle"
            >
                <x-icon name="calendar" class="size-3.5 text-ink-muted" />
                Today, {{ now()->format('M j, Y') }}
            </button>

            <button
                type="button"
                class="inline-flex h-8 items-center gap-2 rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-ink-soft transition-colors hover:bg-subtle"
            >
                <x-icon name="download" class="size-3.5 text-ink-muted" />
                Export
            </button>
        </div>
    </div>

    {{-- Key metrics --}}
    <div class="mt-5 grid gap-[18px] sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Available slots" icon="grid">
            <x-slot:value>
                <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">1</span>
                <span class="text-[12.5px] text-ink-muted">/ 3 total</span>
            </x-slot:value>

            <x-slot:hint>
                <span class="text-ink-soft">2 occupied</span>
                <span class="px-1">·</span>
                <span>0 reserved</span>
            </x-slot:hint>
        </x-stat-card>

        <x-stat-card label="Current occupancy" icon="gauge">
            <x-slot:value>
                <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">67%</span>

                <svg class="ml-auto h-6 w-16 self-center" viewBox="0 0 64 24" fill="none" aria-hidden="true">
                    <path
                        d="M1 18 9 12 17 15 25 7 33 12 41 5 49 10 57 3 63 8"
                        stroke="#087f75"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M1 22 9 19 17 21 25 14 33 18 41 12 49 16 57 10 63 14"
                        stroke="#b1d8ce"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </x-slot:value>

            <x-slot:hint>2 vehicles currently parked</x-slot:hint>
        </x-stat-card>

        <x-stat-card label="Entries today" icon="entry">
            <x-slot:value>
                <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">342</span>
            </x-slot:value>

            <x-slot:hint>
                <span class="inline-flex items-center gap-1 font-medium text-brand">
                    <x-icon name="trend-up" class="size-3" />
                    12.4%
                </span>
                compared with yesterday
            </x-slot:hint>
        </x-stat-card>

        <x-stat-card label="Connected devices" icon="router">
            <x-slot:value>
                <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">7</span>
                <span class="text-[12.5px] text-ink-muted">/ 8 online</span>
            </x-slot:value>

            <x-slot:hint>
                <span class="font-medium text-ink-soft">1 device offline</span>
                - needs attention
            </x-slot:hint>
        </x-stat-card>
    </div>

    {{-- Activity chart + slot status --}}
    <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_384px]">
        <section class="rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Vehicle activity</h2>
                    <p class="mt-1 text-[11.5px] text-ink-muted">Entries and exits by hour</p>
                </div>

                <div class="flex items-center gap-4 text-[11px] text-ink-soft">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-brand"></span>
                        Entries
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-ink-muted"></span>
                        Exits
                    </span>
                </div>
            </div>

            <div class="mt-3 flex items-baseline gap-5">
                <p class="text-[11.5px] text-ink-muted">
                    <span class="mr-1.5 text-[20px] font-semibold tracking-[-0.02em] text-ink">342</span>
                    entries
                </p>
                <p class="text-[11.5px] text-ink-muted">
                    <span class="mr-1.5 text-[20px] font-semibold tracking-[-0.02em] text-ink">340</span>
                    exits
                </p>
            </div>

            <div class="mt-3 flex gap-3">
                <div class="flex h-[102px] w-4 flex-col justify-between text-right text-[10px] leading-none text-ink-muted">
                    <span>60</span>
                    <span>40</span>
                    <span>20</span>
                    <span>0</span>
                </div>

                <div class="min-w-0 flex-1">
                    <svg
                        viewBox="0 0 640 130"
                        preserveAspectRatio="none"
                        class="h-[102px] w-full"
                        aria-hidden="true"
                    >
                        <defs>
                            <linearGradient id="entries-fill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#087f75" stop-opacity="0.16" />
                                <stop offset="100%" stop-color="#087f75" stop-opacity="0" />
                            </linearGradient>
                        </defs>

                        @foreach ([0, 43.3, 86.7, 130] as $y)
                            <line
                                x1="0"
                                y1="{{ $y }}"
                                x2="640"
                                y2="{{ $y }}"
                                stroke="#edf1f3"
                                stroke-width="1"
                                vector-effect="non-scaling-stroke"
                            />
                        @endforeach

                        <path
                            d="M 0.0,108.3 C 10.7,107.6 42.7,106.2 64.0,104.0 C 85.3,101.8 106.7,99.7 128.0,95.3 C 149.3,91.0 170.7,83.1 192.0,78.0 C 213.3,72.9 234.7,69.0 256.0,65.0 C 277.3,61.0 298.7,59.6 320.0,54.2 C 341.3,48.7 362.7,35.0 384.0,32.5 C 405.3,30.0 426.7,41.2 448.0,39.0 C 469.3,36.8 490.7,19.5 512.0,19.5 C 533.3,19.5 554.7,35.0 576.0,39.0 C 597.3,43.0 629.3,42.6 640.0,43.3 L 640,130 L 0,130 Z"
                            fill="url(#entries-fill)"
                        />

                        <path
                            d="M 0.0,114.8 C 10.7,114.1 42.7,111.6 64.0,110.5 C 85.3,109.4 106.7,110.9 128.0,108.3 C 149.3,105.8 170.7,99.7 192.0,95.3 C 213.3,91.0 234.7,85.2 256.0,82.3 C 277.3,79.4 298.7,82.3 320.0,78.0 C 341.3,73.7 362.7,58.9 384.0,56.3 C 405.3,53.8 426.7,63.6 448.0,62.8 C 469.3,62.1 490.7,51.3 512.0,52.0 C 533.3,52.7 554.7,65.4 576.0,67.2 C 597.3,69.0 629.3,63.6 640.0,62.8"
                            fill="none"
                            stroke="#91a5ae"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            vector-effect="non-scaling-stroke"
                        />

                        <path
                            d="M 0.0,108.3 C 10.7,107.6 42.7,106.2 64.0,104.0 C 85.3,101.8 106.7,99.7 128.0,95.3 C 149.3,91.0 170.7,83.1 192.0,78.0 C 213.3,72.9 234.7,69.0 256.0,65.0 C 277.3,61.0 298.7,59.6 320.0,54.2 C 341.3,48.7 362.7,35.0 384.0,32.5 C 405.3,30.0 426.7,41.2 448.0,39.0 C 469.3,36.8 490.7,19.5 512.0,19.5 C 533.3,19.5 554.7,35.0 576.0,39.0 C 597.3,43.0 629.3,42.6 640.0,43.3"
                            fill="none"
                            stroke="#087f75"
                            stroke-width="2"
                            stroke-linecap="round"
                            vector-effect="non-scaling-stroke"
                        />
                    </svg>

                    <div class="mt-2 flex justify-between text-[10px] leading-none text-ink-muted">
                        @foreach (['00:00', '02:00', '04:00', '06:00', '08:00', '10:00'] as $hour)
                            <span>{{ $hour }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <p class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-medium text-brand">
                <x-icon name="trend-up" class="size-3.5" />
                12.4% more entries than the same time yesterday
            </p>
        </section>

        <section class="flex flex-col rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
            <h2 class="text-[15px] font-semibold text-ink">Parking slot status</h2>
            <p class="mt-1 text-[11.5px] text-ink-muted">2 of 3 slots occupied</p>

            <div class="mt-4 space-y-3.5">
                @foreach ([['A01', 'Occupied', true], ['A02', 'Occupied', true], ['A03', 'Available', false]] as [$code, $state, $isOccupied])
                    <div>
                        <div class="flex items-center justify-between gap-3 text-[12px]">
                            <p class="text-ink">
                                {{ $code }}
                                <span class="px-0.5 text-ink-muted">-</span>
                                <span class="{{ $isOccupied ? 'text-ink-soft' : 'text-brand' }}">{{ $state }}</span>
                            </p>

                            <span @class([
                                'size-1.5 rounded-full',
                                'bg-ink-muted' => $isOccupied,
                                'bg-brand' => ! $isOccupied,
                            ])></span>
                        </div>

                        <div @class([
                            'mt-2 h-1.5 rounded-full',
                            'bg-line-soft' => $isOccupied,
                            'bg-brand' => ! $isOccupied,
                        ])></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-line-soft pt-3 text-[10.5px] text-ink-soft">
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-ink-muted"></span>
                    Occupied
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-brand"></span>
                    Available
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-warn"></span>
                    Reserved
                </span>
            </div>
        </section>
    </div>

    {{-- Parking slots --}}
    <section class="mt-6 flex min-h-[330px] flex-col rounded-xl border border-line-soft bg-white shadow-card">
        <div class="flex flex-wrap items-start justify-between gap-3 px-[18px] pt-4">
            <div>
                <h2 class="text-[15px] font-semibold text-ink">Parking slots</h2>
                <p class="mt-1 text-[11.5px] text-ink-muted">
                    Level 01 <span class="px-0.5">·</span> 1 of 3 slots available
                </p>
            </div>

            <a href="#" class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-brand hover:text-brand-strong">
                View all slots
                <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>

        <div class="px-[18px] pt-4">
            <div class="flex h-[30px] items-center justify-center gap-1.5 rounded-md border-y border-brand-line bg-brand-soft text-[11px]">
                <span class="font-semibold text-brand">Zone A</span>
                <span class="text-brand/50">·</span>
                <span class="text-brand/80">2/3 occupied</span>
            </div>

            <div class="mt-3.5 rounded-lg bg-subtle p-2">
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                        ['A01', true],
                        ['A02', true],
                        ['A03', false],
                    ] as [$code, $isOccupied])
                        <div @class([
                            'flex h-[50px] flex-col items-center justify-center gap-0.5 rounded-md',
                            'bg-line-soft' => $isOccupied,
                            'bg-brand-soft' => ! $isOccupied,
                        ])>
                            @if ($isOccupied)
                                <x-icon name="car" class="size-[18px] text-ink-muted" />
                            @else
                                <x-icon name="parking" class="size-[18px] text-brand" />
                            @endif

                            <span @class([
                                'text-[9px] font-medium leading-none tracking-wide',
                                'text-ink-muted' => $isOccupied,
                                'text-brand' => ! $isOccupied,
                            ])>{{ $code }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-2 flex h-[30px] items-center gap-3 px-2 text-[9px] font-medium uppercase tracking-[0.22em] text-ink-muted">
                    <span class="h-px flex-1 bg-line"></span>
                    <x-icon name="arrow-right" class="size-3" />
                    <span>One way</span>
                    <span class="h-px flex-1 bg-line"></span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 px-[18px] py-3.5">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[10.5px] text-ink-soft">
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-ink-muted"></span>
                    Occupied
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-brand"></span>
                    Available
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-1.5 rounded-full bg-warn"></span>
                    Reserved
                </span>
            </div>

            <p class="text-[10.5px] text-ink-muted">
                1 available in <span class="font-medium text-ink-soft">Zone A</span>
            </p>
        </div>

        <div class="flex items-center gap-1.5 border-t border-line-soft px-[18px] py-2.5 text-[10.5px] text-ink-muted">
            <x-icon name="refresh" class="size-3" />
            Last simulated update at 10:42:08
        </div>
    </section>

    {{-- Recent activity --}}
    <section class="mt-6 rounded-xl border border-line-soft bg-white shadow-card">
        <div class="flex flex-wrap items-start justify-between gap-3 px-[18px] pt-[18px]">
            <div>
                <h2 class="text-[15px] font-semibold text-ink">Recent activity</h2>
                <p class="mt-1 text-[11.5px] text-ink-muted">Live feed from gates and slot sensors</p>
            </div>

            <a href="#" class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-brand hover:text-brand-strong">
                View all
                <x-icon name="arrow-right" class="size-3.5" />
            </a>
        </div>

        <ul class="mt-3 divide-y divide-line-soft px-[18px] pb-2">
            @foreach ([
                ['icon' => 'entry', 'title' => 'Vehicle entered', 'meta' => 'Gate A · Slot A02', 'time' => '10:42', 'tone' => 'brand'],
                ['icon' => 'parking', 'title' => 'Slot A02 marked occupied', 'meta' => 'ESP32 sensor · Zone A', 'time' => '10:41', 'tone' => 'brand'],
                ['icon' => 'gate', 'title' => 'Vehicle exited', 'meta' => 'Gate B · Slot A01', 'time' => '10:38', 'tone' => 'muted'],
                ['icon' => 'router', 'title' => 'Device offline', 'meta' => 'ESP32 · Gate B reader', 'time' => '10:31', 'tone' => 'warn'],
                ['icon' => 'parking', 'title' => 'Slot A01 released', 'meta' => 'ESP32 sensor · Zone A', 'time' => '10:30', 'tone' => 'muted'],
            ] as $event)
                <li class="flex items-center gap-3 py-3">
                    <span @class([
                        'grid size-7 shrink-0 place-items-center rounded-lg',
                        'bg-brand-soft text-brand' => $event['tone'] === 'brand',
                        'bg-subtle text-ink-muted' => $event['tone'] === 'muted',
                        'bg-warn/15 text-warn' => $event['tone'] === 'warn',
                    ])>
                        <x-icon :name="$event['icon']" class="size-3.5" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[12px] font-medium text-ink">{{ $event['title'] }}</p>
                        <p class="truncate text-[10.5px] text-ink-muted">{{ $event['meta'] }}</p>
                    </div>

                    <span class="shrink-0 text-[10.5px] text-ink-muted">{{ $event['time'] }}</span>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.app>

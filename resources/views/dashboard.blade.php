<x-layouts.app title="Parking overview">
    @php
        $activeSlots = $activeFloor ? $activeFloor['slots'] : collect();
        $activeOccupied = $activeSlots->where('status_slot', true)->count();
        $activeAvailable = max($activeSlots->count() - $activeOccupied, 0);
    @endphp

    {{-- Page header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink">Parking overview</h1>
            <p class="mt-1.5 text-[12.5px] text-ink-soft">
                Your operations at a glance. All times shown in local time.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('slots.randomize') }}"
                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-brand transition-colors hover:bg-brand-soft"
                title="Acak ulang posisi slot terisi"
            >
                <x-icon name="refresh" class="size-3.5" />
                Randomize slots
            </a>

            <button
                type="button"
                class="inline-flex h-8 items-center gap-2 rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-ink-soft transition-colors hover:bg-subtle"
            >
                <x-icon name="calendar" class="size-3.5 text-ink-muted" />
                Today, {{ now()->format('M j, Y') }}
            </button>
        </div>
    </div>

    <div id="overview-live-wrapper">
        {{-- Key metrics --}}
        <div class="mt-5 grid gap-[18px] sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Available slots" icon="grid">
                <x-slot:value>
                    <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">{{ $statistik['slot_tersedia'] }}</span>
                    <span class="text-[12.5px] text-ink-muted">/ {{ $statistik['total_slot'] }} total</span>
                </x-slot:value>

                <x-slot:hint>
                    <span class="text-ink-soft">{{ $statistik['slot_terisi'] }} occupied</span>
                    <span class="px-1">·</span>
                    <span>0 reserved</span>
                </x-slot:hint>
            </x-stat-card>

            <x-stat-card label="Current occupancy" icon="gauge">
                <x-slot:value>
                    <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">{{ $statistik['persen_terisi'] }}%</span>

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

                <x-slot:hint>{{ $statistik['slot_terisi'] }} {{ $statistik['slot_terisi'] === 1 ? 'vehicle' : 'vehicles' }} currently parked</x-slot:hint>
            </x-stat-card>

            <x-stat-card label="Entries today" icon="entry">
                <x-slot:value>
                    <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">{{ $statistik['entries_hari_ini'] }}</span>
                </x-slot:value>

                <x-slot:hint>
                    <span class="inline-flex items-center gap-1 font-medium text-brand">
                        <x-icon name="trend-up" class="size-3" />
                        {{ $statistik['perbandingan_kemarin'] >= 0 ? '+' : '' }}{{ $statistik['perbandingan_kemarin'] }}%
                    </span>
                    compared with yesterday
                </x-slot:hint>
            </x-stat-card>

            <x-stat-card label="Connected devices" icon="router">
                <x-slot:value>
                    <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">{{ $statistik['device_online'] }}</span>
                    <span class="text-[12.5px] text-ink-muted">/ {{ $statistik['device_total'] }} online</span>
                </x-slot:value>

                <x-slot:hint>
                    @if ($statistik['device_offline'] > 0)
                        <span class="font-medium text-ink-soft">{{ $statistik['device_offline'] }} {{ $statistik['device_offline'] === 1 ? 'device' : 'devices' }} offline</span>
                        - standby / disconnected
                    @else
                        <span class="font-medium text-brand">All ESP32 & sensors online</span>
                    @endif
                </x-slot:hint>
            </x-stat-card>
        </div>

        {{-- Activity chart + floor status --}}
        <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_384px]">
            <section class="rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-[15px] font-semibold text-ink">Vehicle activity</h2>
                        <p class="mt-1 text-[11.5px] text-ink-muted">Entries and exits by hour (Today)</p>
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
                        <span class="mr-1.5 text-[20px] font-semibold tracking-[-0.02em] text-ink">{{ $statistik['entries_hari_ini'] }}</span>
                        entries
                    </p>
                    <p class="text-[11.5px] text-ink-muted">
                        <span class="mr-1.5 text-[20px] font-semibold tracking-[-0.02em] text-ink">{{ $statistik['exits_hari_ini'] }}</span>
                        exits
                    </p>
                </div>

                <div class="mt-3 flex gap-3">
                    <div class="flex h-[102px] w-6 flex-col justify-between text-right text-[10px] leading-none text-ink-muted">
                        <span>{{ $chart['max_y'] }}</span>
                        <span>{{ (int) round($chart['max_y'] * 0.66) }}</span>
                        <span>{{ (int) round($chart['max_y'] * 0.33) }}</span>
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
                                d="{{ $chart['entries_path'] }} L 640,130 L 0,130 Z"
                                fill="url(#entries-fill)"
                            />

                            <path
                                d="{{ $chart['exits_path'] }}"
                                fill="none"
                                stroke="#91a5ae"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                vector-effect="non-scaling-stroke"
                            />

                            <path
                                d="{{ $chart['entries_path'] }}"
                                fill="none"
                                stroke="#087f75"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                vector-effect="non-scaling-stroke"
                            />
                        </svg>

                        <div class="mt-2 flex justify-between text-[10px] leading-none text-ink-muted">
                            @foreach ($chart['labels'] as $hour)
                                <span>{{ $hour }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <p class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-medium text-brand">
                    <x-icon name="trend-up" class="size-3.5" />
                    {{ $statistik['perbandingan_kemarin'] >= 0 ? '+' : '' }}{{ $statistik['perbandingan_kemarin'] }}% entries compared with yesterday
                </p>
            </section>

            <section class="flex flex-col rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
                <h2 class="text-[15px] font-semibold text-ink">Parking floor status</h2>
                <p class="mt-1 text-[11.5px] text-ink-muted">{{ $statistik['slot_terisi'] }} of {{ $statistik['total_slot'] }} total slots occupied</p>

                <div class="mt-4 space-y-3.5">
                    @foreach ($floors as $idx => $floorItem)
                        @php
                            $fTotal = $floorItem['slots']->count();
                            $fOccupied = $floorItem['slots']->where('status_slot', true)->count();
                            $fAvailable = max($fTotal - $fOccupied, 0);
                            $fAvailablePct = $fTotal > 0 ? round(($fAvailable / $fTotal) * 100) : 100;
                            $sensorNum = $idx + 1;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-3 text-[12px]">
                                <p class="text-ink">
                                    {{ $floorItem['label'] }}
                                    <span class="px-0.5 text-ink-muted">·</span>
                                    <span class="{{ $fOccupied > 0 ? 'text-ink-soft' : 'text-brand' }}">
                                        {{ $fAvailable }}/{{ $fTotal }} Available (Ultrasonik {{ $sensorNum }})
                                    </span>
                                </p>

                                <span @class([
                                    'size-1.5 rounded-full',
                                    'bg-warn' => $fOccupied > 0,
                                    'bg-brand' => $fOccupied === 0,
                                ])></span>
                            </div>

                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-line-soft">
                                <div
                                    class="h-full rounded-full bg-brand transition-all duration-300"
                                    style="width: {{ $fAvailablePct }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-line-soft pt-3 text-[10.5px] text-ink-soft">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-warn"></span>
                        Active sensor
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-brand"></span>
                        Available capacity
                    </span>
                </div>
            </section>
        </div>

        {{-- Parking slots availability preview --}}
        @php
            $activeSlots = $activeFloor ? $activeFloor['slots'] : collect();
            $activeAvailable = max($activeSlots->count() - $activeSlots->where('status_slot', true)->count(), 0);
        @endphp

        <section id="floor-plan" class="mt-6 flex flex-col rounded-xl border border-line-soft bg-white shadow-card">
            <div class="flex flex-wrap items-start justify-between gap-3 px-[18px] pt-[18px]">
                <div>
                    <h2 class="text-[14px] font-medium text-ink">Parking slots</h2>
                    @if ($activeFloor)
                        <p class="mt-1 text-[10px] text-ink-muted">
                            {{ $activeFloor['label'] }} · {{ $activeAvailable }} of {{ $activeSlots->count() }} slots available
                        </p>
                    @endif
                </div>

                <a href="{{ route('parking-log') }}" class="inline-flex items-center gap-[5px] text-[11px] font-semibold text-brand hover:text-brand-strong">
                    View all slots
                    <x-icon name="trend-up" class="size-3.5" />
                </a>
            </div>

            <div class="px-[18px] pb-[18px] pt-[14px]">
                <x-floor-plan :floors="$floors" :active-floor-index="$activeFloorIndex" :selected-id="$slotTerpilih?->id_slot" />
            </div>
        </section>

        {{-- Selected slot detail, shown below the floor plan preview --}}
        @if ($slotTerpilih)
            <x-slot-detail :slot-model="$slotTerpilih" :logs="$riwayatSlot" :active-log="$logAktifSlot" :floor-number="($activeFloor['number'] ?? 1)" />
        @endif

        {{-- Recent activity (Real-time from parking_log) --}}
        <section class="mt-6 rounded-xl border border-line-soft bg-white shadow-card">
            <div class="flex flex-wrap items-start justify-between gap-3 px-[18px] pt-[18px]">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Recent activity</h2>
                    <p class="mt-1 text-[11.5px] text-ink-muted">Live feed from gates and ultrasonic sensors</p>
                </div>

                <a href="{{ route('parking-log') }}" class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-brand hover:text-brand-strong">
                    View all
                    <x-icon name="arrow-right" class="size-3.5" />
                </a>
            </div>

            <ul class="mt-3 divide-y divide-line-soft px-[18px] pb-2">
                @forelse ($recentActivities as $event)
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
                @empty
                    <li class="py-6 text-center text-[12px] text-ink-muted">
                        Belum ada aktivitas kendaraan hari ini.
                    </li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Auto-refresh real-time tiap 3 detik tanpa reload halaman --}}
    <script>
        setInterval(async () => {
            if (document.hidden) return;
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const updated = doc.getElementById('overview-live-wrapper');
                const current = document.getElementById('overview-live-wrapper');
                if (updated && current) {
                    current.innerHTML = updated.innerHTML;
                }
            } catch (e) {
                // Abaikan jika koneksi sesaat terputus
            }
        }, 3000);
    </script>
</x-layouts.app>
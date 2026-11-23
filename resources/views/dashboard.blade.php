<x-layouts.app title="Ringkasan Parkir">
    {{-- Header Halaman --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink">Ringkasan Parkir</h1>

        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('slots.randomize') }}"
                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-brand transition-colors hover:bg-brand-soft"
                title="Acak ulang posisi slot yang terisi"
            >
                <x-icon name="refresh" class="size-3.5" />
                Acak Posisi Slot
            </a>

            <div class="inline-flex h-8 items-center gap-2 rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-ink-soft">
                <x-icon name="calendar" class="size-3.5 text-ink-muted" />
                Hari ini, {{ now()->format('d M Y') }}
            </div>
        </div>
    </div>

    <div id="overview-live-wrapper">
        {{-- 3 Kartu Statistik Utama (Connected devices & persentase perbandingan sudah dihapus) --}}
        <div class="mt-5 grid gap-[18px] grid-cols-3">
            <x-stat-card label="Slot tersedia" icon="grid">
                <x-slot:value>
                    <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">{{ $statistik['slot_tersedia'] }}</span>
                    <span class="text-[12.5px] text-ink-muted">/ {{ $statistik['total_slot'] }} total</span>
                </x-slot:value>

                <x-slot:hint>
                    <span class="text-ink-soft">{{ $statistik['slot_terisi'] }} slot terisi</span>
                </x-slot:hint>
            </x-stat-card>

            <x-stat-card label="Akumulasi Parkir" icon="gauge">
                <x-slot:value>
                    <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">{{ $statistik['persen_terisi'] }}%</span>
                </x-slot:value>

                <x-slot:hint>{{ $statistik['slot_terisi'] }} kendaraan sedang terparkir</x-slot:hint>
            </x-stat-card>

            <x-stat-card label="Kendaraan masuk hari ini" icon="entry">
                <x-slot:value>
                    <span class="text-[28px] font-semibold leading-none tracking-[-0.02em] text-ink">{{ $statistik['entries_hari_ini'] }}</span>
                </x-slot:value>

                <x-slot:hint>
                    <span class="text-ink-soft">{{ $statistik['exits_hari_ini'] }} kendaraan sudah keluar</span>
                </x-slot:hint>
            </x-stat-card>
        </div>

        {{-- Grafik Aktivitas Kendaraan (Tampil Lebar Penuh karena Parking floor status dihapus) --}}
        <section class="mt-6 rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Aktivitas kendaraan</h2>
                    <p class="mt-1 text-[11.5px] text-ink-muted">Jumlah kendaraan masuk dan keluar per jam (Hari ini)</p>
                </div>

                <div class="flex items-center gap-4 text-[11px] text-ink-soft">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-brand"></span>
                        Masuk
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-ink-muted"></span>
                        Keluar
                    </span>
                </div>
            </div>

            <div class="mt-3 flex items-baseline gap-5">
                <p class="text-[11.5px] text-ink-muted">
                    <span class="mr-1.5 text-[20px] font-semibold tracking-[-0.02em] text-ink">{{ $statistik['entries_hari_ini'] }}</span>
                    masuk
                </p>
                <p class="text-[11.5px] text-ink-muted">
                    <span class="mr-1.5 text-[20px] font-semibold tracking-[-0.02em] text-ink">{{ $statistik['exits_hari_ini'] }}</span>
                    keluar
                </p>
            </div>

            <div class="mt-3 flex gap-3">
                <div class="flex h-[110px] w-6 flex-col justify-between text-right text-[10px] leading-none text-ink-muted">
                    <span>{{ $chart['max_y'] }}</span>
                    <span>{{ (int) round($chart['max_y'] * 0.66) }}</span>
                    <span>{{ (int) round($chart['max_y'] * 0.33) }}</span>
                    <span>0</span>
                </div>

                <div class="min-w-0 flex-1">
                    <svg
                        viewBox="0 0 640 130"
                        preserveAspectRatio="none"
                        class="h-[110px] w-full"
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
        </section>

        {{-- Denah Slot Parkir (1 Area - 24 Slot) --}}
        <section id="floor-plan" class="mt-6 flex flex-col rounded-xl border border-line-soft bg-white shadow-card">
            <div class="flex flex-wrap items-start justify-between gap-3 px-[18px] pt-[18px]">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Denah slot parkir</h2>
                    <p class="mt-1 text-[11px] text-ink-muted">
                        {{ $statistik['slot_tersedia'] }} dari {{ $statistik['total_slot'] }} slot tersedia
                    </p>
                </div>

                <a href="{{ route('parking-log') }}" class="inline-flex items-center gap-[5px] text-[11.5px] font-semibold text-brand hover:text-brand-strong">
                    Lihat semua riwayat
                    <x-icon name="trend-up" class="size-3.5" />
                </a>
            </div>

            <div class="px-[18px] pb-[18px] pt-[14px]">
                <x-floor-plan :floors="$floors" :active-floor-index="0" :selected-id="$slotTerpilih?->id_slot" />
            </div>
        </section>

        {{-- Detail Slot Jika Diklik --}}
        @if ($slotTerpilih)
            <x-slot-detail :slot-model="$slotTerpilih" :logs="$riwayatSlot" :active-log="$logAktifSlot" :floor-number="1" />
        @endif

        {{-- Aktivitas Terbaru --}}
        <section class="mt-6 rounded-xl border border-line-soft bg-white shadow-card">
            <div class="flex flex-wrap items-start justify-between gap-3 px-[18px] pt-[18px]">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink">Aktivitas terbaru</h2>
                    <p class="mt-1 text-[11.5px] text-ink-muted">Catatan langsung dari gerbang dan sensor ultrasonik</p>
                </div>

                <a href="{{ route('parking-log') }}" class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-brand hover:text-brand-strong">
                    Lihat semua
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
                        ])>
                            <x-icon :name="$event['icon']" class="size-3.5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[12px] font-medium text-ink">{{ $event['title'] }}</p>
                            <p class="truncate text-[10.5px] text-ink-muted">{{ $event['meta'] }}</p>
                        </div>

                        <span class="shrink-0 text-[10.5px] text-ink-muted">{{ $event['time'] }} WIB</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-[12px] text-ink-muted">
                        Belum ada aktivitas kendaraan hari ini.
                    </li>
                @endforelse
            </ul>
        </section>
    </div>

    {{-- Auto-refresh ringan tiap 5 detik --}}
    <script>
        setInterval(async () => {
            if (document.hidden) return;
            try {
                const url = new URL("{{ route('dashboard.live-data') }}", window.location.origin);
                url.search = window.location.search;

                const response = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) return;
                const data = await response.json();

                const floorContainer = document.querySelector('#floor-plan > div:last-child');
                if (floorContainer && data.floor_html) {
                    floorContainer.innerHTML = data.floor_html;
                }
            } catch (e) {}
        }, 5000);
    </script>
</x-layouts.app>
@props(['slotModel', 'logs', 'activeLog' => null, 'floorNumber' => 1])

@php
    $plate = $slotModel->sensor?->plateText();
    $plateImage = $slotModel->sensor?->plateImageUrl();
    $isOccupied = $slotModel->status_slot;
@endphp

<section id="slot-detail" class="mt-6 overflow-hidden rounded-xl border border-line-soft bg-white shadow-card">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft bg-subtle px-[18px] py-4">
        <div class="flex items-center gap-2.5">
            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand">
                <x-icon name="parking" class="size-4" />
            </span>

            <div>
                <h2 class="text-[15px] font-semibold text-ink">Slot {{ $slotModel->nomer_slot }}</h2>
                <p class="mt-0.5 text-[11.5px] text-ink-muted">{{ $slotModel->lokasi ?? 'Lokasi belum diketahui' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if ($isOccupied)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-line-soft px-2.5 py-1 text-[11.5px] font-medium text-ink-soft">
                    <span class="size-1.5 rounded-full bg-ink-muted"></span>
                    Terisi
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-1 text-[11.5px] font-medium text-brand">
                    <span class="size-1.5 rounded-full bg-brand"></span>
                    Tersedia
                </span>
            @endif

            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-ink-muted transition-colors hover:text-ink"
            >
                Tutup
                <x-icon name="arrow-right" class="size-3.5 rotate-45" />
            </a>

            <a
                href="{{ route('slots.show', $slotModel) }}"
                class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-brand hover:text-brand-strong"
            >
                Buka halaman lengkap
                <x-icon name="external-link" class="size-3.5" />
            </a>
        </div>
    </div>

    <div class="grid gap-5 p-[18px] xl:grid-cols-[minmax(0,1fr)_320px]">
        {{-- Parking history --}}
        <div class="overflow-hidden rounded-lg border border-line-soft">
            <div class="flex items-center justify-between border-b border-line-soft bg-white px-3.5 py-3">
                <h3 class="text-[13px] font-semibold text-ink">Riwayat parkir</h3>
                <span class="text-[11px] text-ink-muted">{{ $logs->count() }} catatan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="border-b border-line-soft bg-subtle text-[10.5px] font-semibold uppercase tracking-[0.12em] text-ink-muted">
                            <th class="px-3.5 py-2.5 font-semibold">Plat</th>
                            <th class="px-3.5 py-2.5 font-semibold">Pengguna</th>
                            <th class="px-3.5 py-2.5 font-semibold">Kendaraan</th>
                            <th class="px-3.5 py-2.5 font-semibold">Masuk</th>
                            <th class="px-3.5 py-2.5 font-semibold">Keluar</th>
                            <th class="px-3.5 py-2.5 font-semibold">Tarif</th>
                            <th class="px-3.5 py-2.5 font-semibold">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-soft">
                        @forelse ($logs as $log)
                        <tr class="group relative cursor-pointer transition-colors hover:bg-subtle [transform:translateZ(0)]">
                            <td class="whitespace-nowrap px-3.5 py-3">
                                <a href="{{ route('parking-log.show', $log) }}" class="absolute inset-0 z-10"
                                    aria-label="Lihat detail untuk log #{{ $log->id_log }}"></a>

                                <span class="font-mono text-[12px] font-medium text-ink-soft">
                                    {{ $log->plateText() ?? ($log->plateImageUrl() ? 'Tangkapan gambar' : '-') }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3.5 py-3 text-[12.5px] text-ink">
                                {{ $log->pengguna?->nama ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-3.5 py-3">
                                <span class="inline-flex items-center gap-1 text-[11.5px] font-medium text-ink-soft">
                                    <x-icon :name="$log->jenis_kendaraan === 'mobil' ? 'car' : 'motor'" class="size-3.5" />
                                    {{ $log->jenisKendaraanLabel() }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3.5 py-3 text-[12px] text-ink-muted">
                                {{ optional($log->waktu_masuk)->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-3.5 py-3 text-[12px] text-ink-muted">
                                {{ optional($log->waktu_keluar)->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-3.5 py-3">
                                <span class="inline-flex rounded-md bg-canvas px-2 py-1 text-[11.5px] font-medium text-ink">
                                    {{ $log->tarifFormatted() }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3.5 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($log->waktu_keluar === null)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2 py-0.5 text-[11px] font-medium text-brand">
                                        <span class="size-1.5 rounded-full bg-brand"></span>
                                        Sedang parkir
                                    </span>
                                    @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-line-soft px-2 py-0.5 text-[11px] font-medium text-ink-soft">
                                        <span class="size-1.5 rounded-full bg-ink-muted"></span>
                                        Selesai
                                    </span>
                                    @endif

                                    <x-icon name="arrow-right"
                                        class="size-3.5 text-ink-muted opacity-0 transition-opacity group-hover:opacity-100" />
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-3.5 py-10 text-center">
                                <p class="text-[12px] font-medium text-ink">Belum ada catatan parkir</p>
                                <p class="mt-1 text-[11px] text-ink-muted">
                                    Catatan akan muncul setelah kendaraan terparkir di slot ini.
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Slot details and current session --}}
        <div class="space-y-4">
            <div class="rounded-lg border border-line-soft p-3.5">
                <h3 class="text-[13px] font-semibold text-ink">Detail slot</h3>

                <dl class="mt-3 space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">Lokasi</dt>
                        <dd class="text-[12px] font-medium text-ink">{{ $slotModel->lokasi ?? '-' }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">ID Sensor</dt>
                        <dd class="font-mono text-[12px] text-ink">#{{ $slotModel->id_sensor }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">Status sensor</dt>
                        <dd class="text-[12px] font-medium text-ink">{{ $slotModel->sensor?->status ? 'Aktif' : 'Nonaktif' }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">Plat terdaftar</dt>
                        <dd class="font-mono text-[12px] font-semibold text-ink">
                            {{ $plate ?: ($plateImage ? 'Tangkapan gambar' : '-') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg bg-subtle p-3.5">
                <h3 class="text-[13px] font-semibold text-ink">Sesi saat ini</h3>

                @if ($activeLog)
                    <div class="mt-3 space-y-2.5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                                <x-icon name="user" class="size-3.5 text-ink-muted" />
                                Pengemudi
                            </span>
                            <span class="text-[12px] font-medium text-ink">{{ $activeLog->pengguna?->nama ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                                <x-icon name="entry" class="size-3.5 text-ink-muted" />
                                Masuk
                            </span>
                            <span class="text-[12px] font-medium text-ink">
                                {{ optional($activeLog->waktu_masuk)->translatedFormat('d M Y, H:i') ?? '-' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                                <x-icon name="timer" class="size-3.5 text-ink-muted" />
                                Tarif berjalan
                            </span>
                            <span class="text-[12px] font-semibold text-ink">{{ $activeLog->tarifFormatted() }}</span>
                        </div>
                    </div>

                    <a
                        href="{{ route('parking-log.show', $activeLog) }}"
                        class="mt-3.5 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-brand px-3 py-2 text-[11.5px] font-semibold text-white transition-colors hover:bg-brand-strong"
                    >
                        Buka catatan lengkap
                        <x-icon name="arrow-right" class="size-3.5" />
                    </a>
                @else
                    <div class="mt-3 flex flex-col items-center gap-1.5 rounded-lg bg-white px-3 py-6 text-center">
                        <span class="grid size-9 place-items-center rounded-full bg-brand-soft text-brand">
                            <x-icon name="parking" class="size-4" />
                        </span>
                        <p class="text-[12px] font-medium text-ink">Slot kosong</p>
                        <p class="text-[10.5px] text-ink-muted">Tidak ada kendaraan aktif di slot ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

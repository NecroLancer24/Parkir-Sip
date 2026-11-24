<x-layouts.app title="Riwayat Parkir" breadcrumb="Riwayat Parkir">
    {{-- Judul halaman --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink">Riwayat Parkir</h1>
            <p class="mt-1.5 text-[12.5px] text-ink-soft">
                Setiap aktivitas masuk dan keluar yang dicatat oleh gerbang dan sensor slot.
            </p>
        </div>

        <form method="GET" action="{{ route('parking-log') }}" class="flex flex-wrap items-center gap-2">
            <label class="flex h-8 w-[240px] items-center gap-2 rounded-lg border border-line bg-white px-3">
                <x-icon name="search" class="size-3.5 shrink-0 text-ink-muted" />
                <input type="search" name="q" value="{{ $cari }}" placeholder="Cari slot, plat, pengguna..."
                    class="w-full min-w-0 bg-transparent text-[12px] text-ink placeholder:text-ink-muted focus:outline-none">
            </label>

            <button type="submit"
                class="inline-flex h-8 items-center gap-2 rounded-lg bg-brand px-3 text-[12px] font-medium text-white transition-colors hover:bg-brand-strong">
                Cari
            </button>

            <button type="submit" formaction="{{ route('parking-log.export') }}"
                class="inline-flex h-8 items-center gap-2 rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-ink-soft transition-colors hover:bg-subtle">
                <x-icon name="download" class="size-3.5 text-ink-muted" />
                Ekspor
            </button>

            @if ($cari !== '')
            <a href="{{ route('parking-log') }}"
                class="inline-flex h-8 items-center rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-ink-soft transition-colors hover:bg-subtle">
                Atur ulang
            </a>
            @endif
        </form>
    </div>

    {{-- Tabel log parkir --}}
    <section class="mt-6 overflow-hidden rounded-xl border border-line-soft bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr
                        class="border-b border-line-soft bg-subtle text-[11px] font-semibold uppercase tracking-[0.12em] text-ink-muted">
                        <th class="px-[18px] py-3 font-semibold">Slot</th>
                        <th class="px-[18px] py-3 font-semibold">Lokasi</th>
                        <th class="px-[18px] py-3 font-semibold">Plat</th>
                        <th class="px-[18px] py-3 font-semibold">Pengguna</th>
                        <th class="px-[18px] py-3 font-semibold">Masuk</th>
                        <th class="px-[18px] py-3 font-semibold">Keluar</th>
                        <th class="px-[18px] py-3 font-semibold">Tarif</th>
                        <th class="px-[18px] py-3 font-semibold">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line-soft">
                    @forelse ($riwayatParkir as $log)
                    @php
                    $plateImage = $log->plateImageUrl();
                    $plateText = $log->plateText();
                    @endphp

                    <tr
                        class="group relative cursor-pointer transition-colors hover:bg-subtle [transform:translateZ(0)]">
                        <td class="whitespace-nowrap px-[18px] py-3.5">
                            <a href="{{ route('parking-log.show', $log) }}" class="absolute inset-0 z-10"
                                aria-label="Lihat detail log #{{ $log->id_log }}"></a>

                            @if ($log->nomer_slot)
                                <span class="font-mono text-[13px] font-semibold text-ink">{{ $log->nomer_slot }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-[11px] font-medium text-amber-700">
                                    Menunggu slot
                                </span>
                            @endif
                        </td>

                        <td class="whitespace-nowrap px-[18px] py-3.5 text-[13px] text-ink-soft">
                            {{ $log->lokasi ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-[18px] py-3.5">
                            <div class="flex items-center gap-2.5">
                                @if ($plateImage)
                                <img src="{{ $plateImage }}"
                                    alt="Tangkapan plat untuk {{ $plateText ?: ($log->nomer_slot ?? 'kendaraan') }}"
                                    class="size-8 shrink-0 rounded-md border border-line-soft object-cover"
                                    loading="lazy">
                                @endif

                                <span
                                    class="inline-flex rounded-md bg-canvas px-2 py-1 font-mono text-[12px] font-medium text-ink-soft">
                                    {{ $plateText ?: ($plateImage ? 'Tangkapan gambar' : '-') }}
                                </span>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-[18px] py-3.5 text-[13px] text-ink">
                            {{ $log->nama_pengguna }}
                        </td>

                        <td class="whitespace-nowrap px-[18px] py-3.5 text-[13px] text-ink-muted">
                            {{ optional($log->waktu_masuk)->translatedFormat('d M Y, H:i') ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-[18px] py-3.5 text-[13px] text-ink-muted">
                            {{ optional($log->waktu_keluar)->translatedFormat('d M Y, H:i') ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap px-[18px] py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex rounded-md bg-canvas px-2 py-1 text-[12px] font-medium text-ink">
                                    {{ $log->tarifFormatted() }}
                                </span>

                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-ink-muted">
                                    <x-icon :name="$log->jenis_kendaraan === 'mobil' ? 'car' : 'motor'" class="size-3.5" />
                                    {{ $log->jenisKendaraanLabel() }}
                                </span>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-[18px] py-3.5">
                            <div class="flex items-center gap-2">
                                @if ($log->waktu_keluar === null)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-1 text-[11.5px] font-medium text-brand">
                                    <span class="size-1.5 rounded-full bg-brand"></span>
                                    Sedang parkir
                                </span>
                                @else
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-line-soft px-2.5 py-1 text-[11.5px] font-medium text-ink-soft">
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
                        <td colspan="8" class="px-[18px] py-14 text-center">
                            <span class="mx-auto grid size-10 place-items-center rounded-full bg-subtle text-ink-muted">
                                <x-icon name="activity" class="size-5" />
                            </span>
                            <p class="mt-3 text-[12.5px] font-medium text-ink">Belum ada catatan parkir</p>
                            <p class="mt-1 text-[11.5px] text-ink-muted">
                                {{ $cari !== '' ? 'Coba slot, plat, atau pengguna lain.' : 'Catatan akan muncul setelah kendaraan masuk gerbang.' }}
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div
            class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-[18px] py-3.5 text-[12px] text-ink-muted">
            <p>
                Menampilkan
                <span class="font-medium text-ink-soft">{{ $riwayatParkir->firstItem() ?? 0 }}-{{
                    $riwayatParkir->lastItem() ?? 0 }}</span>
                dari
                <span class="font-medium text-ink-soft">{{ $riwayatParkir->total() }}</span>
                catatan
            </p>

            <div>{{ $riwayatParkir->links() }}</div>
        </div>
    </section>
</x-layouts.app>

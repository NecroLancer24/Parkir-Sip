<x-layouts.app title="Parking log" breadcrumb="Parking log">
    {{-- Page header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink">Parking log</h1>
            <p class="mt-1.5 text-[12.5px] text-ink-soft">
                Every entry and exit recorded by the gate and slot sensors.
            </p>
        </div>

        <form method="GET" action="{{ route('parking-log') }}" class="flex flex-wrap items-center gap-2">
            <label class="flex h-8 w-[240px] items-center gap-2 rounded-lg border border-line bg-white px-3">
                <x-icon name="search" class="size-3.5 shrink-0 text-ink-muted" />
                <input
                    type="search"
                    name="q"
                    value="{{ $cari }}"
                    placeholder="Search slot, plate, user..."
                    class="w-full min-w-0 bg-transparent text-[12px] text-ink placeholder:text-ink-muted focus:outline-none"
                >
            </label>

            <button
                type="submit"
                class="inline-flex h-8 items-center gap-2 rounded-lg bg-brand px-3 text-[12px] font-medium text-white transition-colors hover:bg-brand-strong"
            >
                Search
            </button>

            @if ($cari !== '')
                <a
                    href="{{ route('parking-log') }}"
                    class="inline-flex h-8 items-center rounded-lg border border-line bg-white px-3 text-[12px] font-medium text-ink-soft transition-colors hover:bg-subtle"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Parking log table --}}
    <section class="mt-6 overflow-hidden rounded-xl border border-line-soft bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-line-soft bg-subtle text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">
                        <th class="px-[18px] py-3 font-semibold">Slot</th>
                        <th class="px-[18px] py-3 font-semibold">Location</th>
                        <th class="px-[18px] py-3 font-semibold">Plate</th>
                        <th class="px-[18px] py-3 font-semibold">User</th>
                        <th class="px-[18px] py-3 font-semibold">Entry</th>
                        <th class="px-[18px] py-3 font-semibold">Exit</th>
                        <th class="px-[18px] py-3 font-semibold">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line-soft">
                    @forelse ($riwayatParkir as $log)
                        @php
                            $plateImage = $log->plateImageUrl();
                            $plateText = $log->plateText();
                        @endphp

                        <tr class="group relative cursor-pointer transition-colors hover:bg-subtle [transform:translateZ(0)]">
                            <td class="whitespace-nowrap px-[18px] py-3.5">
                                <a
                                    href="{{ route('parking-log.show', $log) }}"
                                    class="absolute inset-0 z-10"
                                    aria-label="View details for slot {{ $log->nomer_slot }}"
                                ></a>

                                <span class="font-mono text-[12px] font-semibold text-ink">{{ $log->nomer_slot }}</span>
                            </td>

                            <td class="whitespace-nowrap px-[18px] py-3.5 text-[12px] text-ink-soft">
                                {{ $log->lokasi ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-[18px] py-3.5">
                                <div class="flex items-center gap-2.5">
                                    @if ($plateImage)
                                        <img
                                            src="{{ $plateImage }}"
                                            alt="Plate capture for {{ $plateText ?: $log->nomer_slot }}"
                                            class="size-8 shrink-0 rounded-md border border-line-soft object-cover"
                                            loading="lazy"
                                        >
                                    @endif

                                    <span class="inline-flex rounded-md bg-canvas px-2 py-1 font-mono text-[11px] font-medium text-ink-soft">
                                        {{ $plateText ?: ($plateImage ? 'Image capture' : '-') }}
                                    </span>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-[18px] py-3.5 text-[12px] text-ink">
                                {{ $log->nama_pengguna }}
                            </td>

                            <td class="whitespace-nowrap px-[18px] py-3.5 text-[12px] text-ink-muted">
                                {{ optional($log->waktu_masuk)->format('d M Y, H:i') ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-[18px] py-3.5 text-[12px] text-ink-muted">
                                {{ optional($log->waktu_keluar)->format('d M Y, H:i') ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-[18px] py-3.5">
                                <div class="flex items-center gap-2">
                                    @if ($log->waktu_keluar === null)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-1 text-[10.5px] font-medium text-brand">
                                            <span class="size-1.5 rounded-full bg-brand"></span>
                                            Parked
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-line-soft px-2.5 py-1 text-[10.5px] font-medium text-ink-soft">
                                            <span class="size-1.5 rounded-full bg-ink-muted"></span>
                                            Completed
                                        </span>
                                    @endif

                                    <x-icon
                                        name="arrow-right"
                                        class="size-3.5 text-ink-muted opacity-0 transition-opacity group-hover:opacity-100"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-[18px] py-14 text-center">
                                <span class="mx-auto grid size-10 place-items-center rounded-full bg-subtle text-ink-muted">
                                    <x-icon name="activity" class="size-5" />
                                </span>
                                <p class="mt-3 text-[12.5px] font-medium text-ink">No parking records found</p>
                                <p class="mt-1 text-[11.5px] text-ink-muted">
                                    {{ $cari !== '' ? 'Try a different slot, plate, or user.' : 'Records will appear here once vehicles enter the gate.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-[18px] py-3.5 text-[11px] text-ink-muted">
            <p>
                Showing
                <span class="font-medium text-ink-soft">{{ $riwayatParkir->firstItem() ?? 0 }}-{{ $riwayatParkir->lastItem() ?? 0 }}</span>
                of
                <span class="font-medium text-ink-soft">{{ $riwayatParkir->total() }}</span>
                records
            </p>

            <div>{{ $riwayatParkir->links() }}</div>
        </div>
    </section>
</x-layouts.app>

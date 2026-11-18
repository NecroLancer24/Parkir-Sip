<x-layouts.app title="Slot detail" breadcrumb="Overview / Slot detail">
    @php
        $plate = $slot->sensor?->plateText();
        $plateImage = $slot->sensor?->plateImageUrl();
        $isOccupied = $slot->status_slot;
    @endphp

    {{-- Page header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-ink-muted transition-colors hover:text-brand"
            >
                <x-icon name="arrow-left" class="size-3.5" />
                Back to overview
            </a>

            <h1 class="mt-2 text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink">
                Slot {{ $slot->nomer_slot }}
            </h1>

            <p class="mt-1.5 text-[12.5px] text-ink-soft">
                {{ $slot->lokasi ?? 'Unknown location' }}
                <span class="px-1 text-ink-muted">·</span>
                Sensor #{{ $slot->id_sensor }}
            </p>
        </div>

        @if ($isOccupied)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-line-soft px-3 py-1.5 text-[11px] font-medium text-ink-soft">
                <span class="size-1.5 rounded-full bg-ink-muted"></span>
                Occupied
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-3 py-1.5 text-[11px] font-medium text-brand">
                <span class="size-1.5 rounded-full bg-brand"></span>
                Available
            </span>
        @endif
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            {{-- Parking history --}}
            <section class="overflow-hidden rounded-xl border border-line-soft bg-white shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft px-[18px] py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand">
                            <x-icon name="parking" class="size-4" />
                        </span>

                        <div>
                            <h2 class="text-[15px] font-semibold text-ink">Parking history</h2>
                            <p class="mt-0.5 text-[11.5px] text-ink-muted">Every record captured at this slot</p>
                        </div>
                    </div>

                    <span class="text-[11.5px] text-ink-muted">{{ $riwayatSlot->count() }} records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr
                                class="border-b border-line-soft bg-subtle text-[11px] font-semibold uppercase tracking-[0.12em] text-ink-muted">
                                <th class="px-[18px] py-3 font-semibold">Plate</th>
                                <th class="px-[18px] py-3 font-semibold">User</th>
                                <th class="px-[18px] py-3 font-semibold">Vehicle</th>
                                <th class="px-[18px] py-3 font-semibold">Entry</th>
                                <th class="px-[18px] py-3 font-semibold">Exit</th>
                                <th class="px-[18px] py-3 font-semibold">Tariff</th>
                                <th class="px-[18px] py-3 font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-line-soft">
                            @forelse ($riwayatSlot as $log)
                            <tr
                                class="group relative cursor-pointer transition-colors hover:bg-subtle [transform:translateZ(0)]">
                                <td class="whitespace-nowrap px-[18px] py-3.5">
                                    <a href="{{ route('parking-log.show', $log) }}" class="absolute inset-0 z-10"
                                        aria-label="View details for log #{{ $log->id_log }}"></a>

                                    <span class="font-mono text-[13px] font-medium text-ink-soft">
                                        {{ $log->plateText() ?? ($log->plateImageUrl() ? 'Image capture' : '-') }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-[18px] py-3.5 text-[13px] text-ink">
                                    {{ $log->pengguna?->nama ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-[18px] py-3.5">
                                    <span class="inline-flex items-center gap-1 text-[12px] font-medium text-ink-soft">
                                        <x-icon :name="$log->jenis_kendaraan === 'mobil' ? 'car' : 'motor'" class="size-3.5" />
                                        {{ $log->jenisKendaraanLabel() }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-[18px] py-3.5 text-[13px] text-ink-muted">
                                    {{ optional($log->waktu_masuk)->format('d M Y, H:i') ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-[18px] py-3.5 text-[13px] text-ink-muted">
                                    {{ optional($log->waktu_keluar)->format('d M Y, H:i') ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-[18px] py-3.5">
                                    <span class="inline-flex rounded-md bg-canvas px-2 py-1 text-[12px] font-medium text-ink">
                                        {{ $log->tarifFormatted() }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-[18px] py-3.5">
                                    <div class="flex items-center gap-2">
                                        @if ($log->waktu_keluar === null)
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-1 text-[11.5px] font-medium text-brand">
                                            <span class="size-1.5 rounded-full bg-brand"></span>
                                            Parked
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-line-soft px-2.5 py-1 text-[11.5px] font-medium text-ink-soft">
                                            <span class="size-1.5 rounded-full bg-ink-muted"></span>
                                            Completed
                                        </span>
                                        @endif

                                        <x-icon name="arrow-right"
                                            class="size-3.5 text-ink-muted opacity-0 transition-opacity group-hover:opacity-100" />
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-[18px] py-14 text-center">
                                    <span class="mx-auto grid size-10 place-items-center rounded-full bg-subtle text-ink-muted">
                                        <x-icon name="activity" class="size-5" />
                                    </span>
                                    <p class="mt-3 text-[12.5px] font-medium text-ink">No parking records yet</p>
                                    <p class="mt-1 text-[11.5px] text-ink-muted">
                                        Records will appear here once a vehicle parks at this slot.
                                    </p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- Slot summary sidebar --}}
        <aside class="space-y-5">
            <section class="rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
                <h2 class="text-[15px] font-semibold text-ink">Slot details</h2>
                <p class="mt-1 text-[11.5px] text-ink-muted">Hardware and location for this slot</p>

                <dl class="mt-4 space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">Slot</dt>
                        <dd class="font-mono text-[12.5px] font-semibold text-ink">{{ $slot->nomer_slot }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">Location</dt>
                        <dd class="text-[12.5px] font-medium text-ink">{{ $slot->lokasi ?? '-' }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">Sensor ID</dt>
                        <dd class="font-mono text-[12.5px] text-ink">#{{ $slot->id_sensor }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[11.5px] text-ink-soft">Sensor status</dt>
                        <dd class="text-[12.5px] font-medium text-ink">
                            {{ $slot->sensor?->status ? 'Online' : 'Offline' }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 rounded-lg bg-subtle px-3 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Registered plate</p>
                    <p class="mt-1.5 font-mono text-[13px] font-semibold tracking-wide text-ink">
                        {{ $plate ?: ($plateImage ? 'Image capture' : '-') }}
                    </p>
                </div>
            </section>

            <section class="rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
                <h2 class="text-[15px] font-semibold text-ink">Current session</h2>
                <p class="mt-1 text-[11.5px] text-ink-muted">The vehicle parked right now</p>

                @if ($logAktif)
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-line-soft px-3 py-3">
                            <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                                <x-icon name="user" class="size-4 text-ink-muted" />
                                Driver
                            </span>
                            <span class="text-[12.5px] font-medium text-ink">{{ $logAktif->pengguna?->nama ?? '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-lg border border-line-soft px-3 py-3">
                            <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                                <x-icon name="entry" class="size-4 text-ink-muted" />
                                Entry
                            </span>
                            <span class="text-[12px] font-medium text-ink">
                                {{ optional($logAktif->waktu_masuk)->format('d M Y, H:i') ?? '-' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-lg border border-line-soft px-3 py-3">
                            <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                                <x-icon name="timer" class="size-4 text-ink-muted" />
                                Running tariff
                            </span>
                            <span class="text-[12.5px] font-semibold text-ink">{{ $logAktif->tarifFormatted() }}</span>
                        </div>
                    </div>

                    <a
                        href="{{ route('parking-log.show', $logAktif) }}"
                        class="mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-brand px-3 py-2 text-[12px] font-semibold text-white transition-colors hover:bg-brand-strong"
                    >
                        Open full record
                        <x-icon name="arrow-right" class="size-3.5" />
                    </a>
                @else
                    <div class="mt-4 flex flex-col items-center gap-2 rounded-lg bg-subtle px-3 py-8 text-center">
                        <span class="grid size-10 place-items-center rounded-full bg-white text-brand">
                            <x-icon name="parking" class="size-5" />
                        </span>
                        <p class="text-[12.5px] font-medium text-ink">Slot is free</p>
                        <p class="text-[11px] text-ink-muted">No active vehicle is parked here.</p>
                    </div>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.app>

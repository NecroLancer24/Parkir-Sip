<x-layouts.app title="Parking log detail" breadcrumb="Parking log / Detail">
    @php
        $image = $log->plateImageUrl();
        $plate = $log->plateText();
        $isParked = $log->waktu_keluar === null;
        $duration = $log->waktu_masuk?->diff($log->waktu_keluar ?? now());

        $durationLabel = '-';

        if ($duration) {
            $parts = [];

            if ($duration->d > 0) {
                $parts[] = $duration->d.'d';
            }

            if ($duration->h > 0) {
                $parts[] = $duration->h.'h';
            }

            $parts[] = $duration->i.'m';

            $durationLabel = implode(' ', $parts);
        }
    @endphp

    {{-- Page header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a
                href="{{ route('parking-log') }}"
                class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-ink-muted transition-colors hover:text-brand"
            >
                <x-icon name="arrow-left" class="size-3.5" />
                Back to parking log
            </a>

            <h1 class="mt-2 text-[26px] font-semibold leading-tight tracking-[-0.02em] text-ink">
                Log #{{ $log->id_log }}
            </h1>

            <p class="mt-1.5 text-[12.5px] text-ink-soft">
                Slot {{ $log->slot?->nomer_slot ?? '-' }}
                <span class="px-1 text-ink-muted">·</span>
                {{ $log->slot?->lokasi ?? 'Unknown location' }}
            </p>
        </div>

        @if ($isParked)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-3 py-1.5 text-[11px] font-medium text-brand">
                <span class="size-1.5 rounded-full bg-brand"></span>
                Currently parked
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-line-soft px-3 py-1.5 text-[11px] font-medium text-ink-soft">
                <span class="size-1.5 rounded-full bg-ink-muted"></span>
                Completed
            </span>
        @endif
    </div>

    <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-5">
            {{-- Plate capture --}}
            <section class="overflow-hidden rounded-xl border border-line-soft bg-white shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 px-[18px] py-4">
                    <div class="flex items-center gap-2.5">
                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand">
                            <x-icon name="camera" class="size-4" />
                        </span>

                        <div>
                            <h2 class="text-[15px] font-semibold text-ink">Plate capture</h2>
                            <p class="mt-0.5 text-[11.5px] text-ink-muted">Snapshot from the ESP32-CAM at the entry gate</p>
                        </div>
                    </div>

                    @if ($image)
                        <a
                            href="{{ $image }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-brand hover:text-brand-strong"
                        >
                            Open full size
                            <x-icon name="external-link" class="size-3.5" />
                        </a>
                    @endif
                </div>

                <div class="border-y border-line-soft bg-[#0f1f24]">
                    @if ($image)
                        <img
                            src="{{ $image }}"
                            alt="Plate capture for {{ $plate ?: 'slot '.$log->slot?->nomer_slot }}"
                            class="mx-auto max-h-[420px] w-full object-contain"
                        >
                    @else
                        <div class="flex aspect-[16/9] flex-col items-center justify-center gap-2 px-6 text-center">
                            <span class="grid size-11 place-items-center rounded-full bg-white/5 text-white/40">
                                <x-icon name="camera" class="size-5" />
                            </span>
                            <p class="text-[12.5px] font-medium text-white/70">No capture available</p>
                            <p class="max-w-[280px] text-[11px] leading-relaxed text-white/40">
                                The ESP32-CAM has not uploaded an image for this entry yet.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 px-[18px] py-3.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex rounded-md bg-canvas px-2.5 py-1 font-mono text-[12px] font-semibold tracking-wide text-ink">
                            {{ $plate ?: 'Plate not recognised' }}
                        </span>

                        <span class="text-[10.5px] text-ink-muted">
                            {{ $image ? 'Image captured by ESP32-CAM' : 'No image attached to this log' }}
                        </span>
                    </div>

                    <span class="inline-flex items-center gap-1.5 text-[10.5px] text-ink-muted">
                        <x-icon name="clock" class="size-3" />
                        {{ optional($log->waktu_masuk)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>
            </section>

            {{-- Record details --}}
            <section class="rounded-xl border border-line-soft bg-white shadow-card">
                <div class="border-b border-line-soft px-[18px] py-4">
                    <h2 class="text-[15px] font-semibold text-ink">Record details</h2>
                    <p class="mt-1 text-[11.5px] text-ink-muted">Every value stored for this parking log entry</p>
                </div>

                <dl class="grid gap-x-6 gap-y-5 px-[18px] py-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Log ID</dt>
                        <dd class="mt-1.5 font-mono text-[12.5px] text-ink">#{{ $log->id_log }}</dd>
                    </div>

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Sensor ID</dt>
                        <dd class="mt-1.5 font-mono text-[12.5px] text-ink">#{{ $log->id_sensor }}</dd>
                    </div>

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">User</dt>
                        <dd class="mt-1.5 text-[12.5px] font-medium text-ink">{{ $log->pengguna?->nama ?? '-' }}</dd>
                        <dd class="mt-0.5 font-mono text-[11px] text-ink-muted">{{ $log->pengguna?->username ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Slot</dt>
                        <dd class="mt-1.5 text-[12.5px] font-medium text-ink">{{ $log->slot?->nomer_slot ?? '-' }}</dd>
                        <dd class="mt-0.5 text-[11px] text-ink-muted">{{ $log->slot?->lokasi ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Entry time</dt>
                        <dd class="mt-1.5 text-[12.5px] text-ink">
                            {{ optional($log->waktu_masuk)->format('d M Y, H:i:s') ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Exit time</dt>
                        <dd class="mt-1.5 text-[12.5px] text-ink">
                            {{ optional($log->waktu_keluar)->format('d M Y, H:i:s') ?? 'Still parked' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Duration</dt>
                        <dd class="mt-1.5 text-[12.5px] text-ink">{{ $durationLabel }}</dd>
                    </div>

                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Plate text</dt>
                        <dd class="mt-1.5 font-mono text-[12.5px] text-ink">{{ $plate ?: '-' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Summary sidebar --}}
        <aside class="space-y-5">
            <section class="rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
                <h2 class="text-[15px] font-semibold text-ink">Session summary</h2>
                <p class="mt-1 text-[11.5px] text-ink-muted">Entry, exit, and occupancy at a glance</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-subtle p-3">
                        <span class="grid size-7 place-items-center rounded-md bg-white text-brand">
                            <x-icon name="entry" class="size-3.5" />
                        </span>
                        <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-muted">Entry</p>
                        <p class="mt-1 text-[12px] font-medium text-ink">
                            {{ optional($log->waktu_masuk)->format('H:i') ?? '-' }}
                        </p>
                        <p class="text-[10.5px] text-ink-muted">
                            {{ optional($log->waktu_masuk)->format('d M Y') ?? '-' }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-subtle p-3">
                        <span class="grid size-7 place-items-center rounded-md bg-white text-ink-soft">
                            <x-icon name="gate" class="size-3.5" />
                        </span>
                        <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-muted">Exit</p>
                        <p class="mt-1 text-[12px] font-medium text-ink">
                            {{ optional($log->waktu_keluar)->format('H:i') ?? '--:--' }}
                        </p>
                        <p class="text-[10.5px] text-ink-muted">
                            {{ optional($log->waktu_keluar)->format('d M Y') ?? 'Still parked' }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3 rounded-lg border border-line-soft px-3 py-3">
                    <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                        <x-icon name="timer" class="size-4 text-ink-muted" />
                        Duration
                    </span>
                    <span class="text-[12.5px] font-semibold text-ink">{{ $durationLabel }}</span>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3 rounded-lg border border-line-soft px-3 py-3">
                    <span class="inline-flex items-center gap-2 text-[11.5px] text-ink-soft">
                        <x-icon name="pin" class="size-4 text-ink-muted" />
                        Location
                    </span>
                    <span class="text-[12px] font-medium text-ink">{{ $log->slot?->lokasi ?? '-' }}</span>
                </div>
            </section>

            <section class="rounded-xl border border-line-soft bg-white p-[18px] shadow-card">
                <h2 class="text-[15px] font-semibold text-ink">Vehicle owner</h2>

                <div class="mt-4 flex items-center gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand-soft text-brand">
                        <x-icon name="user" class="size-[18px]" />
                    </span>

                    <div class="min-w-0">
                        <p class="truncate text-[12.5px] font-semibold text-ink">{{ $log->pengguna?->nama ?? '-' }}</p>
                        <p class="truncate font-mono text-[11px] text-ink-muted">{{ $log->pengguna?->username ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-subtle px-3 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-muted">Registered plate</p>
                    <p class="mt-1.5 font-mono text-[13px] font-semibold tracking-wide text-ink">{{ $plate ?: '-' }}</p>
                </div>
            </section>
        </aside>
    </div>
</x-layouts.app>

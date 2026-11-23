@props(['floors', 'activeFloorIndex' => 0, 'selectedId' => null])

@php
    // Ambil 24 slot dari database untuk dijadikan 1 area parkir polos tanpa nomor slot
    $slotList = $floors->pluck('slots')->flatten(1)->sortBy('nomer_slot')->take(24)->values();
    $slotOccupied = $slotList->where('status_slot', true)->count();
    $slotAvailable = max($slotList->count() - $slotOccupied, 0);

    // Bagi 24 slot menjadi 4 baris (masing-masing 6 kotak)
    $bands = $slotList->chunk(6)->values()->map(fn ($bandSlots, $bandIndex) => [
        'noseDown' => $bandIndex % 2 === 0,
        'slots' => $bandSlots->values(),
    ]);
@endphp

@if ($slotList->isNotEmpty())
    <div class="flex flex-col gap-[14px]">
        {{-- Denah Parkir 1 Area (24 Kotak Tanpa Tag Nomor Slot) --}}
        <div class="mx-auto w-full max-w-[860px] rounded-[8px] border border-[#e3e9eb] bg-[#f8fafa] p-[10px]">
            <div aria-label="Denah parkir" class="rounded-[6px] border-2 border-[#172c32] bg-white p-[10px]">
                @foreach ($bands as $bandIndex => $band)
                    <div class="flex gap-[5px]">
                        @foreach ($band['slots'] as $slotModel)
                            @php
                                $noseDown = $band['noseDown'];
                                $bayClasses = $slotModel->status_slot
                                    ? 'border-[#e39a9a] bg-[#fdecec]'
                                    : 'border-[#a9d4c9] bg-[#e8f5f1]';
                            @endphp

                            <div
                                title="{{ $slotModel->status_slot ? 'Terisi' : 'Tersedia' }}"
                                @class([
                                    'flex h-[68px] min-w-0 flex-1 flex-col items-center justify-center rounded-[4px] border p-[4px] transition-colors',
                                    $bayClasses,
                                ])
                            >
                                @if ($slotModel->status_slot)
                                    {{-- Ikon mobil merah jika slot terisi --}}
                                    <div @class([
                                        'flex h-[34px] w-[22px] flex-col gap-[6px] rounded-[5px] bg-[#cd5c5c] px-[4px] pb-[4px] pt-[5px]',
                                        'rotate-180' => $noseDown,
                                    ])>
                                        <div class="h-[6px] w-[14px] rounded-[1px] bg-[#f0cfcf]"></div>
                                        <div class="h-[5px] w-[14px] rounded-[1px] bg-[#e5b8b8]"></div>
                                    </div>
                                @else
                                    {{-- Huruf P hijau jika slot kosong --}}
                                    <p class="text-[20px] font-semibold text-[#087f75]">P</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if (in_array($bandIndex, [0, 2]))
                        <div class="my-[4px] flex h-[22px] items-center justify-center border-y border-dashed border-[#e3e9eb]">
                            <span class="text-[9px] tracking-[2px] text-[#a0b0b8]">----</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Keterangan Warna --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-[14px]">
                <span class="inline-flex items-center gap-[5px]">
                    <span class="size-[7px] rounded-[2px] bg-[#cd5c5c]"></span>
                    <span class="text-[10px] text-[#718087]">Terisi</span>
                </span>
                <span class="inline-flex items-center gap-[5px]">
                    <span class="size-[7px] rounded-[2px] bg-[#087f75]"></span>
                    <span class="text-[10px] text-[#718087]">Tersedia</span>
                </span>
            </div>

            <p class="text-[10px] text-[#718087]">
                <span class="font-medium text-[#087f75]">{{ $slotAvailable }} tersedia</span> dari {{ $slotList->count() }} slot
            </p>
        </div>

        <p class="flex items-center gap-1.5 text-[10px] text-[#a0b0b8]">
            <x-icon name="refresh" class="size-3" />
            Terakhir Diperbarui pada: {{ now()->format('H:i:s') }} WIB
        </p>
    </div>
@else
    <div class="flex h-[120px] items-center justify-center text-[11.5px] text-ink-muted">
        Belum ada data slot parkir.
    </div>
@endif
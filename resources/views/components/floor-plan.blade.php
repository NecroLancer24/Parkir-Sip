@props(['floors', 'activeFloorIndex' => 0, 'selectedId' => null])

@php
    $floor = $floors->get($activeFloorIndex) ?? $floors->first();
    $floorNumber = $floor['number'] ?? 1;
    $slotList = $floor ? $floor['slots']->values() : collect();
    $floorOccupied = $slotList->where('status_slot', true)->count();
    $floorAvailable = max($slotList->count() - $floorOccupied, 0);

    // Denah: bays arranged in up to four bands, paired nose-to-nose around the
    // drive lanes (band 1+2 share lane one, band 3+4 share lane two).
    $bandSize = max((int) ceil($slotList->count() / 4), 1);
    $bands = $slotList->chunk($bandSize)->values()->map(fn ($bandSlots, $bandIndex) => [
        'noseDown' => $bandIndex % 2 === 0,
        'slots' => $bandSlots->values(),
    ]);
@endphp

@if ($slotList->isNotEmpty())
    <div class="flex flex-col gap-[14px]">
        {{-- Floor tabs, one pill per level --}}
        <div class="flex items-center gap-2">
            @foreach ($floors as $index => $tab)
                @php $tabOccupied = $tab['slots']->where('status_slot', true)->count(); @endphp
                <a
                    href="{{ route('dashboard', array_filter(['lantai' => $tab['number'], 'slot' => $selectedId])) }}#floor-plan"
                    @class([
                        'inline-flex h-[29px] flex-1 items-center justify-center rounded-[6px] border text-[11px] transition-colors',
                        'border-[#b1d8ce] bg-[#e8f5f1] text-[#087f75]' => $index === $activeFloorIndex,
                        'border-[#e3e9eb] bg-white text-[#718087] hover:bg-[#f8fafa]' => $index !== $activeFloorIndex,
                    ])
                    @if ($index === $activeFloorIndex) aria-current="true" @endif
                >
                    {{ $tab['label'] }} · {{ $tabOccupied }} / {{ $tab['slots']->count() }} occupied
                </a>
            @endforeach
        </div>

        {{-- Parking map: mini floor plan, bay bands paired around the drive lanes --}}
        <div class="mx-auto w-full max-w-[860px] rounded-[8px] border border-[#e3e9eb] bg-[#f8fafa] p-[10px]">
            <div aria-label="Parking map" class="rounded-[6px] border-2 border-[#172c32] bg-white p-[8px]">
                {{-- Structural columns along the top wall --}}
                <div class="mb-[6px] flex justify-between px-[40px]" aria-hidden="true">
                    @foreach (range(1, 7) as $column)
                        <span class="size-[10px] rounded-[1px] border border-[#91a5ae] bg-[#e3e9eb]"></span>
                    @endforeach
                </div>

                @foreach ($bands as $bandIndex => $band)
                    @if ($band['slots']->isNotEmpty())
                        <div class="flex gap-[5px]">
                            @foreach ($band['slots'] as $slotModel)
                                @php
                                    $noseDown = $band['noseDown'];
                                    $isSelected = $slotModel->id_slot === $selectedId;

                                    if ($isSelected) {
                                        $bayClasses = $slotModel->status_slot
                                            ? 'border-[#087f75] bg-[#fdecec]'
                                            : 'border-[#087f75] bg-[#e8f5f1]';
                                    } elseif ($slotModel->status_slot) {
                                        $bayClasses = 'border-[#e39a9a] bg-[#fdecec]';
                                    } else {
                                        $bayClasses = 'border-[#a9d4c9] bg-[#e8f5f1]';
                                    }
                                @endphp

                                <a
                                    href="{{ route('dashboard', ['slot' => $slotModel->id_slot, 'lantai' => $floorNumber]) }}#slot-detail"
                                    title="{{ $slotModel->nomer_slot }} - {{ $slotModel->status_slot ? 'Occupied' : 'Available' }} - {{ $slotModel->lokasi }}"
                                    @class([
                                        'flex h-[72px] min-w-0 flex-1 flex-col items-center justify-center gap-[4px] rounded-[4px] border p-[4px] transition-colors',
                                        $bayClasses,
                                    ])
                                >
                                    @if ($slotModel->status_slot)
                                        @if ($noseDown)
                                            <p class="text-[10px] font-medium leading-[normal] text-[#4e6269]">{{ $slotModel->nomer_slot }}</p>
                                            <div class="flex h-[34px] w-[22px] rotate-180 flex-col gap-[6px] rounded-[5px] bg-[#cd5c5c] px-[4px] pb-[4px] pt-[5px]">
                                                <div class="h-[6px] w-[14px] rounded-[1px] bg-[#f0cfcf]"></div>
                                                <div class="h-[5px] w-[14px] rounded-[1px] bg-[#e5b8b8]"></div>
                                            </div>
                                        @else
                                            <div class="flex h-[34px] w-[22px] flex-col gap-[6px] rounded-[5px] bg-[#cd5c5c] px-[4px] pb-[4px] pt-[5px]">
                                                <div class="h-[6px] w-[14px] rounded-[1px] bg-[#f0cfcf]"></div>
                                                <div class="h-[5px] w-[14px] rounded-[1px] bg-[#e5b8b8]"></div>
                                            </div>
                                            <p class="text-[10px] font-medium leading-[normal] text-[#4e6269]">{{ $slotModel->nomer_slot }}</p>
                                        @endif
                                    @else
                                        @if ($noseDown)
                                            <p class="text-[10px] font-medium leading-[normal] text-[#087f75]">{{ $slotModel->nomer_slot }}</p>
                                            <p class="text-[20px] leading-[normal] text-[#087f75]">P</p>
                                        @else
                                            <p class="text-[20px] leading-[normal] text-[#087f75]">P</p>
                                            <p class="text-[10px] font-medium leading-[normal] text-[#087f75]">{{ $slotModel->nomer_slot }}</p>
                                        @endif
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if (in_array($bandIndex, [0, 2]))
                        {{-- Drive lane with one-way flow, gates at both ends --}}
                        <div class="flex h-[28px] items-center justify-between border-y border-dashed border-[#e3e9eb] px-[20px] text-[#a0b0b8]">
                            <span class="flex items-center gap-1 text-[10px] font-semibold leading-[normal] text-[#087f75]">
                                Entry
                                <x-icon name="arrow-right" class="size-3.5" />
                            </span>
                            <p class="text-[9px] leading-[normal] tracking-[1.5px]">ONE WAY</p>
                            <span class="flex items-center gap-1 text-[10px] font-semibold leading-[normal] text-[#4e6269]">
                                <x-icon name="arrow-right" class="size-3.5" />
                                Exit
                            </span>
                        </div>
                    @endif
                @endforeach

                {{-- Structural columns along the bottom wall --}}
                <div class="mt-[6px] flex justify-between px-[40px]" aria-hidden="true">
                    @foreach (range(1, 7) as $column)
                        <span class="size-[10px] rounded-[1px] border border-[#91a5ae] bg-[#e3e9eb]"></span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Legend and availability summary --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-[14px]">
                <span class="inline-flex items-center gap-[5px]">
                    <span class="size-[7px] rounded-[2px] bg-[#cd5c5c]"></span>
                    <span class="text-[10px] leading-[normal] text-[#718087]">Occupied</span>
                </span>
                <span class="inline-flex items-center gap-[5px]">
                    <span class="size-[7px] rounded-[2px] bg-[#087f75]"></span>
                    <span class="text-[10px] leading-[normal] text-[#718087]">Available</span>
                </span>
                <span class="inline-flex items-center gap-[5px]">
                    <span class="size-[7px] rounded-[2px] bg-[#d5b478]"></span>
                    <span class="text-[10px] leading-[normal] text-[#718087]">Reserved</span>
                </span>
            </div>

            <p class="text-[10px] leading-[normal] text-[#718087]">
                <span class="font-medium text-[#087f75]">{{ $floorAvailable }} available</span> in {{ $floor['label'] }}
            </p>
        </div>

        {{-- Sync note --}}
        <p class="flex items-center gap-1.5 text-[10px] leading-[normal] text-[#a0b0b8]">
            <x-icon name="refresh" class="size-3" />
            Live sensor sync active · Updated at {{ now()->format('H:i:s') }}
        </p>
    </div>
@else
    <div class="flex h-[120px] items-center justify-center text-[11.5px] text-ink-muted">
        No slots registered yet.
    </div>
@endif
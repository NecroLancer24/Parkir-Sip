@props(['href' => '#', 'icon', 'active' => false, 'badge' => null])

<a
    href="{{ $href }}"
    @class([
        'group flex h-10 items-center gap-3 rounded-lg px-3 text-[13.5px] transition-colors',
        'bg-brand-soft font-medium text-brand' => $active,
        'text-ink-soft hover:bg-subtle hover:text-ink' => ! $active,
    ])
    @if ($active) aria-current="page" @endif
>
    <x-icon
        :name="$icon"
        :class="'size-[17px] shrink-0 '.($active ? 'text-brand' : 'text-ink-muted group-hover:text-ink-soft')"
    />

    <span class="flex-1 truncate">{{ $slot }}</span>

    @if ($badge)
        <span @class([
            'min-w-[18px] rounded-md px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none',
            'bg-white/70 text-brand' => $active,
            'bg-canvas text-ink-soft' => ! $active,
        ])>{{ $badge }}</span>
    @endif
</a>

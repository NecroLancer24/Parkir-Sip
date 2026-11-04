@props(['name', 'stroke' => 1.6])

<svg
    {{ $attributes->merge(['class' => 'size-4', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => $stroke, 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}
>
    @switch($name)
        @case('dashboard')
            <rect width="7" height="9" x="3" y="3" rx="1" />
            <rect width="7" height="5" x="14" y="3" rx="1" />
            <rect width="7" height="9" x="14" y="12" rx="1" />
            <rect width="7" height="5" x="3" y="16" rx="1" />
            @break

        @case('gate')
            <path d="M13 4h3a2 2 0 0 1 2 2v14" />
            <path d="M2 20h3" />
            <path d="M13 20h9" />
            <path d="M10 12v.01" />
            <path d="M13 4.562v16.157a1 1 0 0 1-1.242.97L5 20V5.562a2 2 0 0 1 1.515-1.94l4-1A2 2 0 0 1 13 4.562Z" />
            @break

        @case('parking')
            <rect width="18" height="18" x="3" y="3" rx="2" />
            <path d="M9 17V7h4a3 3 0 0 1 0 6H9" />
            @break

        @case('devices')
            <rect width="16" height="16" x="4" y="4" rx="2" />
            <rect width="6" height="6" x="9" y="9" rx="1" />
            <path d="M15 2v2" />
            <path d="M15 20v2" />
            <path d="M2 15h2" />
            <path d="M2 9h2" />
            <path d="M20 15h2" />
            <path d="M20 9h2" />
            <path d="M9 2v2" />
            <path d="M9 20v2" />
            @break

        @case('analytics')
            <path d="M3 3v16a2 2 0 0 0 2 2h16" />
            <path d="m19 9-5 5-4-4-3 3" />
            @break

        @case('activity')
            <path d="M15 12h-5" />
            <path d="M15 8h-5" />
            <path d="M19 17V5a2 2 0 0 0-2-2H4" />
            <path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3" />
            @break

        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
            @break

        @case('calendar')
            <path d="M8 2v4" />
            <path d="M16 2v4" />
            <rect width="18" height="18" x="3" y="4" rx="2" />
            <path d="M3 10h18" />
            @break

        @case('download')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" x2="12" y1="15" y2="3" />
            @break

        @case('grid')
            <rect width="7" height="7" x="3" y="3" rx="1" />
            <rect width="7" height="7" x="14" y="3" rx="1" />
            <rect width="7" height="7" x="14" y="14" rx="1" />
            <rect width="7" height="7" x="3" y="14" rx="1" />
            @break

        @case('gauge')
            <path d="m12 14 4-4" />
            <path d="M3.34 19a10 10 0 1 1 17.32 0" />
            @break

        @case('entry')
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <polyline points="10 17 15 12 10 7" />
            <line x1="15" x2="3" y1="12" y2="12" />
            @break

        @case('router')
            <rect width="20" height="8" x="2" y="14" rx="2" />
            <path d="M6.01 18H6" />
            <path d="M10.01 18H10" />
            <path d="M15 10v4" />
            <path d="M17.84 7.17a4 4 0 0 0-5.66 0" />
            <path d="M20.66 4.34a8 8 0 0 0-11.31 0" />
            @break

        @case('trend-up')
            <path d="M7 7h10v10" />
            <path d="M7 17 17 7" />
            @break

        @case('arrow-right')
            <path d="M5 12h14" />
            <path d="m12 5 7 7-7 7" />
            @break

        @case('refresh')
            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
            <path d="M21 3v5h-5" />
            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
            <path d="M8 16H3v5" />
            @break

        @case('building')
            <rect width="16" height="20" x="4" y="2" rx="2" />
            <path d="M9 22v-4h6v4" />
            <path d="M8 6h.01" />
            <path d="M16 6h.01" />
            <path d="M12 6h.01" />
            <path d="M12 10h.01" />
            <path d="M12 14h.01" />
            <path d="M16 10h.01" />
            <path d="M16 14h.01" />
            <path d="M8 10h.01" />
            <path d="M8 14h.01" />
            @break

        @case('panel')
            <rect width="18" height="18" x="3" y="3" rx="2" />
            <path d="M9 3v18" />
            @break

        @case('car')
            <rect width="11" height="19" x="6.5" y="2.5" rx="3.5" />
            <path d="M6.5 8.5h11" />
            <path d="M6.5 15.5h11" />
            @break

        @case('more')
            <circle cx="12" cy="12" r="1" />
            <circle cx="19" cy="12" r="1" />
            <circle cx="5" cy="12" r="1" />
            @break

        @case('camera')
            <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z" />
            <circle cx="12" cy="13" r="3" />
            @break

        @case('user')
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
            @break

        @case('pin')
            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
            <circle cx="12" cy="10" r="3" />
            @break

        @case('timer')
            <line x1="10" x2="14" y1="2" y2="2" />
            <line x1="12" x2="15" y1="14" y2="11" />
            <circle cx="12" cy="14" r="8" />
            @break

        @case('arrow-left')
            <path d="m12 19-7-7 7-7" />
            <path d="M19 12H5" />
            @break

        @case('external-link')
            <path d="M15 3h6v6" />
            <path d="M10 14 21 3" />
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
            @break
    @endswitch
</svg>

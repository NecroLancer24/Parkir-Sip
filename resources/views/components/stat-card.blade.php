@props(['label', 'icon'])

<div
    {{ $attributes->merge(['class' => 'flex min-h-[115px] flex-col justify-between rounded-xl border border-line-soft bg-white p-4 shadow-card']) }}
>
    <div class="flex items-start justify-between gap-3">
        <p class="text-[12.5px] text-ink-soft">{{ $label }}</p>
        <x-icon :name="$icon" class="size-4 shrink-0 text-brand" />
    </div>

    <div class="mt-2">
        <div class="flex items-baseline gap-1.5">
            {{ $value }}
        </div>

        <p class="mt-1.5 text-[11.5px] leading-4 text-ink-muted">{{ $hint }}</p>
    </div>
</div>

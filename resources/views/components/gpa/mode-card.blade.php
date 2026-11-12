@props([
    'name' => 'mode',
    'value',
    'checked' => false,
    'title',
    'description',
    'sla',
    'cta' => null,
    'badge' => null,
    'model' => null,
])

<label class="block h-full cursor-pointer">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" @checked($checked)
        @if ($model) x-model="{{ $model }}" @endif class="peer sr-only" />

    <span
        class="flex h-full flex-col justify-between gap-3 rounded-lg bg-surface-muted p-3.5 text-ink-muted shadow-card transition-all duration-100 hover:bg-surface-raised/60 peer-checked:bg-brand-soft peer-checked:text-ink peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand peer-checked:[&_.gpa-mode-marker]:border-brand peer-checked:[&_.gpa-mode-marker]:bg-brand peer-checked:[&_.gpa-mode-marker]:text-white">
        <span class="block">
            <span class="mb-1.5 flex flex-wrap items-center justify-between gap-1.5">
                <span class="flex items-center gap-1.5 gpa-mono-xs font-semibold uppercase tracking-wider">
                    <span
                        class="gpa-mode-marker inline-flex h-3.5 w-3.5 items-center justify-center rounded-sm border border-line-strong bg-surface text-[9px] leading-none text-transparent">
                        &bull;
                    </span>
                    {{ $title }}
                </span>
                @isset($badge)
                    {{ $badge }}
                @endisset
            </span>
            <span class="block text-xs leading-relaxed opacity-90">{{ $description }}</span>
        </span>

        <span
            class="flex items-center justify-between gap-2 pt-1 gpa-mono-xs uppercase tracking-wider opacity-75">
            <span>{{ $sla }}</span>
            @if ($cta)
                <span class="flex items-center gap-1 font-semibold">{{ $cta }} <span aria-hidden="true">&rarr;</span></span>
            @endif
        </span>
    </span>
</label>

@props([
    'id' => null,
    'step' => null,
    'title',
    'description' => null,
])

<section @if ($id) aria-labelledby="{{ $id }}-heading" @endif
    {{ $attributes->merge(['class' => 'gpa-panel overflow-hidden']) }}>
    <div class="flex flex-wrap items-center justify-between gap-2 bg-surface-muted px-4 py-2.5 md:px-5">
        <div class="flex min-w-0 items-center gap-2.5">
            @if ($step)
                <span class="inline-flex h-5 shrink-0 items-center rounded-sm bg-brand px-1.5 gpa-mono-xs font-bold text-white">{{ $step }}</span>
            @endif
            <h2 @if ($id) id="{{ $id }}-heading" @endif
                class="gpa-label uppercase tracking-wider text-ink">{{ $title }}</h2>
            @if ($description)
                <span class="hidden truncate gpa-mono-xs text-ink-subtle lg:inline">{{ $description }}</span>
            @endif
        </div>
        <div class="flex shrink-0 items-center gap-2">
            {{ $meta ?? '' }}
        </div>
    </div>
    <div class="space-y-4 p-4 md:p-5">
        {{ $slot }}
    </div>
</section>

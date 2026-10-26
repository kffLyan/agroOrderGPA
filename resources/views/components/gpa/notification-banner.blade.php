@props(['title', 'description' => null, 'time' => null, 'type' => null, 'tone' => null])

@php
    $variant = $type ?? $tone;
    $borderClass = match ($variant) {
        'success' => 'border-l-success',
        'danger', 'error' => 'border-l-danger',
        'warning' => 'border-l-amber-500',
        'info' => 'border-l-sky-500',
        default => 'border-l-accent-deep',
    };
@endphp

<div role="status" {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border border-l-4 border-line-hair ' . $borderClass . ' bg-white px-3.5 py-2.5 shadow-md transition-all duration-200']) }}>
    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-accent/30 mt-0.5">
        <x-gpa.logo-mark class="h-4 w-auto" />
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex items-baseline justify-between gap-2">
            <p class="text-xs font-bold text-ink leading-tight sm:text-sm">{{ $title }}</p>
            @if ($time)
                <time class="shrink-0 font-mono text-[10px] text-ink-body/60">{{ $time }}</time>
            @endif
        </div>
        @if ($description)
            <p class="mt-0.5 text-xs text-ink-body leading-relaxed">{{ $description }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="shrink-0 self-start mt-0.5">{{ $slot }}</div>
    @endif
</div>

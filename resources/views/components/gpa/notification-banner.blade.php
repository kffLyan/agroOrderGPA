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

<div role="status" {{ $attributes->merge(['class' => 'flex items-center sm:items-start gap-2.5 sm:gap-3 rounded-xl border border-l-[3px] sm:border-l-4 border-line-hair ' . $borderClass . ' bg-white/95 backdrop-blur-md px-3 py-2 sm:px-3.5 sm:py-2.5 shadow-md sm:shadow-lg transition-all duration-200']) }}>
    <div class="flex h-6 w-6 sm:h-7 sm:w-7 shrink-0 items-center justify-center rounded-lg bg-accent/30 sm:mt-0.5">
        <x-gpa.logo-mark class="h-3.5 sm:h-4 w-auto" />
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex items-baseline justify-between gap-1.5">
            <p class="text-xs font-bold text-ink leading-tight truncate sm:text-sm">{{ $title }}</p>
            @if ($time)
                <time class="hidden sm:inline shrink-0 font-mono text-[10px] text-ink-body/60">{{ $time }}</time>
            @endif
        </div>
        @if ($description)
            <p class="mt-0.5 text-[11px] sm:text-xs text-ink-body leading-snug sm:leading-relaxed line-clamp-2">{{ $description }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="shrink-0 self-center sm:self-start">{{ $slot }}</div>
    @endif
</div>

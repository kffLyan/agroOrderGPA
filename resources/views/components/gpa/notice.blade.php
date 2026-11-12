@props([
    'tone' => 'neutral',
    'title' => null,
    'icon' => 'info',
    'class' => '',
])

@php
    $tones = [
        'neutral' => ['wrap' => 'bg-surface-muted', 'mark' => 'border-line-strong bg-surface text-ink-muted', 'body' => 'text-ink-muted'],
        'ink' => ['wrap' => 'bg-brand-soft', 'mark' => 'border-brand bg-brand text-white', 'body' => 'text-ink'],
        'success' => ['wrap' => 'bg-success-soft', 'mark' => 'border-success bg-surface text-success', 'body' => 'text-ink'],
        'danger' => ['wrap' => 'bg-danger-soft', 'mark' => 'border-danger bg-surface text-danger', 'body' => 'text-ink'],
    ];

    $palette = $tones[$tone] ?? $tones['neutral'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg p-3.5 shadow-card md:p-4 '.$palette['wrap'].' '.$class]) }}>
    <span
        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-sm border {{ $palette['mark'] }}">
        <x-gpa.icon :name="$icon" class="h-4 w-4" />
    </span>

    <div class="min-w-0 flex-1 space-y-1">
        @if ($title)
            <div class="flex flex-wrap items-center gap-2">
                <p class="gpa-label uppercase tracking-wide text-ink">{{ $title }}</p>
                {{ $meta ?? '' }}
            </div>
        @endif
        <div class="text-xs leading-relaxed {{ $palette['body'] }}">{{ $slot }}</div>
    </div>
</div>

@props([
    'tone' => 'neutral',
    'dot' => false,
    'class' => '',
])

@php
    $tones = [
        'neutral' => 'border-line bg-surface-muted text-ink-muted',
        'ink' => 'border-brand bg-brand text-white',
        'success' => 'border-success bg-success-soft text-success',
        'danger' => 'border-danger bg-danger-soft text-danger',
    ];

    $dots = [
        'neutral' => 'bg-ink-subtle',
        'ink' => 'bg-white',
        'success' => 'bg-success',
        'danger' => 'bg-danger',
    ];

    $toneClass = $tones[$tone] ?? $tones['neutral'];
    $dotClass = $dots[$tone] ?? $dots['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-sm border px-1.5 py-0.5 gpa-mono-xs font-semibold uppercase tracking-wider '.$toneClass.' '.$class]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>

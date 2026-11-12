@props([
    'status',
])

@php
    $solid = ($status['tone'] ?? null) === 'solid';
@endphp

<span @class([
    'inline-flex w-fit items-center gap-1.5 rounded px-2 py-0.5 gpa-micro-bold whitespace-nowrap',
    'bg-brand-strong text-accent' => $solid,
    'bg-canvas text-success outline outline-1 outline-line-hair outline-offset-[-1px]' => ! $solid,
])>
    <span @class([
        'h-1.5 w-1.5 shrink-0 rounded-full',
        'bg-accent' => $solid,
        'bg-success' => ! $solid,
    ]) aria-hidden="true"></span>
    {{ $status['label'] }}
</span>
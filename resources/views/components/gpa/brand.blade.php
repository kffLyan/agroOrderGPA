@props([
    'compact' => false,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-2.5']) }}>
    <span class="flex h-8 w-8 shrink-0 items-center justify-center {{ $compact ? 'h-7 w-7' : '' }}">
        <img src="{{ asset('gpaleaves.png') }}" alt="" class="h-full w-full object-contain">
    </span>
    <span class="flex min-w-0 flex-col leading-none">
        <span class="truncate text-sm font-bold uppercase tracking-tight text-ink">AgroOrder <span
                class="text-ink-muted">GPA</span></span>
        <span class="gpa-eyebrow mt-1 truncate">Portal Distribusi Komoditas</span>
    </span>
</{{ $tag }}>

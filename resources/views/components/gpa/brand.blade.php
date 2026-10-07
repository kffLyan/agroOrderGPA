@props([
    'compact' => false,
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'flex min-w-0 items-center gap-2.5']) }}>
    <span
        class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-brand text-white {{ $compact ? 'h-7 w-7' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
            stroke-linejoin="round" class="h-[18px] w-[18px]" aria-hidden="true">
            <path d="M5 19C5 10.4 11 5 19 5c0 8-5.2 14-14 14Z" />
            <path d="M5 19c2.8-5.2 6.2-8.6 10.5-10.5" />
        </svg>
    </span>
    <span class="flex min-w-0 flex-col leading-none">
        <span class="truncate text-sm font-bold uppercase tracking-tight text-ink">AgroOrder <span
                class="text-ink-muted">GPA</span></span>
        <span class="gpa-eyebrow mt-1 truncate">Portal Distribusi Komoditas</span>
    </span>
</{{ $tag }}>

@props([
    'variant' => 'secondary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'block' => false,
])

@php
    $variants = [
        'primary' => 'border-ink bg-ink text-white hover:border-brand-deep hover:bg-brand-deep active:translate-y-px',
        'accent' => 'border-accent bg-accent text-ink hover:border-accent-deep hover:bg-accent-deep',
        'secondary' => 'border-line-soft bg-surface-pill text-ink-body hover:border-line-board hover:bg-surface-disabled hover:text-ink',
        'dashed' => 'border-dashed border-line-board bg-surface text-ink-body hover:border-ink hover:bg-surface-pill hover:text-ink',
        'ghost' => 'border-transparent bg-transparent text-ink-body hover:bg-surface-pill hover:text-ink',
        'inverse' => 'border-white/30 bg-brand text-white hover:border-white/50 hover:bg-brand-hover',
        'danger' => 'border-danger bg-danger text-white hover:border-danger-ink hover:bg-danger-ink',
    ];

    $sizes = [
        'sm' => 'h-7 gap-1.5 px-2 text-[11px] leading-none',
        'md' => 'h-8 gap-2 px-3 text-xs leading-none',
        'lg' => 'h-9 gap-2 px-4 text-sm leading-none',
    ];

    $variantClass = $variants[$variant] ?? $variants['secondary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $base = 'inline-flex select-none items-center justify-center rounded font-mono font-semibold uppercase tracking-[0.108em] transition-all duration-100 disabled:cursor-not-allowed disabled:opacity-55 disabled:active:translate-y-0';
    $classes = trim($base.' '.$variantClass.' '.$sizeClass.($block ? ' w-full' : ''));
@endphp

@if ($href && ! $disabled && ! $loading)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <x-gpa.spinner />
        @endif
        @isset($icon)
            <x-gpa.icon :name="$icon" class="h-3.5 w-3.5" />
        @endisset
        {{ $slot }}
        @isset($trailing)
            <x-gpa.icon :name="$trailing" class="h-3.5 w-3.5" />
        @endisset
    </a>
@else
    <button type="{{ $type }}" @if ($loading) aria-busy="true" @endif @disabled($disabled || $loading)
        {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <x-gpa.spinner />
        @else
            @isset($icon)
                <x-gpa.icon :name="$icon" class="h-3.5 w-3.5" />
            @endisset
        @endif
        {{ $slot }}
        @if (! $loading)
            @isset($trailing)
                <x-gpa.icon :name="$trailing" class="h-3.5 w-3.5" />
            @endisset
        @endif
    </button>
@endif

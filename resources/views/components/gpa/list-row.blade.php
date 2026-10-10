@props([
    'href' => null,
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'badge' => null,
    'meta' => [],
    'trailing' => null,
    'divider' => true,
    'class' => '',
])

@php
    $tags = $href ? 'a' : 'div';
    $isInteractive = (bool) $href;
@endphp

<{{ $tags }} @if ($href) href="{{ $href }}" @endif
    @if ($isInteractive)
        class="gpa-row-link group flex items-start gap-3 px-3 py-2.5 transition-colors hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand {{ $class }}"
    @else
        class="flex items-start gap-3 px-3 py-2.5 {{ $class }}"
    @endif
    {{ $attributes->except('href', 'class') }}>

    @if ($icon)
        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-sm border border-line bg-surface-muted text-ink-subtle">
            <x-gpa.icon :name="$icon" class="h-3.5 w-3.5" />
        </span>
    @endif

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            @if ($title)
                <p class="truncate text-xs font-semibold text-ink">{{ $title }}</p>
            @endif

            @if (is_array($badge) || $badge instanceof \Illuminate\Contracts\Support\Htmlable)
                @php
                    $badgeLabel = is_array($badge) ? ($badge['label'] ?? '') : $badge;
                    $badgeTone = is_array($badge) ? ($badge['tone'] ?? 'neutral') : 'neutral';
                @endphp
                <x-gpa.badge :tone="$badgeTone">{{ $badgeLabel }}</x-gpa.badge>
            @endif
        </div>

        @if ($subtitle)
            <p class="mt-0.5 truncate text-2xs leading-relaxed text-ink-subtle">{{ $subtitle }}</p>
        @endif

        @if (count($meta) > 0)
            <dl class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 lg:hidden">
                @foreach ($meta as $item)
                    <div class="flex items-baseline gap-1.5">
                        <dt class="gpa-eyebrow">{{ $item['label'] }}</dt>
                        <dd class="gpa-mono-xs font-semibold text-ink">{{ $item['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        {{ $slot }}
    </div>

    <div class="flex shrink-0 items-center gap-2">
        @isset($trailing)
            <div class="flex items-center gap-2">
                {{ $trailing }}
            </div>
        @endisset

        @if ($isInteractive)
            <x-gpa.icon name="chevron-right" class="h-3.5 w-3.5 text-ink-subtle transition-transform group-hover:translate-x-0.5" />
        @endif
    </div>
</{{ $tags }}>

@if ($divider)
    <div class="gpa-hairline"></div>
@endif
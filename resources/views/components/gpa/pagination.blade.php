@props([
    'current' => 1,
    'last' => 1,
    'total' => 0,
    'perPage' => 10,
    'unit' => 'data',
    'urlPattern' => null,
    'label' => 'Navigasi halaman',
    'class' => '',
])

@php
    $current = max(1, (int) $current);
    $last = max(1, (int) $last);
    $total = max(0, (int) $total);
    $perPage = max(1, (int) $perPage);

    if ($current > $last) {
        $current = $last;
    }

    $from = $total > 0 ? (($current - 1) * $perPage) + 1 : 0;
    $to = $total > 0 ? min($total, $current * $perPage) : 0;

    $window = [];

    if ($last <= 7) {
        $window = range(1, $last);
    } else {
        $window[] = 1;

        $start = max(2, $current - 1);
        $end = min($last - 1, $current + 1);

        if ($start > 2) {
            $window[] = 'gap-start';
        }

        for ($page = $start; $page <= $end; $page++) {
            $window[] = $page;
        }

        if ($end < $last - 1) {
            $window[] = 'gap-end';
        }

        $window[] = $last;
    }

    $pageUrl = function (int $page) use ($urlPattern): ?string {
        if (! $urlPattern) {
            return null;
        }

        return str_replace(':page', (string) $page, $urlPattern);
    };

    $linkBase = 'inline-flex h-8 min-w-8 items-center justify-center gap-1 rounded border px-2 font-mono text-xs font-semibold transition-colors';
@endphp

@if ($last > 1)
    <nav role="navigation" aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-3 '.$class]) }}>
        <p class="gpa-mono-xs text-ink-subtle">
            Menampilkan {{ $from }}&ndash;{{ $to }} dari {{ number_format($total, 0, ',', '.') }} {{ $unit }}
        </p>

        <div class="flex flex-wrap items-center gap-1">
            @php
                $previousUrl = $current > 1 ? $pageUrl($current - 1) : null;
                $nextUrl = $current < $last ? $pageUrl($current + 1) : null;
            @endphp

            @if ($previousUrl)
                <a href="{{ $previousUrl }}" rel="prev"
                    class="{{ $linkBase }} border-line-soft bg-surface-pill text-ink-body hover:border-line-board hover:text-ink">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @else
                <span aria-hidden="true"
                    class="{{ $linkBase }} cursor-not-allowed border-line-faint bg-surface-muted text-ink-subtle opacity-55">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @endif

            @foreach ($window as $item)
                @if (is_string($item))
                    <span aria-hidden="true" class="inline-flex h-8 min-w-6 items-center justify-center px-1 font-mono text-xs text-ink-subtle">&hellip;</span>
                @else
                    @php($itemUrl = $pageUrl($item))

                    @if ($item === $current)
                        <span aria-current="page"
                            class="{{ $linkBase }} border-ink bg-ink text-white">{{ $item }}</span>
                    @elseif ($itemUrl)
                        <a href="{{ $itemUrl }}"
                            class="{{ $linkBase }} border-line-soft bg-surface text-ink-body hover:border-line-board hover:bg-surface-pill hover:text-ink">{{ $item }}</a>
                    @else
                        <span
                            class="{{ $linkBase }} border-line-soft bg-surface text-ink-body">{{ $item }}</span>
                    @endif
                @endif
            @endforeach

            @if ($nextUrl)
                <a href="{{ $nextUrl }}" rel="next"
                    class="{{ $linkBase }} border-line-soft bg-surface-pill text-ink-body hover:border-line-board hover:text-ink">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            @else
                <span aria-hidden="true"
                    class="{{ $linkBase }} cursor-not-allowed border-line-faint bg-surface-muted text-ink-subtle opacity-55">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5" />
                </span>
            @endif
        </div>
    </nav>
@endif
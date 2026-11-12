@props([
    'tabs' => [],
    'active' => null,
    'name' => 'gpa-tabs',
    'variant' => 'pill',
    'label' => 'Navigasi tab',
    'class' => '',
])

@php
    if (empty($tabs)) {
        $tabs = [];
    }

    $ids = array_map(fn ($tab) => (string) ($tab['id'] ?? ''), $tabs);
    $active = (string) ($active ?? $ids[0] ?? '');

    if (! in_array($active, $ids, true)) {
        $active = $ids[0] ?? '';
    }

    $pillActive = 'border-ink bg-ink text-white';
    $pillIdle = 'border-line-soft bg-surface text-ink-body hover:border-line-board hover:bg-surface-pill hover:text-ink';
    $lineActive = 'border-brand text-ink';
    $lineIdle = 'border-transparent text-ink-subtle hover:text-ink';
@endphp

@if (empty($tabs))
@else
<div x-data="{ active: @js($active), order: @js($ids), move(step) { const i = this.order.indexOf(this.active); const next = this.order[(i + step + this.order.length) % this.order.length]; this.active = next; this.$nextTick(() => this.$root.querySelectorAll('[role=tab]')[this.order.indexOf(next)]?.focus()); } }"
    {{ $attributes->merge(['class' => 'space-y-3 '.$class]) }}>
    <div role="tablist" aria-label="{{ $label }}"
        @class([
            'flex flex-wrap items-center gap-1.5',
            'border-b border-line pb-2' => $variant === 'underline',
            'rounded-lg bg-surface-muted p-1' => $variant !== 'underline',
        ])
        @keydown.left.prevent="move(-1)" @keydown.right.prevent="move(1)" @keydown.up.prevent="move(-1)"
        @keydown.down.prevent="move(1)">
        @foreach ($tabs as $tab)
            @php
                $id = (string) ($tab['id'] ?? '');
                $isPill = $variant !== 'underline';
            @endphp

            <button type="button" role="tab" id="{{ $name }}-tab-{{ $id }}" aria-controls="{{ $name }}-panel-{{ $id }}"
                :aria-selected="active === @js($id) ? 'true' : 'false'" :tabindex="active === @js($id) ? '0' : '-1'"
                x-on:click="active = @js($id)"
                @class([
                    'inline-flex items-center gap-1.5 whitespace-nowrap font-mono text-xs font-semibold uppercase tracking-[0.108em] transition-colors',
                    'h-7 rounded px-2.5' => $isPill,
                    '-mb-2 border-b-2 px-2.5 pb-1.5' => ! $isPill,
                    $isPill ? ($active === $id ? $pillActive : $pillIdle) : ($active === $id ? $lineActive : $lineIdle),
                ])>
                <span>{{ $tab['label'] ?? $id }}</span>

                @if (array_key_exists('count', $tab) && $tab['count'] !== null)
                    <span
                        class="{{ $isPill ? ($active === $id ? 'bg-white/20 text-white' : 'bg-surface-disabled text-ink-subtle') : ($active === $id ? 'bg-brand-soft text-ink' : 'bg-surface-muted text-ink-subtle') }} inline-flex h-4 min-w-4 items-center justify-center rounded-sm px-1 text-[0.625rem]">
                        {{ $tab['count'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    @foreach ($tabs as $tab)
        @php
            $id = (string) ($tab['id'] ?? '');
            $panel = $tab['panel'] ?? null;

            if ($panel instanceof \Closure) {
                $panel = $panel();
            }
        @endphp

        <div role="tabpanel" id="{{ $name }}-panel-{{ $id }}" aria-labelledby="{{ $name }}-tab-{{ $id }}"
            tabindex="0" x-show="active === @js($id)" x-cloak>
            @if ($panel instanceof \Illuminate\Contracts\Support\Htmlable)
                {!! $panel !!}
            @elseif ($panel !== null)
                {!! $panel !!}
            @else
                {{ $slot }}
            @endif
        </div>
    @endforeach
</div>
@endif
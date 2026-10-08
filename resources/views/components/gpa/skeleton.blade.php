@props([
    'variant' => 'lines',
    'rows' => 3,
    'lines' => 3,
    'animated' => true,
    'class' => '',
])

@php
    $pulse = $animated ? 'animate-pulse' : '';
    $widths = [100, 92, 76];
@endphp

<div {{ $attributes->merge(['class' => 'space-y-3 '.$class]) }} aria-hidden="true">
    @switch($variant)
        @case('table')
            <div class="overflow-hidden rounded-lg border border-line">
                <div class="{{ $pulse }} h-9 bg-surface-raised"></div>
                @for ($i = 0; $i < $rows; $i++)
                    <div class="flex items-center gap-3 border-t border-line-faint px-3 py-3">
                        <div class="{{ $pulse }} h-2.5 w-1/4 rounded-sm bg-surface-disabled"></div>
                        <div class="{{ $pulse }} h-2.5 flex-1 rounded-sm bg-surface-disabled"></div>
                        <div class="{{ $pulse }} h-2.5 w-16 rounded-sm bg-surface-disabled"></div>
                    </div>
                @endfor
            </div>
            @break

        @case('card')
            @for ($i = 0; $i < $rows; $i++)
                <div class="gpa-panel p-4">
                    <div class="{{ $pulse }} mb-3 h-3 w-1/3 rounded-sm bg-surface-disabled"></div>
                    <div class="{{ $pulse }} mb-2 h-2.5 w-full rounded-sm bg-surface-disabled"></div>
                    <div class="{{ $pulse }} h-2.5 w-4/5 rounded-sm bg-surface-disabled"></div>
                </div>
            @endfor
            @break

        @case('block')
            <div class="{{ $pulse }} h-24 w-full rounded-lg bg-surface-disabled"></div>
            @break

        @default
            @for ($i = 0; $i < $lines; $i++)
                <div class="{{ $pulse }} h-2.5 rounded-sm bg-surface-disabled" style="width: {{ $widths[$i % 3] }}%"></div>
            @endfor
    @endswitch
</div>
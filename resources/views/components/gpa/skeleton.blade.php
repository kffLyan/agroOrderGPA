@props([
    'variant' => 'lines',
    'rows' => 3,
    'lines' => 3,
    'animated' => true,
    'shimmer' => true,
    'class' => '',
])

@php
    $animClass = $shimmer ? 'gpa-shimmer' : ($animated ? 'animate-pulse' : '');
    $widths = [100, 92, 76];
@endphp

<div {{ $attributes->merge(['class' => 'space-y-3 '.$class]) }} aria-hidden="true">
    @switch($variant)
        @case('table')
            <div class="overflow-hidden rounded-xl border border-line bg-surface shadow-sub">
                <div class="{{ $animClass }} h-11 bg-surface-raised border-b border-line-hair"></div>
                @for ($i = 0; $i < $rows; $i++)
                    <div class="flex items-center gap-3 border-t border-line-faint px-4 py-3.5">
                        <div class="{{ $animClass }} h-3 w-1/4 rounded-md bg-surface-disabled"></div>
                        <div class="{{ $animClass }} h-3 flex-1 rounded-md bg-surface-disabled"></div>
                        <div class="{{ $animClass }} h-3 w-20 rounded-md bg-surface-disabled"></div>
                    </div>
                @endfor
            </div>
            @break

        @case('card')
            @for ($i = 0; $i < $rows; $i++)
                <div class="gpa-panel p-5 rounded-xl border border-line-hair shadow-sub">
                    <div class="{{ $animClass }} mb-3 h-3.5 w-1/3 rounded-md bg-surface-disabled"></div>
                    <div class="{{ $animClass }} mb-2 h-3 w-full rounded-md bg-surface-disabled"></div>
                    <div class="{{ $animClass }} h-3 w-4/5 rounded-md bg-surface-disabled"></div>
                </div>
            @endfor
            @break

        @case('stats')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @for ($i = 0; $i < $rows; $i++)
                    <div class="rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
                        <div class="flex items-center justify-between">
                            <div class="{{ $animClass }} h-3 w-20 rounded-md bg-surface-disabled"></div>
                            <div class="{{ $animClass }} h-7 w-7 rounded-lg bg-surface-disabled"></div>
                        </div>
                        <div class="{{ $animClass }} mt-3 h-7 w-32 rounded-md bg-surface-disabled"></div>
                        <div class="{{ $animClass }} mt-2 h-2.5 w-24 rounded-md bg-surface-disabled"></div>
                    </div>
                @endfor
            </div>
            @break

        @case('page')
            <div class="space-y-5">
                <div class="flex flex-col gap-2">
                    <div class="{{ $animClass }} h-3.5 w-32 rounded-md bg-surface-disabled"></div>
                    <div class="{{ $animClass }} h-8 w-64 rounded-lg bg-surface-disabled"></div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
                            <div class="flex items-center justify-between">
                                <div class="{{ $animClass }} h-3 w-20 rounded-md bg-surface-disabled"></div>
                                <div class="{{ $animClass }} h-7 w-7 rounded-lg bg-surface-disabled"></div>
                            </div>
                            <div class="{{ $animClass }} mt-3 h-7 w-32 rounded-md bg-surface-disabled"></div>
                            <div class="{{ $animClass }} mt-2 h-2.5 w-24 rounded-md bg-surface-disabled"></div>
                        </div>
                    @endfor
                </div>
                <div class="rounded-xl border border-line-hair bg-surface p-5 shadow-card space-y-4">
                    <div class="flex items-center justify-between border-b border-line-hair pb-4">
                        <div class="{{ $animClass }} h-4 w-44 rounded-md bg-surface-disabled"></div>
                        <div class="flex gap-2">
                            <div class="{{ $animClass }} h-8 w-24 rounded-lg bg-surface-disabled"></div>
                            <div class="{{ $animClass }} h-8 w-28 rounded-lg bg-surface-disabled"></div>
                        </div>
                    </div>
                    @for ($i = 0; $i < 5; $i++)
                        <div class="{{ $animClass }} h-9 w-full rounded-md bg-surface-disabled"></div>
                    @endfor
                </div>
            </div>
            @break

        @case('block')
            <div class="{{ $animClass }} h-24 w-full rounded-xl bg-surface-disabled"></div>
            @break

        @default
            @for ($i = 0; $i < $lines; $i++)
                <div class="{{ $animClass }} h-2.5 rounded-md bg-surface-disabled" style="width: {{ $widths[$i % 3] }}%"></div>
            @endfor
    @endswitch
</div>
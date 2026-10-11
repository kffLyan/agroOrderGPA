@props([
    'validation',
])

@php
    $key = $validation['commodityKey'];
    $under = "quantity('{$key}') < find('{$key}').moq";
@endphp

<section class="rounded-xl border border-accent-deep/50 bg-surface p-4 shadow-sub sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
        <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
            {{ $validation['title'] }}
        </h2>
    </div>

    <div class="mt-3 space-y-1">
        <p class="gpa-mono-xs font-semibold uppercase tracking-wider text-ink-subtle">{{ $validation['intro'] }}</p>
        <p class="gpa-mono-xs font-semibold text-brand-strong">{{ $validation['commodity'] }}</p>
    </div>

    <p class="mt-3 flex items-baseline justify-between gap-3 rounded-lg border border-line-hair bg-canvas px-3 py-2">
        <span class="gpa-mono-xs text-ink-body">{{ $validation['ruleLabel'] }}</span>
        <span class="gpa-mono-xs font-extrabold text-brand-strong">{{ $validation['ruleValue'] }}</span>
    </p>

    <div class="mt-4">
        <p class="gpa-micro tracking-wider text-accent-deep">{{ $validation['logisticsTitle'] }}</p>
        <dl class="mt-2 divide-y divide-line-hair rounded-lg border border-line-hair bg-canvas px-3">
            @foreach ($validation['logistics'] as $row)
                <div class="flex items-baseline justify-between gap-3 py-2">
                    <dt class="gpa-mono-xs text-ink-subtle">{{ $row['label'] }}</dt>
                    <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <x-gpa.btn variant="secondary" block class="mt-4" @click="exportSpec()">
        <x-slot:icon>
            <x-gpa.icon name="download" />
        </x-slot:icon>
        {{ $validation['download'] }}
    </x-gpa.btn>
</section>

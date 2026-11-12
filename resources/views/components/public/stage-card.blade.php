@props([
    'stage',
])

@php
    $critical = (bool) $stage['critical'];
@endphp

<article @class([
    'flex flex-col gap-4 rounded-xl border p-5',
    'border-accent bg-brand-deep text-white shadow-pop' => $critical,
    'border-line-hair bg-surface' => ! $critical,
])>
    <div @class([
        'flex flex-wrap items-center justify-between gap-2 border-b pb-3',
        'border-white/10' => $critical,
        'border-line-hair' => ! $critical,
    ])>
        <span @class([
            'flex h-7 w-7 items-center justify-center rounded-lg gpa-mono-xs font-bold uppercase',
            'bg-accent text-brand-deep' => $critical,
            'bg-brand-strong text-accent' => ! $critical,
        ])>{{ $stage['stage'] }}</span>

        <p @class([
            'gpa-mono-xs font-semibold uppercase tracking-wide',
            'text-accent' => $critical,
            'text-success' => ! $critical,
        ])>{{ $stage['rule'] }}</p>
    </div>

    @if ($critical)
        <p
            class="inline-flex w-fit items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-brand-deep">
            <x-gpa.icon name="alert-triangle" class="h-3 w-3" />
            Critical Checkpoint
        </p>
    @endif

    <h3 @class([
        'text-base font-bold leading-snug',
        'text-white' => $critical,
        'text-brand-strong' => ! $critical,
    ])>{{ $stage['title'] }}</h3>

    <p @class([
        'text-xs leading-relaxed',
        'text-white/85' => $critical,
        'text-ink-body' => ! $critical,
    ])>{{ $stage['body'] }}</p>

    <div @class([
        'rounded-lg border px-3 py-2.5',
        'border-accent/30 bg-brand/85' => $critical,
        'border-line-hair bg-canvas' => ! $critical,
    ])>
        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">{{ $stage['token'] }}</p>
        <p @class([
            'mt-1 gpa-mono-xs',
            'text-white/75' => $critical,
            'text-ink-body' => ! $critical,
        ])>{{ $stage['caption'] }}</p>
    </div>

    <dl @class([
        'mt-auto space-y-1.5 border-t pt-3',
        'border-white/10' => $critical,
        'border-line-hair' => ! $critical,
    ])>
        @foreach ($stage['meta'] as $meta)
            <div class="flex items-baseline justify-between gap-3">
                <dt @class([
                    'gpa-mono-xs',
                    'text-white/60' => $critical,
                    'text-ink-subtle' => ! $critical,
                ])>{{ $meta['label'] }}</dt>

                <dd @class([
                    'gpa-mono-xs text-right font-semibold',
                    ($meta['tone'] ?? null) === 'success' && $critical => 'text-accent',
                    ($meta['tone'] ?? null) === 'success' && ! $critical => 'text-success',
                    ($meta['tone'] ?? null) !== 'success' && $critical => 'text-white',
                    ($meta['tone'] ?? null) !== 'success' && ! $critical => 'text-brand-strong',
                ])>{{ $meta['value'] }}</dd>
            </div>
        @endforeach
    </dl>
</article>
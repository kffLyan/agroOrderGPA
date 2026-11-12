@props([
    'blueprint',
])

<section aria-labelledby="blueprint-heading"
    class="mt-8 space-y-4 rounded-2xl border-2 border-accent/50 bg-brand-deep/90 p-5 shadow-pop md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 pb-3">
        <h2 id="blueprint-heading"
            class="flex items-center gap-2 gpa-mono-xs font-bold uppercase tracking-wider text-white/90">
            <span class="h-2.5 w-2.5 rounded-full bg-accent" aria-hidden="true"></span>
            {{ $blueprint['title'] }}
        </h2>

        <span
            class="rounded bg-brand px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-accent/30 gpa-mono-xs text-accent">
            {{ $blueprint['revision'] }}
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-3 rounded-xl border border-accent/25 bg-brand/85 px-3 py-3">
        <p class="gpa-mono-xs text-white/80">{{ $blueprint['input']['source'] }}</p>
        <span class="text-accent" aria-hidden="true">&rarr;</span>
        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">{{ $blueprint['input']['field'] }}</p>
    </div>

    <dl class="grid gap-3 sm:grid-cols-2">
        @foreach ($blueprint['metrics'] as $metric)
            <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-brand/85 px-3 py-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/15 text-accent">
                    <x-gpa.icon :name="$metric['icon']" class="h-4 w-4" />
                </span>

                <div class="min-w-0">
                    <dt class="gpa-micro text-white/60">{{ $metric['label'] }}</dt>
                    <dd class="mt-1 text-lg font-extrabold leading-tight text-white">{{ $metric['value'] }}</dd>
                </div>
            </div>
        @endforeach
    </dl>

    <dl class="grid gap-3 border-t border-white/10 pt-4 sm:grid-cols-3">
        @foreach ($blueprint['checks'] as $check)
            <div class="space-y-0.5">
                <dt class="gpa-micro text-white/55">{{ $check['label'] }}</dt>
                <dd class="gpa-mono-xs font-semibold text-white">{{ $check['value'] }}</dd>
            </div>
        @endforeach
    </dl>

    <div
        class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-accent/25 bg-brand px-3 py-2 gpa-mono-xs">
        <p class="text-white/70">{{ $blueprint['status']['label'] }}</p>
        <p class="font-bold text-accent">{{ $blueprint['status']['value'] }}</p>
    </div>
</section>
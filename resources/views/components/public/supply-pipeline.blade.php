@props([
    'pipeline',
])

<section aria-labelledby="pipeline-heading"
    class="mt-8 space-y-4 rounded-2xl border-2 border-accent/50 bg-brand-deep/85 p-5 shadow-pop backdrop-blur-[2px] md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 pb-4">
        <h2 id="pipeline-heading" class="flex items-center gap-2 gpa-mono-xs font-bold uppercase tracking-wider text-white/90">
            <span class="h-2.5 w-2.5 rounded-full bg-accent" aria-hidden="true"></span>
            Schematic // GPA Supply Pipeline
        </h2>

        <span
            class="rounded bg-brand px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-accent/30 gpa-mono-xs text-accent">
            {{ $pipeline['revision'] }}
        </span>
    </div>

    <ol class="space-y-3">
        @foreach ($pipeline['stages'] as $index => $stage)
            <li>
                <div
                    class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-accent/25 bg-brand/90 px-3 py-3">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded bg-accent gpa-mono-xs font-bold text-brand-deep">
                            {{ $stage['step'] }}
                        </span>
                        <div class="min-w-0">
                            <p class="gpa-mono-xs font-bold uppercase text-white">{{ $stage['title'] }}</p>
                            <p class="gpa-mono-xs text-white/75">{{ $stage['location'] }}</p>
                        </div>
                    </div>

                    <p class="gpa-mono-xs font-semibold text-accent">{{ $stage['metric'] }}</p>
                </div>

                @if (! $loop->last)
                    <p class="flex h-3 items-center justify-center" aria-hidden="true">
                        <span class="block h-2 w-2 bg-accent/60"></span>
                    </p>
                @endif
            </li>
        @endforeach
    </ol>

    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-white/10 pt-3 gpa-mono-xs">
        <p class="text-white/70">{{ $pipeline['audit'] }}</p>
        <p class="font-bold text-accent">{{ $pipeline['claim'] }}</p>
    </div>
</section>
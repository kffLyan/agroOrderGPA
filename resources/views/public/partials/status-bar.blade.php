<div class="bg-brand-deep text-white">
    <div
        class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-1 px-4 py-2 gpa-mono-xs sm:px-6">
        <p class="flex min-w-0 flex-wrap items-center gap-x-2">
            <span class="inline-flex items-center gap-1.5 font-bold tracking-wide">
                <span class="h-2 w-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                {{ $statusBar['node'] }}
            </span>
            <span class="text-white/40" aria-hidden="true">•</span>
            <span class="truncate text-white/80">{{ $statusBar['site'] }}</span>
        </p>

        <dl class="flex flex-wrap items-center gap-x-4 gap-y-1">
            @foreach ($statusBar['metrics'] as $metric)
                <div class="flex items-center gap-1.5">
                    <dt class="text-white/80">{{ $metric['label'] }}</dt>
                    <dd class="text-accent">{{ $metric['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</div>
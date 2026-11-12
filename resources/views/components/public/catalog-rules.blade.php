@props([
    'rules',
])

<section class="rounded-xl border border-accent-deep/40 bg-surface p-5 shadow-sub">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-deep text-accent">
                <x-gpa.icon name="shield" class="h-5 w-5" />
            </span>
            <div>
                <h2 class="text-sm font-bold leading-snug text-ink">{{ $rules['title'] }}</h2>
                <p class="gpa-mono-xs text-ink-body">{{ $rules['subtitle'] }}</p>
            </div>
        </div>

        <span
            class="inline-flex items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold tracking-wider text-brand-deep">
            <x-gpa.icon name="badge-check" class="h-3 w-3" />
            {{ $rules['badge'] }}
        </span>
    </div>

    <p class="mt-3 text-xs leading-relaxed text-ink-body">{{ $rules['body'] }}</p>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($rules['actions'] as $action)
            <x-gpa.btn :href="route($action['href'])" :variant="$action['variant']">
                {{ $action['label'] }}
            </x-gpa.btn>
        @endforeach
    </div>
</section>

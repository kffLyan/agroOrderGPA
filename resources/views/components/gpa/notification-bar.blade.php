@props(['title', 'description' => null, 'time' => null])

<div role="status" {{ $attributes->merge(['class' => 'border-b border-line-hair bg-accent/30']) }}>
    <div class="mx-auto flex max-w-7xl items-center justify-center gap-3 px-4 py-2.5 text-sm">
        <x-gpa.logo-mark class="h-5 w-auto shrink-0" />
        @if ($time)
            <time class="shrink-0 font-mono text-[11px] text-ink-body/70">{{ $time }}</time>
        @endif
        <p class="min-w-0 text-ink">
            <span class="font-extrabold">{{ $title }}</span>
            @if ($description)
                <span class="text-ink-body">{{ $description }}</span>
            @endif
        </p>
        @if (! $slot->isEmpty())
            <div class="shrink-0 font-semibold text-brand underline-offset-4 hover:underline">{{ $slot }}</div>
        @endif
    </div>
</div>

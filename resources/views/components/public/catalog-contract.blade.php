@props([
    'contract',
])

<section class="flex flex-col gap-4 rounded-xl border border-brand-strong bg-brand p-5 text-white shadow-sub md:flex-row md:items-center md:justify-between md:p-6">
    <div class="flex gap-4">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent text-brand-deep">
            <x-gpa.icon name="users" class="h-5 w-5" />
        </span>

        <div class="max-w-3xl space-y-1.5">
            <h2 class="text-base font-bold leading-snug">{{ $contract['title'] }}</h2>
            <p class="text-xs leading-relaxed text-white/85">{{ $contract['body'] }}</p>
            <p class="gpa-mono-xs text-accent">{{ $contract['note'] }}</p>
        </div>
    </div>

    <x-gpa.btn :href="route($contract['cta']['href'])" variant="accent" size="lg" class="shrink-0">
        <x-slot:icon>
            <x-gpa.icon name="plus" />
        </x-slot:icon>
        {{ $contract['cta']['label'] }}
    </x-gpa.btn>
</section>

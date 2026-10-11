@props([
    'cart',
])

<section class="gpa-panel p-4 sm:p-5">
    <header class="flex items-center justify-between gap-3 border-b border-line-hair pb-3">
        <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
            {{ $cart['title'] }}
        </h2>
        <span x-show="draftCount() > 0" x-cloak
            class="rounded-full bg-accent px-2.5 py-1 gpa-micro-bold tracking-wider text-brand-deep">
            <span x-text="draftCount()">0</span> {{ $cart['itemsLabel'] }}
        </span>
    </header>

    <div x-show="draftCount() === 0" x-cloak class="px-2 py-6 text-center">
        <x-gpa.icon name="package" class="mx-auto h-7 w-7 text-ink-subtle" />
        <p class="mt-2 text-xs font-semibold text-ink-body">{{ $cart['emptyTitle'] }}</p>
        <p class="mt-1 text-2xs leading-relaxed text-ink-subtle">{{ $cart['emptyBody'] }}</p>
    </div>

    <ul x-show="draftCount() > 0" x-cloak class="mt-3 space-y-2">
        <template x-for="line in draft" :key="line.key">
            <li class="rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold text-ink" x-text="line.name"></p>
                    <p class="text-xs font-extrabold text-brand-strong" x-text="money(line.total)"></p>
                </div>
                <div class="mt-1 flex items-center justify-between gap-3">
                    <p class="gpa-mono-xs text-ink-subtle">
                        <span x-text="line.qty + ' kg @ ' + money(line.price)"></span>
                    </p>
                    <button type="button" @click="removeFromDraft(line.key)"
                        class="gpa-mono-xs font-semibold uppercase tracking-wider text-danger hover:underline">
                        {{ $cart['removeLabel'] }}
                    </button>
                </div>
            </li>
        </template>
    </ul>

    <div class="mt-4 space-y-3 border-t border-line-hair pt-3">
        <p class="flex items-baseline justify-between gap-3">
            <span class="gpa-mono-xs font-semibold uppercase tracking-wider text-ink-body">
                {{ $cart['subtotalLabel'] }}</span>
            <span class="text-base font-extrabold text-brand-strong" x-text="money(draftTotal())">Rp0</span>
        </p>

        <x-gpa.btn :href="route('cart')" variant="accent" block>{{ $cart['cta'] }}</x-gpa.btn>
    </div>
</section>

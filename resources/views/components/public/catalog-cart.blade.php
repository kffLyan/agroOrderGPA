@props([
    'cart' => null,
    'showAction' => true,
    'actionLabel' => 'Lanjutkan ke Pemesanan Resmi',
    'actionHref' => null,
])

@php
    $actionHref = $actionHref ?? (auth()->check() ? route('klien.keranjang') : route('login'));
@endphp

<section class="gpa-panel p-4 sm:p-5">
    <header class="flex items-center justify-between gap-3 border-b border-line-hair pb-3">
        <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
            Draf Pesanan Komoditas
        </h2>
        <span x-show="draftCount() > 0" x-cloak
            class="rounded-full bg-accent px-2.5 py-1 gpa-micro-bold tracking-wider text-brand-deep">
            <span x-text="draftCount()">0</span> Item
        </span>
    </header>

    <div x-show="draftCount() === 0" x-cloak class="px-2 py-6 text-center">
        <x-gpa.icon name="package" class="mx-auto h-7 w-7 text-ink-subtle" />
        <p class="mt-2 text-xs font-semibold text-ink-body">Belum Ada Item Terpilih</p>
        <p class="mt-1 text-2xs leading-relaxed text-ink-subtle">Pilih komoditas dari katalog untuk menyusun draf estimasi pesanan.</p>
    </div>

    <ul x-show="draftCount() > 0" x-cloak class="mt-3 space-y-2">
        <template x-for="line in draft" :key="line.key">
            <li class="rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-xs font-semibold text-ink" x-text="line.name"></p>
                    <p class="text-xs font-extrabold text-brand-strong" x-text="money(line.total)"></p>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                    <p class="gpa-mono-xs text-ink-subtle">
                        <span x-text="money(line.price) + ' / kg'"></span>
                    </p>
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 items-center rounded border border-line-board bg-surface">
                            <button type="button" @click="adjustDraftQty(line.key, -1)"
                                :aria-label="'Kurangi jumlah ' + line.name"
                                class="flex h-full w-7 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                                &minus;
                            </button>
                            <span class="w-10 border-x border-line-faint text-center gpa-mono-xs text-ink" x-text="line.qty"></span>
                            <button type="button" @click="adjustDraftQty(line.key, 1)"
                                :aria-label="'Tambah jumlah ' + line.name"
                                class="flex h-full w-7 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                                +
                            </button>
                        </div>
                        <span class="gpa-mono-xs text-ink-subtle" x-text="line.unit || 'kg'"></span>
                        <button type="button" @click="removeFromDraft(line.key)"
                            class="gpa-mono-xs font-semibold uppercase tracking-wider text-danger hover:underline">
                            Hapus
                        </button>
                    </div>
                </div>
            </li>
        </template>
    </ul>

    <div class="mt-4 space-y-3 border-t border-line-hair pt-3">
        <p class="flex items-baseline justify-between gap-3">
            <span class="gpa-mono-xs font-semibold uppercase tracking-wider text-ink-body">Subtotal Estimasi:</span>
            <span class="text-base font-extrabold text-brand-strong" x-text="money(draftTotal())">Rp0</span>
        </p>

        @if ($showAction)
            <x-gpa.btn :href="$actionHref" variant="accent" size="md" block>
                {{ $actionLabel }}
            </x-gpa.btn>
        @endif
    </div>
</section>

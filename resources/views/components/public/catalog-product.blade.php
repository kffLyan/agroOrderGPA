@props([
    'item',
])

@php
    $stockClass = match ($item['stock_tone']) {
        'safe' => 'bg-accent text-brand-deep ring-accent-edge',
        'caution' => 'bg-warning-cream text-warning-caution ring-warning-caution/30',
        default => 'bg-surface-muted text-ink-subtle ring-line-board',
    };
@endphp

<article id="komoditas-{{ $item['key'] }}" x-show="matches('{{ $item['key'] }}')" @class([
    'flex h-full flex-col overflow-hidden rounded-xl border border-line-hair bg-surface shadow-sub transition hover:shadow-card',
    'opacity-75' => ! $item['available'],
])>
    <div class="relative flex h-36 flex-col justify-end gap-1 overflow-hidden bg-surface-muted px-4 pb-3 pt-6 text-left">
        <img src="{{ asset($item['image_path']) }}" alt="{{ $item['name'] }}"
            class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
        <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
            @foreach ($item['badges'] as $badge)
                <span
                    class="rounded bg-brand-deep px-2 py-0.5 gpa-micro-bold tracking-wider text-accent">{{ $badge }}</span>
            @endforeach
        </div>

        <span class="absolute right-3 top-3 rounded bg-white/90 px-1.5 py-0.5 gpa-mono-xs text-ink">{{ $item['sku'] }}</span>

        <p class="relative text-sm font-bold leading-snug text-white">{{ $item['botanical'] }}</p>
        <p class="relative gpa-mono-xs italic text-white/90">{{ $item['latin'] }}</p>
    </div>

    <div class="flex flex-1 flex-col gap-3 p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="gpa-micro tracking-wider text-accent-deep">{{ $item['category_label'] }}</p>
                <h3 class="mt-1 text-sm font-bold leading-snug text-ink">{{ $item['name'] }}</h3>
            </div>

            <span @class([
                'shrink-0 rounded-full px-2.5 py-1 gpa-micro-bold ring-1 ring-inset',
                $stockClass,
            ])>
                {{ $item['stock_label'] }}@if ($item['stock_note']) · {{ $item['stock_note'] }}@endif
            </span>
        </div>

        <dl class="space-y-1.5 rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
            <div class="flex items-baseline justify-between gap-3">
                <dt class="gpa-mono-xs text-ink-subtle">Min. Order (MOQ)</dt>
                <dd class="gpa-mono-xs font-semibold text-brand-strong">{{ $item['moq'] }} {{ $item['unit'] }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-3">
                <dt class="gpa-mono-xs text-ink-subtle">Satuan</dt>
                <dd class="gpa-mono-xs font-semibold text-brand-strong">{{ $item['unit'] }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-3">
                <dt class="gpa-mono-xs text-ink-subtle">Gudang Franco</dt>
                <dd class="gpa-mono-xs font-semibold text-brand-strong">{{ $item['franco'] }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-3">
                <dt class="gpa-mono-xs text-ink-subtle">{{ $item['extra']['label'] }}</dt>
                <dd class="gpa-mono-xs font-semibold text-brand-strong">{{ $item['extra']['value'] }}</dd>
            </div>
        </dl>

        <div class="mt-auto space-y-3">
            <p class="flex items-baseline justify-between gap-2 border-t border-line-hair pt-3">
                <span class="gpa-mono-xs font-semibold uppercase tracking-wider text-ink-body">
                    {{ $item['price_label'] }}:</span>
                <span class="text-lg font-extrabold leading-none text-brand-strong">
                    Rp{{ number_format($item['price'], 0, ',', '.') }}<span
                        class="text-2xs font-semibold text-ink-body"> / kg</span>
                </span>
            </p>

            @if ($item['available'])
                <div class="flex items-center gap-2">
                    <div class="flex h-9 items-center rounded border border-line-board bg-canvas">
                        <button type="button" @click="stepBy('{{ $item['key'] }}', -1)"
                            aria-label="Kurangi volume {{ $item['name'] }}"
                            class="flex h-full w-9 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                            &minus;
                        </button>
                        <input type="number" min="0" max="{{ $item['stock'] }}" step="{{ $item['step'] }}"
                            value="{{ $item['initial_qty'] }}" :value="quantity('{{ $item['key'] }}')"
                            @input="setQuantity('{{ $item['key'] }}', $event.target.value)"
                            :aria-label="`Volume {{ $item['name'] }} dalam kilogram`"
                            class="w-16 border-x border-line-faint px-1 py-2 text-center gpa-mono-xs outline-none focus:bg-surface-shell">
                        <button type="button" @click="stepBy('{{ $item['key'] }}', 1)"
                            aria-label="Tambah volume {{ $item['name'] }}"
                            class="flex h-full w-9 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                            +
                        </button>
                    </div>
                    <span class="gpa-mono-xs text-ink-subtle">{{ $item['unit'] }}</span>
                </div>

                <x-gpa.btn variant="accent" block @click="addToDraft('{{ $item['key'] }}')">
                    <x-slot:icon>
                        <x-gpa.icon name="cart" />
                    </x-slot:icon>
                    Tambah ke Pesanan
                </x-gpa.btn>
            @else
                <x-gpa.btn variant="secondary" block disabled>
                    Stok Habis / Pre-Order Kontak Sekre
                </x-gpa.btn>
            @endif
        </div>
    </div>
</article>

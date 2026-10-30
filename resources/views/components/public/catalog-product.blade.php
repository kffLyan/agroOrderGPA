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
    <div class="relative flex h-24 flex-col justify-end gap-1 overflow-hidden bg-surface-muted px-3 pb-2 pt-5 text-left sm:h-28 lg:h-36 sm:px-4 sm:pb-3 sm:pt-6">
        <img src="{{ asset($item['image_path']) }}" alt="{{ $item['name'] }}"
            class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
        <div class="absolute left-2 top-2 flex flex-wrap gap-1 sm:left-3 sm:top-3 sm:gap-1.5">
            @foreach ($item['badges'] as $badge)
                <span
                    class="rounded bg-brand-deep px-1.5 py-0.5 gpa-micro-bold tracking-wider text-accent sm:px-2">{{ $badge }}</span>
            @endforeach
        </div>

        <span class="absolute right-2 top-2 hidden rounded bg-white/90 px-1.5 py-0.5 gpa-mono-xs text-ink sm:right-3 sm:top-3 sm:block">{{ $item['sku'] }}</span>

        <p class="relative hidden text-sm font-bold leading-snug text-white sm:block">{{ $item['botanical'] }}</p>
        <p class="relative hidden gpa-mono-xs italic text-white/90 sm:block">{{ $item['latin'] }}</p>
    </div>

    <div class="flex flex-1 flex-col gap-2 p-3 sm:gap-3 sm:p-4">
        <div class="flex flex-col gap-1.5 sm:flex-row sm:items-start sm:justify-between sm:gap-2">
            <div class="min-w-0">
                <p class="hidden gpa-micro tracking-wider text-accent-deep sm:block">{{ $item['category_label'] }}</p>
                <h3 class="text-xs font-bold leading-snug text-ink sm:mt-1 sm:text-sm">{{ $item['name'] }}</h3>
            </div>

            <span @class([
                'w-fit shrink-0 rounded-full px-2 py-0.5 gpa-micro-bold ring-1 ring-inset sm:px-2.5 sm:py-1',
                $stockClass,
            ])>
                <span class="hidden sm:inline">{{ $item['stock_label'] }}@if ($item['stock_note']) · {{ $item['stock_note'] }}@endif</span>
                <span class="sm:hidden">{{ $item['stock_label'] }}</span>
            </span>
        </div>

        <dl class="hidden space-y-1.5 rounded-lg border border-line-hair bg-canvas px-3 py-2.5 sm:block">
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

        <div class="mt-auto space-y-2 sm:space-y-3">
            <p class="flex items-center justify-between gap-1 border-t border-line-hair pt-2 sm:pt-3">
                <span class="gpa-mono-xs font-semibold uppercase tracking-wider text-ink-body">
                    {{ $item['price_label'] }}:</span>
                <span class="text-sm font-extrabold leading-tight text-brand-strong sm:text-lg">
                    Rp{{ number_format($item['price'], 0, ',', '.') }}/kg
                </span>
            </p>

            @if ($item['available'])
                <div class="hidden items-center gap-2 sm:flex">
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

                <x-gpa.btn variant="accent" block size="sm" @click="addToDraft('{{ $item['key'] }}')" class="sm:py-2">
                    <x-slot:icon>
                        <x-gpa.icon name="cart" />
                    </x-slot:icon>
                    <span class="hidden sm:inline">Tambah ke Pesanan</span>
                    <span class="sm:hidden">Pesan</span>
                </x-gpa.btn>
            @else
                <x-gpa.btn variant="secondary" block size="sm" disabled>
                    <span class="hidden sm:inline">Stok Habis / Pre-Order Kontak Sekre</span>
                    <span class="sm:hidden">Habis</span>
                </x-gpa.btn>
            @endif
        </div>
    </div>
</article>

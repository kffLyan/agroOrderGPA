@extends('layouts.dashboard')

@section('title', 'Katalog Komoditas & Kuota Pasokan Terikat B2B')

@php
    $rupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $kilogram = fn ($value) => number_format($value, 0, ',', '.').' kg';
    $quotaBarWidth = max(0, min(100, $quota['percent'])).'%';

    $chipClass = fn ($tone) => match ($tone) {
        'safe' => 'bg-accent text-success-ink ring-accent-edge',
        'caution' => 'bg-warning-cream text-warning-caution ring-warning-caution/30',
        default => 'bg-surface-pill text-ink-body ring-line-board',
    };
    $textTone = fn ($tone) => match ($tone) {
        'success' => 'text-success-deep',
        'caution' => 'text-warning-caution',
        default => 'text-ink',
    };
@endphp

@section('content')
    <div x-data="clientCatalog(@js($commodities))" class="flex flex-col gap-5">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Kop halaman + aksi ekspor / matrix --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-4 p-5 lg:flex-row lg:items-start lg:justify-between lg:gap-6 lg:p-6">
            <div class="flex min-w-0 flex-col gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-accent px-2.5 py-1 ring-1 ring-inset ring-accent-edge gpa-micro-bold text-success-ink">
                        {{ $contract['binding'] }}
                    </span>
                    <span class="gpa-meta-lg text-ink-quiet">No: {{ $contract['number'] }}</span>
                </div>

                <h1 class="gpa-section-title text-ink lg:text-[1.375rem] lg:leading-7">
                    Katalog Komoditas &amp; Kuota Pasokan Terikat B2B
                </h1>

                <p class="max-w-3xl text-sm leading-5 text-ink-body">
                    Ketersediaan panen langsung dari Petani Binaan &amp; Buffer Stock resmi PT Green Pasundan
                    Agriculture. Harga franco gudang penerima, terintegrasi dengan validasi kuota kontrak B2B
                    dan jadwal pengiriman subuh.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="exportSpec()"
                    class="inline-flex items-center gap-2 rounded-lg border border-line-soft bg-surface-pill px-3.5 py-2 transition-colors hover:border-brand hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 text-ink" />
                    <span class="gpa-meta-lg text-ink">Export Spesifikasi (PDF)</span>
                </button>

                <button type="button" @click="toggleMatrix()"
                    class="inline-flex items-center gap-2 rounded-lg bg-ink px-4 py-2 shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="grid" class="h-3.5 w-3.5 text-white" />
                    <span class="gpa-meta-lg font-bold text-white">Batch Matrix View</span>
                </button>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan kuota / kredit / dispatch / rulebook --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="gpa-card flex flex-col justify-between gap-4 p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">{{ $quota['label'] }}</h2>
                        <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="flex flex-wrap items-baseline gap-1 pt-1 text-ink">
                        <span class="gpa-figure">{{ number_format($quota['used'], 0, ',', '.') }}</span>
                        <span class="font-inter text-sm text-ink-body">/ {{ $kilogram($quota['total']) }}</span>
                    </p>
                    <p class="gpa-body font-bold text-success-deep">
                        {{ number_format($quota['percent'], 1, ',', '') }}% terserap
                    </p>
                </div>
                <div class="flex flex-col gap-1.5 border-t border-line-soft/60 pt-3">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-surface-pill" role="img"
                        aria-label="Kuota terserap {{ number_format($quota['percent'], 1, ',', '') }} persen">
                        <div class="h-full rounded-full bg-success-deep"
                            style="width: {{ $quotaBarWidth }}"></div>
                    </div>
                    <p class="gpa-micro text-ink-body">{{ $quota['footnote'] }}</p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">{{ $credit['label'] }}</h2>
                        <x-gpa.icon name="wallet" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-ink">{{ $rupiah($credit['available']) }}</p>
                    <p class="gpa-body text-ink-body">
                        Alokasi plafon total:
                        <span class="font-medium text-ink">{{ $rupiah($credit['limit']) }}</span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <p class="gpa-micro-bold text-success-deep">Status: {{ $credit['status'] }}</p>
                    <span
                        class="rounded bg-surface-pill px-2 py-0.5 gpa-micro-bold text-ink-body">
                        {{ $credit['chip'] }}
                    </span>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">{{ $dispatch['label'] }}</h2>
                        <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <p class="gpa-figure text-ink">{{ $dispatch['po'] }}</p>
                        <span
                            class="rounded-md bg-accent px-2 py-0.5 ring-1 ring-inset ring-accent-edge gpa-meta-lg font-bold text-success-ink">
                            {{ $dispatch['weight'] }}
                        </span>
                    </div>
                    <p class="gpa-body text-ink-body">Tujuan: {{ $dispatch['destination'] }}</p>
                </div>
                <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <p class="gpa-meta-lg font-bold text-ink">ETA: {{ $dispatch['eta'] }}</p>
                    <p class="gpa-micro-bold text-success-deep">Reefer {{ $dispatch['temperature'] }}</p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 bg-brand p-5 ring-1 ring-inset ring-accent/30">
                <div class="flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-accent">{{ $rulebook['label'] }}</h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-accent" />
                    </div>
                    <ul class="flex flex-col gap-1.5">
                        @foreach ($rulebook['rules'] as $rule)
                            <li class="flex items-start gap-2">
                                <x-gpa.icon name="check" class="mt-0.5 h-3 w-3 shrink-0 text-accent" />
                                <p class="gpa-body text-white/90">
                                    <span class="gpa-micro-bold text-accent">{{ $rule['code'] }}:</span>
                                    {{ $rule['text'] }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <p class="gpa-micro border-t border-white/10 pt-3 text-white/60">{{ $rulebook['footer'] }}</p>
            </article>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Filter kategori + sentra + pencarian --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-3 p-4">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($categories as $category)
                    <button type="button" @click="category = @js($category['value'])"
                        :class="category === @js($category['value'])
                            ? 'border-ink bg-ink text-white'
                            : 'border-line-soft bg-surface-pill text-ink-body hover:border-line-soft hover:text-ink'"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 gpa-meta-lg transition-colors">
                        <span>{{ mb_strtoupper($category['label']) }}</span>
                        <span
                            class="rounded px-1 text-[10px] leading-3 gpa-micro"
                            :class="category === @js($category['value']) ? 'bg-accent text-success-ink' : 'bg-surface-disabled text-ink-body'"
                            x-text="@js($category['count'])"></span>
                    </button>
                @endforeach

                <button type="button" @click="availableOnly = ! availableOnly" :aria-pressed="availableOnly"
                    class="ml-auto inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 gpa-meta-lg transition-colors"
                    :class="availableOnly
                        ? 'border-ink bg-ink text-white'
                        : 'border-line-soft bg-surface-pill text-ink-body hover:border-line-soft hover:text-ink'">
                    <x-gpa.icon name="filter" class="h-3.5 w-3.5" />
                    Hanya Stok Tersedia
                </button>
            </div>

            <div class="flex flex-col gap-2 border-t border-line-hair pt-3 lg:flex-row lg:items-center">
                <label class="sr-only" for="filter-sentra">Sentra panen</label>
                <select id="filter-sentra" x-model="origin" class="gpa-control lg:max-w-[15rem]">
                    <option value="all">Semua Sentra Panen ({{ $contract['hub'] }})</option>
                    @foreach ($origins as $origin)
                        <option value="{{ $origin['value'] }}">{{ $origin['label'] }}</option>
                    @endforeach
                </select>

                <label class="sr-only" for="filter-urutkan">Urutkan komoditas</label>
                <select id="filter-urutkan" x-model="sort" class="gpa-control lg:max-w-[17rem]">
                    @foreach ($sortOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>

                <div class="relative flex-1">
                    <x-gpa.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-body" />
                    <label class="sr-only" for="filter-pencarian">Cari komoditas</label>
                    <input id="filter-pencarian" type="search" x-model.debounce.200ms="query"
                        placeholder="Cari nama komoditas, grade, atau ID sentra panen..."
                        class="gpa-control pl-8">
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <p class="gpa-micro-bold text-ink-body">
                        <span class="text-ink" x-text="visibleCount()"></span>/{{ count($commodities) }} Komoditas
                    </p>
                    <button type="button" x-show="filtered()" @click="reset()"
                        class="inline-flex items-center gap-1 rounded-lg border border-line-hair bg-surface px-2.5 py-1.5 gpa-micro-bold text-ink-body transition-colors hover:border-brand hover:text-ink">
                        <x-gpa.icon name="refresh" class="h-3 w-3" />
                        Reset
                    </button>
                </div>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Kartu komoditas --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            <template x-for="card in ordered()" :key="card.key">
                <article :id="`komoditas-${card.key}`"
                    class="gpa-card flex flex-col overflow-hidden ring-1 ring-line-hair transition-shadow duration-200"
                    :class="card.critical ? 'bg-warning-cream/40' : ''">
                    <header class="flex flex-col gap-2 border-b border-line-soft bg-surface-shell p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="gpa-micro-bold text-ink-quiet" x-text="card.category_label"></p>
                            <span class="shrink-0 rounded-md px-2 py-0.5 ring-1 ring-inset gpa-micro-bold"
                                :class="chipClass(card.availability_tone)" x-text="card.availability"></span>
                        </div>

                        <div class="flex min-w-0 flex-col gap-0.5">
                            <p class="gpa-micro text-ink-quiet" x-text="card.code"></p>
                            <h3 class="font-sans text-[15px] font-bold leading-5 text-ink" x-text="card.name"></h3>
                            <p class="text-xs leading-4 text-ink-body" x-text="card.subtitle"></p>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <dl class="grid grid-cols-2 gap-2">
                            <template x-for="spec in card.specs" :key="spec.label">
                                <div class="rounded-md border border-line-faint bg-surface-shell p-2">
                                    <dt class="gpa-micro text-ink-quiet" x-text="spec.label"></dt>
                                    <dd class="mt-1 gpa-body font-bold" :class="toneClass(spec.tone)"
                                        x-text="spec.value"></dd>
                                </div>
                            </template>
                        </dl>

                        <div class="flex flex-wrap items-end justify-between gap-2 rounded-lg border border-line-faint bg-surface-muted p-3">
                            <div>
                                <p class="gpa-micro text-ink-quiet">Harga Kontrak B2B (Franco)</p>
                                <p class="mt-1 flex flex-wrap items-baseline gap-1">
                                    <span class="font-sans text-xl font-extrabold leading-6 text-ink"
                                        x-text="money(card.price)"></span>
                                    <span class="gpa-meta text-ink-body">/kg</span>
                                </p>
                            </div>
                            <p class="gpa-micro-bold" :class="toneClass(card.price_note_tone)"
                                x-text="card.price_note"></p>
                        </div>

                        <div class="mt-auto flex flex-col gap-2 border-t border-line-hair pt-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="gpa-micro text-ink-quiet">Input Volume Pemesanan</p>
                                <p class="gpa-meta-lg font-bold text-success-ink">
                                    Est: <span x-text="money(estimatedTotal(card.key))"></span>
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <div class="flex items-center rounded-md border border-line-strong bg-surface">
                                    <button type="button" @click="stepBy(card.key, -1)" aria-label="Kurangi volume"
                                        class="flex h-9 w-9 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                                        <x-gpa.icon name="x" class="h-3 w-3" />
                                    </button>
                                    <input type="number" :value="quantity(card.key)"
                                        @input="setQuantity(card.key, $event.target.value)"
                                        :aria-label="`Volume ${card.name} dalam kilogram`"
                                        class="w-16 border-x border-line-faint px-1 py-2 text-center gpa-mono-xs outline-none focus:bg-surface-shell">
                                    <button type="button" @click="stepBy(card.key, 1)" aria-label="Tambah volume"
                                        class="flex h-9 w-9 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                                        <x-gpa.icon name="plus" class="h-3 w-3" />
                                    </button>
                                </div>

                                <span class="gpa-micro-bold text-ink-body">KG</span>

                                <button type="button" @click="addToDraft(card.key)"
                                    class="inline-flex min-w-[9rem] flex-1 items-center justify-center gap-2 rounded bg-accent px-3 py-2.5 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                                    <x-gpa.icon name="cart" class="h-3.5 w-3.5" />
                                    <span class="gpa-meta-lg font-bold">Tambah Draft</span>
                                </button>
                            </div>

                            <p class="gpa-micro text-ink-quiet">
                                MOQ <span class="text-ink" x-text="card.moq"></span> kg ·
                                <span x-text="card.stock_label"></span>
                                <span class="text-ink" x-text="card.stock"></span> kg ·
                                Cold-chain <span class="text-ink" x-text="card.cold_chain"></span>
                            </p>
                        </div>
                    </div>
                </article>
            </template>

            <article x-show="visibleCount() === 0" x-cloak
                class="gpa-card col-span-full flex flex-col items-center gap-3 p-10 text-center">
                <x-gpa.icon name="search" class="h-6 w-6 text-ink-quiet" />
                <p class="gpa-section-title text-ink">Tidak ada komoditas yang cocok</p>
                <p class="max-w-md gpa-body text-ink-body">
                    Sesuaikan kata kunci pencarian, kategori, sentra panen, atau matikan filter
                    "Hanya Stok Tersedia".
                </p>
                <button type="button" @click="reset()"
                    class="mt-1 inline-flex items-center gap-2 rounded-lg border border-line-hair bg-surface-shell px-3.5 py-2 transition-colors hover:border-brand">
                    <x-gpa.icon name="refresh" class="h-3.5 w-3.5 text-ink" />
                    <span class="gpa-meta-lg text-ink">Reset Filter</span>
                </button>
            </article>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Program ad hoc tonase --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card relative overflow-hidden bg-brand p-5 ring-2 ring-inset ring-accent-edge lg:p-6">
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-accent/20 blur-3xl"
                aria-hidden="true"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:gap-8">
                <div class="flex min-w-0 flex-col gap-2">
                    <span
                        class="w-fit rounded bg-accent px-2.5 py-1 gpa-micro-bold text-success-ink">
                        {{ $adhoc['chip'] }}
                    </span>
                    <h2 class="font-sans text-lg font-extrabold leading-6 text-white">{{ $adhoc['title'] }}</h2>
                    <p class="max-w-2xl text-sm leading-5 text-white/80">{{ $adhoc['body'] }}</p>

                    <ul class="mt-1 flex flex-col gap-1.5">
                        @foreach ($adhoc['highlights'] as $highlight)
                            <li class="flex items-start gap-2">
                                <x-gpa.icon name="check-circle" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-accent" />
                                <p class="gpa-body text-white/90">{{ $highlight }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="flex shrink-0 flex-col items-start gap-2 lg:items-end">
                    <button type="button" @click="$dispatch('gpa:toast', { title: 'Permintaan dikomunikasikan', message: 'Tim Key Account GPA akan menghubungi Anda dalam 1x24 jam.', tone: 'success' })"
                        class="inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-ink shadow-pop transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="plus" class="h-4 w-4" />
                        <span class="gpa-meta-lg font-bold">{{ $adhoc['cta'] }}</span>
                    </button>
                    <p class="gpa-micro text-white/60">Minimum batch 5 Ton · Penawaran 1x24 Jam</p>
                </div>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Ringkasan draft PO (mengikuti badge Keranjang di sidebar) --}}
        {{-- ---------------------------------------------------------------- --}}
        <div id="quick-reorder" class="sticky bottom-4 z-10 scroll-mt-24">
            <section x-show="draftCount() > 0" x-cloak
                class="gpa-card flex flex-col gap-3 border border-brand-line p-4 shadow-pop lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 flex-col gap-1">
                    <p class="gpa-micro-bold text-success-deep">
                        Draft PO aktif · <span x-text="draftCount()"></span> item
                    </p>
                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <p class="gpa-figure text-ink"><span x-text="draftWeight()"></span> kg</p>
                        <p class="gpa-meta-lg font-bold text-ink">
                            Total <span x-text="money(draftTotal())"></span>
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <template x-for="line in draft" :key="`draft-${line.key}`">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-md border border-line-hair bg-surface-shell px-2 py-1 gpa-micro-bold text-ink-body">
                            <span x-text="`${line.name} · ${line.qty} kg`"></span>
                            <button type="button" @click="removeFromDraft(line.key)" aria-label="Hapus dari draft"
                                class="text-ink-quiet transition-colors hover:text-danger">
                                <x-gpa.icon name="x" class="h-3 w-3" />
                            </button>
                        </span>
                    </template>

                    <button type="button" @click="submitDraft()"
                        class="inline-flex items-center gap-2 rounded-lg bg-ink px-4 py-2.5 shadow-sub transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="send" class="h-3.5 w-3.5 text-white" />
                        <span class="gpa-meta-lg font-bold text-white">Kirim Draft PO</span>
                    </button>
                </div>
            </section>
        </div>
    </div>
@endsection

@extends('layouts.klien')

@section('title', 'Katalog Komoditas & Kuota Pasokan Terikat B2B')

@php
    $user = auth()->user();
    $activeContractFirst = $activeContracts->first();
    $quotaPercent = $totalCommittedQuota > 0 ? min(100, ($usedQuota / $totalCommittedQuota) * 100) : 0;
    $creditAvailable = max(0, $creditLimit - $unpaidTotal);
    $creditPercent = $creditLimit > 0 ? ($creditAvailable / $creditLimit) * 100 : 0;
    $quotaBarWidth = max(0, min(100, $quotaPercent)) . '%';

    $commodities = ($rawProducts ?? collect())->map(function ($product) use ($activeContracts, $stockMap) {
        $availableStock = $stockMap[$product->id] ?? 0;
        $hasContract = $activeContracts->has($product->id);
        $contractItem = $activeContracts->get($product->id);
        $effectivePrice = $hasContract ? (float) $contractItem->fixed_price_per_kg : (float) $product->base_price;
        $moq = (float) ($product->minimum_order ?? 10);

        return [
            'key'                => (string) $product->id,
            'id'                 => $product->id,
            'sku'                => $product->sku ?? ('SKU-' . $product->id),
            'code'               => $product->sku ?? ('SKU-' . $product->id),
            'name'               => $product->name,
            'image_path'         => $product->image_path,
            'image_url'          => asset($product->image_path),
            'subtitle'           => $product->description ?: "Komoditas hortikultura unggulan Pasundan Grade {$product->grade}",
            'category'           => 'sayur-daun',
            'grade'              => $product->grade ?: 'Grade A',
            'packaging'          => "Krat Plastik Higienis 10 {$product->unit}",
            'price'              => $effectivePrice,
            'base_price'         => (float) $product->base_price,
            'has_contract'       => $hasContract,
            'price_note'         => $hasContract ? 'Harga Kontrak Terkunci' : 'Harga Pasar Reguler',
            'price_note_tone'    => $hasContract ? 'success' : 'default',
            'stock'              => $availableStock,
            'stock_label'        => 'Stok Bebas:',
            'availability'       => $availableStock > 0 ? 'Stok Tersedia' : 'Habis Terpesan',
            'availability_tone'  => $availableStock > 0 ? 'safe' : 'caution',
            'moq'                => $moq,
            'step'               => 1,
            'crate_kg'           => 10,
            'pack_label'         => 'Keranjang',
            'cold_chain'         => '2-6°C Pre-Cooled',
            'critical'           => $availableStock < 50 && $availableStock > 0,
            'origin'             => 'Lembang',
            'specs'              => [
                [
                    'label' => 'Satuan',
                    'value' => strtoupper($product->unit ?? 'KG'),
                    'tone'  => 'default',
                ],
                [
                    'label' => 'Grade Mutu',
                    'value' => $product->grade ?: 'Grade A',
                    'tone'  => 'success',
                ],
                [
                    'label' => 'Stok Riil Panen',
                    'value' => number_format($availableStock, 0, ',', '.') . ' ' . $product->unit,
                    'tone'  => $availableStock > 0 ? 'success' : 'caution',
                ],
                [
                    'label' => 'Min Order',
                    'value' => number_format($moq, 0, ',', '.') . ' ' . $product->unit,
                    'tone'  => 'default',
                ],
            ],
        ];
    })->values()->all();
@endphp

@section('content')
    <div x-data="clientCatalog(@js($commodities))" class="flex flex-col gap-5">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Kop halaman + aksi ekspor / matrix --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-4 p-5 lg:flex-row lg:items-start lg:justify-between lg:gap-6 lg:p-6 ring-1 ring-line-hair shadow-sub">
            <div class="flex min-w-0 flex-col gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-accent px-2.5 py-1 ring-1 ring-inset ring-accent-edge gpa-micro-bold text-success-ink">
                        {{ $isB2b ? 'Kontrak B2B Mengikat' : 'Pembelian Reguler Harian' }}
                    </span>
                    <span class="gpa-meta-lg text-ink-quiet">No: {{ $activeContractFirst ? $activeContractFirst->contract_number : ($isB2b ? 'CTR-GPA-B2B' : 'REGULER-SPOT') }}</span>
                </div>

                <h1 class="gpa-section-title text-ink lg:text-[1.375rem] lg:leading-7">
                    Katalog Komoditas &amp; Kuota Pasokan Terikat B2B
                </h1>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan kuota / kredit / dispatch / panduan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <article class="gpa-card flex flex-col justify-between gap-3 p-4 sm:gap-4 sm:p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body leading-tight">Serapan Kuota Kontrak Bulanan</h2>
                        <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="flex flex-wrap items-baseline gap-1 pt-1 text-ink">
                        <span class="text-base sm:text-xl font-extrabold leading-tight">{{ number_format($usedQuota, 0, ',', '.') }}</span>
                        <span class="font-inter text-xs sm:text-sm text-ink-body">/ {{ number_format($totalCommittedQuota, 0, ',', '.') }} kg</span>
                    </p>
                    <p class="gpa-body font-bold text-success-deep">
                        {{ number_format($quotaPercent, 1, ',', '') }}% terserap
                    </p>
                </div>
                <div class="flex flex-col gap-1.5 border-t border-line-soft/60 pt-2 sm:pt-3">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-surface-pill" role="img"
                        aria-label="Kuota terserap {{ number_format($quotaPercent, 1, ',', '') }} persen">
                        <div class="h-full rounded-full bg-success-deep"
                            style="width: {{ $quotaBarWidth }}"></div>
                    </div>
                    <p class="gpa-micro text-ink-body leading-tight">Reset otomatis tiap tgl 1</p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-4 sm:gap-4 sm:p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body leading-tight">Plafon Kredit &amp; Termin Tempo</h2>
                        <x-gpa.icon name="wallet" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="pt-1 text-ink text-base sm:text-xl font-extrabold leading-tight">Rp {{ number_format($creditAvailable, 0, ',', '.') }}</p>
                    <p class="gpa-body text-ink-body">
                        Total: <span class="font-medium text-ink">Rp {{ number_format($creditLimit, 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 sm:gap-2 border-t border-line-soft/60 pt-2 sm:pt-3">
                    <p class="gpa-micro-bold text-success-deep leading-tight">{{ $creditAvailable > 0 ? 'Lancar / Tersedia' : 'Plafon Penuh' }}</p>
                    <span class="w-fit rounded bg-surface-pill px-2 py-0.5 gpa-micro-bold text-ink-body">
                        {{ $isB2b ? 'TOP 30 Hari' : 'H+0 Cash/Transfer' }}
                    </span>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-4 sm:gap-4 sm:p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body leading-tight">Pengiriman Terdekat</h2>
                        <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 pt-1">
                        <p class="text-sm sm:text-base font-bold text-ink leading-tight truncate max-w-[120px] sm:max-w-full">{{ $lastActiveOrder ? $lastActiveOrder->order_number : 'Belum Ada' }}</p>
                        <span class="rounded-md bg-accent px-1.5 sm:px-2 py-0.5 ring-1 ring-inset ring-accent-edge gpa-micro-bold text-success-ink">
                            {{ $lastActiveOrder ? number_format($lastActiveOrder->orderItems()->sum('ordered_qty'), 0, ',', '.') . ' kg' : '0 kg' }}
                        </span>
                    </div>
                    <p class="gpa-body text-ink-body">Tujuan: {{ $user->address ?: 'Gudang Utama Klien' }}</p>
                </div>
                <div class="flex items-center justify-between gap-1.5 sm:gap-2 border-t border-line-soft/60 pt-2 sm:pt-3">
                    <p class="gpa-meta text-ink leading-tight">ETA: {{ $lastActiveOrder ? ($lastActiveOrder->target_delivery_date ? $lastActiveOrder->target_delivery_date->format('d M Y') : 'Besok') : 'Standby' }}</p>
                    <p class="gpa-micro-bold text-success-deep">Reefer 4°C</p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 bg-brand p-4 sm:gap-4 sm:p-5 ring-1 ring-inset ring-accent/30">
                <div class="flex flex-col gap-2 sm:gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-accent leading-tight">SOP Pemesanan &amp; Alokasi</h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-accent" />
                    </div>
                    <ul class="flex flex-col gap-1 sm:gap-1.5">
                        <li class="flex items-start gap-1.5">
                            <x-gpa.icon name="check" class="mt-0.5 h-2.5 w-2.5 sm:h-3 sm:w-3 shrink-0 text-accent" />
                            <p class="text-[11px] leading-tight sm:text-xs text-white/90">Cut-off 16:00 WIB utk H+1</p>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <x-gpa.icon name="check" class="mt-0.5 h-2.5 w-2.5 sm:h-3 sm:w-3 shrink-0 text-accent" />
                            <p class="text-[11px] leading-tight sm:text-xs text-white/90">Harga kontrak terkunci</p>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <x-gpa.icon name="check" class="mt-0.5 h-2.5 w-2.5 sm:h-3 sm:w-3 shrink-0 text-accent" />
                            <p class="text-[11px] leading-tight sm:text-xs text-white/90">Penimbangan neto digital</p>
                        </li>
                    </ul>
                </div>
                <p class="gpa-micro border-t border-white/10 pt-2 sm:pt-3 text-white/60 leading-tight">Perjanjian Kerjasama GPA</p>
            </article>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Filter kategori + sentra + pencarian --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-3 p-3 sm:p-4">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                <button type="button" @click="category = 'all'"
                    :class="category === 'all'
                        ? 'border-ink bg-ink text-white'
                        : 'border-line-soft bg-surface-pill text-ink-body hover:border-line-soft hover:text-ink'"
                    class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 sm:px-3 sm:py-1.5 gpa-meta text-xs sm:text-sm transition-colors">
                    <span>SEMUA</span>
                    <span class="rounded px-1 text-[10px] leading-3 gpa-micro"
                        :class="category === 'all' ? 'bg-accent text-success-ink' : 'bg-surface-disabled text-ink-body'"
                        x-text="{{ count($commodities) }}"></span>
                </button>
                <button type="button" @click="category = 'sayur-daun'"
                    :class="category === 'sayur-daun'
                        ? 'border-ink bg-ink text-white'
                        : 'border-line-soft bg-surface-pill text-ink-body hover:border-line-soft hover:text-ink'"
                    class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 sm:px-3 sm:py-1.5 gpa-meta text-xs sm:text-sm transition-colors">
                    <span>SAYURAN SEGAR</span>
                    <span class="rounded px-1 text-[10px] leading-3 gpa-micro"
                        :class="category === 'sayur-daun' ? 'bg-accent text-success-ink' : 'bg-surface-disabled text-ink-body'"
                        x-text="{{ count($commodities) }}"></span>
                </button>

                <button type="button" @click="availableOnly = ! availableOnly" :aria-pressed="availableOnly"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 sm:px-3 sm:py-1.5 gpa-meta text-xs sm:text-sm transition-colors"
                    :class="availableOnly
                        ? 'border-ink bg-ink text-white'
                        : 'border-line-soft bg-surface-pill text-ink-body hover:border-line-soft hover:text-ink'">
                    <x-gpa.icon name="filter" class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                    Hanya Stok Tersedia
                </button>
            </div>

            <div class="flex flex-col gap-2 border-t border-line-hair pt-3 lg:flex-row lg:flex-wrap lg:items-center">
                <div class="grid grid-cols-2 gap-2 lg:flex lg:flex-wrap lg:gap-2 lg:flex-1">
                    <label class="sr-only" for="filter-sentra">Sentra panen</label>
                    <select id="filter-sentra" x-model="origin" class="gpa-control text-xs sm:text-sm lg:max-w-[14rem]">
                        <option value="all">Semua Sentra</option>
                        <option value="lembang">Lembang</option>
                        <option value="ciwidey">Ciwidey</option>
                        <option value="pangalengan">Pangalengan</option>
                    </select>

                    <label class="sr-only" for="filter-urutkan">Urutkan komoditas</label>
                    <select id="filter-urutkan" x-model="sort" class="gpa-control text-xs sm:text-sm lg:max-w-[14rem]">
                        <option value="name-asc">Nama (A - Z)</option>
                        <option value="price-asc">Harga Termurah</option>
                        <option value="price-desc">Harga Tertinggi</option>
                        <option value="stock-desc">Stok Terbanyak</option>
                    </select>

                    <div class="relative col-span-2 lg:flex-1 lg:min-w-[200px]">
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-body" />
                        <label class="sr-only" for="filter-pencarian">Cari komoditas</label>
                        <input id="filter-pencarian" type="search" x-model.debounce.200ms="query"
                            placeholder="Cari komoditas..."
                            class="gpa-control pl-8 text-xs sm:text-sm">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 lg:justify-end">
                    <p class="gpa-micro-bold text-ink-body">
                        <span class="text-ink" x-text="visibleCount()"></span>/{{ count($commodities) }} Komoditas
                    </p>
                    <button type="button" x-show="filtered()" @click="reset()"
                        class="inline-flex items-center gap-1 rounded-lg border border-line-hair bg-surface px-2.5 py-1 gpa-micro-bold text-ink-body transition-colors hover:border-brand hover:text-ink">
                        <x-gpa.icon name="refresh" class="h-3 w-3" />
                        Reset
                    </button>
                </div>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Kartu komoditas --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid gap-2 sm:gap-3 md:gap-4 grid-cols-2 2xl:grid-cols-3">
            <template x-for="card in ordered()" :key="card.key">
                <article :id="`komoditas-${card.key}`"
                    class="gpa-card flex flex-col overflow-hidden ring-1 ring-line-hair transition-shadow duration-200"
                    :class="card.critical ? 'bg-warning-cream/40' : ''">
                    <header class="flex flex-col gap-1.5 border-b border-line-soft bg-surface-shell p-2.5 sm:p-4">
                        <div class="flex items-start justify-between gap-1.5">
                            <span class="shrink-0 rounded-md px-1.5 sm:px-2 py-0.5 ring-1 ring-inset gpa-micro-bold"
                                :class="chipClass(card.availability_tone)" x-text="card.availability"></span>
                        </div>

                        <div class="flex min-w-0 flex-col gap-0.5">
                            <p class="gpa-micro text-ink-quiet" x-text="card.code"></p>
                            <h3 class="font-sans text-[13px] sm:text-[15px] font-bold leading-tight sm:leading-5 text-ink line-clamp-2" x-text="card.name"></h3>
                            <p class="hidden sm:block text-xs leading-4 text-ink-body" x-text="card.subtitle"></p>
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col gap-1.5 sm:gap-3 p-2.5 sm:p-4">
                        <div class="relative h-24 sm:h-40 w-full overflow-hidden rounded-lg border border-line-hair bg-surface-muted">
                            <img :src="card.image_url" :alt="card.name"
                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                loading="lazy"
                                x-on:error="$event.target.src = '{{ asset('gpaleaves.png') }}'">
                        </div>

                        <dl class="hidden sm:grid grid-cols-2 gap-2">
                            <template x-for="spec in card.specs" :key="spec.label">
                                <div class="rounded-md border border-line-faint bg-surface-shell p-2">
                                    <dt class="gpa-micro text-ink-quiet" x-text="spec.label"></dt>
                                    <dd class="mt-1 gpa-body font-bold" :class="toneClass(spec.tone)"
                                        x-text="spec.value"></dd>
                                </div>
                            </template>
                        </dl>

                        <div class="flex flex-wrap items-end justify-between gap-1.5 rounded-lg border border-line-faint bg-surface-muted p-2 sm:p-3">
                            <div>
                                <p class="gpa-micro text-ink-quiet leading-tight">Harga Kontrak B2B</p>
                                <p class="mt-0.5 flex flex-wrap items-baseline gap-1">
                                    <span class="font-sans text-sm sm:text-xl font-extrabold leading-tight sm:leading-6 text-ink"
                                        x-text="money(card.price)"></span>
                                    <span class="gpa-meta text-ink-body">/kg</span>
                                </p>
                            </div>
                            <p class="hidden sm:block gpa-micro-bold" :class="toneClass(card.price_note_tone)"
                                x-text="card.price_note"></p>
                        </div>

                        <div class="mt-auto flex flex-col gap-1.5 border-t border-line-hair pt-2 sm:pt-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="hidden sm:block gpa-micro text-ink-quiet">Input Volume Pemesanan</p>
                                <p class="gpa-meta text-success-ink">
                                    Est: <span x-text="money(estimatedTotal(card.key))"></span>
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                                <div class="flex items-center rounded-md border border-line-strong bg-surface">
                                    <button type="button" @click="stepBy(card.key, -1)" aria-label="Kurangi volume"
                                        class="flex h-7 w-7 sm:h-9 sm:w-9 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                                        <span class="text-sm font-bold leading-none">-</span>
                                    </button>
                                    <input type="number" :value="quantity(card.key)"
                                        @input="setQuantity(card.key, $event.target.value)"
                                        :aria-label="`Volume ${card.name} dalam kilogram`"
                                        class="w-10 sm:w-16 border-x border-line-faint px-1 py-1 sm:py-2 text-center gpa-mono-xs outline-none focus:bg-surface-shell">
                                    <button type="button" @click="stepBy(card.key, 1)" aria-label="Tambah volume"
                                        class="flex h-7 w-7 sm:h-9 sm:w-9 items-center justify-center text-ink transition-colors hover:bg-surface-shell">
                                        <span class="text-sm font-bold leading-none">+</span>
                                    </button>
                                </div>

                                <span class="hidden sm:inline gpa-micro-bold text-ink-body">KG</span>

                                <button type="button" @click="addToDraft(card.key)"
                                    :class="card.stock <= 0 ? 'bg-surface-disabled text-ink-muted hover:bg-surface-disabled' : 'bg-accent text-ink hover:bg-accent-deep'"
                                    class="inline-flex min-w-0 flex-1 items-center justify-center gap-1 sm:gap-2 rounded px-2 sm:px-3 py-1.5 sm:py-2.5 shadow-sub transition-colors">
                                    <x-gpa.icon name="cart" class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                    <span class="gpa-meta text-xs sm:text-sm font-bold" x-text="card.stock <= 0 ? 'Habis' : 'Tambah'"></span>
                                </button>
                            </div>

                            <p class="hidden sm:block gpa-micro text-ink-quiet">
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
                        Fasilitas Mitra B2B
                    </span>
                    <h2 class="font-sans text-lg font-extrabold leading-6 text-white">Kebutuhan Pasokan Ad-Hoc &amp; Skala Tonase Khusus</h2>
                    <p class="max-w-2xl text-sm leading-5 text-white/80">Membutuhkan penambahan alokasi komoditas di luar batas kuota kontrak reguler? Hubungi Account Executive GPA untuk pembukaan buffer panen darurat 24 jam.</p>

                    <ul class="mt-1 flex flex-col gap-1.5">
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check-circle" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">Prioritas alokasi sortasi Grade Super dari green house binaan</p>
                        </li>
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check-circle" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">Pengiriman armada chiller berpendingin khusus langsung ke sentra produksi Anda</p>
                        </li>
                    </ul>
                </div>

                <div class="flex shrink-0 flex-col items-start gap-2 lg:items-end">
                    <button type="button" @click="$dispatch('gpa:toast', { title: 'Permintaan dikomunikasikan', message: 'Tim Key Account GPA akan menghubungi Anda dalam 1x24 jam.', tone: 'success' })"
                        class="inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-ink shadow-pop transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="plus" class="h-4 w-4" />
                        <span class="gpa-meta-lg font-bold">Ajukan Pasokan Khusus</span>
                    </button>
                    <p class="gpa-micro text-white/60">Minimum batch 5 Ton · Penawaran 1x24 Jam</p>
                </div>
            </div>
        </section>



        {{-- ---------------------------------------------------------------- --}}
        {{-- Draft PO Mobile Floating Bar + Bottom Sheet / Desktop Sticky --}}
        {{-- ---------------------------------------------------------------- --}}
        <div x-show="draftCount() > 0" x-cloak x-data="{ mobileCartOpen: false }">
            {{-- Desktop sticky dock --}}
            <section id="quick-reorder"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-6 scale-98"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-6 scale-98"
                class="hidden lg:flex flex-col gap-3 rounded-2xl border border-line-hair/80 bg-white/95 px-4 py-3 sm:px-5 sm:py-3.5 shadow-[0_16px_40px_-12px_rgba(20,58,0,0.22)] backdrop-blur-lg scroll-mt-24 lg:flex-row lg:items-center lg:justify-between transition-all duration-300">

                <div class="flex shrink-0 items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand text-accent shadow-sm">
                        <x-gpa.icon name="cart" class="h-5 w-5" />
                    </div>
                    <div class="flex flex-col min-w-fit">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-accent/30 px-2.5 py-0.5 text-[10px] font-bold text-brand uppercase tracking-wider">
                                <span class="h-1.5 w-1.5 rounded-full bg-brand animate-pulse"></span>
                                Draft PO Aktif · <span x-text="draftCount()"></span> Komoditas
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2 mt-0.5 whitespace-nowrap">
                            <span class="font-sans text-xl font-extrabold text-ink leading-tight tabular-nums">
                                <span x-text="draftWeight()"></span> kg
                            </span>
                            <span class="text-ink-quiet/50 text-xs font-semibold">·</span>
                            <span class="font-sans text-sm font-bold text-success-deep leading-tight tabular-nums">
                                Total <span x-text="money(draftTotal())"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto py-1 px-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <template x-for="line in draft" :key="`draft-${line.key}`">
                        <span
                            class="inline-flex shrink-0 items-center gap-2 rounded-full border border-line-hair bg-canvas px-3 py-1.5 text-xs text-ink shadow-xs transition-colors hover:border-line-strong hover:bg-white">
                            <span class="max-w-[140px] truncate font-medium text-ink sm:max-w-[180px]" x-text="line.name"></span>
                            <span class="rounded-full bg-accent/40 px-2 py-0.5 font-mono text-[10px] font-bold text-brand tabular-nums" x-text="`${line.qty} kg`"></span>
                            <button type="button" @click="removeFromDraft(line.key)" aria-label="Hapus dari draft"
                                class="-mr-1 flex h-4 w-4 items-center justify-center rounded-full text-ink-quiet transition-colors hover:bg-danger/10 hover:text-danger">
                                <x-gpa.icon name="x" class="h-3 w-3" />
                            </button>
                        </span>
                    </template>
                </div>

                <div class="flex shrink-0 items-center justify-end">
                    <a href="{{ route('klien.keranjang') }}"
                        class="group inline-flex w-full sm:w-auto items-center justify-center gap-2.5 rounded-xl bg-brand px-5 py-3 text-xs font-bold text-white shadow-md transition-all hover:bg-brand-hover hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                        <x-gpa.icon name="cart" class="h-4 w-4 text-accent transition-transform group-hover:scale-110" />
                        <span class="font-sans font-bold text-white">Lanjut ke Keranjang PO</span>
                        <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 text-white/70 transition-transform group-hover:translate-x-0.5" />
                    </a>
                </div>
            </section>

            {{-- Mobile floating bar + bottom sheet --}}
            <div class="lg:hidden pb-24">
                <div class="fixed inset-x-0 bottom-0 z-40 px-3 pb-[calc(env(safe-area-inset-bottom,0)+0.75rem)]"
                    x-show="!mobileCartOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-full opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-y-0 opacity-100"
                    x-transition:leave-end="translate-y-full opacity-0">
                    <button type="button" @click="mobileCartOpen = true"
                        class="mx-auto flex w-full max-w-md items-center justify-between gap-3 rounded-full border border-brand-strong/20 bg-brand-deep px-4 py-2.5 text-white shadow-pop">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="cart" class="h-4 w-4" />
                            <span class="text-xs font-semibold">
                                <span x-text="draftCount()"></span> Item • <span x-text="draftWeight()"></span> kg • <span x-text="money(draftTotal())"></span>
                            </span>
                        </div>
                        <span class="flex items-center gap-1 text-xs font-bold">
                            Lihat Draft PO
                            <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.5 2.25L8.25 6L4.5 9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>
                </div>

                <div x-show="mobileCartOpen" x-cloak class="fixed inset-0 z-50">
                    <div class="absolute inset-0 bg-black/60 transition-opacity duration-300"
                        x-show="mobileCartOpen"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="mobileCartOpen = false"></div>
                    <div class="absolute inset-x-0 bottom-0 flex max-h-[90vh] flex-col rounded-t-2xl bg-surface shadow-2xl will-change-transform"
                        x-show="mobileCartOpen"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-y-full"
                        x-transition:enter-end="translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full">
                        <div class="flex items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                            <div class="flex items-center gap-2">
                                <x-gpa.icon name="cart" class="h-4 w-4 text-brand" />
                                <h3 class="gpa-section-title text-ink">Draft PO</h3>
                                <span class="rounded-full bg-accent/40 px-2 py-0.5 gpa-micro-bold text-brand">
                                    <span x-text="draftCount()"></span> Item
                                </span>
                            </div>
                            <button type="button" @click="mobileCartOpen = false" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-surface-shell">
                                <x-gpa.icon name="x" class="h-4 w-4" />
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto px-4 py-3">
                            <ul class="space-y-2">
                                <template x-for="line in draft" :key="line.key">
                                    <li class="rounded-lg border border-line-hair bg-white p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-ink" x-text="line.name"></p>
                                            <p class="text-sm font-bold text-success-deep" x-text="money(line.total)"></p>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="gpa-micro text-ink-body"><span x-text="line.qty"></span> kg</span>
                                            </div>
                                            <button type="button" @click="removeFromDraft(line.key); if(draftCount()===0) mobileCartOpen=false" class="gpa-micro-bold text-danger">Hapus</button>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div class="border-t border-line-hair px-4 py-3 pb-[calc(env(safe-area-inset-bottom,0)+0.75rem)]">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="gpa-body text-ink-body">Total (<span x-text="draftWeight()"></span> kg)</span>
                                <span class="text-base font-extrabold text-success-deep" x-text="money(draftTotal())"></span>
                            </div>
                            <a href="{{ route('klien.keranjang') }}" @click="mobileCartOpen = false"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-4 py-2.5 text-sm font-bold text-white">
                                Lanjut ke Keranjang PO
                                <x-gpa.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

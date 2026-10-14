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
            'category_label'     => 'Komoditas Pertanian GPA',
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
                    'label' => 'Min Order (MOQ)',
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
        <section class="gpa-card flex flex-col gap-4 p-5 lg:flex-row lg:items-start lg:justify-between lg:gap-6 lg:p-6">
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
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan kuota / kredit / dispatch / rulebook --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="gpa-card flex flex-col justify-between gap-4 p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Serapan Kuota Kontrak Bulanan</h2>
                        <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="flex flex-wrap items-baseline gap-1 pt-1 text-ink">
                        <span class="gpa-figure">{{ number_format($usedQuota, 0, ',', '.') }}</span>
                        <span class="font-inter text-sm text-ink-body">/ {{ number_format($totalCommittedQuota, 0, ',', '.') }} kg</span>
                    </p>
                    <p class="gpa-body font-bold text-success-deep">
                        {{ number_format($quotaPercent, 1, ',', '') }}% terserap
                    </p>
                </div>
                <div class="flex flex-col gap-1.5 border-t border-line-soft/60 pt-3">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-surface-pill" role="img"
                        aria-label="Kuota terserap {{ number_format($quotaPercent, 1, ',', '') }} persen">
                        <div class="h-full rounded-full bg-success-deep"
                            style="width: {{ $quotaBarWidth }}"></div>
                    </div>
                    <p class="gpa-micro text-ink-body">Reset otomatis setiap tanggal 1 kalender operasional.</p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Plafon Kredit &amp; Termin Tempo</h2>
                        <x-gpa.icon name="wallet" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-ink">Rp {{ number_format($creditAvailable, 0, ',', '.') }}</p>
                    <p class="gpa-body text-ink-body">
                        Alokasi plafon total:
                        <span class="font-medium text-ink">Rp {{ number_format($creditLimit, 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <p class="gpa-micro-bold text-success-deep">Status: {{ $creditAvailable > 0 ? 'Lancar / Kuota Tersedia' : 'Plafon Penuh / Perlu Pembayaran' }}</p>
                    <span
                        class="rounded bg-surface-pill px-2 py-0.5 gpa-micro-bold text-ink-body">
                        {{ $isB2b ? 'TOP 30 Hari' : 'H+0 Cash/Transfer' }}
                    </span>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Pengiriman Terdekat</h2>
                        <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <p class="gpa-figure text-ink">{{ $lastActiveOrder ? $lastActiveOrder->order_number : 'Belum Ada' }}</p>
                        <span
                            class="rounded-md bg-accent px-2 py-0.5 ring-1 ring-inset ring-accent-edge gpa-meta-lg font-bold text-success-ink">
                            {{ $lastActiveOrder ? number_format($lastActiveOrder->orderItems()->sum('ordered_qty'), 0, ',', '.') . ' kg' : '0 kg' }}
                        </span>
                    </div>
                    <p class="gpa-body text-ink-body">Tujuan: {{ $user->address ?: 'Gudang Utama Klien' }}</p>
                </div>
                <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <p class="gpa-meta-lg font-bold text-ink">ETA: {{ $lastActiveOrder ? ($lastActiveOrder->target_delivery_date ? $lastActiveOrder->target_delivery_date->format('d M Y') . ' 05:30 WIB' : 'Besok 05:30 WIB') : 'Standby' }}</p>
                    <p class="gpa-micro-bold text-success-deep">Reefer 4°C Stabil</p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 bg-brand p-5 ring-1 ring-inset ring-accent/30">
                <div class="flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-accent">SOP Pemesanan &amp; Alokasi Petani</h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-accent" />
                    </div>
                    <ul class="flex flex-col gap-1.5">
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check" class="mt-0.5 h-3 w-3 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">
                                <span class="gpa-micro-bold text-accent">Rule 01:</span>
                                Order cut-off pukul 16:00 WIB untuk pengiriman H+1 subuh.
                            </p>
                        </li>
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check" class="mt-0.5 h-3 w-3 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">
                                <span class="gpa-micro-bold text-accent">Rule 02:</span>
                                Harga kontrak terkunci mengikat tanpa fluktuasi harian.
                            </p>
                        </li>
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check" class="mt-0.5 h-3 w-3 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">
                                <span class="gpa-micro-bold text-accent">Rule 03:</span>
                                Penimbangan neto sah dilakukan di packing house dengan bukti timbang digital.
                            </p>
                        </li>
                    </ul>
                </div>
                <p class="gpa-micro border-t border-white/10 pt-3 text-white/60">Ketentuan mengacu pada Perjanjian Kerjasama Pasokan Komoditas GPA.</p>
            </article>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Filter kategori + sentra + pencarian --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-3 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="category = 'all'"
                    :class="category === 'all'
                        ? 'border-ink bg-ink text-white'
                        : 'border-line-soft bg-surface-pill text-ink-body hover:border-line-soft hover:text-ink'"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 gpa-meta-lg transition-colors">
                    <span>SEMUA</span>
                    <span class="rounded px-1 text-[10px] leading-3 gpa-micro"
                        :class="category === 'all' ? 'bg-accent text-success-ink' : 'bg-surface-disabled text-ink-body'"
                        x-text="{{ count($commodities) }}"></span>
                </button>
                <button type="button" @click="category = 'sayur-daun'"
                    :class="category === 'sayur-daun'
                        ? 'border-ink bg-ink text-white'
                        : 'border-line-soft bg-surface-pill text-ink-body hover:border-line-soft hover:text-ink'"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 gpa-meta-lg transition-colors">
                    <span>SAYURAN SEGAR</span>
                    <span class="rounded px-1 text-[10px] leading-3 gpa-micro"
                        :class="category === 'sayur-daun' ? 'bg-accent text-success-ink' : 'bg-surface-disabled text-ink-body'"
                        x-text="{{ count($commodities) }}"></span>
                </button>

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
                    <option value="all">Semua Sentra Panen (Ciwidey &amp; Lembang Agro-Hub)</option>
                    <option value="lembang">Sentra Panen Lembang (Ketinggian 1.200 mdpl)</option>
                    <option value="ciwidey">Sentra Panen Ciwidey (Ketinggian 1.400 mdpl)</option>
                    <option value="pangalengan">Sentra Pangalengan (Buffer Stock Gudang)</option>
                </select>

                <label class="sr-only" for="filter-urutkan">Urutkan komoditas</label>
                <select id="filter-urutkan" x-model="sort" class="gpa-control lg:max-w-[17rem]">
                    <option value="name-asc">Nama Komoditas (A - Z)</option>
                    <option value="price-asc">Harga Termurah</option>
                    <option value="price-desc">Harga Tertinggi</option>
                    <option value="stock-desc">Stok Tersedia Terbanyak</option>
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
                        <div class="relative h-40 w-full overflow-hidden rounded-lg border border-line-hair bg-surface-muted">
                            <img :src="card.image_url" :alt="card.name"
                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                loading="lazy"
                                x-on:error="$event.target.src = '{{ asset('gpaleaves.png') }}'">
                        </div>

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
        {{-- Ringkasan draft PO (mengikuti badge Keranjang di sidebar) --}}
        {{-- ---------------------------------------------------------------- --}}
        <div id="quick-reorder" class="scroll-mt-24">
            <section x-show="draftCount() > 0" x-cloak
                class="gpa-card sticky bottom-4 flex flex-col gap-3 border border-brand-line p-4 shadow-pop lg:flex-row lg:items-center lg:justify-between">
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

                    <a href="{{ route('klien.cart') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-ink px-4 py-2.5 shadow-sub transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="cart" class="h-3.5 w-3.5 text-white" />
                        <span class="gpa-meta-lg font-bold text-white">Lanjut ke Keranjang PO</span>
                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection

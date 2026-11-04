@extends('layouts.klien')

@section('title', 'Katalog Komoditas & Kuota Pasokan Terikat B2B')

@php
    $user = auth()->user();
    $activeContractFirst = $activeContracts->first();
    $quotaPercent = $quotaPercent ?? ($totalCommittedQuota > 0 ? min(100, ($usedQuota / $totalCommittedQuota) * 100) : 0);
    $creditAvailable = $creditAvailable ?? max(0, $creditLimit - $unpaidTotal);
    $creditPercent = $creditPercent ?? ($creditLimit > 0 ? ($creditAvailable / $creditLimit) * 100 : 0);
    $quotaBarWidth = $quotaBarWidth ?? (max(0, min(100, $quotaPercent)) . '%');
    $commodities = $commodities ?? [];
@endphp

@section('content')
    <div x-data="clientCatalog(@js($commodities))" class="flex flex-col gap-5">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Kop halaman  --}}
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
                    Ketersediaan panen langsung dari Petani Binaan &amp; Buffer Stock resmi Green Pasundan
                    Agriculture. Harga franco gudang penerima, terintegrasi dengan validasi kuota kontrak B2B
                    dan jadwal pengiriman subuh.
                </p>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan kuota / kredit / dispatch / panduan --}}
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
                                <span class="gpa-micro-bold text-accent">Ketentuan 1:</span>
                                Order cut-off pukul 16:00 WIB untuk pengiriman H+1 subuh.
                            </p>
                        </li>
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check" class="mt-0.5 h-3 w-3 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">
                                <span class="gpa-micro-bold text-accent">Ketentuan 2:</span>
                                Harga kontrak terkunci mengikat tanpa fluktuasi harian.
                            </p>
                        </li>
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check" class="mt-0.5 h-3 w-3 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">
                                <span class="gpa-micro-bold text-accent">Ketentuan 3:</span>
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

            <div class="flex flex-col gap-2.5 border-t border-line-hair pt-3 lg:flex-row lg:items-center">
                <label class="sr-only" for="filter-urutkan">Urutkan komoditas</label>
                <div class="relative inline-flex h-10 items-center rounded-full border border-line-soft bg-surface px-3.5 shadow-xs transition-colors hover:border-line-board focus-within:border-brand lg:w-72">
                    <x-gpa.icon name="filter" class="mr-2 h-4 w-4 shrink-0 text-ink-body" />
                    <select id="filter-urutkan" x-model="sort" class="w-full cursor-pointer border-0 bg-transparent py-0 pl-0 pr-6 text-xs font-semibold text-ink focus:ring-0">
                        <option value="name-asc">Nama Komoditas (A - Z)</option>
                        <option value="price-asc">Harga Termurah</option>
                        <option value="price-desc">Harga Tertinggi</option>
                        <option value="stock-desc">Stok Tersedia Terbanyak</option>
                    </select>
                </div>

                <div class="relative flex-1">
                    <x-gpa.icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-body" />
                    <label class="sr-only" for="filter-pencarian">Cari komoditas</label>
                    <input id="filter-pencarian" type="search" x-model.debounce.200ms="query"
                        placeholder="Cari nama komoditas, grade, atau ID sentra panen..."
                        class="h-10 w-full rounded-full border border-line-soft bg-surface pl-10 pr-4 text-xs font-medium text-ink placeholder:text-ink-subtle shadow-xs transition-colors focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
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
                                    :class="card.stock <= 0 ? 'bg-surface-disabled text-ink-muted hover:bg-surface-disabled' : 'bg-accent text-ink hover:bg-accent-deep'"
                                    class="inline-flex min-w-[9rem] flex-1 items-center justify-center gap-2 rounded px-3 py-2.5 shadow-sub transition-colors">
                                    <x-gpa.icon name="cart" class="h-3.5 w-3.5" />
                                    <span class="gpa-meta-lg font-bold" x-text="card.stock <= 0 ? 'Stok Habis' : 'Tambah Draft'"></span>
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
                        Fasilitas Eksklusif Mitra Kontrak B2B
                    </span>
                    <h2 class="font-sans text-lg font-extrabold leading-6 text-white">Kebutuhan Pasokan Ad-Hoc &amp; Skala Tonase Khusus</h2>
                    <p class="max-w-2xl text-sm leading-5 text-white/80">Khusus bagi mitra kontrak resmi Koperasi GPA: Nikmati fasilitas alokasi buffer panen darurat 24 jam dan pasokan tonase skala besar di luar kuota harian dengan harga terikat.</p>

                    <ul class="mt-1 flex flex-col gap-1.5">
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check-circle" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">Jaminan kuota buffer darurat 24 jam tanpa terpengaruh fluktuasi harga pasar spot</p>
                        </li>
                        <li class="flex items-start gap-2">
                            <x-gpa.icon name="check-circle" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-accent" />
                            <p class="gpa-body text-white/90">Pengiriman armada chiller berpendingin khusus langsung ke sentra produksi Anda</p>
                        </li>
                    </ul>
                </div>

                <div class="flex shrink-0 flex-col items-start gap-2 lg:items-end">
                    <button type="button" @click="openAdHocModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-ink shadow-pop transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="sparkles" class="h-4 w-4" />
                        <span class="gpa-meta-lg font-bold">Pelajari Fasilitas Kontrak</span>
                    </button>
                    <p class="gpa-micro text-white/60">Fasilitas Khusus Mitra Kontrak B2B Aktif</p>
                </div>
            </div>
        </section>



        {{-- ---------------------------------------------------------------- --}}
        {{-- Ringkasan draft PO (Floating Dock Modern & Rapi) --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="quick-reorder" x-show="draftCount() > 0" x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-6 scale-98"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-6 scale-98"
            class="sticky bottom-4 z-30 flex flex-col gap-3 rounded-2xl border border-line-hair/80 bg-white/95 px-4 py-3 sm:px-5 sm:py-3.5 shadow-[0_16px_40px_-12px_rgba(20,58,0,0.22)] backdrop-blur-lg scroll-mt-24 lg:flex-row lg:items-center lg:justify-between transition-all duration-300">
            
            {{-- Sisi Kiri: Ringkasan Total & Bobot (Solid, Bebas Macet/Wrap) --}}
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

            {{-- Sisi Tengah: Chip Preview Komoditas (Scrollable Horizontal & Rapi) --}}
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

            {{-- Sisi Kanan: Tombol Aksi Lanjut ke Keranjang --}}
            <div class="flex shrink-0 items-center justify-end">
                <a href="{{ route('klien.keranjang') }}"
                    class="group inline-flex w-full sm:w-auto items-center justify-center gap-2.5 rounded-xl bg-brand px-5 py-3 text-xs font-bold text-white shadow-md transition-all hover:bg-brand-hover hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">
                    <x-gpa.icon name="cart" class="h-4 w-4 text-accent transition-transform group-hover:scale-110" />
                    <span class="font-sans font-bold text-white">Lanjut ke Keranjang PO</span>
                    <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 text-white/70 transition-transform group-hover:translate-x-0.5" />
                </a>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- MODAL PENGAJUAN PASOKAN KHUSUS (AD-HOC SKALA TONASE KHUSUS)      --}}
        {{-- ---------------------------------------------------------------- --}}
        {{-- ---------------------------------------------------------------- --}}
        {{-- MODAL PROMOSI FASILITAS EKSKLUSIF KEMITRAAN KONTRAK B2B           --}}
        {{-- ---------------------------------------------------------------- --}}
        <div x-cloak x-show="adHocOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-ink/60 backdrop-blur-sm transition-opacity" @click="adHocOpen = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
                    @click.outside="adHocOpen = false">
                    
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between border-b border-line-soft pb-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand text-accent">
                                <x-gpa.icon name="shield" class="h-5 w-5" />
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-sans text-base font-bold text-ink">Fasilitas Eksklusif Mitra Kontrak B2B</h3>
                                    <span class="rounded bg-accent px-2 py-0.5 text-[10px] font-bold text-success-ink">Program Eksekutif</span>
                                </div>
                                <p class="text-xs text-ink-body">Jaminan suplai buffer panen darurat 24 jam &amp; termin tempo 30 hari</p>
                            </div>
                        </div>
                        <button type="button" @click="adHocOpen = false" class="rounded-lg p-1 text-ink-body hover:bg-surface-shell hover:text-ink">
                            <x-gpa.icon name="x" class="h-5 w-5" />
                        </button>
                    </div>

                    {{-- Konten Promosi & Fasilitas Kontrak --}}
                    <div class="mt-4 flex flex-col gap-4">
                        {{-- Status Akun Pengguna Saat Ini --}}
                        <div class="rounded-xl border border-line-soft bg-surface-shell/80 p-4">
                            <div class="flex items-center justify-between">
                                <span class="gpa-micro text-ink-body font-semibold">STATUS AKUN ANDA</span>
                                <span class="rounded {{ $isB2b ? 'bg-accent/30 text-success-ink' : 'bg-surface-pill text-ink-body' }} px-2 py-0.5 text-[10px] font-bold">
                                    {{ $isB2b ? 'Mitra Kontrak B2B Aktif' : 'Klien Reguler Harian' }}
                                </span>
                            </div>
                            <p class="mt-1 font-sans text-sm font-bold text-ink">
                                {{ $user->company_name ?: $user->name }}
                            </p>
                            <p class="mt-0.5 text-xs text-ink-body">
                                @if ($isB2b)
                                    Akun Anda memiliki hak akses penuh ke fasilitas pembukaan alokasi buffer panen darurat dan tonase skala besar.
                                @else
                                    Akun Anda saat ini berada pada skema reguler harian. Tingkatkan ke kemitraan kontrak untuk membuka plafon tempo dan alokasi buffer darurat.
                                @endif
                            </p>
                        </div>

                        {{-- 4 Pilar Keuntungan Kemitraan Kontrak B2B --}}
                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                            <div class="flex items-start gap-2.5 rounded-xl border border-line-soft p-3 bg-white">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                    <x-gpa.icon name="check-circle" class="h-4 w-4" />
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold text-ink">Buffer Panen 24 Jam</h4>
                                    <p class="mt-0.5 text-[11px] text-ink-body leading-4">Jaminan alokasi darurat skala 5–20 Ton saat terjadi lonjakan kebutuhan dapur sentral.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 rounded-xl border border-line-soft p-3 bg-white">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                    <x-gpa.icon name="gauge" class="h-4 w-4" />
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold text-ink">Harga Mengikat (Fixed Price)</h4>
                                    <p class="mt-0.5 text-[11px] text-ink-body leading-4">Bebas dari fluktuasi harga pasar sayuran dengan termin pembayaran TOP 30 hari.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 rounded-xl border border-line-soft p-3 bg-white">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                    <x-gpa.icon name="truck" class="h-4 w-4" />
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold text-ink">Sortasi Grade Super</h4>
                                    <p class="mt-0.5 text-[11px] text-ink-body leading-4">Prioritas sortir panen pertama greenhouse binaan dengan armada chiller thermo king.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 rounded-xl border border-line-soft p-3 bg-white">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                    <x-gpa.icon name="badge-check" class="h-4 w-4" />
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold text-ink">Dedicated Account Executive</h4>
                                    <p class="mt-0.5 text-[11px] text-ink-body leading-4">Layanan personal 24/7 via WhatsApp desk &amp; telepon untuk kepastian jadwal supply.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Kotak Ajakan / Kontak Resmi --}}
                        <div class="rounded-xl bg-accent-soft/40 p-4 text-xs text-ink border border-accent-edge">
                            <div class="flex items-center gap-2 font-bold text-success-deep">
                                <x-gpa.icon name="message-circle" class="h-4 w-4" />
                                <span>Konsultasi &amp; Penandatanganan PKS Kemitraan</span>
                            </div>
                            <p class="mt-1 text-ink-body">
                                @if ($isB2b)
                                    Untuk aktivasi alokasi pasokan ad-hoc atau penambahan tonase kuota darurat hari ini, hubungi Account Executive B2B GPA Anda secara langsung:
                                @else
                                    Ingin menikmati harga mengikat, termin pembayaran tempo 30 hari, dan buffer panen darurat untuk bisnis Horeka / Retail Anda? Hubungi desk kemitraan B2B kami:
                                @endif
                            </p>
                            <p class="mt-2 font-mono text-xs font-bold text-ink">
                                Hotline Kemitraan: <span class="text-brand">+62 811-2345-6789</span> (Senin - Sabtu, 24 Jam B2B On-Call)
                            </p>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-line-soft pt-4">
                        <button type="button" @click="adHocOpen = false"
                            class="rounded-lg border border-line-soft px-4 py-2 text-xs font-bold text-ink transition hover:bg-surface-muted">
                            Tutup
                        </button>
                        <a href="https://wa.me/{{ config('app.contact_wa', '6281112233445') }}?text={{ urlencode($isB2b ? 'Halo Account Executive Koperasi GPA, saya dari ' . ($user->company_name ?: $user->name) . ' (Mitra Kontrak B2B). Saya ingin konsultasi aktivasi buffer pasokan panen darurat / penambahan skala tonase khusus.' : 'Halo Tim Kemitraan Koperasi GPA, saya dari ' . ($user->company_name ?: $user->name) . '. Kami tertarik untuk berkonsultasi mengenai persyaratan upgrade ke Kemitraan Kontrak B2B (Fasilitas Buffer Panen & Tempo TOP 30 Hari).') }}"
                            target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                            <x-gpa.icon name="message-circle" class="h-4 w-4" />
                            <span>{{ $isB2b ? 'Hubungi Account Executive via WhatsApp' : 'Konsultasi Kemitraan Kontrak B2B' }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

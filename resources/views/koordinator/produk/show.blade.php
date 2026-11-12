@extends('layouts.koordinator')

@section('title', 'Detail Komoditas // Koordinator')

@section('content')
    <div class="mx-auto flex max-w-shell flex-col gap-6">
        {{-- Header --}}
        <section class="flex flex-col gap-4">
            <header class="flex flex-col gap-4 rounded-2xl bg-surface p-4 sm:p-6 shadow-card lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Manajemen Komoditas</p>
                    <h1 class="font-sans text-xl font-bold leading-7 tracking-[-0.01em] text-ink sm:text-2xl sm:leading-8 xl:text-[28px]">
                        Detail Komoditas
                    </h1>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('koordinator.produk.index') }}"
                        class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 sm:px-3 sm:py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 shrink-0" />
                        Kembali
                    </a>
                    <a href="{{ route('koordinator.produk.edit', $product) }}"
                        class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 sm:px-3 sm:py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="settings" class="h-3.5 w-3.5 shrink-0" />
                        Edit
                    </a>
                    <a href="{{ route('koordinator.stok') }}?product_id={{ $product->id }}#form-intake"
                        class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 sm:px-3 sm:py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                        Input Pasokan
                    </a>
                </div>
            </header>
        </section>

        {{-- Detail Spesifikasi Komoditas & Ringkasan Stok --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri: Informasi Produk --}}
            <section class="lg:col-span-1 rounded-2xl bg-surface shadow-card overflow-hidden">
                <header class="flex items-start justify-between gap-3 border-b border-line-board/60 px-4 py-3 sm:px-6 sm:py-4">
                    <div class="flex items-start gap-2 sm:gap-3 min-w-0">
                        <span class="mt-0.5 shrink-0 rounded px-1.5 py-0.5 text-[9px] sm:text-[10px] font-bold leading-[14px] sm:leading-[15px] gpa-micro bg-surface-track text-ink-body">{{ $product->sku }}</span>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-base font-bold leading-5 sm:leading-6 text-ink">{{ $product->name }}</h2>
                            <p class="mt-0.5 text-[10px] sm:text-[11px] leading-4 text-ink-body">{{ $product->grade ?? '—' }} · {{ $product->unit }}</p>
                        </div>
                    </div>
                    @if ($product->is_active)
                        <span class="inline-block rounded px-1.5 sm:px-2 py-0.5 text-[9px] sm:text-[10px] font-bold leading-[14px] sm:leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep shrink-0">AKTIF</span>
                    @else
                        <span class="inline-block rounded px-1.5 sm:px-2 py-0.5 text-[9px] sm:text-[10px] font-bold leading-[14px] sm:leading-[15px] gpa-meta bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board shrink-0">NONAKTIF</span>
                    @endif
                </header>

                <div class="px-4 py-4 sm:px-6 sm:py-5 space-y-4 sm:space-y-5">
                    <div class="flex justify-center">
                        @if ($product->image_path || $product->image_url)
                            <img src="{{ asset($product->image_path ?: $product->image_url) }}" alt="{{ $product->name }}" class="h-32 w-32 sm:h-40 sm:w-40 rounded-xl border border-line-board object-cover" onerror="this.onerror=null;this.src='{{ asset('gpaleaves.png') }}';">
                        @else
                            <div class="h-32 w-32 sm:h-40 sm:w-40 rounded-xl border border-line-board bg-surface-shell flex items-center justify-center">
                                <x-gpa.icon name="image" class="h-6 w-6 sm:h-8 sm:w-8 text-ink-body" />
                            </div>
                        @endif
                    </div>

                    <div class="divide-y divide-line-hair">
                        <div class="grid grid-cols-2 gap-2 py-2">
                            <dt class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kode SKU</dt>
                            <dd class="text-[12px] leading-5 text-ink font-mono text-right">{{ $product->sku }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-2 py-2">
                            <dt class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Grade Mutu</dt>
                            <dd class="text-[12px] leading-5 text-ink text-right">{{ $product->grade ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-2 py-2">
                            <dt class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Satuan</dt>
                            <dd class="text-[12px] leading-5 text-ink text-right">{{ $product->unit }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-2 py-2">
                            <dt class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Harga Dasar</dt>
                            <dd class="text-[12px] leading-5 text-ink font-mono text-right">Rp {{ number_format($product->base_price, 0, ',', '.') }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-2 py-2">
                            <dt class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Min. Pemesanan</dt>
                            <dd class="text-[12px] leading-5 text-ink font-mono text-right">{{ number_format($product->minimum_order, 1, ',', '.') }} {{ $product->unit }}</dd>
                        </div>
                    </div>

                    @if ($product->description)
                        <div class="space-y-2">
                            <p class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Deskripsi / Spesifikasi</p>
                            <p class="text-[12px] leading-5 text-ink whitespace-pre-line">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>

                <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line-board/60 px-6 py-3">
                    <a href="{{ route('koordinator.produk.edit', $product) }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="settings" class="h-3.5 w-3.5 shrink-0" />
                        Edit Komoditas
                    </a>
                </footer>
            </section>

            {{-- Kolom Kanan: Status Stok & Riwayat Batch Pasokan --}}
            <div class="lg:col-span-2 space-y-6">
                @php
                    $binaanStock = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
                    $bufferStock = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
                @endphp
                {{-- Metrik Stok Produk --}}
                <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-4 sm:p-5 shadow-card">
                        <div class="flex flex-col gap-2 sm:gap-3">
                            <h3 class="gpa-micro-bold text-ink-body leading-tight">STOK TERSEDIA</h3>
                            <p class="gpa-figure text-[18px] leading-6 sm:text-[22px] sm:leading-7 text-ink">{{ number_format($availableStock ?? 0, 1, ',', '.') }} <span class="text-sm sm:text-base font-semibold">kg</span></p>
                            <p class="text-[10px] leading-3 sm:text-[11px] sm:leading-4 text-ink-body">Total tersedia saat ini</p>
                        </div>
                    </article>
                    <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-4 sm:p-5 shadow-card">
                        <div class="flex flex-col gap-2 sm:gap-3">
                            <h3 class="gpa-micro-bold text-ink-body leading-tight">DARI PETANI BINAAN</h3>
                            <p class="gpa-figure text-[18px] leading-6 sm:text-[22px] sm:leading-7 text-ink">{{ number_format($binaanStock, 1, ',', '.') }} <span class="text-sm sm:text-base font-semibold">kg</span></p>
                            <p class="text-[10px] leading-3 sm:text-[11px] sm:leading-4 text-ink-body">Pasokan inti mitra</p>
                        </div>
                    </article>
                    <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-4 sm:p-5 shadow-card">
                        <div class="flex flex-col gap-2 sm:gap-3">
                            <h3 class="gpa-micro-bold text-ink-body leading-tight">DARI BUFFER LUAR</h3>
                            <p class="gpa-figure text-[18px] leading-6 sm:text-[22px] sm:leading-7 text-ink">{{ number_format($bufferStock, 1, ',', '.') }} <span class="text-sm sm:text-base font-semibold">kg</span></p>
                            <p class="text-[10px] leading-3 sm:text-[11px] sm:leading-4 text-ink-body">Cadangan penyeimbang</p>
                        </div>
                    </article>
                </section>

                {{-- Riwayat Batch Pasokan Panen --}}
                <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
                    <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-4 py-3 sm:px-6 sm:py-4">
                        <div class="min-w-0">
                            <h2 class="gpa-section-title text-ink">Riwayat Batch Pasokan Panen</h2>
                            <p class="mt-1 text-xs text-ink-body">{{ $product->harvestBatches->count() }} lot tercatat</p>
                        </div>
                        <a href="{{ route('koordinator.stok') }}?product_id={{ $product->id }}#form-intake"
                            class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 sm:px-3 sm:py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                            <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                            Tambah Batch
                        </a>
                    </header>

                    {{-- Desktop Table --}}
                    <div class="hidden md:block gpa-scroll-x overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-line-board">
                                    <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">ID Batch</th>
                                    <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Tanggal Panen</th>
                                    <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Petani / Kelompok Tani</th>
                                    <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Sumber</th>
                                    <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Qty Awal</th>
                                    <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Tersedia</th>
                                    <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Catatan QC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line-hair">
                                @forelse ($product->harvestBatches as $b)
                                    <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                        <td class="px-3 py-3.5">
                                            <span class="font-mono text-[11px] font-semibold leading-5 text-ink">#{{ $b->id }}</span>
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <span class="text-[11px] leading-4 text-ink-body">{{ $b->batch_date ? $b->batch_date->format('d/m/Y') : '—' }}</span>
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <span class="text-[12px] font-semibold leading-5 text-ink">{{ $b->supplier_name ?: 'Mitra Tani' }}</span>
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                                {{ $b->source_type === 'BUFFER_LUAR' ? 'BUFFER' : 'BINAAN' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-right">
                                            <span class="font-mono text-[11px] leading-5 text-ink-body">{{ number_format($b->initial_quantity, 1, ',', '.') }} kg</span>
                                        </td>
                                        <td class="px-3 py-3.5 text-right">
                                            <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($b->available_quantity, 1, ',', '.') }} kg</span>
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <span class="text-[11px] leading-4 text-ink-body">{{ $b->qc_notes ?: '—' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-10 text-center">
                                            <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                                <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                                                <p class="text-sm font-semibold text-ink">Belum ada batch panen</p>
                                                <p class="text-xs text-ink-body">Batch pasokan akan muncul setelah dilakukan input intake panen.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden flex flex-col gap-2 p-2">
                        @forelse ($product->harvestBatches as $b)
                            <div class="rounded-lg border border-line-hair bg-surface p-3 flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-[11px] font-semibold text-ink">#{{ $b->id }}</span>
                                    <span class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold leading-[14px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                        {{ $b->source_type === 'BUFFER_LUAR' ? 'BUFFER' : 'BINAAN' }}
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-ink">{{ $b->supplier_name ?: 'Mitra Tani' }}</p>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[10px]">
                                    <div class="flex justify-between">
                                        <span class="text-ink-body">Tanggal</span>
                                        <span class="text-ink">{{ $b->batch_date ? $b->batch_date->format('d/m/Y') : '—' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-ink-body">Qty Awal</span>
                                        <span class="font-mono text-ink">{{ number_format($b->initial_quantity, 1, ',', '.') }} kg</span>
                                    </div>
                                    <div class="flex justify-between col-span-2">
                                        <span class="text-ink-body">Tersedia</span>
                                        <span class="font-mono font-semibold text-ink">{{ number_format($b->available_quantity, 1, ',', '.') }} kg</span>
                                    </div>
                                </div>
                                @if ($b->qc_notes)
                                    <div class="text-[10px] text-ink-body pt-1.5 border-t border-line-hair">
                                        <span class="font-semibold text-ink">QC:</span> {{ $b->qc_notes }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                    <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                                    <p class="text-sm font-semibold text-ink">Belum ada batch panen</p>
                                    <p class="text-xs text-ink-body">Batch pasokan akan muncul setelah dilakukan input intake panen.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

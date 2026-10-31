@extends('layouts.koordinator')

@section('title', 'Dasbor Koordinator Lapangan // AgroOrder GPA')

@section('content')
    <div class="mx-auto flex max-w-shell flex-col gap-6">
        {{-- Header Halaman --}}
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Koordinator Lapangan</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        Dasbor Koordinator Lapangan
                    </h1>
                    <p class="text-xs text-ink-body max-w-2xl">
                        Pemantauan persediaan, kebutuhan pasar klien, dan lot pasokan panen untuk perencanaan distribusi.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('koordinator.produk.create') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                        Tambah Komoditas
                    </a>
                    <a href="{{ route('koordinator.stok') }}#form-intake"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                        Catat Pasokan Masuk
                    </a>
                </div>
            </header>

            {{-- Ringkasan Metrik --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">TOTAL STOK GUDANG</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($totalStock, 1, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                        <p class="text-[11px] leading-4 text-ink-body">Tersedia di gudang</p>
                    </div>
                    <div class="border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">Real-time sinkronisasi</span>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">STOK PETANI BINAAN</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($binaanStock, 1, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                        <p class="text-[11px] leading-4 text-ink-body">Pasokan inti mitra</p>
                    </div>
                    <div class="border-t border-surface-track pt-3">
                        <span class="gpa-note font-semibold text-success-deep">Terpantau</span>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">STOK BUFFER LUAR</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($bufferStock, 1, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                        <p class="text-[11px] leading-4 text-ink-body">Penyeimbang fluktuasi</p>
                    </div>
                    <div class="border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">Cadangan</span>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">KOMODITAS AKTIF</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ $activeProductsCount }}</p>
                        <p class="text-[11px] leading-4 text-ink-body">Tampil di katalog klien</p>
                    </div>
                    <div class="border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">Terkatalog</span>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">TOTAL BATCH PANEN</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ $totalIntakeBatches }}</p>
                        <p class="text-[11px] leading-4 text-ink-body">Riwayat pasokan tercatat</p>
                    </div>
                    <div class="border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">Riwayat tercatat</span>
                    </div>
                </article>
            </div>
        </section>

        {{-- BAGIAN 1: Antrean Pesanan Masuk --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Pantau Antrean Pesanan Masuk (Demand Pasar Klien)</h2>
                    <p class="mt-1 text-xs text-ink-body">Pesanan aktif untuk acuan kuota panen koordinator</p>
                </div>
                <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    {{ count($incomingOrders) }} Pesanan Aktif
                </span>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">No. Pesanan</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Klien / Pembeli</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Waktu Order</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Komoditas Dipesan</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Total Kuantitas</th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($incomingOrders as $ord)
                            @php
                                $totalQty = $ord->orderItems->sum('ordered_qty');
                            @endphp
                            <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                <td class="px-3 py-3.5">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ $ord->order_number }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-col gap-0.5">
                                        <p class="text-[12px] font-semibold leading-5 text-ink">{{ $ord->user?->name ?? 'Klien Reguler' }}</p>
                                        <p class="gpa-note leading-4 text-ink-body line-clamp-1">{{ $ord->delivery_address ?: 'Alamat pengiriman' }}</p>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[12px] leading-5 text-ink-body">{{ $ord->created_at ? $ord->created_at->format('d/m/Y H:i') : '—' }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-col gap-0.5">
                                        @foreach ($ord->orderItems as $item)
                                            <p class="text-[11px] leading-4 text-ink">
                                                <span class="font-semibold">{{ $item->product?->name ?? 'Komoditas' }}</span>
                                                <span class="text-ink-body">· {{ number_format($item->ordered_qty, 0, ',', '.') }} {{ $item->product?->unit ?? 'kg' }}</span>
                                            </p>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($totalQty, 0, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <x-gpa.status-badge :status="$ord->status" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Belum ada pesanan aktif</p>
                                        <p class="text-xs text-ink-body">Pesanan masuk akan muncul di sini untuk acuan perencanaan pasokan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- BAGIAN 2: Status Pasokan Komoditas --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Status Pasokan &amp; Stok Komoditas</h2>
                    <p class="mt-1 text-xs text-ink-body">Ketersediaan stok dari petani binaan dan buffer siap kirim</p>
                </div>
                <a href="{{ route('koordinator.stok') }}"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                    Kelola Stok
                    <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 shrink-0" />
                </a>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Komoditas</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Grade</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Stok Binaan</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Stok Buffer</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Total Stok</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kelompok Tani Mitra</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($supplyRows as $row)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                <td class="px-3 py-3.5">
                                    <p class="text-[12px] font-semibold leading-5 text-ink">{{ $row->name ?? $row['name'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-block rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[10px] font-semibold leading-[15px] text-ink outline outline-1 -outline-offset-1 outline-line-board">{{ $row->grade ?? ($row['grade'] ?? 'Grade A') }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] leading-5 text-ink">{{ number_format($row->stock_bud ?? ($row['supply'] ?? 0), 0, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] leading-5 text-ink-body">{{ number_format($row->stock_buffer ?? ($row['buffer'] ?? 0), 0, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($row->stock ?? ($row['total_stock'] ?? 0), 0, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[11px] leading-4 text-ink-body">{{ ($row->supplier_name ?? ($row['group'] ?? '')) ?: '-' }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <a href="{{ route('koordinator.stok') }}?product_id={{ $row->id ?? $row['key'] }}#form-intake"
                                        class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                        <x-gpa.icon name="plus" class="h-3 w-3 shrink-0" />
                                        Tambah Panen
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="grid" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Belum ada komoditas aktif</p>
                                        <p class="text-xs text-ink-body">Tambahkan komoditas pada menu Manajemen Komoditas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- BAGIAN 3: Log Pasokan Panen Terakhir --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Log Pasokan Panen Masuk Terakhir</h2>
                    <p class="mt-1 text-xs text-ink-body">Riwayat pasokan panen terbaru</p>
                </div>
                <a href="{{ route('koordinator.stok') }}"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                    Lihat Semua
                    <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 shrink-0" />
                </a>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">ID Batch</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Waktu Pencatatan</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Komoditas</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Petani / Supplier</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Tipe Sumber</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kuantitas Masuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($gateLogs as $log)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                <td class="px-3 py-3.5">
                                    <span class="font-mono text-[11px] font-semibold leading-5 text-ink">{{ $log->ticket ?? $log['ticket'] }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[11px] leading-4 text-ink-body">{{ $log->time_formatted ?? $log['time'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[12px] font-semibold leading-5 text-ink">{{ $log->commodity ?? $log['commodity'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[11px] leading-4 text-ink-body">{{ $log->name ?? $log['name'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                        {{ strtoupper($log->source_label ?? ($log['note'] ?? '')) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($log->net ?? ($log['net'] ?? 0), 1, ',', '.') }} kg</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Belum ada catatan batch panen</p>
                                        <p class="text-xs text-ink-body">Catatan pasokan panen akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

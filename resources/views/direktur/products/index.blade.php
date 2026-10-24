@extends('layouts.direktur')

@section('title', 'Katalog Komoditas & Manajemen Produk B2B // Direktur AgroOrder GPA')

@section('content')
<div class="mx-auto flex max-w-[1280px] flex-col gap-6" x-data="{
    search: '{{ request('search') }}',
    gradeFilter: '{{ request('grade', 'all') }}',
    statusFilter: '{{ request('status', 'all') }}'
}">
    {{-- Notifikasi Flash --}}
    @if (session('success'))
        <div class="rounded-xl border border-accent bg-accent/20 px-4 py-3 text-xs font-semibold text-success-deep shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-xs font-semibold text-red-800 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-gpa.icon name="alert-circle" class="h-4 w-4 text-red-600 shrink-0" />
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Kop Halaman & Aksi Utama --}}
    <section class="flex flex-col gap-4">
        <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Master Data &amp; Tata Kelola Komoditas</p>
                <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                    Katalog Komoditas &amp; Penetapan Harga Dasar
                </h1>
                <p class="text-xs text-ink-body max-w-2xl">
                    Pusat kendali spesifikasi hortikultura unggulan, foto komoditas, penetapan harga dasar franco, batas minimum order (MOQ), dan status keterikatan pasokan B2B.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('direktur.commodities.export') }}"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0" />
                    Export Laporan (.CSV)
                </a>
                <a href="{{ route('direktur.products.create') }}"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                    Tambah Komoditas Baru
                </a>
            </div>
        </header>

        {{-- Kartu Metrik Ringkasan --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Metrik 1: Total Komoditas --}}
            <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="gpa-micro-bold text-ink-body">Total Komoditas</h3>
                    <span class="rounded p-1 bg-surface-shell text-ink">
                        <x-gpa.icon name="grid" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">{{ $totalProducts }} Komoditas</p>
                    <p class="mt-1 text-[11px] leading-4 text-ink-body">{{ $activeProducts }} Aktif di Katalog B2B</p>
                </div>
            </article>

            {{-- Metrik 2: Ketersediaan Pasokan --}}
            <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="gpa-micro-bold text-ink-body">Ketersediaan Pasokan</h3>
                    <span class="rounded p-1 bg-surface-shell text-ink">
                        <x-gpa.icon name="package" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($totalStockKg, 0, ',', '.') }} kg</p>
                    <p class="mt-1 text-[11px] leading-4 text-ink-body">Buffer &amp; Petani Binaan Siap Kirim</p>
                </div>
            </article>

            {{-- Metrik 3: Rata-rata Harga Dasar --}}
            <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="gpa-micro-bold text-ink-body">Rata-rata Harga Dasar</h3>
                    <span class="rounded p-1 bg-surface-shell text-ink">
                        <x-gpa.icon name="banknote" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">Rp {{ number_format($avgPrice, 0, ',', '.') }}</p>
                    <p class="mt-1 text-[11px] leading-4 text-ink-body">Harga Franco Gudang Ciwidey/Lembang</p>
                </div>
            </article>

            {{-- Metrik 4: Standar Mutu & Metrologi --}}
            <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="gpa-micro-bold text-ink-body">Standar Mutu &amp; Metrologi</h3>
                    <span class="rounded p-1 bg-surface-shell text-ink">
                        <x-gpa.icon name="shield" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">Grade A &amp; Super</p>
                    <p class="mt-1 text-[11px] leading-4 text-ink-body">Sertifikasi Tera Bebas Residu</p>
                </div>
            </article>
        </div>
    </section>

    {{-- Filter & Pencarian --}}
    <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 pb-4">
            <div>
                <h2 class="gpa-section-title text-ink">Daftar Komoditas Pertanian GPA</h2>
                <p class="text-xs text-ink-body">Kelola ketersediaan, foto produk, standar metrologi, dan harga resmi.</p>
            </div>

            <form method="GET" action="{{ route('direktur.products.index') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative flex items-center">
                    <x-gpa.icon name="search" class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                    <input type="search" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, SKU, atau grade..."
                        class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                </div>

                <select name="grade" onchange="this.form.submit()"
                    class="rounded bg-surface-shell py-1.5 px-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-success-deep">
                    <option value="all" {{ request('grade') === 'all' || !request('grade') ? 'selected' : '' }}>Semua Grade</option>
                    <option value="Grade A" {{ request('grade') === 'Grade A' ? 'selected' : '' }}>Grade A</option>
                    <option value="Grade Super" {{ request('grade') === 'Grade Super' ? 'selected' : '' }}>Grade Super</option>
                    <option value="Grade B" {{ request('grade') === 'Grade B' ? 'selected' : '' }}>Grade B</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                    class="rounded bg-surface-shell py-1.5 px-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-success-deep">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                </select>

                <button type="submit"
                    class="rounded bg-ink px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-brand-deep">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'grade', 'status']))
                    <a href="{{ route('direktur.products.index') }}"
                        class="rounded bg-surface-shell px-2.5 py-1.5 text-[11px] font-semibold text-ink-body outline outline-1 -outline-offset-1 outline-line-board hover:text-ink">
                        Reset
                    </a>
                @endif
            </form>
        </header>

        {{-- Tabel Komoditas --}}
        <div class="gpa-scroll-x overflow-x-auto">
            <table class="w-full min-w-[70rem] border-collapse">
                <thead>
                    <tr class="border-b border-line-board">
                        <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-ink-body">Komoditas &amp; Foto</th>
                        <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-ink-body">SKU &amp; Grade</th>
                        <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-ink-body">Harga Dasar (Franco)</th>
                        <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-ink-body">Min. Order (MOQ)</th>
                        <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-ink-body">Stok Riil Panen</th>
                        <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-ink-body">Kontrak Terikat</th>
                        <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-ink-body">Status Katalog</th>
                        <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-ink-body">Aksi Otoritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-hair">
                    @forelse ($products as $prod)
                        @php
                            $availableStock = (float) $prod->harvestBatches->sum('available_quantity');
                            $activeContractsCount = $prod->contracts->where('status', 'ACTIVE')->count();
                        @endphp
                        <tr class="align-middle transition-colors hover:bg-surface-shell/50">
                            {{-- Foto & Nama --}}
                            <td class="px-3 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-line-hair bg-surface-muted shadow-sub">
                                        <img src="{{ asset($prod->image_path) }}" alt="{{ $prod->name }}"
                                            class="h-full w-full object-cover"
                                            x-on:error="$event.target.src = '{{ asset('gpaleaves.png') }}'">
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('direktur.products.show', $prod->id) }}"
                                            class="text-[13px] font-bold leading-snug text-ink hover:text-brand hover:underline">
                                            {{ $prod->name }}
                                        </a>
                                        <p class="mt-0.5 text-[11px] text-ink-body truncate max-w-xs">
                                            {{ $prod->description ?: 'Komoditas hortikultura binaan GPA' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- SKU & Grade --}}
                            <td class="px-3 py-3.5">
                                <p class="gpa-mono-xs font-bold text-ink">{{ $prod->sku }}</p>
                                <span class="mt-1 inline-block rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-brand-deep">
                                    {{ $prod->grade }}
                                </span>
                            </td>

                            {{-- Harga Dasar --}}
                            <td class="px-3 py-3.5">
                                <p class="text-[13px] font-bold text-ink">
                                    Rp {{ number_format($prod->base_price, 0, ',', '.') }}
                                </p>
                                <p class="text-[10px] text-ink-body">/ {{ strtoupper($prod->unit) }}</p>
                            </td>

                            {{-- MOQ --}}
                            <td class="px-3 py-3.5">
                                <p class="gpa-meta font-semibold text-ink">
                                    {{ number_format($prod->minimum_order, 0, ',', '.') }} {{ $prod->unit }}
                                </p>
                            </td>

                            {{-- Stok Riil --}}
                            <td class="px-3 py-3.5">
                                <p class="gpa-meta font-bold {{ $availableStock > 0 ? 'text-success-deep' : 'text-danger' }}">
                                    {{ number_format($availableStock, 0, ',', '.') }} {{ $prod->unit }}
                                </p>
                                <p class="text-[10px] text-ink-body">{{ $prod->harvestBatches->count() }} batch panen</p>
                            </td>

                            {{-- Kontrak Terikat --}}
                            <td class="px-3 py-3.5">
                                <span class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-0.5 gpa-meta font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                    <x-gpa.icon name="badge-check" class="h-3 w-3 text-accent" />
                                    {{ $activeContractsCount }} Kontrak B2B
                                </span>
                            </td>

                            {{-- Status Katalog --}}
                            <td class="px-3 py-3.5">
                                <form action="{{ route('direktur.products.toggle-status', $prod->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        title="Klik untuk beralih status aktif"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-bold transition {{ $prod->is_active ? 'bg-accent text-success-ink hover:opacity-90' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $prod->is_active ? 'bg-success-deep' : 'bg-red-600' }}"></span>
                                        {{ $prod->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </button>
                                </form>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('direktur.products.show', $prod->id) }}"
                                        title="Rincian Komoditas"
                                        class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                        <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                        Detail
                                    </a>

                                    <a href="{{ route('direktur.products.edit', $prod->id) }}"
                                        title="Ubah Data Komoditas"
                                        class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                        <x-gpa.icon name="settings" class="h-3 w-3 shrink-0" />
                                        Edit
                                    </a>

                                    <form action="{{ route('direktur.products.destroy', $prod->id) }}" method="POST" class="inline"
                                        data-gpa-confirm="Apakah Anda yakin ingin menghapus atau menonaktifkan komoditas {{ addslashes($prod->name) }}?"
                                        data-gpa-title="Hapus Komoditas"
                                        data-gpa-tone="danger"
                                        data-gpa-ok="Hapus Komoditas">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            title="Hapus / Nonaktifkan Komoditas"
                                            class="inline-flex items-center gap-1 rounded bg-red-50 text-red-700 px-2 py-1 gpa-note font-semibold outline outline-1 -outline-offset-1 outline-red-200 transition-colors hover:bg-red-100">
                                            <x-gpa.icon name="trash" class="h-3 w-3 shrink-0 text-red-600" />
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-xs text-ink-body">
                                <x-gpa.icon name="package" class="mx-auto h-8 w-8 text-ink-body/40 mb-2" />
                                Tidak ada komoditas yang sesuai kriteria pencarian dan filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 pt-3">
            <p class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-success-deep" aria-hidden="true"></span>
                <span class="gpa-note font-bold text-success-deep">Integritas Standar Metrologi GPA Aktif</span>
            </p>
            <p class="gpa-note text-ink-body">
                Menampilkan <span class="font-bold text-ink">{{ $products->count() }}</span> dari {{ $totalProducts }} komoditas resmi.
            </p>
        </footer>
    </section>
</div>
@endsection

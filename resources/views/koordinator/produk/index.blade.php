@extends('layouts.koordinator')

@section('title', 'Manajemen Komoditas // Koordinator')

@section('content')
    <div class="mx-auto flex max-w-shell flex-col gap-6">
        {{-- Header --}}
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Manajemen Komoditas</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        Daftar Komoditas Katalog
                    </h1>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('koordinator.produk.create') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                        Tambah Komoditas Baru
                    </a>
                </div>
            </header>

            {{-- Filter & Ringkasan --}}
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-surface p-4 shadow-card">
                <form method="GET" action="{{ route('koordinator.produk.index') }}" class="flex flex-wrap items-center gap-2">
                    <label class="relative flex items-center">
                        <span class="sr-only">Cari nama atau SKU</span>
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..."
                            class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </label>

                    <select name="grade" class="rounded bg-surface-shell py-1.5 px-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        <option value="all">Semua Grade</option>
                        @foreach (['Grade A', 'Grade A+', 'Grade B', 'Grade C'] as $g)
                            <option value="{{ $g }}" {{ request('grade') === $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="rounded bg-surface-shell py-1.5 px-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        <option value="all">Semua Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <button type="submit" class="inline-flex items-center gap-1.5 rounded bg-surface-shell px-2.5 py-1.5 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="filter" class="h-3 w-3 shrink-0" />
                        Filter
                    </button>

                    @if (request('search') || (request('grade') && request('grade') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a href="{{ route('koordinator.produk.index') }}" class="gpa-note font-semibold text-ink-body hover:text-ink">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-3 text-[11px] leading-4">
                    <span class="text-ink-body">Total: <span class="font-semibold text-ink">{{ $totalProducts ?? count($products) }}</span></span>
                    <span class="text-ink-body">Aktif: <span class="font-semibold text-success-deep">{{ $activeProducts ?? 0 }}</span></span>
                    <span class="text-ink-body">Stok Fisik: <span class="font-semibold text-ink">{{ number_format($totalStockKg ?? 0, 1, ',', '.') }} kg</span></span>
                </div>
            </div>
        </section>

        {{-- Tabel Komoditas --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-4 py-3 sm:px-6 sm:py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Daftar Komoditas</h2>
                    <p class="mt-1 text-xs text-ink-body">Kelola komoditas yang tampil di katalog klien</p>
                </div>
            </header>

            {{-- Desktop Table --}}
            <div class="hidden md:block gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">No</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Foto</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">SKU</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Nama Komoditas</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Grade</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Satuan</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Harga Dasar</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Min. Order</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Stok Riil</th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Status</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($products as $idx => $prod)
                            @php
                                $stokTersedia = (float) $prod->harvestBatches->sum('available_quantity');
                            @endphp
                            <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                <td class="px-3 py-3.5">
                                    <span class="gpa-note text-ink-body">{{ $idx + 1 }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    @if ($prod->image_path || $prod->image_url)
                                        <img src="{{ asset($prod->image_path ?: $prod->image_url) }}" alt="{{ $prod->name }}" class="h-24 w-24 rounded-lg border border-line-board object-cover" onerror="this.onerror=null;this.src='{{ asset('gpaleaves.png') }}';">
                                    @else
                                        <div class="h-24 w-24 rounded-lg border border-line-board bg-surface-shell flex items-center justify-center">
                                            <x-gpa.icon name="image" class="h-4 w-4 text-ink-body" />
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="font-mono text-[11px] font-semibold leading-5 text-ink">{{ $prod->sku }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <a href="{{ route('koordinator.produk.show', $prod) }}" class="text-[12px] font-semibold leading-5 text-ink hover:underline">
                                        {{ $prod->name }}
                                    </a>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-block rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[10px] font-semibold leading-[15px] text-ink outline outline-1 -outline-offset-1 outline-line-board">{{ $prod->grade ?? '—' }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="text-[11px] leading-4 text-ink-body">{{ $prod->unit }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[11px] leading-5 text-ink">Rp {{ number_format($prod->base_price, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[11px] leading-5 text-ink-body">{{ number_format($prod->minimum_order, 1, ',', '.') }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($stokTersedia, 1, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    @if ($prod->is_active)
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">AKTIF</span>
                                    @else
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <a href="{{ route('koordinator.produk.show', $prod) }}"
                                            title="Lihat Detail Komoditas"
                                            class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                            Detail
                                        </a>
                                        <form method="POST" action="{{ route('koordinator.produk.toggle-status', $prod) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                                {{ $prod->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('koordinator.produk.edit', $prod) }}"
                                            class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="settings" class="h-3 w-3 shrink-0" />
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('koordinator.produk.destroy', $prod) }}" class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus komoditas {{ $prod->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-1 gpa-note font-semibold text-red-700 outline outline-1 -outline-offset-1 outline-red-200 transition-colors hover:bg-red-100">
                                                <x-gpa.icon name="trash" class="h-3 w-3 shrink-0 text-red-600" />
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="grid" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Belum ada komoditas ditemukan</p>
                                        <p class="text-xs text-ink-body">Tambahkan komoditas baru untuk mulai mengelola katalog.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden flex flex-col gap-2 p-2">
                @forelse ($products as $prod)
                    @php
                        $stokTersedia = (float) $prod->harvestBatches->sum('available_quantity');
                    @endphp
                    <div class="rounded-lg border border-line-hair bg-surface p-3 flex flex-col gap-2">
                        <div class="flex items-start gap-2.5">
                            @if ($prod->image_path || $prod->image_url)
                                <img src="{{ asset($prod->image_path ?: $prod->image_url) }}" alt="{{ $prod->name }}" class="h-16 w-16 rounded-lg border border-line-board object-cover shrink-0" onerror="this.onerror=null;this.src='{{ asset('gpaleaves.png') }}';">
                            @else
                                <div class="h-16 w-16 rounded-lg border border-line-board bg-surface-shell flex items-center justify-center shrink-0">
                                    <x-gpa.icon name="image" class="h-6 w-6 text-ink-body" />
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('koordinator.produk.show', $prod) }}" class="text-sm font-semibold text-ink hover:underline">{{ $prod->name }}</a>
                                        <p class="text-[10px] font-mono text-ink-body mt-0.5">{{ $prod->sku }}</p>
                                    </div>
                                    @if ($prod->is_active)
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold leading-[14px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep shrink-0">AKTIF</span>
                                    @else
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold leading-[14px] gpa-meta bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board shrink-0">NONAKTIF</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-block rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[9px] font-semibold leading-[13px] text-ink outline outline-1 -outline-offset-1 outline-line-board">{{ $prod->grade ?? '—' }}</span>
                                    <span class="text-[10px] text-ink-body">{{ $prod->unit }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[10px]">
                            <div class="flex justify-between">
                                <span class="text-ink-body">Harga Dasar</span>
                                <span class="font-mono text-ink">Rp {{ number_format($prod->base_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink-body">Min. Order</span>
                                <span class="font-mono text-ink">{{ number_format($prod->minimum_order, 1, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between col-span-2">
                                <span class="text-ink-body">Stok Riil</span>
                                <span class="font-mono font-semibold text-ink">{{ number_format($stokTersedia, 1, ',', '.') }} kg</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-1.5 border-t border-line-hair">
                            <a href="{{ route('koordinator.produk.show', $prod) }}" class="flex-1 inline-flex items-center justify-center gap-1 rounded bg-surface-shell px-2 py-1.5 text-[10px] font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                <x-gpa.icon name="eye" class="h-3 w-3" />
                                Detail
                            </a>
                            <a href="{{ route('koordinator.produk.edit', $prod) }}" class="flex-1 inline-flex items-center justify-center gap-1 rounded bg-surface-shell px-2 py-1.5 text-[10px] font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                <x-gpa.icon name="settings" class="h-3 w-3" />
                                Edit
                            </a>
                            <form method="POST" action="{{ route('koordinator.produk.toggle-status', $prod) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1 rounded bg-surface-shell px-2 py-1.5 text-[10px] font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                    {{ $prod->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center">
                        <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                            <x-gpa.icon name="grid" class="h-7 w-7 text-ink-body" />
                            <p class="text-sm font-semibold text-ink">Belum ada komoditas ditemukan</p>
                            <p class="text-xs text-ink-body">Tambahkan komoditas baru untuk mulai mengelola katalog.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

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
                        Catat Intake Panen
                    </a>
                </div>
            </header>

            {{-- Ringkasan Metrik --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <article class="col-span-2 xl:col-span-1 flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-4 sm:p-5 shadow-card">
                    <div class="flex flex-col gap-2 sm:gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body leading-tight">TOTAL STOK GUDANG</h3>
                        </div>
                        <p class="gpa-figure text-[18px] leading-6 sm:text-[22px] sm:leading-7 text-ink">{{ number_format($totalStock, 1, ',', '.') }} <span class="text-sm sm:text-base font-semibold">kg</span></p>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-4 sm:p-5 shadow-card">
                    <div class="flex flex-col gap-2 sm:gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body leading-tight">STOK PETANI BINAAN</h3>
                        </div>
                        <p class="gpa-figure text-[18px] leading-6 sm:text-[22px] sm:leading-7 text-ink">{{ number_format($binaanStock, 1, ',', '.') }} <span class="text-sm sm:text-base font-semibold">kg</span></p>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-3 rounded-2xl bg-surface p-4 sm:p-5 shadow-card">
                    <div class="flex flex-col gap-2 sm:gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body leading-tight">STOK BUFFER LUAR</h3>
                        </div>
                        <p class="gpa-figure text-[18px] leading-6 sm:text-[22px] sm:leading-7 text-ink">{{ number_format($bufferStock, 1, ',', '.') }} <span class="text-sm sm:text-base font-semibold">kg</span></p>
                    </div>
                </article>
            </div>
        </section>

        {{-- BAGIAN 1: Antrean Pesanan Masuk --}}
        <section x-data="{ openDrawer: false, selectedOrder: null }" class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-4 py-3 sm:px-6 sm:py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Pantau Antrean Pesanan Masuk</h2>
                    <p class="mt-1 text-xs text-ink-body">Pesanan aktif untuk acuan kuota panen koordinator</p>
                </div>
                <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    {{ count($incomingOrders) }} Pesanan Aktif
                </span>
            </header>

            {{-- Desktop Table --}}
            <div class="hidden md:block gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">No. Pesanan</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Pembeli</th>
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
                                    @php
                                        $badgeClass = match ($ord->status) {
                                            'MENUNGGU_VERIFIKASI' => 'bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
                                            'MENUNGGU_PEMBAYARAN' => 'bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
                                            'LUNAS' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
                                            'SIAP_KIRIM' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
                                            default => 'bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
                                        };
                                    @endphp
                                    <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $badgeClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $ord->status)) }}
                                    </span>
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

            {{-- Mobile Cards --}}
            <div class="md:hidden divide-y divide-line-hair">
                @forelse ($incomingOrders as $ord)
                    @php
                        $totalQty = $ord->orderItems->sum('ordered_qty');
                        $firstItem = $ord->orderItems->first();
                        $badgeClass = match ($ord->status) {
                            'MENUNGGU_VERIFIKASI' => 'bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
                            'MENUNGGU_PEMBAYARAN' => 'bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
                            'LUNAS' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
                            'SIAP_KIRIM' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
                            default => 'bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
                        };
                    @endphp
                    <button type="button" @click="selectedOrder = {
                        order_number: @js($ord->order_number),
                        status: @js(strtoupper(str_replace('_', ' ', $ord->status))),
                        client_name: @js($ord->user?->name ?? 'Klien Reguler'),
                        address: @js($ord->delivery_address ?: '—'),
                        date: @js($ord->created_at ? $ord->created_at->format('d/m/Y') : '—'),
                        time: @js($ord->created_at ? $ord->created_at->format('H:i') . ' WIB' : '—'),
                        total_qty: @js(number_format($totalQty, 0, ',', '.')),
                        items: @js($ord->orderItems->map(function($item){
                            return [
                                'name' => $item->product?->name ?? 'Komoditas',
                                'qty' => number_format($item->ordered_qty, 0, ',', '.'),
                                'unit' => $item->product?->unit ?? 'kg',
                            ];
                        })->values())
                    }; openDrawer = true" class="w-full p-3 text-left transition-colors hover:bg-surface-shell/50">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="font-mono text-xs font-semibold text-ink">{{ $ord->order_number }}</span>
                                    <span class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold leading-[14px] gpa-meta {{ $badgeClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $ord->status)) }}
                                    </span>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <p class="text-xs font-semibold text-ink truncate">{{ $firstItem?->product?->name ?? 'Komoditas' }}</p>
                                    <p class="text-xs font-mono text-ink">· {{ number_format($firstItem?->ordered_qty ?? 0, 0, ',', '.') }} {{ $firstItem?->product?->unit ?? 'kg' }}</p>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[10px] text-ink-body">
                                    <span>{{ $ord->user?->name ?? 'Klien Reguler' }}</span>
                                    <span>•</span>
                                    <span>{{ $ord->created_at ? $ord->created_at->format('d/m/Y') : '—' }}</span>
                                    <span>•</span>
                                    <span>{{ $ord->created_at ? $ord->created_at->format('H:i') . ' WIB' : '—' }}</span>
                                </div>
                            </div>
                            <x-gpa.icon name="chevron-right" class="h-4 w-4 text-ink-body shrink-0 mt-1" />
                        </div>
                    </button>
                @empty
                    <div class="px-4 py-10 text-center">
                        <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                            <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                            <p class="text-sm font-semibold text-ink">Belum ada pesanan aktif</p>
                            <p class="text-xs text-ink-body">Pesanan masuk akan muncul di sini untuk acuan perencanaan pasokan.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Detail Drawer --}}
            <div x-show="openDrawer" x-cloak class="fixed inset-0 z-50 md:hidden">
                <div class="absolute inset-0 bg-black/60" @click="openDrawer = false" x-show="openDrawer" x-transition.opacity></div>
                <div class="absolute inset-x-0 bottom-0 max-h-[85vh] flex flex-col rounded-t-2xl bg-surface shadow-2xl" x-show="openDrawer" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
                    <div class="flex items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                        <h3 class="text-sm font-bold text-ink">Detail Pesanan</h3>
                        <button type="button" @click="openDrawer = false" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-surface-shell">
                            <x-gpa.icon name="x" class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
                        <template x-if="selectedOrder">
                            <div class="space-y-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-mono text-sm font-semibold text-ink" x-text="selectedOrder.order_number"></p>
                                        <p class="text-xs text-ink-body mt-0.5" x-text="selectedOrder.status"></p>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-line-hair p-3 space-y-2">
                                    <p class="text-xs font-semibold text-ink">Informasi Klien</p>
                                    <div class="text-xs space-y-1.5">
                                        <div class="flex justify-between gap-2">
                                            <span class="text-ink-body">Nama</span>
                                            <span class="text-ink text-right" x-text="selectedOrder.client_name"></span>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <span class="text-ink-body">Alamat</span>
                                            <span class="text-ink text-right max-w-[60%]" x-text="selectedOrder.address"></span>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <span class="text-ink-body">Tanggal</span>
                                            <span class="text-ink" x-text="selectedOrder.date"></span>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <span class="text-ink-body">Waktu</span>
                                            <span class="text-ink" x-text="selectedOrder.time"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-line-hair p-3 space-y-2">
                                    <p class="text-xs font-semibold text-ink">Detail Komoditas</p>
                                    <template x-for="(item, idx) in selectedOrder.items" :key="idx">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-ink" x-text="item.name"></span>
                                            <span class="font-mono text-ink" x-text="item.qty + ' ' + item.unit"></span>
                                        </div>
                                    </template>
                                    <div class="pt-2 border-t border-line-hair flex justify-between text-xs font-semibold">
                                        <span class="text-ink">Total Kuantitas</span>
                                        <span class="font-mono text-ink" x-text="selectedOrder.total_qty + ' kg'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        {{-- BAGIAN 2: Status Pasokan Komoditas --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-4 py-3 sm:px-6 sm:py-4">
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

            {{-- Desktop Table --}}
            <div class="hidden md:block gpa-scroll-x overflow-x-auto">
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
                                    <p class="text-[12px] font-semibold leading-5 text-ink">{{ $row['name'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-block rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[10px] font-semibold leading-[15px] text-ink outline outline-1 -outline-offset-1 outline-line-board">{{ $row['grade'] }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] leading-5 text-ink">{{ number_format($row['supply'], 0, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] leading-5 text-ink-body">{{ number_format($row['buffer'], 0, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($row['total_stock'], 0, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[11px] leading-4 text-ink-body">{{ $row['group'] ?: '-' }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <a href="{{ route('koordinator.stok') }}?product_id={{ $row['id'] ?? ($row->id ?? $row['key']) }}#form-intake"
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

            {{-- Mobile Cards --}}
            <div class="md:hidden flex flex-col gap-2 p-2">
                @forelse ($supplyRows as $row)
                    @php
                        $stokBebas = $row['total_stock'] ?? 0;
                        $stokBinaan = $row['supply'] ?? 0;
                        $stokBuffer = $row['buffer'] ?? 0;
                        $totalStok = $row['total_stock'] ?? 0;
                        $alokasiPo = $row['order'] ?? 0;
                        
                        if ($stokBebas >= 100) {
                            $statusBadge = 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep';
                            $statusText = 'CUKUP';
                        } elseif ($stokBebas > 0) {
                            $statusBadge = 'bg-warning-soft text-warning-deep outline outline-1 -outline-offset-1 outline-warning/40';
                            $statusText = 'TERBATAS';
                        } else {
                            $statusBadge = 'bg-danger-soft text-danger outline outline-1 -outline-offset-1 outline-danger/40';
                            $statusText = 'HABIS';
                        }
                    @endphp
                    <div class="rounded-lg border border-line-hair bg-surface p-3 flex flex-col gap-2.5 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    <p class="text-sm font-semibold text-ink truncate">{{ $row['name'] }}</p>
                                    <span class="inline-block rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[9px] font-semibold leading-[13px] text-ink outline outline-1 -outline-offset-1 outline-line-board shrink-0">{{ $row['grade'] }}</span>
                                </div>
                                <div class="flex items-baseline justify-between gap-2">
                                    <p class="font-mono text-xl font-bold leading-6 text-ink">{{ number_format($stokBebas, 0, ',', '.') }} <span class="text-xs font-semibold">kg</span></p>
                                    <span class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold leading-[14px] gpa-meta {{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-[10px]">
                            <div class="flex justify-between">
                                <span class="text-ink-body">Pasokan Binaan</span>
                                <span class="font-mono text-ink">{{ number_format($stokBinaan, 0, ',', '.') }} kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink-body">Buffer Siaga</span>
                                <span class="font-mono text-ink">{{ number_format($stokBuffer, 0, ',', '.') }} kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink-body">Total Stok</span>
                                <span class="font-mono font-semibold text-ink">{{ number_format($totalStok, 0, ',', '.') }} kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-ink-body">Alokasi PO</span>
                                <span class="font-mono text-ink">{{ number_format($alokasiPo, 0, ',', '.') }} kg</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-line-hair">
                            <a href="{{ route('koordinator.stok') }}?product_id={{ $row['id'] ?? ($row->id ?? $row['key']) }}#form-intake"
                                class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-ink px-3 py-2 text-xs font-semibold text-white transition-opacity hover:opacity-90">
                                <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                                Tambah Panen
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center">
                        <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                            <x-gpa.icon name="grid" class="h-7 w-7 text-ink-body" />
                            <p class="text-sm font-semibold text-ink">Belum ada komoditas aktif</p>
                            <p class="text-xs text-ink-body">Tambahkan komoditas pada menu Manajemen Komoditas.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- BAGIAN 3: Log Pasokan Panen Terakhir --}}
        @php
            $totalPasokan = collect($gateLogs)->sum('net');
            $totalBatch = count($gateLogs);
        @endphp
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-4 py-3 sm:px-6 sm:py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Log Pasokan Panen Masuk Terakhir</h2>
                    <p class="mt-1 text-xs text-ink-body">Riwayat lot intake terbaru</p>
                </div>
                <a href="{{ route('koordinator.stok') }}"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                    Lihat Semua
                    <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 shrink-0" />
                </a>
            </header>

            {{-- Summary --}}
            <div class="px-4 sm:px-6">
                <div class="rounded-lg bg-surface-shell p-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-ink-body">Total Pasokan Masuk · 7 Hari Terakhir</p>
                    <p class="mt-1 font-mono text-lg font-bold text-ink">{{ number_format($totalPasokan, 0, ',', '.') }} kg</p>
                    <p class="text-xs text-ink-body">Dari {{ $totalBatch }} batch pencatatan</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="px-4 sm:px-6">
                <form method="GET" action="{{ route('koordinator.dashboard') }}" class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end">
                    <div class="flex flex-col gap-1 flex-1 min-w-[120px]">
                        <label class="text-[10px] font-semibold text-ink-body">Rentang Waktu</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border border-line-hair px-2 py-1.5 text-xs text-ink" />
                    </div>
                    <div class="flex flex-col gap-1 flex-1 min-w-[120px]">
                        <label class="text-[10px] font-semibold text-ink-body">Komoditas</label>
                        <select name="commodity" class="rounded-lg border border-line-hair px-2 py-1.5 text-xs text-ink">
                            <option value="">Semua</option>
                            @foreach(($commodityList ?? collect($gateLogs)->pluck('commodity')->unique()->filter()) as $c)
                                <option value="{{ $c }}" {{ request('commodity') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1 flex-1 min-w-[120px]">
                        <label class="text-[10px] font-semibold text-ink-body">Tipe Sumber</label>
                        <select name="source" class="rounded-lg border border-line-hair px-2 py-1.5 text-xs text-ink">
                            <option value="">Semua</option>
                            <option value="petani" {{ request('source') === 'petani' ? 'selected' : '' }}>Petani / Supplier</option>
                            <option value="binaan" {{ request('source') === 'binaan' ? 'selected' : '' }}>Panen Binaan</option>
                            <option value="buffer" {{ request('source') === 'buffer' ? 'selected' : '' }}>Buffer Luar</option>
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg bg-ink px-3 py-1.5 text-xs font-semibold text-white">Terapkan</button>
                    @if(request('date_from') || request('commodity') || request('source'))
                        <a href="{{ route('koordinator.dashboard') }}" class="inline-flex items-center rounded-lg border border-line-hair px-3 py-1.5 text-xs font-semibold text-ink-body hover:text-ink">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block gpa-scroll-x overflow-x-auto">
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
                                    <span class="font-mono text-[11px] font-semibold leading-5 text-ink">{{ $log['ticket'] }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[11px] leading-4 text-ink-body">{{ $log['time'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[12px] font-semibold leading-5 text-ink">{{ $log['commodity'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="text-[11px] leading-4 text-ink-body">{{ $log['name'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                        {{ strtoupper($log['note']) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($log['net'], 1, ',', '.') }} kg</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Belum ada catatan batch panen</p>
                                        <p class="text-xs text-ink-body">Catatan intake panen akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden flex flex-col gap-2 p-2">
                @forelse ($gateLogs as $log)
                    <div class="rounded-lg border border-line-hair bg-surface p-3 flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-mono text-[11px] font-semibold text-ink">{{ $log['ticket'] }}</p>
                                <p class="text-sm font-semibold text-ink mt-0.5">{{ $log['commodity'] }}</p>
                                <p class="text-[10px] text-ink-body mt-0.5">{{ $log['name'] }}</p>
                            </div>
                            <span class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold leading-[14px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board shrink-0">
                                {{ strtoupper($log['note']) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] text-ink-body">{{ $log['time'] }}</p>
                            <p class="font-mono text-base font-bold text-ink">{{ number_format($log['net'], 0, ',', '.') }} <span class="text-xs">kg</span></p>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center">
                        <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                            <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                            <p class="text-sm font-semibold text-ink">Belum ada catatan batch panen</p>
                            <p class="text-xs text-ink-body">Catatan intake panen akan muncul di sini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

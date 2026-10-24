@extends('layouts.coordinator')

@section('title', 'Uji Coba CRUD Komoditas')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Manajemen Komoditas (CRUD)</h1>
            <p class="text-xs text-slate-500">Uji coba penambahan, pengubahan, toggle status katalog, dan hapus master data komoditas.</p>
        </div>
        <a href="{{ route('koordinator.products.create') }}" class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
            + Tambah Komoditas Baru
        </a>
    </div>

    {{-- Filter & Ringkasan Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('koordinator.products.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..."
                class="rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">

            <select name="grade" class="rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                <option value="all">Semua Grade</option>
                @foreach (['Grade A', 'Grade A+', 'Grade B', 'Grade C'] as $g)
                    <option value="{{ $g }}" {{ request('grade') === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>

            <select name="status" class="rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                <option value="all">Semua Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button type="submit" class="rounded bg-slate-800 px-3 py-1.5 font-medium text-white hover:bg-slate-700">
                Filter
            </button>

            @if (request('search') || (request('grade') && request('grade') !== 'all') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('koordinator.products.index') }}" class="text-slate-500 hover:text-slate-800 underline">
                    Reset
                </a>
            @endif
        </form>

        <div class="flex gap-4 text-xs font-mono">
            <div>Total: <span class="font-bold text-slate-900">{{ $totalProducts ?? count($products) }}</span></div>
            <div>Aktif: <span class="font-bold text-emerald-700">{{ $activeProducts ?? 0 }}</span></div>
            <div>Stok Fisik: <span class="font-bold text-slate-900">{{ number_format($totalStockKg ?? 0, 1) }} KG</span></div>
        </div>
    </div>

    {{-- Tabel Komoditas --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-100 font-semibold text-slate-700">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Foto</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Nama Komoditas</th>
                        <th class="px-4 py-3">Grade</th>
                        <th class="px-4 py-3">Satuan</th>
                        <th class="px-4 py-3 text-right">Harga Dasar</th>
                        <th class="px-4 py-3 text-right">Min. Order</th>
                        <th class="px-4 py-3 text-right">Stok Riil (KG)</th>
                        <th class="px-4 py-3 text-center">Status Katalog</th>
                        <th class="px-4 py-3 text-center">Aksi Backend</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse ($products as $idx => $prod)
                        @php
                            $stokTersedia = (float) $prod->harvestBatches->sum('available_quantity');
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3">
                                @if ($prod->image_url)
                                    <img src="{{ asset($prod->image_url) }}" alt="{{ $prod->name }}" class="h-10 w-10 rounded border border-slate-200 object-cover">
                                @else
                                    <div class="h-10 w-10 rounded border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-2xs text-slate-400">
                                        No pic
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono font-medium text-slate-800">{{ $prod->sku }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('koordinator.products.show', $prod) }}" class="hover:underline hover:text-emerald-700">
                                    {{ $prod->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-2xs font-mono font-medium text-slate-700">
                                    {{ $prod->grade ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-500">{{ $prod->unit }}</td>
                            <td class="px-4 py-3 text-right font-mono text-slate-800">
                                Rp {{ number_format($prod->base_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-slate-600">
                                {{ number_format($prod->minimum_order, 1, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($stokTersedia, 1, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($prod->is_active)
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-2xs font-bold text-emerald-800">
                                        AKTIF
                                    </span>
                                @else
                                    <span class="rounded bg-slate-200 px-2 py-0.5 text-2xs font-semibold text-slate-600">
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1">
                                    {{-- Form Toggle Status --}}
                                    <form method="POST" action="{{ route('koordinator.products.toggle-status', $prod) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Ubah status tampil di katalog"
                                            class="rounded border border-slate-300 bg-white px-2 py-1 text-2xs font-medium text-slate-700 hover:bg-slate-100">
                                            {{ $prod->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Link Edit --}}
                                    <a href="{{ route('koordinator.products.edit', $prod) }}"
                                        class="rounded bg-sky-50 px-2 py-1 text-2xs font-medium text-sky-700 border border-sky-200 hover:bg-sky-100">
                                        Edit
                                    </a>

                                    {{-- Form Hapus --}}
                                    <form method="POST" action="{{ route('koordinator.products.destroy', $prod) }}" class="inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus komoditas {{ $prod->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded bg-rose-50 px-2 py-1 text-2xs font-medium text-rose-700 border border-rose-200 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-slate-400 italic">
                                Belum ada komoditas ditemukan. Silakan klik tombol "+ Tambah Komoditas Baru".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

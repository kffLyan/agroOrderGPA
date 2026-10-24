@extends('layouts.coordinator')

@section('title', 'Detail Komoditas: ' . $product->name)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('koordinator.products.index') }}" class="text-xs text-slate-500 hover:text-slate-800">&larr; Kembali ke Daftar</a>
                <span class="text-slate-300">/</span>
                <span class="text-xs font-mono text-slate-600">{{ $product->sku }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 mt-1">{{ $product->name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('koordinator.products.edit', $product) }}" class="rounded bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-700">
                Edit Komoditas
            </a>
            <a href="{{ route('koordinator.stock') }}?product_id={{ $product->id }}#form-intake" class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                + Input Pasokan Panen
            </a>
        </div>
    </div>

    {{-- Detail Spesifikasi Komoditas & Ringkasan Stok --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri: Informasi Produk --}}
        <div class="lg:col-span-1 rounded-lg border border-slate-200 bg-white p-5 shadow-sm space-y-4">
            <div class="flex justify-center">
                @if ($product->image_url)
                    <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="h-40 w-40 rounded-lg border border-slate-200 object-cover shadow-inner">
                @else
                    <div class="h-40 w-40 rounded-lg border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-xs text-slate-400">
                        Tidak ada gambar
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-100 pt-3 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Kode SKU:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $product->sku }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Grade:</span>
                    <span class="font-mono font-medium text-slate-800">{{ $product->grade ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Satuan:</span>
                    <span class="font-mono text-slate-800">{{ $product->unit }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Harga Dasar:</span>
                    <span class="font-mono font-bold text-slate-900">Rp {{ number_format($product->base_price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Min. Pemesanan:</span>
                    <span class="font-mono text-slate-800">{{ number_format($product->minimum_order, 1, ',', '.') }} {{ $product->unit }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Status Katalog:</span>
                    <span>
                        @if ($product->is_active)
                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-2xs font-bold text-emerald-800">AKTIF</span>
                        @else
                            <span class="rounded bg-slate-200 px-2 py-0.5 text-2xs font-semibold text-slate-600">NONAKTIF</span>
                        @endif
                    </span>
                </div>
            </div>

            @if ($product->description)
                <div class="border-t border-slate-100 pt-3">
                    <div class="text-2xs font-semibold text-slate-500 uppercase">Deskripsi:</div>
                    <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Status Stok & Riwayat Batch Pasokan --}}
        <div class="lg:col-span-2 space-y-4">
            @php
                $binaanStock = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
                $bufferStock = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
            @endphp
            {{-- Metrik Stok Produk --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="text-2xs font-medium text-slate-500">Stok Tersedia</div>
                    <div class="mt-1 text-xl font-bold text-slate-900 font-mono">{{ number_format($availableStock ?? 0, 1, ',', '.') }} <span class="text-xs font-normal">KG</span></div>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="text-2xs font-medium text-slate-500">Dari Petani Binaan</div>
                    <div class="mt-1 text-xl font-bold text-emerald-700 font-mono">{{ number_format($binaanStock, 1, ',', '.') }} <span class="text-xs font-normal">KG</span></div>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="text-2xs font-medium text-slate-500">Dari Buffer Luar</div>
                    <div class="mt-1 text-xl font-bold text-sky-700 font-mono">{{ number_format($bufferStock, 1, ',', '.') }} <span class="text-xs font-normal">KG</span></div>
                </div>
            </div>

            {{-- Riwayat Batch Pasokan Panen --}}
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-800">Riwayat Batch Pasokan Panen (Harvest Batches)</h2>
                    <span class="text-2xs font-mono text-slate-500">{{ $product->harvestBatches->count() }} Lot</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-200 bg-slate-100 font-semibold text-slate-700">
                            <tr>
                                <th class="px-3 py-2">ID</th>
                                <th class="px-3 py-2">Tanggal Panen</th>
                                <th class="px-3 py-2">Petani / Kelompok Tani</th>
                                <th class="px-3 py-2">Sumber</th>
                                <th class="px-3 py-2 text-right">Qty Awal (KG)</th>
                                <th class="px-3 py-2 text-right">Tersedia (KG)</th>
                                <th class="px-3 py-2">Catatan QC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            @forelse ($product->harvestBatches as $b)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2 font-mono text-slate-500">#{{ $b->id }}</td>
                                    <td class="px-3 py-2 font-mono">{{ $b->batch_date ? $b->batch_date->format('d/m/Y') : '—' }}</td>
                                    <td class="px-3 py-2 font-medium text-slate-800">{{ $b->supplier_name ?: 'Mitra Tani' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="rounded px-1.5 py-0.5 text-2xs font-mono {{ $b->source_type === 'BUFFER_LUAR' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $b->source_type === 'BUFFER_LUAR' ? 'Buffer' : 'Binaan' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono">{{ number_format($b->initial_quantity, 1, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-mono font-bold text-slate-900">{{ number_format($b->available_quantity, 1, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-2xs text-slate-500">{{ $b->qc_notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-slate-400 italic">
                                        Belum ada batch panen tercatat untuk komoditas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

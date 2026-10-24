@extends('layouts.coordinator')

@section('title', 'Dasbor Pengujian Koordinator')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Dasbor Koordinator Lapangan</h1>
            <p class="text-xs text-slate-500">Uji coba pemantauan persediaan, kebutuhan pasar klien, dan lot pasokan panen.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('koordinator.products.create') }}" class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                + Tambah Komoditas
            </a>
            <a href="{{ route('koordinator.stock') }}#form-intake" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                + Catat Intake Panen
            </a>
        </div>
    </div>

    {{-- Ringkasan Metrik Database --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Total Stok Gudang</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($totalStock, 1, ',', '.') }} <span class="text-xs font-normal text-slate-500">KG</span></div>
            <div class="mt-1 text-2xs text-slate-400">Tersedia di gudang</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Stok Petani Binaan</div>
            <div class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format($binaanStock, 1, ',', '.') }} <span class="text-xs font-normal text-slate-500">KG</span></div>
            <div class="mt-1 text-2xs text-emerald-600">Pasokan inti mitra</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Stok Buffer Luar</div>
            <div class="mt-1 text-2xl font-bold text-sky-700">{{ number_format($bufferStock, 1, ',', '.') }} <span class="text-xs font-normal text-slate-500">KG</span></div>
            <div class="mt-1 text-2xs text-sky-600">Penyeimbang fluktuasi</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Komoditas Aktif</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $activeProductsCount }}</div>
            <div class="mt-1 text-2xs text-slate-400">Tampil di katalog klien</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Total Batch Panen</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $totalIntakeBatches }}</div>
            <div class="mt-1 text-2xs text-slate-400">Riwayat intake tercatat</div>
        </div>
    </div>

    {{-- BAGIAN 1: Pantau Antrean Pesanan Masuk (Demand Pasar Klien) --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">1. Pantau Antrean Pesanan Masuk (Demand Pasar Klien)</h2>
                <p class="text-xs text-slate-500">Data pesanan pembeli dari tabel <code class="font-mono text-emerald-700">orders</code> untuk acuan kuota panen koordinator.</p>
            </div>
            <span class="rounded bg-slate-200 px-2 py-0.5 text-xs font-mono font-medium text-slate-700">
                {{ count($incomingOrders) }} Pesanan Aktif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-100 font-semibold text-slate-700">
                    <tr>
                        <th class="px-4 py-2.5">No</th>
                        <th class="px-4 py-2.5">No. Pesanan / Invoice</th>
                        <th class="px-4 py-2.5">Nama Klien / Pembeli</th>
                        <th class="px-4 py-2.5">Waktu Order</th>
                        <th class="px-4 py-2.5">Komoditas Dipesan</th>
                        <th class="px-4 py-2.5 text-right">Total Kuantitas</th>
                        <th class="px-4 py-2.5 text-center">Status Pesanan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse ($incomingOrders as $idx => $ord)
                        @php
                            $totalQty = $ord->orderItems->sum('ordered_qty');
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-2.5 font-mono font-semibold text-slate-800">
                                {{ $ord->order_number }}
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-slate-800">{{ $ord->user?->name ?? 'Klien Reguler' }}</div>
                                <div class="text-2xs text-slate-400 font-mono">{{ $ord->delivery_address ? Str::limit($ord->delivery_address, 35) : 'Alamat gudang' }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-slate-500">
                                {{ $ord->created_at ? $ord->created_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-2.5">
                                <ul class="space-y-0.5">
                                    @foreach ($ord->orderItems as $item)
                                        <li>
                                            <span class="font-medium text-slate-800">{{ $item->product?->name ?? 'Komoditas' }}</span>:
                                            {{ number_format($item->ordered_qty, 0, ',', '.') }} {{ $item->product?->unit ?? 'kg' }}
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-800">
                                {{ number_format($totalQty, 0, ',', '.') }} KG
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @php
                                    $badge = match ($ord->status) {
                                        'MENUNGGU_VERIFIKASI' => 'bg-amber-100 text-amber-800',
                                        'MENUNGGU_PEMBAYARAN' => 'bg-yellow-100 text-yellow-800',
                                        'LUNAS' => 'bg-emerald-100 text-emerald-800',
                                        'SIAP_KIRIM' => 'bg-sky-100 text-sky-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="rounded px-2 py-0.5 text-2xs font-semibold font-mono {{ $badge }}">
                                    {{ $ord->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-slate-400 italic">
                                Belum ada pesanan aktif masuk saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BAGIAN 2: Ringkasan Pasokan Komoditas Hasil Panen --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">2. Status Pasokan &amp; Stok Komoditas</h2>
                <p class="text-xs text-slate-500">Ketersediaan stok komoditas dari petani binaan dan buffer siap kirim.</p>
            </div>
            <a href="{{ route('koordinator.stock') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                Kelola Stok &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-100 font-semibold text-slate-700">
                    <tr>
                        <th class="px-4 py-2.5">No</th>
                        <th class="px-4 py-2.5">Komoditas</th>
                        <th class="px-4 py-2.5">Grade</th>
                        <th class="px-4 py-2.5 text-right">Stok Binaan (KG)</th>
                        <th class="px-4 py-2.5 text-right">Stok Buffer (KG)</th>
                        <th class="px-4 py-2.5 text-right">Total Stok (KG)</th>
                        <th class="px-4 py-2.5">Kelompok Tani Mitra</th>
                        <th class="px-4 py-2.5 text-center">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse ($supplyRows as $idx => $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $row['name'] }}</td>
                            <td class="px-4 py-2.5">
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-2xs font-mono font-medium text-slate-700">
                                    {{ $row['grade'] }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-emerald-700 font-medium">
                                {{ number_format($row['supply'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-sky-700 font-medium">
                                {{ number_format($row['buffer'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900">
                                {{ number_format($row['total_stock'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $row['group'] }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <a href="{{ route('koordinator.stock') }}?product_id={{ $row['key'] }}#form-intake"
                                    class="rounded border border-slate-300 bg-white px-2 py-1 text-2xs font-medium text-slate-700 hover:bg-slate-100">
                                    + Tambah Panen
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-400 italic">
                                Belum ada komoditas aktif. Silakan tambahkan komoditas pada menu Manajemen Komoditas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BAGIAN 3: Log Batch Panen Masuk Terbaru (Intake Staging) --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3 bg-slate-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">3. Log Pasokan Panen Masuk Terakhir</h2>
                <p class="text-xs text-slate-500">Riwayat lot intake dari tabel <code class="font-mono text-emerald-700">harvest_batches</code>.</p>
            </div>
            <a href="{{ route('koordinator.stock') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-100 font-semibold text-slate-700">
                    <tr>
                        <th class="px-4 py-2.5">ID Batch</th>
                        <th class="px-4 py-2.5">Waktu Pencatatan</th>
                        <th class="px-4 py-2.5">Komoditas</th>
                        <th class="px-4 py-2.5">Petani / Supplier</th>
                        <th class="px-4 py-2.5">Tipe Sumber</th>
                        <th class="px-4 py-2.5 text-right">Kuantitas Masuk (KG)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse ($gateLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 font-mono font-medium text-slate-800">{{ $log['ticket'] }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ $log['time'] }}</td>
                            <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $log['commodity'] }}</td>
                            <td class="px-4 py-2.5">{{ $log['name'] }}</td>
                            <td class="px-4 py-2.5">
                                <span class="rounded px-1.5 py-0.5 text-2xs font-mono font-medium {{ $log['note'] === 'Buffer Luar' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $log['note'] }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900">
                                {{ number_format($log['net'], 1, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400 italic">
                                Belum ada catatan batch panen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

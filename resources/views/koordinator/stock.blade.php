@extends('layouts.coordinator')

@section('title', 'Uji Coba Suplai Stok Panen')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Suplai Stok Panen (Intake Ledger)</h1>
            <p class="text-xs text-slate-500">Uji coba pencatatan pasokan masuk dari petani binaan maupun buffer ke <code class="font-mono text-emerald-700">harvest_batches</code>.</p>
        </div>
        <div class="flex gap-2">
            <a href="#form-intake" class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                + Input Pasokan Baru
            </a>
            <a href="{{ route('koordinator.products.index') }}" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Lihat Master Komoditas
            </a>
        </div>
    </div>

    {{-- Ringkasan Metrik Gudang --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Total Stok Fisik</div>
            <div class="mt-1 text-2xl font-bold text-slate-900 font-mono">{{ number_format($totalStock, 1, ',', '.') }} <span class="text-xs font-normal">KG</span></div>
            <div class="mt-1 text-2xs text-slate-400">Total di gudang GPA</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Stok Petani Binaan</div>
            <div class="mt-1 text-2xl font-bold text-emerald-700 font-mono">{{ number_format($totalBinaan, 1, ',', '.') }} <span class="text-xs font-normal">KG</span></div>
            <div class="mt-1 text-2xs text-emerald-600">Pasokan kemitraan inti</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Stok Buffer Luar</div>
            <div class="mt-1 text-2xl font-bold text-sky-700 font-mono">{{ number_format($totalBuffer, 1, ',', '.') }} <span class="text-xs font-normal">KG</span></div>
            <div class="mt-1 text-2xs text-sky-600">Penyangga kekurangan</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium text-slate-500">Alokasi Terikat PO</div>
            <div class="mt-1 text-2xl font-bold text-amber-700 font-mono">{{ number_format($totalReserved, 1, ',', '.') }} <span class="text-xs font-normal">KG</span></div>
            <div class="mt-1 text-2xs text-amber-600">{{ $activePoCount }} pesanan terverifikasi</div>
        </div>
    </div>

    {{-- SECTION 1: Formulir Input Pasokan Panen (Quick Intake Ledger) --}}
    <div id="form-intake" class="rounded-lg border border-emerald-300 bg-white p-6 shadow-sm">
        <div class="border-b border-slate-200 pb-3 mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-600"></span>
                    Formulir Input Pasokan Panen (Intake Ledger)
                </h2>
                <p class="text-xs text-slate-500">Menyimpan data lot panen baru langsung ke tabel database <code class="font-mono text-emerald-700">harvest_batches</code>.</p>
            </div>
            <span class="rounded bg-emerald-100 px-2 py-0.5 text-2xs font-bold text-emerald-800 font-mono">
                POST /koordinator/stok
            </span>
        </div>

        <form method="POST" action="{{ route('koordinator.stock.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                {{-- Komoditas --}}
                <div>
                    <label for="product_id" class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilih Komoditas <span class="text-rose-500">*</span>
                    </label>
                    <select id="product_id" name="product_id" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        <option value="">-- Pilih Komoditas --</option>
                        @foreach ($products as $prod)
                            <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} ({{ $prod->grade ?: 'Grade A' }}) - SKU: {{ $prod->sku }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sumber Pasokan --}}
                <div>
                    <label for="source_type" class="block text-xs font-semibold text-slate-700 mb-1">
                        Tipe Sumber Pasokan <span class="text-rose-500">*</span>
                    </label>
                    <select id="source_type" name="source_type" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        <option value="PETANI_BINAAN">Petani Binaan Inti (Kebun Binaan GPA)</option>
                        <option value="BUFFER_LUAR">Buffer Luar (Pasokan Mitigasi / Pasar Luar)</option>
                    </select>
                </div>

                {{-- Nama Supplier / Kelompok Tani --}}
                <div>
                    <label for="supplier_name" class="block text-xs font-semibold text-slate-700 mb-1">
                        Kelompok Tani / Supplier <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="supplier_name" name="supplier_name" list="suppliers-list" placeholder="Contoh: Poktan Sukamaju" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    <datalist id="suppliers-list">
                        @foreach ($distinctSuppliers as $sup)
                            <option value="{{ $sup }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                {{-- Tanggal Panen --}}
                <div>
                    <label for="batch_date" class="block text-xs font-semibold text-slate-700 mb-1">
                        Tanggal Panen <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="batch_date" name="batch_date" value="{{ date('Y-m-d') }}" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>

                {{-- Kuantitas Masuk --}}
                <div>
                    <label for="initial_quantity" class="block text-xs font-semibold text-slate-700 mb-1">
                        Kuantitas Masuk (KG) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="initial_quantity" name="initial_quantity" min="0.1" step="any" placeholder="Contoh: 250" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>

                {{-- Grade Mutu --}}
                <div>
                    <label for="grade" class="block text-xs font-semibold text-slate-700 mb-1">
                        Hasil Uji Mutu (Grade)
                    </label>
                    <select id="grade" name="grade"
                        class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        <option value="Grade A">Grade A (Super Segar)</option>
                        <option value="Grade B">Grade B (Standar Pasar)</option>
                        <option value="Grade C">Grade C (Industri / Olahan)</option>
                    </select>
                </div>
            </div>

            {{-- Catatan QC --}}
            <div>
                <label for="qc_notes" class="block text-xs font-semibold text-slate-700 mb-1">
                    Catatan Pemeriksaan Mutu (QC)
                </label>
                <input type="text" id="qc_notes" name="qc_notes" placeholder="Contoh: Lolos sortir manual, kadar air optimal, susut 0.8%"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            {{-- Tombol Submit --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="reset" class="rounded border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Reset Form
                </button>
                <button type="submit" class="rounded bg-emerald-600 px-5 py-2 text-xs font-bold text-white hover:bg-emerald-700 shadow-sm">
                    Simpan Pasokan Panen (Submit Intake)
                </button>
            </div>
        </form>
    </div>

    {{-- SECTION 2: Tabel Monitoring Kesiapan Stok & Alokasi PO Komoditas --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Kesiapan Stok Komoditas &amp; Alokasi PO</h2>
                <p class="text-xs text-slate-500">Perbandingan stok fisik gudang terhadap alokasi pesanan klien.</p>
            </div>
            <span class="text-xs font-mono text-slate-500">{{ count($commodities) }} Komoditas</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-100 font-semibold text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Komoditas</th>
                        <th class="px-4 py-3">Grade</th>
                        <th class="px-4 py-3 text-right">Pasokan Binaan</th>
                        <th class="px-4 py-3 text-right">Buffer Siaga</th>
                        <th class="px-4 py-3 text-right">Total Stok Fisik</th>
                        <th class="px-4 py-3 text-right">Alokasi PO</th>
                        <th class="px-4 py-3 text-right">Stok Bebas (RTP)</th>
                        <th class="px-4 py-3 text-center">Status Pemenuhan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse ($commodities as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $c['name'] }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-2xs font-mono font-medium text-slate-700">
                                    {{ $c['grade'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-emerald-700">{{ number_format($c['tiles'][0]['value'] ?? 0, 1, ',', '.') }} KG</td>
                            <td class="px-4 py-3 text-right font-mono text-sky-700">{{ number_format($c['tiles'][1]['value'] ?? 0, 1, ',', '.') }} KG</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">{{ number_format($c['total'], 1, ',', '.') }} KG</td>
                            <td class="px-4 py-3 text-right font-mono text-amber-700">{{ number_format($c['tiles'][2]['value'] ?? 0, 1, ',', '.') }} KG</td>
                            <td class="px-4 py-3 text-right font-mono font-bold {{ $c['rtp'] < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ number_format($c['rtp'], 1, ',', '.') }} KG
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($c['rtp'] < 0)
                                    <span class="rounded bg-rose-100 px-2 py-0.5 text-2xs font-bold text-rose-800">
                                        DEFISIT (Butuh Buffer)
                                    </span>
                                @elseif ($c['utilization'] > 80)
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-2xs font-bold text-amber-800">
                                        KETAT ({{ $c['utilization'] }}%)
                                    </span>
                                @else
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-2xs font-bold text-emerald-800">
                                        AMAN
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-slate-400 italic">
                                Belum ada komoditas aktif tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION 3: Log Riwayat Batch Panen (Intake Ledger) --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Log Pasokan Panen Masuk Terbaru</h2>
                <p class="text-xs text-slate-500">Data riwayat batch dari tabel <code class="font-mono text-emerald-700">harvest_batches</code>.</p>
            </div>
            <span class="text-xs font-mono text-slate-500">{{ count($logItems) }} Record Terakhir</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 bg-slate-100 font-semibold text-slate-700">
                    <tr>
                        <th class="px-4 py-2.5">Kode Lot</th>
                        <th class="px-4 py-2.5">Waktu Pencatatan</th>
                        <th class="px-4 py-2.5">Rincian Intake</th>
                        <th class="px-4 py-2.5">Catatan Mutu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse ($logItems as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 font-mono font-medium text-slate-800">{{ $item['lot'] }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ $item['time'] }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $item['entry'] }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ $item['note'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-400 italic">
                                Belum ada log pasokan panen. Silakan gunakan formulir di atas untuk menguji input batch panen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

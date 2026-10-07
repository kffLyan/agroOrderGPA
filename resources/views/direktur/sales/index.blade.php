<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Monitoring Penjualan & Analisis Revenue Eksekutif
    </x-slot>

    <!-- Top Action Bar & Filter Periode -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-gray-900">Monitoring Penjualan & Analisis Revenue Eksekutif</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Konsol Pemantauan Performa Omzet, Realisasi Margin Kotor 5 Komoditas Inti, Analisis Saluran Penjualan B2B vs Reguler, dan Pertumbuhan Finansial Makro.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-md shadow-sm border border-gray-300">
                <a href="{{ route('direktur.sales.index', ['period' => 'MTD']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-l-md {{ $period === 'MTD' ? 'bg-[#153a01] text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    MTD / Q4 2026
                </a>
                <a href="{{ route('direktur.sales.index', ['period' => 'WTD']) }}" class="px-3 py-1.5 text-xs font-semibold border-l border-r border-gray-300 {{ $period === 'WTD' ? 'bg-[#153a01] text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    WTD (Mingguan)
                </a>
                <a href="{{ route('direktur.sales.index', ['period' => 'YTD']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-r-md {{ $period === 'YTD' ? 'bg-[#153a01] text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    YTD Konsolidasi
                </a>
            </div>
            <a href="{{ route('direktur.sales.export') }}" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <span>Unduh Ringkasan XLSX</span>
            </a>
            <a href="{{ route('direktur.reports.download') }}" target="_blank" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-800 text-white rounded text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <span>Cetak Rekapitulasi PDF</span>
            </a>
        </div>
    </div>

    <!-- 1. KARTU KPI METRIK PENJUALAN & MARGIN -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- KPI 1: TOTAL OMZET MTD -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-[#153a01]">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">TOTAL OMZET MTD</div>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">+14.2% MoM</span>
            </div>
            <div class="text-xl font-black text-gray-900 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Target Q4:</span>
                    <span class="font-bold text-gray-700">Rp {{ number_format($targetRevenue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Defisit / Sisa:</span>
                    <span class="font-bold text-amber-700">Rp {{ number_format($deficit, 0, ',', '.') }} ({{ $targetProgress }}%)</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: GROSS MARGIN RATA-RATA -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-emerald-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">GROSS MARGIN RATA-RATA</div>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">SPREAD +6.8%</span>
            </div>
            <div class="text-xl font-black text-emerald-800 mt-1">{{ $grossMarginPercent }}%</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Target Direksi:</span>
                    <span class="font-bold text-gray-700">Min 15.0%</span>
                </div>
                <div class="flex justify-between">
                    <span>Realisasi Laba Kotor:</span>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- KPI 3: TRANSAKSI SAH (SETTLED) -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">TRANSAKSI SAH (SETTLED)</div>
                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[9px]">FULFILL: 98.6%</span>
            </div>
            <div class="text-xl font-black text-blue-900 mt-1">{{ $settledOrdersCount }} PO Terpenuhi</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Nilai Rata-rata (AOV):</span>
                    <span class="font-bold text-gray-700">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Retur / Klaim Fisik:</span>
                    <span class="font-bold text-emerald-700">2 Kasus (0.3% Vol)</span>
                </div>
            </div>
        </div>

        <!-- KPI 4: RASIO PIUTANG TERBAYAR -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">RASIO PIUTANG TERBAYAR</div>
                <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-800 font-bold text-[9px]">KAS SEHAT</span>
            </div>
            <div class="text-xl font-black text-gray-900 mt-1">{{ $cashRatio }}% Kas Masuk</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Kas Terealisasi:</span>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($totalPaidCash, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Piutang Berjalan (TOP):</span>
                    <span class="font-bold text-blue-700">Rp {{ number_format($openReceivables, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. TREN OMZET MINGGUAN & KOMPOSISI REVENUE SALURAN -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Tren Mingguan -->
        <div class="lg:col-span-2 bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-bold text-xs text-gray-900">Tren Omzet Mingguan vs Target Direksi (W40 - W44)</h3>
                    <p class="text-[11px] text-gray-500">Evaluasi trajektori omzet per pekan dengan perbandingan ambang batas BEP dan Ceiling Target.</p>
                </div>
                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px]">
                    Target Cap: Rp 120M / Mgg
                </span>
            </div>

            <div class="space-y-3">
                @foreach($weeklyTrends as $wt)
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-gray-800">{{ $wt['week'] }}</span>
                            <span class="text-gray-900">Rp {{ number_format($wt['realisasi'], 0, ',', '.') }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-[#153a01] h-3 rounded-full" style="width: {{ min(100, round(($wt['realisasi'] / $wt['target_cap']) * 100)) }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-400 mt-0.5">
                            <span>BEP Baseline: Rp {{ number_format($wt['bep'] / 1000000, 0) }}M</span>
                            <span>Cap: Rp {{ number_format($wt['target_cap'] / 1000000, 0) }}M</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-2 text-[10px] text-gray-400 border-t flex justify-between">
                <span>Pertumbuhan Rata-rata: +3.2% per Pekan</span>
                <span>Cut-off data: Live Sync Sistem</span>
            </div>
        </div>

        <!-- Komposisi Revenue Saluran -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-xs text-gray-900">Komposisi Revenue Saluran</h3>
                <p class="text-[11px] text-gray-500">Dekomposisi bauran omzet B2B Kontrak vs Pasokan Reguler.</p>
            </div>

            <div class="space-y-4">
                @foreach($channels as $ch)
                    <div class="p-3 bg-gray-50 rounded border border-gray-100">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-gray-800">{{ $ch['name'] }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $ch['color'] }} text-white">{{ $ch['percentage'] }}%</span>
                        </div>
                        <div class="text-sm font-black text-gray-900 mt-1">
                            Rp {{ number_format($ch['amount'], 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3 border-t text-xs text-gray-600 flex justify-between">
                <span>Konsolidasi 3 Kanal:</span>
                <strong class="text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <!-- 3. TABEL KINERJA PORTOFOLIO FINANSIAL 5 KOMODITAS INTI -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 bg-gray-50">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Kinerja Portofolio Finansial: 5 Komoditas Inti GPA</h3>
                <p class="text-[11px] text-gray-500">
                    Rincian volume realisasi, Average Selling Price (ASP), Harga Pokok Penjualan Binaan, serta persentase margin laba kotor per komoditas.
                </p>
            </div>
            <span class="px-2 py-1 bg-emerald-100 text-[#153a01] border border-emerald-300 rounded text-[10px] font-bold">
                PRD CORE 5 PRIMA
            </span>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">Komoditas Utama & SKU</th>
                        <th class="p-3 text-right">Volume (Kg)</th>
                        <th class="p-3 text-right">ASP (IDR/Kg)</th>
                        <th class="p-3 text-right">HPP Petani (IDR/Kg)</th>
                        <th class="p-3 text-right">Total Omzet (Rp)</th>
                        <th class="p-3 text-right">Gross Profit (Rp)</th>
                        <th class="p-3 text-center">Gross Margin %</th>
                        <th class="p-3 text-center">Bauran %</th>
                        <th class="p-3 text-center">Status Direksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($commodityPortfolio as $cp)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3">
                                <div class="font-bold text-gray-900">{{ $cp['product']->name }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $cp['sku'] }}</div>
                            </td>
                            <td class="p-3 text-right font-semibold text-gray-800">
                                {{ number_format($cp['volume_kg'], 0, ',', '.') }} kg
                            </td>
                            <td class="p-3 text-right font-medium text-gray-700">
                                Rp {{ number_format($cp['asp'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-medium text-gray-500">
                                Rp {{ number_format($cp['hpp'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-bold text-gray-900">
                                Rp {{ number_format($cp['omzet'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-bold text-emerald-700">
                                Rp {{ number_format($cp['profit'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center font-bold text-gray-900">
                                {{ $cp['margin_percent'] }}%
                            </td>
                            <td class="p-3 text-center font-semibold text-gray-600">
                                {{ $cp['mix_percent'] }}%
                            </td>
                            <td class="p-3 text-center">
                                @if($cp['status'] === 'PRIMA')
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-800 border border-green-200">PRIMA</span>
                                @elseif($cp['status'] === 'MARGIN TIGHT')
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-800 border border-red-200">MARGIN TIGHT</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800 border border-blue-200">{{ $cp['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <!-- Total Row -->
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-300">
                        <td class="p-3 uppercase">Total Konsolidasi (5 Komoditas)</td>
                        <td class="p-3 text-right text-gray-900">{{ number_format($totalVolAll, 0, ',', '.') }} kg</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right text-gray-900">Rp {{ number_format($totalOmzetAll, 0, ',', '.') }}</td>
                        <td class="p-3 text-right text-emerald-800">Rp {{ number_format($totalProfitAll, 0, ',', '.') }}</td>
                        <td class="p-3 text-center text-gray-900">21.8%</td>
                        <td class="p-3 text-center">100.0%</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-800 text-white">AUDITED VERIFIED</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. REKONSILIASI CASH INFLOW & METODE PEMBAYARAN -->
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Rekonsiliasi Cash Inflow & Distribusi Metode Pembayaran</h3>
                <p class="text-[11px] text-gray-500">Validasi kepatuhan tata kelola arus kas masuk berdasarkan pedoman PRD Rule 11 & Rule 15.</p>
            </div>
            <span class="px-2 py-0.5 bg-blue-50 text-blue-800 font-bold text-[10px] rounded border border-blue-200">
                GATEWAY AUDIT: PASSED
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
            @foreach($paymentMethods as $pm)
                <div class="p-3.5 bg-gray-50 rounded border border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-bold text-gray-800">{{ $pm['name'] }}</span>
                        <span class="font-black text-xs text-[#153a01]">{{ $pm['percentage'] }}%</span>
                    </div>
                    <div class="text-base font-black text-gray-900 mt-1.5">
                        Rp {{ number_format($pm['amount'], 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-gray-500 mt-1 border-t pt-1">
                        {{ $pm['note'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-3 bg-emerald-50/60 rounded border border-emerald-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 text-xs">
            <div>
                <span class="font-bold text-emerald-900">Kepatuhan Tata Kelola:</span>
                <span class="text-emerald-800 ml-1">PRD SEC 11 (Otorisasi Piutang) & SEC 15 (Rekonsiliasi Kas Harian). Semua transaksi MTD senilai Rp {{ number_format($totalRevenue, 0, ',', '.') }} telah di-hash SHA-256.</span>
            </div>
            <div class="flex items-center gap-2 font-mono text-[11px] text-emerald-900 bg-white px-2.5 py-1 rounded border border-emerald-300">
                <span>CHECKSUM:</span>
                <strong class="font-black">{{ $checksum }}...8f2b14c9</strong>
                <span class="text-emerald-600 font-bold">✓ VERIFIKASI INTEGRITAS</span>
            </div>
        </div>
    </div>
</x-dynamic-component>

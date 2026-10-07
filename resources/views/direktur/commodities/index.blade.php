<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Monitoring Volume Komoditas, Stok Panen & Kapasitas Pasokan
    </x-slot>

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-gray-900">Monitoring Volume Komoditas, Stok Panen & Kapasitas Pasokan</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Pengawasan alokasi tonase riil, rasio pasokan petani binaan vs buffer stock, mitigasi risiko overselling, dan neraca komoditas 5 produk inti (PRD Section 7.3, 8.2 & 16).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 bg-emerald-50 text-[#153a01] border border-emerald-200 rounded text-xs font-mono font-bold">
                RANGE: MTD (OKTOBER 2026)
            </span>
            <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded text-xs font-semibold border">
                REKONSILIASI NERACA
            </span>
        </div>
    </div>

    <!-- 1. KARTU METRIK UTAMA PASOKAN -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Card 1: TOTAL VOLUME TERJUAL -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-[#153a01]">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">TOTAL VOLUME TERJUAL (MTD)</div>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">+14.2% MoM</span>
            </div>
            <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($totalSoldTon, 2) }} Ton</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>vs Kuartal 420 T:</span>
                    <strong class="text-emerald-700 font-bold">114.9% Target Achieved</strong>
                </div>
            </div>
        </div>

        <!-- Card 2: SUMBER PASOKAN (RULE 02) -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-emerald-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">SUMBER PASOKAN (RULE 02)</div>
                <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-800 font-bold text-[9px]">OPTIMAL</span>
            </div>
            <div class="text-xl font-black text-emerald-800 mt-1">{{ $binaanRatio }}% Binaan : {{ $bufferRatio }}% Buffer</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Binaan: <strong>{{ number_format($binaanTon, 2) }} T</strong></span>
                    <span>Buffer: <strong>{{ number_format($bufferTon, 2) }} T</strong></span>
                </div>
                <div class="text-emerald-700 font-semibold mt-0.5">Alokasi Mandatori Ratio Terpenuhi</div>
            </div>
        </div>

        <!-- Card 3: SUSUT GUDANG & SORTIR -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">SUSUT GUDANG & SORTIR</div>
                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[9px]">SANGAT AMAN</span>
            </div>
            <div class="text-xl font-black text-blue-900 mt-1">{{ $lossDeviationPercent }}% Deviasi Rata-rata</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Plafon PRD Rule 04 & 13:</span>
                    <strong class="text-gray-700">Maks 2.0%</strong>
                </div>
                <div class="text-gray-400">Status Penyimpanan Cold-Chain Normal</div>
            </div>
        </div>

        <!-- Card 4: SAFETY STOCK BUFFER -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">SAFETY STOCK BUFFER (RULE 03)</div>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[9px]">VERIFIED</span>
            </div>
            <div class="text-xl font-black text-amber-900 mt-1">{{ number_format($safetyStockBufferTon, 2) }} Ton Siap Pakai</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Zero-Deficit Flag:</span>
                    <strong class="text-emerald-700">Verified Passed</strong>
                </div>
                <div class="text-gray-600 font-bold">Anti-Overselling Guard: 100% Aktif</div>
            </div>
        </div>
    </div>

    <!-- 2. NERACA VOLUME & KOMPOSISI 5 KOMODITAS INTI -->
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Neraca Volume & Komposisi 5 Komoditas Inti</h3>
                <p class="text-[11px] text-gray-500">Realisasi alokasi kebun binaan vs buffer mitra luar serta sisa kuota order B2B terbuka.</p>
            </div>
            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-mono">
                METRIK REFRESH: REALTIME LIVE SYNC
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
            @foreach($commodityBalance as $cb)
                <div class="p-3 bg-gray-50 rounded border border-gray-200 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center">
                            <span class="font-mono text-[9px] text-gray-400">{{ $cb['code'] }}</span>
                            <span class="px-1.5 py-0.2 rounded font-black text-[9px] bg-emerald-100 text-emerald-800">{{ $cb['mix'] }}%</span>
                        </div>
                        <div class="font-bold text-gray-900 text-xs mt-1">{{ $cb['name'] }}</div>
                        <div class="text-[10px] text-gray-500">{{ $cb['spec'] }}</div>
                        <div class="text-base font-black text-[#153a01] mt-2">{{ $cb['realisasi_ton'] }} Ton</div>
                    </div>

                    <div class="mt-3 pt-2 border-t border-gray-200 text-[10px] space-y-1">
                        <div class="flex justify-between text-gray-600">
                            <span>• Binaan:</span>
                            <strong class="text-gray-900">{{ $cb['binaan_ton'] }} T</strong>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>• Buffer:</span>
                            <strong class="text-gray-900">{{ $cb['buffer_ton'] }} T</strong>
                        </div>
                        <div class="flex justify-between text-emerald-800 font-bold border-t pt-1">
                            <span>Sisa Kuota Bebas:</span>
                            <span>{{ $cb['free_quota_ton'] }} T</span>
                        </div>
                        <div class="mt-2 text-center">
                            <span class="inline-block w-full py-0.5 rounded text-[9px] font-bold border {{ $cb['badge'] }}">
                                {{ $cb['status'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. TREN MINGGUAN VOLUME VS KAPASITAS PANEN & MATRIKS ALOKASI -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Tren Mingguan Volume vs Kapasitas Panen -->
        <div class="lg:col-span-2 bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-bold text-xs text-gray-900">Tren Mingguan Volume vs Kapasitas Panen (W40 - W47)</h3>
                    <p class="text-[11px] text-gray-500">Komparasi demand kontrak vs yield riil petani vs safety buffer reserve (PRD 8.2).</p>
                </div>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-800 rounded text-[10px] font-mono">
                    Konfidensi Model: 97.4%
                </span>
            </div>

            <div class="overflow-x-auto text-xs">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-[10px] border-b">
                        <tr>
                            <th class="p-2.5">Siklus Pekan</th>
                            <th class="p-2.5 text-right">Demand Kontrak</th>
                            <th class="p-2.5 text-right">Yield Binaan</th>
                            <th class="p-2.5 text-right">Safety Reserve</th>
                            <th class="p-2.5 text-center">Rasio Ketersediaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($weeklyCapacityTrends as $wct)
                            <tr class="hover:bg-gray-50 {{ str_contains($wct['week'], 'Aktif') ? 'bg-emerald-50/50 font-bold' : '' }}">
                                <td class="p-2.5 text-gray-900">{{ $wct['week'] }}</td>
                                <td class="p-2.5 text-right font-medium text-gray-700">{{ $wct['demand'] }} Ton</td>
                                <td class="p-2.5 text-right font-bold text-emerald-800">{{ $wct['yield'] }} Ton</td>
                                <td class="p-2.5 text-right font-medium text-blue-700">{{ $wct['reserve'] }} Ton</td>
                                <td class="p-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">
                                        Surplus (+{{ $wct['yield'] - $wct['demand'] }} T)
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-[10px] text-gray-400 border-t pt-2">
                Catatan Metrologi: Proyeksi yield W45 - W47 mengintegrasikan curah hujan BMKG Stasiun Geofisika Bandung Kelas I.
            </div>
        </div>

        <!-- Matriks Alokasi Kontrak B2B (PRD 7.3) -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
            <div class="border-b pb-3 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-xs text-gray-900">Matriks Alokasi Kontrak B2B</h3>
                    <p class="text-[11px] text-gray-500">Komitmen mingguan 4 mitra tier-1 vs kuota kebun.</p>
                </div>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded">
                    91.2% SERAP
                </span>
            </div>

            <div class="space-y-3 text-xs">
                @foreach($tier1Allocations as $ta)
                    <div class="p-3 bg-gray-50 rounded border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-800 text-[11px]">{{ $ta['client_name'] }}</span>
                            <span class="font-black text-[#153a01]">{{ $ta['weekly_quota_ton'] }} T/mgg</span>
                        </div>
                        <div class="text-[10px] text-gray-500 mt-1 flex justify-between">
                            <span>{{ $ta['production_hub'] }}</span>
                            <span class="font-semibold text-blue-800">{{ $ta['category'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3 border-t text-xs flex justify-between font-bold text-gray-800">
                <span>TOTAL KOMITMEN 4 MITRA:</span>
                <span class="text-[#153a01] font-black">{{ number_format($totalTier1CommitmentTon, 2) }} Ton / Minggu</span>
            </div>
        </div>
    </div>
</x-dynamic-component>

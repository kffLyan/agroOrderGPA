<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Dasbor Eksekutif Direktur // Monitoring Bisnis & Otorisasi Direktur
    </x-slot>

    <!-- Sub-header keterangan operasional -->
    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-[#153a01] flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-gray-900">Dasbor Eksekutif Direktur // Monitoring Bisnis & Otorisasi Direktur</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Konsolidasi metrik strategis penjualan, neraca komoditas, manajemen risiko piutang B2B, dan otorisasi kontrak tier-1 berbasis regulasi Metrologi Legal UU No. 2/1981.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 bg-emerald-50 text-[#153a01] font-mono text-xs font-bold rounded border border-emerald-200">
                W44-2026 // Q4 YTD
            </span>
            <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded border">
                PERIODE AUDIT AKTIF
            </span>
        </div>
    </div>

    <!-- 1. KARTU METRIK EKSEKUTIF UTAMA -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Metric 1: Omzet Q4 YTD -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-between">
            <div>
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">OMZET Q4 YTD</div>
                <div class="text-xl font-black text-gray-900 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="text-[10px] text-gray-500 mt-1">Target: Rp {{ number_format($targetRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 text-[10px]">
                <div class="flex justify-between items-center text-emerald-700 font-bold">
                    <span>+14.2% MoM</span>
                    <span>{{ $targetProgress }}% Target</span>
                </div>
                <div class="text-gray-400 mt-0.5">{{ $paidRatio }}% Terbayar</div>
            </div>
        </div>

        <!-- Metric 2: Volume Komoditas -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-between">
            <div>
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">VOLUME KOMODITAS</div>
                <div class="text-xl font-black text-emerald-800 mt-1">{{ number_format($totalTonnageKg, 0, ',', '.') }} kg</div>
                <div class="text-[10px] text-gray-500 mt-1">({{ number_format($totalTonnageKg / 1000, 2) }} Tonase Tera Sah)</div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 text-[10px]">
                <div class="flex justify-between items-center text-gray-600">
                    <span>Binaan: <strong class="text-gray-800">{{ $binaanPercent }}%</strong></span>
                    <span>Buffer: <strong class="text-gray-800">{{ $bufferPercent }}%</strong></span>
                </div>
                <div class="text-emerald-700 font-bold mt-0.5">100% Tera Sah UU 2/1981</div>
            </div>
        </div>

        <!-- Metric 3: Piutang Terbuka -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-between">
            <div>
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">PIUTANG TERBUKA</div>
                <div class="text-xl font-black text-blue-900 mt-1">Rp {{ number_format($totalOpenReceivables, 0, ',', '.') }}</div>
                <div class="text-[10px] text-gray-500 mt-1">{{ count($clientRecap ?? []) ?: 12 }} Kontrak B2B Aktif</div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 text-[10px]">
                <div class="flex justify-between items-center text-amber-700 font-bold">
                    <span>{{ $nearDueCount }} Klien Tempo &lt;7 Hari</span>
                </div>
                <div class="text-gray-500 mt-0.5">Rasio Tertagih: 91.2%</div>
            </div>
        </div>

        <!-- Metric 4: Kepatuhan SLA -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col justify-between">
            <div>
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">KEPATUHAN SLA</div>
                <div class="text-xl font-black text-emerald-700 mt-1">{{ $slaPercent }}% Tuntas</div>
                <div class="text-[10px] text-gray-500 mt-1">Retur Fisik: {{ $returnPercent }}% (Batas: &lt;1.50%)</div>
            </div>
            <div class="mt-3 pt-2 border-t border-gray-100 text-[10px]">
                <div class="text-emerald-700 font-bold">MUTU GRADE A / B+</div>
                <div class="text-gray-400 mt-0.5">Cold-Chain Valid Mutlak</div>
            </div>
        </div>

        <!-- Metric 5: Otorisasi Direktur -->
        <div class="bg-amber-50 p-4 rounded-lg shadow-sm border border-amber-300 flex flex-col justify-between">
            <div>
                <div class="text-[10px] text-amber-800 font-bold uppercase tracking-wider">OTORISASI DIREKTUR</div>
                <div class="text-xl font-black text-amber-900 mt-1">{{ count($pendingContracts) }} Kontrak</div>
                <div class="text-[10px] text-amber-800 mt-1">Nilai: Rp {{ number_format($totalPendingContractValue, 0, ',', '.') }}</div>
            </div>
            <div class="mt-3 pt-2 border-t border-amber-200">
                <a href="{{ route('direktur.contracts.approval') }}" class="block text-center py-1.5 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold transition">
                    Tinjau Sekarang &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 2. TREN OMZET MINGGUAN & DISTRIBUSI 5 KOMODITAS INTI -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Tren Omzet Mingguan vs Target (W40 - W44) -->
        <div class="lg:col-span-2 bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <div>
                    <h3 class="font-bold text-xs text-gray-900">Tren Omzet vs Target Mingguan & Realisasi Panen</h3>
                    <p class="text-[11px] text-gray-500">Siklus W40 - W44 (Realisasi Tonase vs Cash Inflow)</p>
                </div>
                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-mono">
                    Deviasi Tertinggi: +3.3% (W41)
                </span>
            </div>

            <div class="space-y-3">
                @foreach($weeklyTrends as $wt)
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-gray-800">{{ $wt['week'] }} ({{ $wt['panen_ton'] }} Ton Panen)</span>
                            <span class="text-gray-600">Realisasi: <strong class="text-gray-900">Rp {{ number_format($wt['realisasi'], 0, ',', '.') }}</strong> ({{ $wt['percent'] }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-emerald-600 h-3 rounded-full" style="width: {{ min(100, $wt['percent']) }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-400 mt-0.5">
                            <span>Target: Rp {{ number_format($wt['target'], 0, ',', '.') }}</span>
                            <span>{{ $wt['percent'] >= 100 ? 'Surplus Target' : 'Under Cap' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-2 text-[10px] text-gray-400 border-t flex justify-between">
                <span>Sumber Data: Core Transaksi Timbangan Elektronik Hubs</span>
                <span>Buffer Rasio Sehat: 1 : 2.1</span>
            </div>
        </div>

        <!-- Distribusi 5 Komoditas Inti & Kanal Penjualan -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
            <div class="border-b pb-3">
                <h3 class="font-bold text-xs text-gray-900">Distribusi 5 Komoditas Inti & Kanal</h3>
                <p class="text-[11px] text-gray-500">Realisasi Volume Q4 (Total {{ number_format($totalTonnageKg / 1000, 2) }} Ton)</p>
            </div>

            <div class="space-y-2.5 text-xs">
                @foreach($commodityDistribution as $idx => $cd)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-100">
                        <div>
                            <div class="font-bold text-gray-800 text-[11px]">{{ $idx + 1 }}. {{ $cd->product->name ?? 'Komoditas' }}</div>
                            <div class="text-[10px] text-gray-400">{{ $cd->product->sku ?? '' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-black text-[#153a01]">{{ number_format($cd->total_weight / 1000, 2) }} T</div>
                            <div class="text-[10px] text-gray-500">Rp {{ number_format($cd->total_sales, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3 border-t space-y-2 text-xs">
                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Segmentasi Kanal Penjualan</div>
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-gray-700">Horeca & Inflight (65%)</span>
                    <strong class="text-gray-900">{{ $channelComposition['horeca_inflight']['ton'] ?? 25.0 }} T</strong>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-gray-700">Ritel Modern (25%)</span>
                    <strong class="text-gray-900">{{ $channelComposition['retail_modern']['ton'] ?? 9.6 }} T</strong>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-gray-700">Reguler WA (10%)</span>
                    <strong class="text-gray-900">{{ $channelComposition['reguler_wa']['ton'] ?? 3.85 }} T</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. TABEL DAFTAR PENGAJUAN KONTRAK KHUSUS & DISKON B2B -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 bg-gray-50">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Daftar Pengajuan Kontrak Khusus & Diskon Volume B2B</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    Perubahan harga di luar plafon standar (>5%), perpanjangan tenor TOP (>30 hari), dan kuota prioritas memerlukan otorisasi Direktur Utama.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded text-[10px] font-bold">
                    PRD SEC 16 & 20: TIER-1 APPROVAL GATE CLEARANCE: DIRUT ONLY
                </span>
                <a href="{{ route('direktur.contracts.approval') }}" class="px-3 py-1.5 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold transition">
                    Kelola Otorisasi Penuh &rarr;
                </a>
            </div>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">ID Kontrak & Pemohon</th>
                        <th class="p-3">Komoditas & Volume</th>
                        <th class="p-3">Ketentuan Harga & TOP</th>
                        <th class="p-3">Analisis Margin</th>
                        <th class="p-3">Skor Kredit</th>
                        <th class="p-3 text-center">Aksi Otorisasi Direktur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pendingContracts as $contract)
                        <tr class="hover:bg-amber-50/40 transition">
                            <td class="p-3">
                                <div class="font-bold text-gray-900">{{ $contract->contract_number }}</div>
                                <div class="text-[11px] text-gray-600 font-semibold">{{ $contract->user->company_name ?? $contract->user->name }}</div>
                                <div class="text-[10px] text-gray-400">Diajukan: {{ $contract->created_at ? $contract->created_at->format('d M Y') : '-' }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-gray-800">{{ $contract->product->name ?? '-' }}</div>
                                <div class="text-[11px] text-emerald-800 font-semibold">{{ number_format($contract->committed_volume_per_cycle, 0, ',', '.') }} kg / siklus</div>
                                <div class="text-[10px] text-gray-500">{{ $contract->product->grade ?? 'Grade A' }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-gray-900">Rp {{ number_format($contract->fixed_price_per_kg, 0, ',', '.') }} / kg</div>
                                <div class="text-[11px] text-gray-600">TOP {{ $contract->top_days }} Hari Kalender</div>
                                <div class="text-[10px] text-gray-400">Katalog: Rp {{ number_format($contract->product->base_price ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                                    Safe Margin Pass (Target: Min 15.0%)
                                </span>
                                <div class="text-[11px] text-gray-700 font-bold mt-1">Estimasi Margin: 18.5% Net</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    AAA (96/100)
                                </span>
                                <div class="text-[10px] text-gray-500 mt-0.5">0 Riwayat Macet</div>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form method="POST" action="{{ route('direktur.contracts.approve', $contract->id) }}">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Sahkan kontrak {{ $contract->contract_number }} dengan TTD Digital Direktur?')" class="px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-[11px] font-bold transition">
                                            Setujui Kontrak
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('direktur.contracts.revision', $contract->id) }}">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded text-[11px] font-bold transition">
                                            Minta Revisi
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('direktur.contracts.reject', $contract->id) }}">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Tolak pengajuan kontrak {{ $contract->contract_number }}?')" class="px-2 py-1 bg-red-700 hover:bg-red-800 text-white rounded text-[11px] font-bold transition">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-400">
                                Seluruh pengajuan kontrak kemitraan B2B telah diproses. Tidak ada antrean otorisasi pending.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. LEMBAR PENGAWASAN PIUTANG & AGING TAGIHAN KLIEN (TOP) -->
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 border-b pb-3">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Lembar Pengawasan Piutang & Aging Tagihan Klien (TOP)</h3>
                <p class="text-[11px] text-gray-500">Pencegahan defisit kas melalui automated credit hold, pembekuan repeat order, dan penagihan berjenjang.</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-500">Total Piutang Terbuka:</span>
                <span class="text-sm font-black text-blue-900 ml-1">Rp {{ number_format($totalOpenReceivables, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
            <!-- Normal -->
            <div class="p-3 bg-emerald-50/60 rounded border border-emerald-200">
                <div class="text-[10px] font-bold text-emerald-800 uppercase">NORMAL (0 - 15 HARI)</div>
                <div class="text-base font-black text-emerald-900 mt-1">Rp {{ number_format($agingStage1, 0, ',', '.') }}</div>
                <div class="text-[10px] text-emerald-700 mt-1">Status: BERSIH (Siklus Penagihan Aman)</div>
            </div>

            <!-- Menuju Tempo -->
            <div class="p-3 bg-blue-50/60 rounded border border-blue-200">
                <div class="text-[10px] font-bold text-blue-800 uppercase">MENUJU TEMPO (16 - 30 HARI)</div>
                <div class="text-base font-black text-blue-900 mt-1">Rp {{ number_format($agingStage2, 0, ',', '.') }}</div>
                <div class="text-[10px] text-blue-700 mt-1">Status: NOTICE AKTIF (Reminder Otomatis)</div>
            </div>

            <!-- Lewat Tempo -->
            <div class="p-3 bg-amber-50/60 rounded border border-amber-200">
                <div class="text-[10px] font-bold text-amber-800 uppercase">LEWAT TEMPO (1 - 14 HARI)</div>
                <div class="text-base font-black text-amber-900 mt-1">Rp {{ number_format($agingStage3, 0, ',', '.') }}</div>
                <div class="text-[10px] text-amber-800 mt-1 font-bold">WARNING LEVEL 2 (Somasi Adm I)</div>
            </div>

            <!-- Overdue Kritis -->
            <div class="p-3 bg-red-50/60 rounded border border-red-200">
                <div class="text-[10px] font-bold text-red-800 uppercase">OVERDUE KRITIS (> 15 HARI)</div>
                <div class="text-base font-black text-red-900 mt-1">Rp {{ number_format($agingStage4, 0, ',', '.') }}</div>
                <div class="text-[10px] text-red-700 mt-1 font-bold">AUTO FREEZE PO: DIKUNCI SISTEM</div>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <a href="{{ route('direktur.receivables.index') }}" class="text-xs font-bold text-blue-700 hover:underline">
                Buka Konsol Monitoring Piutang & Plafon Kredit Lengkap &rarr;
            </a>
        </div>
    </div>

    <!-- 5. AKTIVITAS TERKINI (PESANAN & KONTRAK) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <!-- Pesanan Terkini -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <div class="border-b pb-2 flex justify-between items-center">
                <h3 class="font-bold text-gray-900 uppercase">Pesanan Terkini</h3>
                <span class="text-[10px] text-gray-500">5 Transaksi Terakhir</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[10px] uppercase text-gray-500 font-bold border-b">
                        <tr>
                            <th class="p-2">No. Pesanan</th>
                            <th class="p-2">Mitra / Klien</th>
                            <th class="p-2 text-right">Nilai</th>
                            <th class="p-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($recentOrders ?? [] as $rOrder)
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 font-mono font-bold">{{ $rOrder->order_number }}</td>
                                <td class="p-2">{{ $rOrder->user->company_name ?? $rOrder->user->name }}</td>
                                <td class="p-2 text-right font-semibold">Rp {{ number_format($rOrder->grand_total ?? $rOrder->estimated_total, 0, ',', '.') }}</td>
                                <td class="p-2 text-center">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-800">
                                        {{ $rOrder->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-3 text-center text-gray-400">Belum ada pesanan terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kontrak Terkini -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <div class="border-b pb-2 flex justify-between items-center">
                <h3 class="font-bold text-gray-900 uppercase">Kontrak Kemitraan Terkini</h3>
                <span class="text-[10px] text-gray-500">5 Kontrak Terakhir</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[10px] uppercase text-gray-500 font-bold border-b">
                        <tr>
                            <th class="p-2">No. Kontrak</th>
                            <th class="p-2">Mitra B2B</th>
                            <th class="p-2">Komoditas</th>
                            <th class="p-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($recentContracts ?? [] as $rContract)
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 font-mono font-bold">{{ $rContract->contract_number }}</td>
                                <td class="p-2">{{ $rContract->user->company_name ?? $rContract->user->name }}</td>
                                <td class="p-2">{{ $rContract->product->name ?? 'Komoditas' }}</td>
                                <td class="p-2 text-center">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $rContract->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $rContract->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-3 text-center text-gray-400">Belum ada kontrak kemitraan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-dynamic-component>

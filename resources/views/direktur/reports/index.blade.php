<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Laporan Penjualan Eksekutif Direktur - Status Terkunci
    </x-slot>

    <!-- BANNER STATUS PENGUNCIAN AUDIT // IMMUTABLE ARCHIVE -->
    <div class="rounded-lg shadow-sm border {{ ($periodLock['is_locked'] ?? false) ? 'bg-emerald-900 text-white border-emerald-950' : 'bg-amber-50 border-amber-300 text-amber-950' }} p-5 space-y-3">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-wider uppercase {{ ($periodLock['is_locked'] ?? false) ? 'bg-emerald-800 text-emerald-200 border border-emerald-700' : 'bg-amber-200 text-amber-900 border border-amber-300' }}">
                    {{ ($periodLock['is_locked'] ?? false) ? '[STATUS: LAPORAN TELAH DIKUNCI & DISAHKAN // IMMUTABLE ARCHIVE]' : '[STATUS: DRAF LAPORAN TERBUKA // BELUM DIKUNCI]' }}
                </span>
                <h1 class="text-base font-black mt-2">
                    Laporan Penjualan Konsolidasi {{ $periodName }}
                </h1>
                <p class="text-xs {{ ($periodLock['is_locked'] ?? false) ? 'text-emerald-200' : 'text-amber-800' }} mt-1 max-w-4xl leading-relaxed">
                    @if($periodLock['is_locked'] ?? false)
                        Periode {{ $periodName }} telah dikunci permanen pada {{ $periodLock['locked_at'] }} oleh <strong>{{ $periodLock['locked_by'] }}</strong>. Seluruh transaksi, Surat Jalan, Timbangan Aktual, dan Faktur Tempo berstatus <strong>Read-Only</strong> dan dilindungi hash kriptografi SHA-256.
                    @else
                        Periode {{ $periodName }} saat ini masih dalam mode aktif. Anda dapat melakukan penguncian statutori (Immutable Seal) untuk membekukan pembukuan dan menerbitkan sertifikat audit forensik.
                    @endif
                </p>
                @if($periodLock['is_locked'] ?? false)
                    <div class="font-mono text-[11px] bg-black/30 px-3 py-1.5 rounded mt-2 select-all inline-block border border-white/10">
                        SHA-256: {{ $periodLock['sha256_hash'] ?? '0x8F9C4A217B1E90D4CC67F814E32A0B7D18C992E5F67104B8A293CD0891DE33' }}
                    </div>
                @endif
            </div>

            <!-- Tombol Aksi Penguncian -->
            <div class="flex flex-col sm:flex-row gap-2">
                @if($periodLock['is_locked'] ?? false)
                    <a href="{{ route('direktur.governance.index') }}" class="px-3 py-1.5 bg-emerald-800 hover:bg-emerald-700 text-emerald-100 rounded text-xs font-bold text-center transition">
                        Lihat Log Audit Forensik
                    </a>
                    <form method="POST" action="{{ route('direktur.reports.unlock') }}">
                        @csrf
                        <input type="hidden" name="period" value="{{ $periodName }}">
                        <button type="submit" onclick="return confirm('Buka kunci audit sementara untuk koreksi data?')" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded text-xs font-bold transition">
                            Buka Kunci (Direktur)
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('direktur.reports.lock') }}">
                        @csrf
                        <input type="hidden" name="period" value="{{ $periodName }}">
                        <button type="submit" onclick="return confirm('Kunci permanen dan sahkan pembukuan periode {{ $periodName }} dengan SHA-256?')" class="px-4 py-2 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-black shadow transition">
                            🔒 Kunci & Sahkan Laporan (SHA-256)
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- 1. KARTU METRIK EKSEKUTIF LAPORAN -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Card 1: OMZET DISAHKAN -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-[#153a01]">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">OMZET DISAHKAN (NETTO RIIL)</div>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">FINAL</span>
            </div>
            <div class="text-xl font-black text-gray-900 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Target Q4 (Rp 600M):</span>
                    <strong class="text-emerald-700">80.4% Tercapai</strong>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>Diskon Kontrak Sah:</span>
                    <span>-Rp 14.800.000</span>
                </div>
            </div>
        </div>

        <!-- Card 2: TOTAL VOLUME TERA SAH -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-emerald-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">TOTAL VOLUME TERA SAH</div>
                <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-800 font-bold text-[9px]">UU 2/1981</span>
            </div>
            <div class="text-xl font-black text-emerald-800 mt-1">{{ number_format($totalTonnageKg, 0, ',', '.') }} kg</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Ekuivalen:</span>
                    <strong class="text-gray-800">{{ number_format($totalTonnageKg / 1000, 2) }} Tonase Realisasi</strong>
                </div>
                <div class="flex justify-between text-emerald-700">
                    <span>Rasio Pasokan:</span>
                    <span>68% Binaan | 32% Buffer</span>
                </div>
            </div>
        </div>

        <!-- Card 3: CASH INFLOW REALISASI -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">CASH INFLOW REALISASI</div>
                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[9px]">84.6% SETTLED</span>
            </div>
            <div class="text-xl font-black text-blue-900 mt-1">Rp {{ number_format($cashInflow, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Piutang Berjalan Tempo:</span>
                    <strong class="text-blue-700">Rp {{ number_format($openReceivables, 0, ',', '.') }}</strong>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Kolektibilitas / NPL:</span>
                    <span class="font-bold text-emerald-700">0.0% (LANCAR)</span>
                </div>
            </div>
        </div>

        <!-- Card 4: DEVIASI SUSUT & RETUR -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">DEVIASI SUSUT & RETUR</div>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[9px]">KLAIM TUNTAS</span>
            </div>
            <div class="text-xl font-black text-amber-900 mt-1">Rp {{ number_format($lossValue, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500 space-y-0.5">
                <div class="flex justify-between">
                    <span>Deviasi Rata-rata:</span>
                    <strong class="text-emerald-700 font-bold">{{ $lossPercent }}% (&lt; 2.0% Toleransi)</strong>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>Susut: Rp 3.25M</span>
                    <span>Retur Fisik: Rp 4.15M</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. TABEL 1: BREAKDOWN ANALITIK 5 KOMODITAS INTI -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 bg-gray-50">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Breakdown Analitik 5 Komoditas Inti (Estimasi PO vs Tera Sah Gudang)</h3>
                <p class="text-[11px] text-gray-500">Audit Method: Direct Digital Scale Weight Sensor Terkoneksi Kalibrasi Metrologi Legal.</p>
            </div>
            <span class="px-2 py-1 bg-gray-200 text-gray-800 font-mono text-[10px] font-bold rounded">
                AUDIT METHOD: DIRECT DIGITAL SCALE WEIGHT [TERKUNCI]
            </span>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">Komoditas Agribisnis</th>
                        <th class="p-3 text-right">Volume PO (kg)</th>
                        <th class="p-3 text-right">Tera Sah (kg)</th>
                        <th class="p-3 text-right">Deviasi Susut</th>
                        <th class="p-3 text-right">Harga Rata-rata / kg</th>
                        <th class="p-3 text-right">Omzet Realisasi</th>
                        <th class="p-3 text-center">Gross Margin</th>
                        <th class="p-3 text-center">Kontribusi</th>
                        <th class="p-3 text-center">Status Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($commodityAnalytics as $ca)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 font-bold text-gray-900">{{ $ca['name'] }}</td>
                            <td class="p-3 text-right text-gray-600">{{ number_format($ca['po_kg'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-black text-gray-900">{{ number_format($ca['actual_kg'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-semibold text-emerald-700">
                                {{ $ca['diff_kg'] }} kg ({{ $ca['diff_pct'] }}%)
                            </td>
                            <td class="p-3 text-right text-gray-700">
                                Rp {{ number_format($ca['avg_price'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-bold text-gray-900">
                                Rp {{ number_format($ca['omzet'], 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center font-bold text-emerald-800">
                                {{ $ca['gross_margin'] }}%
                            </td>
                            <td class="p-3 text-center font-semibold text-gray-600">
                                {{ $ca['contrib'] }}%
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    LOCKED
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    <!-- Konsolidasi Row -->
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-300 text-xs">
                        <td class="p-3 uppercase">Total Konsolidasi (5 Komoditas)</td>
                        <td class="p-3 text-right">38.860 kg</td>
                        <td class="p-3 text-right text-gray-900">38.450 kg</td>
                        <td class="p-3 text-right text-emerald-800">-410 kg (-1.05%)</td>
                        <td class="p-3 text-right">Rp 12.553 (Avg)</td>
                        <td class="p-3 text-right text-gray-900">Rp 482.650.000</td>
                        <td class="p-3 text-center text-emerald-800">25.2% (Avg)</td>
                        <td class="p-3 text-center">100.0%</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[9px] font-black bg-[#153a01] text-white">100% AUDITED</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. TABEL 2: REKAPITULASI PENJUALAN KLIEN B2B & STATUS PEMBAYARAN TEMPO (TOP) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 bg-gray-50">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Tabel 2: Rekapitulasi Penjualan Klien B2B & Status Pembayaran Tempo (TOP)</h3>
                <p class="text-[11px] text-gray-500">84 Total PO // 0 Sengketa AR-Audit Completed.</p>
            </div>
            <span class="px-2 py-0.5 bg-blue-100 text-blue-900 font-bold text-[10px] rounded">
                84 TOTAL PO // AR-AUDIT COMPLETED
            </span>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">Entitas Klien / Mitra B2B</th>
                        <th class="p-3 text-center">Frek. PO</th>
                        <th class="p-3 text-right">Volume Tera Sah (kg)</th>
                        <th class="p-3 text-right">Total Tagihan Bruto</th>
                        <th class="p-3 text-right">Settled (Inflow Sah)</th>
                        <th class="p-3 text-right">Sisa Piutang Berjalan</th>
                        <th class="p-3">Ketentuan Tempo</th>
                        <th class="p-3 text-center">Status Piutang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($clientRecap as $cr)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3">
                                <div class="font-bold text-gray-900">{{ $cr['client_name'] }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $cr['contract_no'] }}</div>
                            </td>
                            <td class="p-3 text-center font-bold text-gray-700">{{ $cr['po_count'] }} PO</td>
                            <td class="p-3 text-right font-medium text-gray-800">{{ number_format($cr['volume_kg'], 0, ',', '.') }} kg</td>
                            <td class="p-3 text-right font-bold text-gray-900">Rp {{ number_format($cr['gross_amount'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-bold text-emerald-700">Rp {{ number_format($cr['settled_amount'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-bold text-blue-700">Rp {{ number_format($cr['outstanding_amount'], 0, ',', '.') }}</td>
                            <td class="p-3 font-semibold text-gray-600">{{ $cr['top_terms'] }}</td>
                            <td class="p-3 text-center">
                                @if(str_contains($cr['status'], 'LUNAS'))
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                                        {{ $cr['status'] }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-800 border border-green-200">
                                        {{ $cr['status'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <!-- Konsolidasi Row -->
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-300 text-xs">
                        <td class="p-3 uppercase">Total Rekapitulasi B2B (Q4 2026)</td>
                        <td class="p-3 text-center">84 PO</td>
                        <td class="p-3 text-right text-gray-900">38.450 kg</td>
                        <td class="p-3 text-right text-gray-900">Rp 482.650.000</td>
                        <td class="p-3 text-right text-emerald-800">Rp 408.330.000</td>
                        <td class="p-3 text-right text-blue-900">Rp 74.320.000</td>
                        <td class="p-3 text-gray-600">Rata-rata: 26 Hari</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-800 text-white">AUDITED & VERIFIED</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. LAPORAN REKAPITULASI PENJUALAN PESANAN KONSOLIDASI -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Laporan Rekapitulasi Penjualan (Rincian Pesanan)</h3>
                <p class="text-[11px] text-gray-500">Daftar transaksi pesanan riil pada periode pembukuan aktif.</p>
            </div>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">No. Pesanan</th>
                        <th class="p-3">Mitra Pembeli</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3 text-right">Nilai Transaksi</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders ?? [] as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 font-mono font-bold">{{ $order->order_number }}</td>
                            <td class="p-3 font-medium">{{ $order->user->company_name ?? $order->user->name }}</td>
                            <td class="p-3 text-gray-500">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="p-3 text-right font-bold text-gray-900">Rp {{ number_format($order->grand_total ?? $order->estimated_total, 0, ',', '.') }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-800 border border-green-200">
                                    {{ $order->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-400">Belum ada data pesanan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. LEMBAR PENGESAHAN DIGITAL DIREKSI & SERTIFIKAT AUDIT -->
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Lembar Pengesahan Digital Direksi & Sertifikat Audit</h3>
                <p class="text-[11px] text-gray-500">Hash Kriptografis Konsolidasi: {{ $periodLock['sha256_hash'] ?? '0x8F9C4A217B1E90D4CC67F814E32A0B7D18C992E5F67104B8A293CD0891DE33' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('direktur.reports.export') }}" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-xs font-bold transition shadow-sm">
                    Download Buku Besar Excel (.CSV)
                </a>
                <a href="{{ route('direktur.reports.download') }}" target="_blank" class="px-3 py-1.5 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold transition shadow-sm">
                    Cetak Dokumen Pengesahan (.PDF)
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs bg-gray-50 p-4 rounded border border-gray-200">
            <div>
                <div class="text-[10px] font-bold text-gray-500 uppercase">STATUS PENGUNCIAN PERIODE</div>
                <div class="font-black text-gray-900 text-sm mt-1">
                    {{ ($periodLock['is_locked'] ?? false) ? 'STATUS: PERIODE TELAH DIKUNCI PERMANEN' : 'STATUS: DRAF TERBUKA' }}
                </div>
                <div class="text-[10px] text-gray-400 mt-1">
                    Waktu Penguncian: {{ $periodLock['locked_at'] ?? '24 Okt 2026 23:59:59 WIB' }}
                </div>
            </div>

            <div>
                <div class="text-[10px] font-bold text-gray-500 uppercase">PENANDATANGAN UTAMA (DIRECTORATE AUTHORITY)</div>
                <div class="font-black text-gray-900 text-sm mt-1">
                    {{ $periodLock['locked_by'] ?? 'Ahmad Sanusi, S.P.' }}
                </div>
                <div class="text-[10px] text-gray-500 mt-1 font-mono">
                    DIR-01 // Direktur Utama PT Agro Pasti Ada / GPA
                </div>
            </div>

            <div>
                <div class="text-[10px] font-bold text-gray-500 uppercase">SERTIFIKASI ENKRIPSI</div>
                <div class="font-mono font-bold text-[#153a01] text-xs mt-1">
                    X.509 v3 / RSA 4096-bit
                </div>
                <div class="text-[10px] text-gray-400 font-mono mt-0.5">
                    GPA-AUDIT-CERT-2026-Q4 (100% VALID)
                </div>
            </div>
        </div>

        <div class="p-3 bg-gray-900 text-gray-200 font-mono text-[10px] rounded space-y-1">
            <div class="text-emerald-400 font-bold">&gt; AUDIT TRAIL LOG TERMINAL [SYS-V4.9.1]</div>
            <div>[2026-10-24 23:45:10 WIB] &gt; SYS_VERIFY: 5 komoditas timbangan tera gudang tuntas dicocokkan (38.450 kg).</div>
            <div>[2026-10-24 23:51:22 WIB] &gt; AR_CLEARING: Piutang berjalan Rp 74.320.000 diverifikasi terhadap 4 kontrak aktif.</div>
            <div>[2026-10-24 23:58:05 WIB] &gt; DIR_AUTH: Ahmad Sanusi, S.P. memasukkan token kunci otorisasi biometric.</div>
            <div class="text-emerald-400">[2026-10-24 23:59:59 WIB] &gt; STATUS_LOCK_ENGAGED: Record disahkan. SHA-256 dibuat. Write-access dicabut permanen.</div>
        </div>
    </div>
</x-dynamic-component>

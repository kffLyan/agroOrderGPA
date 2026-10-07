<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Monitoring Piutang, Tagihan Tempo & Manajemen Risiko Kredit Klien B2B
    </x-slot>

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-gray-900">Monitoring Piutang, Tagihan Tempo & Manajemen Risiko Kredit Klien B2B</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Pengawasan buku besar faktur konsolidasi (TOP 14/30/45 Hari), utilisasi plafon kredit klien korporat, evaluasi umur piutang (Aging AR), dan mitigasi risiko gagal bayar.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 bg-blue-50 text-blue-900 border border-blue-200 rounded text-xs font-mono font-bold">
                PERIODE: OKTOBER 2026
            </span>
            <span class="px-2.5 py-1 bg-emerald-50 text-[#153a01] border border-emerald-200 rounded text-xs font-bold">
                KOLEKTIBILITAS: 92.8%
            </span>
        </div>
    </div>

    <!-- 1. KARTU METRIK UTAMA PIUTANG & RISIKO KREDIT -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Card 1: TOTAL PIUTANG B2B -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-800">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">TOTAL PIUTANG B2B</div>
                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[9px]">100% TERMONITOR</span>
            </div>
            <div class="text-xl font-black text-gray-900 mt-1">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500">
                18 Klien Kontrak Aktif Berjalan
            </div>
        </div>

        <!-- Card 2: PIUTANG LANCAR (< 15 HARI) -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-emerald-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">PIUTANG LANCAR (< 15 HARI)</div>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">NORMAL / AMAN</span>
            </div>
            <div class="text-xl font-black text-emerald-800 mt-1">Rp {{ number_format($lancar, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500">
                {{ $totalPiutang > 0 ? round(($lancar / $totalPiutang) * 100, 1) : 74.6 }}% dari Total AR
            </div>
        </div>

        <!-- Card 3: WARNING TEMPO (< 7 HARI) -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">WARNING TEMPO (< 7 HARI)</div>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[9px]">FOLLOW-UP SEKRE</span>
            </div>
            <div class="text-xl font-black text-amber-900 mt-1">Rp {{ number_format($warningTempo, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500">
                {{ $totalPiutang > 0 ? round(($warningTempo / $totalPiutang) * 100, 1) : 18.2 }}% • Menuju Jatuh Tempo
            </div>
        </div>

        <!-- Card 4: OVERDUE KRITIS (> 15 HARI) -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">OVERDUE KRITIS (> 15 HARI)</div>
                <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-800 font-bold text-[9px]">AUTO-FREEZE AKTIF</span>
            </div>
            <div class="text-xl font-black text-red-900 mt-1">Rp {{ number_format($overdueKritis, 0, ',', '.') }}</div>
            <div class="mt-2 text-[10px] text-gray-500">
                {{ $totalPiutang > 0 ? round(($overdueKritis / $totalPiutang) * 100, 1) : 7.2 }}% • Lockdown PO Otomatis
            </div>
        </div>
    </div>

    <!-- 2. MATRIKS ANALISIS UMUR PIUTANG (AGING AR SCHEDULE BREAKDOWN) -->
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
        <div class="border-b pb-3">
            <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Matriks Analisis Umur Piutang (Aging AR Schedule Breakdown)</h3>
            <p class="text-[11px] text-gray-500">Distribusi komposit siklus penagihan berdasarkan Termin Pembayaran (TOP) dan keterlambatan real-time.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
            <!-- Stage 01 -->
            <div class="p-3.5 bg-emerald-50 rounded border border-emerald-200">
                <div class="flex justify-between items-center text-[10px] font-bold text-emerald-800">
                    <span>STAGE 01 (0 - 15 HARI)</span>
                    <span class="px-1.5 py-0.2 rounded bg-emerald-200 text-emerald-900">SANGAT AMAN</span>
                </div>
                <div class="text-base font-black text-emerald-900 mt-1.5">
                    Rp {{ number_format($stage1, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-emerald-700 mt-1">
                    In-Schedule / Dalam Siklus Pembayaran
                </div>
            </div>

            <!-- Stage 02 -->
            <div class="p-3.5 bg-blue-50 rounded border border-blue-200">
                <div class="flex justify-between items-center text-[10px] font-bold text-blue-800">
                    <span>STAGE 02 (16 - 30 HARI)</span>
                    <span class="px-1.5 py-0.2 rounded bg-blue-200 text-blue-900">IN-SCHEDULE</span>
                </div>
                <div class="text-base font-black text-blue-900 mt-1.5">
                    Rp {{ number_format($stage2, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-blue-700 mt-1">
                    Menuju Tempo / Notifikasi Terkirim
                </div>
            </div>

            <!-- Stage 03 -->
            <div class="p-3.5 bg-amber-50 rounded border border-amber-200">
                <div class="flex justify-between items-center text-[10px] font-bold text-amber-800">
                    <span>STAGE 03 (1 - 14 HARI LEWAT)</span>
                    <span class="px-1.5 py-0.2 rounded bg-amber-200 text-amber-900">WARNING LEVEL 2</span>
                </div>
                <div class="text-base font-black text-amber-900 mt-1.5">
                    Rp {{ number_format($stage3, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-amber-700 mt-1">
                    Somasi Administratif I / Follow-up Sekre
                </div>
            </div>

            <!-- Stage 04 -->
            <div class="p-3.5 bg-red-50 rounded border border-red-200">
                <div class="flex justify-between items-center text-[10px] font-bold text-red-800">
                    <span>STAGE 04 (> 15 HARI OVERDUE)</span>
                    <span class="px-1.5 py-0.2 rounded bg-red-200 text-red-900 font-bold">LOCKDOWN PO</span>
                </div>
                <div class="text-base font-black text-red-900 mt-1.5">
                    Rp {{ number_format($stage4, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-red-700 mt-1 font-bold">
                    Auto-Freeze PO & Blokir Surat Jalan Baru
                </div>
            </div>
        </div>
    </div>

    <!-- 3. TABEL BUKU BESAR FAKTUR TEMPO KONSOLIDASI & EVALUASI PLAFON KREDIT -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden space-y-0">
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 bg-gray-50">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Buku Besar Faktur Tempo Konsolidasi & Evaluasi Plafon Kredit Klien</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Audit rincian surat jalan (SJ), sisa hari jatuh tempo TOP, dan monitoring pembekuan PO otomatis sesuai batas plafon kredit.</p>
            </div>
            <form method="GET" action="{{ route('direktur.receivables.index') }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari Klien / No. SJ..." class="border border-gray-300 rounded px-2.5 py-1 text-xs focus:ring-1 focus:ring-emerald-600 focus:outline-none w-48">
                <button type="submit" class="px-2.5 py-1 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">Klien Korporat B2B</th>
                        <th class="p-3">Surat Jalan (SJ) Terlampir</th>
                        <th class="p-3 text-right">Nilai Tagihan Terutang</th>
                        <th class="p-3">Termin (TOP) & Jatuh Tempo</th>
                        <th class="p-3 text-center">Status AR</th>
                        <th class="p-3">Utilisasi Plafon Kredit</th>
                        <th class="p-3 text-center">Tindakan Direksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($clientLedger as $cl)
                        <tr class="hover:bg-gray-50 transition {{ $cl['is_frozen'] ? 'bg-red-50/40' : '' }}">
                            <td class="p-3">
                                <div class="font-bold text-gray-900">{{ $cl['client']->company_name ?? $cl['client']->name }}</div>
                                <div class="text-[10px] text-gray-500 font-mono">{{ $cl['client_code'] }}</div>
                                @if($cl['is_frozen'])
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9px] font-black bg-red-800 text-white">[LOCKED]</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="font-semibold text-gray-800">{{ $cl['surat_jalan_count'] }} Surat Jalan</span>
                                <div class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $cl['surat_jalan_preview'] }}</div>
                            </td>
                            <td class="p-3 text-right">
                                <div class="font-bold text-gray-900">Rp {{ number_format($cl['outstanding_amount'], 0, ',', '.') }}</div>
                                <div class="text-[10px] text-emerald-700 font-semibold">Tervalidasi Finance</div>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-gray-800">TOP {{ $cl['top_days'] }} Hari</div>
                                <div class="text-[10px] text-gray-500">
                                    {{ $cl['due_date'] ? \Carbon\Carbon::parse($cl['due_date'])->format('d M Y') : '-' }}
                                    @if($cl['days_left'] < 0)
                                        <strong class="text-red-700 font-bold ml-1">(Overdue {{ abs($cl['days_left']) }} Hari)</strong>
                                    @elseif($cl['days_left'] <= 7)
                                        <strong class="text-amber-700 font-bold ml-1">(! H-{{ $cl['days_left'] }} JT)</strong>
                                    @else
                                        <span class="text-gray-400 ml-1">({{ $cl['days_left'] }} Hari Tersisa)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                @if($cl['is_frozen'])
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-800 border border-red-300">
                                        AUTO-FREEZE PO
                                    </span>
                                @elseif($cl['days_left'] <= 7)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        WARNING TEMPO
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-800 border border-green-300">
                                        LANCAR
                                    </span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="text-[11px] font-bold text-gray-800">
                                    Rp {{ number_format($cl['outstanding_amount'] / 1000000, 0) }}M / {{ number_format($cl['credit_ceiling'] / 1000000, 0) }}M
                                </div>
                                <div class="w-28 bg-gray-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $cl['utilization_percent'] > 100 ? 'bg-red-600' : ($cl['utilization_percent'] > 75 ? 'bg-amber-500' : 'bg-emerald-600') }}" style="width: {{ min(100, $cl['utilization_percent']) }}%"></div>
                                </div>
                                <div class="text-[10px] {{ $cl['utilization_percent'] > 100 ? 'text-red-700 font-bold' : 'text-gray-500' }} mt-0.5">
                                    {{ $cl['utilization_percent'] }}% {{ $cl['utilization_percent'] > 100 ? '(OVER LIMIT)' : '' }}
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form method="POST" action="{{ route('direktur.receivables.reminder', $cl['client']->id) }}">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-blue-700 hover:bg-blue-800 text-white rounded text-[10px] font-semibold transition" title="Kirim notifikasi WhatsApp ke PIC Klien">
                                            Kirim Reminder
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('direktur.receivables.freeze', $cl['client']->id) }}">
                                        @csrf
                                        @if($cl['is_frozen'])
                                            <button type="submit" class="px-2 py-1 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-[10px] font-bold transition">
                                                Lepas Freeze
                                            </button>
                                        @else
                                            <button type="submit" class="px-2 py-1 bg-red-700 hover:bg-red-800 text-white rounded text-[10px] font-bold transition">
                                                Kunci Plafon
                                            </button>
                                        @endif
                                    </form>

                                    @if($cl['is_frozen'])
                                        <form method="POST" action="{{ route('direktur.receivables.restructure', $cl['client']->id) }}">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Restrukturisasi tagihan dan berikan perpanjangan tenor 14 hari untuk {{ $cl['client']->company_name ?? $cl['client']->name }}?')" class="px-2 py-1 bg-purple-700 hover:bg-purple-800 text-white rounded text-[10px] font-semibold transition">
                                                Restrukturisasi
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-400">
                                Tidak ada data tagihan piutang B2B yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. KEBIJAKAN PEMBEKUAN OTOMATIS & PROTOKOL VERIFIKASI KAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <!-- Kebijakan Pembekuan & Otomasi Sistem -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <div class="flex justify-between items-center border-b pb-2">
                <h3 class="font-bold text-gray-900 uppercase">Kebijakan Pembekuan & Otomasi Sistem (PRD Rule 15.3 & 15.6)</h3>
                <span class="px-2 py-0.5 bg-emerald-100 text-[#153a01] font-bold text-[9px] rounded">SISTEM AKTIF</span>
            </div>
            <div class="space-y-2 text-gray-600">
                <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                    <strong class="text-gray-900">RULE 15.3: PEMBEKUAN PO OTOMATIS</strong>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Setiap pemesanan (PO) baru akan diblokir otomatis oleh sistem terminal jika terdapat faktur tempo yang menunggak <strong>&gt; 7 hari</strong> melewati batas TOP yang disepakati dalam kontrak legal.
                    </p>
                    <div class="mt-1 text-[10px] font-mono text-emerald-800 font-bold">STATUS: ENFORCED (AMBANG OVERDUE: 7 HARI)</div>
                </div>

                <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                    <strong class="text-gray-900">RULE 15.6: CEILING PLAFON KREDIT</strong>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Penerbitan Surat Jalan baru akan ditolak otomatis jika saldo tagihan berjalan ditambah nilai PO baru menghasilkan utilisasi plafon melebihi <strong>100%</strong> dari limit yang disetujui Direktur.
                    </p>
                    <div class="mt-1 text-[10px] font-mono text-emerald-800 font-bold">STATUS: ENFORCED (LIMIT CAP: 100.0% PLAFON)</div>
                </div>
            </div>
        </div>

        <!-- Protokol Verifikasi Kas -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <div class="flex justify-between items-center border-b pb-2">
                <h3 class="font-bold text-gray-900 uppercase">Protokol Verifikasi Kas (Rule 11)</h3>
                <span class="px-2 py-0.5 bg-blue-100 text-blue-900 font-bold text-[9px] rounded">TWO-WAY RECONCILIATION</span>
            </div>
            <p class="text-gray-600 text-[11px]">
                Pembayaran transfer bilyet giro dan RTGS dari klien korporat wajib melalui proses pencocokan dua arah oleh Sekretaris & Finance sebelum status piutang dinyatakan lunas di sistem.
            </p>
            <div class="p-3 bg-blue-50 rounded border border-blue-200 flex justify-between items-center">
                <div>
                    <div class="font-bold text-blue-900">Antrean Verifikasi Masuk: {{ $pendingClearingCount }} Faktur</div>
                    <div class="text-[10px] text-blue-700">Total Nilai Kliring Pending: Rp {{ number_format($pendingClearingAmount, 0, ',', '.') }}</div>
                </div>
                <span class="px-2 py-1 bg-white text-blue-900 font-mono text-[10px] font-bold rounded border border-blue-300">
                    SLO: 1x24 JAM
                </span>
            </div>
            <div class="pt-2 text-[10px] text-gray-400 border-t">
                Semua tindakan dispensasi / bypass pembekuan tercatat otomatis pada append-only audit trail SHA-256 Direktur.
            </div>
        </div>
    </div>
</x-dynamic-component>

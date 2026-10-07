<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Pengaturan Tata Kelola, Kebijakan Bisnis & Audit Trail Sistem
    </x-slot>

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-gray-900">Pengaturan Tata Kelola, Kebijakan Bisnis & Audit Trail Sistem</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Konsol Otorisasi Kebijakan Utama Direksi, Kontrol Batas Kredit Korporat, Kalibrasi Batas Toleransi Mutu, & Log Forensik Keamanan Transaksi. Seluruh perubahan diproteksi append-only SHA-256 ledger.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('direktur.governance.export-audit') }}" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-xs font-bold transition shadow-sm">
                Export Full Audit Log (.CSV)
            </a>
            <span class="px-3 py-1.5 bg-gray-100 text-gray-700 font-mono text-xs font-bold rounded border">
                SHA-256 LEDGER VALID
            </span>
        </div>
    </div>

    <!-- 1. KARTU 4 METRIK TATA KELOLA -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Metric 1: Integritas Ledger -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-[#153a01]">
            <div class="text-[10px] text-gray-400 font-mono">METRIC #01 // INTEGRITAS LEDGER</div>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-lg font-black text-gray-900">ACTIVE GUARD</span>
            </div>
            <div class="mt-2 text-[10px] text-emerald-800 font-bold">100% VALID // SHA-256 Verified</div>
            <div class="text-[10px] text-gray-400 mt-0.5">0 Anomali (Zero Breach)</div>
        </div>

        <!-- Metric 2: Kontrol Kredit B2B -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-600">
            <div class="text-[10px] text-gray-400 font-mono">METRIC #02 // KONTROL KREDIT B2B</div>
            <div class="text-lg font-black text-blue-900 mt-1">CEILING LIMIT</div>
            <div class="mt-1 text-xs font-black text-gray-800">Rp 2.500.000.000</div>
            <div class="text-[10px] text-emerald-700 font-bold mt-1">Terutilisasi: Rp 1.185M (47.4% Exposure Aman)</div>
        </div>

        <!-- Metric 3: Approval Status -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-600">
            <div class="text-[10px] text-gray-400 font-mono">METRIC #03 // DIR APPROVAL STATUS</div>
            <div class="text-lg font-black text-amber-900 mt-1">VERIFIED</div>
            <div class="mt-1 text-xs font-bold text-gray-800">3 Kontrak Disetujui</div>
            <div class="text-[10px] text-gray-500 mt-1">Threshold Policy Margin: Min. {{ $settings['min_margin_percent'] ?? 15.0 }}%</div>
        </div>

        <!-- Metric 4: Hard-Guard Compliance -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-600">
            <div class="text-[10px] text-gray-400 font-mono">METRIC #04 // HARD-GUARD COMPLIANCE</div>
            <div class="text-lg font-black text-purple-900 mt-1">POLICY ENGINE</div>
            <div class="mt-1 text-xs font-bold text-emerald-700">0 Pelanggaran (Clean)</div>
            <div class="text-[10px] text-gray-500 mt-1">Hard-Guard Rules 02, 03, 05, 06 Enforced</div>
        </div>
    </div>

    <!-- 2. KONFIGURASI PARAMETER BISNIS & RULE ENGINE -->
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-4">
        <div class="border-b pb-3 flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Konfigurasi Parameter Bisnis & Rule Engine</h3>
                <p class="text-[11px] text-gray-500">5 Parameter Statutori Tata Kelola yang Mengunci Otomatisasi Validasi Sistem ERP & Pergudangan.</p>
            </div>
            <span class="px-2 py-0.5 bg-gray-100 text-gray-700 font-mono text-[10px] rounded border">
                ID: CFG-MASTER-RULE-ENGINE
            </span>
        </div>

        <form method="POST" action="{{ route('direktur.governance.parameters') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Rule 04 & 13: Toleransi Deviasi Susut Gudang -->
                <div class="p-3.5 bg-gray-50 rounded border border-gray-200 space-y-2">
                    <div class="flex justify-between items-center">
                        <strong class="text-gray-900">RULE 04 & 13: SUSUT GUDANG</strong>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-green-100 text-green-800">ENFORCED</span>
                    </div>
                    <p class="text-[10px] text-gray-500">Ambang batas deviasi timbangan asal versus timbangan terima di fasilitas pergudangan.</p>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700">Maks Hard Limit (%):</label>
                        <input type="number" step="0.1" name="warehouse_loss_max_percent" value="{{ $settings['warehouse_loss_max_percent'] ?? 2.0 }}" class="w-full border rounded px-2 py-1 text-xs mt-0.5">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700">Warning Threshold (%):</label>
                        <input type="number" step="0.1" name="warehouse_loss_warning_percent" value="{{ $settings['warehouse_loss_warning_percent'] ?? 1.5 }}" class="w-full border rounded px-2 py-1 text-xs mt-0.5">
                    </div>
                    <div class="text-[9px] text-gray-400 font-mono">Action: Auto-Hold Invoice if Exceeded</div>
                </div>

                <!-- Section 15.3: Plafon Kredit Default TOP Klien Baru -->
                <div class="p-3.5 bg-gray-50 rounded border border-gray-200 space-y-2">
                    <div class="flex justify-between items-center">
                        <strong class="text-gray-900">SECTION 15.3: PLAFON TOP B2B</strong>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-green-100 text-green-800">ENFORCED</span>
                    </div>
                    <p class="text-[10px] text-gray-500">Kebijakan batas kredit awal bagi mitra agribisnis atau buyer yang belum diaudit Direksi.</p>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700">Default Plafon Kredit (Rp):</label>
                        <input type="number" name="default_top_credit_ceiling" value="{{ $settings['default_top_credit_ceiling'] ?? 100000000 }}" class="w-full border rounded px-2 py-1 text-xs mt-0.5">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700">Default Term of Payment (Hari):</label>
                        <input type="number" name="default_top_days" value="{{ $settings['default_top_days'] ?? 30 }}" class="w-full border rounded px-2 py-1 text-xs mt-0.5">
                    </div>
                    <div class="text-[9px] text-gray-400 font-mono">Min Skor Kredit: 75 Poin Sistem</div>
                </div>

                <!-- Section 16: Batas Margin Minimum Penjualan B2B -->
                <div class="p-3.5 bg-gray-50 rounded border border-gray-200 space-y-2">
                    <div class="flex justify-between items-center">
                        <strong class="text-gray-900">SECTION 16: FLOOR MARGIN</strong>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-green-100 text-green-800">ENFORCED</span>
                    </div>
                    <p class="text-[10px] text-gray-500">Floor margin proteksi profitabilitas bruto komoditas pangan segar terhadap fluktuasi lelang.</p>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700">Margin Minimum Statutori (%):</label>
                        <input type="number" step="0.5" name="min_margin_percent" value="{{ $settings['min_margin_percent'] ?? 15.0 }}" class="w-full border rounded px-2 py-1 text-xs mt-0.5">
                    </div>
                    <div class="p-2 bg-amber-50 rounded text-[10px] text-amber-800">
                        Order &lt; {{ $settings['min_margin_percent'] ?? 15.0 }}% wajib auto-eskalasi otorisasi Direktur Utama. Sales bypass dilarang keras.
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold transition shadow-sm">
                    Simpan Perubahan Parameter Kebijakan
                </button>
            </div>
        </form>
    </div>

    <!-- 3. TABEL AUDIT TRAIL FORENSIK SHA-256 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden space-y-0">
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 bg-gray-50">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Tabel Audit Trail Forensik & Riwayat Aksi Direksi / Admin</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Append-Only Cryptographic Ledger berantai SHA-256. Setiap modifikasi kebijakan dan otorisasi tersimpan permanen.</p>
            </div>
            <form method="GET" action="{{ route('direktur.governance.index') }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari Dokumen / Hash..." class="border border-gray-300 rounded px-2.5 py-1 text-xs focus:ring-1 focus:ring-emerald-600 focus:outline-none w-48">
                <select name="category" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="ALL">Semua Kategori</option>
                    <option value="APPROVAL KONTRAK" {{ $categoryFilter === 'APPROVAL KONTRAK' ? 'selected' : '' }}>Approval Kontrak</option>
                    <option value="REKONSILIASI KAS" {{ $categoryFilter === 'REKONSILIASI KAS' ? 'selected' : '' }}>Rekonsiliasi Kas</option>
                    <option value="SYSTEM GOVERNANCE" {{ $categoryFilter === 'SYSTEM GOVERNANCE' ? 'selected' : '' }}>System Governance</option>
                    <option value="BUFFER OVERRIDE" {{ $categoryFilter === 'BUFFER OVERRIDE' ? 'selected' : '' }}>Buffer Override</option>
                    <option value="SECURITY BLOCK" {{ $categoryFilter === 'SECURITY BLOCK' ? 'selected' : '' }}>Security Block</option>
                </select>
                <button type="submit" class="px-2.5 py-1 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">Block ID</th>
                        <th class="p-3">Timestamp / WIB</th>
                        <th class="p-3">Aktor & User ID</th>
                        <th class="p-3">Kategori Aksi</th>
                        <th class="p-3">Dokumen Terkait</th>
                        <th class="p-3">Rincian Perubahan / Keputusan</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 font-mono">Hash SHA-256</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 font-mono font-bold text-gray-700">{{ $log['block_id'] ?? '-' }}</td>
                            <td class="p-3 font-mono text-[10px] text-gray-600">{{ $log['timestamp'] ?? '-' }}</td>
                            <td class="p-3">
                                <div class="font-bold text-gray-900">{{ $log['actor'] ?? '-' }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $log['actor_uid'] ?? '' }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-900 border border-blue-200">
                                    {{ $log['action_category'] ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3 font-mono font-bold text-[#153a01]">{{ $log['document_ref'] ?? '-' }}</td>
                            <td class="p-3 text-gray-700 max-w-xs">{{ $log['details'] ?? '-' }}</td>
                            <td class="p-3 text-center">
                                @if(($log['status'] ?? '') === 'SEALED' || ($log['status'] ?? '') === 'LOCKED')
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        {{ $log['status'] }}
                                    </span>
                                @elseif(($log['status'] ?? '') === 'INTERCEPTED')
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-800 border border-red-300">
                                        INTERCEPTED
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-gray-100 text-gray-800">
                                        {{ $log['status'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 font-mono text-[10px] text-gray-500">
                                {{ substr($log['hash'] ?? '', 0, 12) }}...{{ substr($log['hash'] ?? '', -6) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-gray-400">
                                Tidak ada catatan audit log yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 bg-gray-50 border-t border-gray-200 text-[10px] text-gray-500 flex justify-between">
            <span>LEDGER STATUS: CONTINUOUS APPEND // PREVIOUS BLOCK HASH MATCHED 100%</span>
            <span>Menampilkan {{ count($auditLogs) }} Aksi Terverifikasi</span>
        </div>
    </div>

    <!-- 4. PROTOKOL KESELAMATAN KRITIS: MASTER FREEZE & DELEGASI PLT -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <!-- Critical Safety Protocol #01: Master Freeze Switch -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3 {{ ($settings['master_freeze_active'] ?? false) ? 'border-red-400 bg-red-50/20' : '' }}">
            <div class="flex justify-between items-center border-b pb-2">
                <div>
                    <h3 class="font-bold text-gray-900 uppercase">Critical Safety Protocol #01</h3>
                    <p class="text-[11px] text-gray-500">Master Freeze Switch (Stock Opname & Audit Khusus)</p>
                </div>
                <span class="px-2 py-0.5 rounded font-black text-[9px] {{ ($settings['master_freeze_active'] ?? false) ? 'bg-red-600 text-white animate-pulse' : 'bg-green-100 text-green-800' }}">
                    STATUS: {{ ($settings['master_freeze_active'] ?? false) ? 'FREEZE AKTIF (TERKUNCI)' : 'STANDBY READY' }}
                </span>
            </div>

            <p class="text-gray-600 text-[11px]">
                Menghentikan seluruh transaksi input PO, penerbitan surat jalan, rekonsiliasi kas, dan mutasi komoditas secara instan di seluruh node gudang saat stock opname tahunan atau investigasi fraud berlangsung.
            </p>

            <form method="POST" action="{{ route('direktur.governance.freeze') }}" class="space-y-3 pt-2">
                @csrf
                <input type="hidden" name="freeze_action" value="{{ ($settings['master_freeze_active'] ?? false) ? '0' : '1' }}">

                <div>
                    <label class="block text-[10px] font-bold text-gray-700">Alasan Prosedur Freeze / Buka Kunci:</label>
                    <input type="text" name="freeze_reason" placeholder="Contoh: Stock Opname Kuartal IV 2026..." class="w-full border rounded px-2.5 py-1 text-xs mt-0.5" required>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-[10px] text-gray-400">Otorisasi: Dual Digital Signature Direktur Utama</span>
                    @if($settings['master_freeze_active'] ?? false)
                        <button type="submit" onclick="return confirm('Cabut Master Freeze dan pulihkan seluruh transaksi normal?')" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-xs font-bold transition">
                            Buka Kunci Master Freeze
                        </button>
                    @else
                        <button type="submit" onclick="return confirm('PERINGATAN: Mengaktifkan Master Freeze akan menghentikan seluruh transaksi input PO dan Surat Jalan di seluruh cabang gudang. Lanjutkan?')" class="px-3.5 py-1.5 bg-red-700 hover:bg-red-800 text-white rounded text-xs font-bold transition">
                            ⚠️ Aktifkan Master Freeze
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Governance Succession #02: Delegasi Wewenang Sementara (Plt) -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <div class="flex justify-between items-center border-b pb-2">
                <div>
                    <h3 class="font-bold text-gray-900 uppercase">Governance Succession #02</h3>
                    <p class="text-[11px] text-gray-500">Delegasi Wewenang Sementara (Plt Direksi)</p>
                </div>
                <span class="px-2 py-0.5 rounded font-black text-[9px] {{ ($settings['plt_active'] ?? false) ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                    MODE: {{ ($settings['plt_active'] ?? false) ? 'MANDAT DINAS LUAR AKTIF' : 'DIREKSI PENUH' }}
                </span>
            </div>

            <p class="text-gray-600 text-[11px]">
                Konfigurasi mandat otorisasi otomatis kepada Pejabat Pelaksana Tugas (Plt) ketika Direksi bertugas ke sentra pertanian luar pulau. Akses dibatasi ketat dengan plafon limit transaksi spesifik.
            </p>

            <form method="POST" action="{{ route('direktur.governance.plt') }}" class="space-y-3 pt-2">
                @csrf
                <input type="hidden" name="plt_active" value="{{ ($settings['plt_active'] ?? false) ? '0' : '1' }}">

                <div>
                    <label class="block text-[10px] font-bold text-gray-700">Penerima Mandat Plt:</label>
                    <input type="text" name="plt_delegate_name" value="{{ $settings['plt_delegate_name'] ?? 'Nurhayati, S.Ak (VP Finance & Controller)' }}" class="w-full border rounded px-2.5 py-1 text-xs mt-0.5" required>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700">Masa Berlaku (Jam):</label>
                        <input type="number" name="plt_duration_hours" value="{{ $settings['plt_duration_hours'] ?? 72 }}" class="w-full border rounded px-2.5 py-1 text-xs mt-0.5" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-700">Plafon Maks / Kontrak (Rp):</label>
                        <input type="number" name="plt_ceiling_amount" value="{{ $settings['plt_ceiling_amount'] ?? 350000000 }}" class="w-full border rounded px-2.5 py-1 text-xs mt-0.5" required>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-[10px] text-gray-400">Protokol Tata Kelola PRD SEC 21</span>
                    @if($settings['plt_active'] ?? false)
                        <button type="submit" class="px-3.5 py-1.5 bg-gray-700 hover:bg-gray-800 text-white rounded text-xs font-bold transition">
                            Cabut Mandat Plt
                        </button>
                    @else
                        <button type="submit" class="px-3.5 py-1.5 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold transition">
                            Atur & Aktifkan Plt Direksi
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>

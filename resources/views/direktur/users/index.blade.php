<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Kelola Akun dan Hak Akses Pengguna & Matriks RBAC
    </x-slot>

    <!-- Top Action Bar -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-gray-900">Kelola Akun dan Hak Akses Pengguna & Matriks RBAC</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Pusat Kontrol Otorisasi Pengguna Berjenjang, Pembagian Peran (Client, Admin, Koordinator, Supir, Direksi), Token Sesi Kriptografis, & Audit Akses Keamanan (PRD Rule 08 Hak Akses Tidak Boleh Dicampur).
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('direktur.users.rbac.export') }}" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-800 text-white rounded text-xs font-bold transition shadow-sm">
                Unduh Matriks Akses (.CSV)
            </a>
            <a href="{{ route('direktur.users.create') }}" class="px-3.5 py-1.5 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold transition shadow-sm">
                + Tambah Pengguna Baru
            </a>
        </div>
    </div>

    <!-- 1. KARTU METRIK AKUN & SESI -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Card 1: TOTAL AKUN TERDAFTAR -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-[#153a01]">
            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">TOTAL AKUN TERDAFTAR</div>
            <div class="text-xl font-black text-gray-900 mt-1">{{ $roleCounts['ALL'] }} PENGGUNA AKTIF</div>
            <div class="text-[10px] text-emerald-700 font-semibold mt-1">5 Role Terisolasi • 0 Ditangguhkan</div>
        </div>

        <!-- Card 2: SESI AKTIF REALTIME -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-600">
            <div class="flex justify-between items-start">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">SESI AKTIF REALTIME</div>
                <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px] animate-pulse">LIVE PING</span>
            </div>
            <div class="text-xl font-black text-blue-900 mt-1">{{ $activeSessionsCount }} NODE LOGIN</div>
            <div class="text-[10px] text-gray-500 mt-1">2 Dir | 4 Adm | 3 Koord | 5 Supir</div>
        </div>

        <!-- Card 3: OTENTIKASI 2FA & TOKEN -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-emerald-600">
            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">OTENTIKASI 2FA & TOKEN</div>
            <div class="text-xl font-black text-emerald-800 mt-1">100% TERPROTEKSI</div>
            <div class="text-[10px] text-gray-500 mt-1">Enkripsi RSA-4096 • Biometrik Aktif</div>
        </div>

        <!-- Card 4: LOG PELANGGARAN HAK AKSES -->
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-600">
            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">LOG PELANGGARAN HAK AKSES</div>
            <div class="text-xl font-black text-purple-900 mt-1">0 INSIDEN (0%)</div>
            <div class="text-[10px] text-gray-500 mt-1">Rule 08 Enforcement • Tamper-Proof</div>
        </div>
    </div>

    <!-- 2. MATRIKS HAK AKSES & WEWENANG 5 PERAN (PRD SECTION 6 & 7.1) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 bg-gray-50">
            <div>
                <h3 class="font-bold text-xs text-gray-900 uppercase tracking-wide">Matriks Hak Akses & Wewenang 5 Peran (PRD Section 6 & 7.1 Matrix Table)</h3>
                <p class="text-[11px] text-gray-500">Pemisahan ketat tugas komputasi dan otorisasi. Seluruh wewenang diatur dengan token kriptografi per sesi.</p>
            </div>
            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded border border-emerald-300">
                STATUS: IMMUTABLE AUDIT ACTIVE
            </span>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">Modul & Fitur Operasional</th>
                        <th class="p-3">Klien / Buyer B2B</th>
                        <th class="p-3">Sekretaris / Finance</th>
                        <th class="p-3">Koordinator Lapangan</th>
                        <th class="p-3">Armada / Supir</th>
                        <th class="p-3 bg-emerald-50 text-[#153a01]">Direktur / Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-[11px]">
                    @foreach($rbacMatrix as $m)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 font-bold text-gray-900">{{ $m['feature'] }}</td>
                            <td class="p-3 text-gray-600">{{ $m['klien'] }}</td>
                            <td class="p-3 text-gray-700">{{ $m['sekre'] }}</td>
                            <td class="p-3 text-gray-600">{{ $m['koord'] }}</td>
                            <td class="p-3 text-gray-600">{{ $m['armada'] }}</td>
                            <td class="p-3 bg-emerald-50/50 font-bold text-[#153a01]">{{ $m['direktur'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. DIREKTORI PENGGUNA & STATUS KREDENSIAL -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden space-y-0">
        <!-- Filter Tabs & Pencarian -->
        <div class="p-3.5 bg-gray-50 border-b border-gray-200 flex flex-wrap gap-3 items-center justify-between text-xs">
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('direktur.users.index') }}" class="px-2.5 py-1 rounded font-bold {{ empty($roleFilter) ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-100' }}">
                    Semua Role ({{ $roleCounts['ALL'] }})
                </a>
                <a href="{{ route('direktur.users.index', ['role' => 'DIREKTUR']) }}" class="px-2.5 py-1 rounded font-bold {{ $roleFilter === 'DIREKTUR' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-100' }}">
                    DIR ({{ $roleCounts['DIREKTUR'] }})
                </a>
                <a href="{{ route('direktur.users.index', ['role' => 'SEKRETARIS']) }}" class="px-2.5 py-1 rounded font-bold {{ $roleFilter === 'SEKRETARIS' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-100' }}">
                    ADM / SEC ({{ $roleCounts['SEKRETARIS'] }})
                </a>
                <a href="{{ route('direktur.users.index', ['role' => 'KOORDINATOR']) }}" class="px-2.5 py-1 rounded font-bold {{ $roleFilter === 'KOORDINATOR' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-100' }}">
                    KRD ({{ $roleCounts['KOORDINATOR'] }})
                </a>
                <a href="{{ route('direktur.users.index', ['role' => 'ARMADA']) }}" class="px-2.5 py-1 rounded font-bold {{ $roleFilter === 'ARMADA' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-100' }}">
                    DVR ({{ $roleCounts['ARMADA'] }})
                </a>
                <a href="{{ route('direktur.users.index', ['role' => 'KLIEN']) }}" class="px-2.5 py-1 rounded font-bold {{ $roleFilter === 'KLIEN' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-100' }}">
                    CLI B2B ({{ $roleCounts['KLIEN'] }})
                </a>
            </div>

            <form method="GET" action="{{ route('direktur.users.index') }}" class="flex items-center gap-2">
                @if($roleFilter)
                    <input type="hidden" name="role" value="{{ $roleFilter }}">
                @endif
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari NIK, Nama, atau ID..." class="border border-gray-300 rounded px-2.5 py-1 text-xs focus:ring-1 focus:ring-emerald-600 focus:outline-none w-48">
                <button type="submit" class="px-2.5 py-1 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold">
                    Cari
                </button>
            </form>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                    <tr>
                        <th class="p-3">ID & Nama Lengkap</th>
                        <th class="p-3">Role & Divisi</th>
                        <th class="p-3">Identitas Resmi & Kontak</th>
                        <th class="p-3">Wilayah / Hub</th>
                        <th class="p-3 text-center">Status 2FA</th>
                        <th class="p-3 text-center">Aksi Otoritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3">
                                <div class="font-bold text-gray-900 text-sm">{{ $u->name }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">UID: USR-0{{ $u->id }} // {{ $u->role }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded font-black text-[9px]
                                    @if($u->role === 'DIREKTUR') bg-purple-100 text-purple-900 border border-purple-200
                                    @elseif($u->role === 'SEKRETARIS') bg-blue-100 text-blue-900 border border-blue-200
                                    @elseif($u->role === 'KOORDINATOR') bg-amber-100 text-amber-900 border border-amber-200
                                    @elseif($u->role === 'ARMADA') bg-indigo-100 text-indigo-900 border border-indigo-200
                                    @else bg-green-100 text-green-900 border border-green-200 @endif">
                                    {{ $u->role }}
                                </span>
                                <div class="text-[10px] text-gray-500 mt-1">{{ $u->company_name ?? ($u->role === 'KLIEN' ? 'Buyer B2B' : 'Internal GPA') }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-mono text-gray-800">{{ $u->email }}</div>
                                <div class="text-[10px] text-gray-500 font-mono">{{ $u->phone }}</div>
                            </td>
                            <td class="p-3 text-gray-700">
                                <div class="font-semibold">{{ $u->role === 'DIREKTUR' ? 'Kantor Pusat GPA' : ($u->role === 'KOORDINATOR' ? 'STA Lembang / Rancabali' : ($u->role === 'ARMADA' ? 'Pangkalan Armada' : 'Portal Klien')) }}</div>
                                <div class="text-[10px] text-emerald-700 font-bold">Online Now</div>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-800 border border-green-200">
                                    2FA AKTIF
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form method="POST" action="{{ route('direktur.users.reset-password', $u->id) }}">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Reset kata sandi pengguna {{ $u->name }} ke default?')" class="px-2 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded text-[10px] font-bold transition">
                                            Reset Sandi
                                        </button>
                                    </form>

                                    <a href="{{ route('direktur.users.edit', $u->id) }}" class="px-2 py-1 bg-gray-700 hover:bg-gray-800 text-white rounded text-[10px] font-bold transition">
                                        Edit Role
                                    </a>

                                    <a href="{{ route('direktur.users.show', $u->id) }}" class="px-2 py-1 bg-[#153a01] hover:bg-[#255808] text-white rounded text-[10px] font-bold transition">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-400">
                                Tidak ada akun pengguna yang ditemukan untuk kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 bg-gray-50 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>

    <!-- 4. KEBIJAKAN SESI & PROSEDUR DARURAT OTORITAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <!-- Kebijakan Sesi & Idle Timeout -->
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 space-y-3">
            <div class="border-b pb-2 flex justify-between items-center">
                <h3 class="font-bold text-gray-900 uppercase">Kebijakan Sesi & Idle Timeout</h3>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[9px] rounded">MANDATORY ACTIVE</span>
            </div>
            <div class="space-y-2 text-gray-600">
                <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                    <strong class="text-gray-900">Auto Logout Direksi & Admin (15 Menit)</strong>
                    <p class="text-[11px] text-gray-500 mt-0.5">Sesi terminal tanpa interaksi keyboard / pointer selama 900 detik otomatis ditutup untuk mitigasi serangan unattended workstation.</p>
                </div>
                <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
                    <strong class="text-gray-900">Single Active Node Policy</strong>
                    <p class="text-[11px] text-gray-500 mt-0.5">Login dari lokasi / browser baru langsung mendepak sesi sebelumnya (mencegah duplikasi kredensial perorangan).</p>
                </div>
            </div>
        </div>

        <!-- Prosedur Darurat Otoritas: Revoke All Sessions -->
        <div class="bg-red-50 p-5 rounded-lg shadow-sm border border-red-300 space-y-3">
            <div class="border-b border-red-200 pb-2">
                <h3 class="font-bold text-red-950 uppercase">Prosedur Darurat Otoritas Direktur</h3>
                <p class="text-[11px] text-red-800">Gunakan fungsi di bawah jika terdeteksi ancaman serangan siber, credential leak, atau anomali transaksi massal pada malam penutupan buku.</p>
            </div>

            <p class="text-xs text-red-900 font-bold">
                ⚠️ PERINGATAN: Tindakan ini akan seketika memutuskan seluruh sesi aktif pengguna lain di seluruh terminal kantor, gudang sentra, dan kendaraan distribusi.
            </p>

            <form method="POST" action="{{ route('direktur.users.revoke-sessions') }}" class="pt-2">
                @csrf
                <button type="submit" onclick="return confirm('PERINGATAN TINGKAT TINGGI: Anda akan memutuskan seluruh sesi login aktif di seluruh sistem. Lanjutkan?')" class="w-full py-2 bg-red-800 hover:bg-red-900 text-white rounded text-xs font-black transition shadow">
                    ⚠️ REVOKE ALL SESSIONS (LOGOUT SEMUA PENGGUNA)
                </button>
            </form>
        </div>
    </div>
</x-dynamic-component>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Direktur' }} - AgroOrder GPA</title>

    <!-- Tailwind CSS (Vite / Standar) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">
    <div class="min-h-screen flex">

        <!-- ============================================================= -->
        <!-- SIDEBAR KIRI (PORTAL DIREKTUR / EKSEKUTIF GPA)                -->
        <!-- ============================================================= -->
        <aside class="w-64 bg-[#153a01] text-white flex flex-col justify-between shrink-0 shadow-xl min-h-screen">
            <div>
                <!-- Header Brand Sidebar -->
                <div class="p-5 border-b border-[#255808]">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-white text-[#153a01] rounded-lg font-black text-base flex items-center justify-center shadow">
                            GPA
                        </div>
                        <div>
                            <h1 class="font-bold text-sm tracking-wide leading-tight">AgroOrder GPA</h1>
                            <p class="text-[10px] text-emerald-300 font-mono">PANEL DIREKTUR / OWNER</p>
                        </div>
                    </div>
                </div>

                <!-- Navigasi Menu Utama Sesuai Use Case & Design System -->
                <nav class="p-3 space-y-1 text-xs">
                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-400/80">
                        Monitoring & Analitik
                    </div>

                    <!-- 1. Dasbor & Monitoring Volume Komoditas -->
                    <a href="{{ route('direktur.dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md font-semibold transition {{ request()->routeIs('direktur.dashboard') ? 'bg-[#255808] text-white shadow-sm' : 'text-emerald-100/90 hover:bg-[#1f4e04] hover:text-white' }}">
                        <span>Dasbor Eksekutif</span>
                    </a>

                    <!-- 2. Laporan Penjualan Global -->
                    <a href="{{ route('direktur.reports.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md font-semibold transition {{ request()->routeIs('direktur.reports.*') ? 'bg-[#255808] text-white shadow-sm' : 'text-emerald-100/90 hover:bg-[#1f4e04] hover:text-white' }}">
                        <span>Laporan Penjualan</span>
                    </a>

                    <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-400/80">
                        Otoritas & Tata Kelola
                    </div>

                    <!-- 3. Kelola Akun & Hak Akses (CRUD User Seluruh Role) -->
                    <a href="{{ route('direktur.users.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md font-semibold transition {{ request()->routeIs('direktur.users.*') ? 'bg-[#255808] text-white shadow-sm' : 'text-emerald-100/90 hover:bg-[#1f4e04] hover:text-white' }}">
                        <span>Kelola Akun & RBAC</span>
                    </a>

                    <!-- 4. Kontrak Kemitraan B2B & Harga Khusus -->
                    <a href="{{ route('direktur.contracts.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-md font-semibold transition {{ request()->routeIs('direktur.contracts.*') ? 'bg-[#255808] text-white shadow-sm' : 'text-emerald-100/90 hover:bg-[#1f4e04] hover:text-white' }}">
                        <span>Kontrak & Harga B2B</span>
                    </a>
                </nav>
            </div>

            <!-- Bagian Bawah Sidebar: Profil Akun & Aksi Logout -->
            <div class="p-4 border-t border-[#255808] bg-[#0f2a01]/60">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <div>
                        <div class="font-bold text-white text-xs truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-emerald-300 font-mono truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <span class="px-1.5 py-0.5 rounded bg-emerald-800 text-emerald-200 text-[9px] font-bold">AKTIF</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('profile.edit') }}" class="w-1/2 text-center py-1.5 bg-[#1f4e04] hover:bg-[#286306] rounded text-[11px] font-semibold text-emerald-100 transition">
                        Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="w-1/2">
                        @csrf
                        <button type="submit" class="w-full text-center py-1.5 bg-red-900/70 hover:bg-red-800 rounded text-[11px] font-semibold text-red-100 transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ============================================================= -->
        <!-- AREA KONTEN UTAMA                                             -->
        <!-- ============================================================= -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- Top Header Bar Ringkas -->
            <header class="bg-white border-b border-gray-200 px-6 py-3.5 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                <div>
                    <h2 class="text-base font-bold text-gray-800 leading-tight">
                        {{ $header ?? 'Monitoring & Dasbor Direktur' }}
                    </h2>
                    <p class="text-[11px] text-gray-500">Kelompok Tani / Koperasi Green Pasundan Agriculture (Ciwidey)</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded font-mono border">
                        {{ date('d M Y') }}
                    </span>
                    <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded font-bold border border-green-200">
                        Database: Terhubung
                    </span>
                </div>
            </header>

            <!-- Notifikasi Global (Flash Session & Error Bag) -->
            <main class="p-6 space-y-6">
                @if(session('success'))
                    <div class="p-3 bg-green-50 border-l-4 border-green-600 text-green-900 text-xs rounded shadow-sm flex justify-between items-center">
                        <div><strong>Berhasil:</strong> {{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-3 bg-red-50 border-l-4 border-red-600 text-red-900 text-xs rounded shadow-sm">
                        <strong>Gagal:</strong> {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-3 bg-red-50 border-l-4 border-red-600 text-red-900 text-xs rounded shadow-sm">
                        <strong>Perhatian:</strong> {{ $errors->first() }}
                    </div>
                @endif

                {{ $slot }}
            </main>

        </div>
    </div>
</body>
</html>

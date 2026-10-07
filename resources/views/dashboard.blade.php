<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dasbor Operasional — {{ Auth::user()->role }}
            </h2>
            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">
                AKTOR AKTIF: {{ Auth::user()->role }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Kartu Profil Aktor -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-700">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Sistem Rantai Pasok Agribisnis — Koperasi Green Pasundan Agriculture (GPA) Ciwidey.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs bg-gray-50 p-4 rounded-md">
                    <div><span class="text-gray-500 block">Email Akun:</span> <strong>{{ Auth::user()->email }}</strong></div>
                    <div><span class="text-gray-500 block">Nomor Kontak:</span> <strong>{{ Auth::user()->phone }}</strong></div>
                    <div><span class="text-gray-500 block">Peran Sistem:</span> <strong class="text-green-700">{{ Auth::user()->role }}</strong></div>
                    @if(Auth::user()->company_name)
                        <div class="md:col-span-3 mt-2 pt-2 border-t border-gray-200">
                            <span class="text-gray-500 block">Perusahaan / Mitra:</span>
                            <strong>{{ Auth::user()->company_name }}</strong> (Tipe: {{ Auth::user()->client_type }})
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pintasan Navigasi Cepat Sesuai Peran -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h4 class="font-bold text-gray-800 mb-3 text-sm">Menu Cepat Berdasarkan Wewenang:</h4>
                <div class="flex flex-wrap gap-3">
                    @if(Auth::user()->role === 'KOORDINATOR')
                        <a href="{{ route('koordinator.stok.index') }}" class="px-4 py-2 bg-green-800 hover:bg-green-700 text-white rounded text-sm font-semibold transition">
                            📦 Kelola Stok Panen & Buffer (S2-02)
                        </a>
                    @elseif(Auth::user()->role === 'SEKRETARIS')
                        <span class="px-3 py-2 bg-gray-100 text-gray-700 rounded text-xs">
                            Antrean Verifikasi Pesanan (Menunggu S2-03)
                        </span>
                    @elseif(Auth::user()->role === 'KLIEN')
                        <span class="px-3 py-2 bg-gray-100 text-gray-700 rounded text-xs">
                            Katalog Belanja Mandiri (Menunggu Integrasi Rangga)
                        </span>
                    @elseif(Auth::user()->role === 'ARMADA')
                        <span class="px-3 py-2 bg-gray-100 text-gray-700 rounded text-xs">
                            Daftar Tugas Pengantaran Subuh (Menunggu S2-05)
                        </span>
                    @elseif(Auth::user()->role === 'DIREKTUR')
                        <a href="{{ route('direktur.dashboard') }}" class="px-4 py-2 bg-[#153a01] hover:bg-[#0f2801] text-white rounded text-sm font-semibold transition">
                            Buka Dasbor Eksekutif
                        </a>
                        <a href="{{ route('direktur.users.index') }}" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded text-sm font-semibold transition">
                            Kelola Akun Pengguna
                        </a>
                        <a href="{{ route('direktur.contracts.index') }}" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded text-sm font-semibold transition">
                            Kelola Kontrak B2B
                        </a>
                        <a href="{{ route('direktur.reports.index') }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-sm font-semibold transition">
                            Laporan Penjualan &amp; Ekspor
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

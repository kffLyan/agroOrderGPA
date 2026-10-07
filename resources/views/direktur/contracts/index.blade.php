<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Daftar Kontrak Kemitraan B2B & Harga Khusus
    </x-slot>

    <div class="space-y-4">
        <!-- Bar Aksi Atas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div>
                <h3 class="font-bold text-sm text-gray-800">Otorisasi & Manajemen Kontrak Kemitraan B2B</h3>
                <p class="text-xs text-gray-500">Penetapan harga tetap, komitmen pasokan, dan jangka waktu pembayaran (TOP).</p>
            </div>
            <a href="{{ route('direktur.contracts.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-[#153a01] hover:bg-[#0f2801] text-white text-xs font-bold rounded-lg shadow-sm transition">
                + Terbitkan Kontrak Baru
            </a>
        </div>
        <!-- Notifikasi -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 text-xs rounded-r-lg shadow-sm">
                <strong>Berhasil:</strong> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-900 text-xs rounded-r-lg shadow-sm">
                <strong>Terjadi Kesalahan:</strong> {{ $errors->first() }}
            </div>
        @endif

        <!-- Filter Tab Status & Search Bar -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <!-- Tab Status Navigasi -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <a href="{{ route('direktur.contracts.index', ['status' => 'ALL', 'q' => $search]) }}"
                       class="px-3 py-1.5 rounded-lg font-bold transition {{ empty($statusFilter) || $statusFilter === 'ALL' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua Kontrak ({{ $statusCounts['ALL'] }})
                    </a>

                    <a href="{{ route('direktur.contracts.index', ['status' => 'PENDING_APPROVAL', 'q' => $search]) }}"
                       class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ $statusFilter === 'PENDING_APPROVAL' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' }}">
                        @if($statusCounts['PENDING_APPROVAL'] > 0)
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        @endif
                        Menunggu Persetujuan ({{ $statusCounts['PENDING_APPROVAL'] }})
                    </a>

                    <a href="{{ route('direktur.contracts.index', ['status' => 'ACTIVE', 'q' => $search]) }}"
                       class="px-3 py-1.5 rounded-lg font-bold transition {{ $statusFilter === 'ACTIVE' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Aktif Berjalan ({{ $statusCounts['ACTIVE'] }})
                    </a>

                    <a href="{{ route('direktur.contracts.index', ['status' => 'INACTIVE', 'q' => $search]) }}"
                       class="px-3 py-1.5 rounded-lg font-bold transition {{ $statusFilter === 'INACTIVE' ? 'bg-gray-700 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Selesai / Dibatalkan ({{ $statusCounts['TERMINATED'] }})
                    </a>
                </div>

                <!-- Input Pencarian -->
                <form method="GET" action="{{ route('direktur.contracts.index') }}" class="flex items-center gap-2">
                    @if(!empty($statusFilter))
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                    @endif
                    <div class="relative w-full sm:w-64">
                        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nomor, mitra, sayuran..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold rounded-lg transition">
                        Cari
                    </button>
                    @if($search || ($statusFilter && $statusFilter !== 'ALL'))
                        <a href="{{ route('direktur.contracts.index') }}" class="text-xs text-gray-500 hover:underline">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tabel Kontrak -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase font-bold text-[11px] border-b border-gray-100">
                            <th class="p-3">No. Kontrak PKS</th>
                            <th class="p-3">Klien Mitra B2B</th>
                            <th class="p-3">Komoditas Sayuran</th>
                            <th class="p-3">Harga Khusus Tetap</th>
                            <th class="p-3">TOP</th>
                            <th class="p-3">Komitmen Vol</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Pengesahan Direktur</th>
                            <th class="p-3 text-center">Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="p-3 font-mono font-bold text-gray-900">
                                    <a href="{{ route('direktur.contracts.show', $contract->id) }}" class="text-blue-700 hover:underline">
                                        {{ $contract->contract_number }}
                                    </a>
                                    <span class="block text-[10px] text-gray-400 font-normal">
                                        {{ $contract->created_at->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-gray-800">
                                        {{ $contract->user->company_name ?? $contract->user->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        PIC: {{ $contract->user->pic_name ?? $contract->user->name }} ({{ $contract->user->phone }})
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-gray-900">
                                        {{ $contract->product->name }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        Harga Katalog: Rp {{ number_format($contract->product->base_price, 0) }}/Kg
                                    </div>
                                </td>
                                <td class="p-3 font-bold text-emerald-700 text-sm">
                                    Rp {{ number_format($contract->fixed_price_per_kg, 0, ',', '.') }}<span class="text-[10px] text-gray-500 font-normal">/Kg</span>
                                </td>
                                <td class="p-3 font-semibold text-gray-700">
                                    {{ $contract->top_days }} Hari
                                </td>
                                <td class="p-3 font-semibold text-gray-700">
                                    {{ number_format($contract->committed_volume_per_cycle, 1, ',', '.') }} Kg
                                </td>
                                <td class="p-3">
                                    @if($contract->status === 'ACTIVE')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full"></span>
                                            AKTIF
                                        </span>
                                    @elseif($contract->status === 'PENDING_APPROVAL')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 mr-1 bg-amber-500 rounded-full animate-ping"></span>
                                            MENUNGGU PERSETUJUAN
                                        </span>
                                    @elseif($contract->status === 'EXPIRED')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                            KEDALUWARSA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                            DIBATALKAN
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-[11px] text-gray-600">
                                    @if($contract->status === 'ACTIVE' && $contract->approved_at)
                                        <span class="font-medium text-emerald-800">Disahkan: {{ $contract->approver->name ?? 'Direktur' }}</span>
                                        <span class="block text-[10px] text-gray-400">{{ $contract->approved_at->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span class="text-amber-700 font-semibold italic">Belum disahkan</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center space-x-1 whitespace-nowrap">
                                    <!-- Tombol Setujui Langsung -->
                                    @if($contract->status === 'PENDING_APPROVAL')
                                        <form action="{{ route('direktur.contracts.approve', $contract->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-bold shadow-sm transition" title="Sahkan Kontrak">
                                                Setujui
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Tombol Detail -->
                                    <a href="{{ route('direktur.contracts.show', $contract->id) }}" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded font-semibold text-[10px] transition inline-block">
                                        Detail
                                    </a>

                                    <!-- Tombol Edit -->
                                    <a href="{{ route('direktur.contracts.edit', $contract->id) }}" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded font-semibold text-[10px] transition inline-block">
                                        Edit
                                    </a>

                                    <!-- Tombol Hentikan (Jika Aktif) -->
                                    @if($contract->status === 'ACTIVE')
                                        <form action="{{ route('direktur.contracts.terminate', $contract->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan kontrak {{ $contract->contract_number }}?')">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded font-semibold text-[10px] transition" title="Hentikan Kontrak">
                                                Hentikan
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('direktur.contracts.destroy', $contract->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen kontrak {{ $contract->contract_number }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded font-semibold text-[10px] transition" title="Hapus Permanen">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-gray-400">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <p class="font-bold text-gray-700">Tidak ada kontrak kemitraan yang ditemukan.</p>
                                        <p class="text-[11px] text-gray-500">Gunakan tombol di atas untuk menerbitkan kontrak penetapan harga khusus baru bagi klien mitra.</p>
                                        <div class="pt-2">
                                            <a href="{{ route('direktur.contracts.create') }}" class="px-3 py-1.5 bg-[#153a01] hover:bg-[#0f2801] text-white text-xs font-bold rounded shadow transition">
                                                + Buat Kontrak Baru
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>

<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Lembar Detail Kontrak PKS: {{ $contract->contract_number }}
    </x-slot>

    <div class="space-y-6">
        <!-- Bar Aksi Kontrak -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div>
                <a href="{{ route('direktur.contracts.index') }}" class="text-xs text-[#153a01] hover:underline font-semibold flex items-center gap-1">
                    &larr; Kembali ke Daftar Kontrak
                </a>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($contract->status === 'PENDING_APPROVAL')
                    <form action="{{ route('direktur.contracts.approve', $contract->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                            Sahkan Kontrak Ini
                        </button>
                    </form>
                @elseif($contract->status === 'ACTIVE')
                    <form action="{{ route('direktur.contracts.terminate', $contract->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan kontrak ini?')">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition">
                            Hentikan Kontrak
                        </button>
                    </form>
                @endif

                <a href="{{ route('direktur.contracts.edit', $contract->id) }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition">
                    Edit Kontrak
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 text-xs rounded shadow-sm">
                <strong>Berhasil:</strong> {{ session('success') }}
            </div>
        @endif

        <!-- Card Dokumen Resmi PKS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header Kop Surat Mini GPA -->
            <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="text-[11px] font-bold tracking-wider uppercase text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        Perjanjian Kerja Sama Pasokan B2B
                    </span>
                    <h3 class="text-lg font-bold text-gray-900 mt-1">
                        {{ $contract->contract_number }}
                    </h3>
                    <p class="text-xs text-gray-500">
                        Koperasi Green Pasundan Agriculture (GPA) Ciwidey & {{ $contract->user->company_name ?? $contract->user->name }}
                    </p>
                </div>
                <div>
                    @if($contract->status === 'ACTIVE')
                        <div class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            KONTRAK AKTIF BERLAKU
                        </div>
                    @elseif($contract->status === 'PENDING_APPROVAL')
                        <div class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1.5 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            MENUNGGU PENGESAHAN DIREKTUR
                        </div>
                    @else
                        <div class="px-3 py-1.5 rounded-lg text-xs font-bold bg-red-100 text-red-800 border border-red-300">
                            {{ $contract->status }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Konten Dua Kolom -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <!-- Kolom 1: Informasi Mitra B2B -->
                <div class="space-y-4 bg-gray-50/60 p-4 rounded-xl border border-gray-200/60">
                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-200 pb-2">
                        Entitas Klien / Mitra Penerima
                    </h4>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Nama Perusahaan:</span>
                        <span class="col-span-2 font-bold text-gray-800">{{ $contract->user->company_name ?? '-' }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Nama Akun:</span>
                        <span class="col-span-2 font-semibold text-gray-800">{{ $contract->user->name }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Email Akun:</span>
                        <span class="col-span-2 text-gray-700">{{ $contract->user->email }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Kontak Person (PIC):</span>
                        <span class="col-span-2 font-semibold text-gray-800">
                            {{ $contract->user->pic_name ?? $contract->user->name }} ({{ $contract->user->pic_phone ?? $contract->user->phone }})
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Tipe Pelanggan:</span>
                        <span class="col-span-2">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[10px] font-bold">
                                {{ $contract->user->client_type ?? 'B2B_KONTRAK' }}
                            </span>
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Alamat Kirim:</span>
                        <span class="col-span-2 text-gray-700 leading-relaxed">{{ $contract->user->address ?? 'Sesuai pesanan' }}</span>
                    </div>
                </div>

                <!-- Kolom 2: Parameter Finansial & Pasokan -->
                <div class="space-y-4 bg-gray-50/60 p-4 rounded-xl border border-gray-200/60">
                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-200 pb-2">
                        Klausul Pasokan &amp; Penetapan Harga
                    </h4>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Komoditas:</span>
                        <span class="col-span-2 font-bold text-gray-900">{{ $contract->product->name }} ({{ $contract->product->grade ?? 'Grade A' }})</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Harga Katalog Dasar:</span>
                        <span class="col-span-2 text-gray-600">Rp {{ number_format($contract->product->base_price, 0, ',', '.') }}/Kg</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 items-center">
                        <span class="text-gray-500">Harga Kesepakatan PKS:</span>
                        <div class="col-span-2">
                            <span class="font-extrabold text-base text-emerald-700">
                                Rp {{ number_format($contract->fixed_price_per_kg, 0, ',', '.') }}
                            </span>
                            <span class="text-gray-500 text-[10px]">/Kg (Tetap)</span>
                            @php
                                $diff = $contract->fixed_price_per_kg - $contract->product->base_price;
                            @endphp
                            @if($diff < 0)
                                <span class="block text-[10px] text-emerald-600 font-semibold">
                                    Diskon khusus: -Rp {{ number_format(abs($diff), 0) }}/Kg (-{{ round(abs($diff) / $contract->product->base_price * 100, 1) }}%)
                                </span>
                            @elseif($diff > 0)
                                <span class="block text-[10px] text-blue-600 font-semibold">
                                    Premi spesifikasi: +Rp {{ number_format($diff, 0) }}/Kg
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Termin Pembayaran:</span>
                        <span class="col-span-2 font-bold text-gray-800">
                            {{ $contract->top_days }} Hari Kalender (TOP)
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Komitmen Volume:</span>
                        <span class="col-span-2 font-bold text-gray-800">
                            {{ number_format($contract->committed_volume_per_cycle, 1, ',', '.') }} Kg / Siklus
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <span class="text-gray-500">Estimasi Nilai Siklus:</span>
                        <span class="col-span-2 font-extrabold text-gray-900">
                            Rp {{ number_format($contract->committed_volume_per_cycle * $contract->fixed_price_per_kg, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Jejak Audit & Otorisasi Direktur -->
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <h4 class="font-bold text-gray-900 text-xs mb-3 uppercase tracking-wider">
                    Jejak Audit & Otorisasi Legalitas
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-400 block text-[10px]">WAKTU PENERBITAN</span>
                        <strong class="text-gray-800">{{ $contract->created_at->format('d F Y, H:i') }} WIB</strong>
                    </div>

                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-400 block text-[10px]">PEJABAT PENGESAH</span>
                        @if($contract->approver)
                            <strong class="text-emerald-800">{{ $contract->approver->name }}</strong>
                            <span class="block text-[10px] text-gray-500">Direktur / Owner Koperasi GPA</span>
                        @else
                            <strong class="text-amber-700 italic">Menunggu Pengesahan</strong>
                        @endif
                    </div>

                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-400 block text-[10px]">WAKTU PENGESAHAN</span>
                        @if($contract->approved_at)
                            <strong class="text-emerald-800">{{ $contract->approved_at->format('d F Y, H:i') }} WIB</strong>
                        @else
                            <strong class="text-amber-700 italic">-</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Transaksi Pesanan Menggunakan Komoditas Ini -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">
                        Riwayat Realisasi Pesanan Mitra untuk Komoditas Ini
                    </h3>
                    <p class="text-xs text-gray-500">
                        Transaksi pesanan dari {{ $contract->user->company_name ?? $contract->user->name }} yang memuat {{ $contract->product->name }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 font-bold uppercase text-[11px] border-b border-gray-100">
                            <th class="p-3">No. Pesanan</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Bobot Dipesan vs Realisasi</th>
                            <th class="p-3">Total Pesanan</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                            @php
                                $item = $order->orderItems->firstWhere('product_id', $contract->product_id);
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="p-3 font-mono font-bold text-gray-900">
                                    {{ $order->order_number }}
                                </td>
                                <td class="p-3 text-gray-600">
                                    {{ $order->created_at->format('d/m/Y') }}
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if($item)
                                        {{ number_format($item->ordered_qty, 1) }} Kg
                                        @if($item->actual_net_weight)
                                            <span class="font-bold text-emerald-700">(Riil: {{ number_format($item->actual_net_weight, 1) }} Kg)</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-3 font-semibold text-emerald-700">
                                    Rp {{ number_format($order->grand_total ?? $order->estimated_total, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('direktur.orders.show', $order->id) }}" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded font-semibold text-[10px] transition">
                                        Inspeksi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-400">
                                    Belum ada transaksi pemesanan untuk komoditas ini dari klien terkait.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-dynamic-component>

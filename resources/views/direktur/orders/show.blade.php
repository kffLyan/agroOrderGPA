<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Inspeksi Pengawasan Pesanan: {{ $order->order_number }}
    </x-slot>

    <div class="space-y-6 text-xs">
        <!-- Bar Aksi & Status Pesanan -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div>
                <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('direktur.dashboard') }}" class="text-xs text-[#153a01] hover:underline font-semibold flex items-center gap-1">
                    &larr; Kembali
                </a>
            </div>
            <div>
                @php
                    $badgeClasses = match($order->status) {
                        'SELESAI' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'SELESAI_CATATAN' => 'bg-teal-100 text-teal-800 border-teal-300',
                        'DALAM_PENGIRIMAN', 'SIAP_KIRIM' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'TERVERIFIKASI' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                        'MENUNGGU_VERIFIKASI' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'BATAL' => 'bg-red-100 text-red-800 border-red-300',
                        default => 'bg-gray-100 text-gray-700 border-gray-300',
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                    Status: {{ str_replace('_', ' ', $order->status) }}
                </span>
            </div>
        </div>

        <!-- Grid 4 Pilar Pengawasan Pesanan -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Klien Pemesan -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">1. ENTITAS PEMBELI</span>
                <div class="font-bold text-gray-900 text-sm">
                    {{ $order->user->company_name ?? $order->user->name }}
                </div>
                <div class="text-gray-600">
                    Akun: {{ $order->user->name }}<br>
                    Kontak: {{ $order->user->phone }}<br>
                    Tipe: <span class="font-semibold text-blue-700">{{ $order->user->client_type ?? 'REGULER' }}</span>
                </div>
                <div class="pt-1 text-[11px] text-gray-500 border-t border-gray-100">
                    <strong>Tujuan:</strong> {{ $order->delivery_address }}
                </div>
            </div>

            <!-- 2. Verifikasi Sekretaris -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">2. TAHAP ADMINISTRASI</span>
                <div class="font-bold text-gray-900 text-sm">
                    {{ $order->verifier ? 'Terverifikasi' : 'Menunggu Verifikasi' }}
                </div>
                <div class="text-gray-600">
                    Petugas: <strong>{{ $order->verifier->name ?? 'Staf Sekretariat' }}</strong><br>
                    Waktu Pesan: {{ $order->created_at->format('d/m/Y H:i') }}<br>
                    Target Kirim: <strong class="text-emerald-800">{{ $order->target_delivery_date ? $order->target_delivery_date->format('d F Y') : '-' }}</strong>
                </div>
                <div class="pt-1 text-[11px] text-gray-500 border-t border-gray-100">
                    Jalur Order: <span class="font-semibold">{{ $order->order_source }}</span>
                </div>
            </div>

            <!-- 3. Eksekusi Armada & Logistik -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">3. DISTRIBUSI & ARMADA</span>
                <div class="font-bold text-gray-900 text-sm">
                    {{ $order->driver ? $order->driver->name : 'Belum Ditugaskan' }}
                </div>
                <div class="text-gray-600">
                    No. Polisi: <strong>{{ $order->vehicle_plate_number ?? '-' }}</strong><br>
                    Surat Jalan: <strong class="font-mono text-gray-900">{{ $order->surat_jalan_number ?? '-' }}</strong><br>
                    Penerima: <strong>{{ $order->received_by_name ?? '-' }}</strong>
                </div>
                <div class="pt-1 text-[11px] text-gray-500 border-t border-gray-100">
                    Waktu Sampai: {{ $order->arrival_time ? $order->arrival_time->format('d/m/Y H:i') : '-' }}
                </div>
            </div>

            <!-- 4. Faktur & Finansial -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">4. STATUS FAKTUR / PEMBAYARAN</span>
                <div class="font-bold text-emerald-800 text-sm">
                    Rp {{ number_format($order->grand_total ?? $order->estimated_total, 0, ',', '.') }}
                </div>
                <div class="text-gray-600">
                    Faktur: <span class="font-mono">{{ $order->invoice->invoice_number ?? 'Belum terbit' }}</span><br>
                    Status Faktur: <strong class="text-blue-700">{{ $order->invoice->status ?? 'UNPAID' }}</strong><br>
                    Jatuh Tempo: {{ $order->invoice && $order->invoice->due_date ? date('d/m/Y', strtotime($order->invoice->due_date)) : '-' }}
                </div>
                <div class="pt-1 text-[11px] text-gray-500 border-t border-gray-100">
                    Pembayaran: {{ $order->payments->first() ? $order->payments->first()->status : 'Belum tercatat' }}
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Komoditas & Timbangan Lapangan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                    Verifikasi Hasil Timbangan Riil Komoditas (Packing House)
                </h3>
                <p class="text-xs text-gray-500">Perbandingan antara estimasi pemesanan dengan penimbangan fisik subuh</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase font-bold text-[11px] border-b border-gray-100">
                            <th class="p-3">Komoditas Sayuran</th>
                            <th class="p-3">Grade</th>
                            <th class="p-3 text-right">Qty Pesan</th>
                            <th class="p-3 text-right">Bobot Riil Timbang</th>
                            <th class="p-3 text-right">Harga Satuan</th>
                            <th class="p-3 text-right">Subtotal Final</th>
                            <th class="p-3">Catatan / Retur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->orderItems as $item)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="p-3 font-semibold text-gray-900">
                                    {{ $item->product->name }}
                                    <span class="block text-[10px] text-gray-400 font-mono">SKU: {{ $item->product->sku }}</span>
                                </td>
                                <td class="p-3 text-gray-600">
                                    {{ $item->product->grade ?? 'Grade A' }}
                                </td>
                                <td class="p-3 text-right font-medium text-gray-700">
                                    {{ number_format($item->ordered_qty, 1) }} Kg
                                </td>
                                <td class="p-3 text-right font-bold text-emerald-800">
                                    @if($item->actual_net_weight)
                                        {{ number_format($item->actual_net_weight, 1) }} Kg
                                    @else
                                        <span class="text-gray-400 italic">Belum ditimbang</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right text-gray-800">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}/Kg
                                </td>
                                <td class="p-3 text-right font-bold text-emerald-700 text-sm">
                                    Rp {{ number_format($item->subtotal_final ?? ($item->unit_price * $item->ordered_qty), 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-[11px]">
                                    @if($item->returned_weight > 0)
                                        <span class="text-red-600 font-semibold">
                                            Retur: {{ number_format($item->returned_weight, 1) }} Kg ({{ $item->return_reason ?? 'Afkir' }})
                                        </span>
                                    @else
                                        <span class="text-gray-400">Sesuai standar</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold text-gray-900 border-t border-gray-200">
                            <td colspan="5" class="p-3 text-right">TOTAL NILAI AKHIR TRANSAKSI:</td>
                            <td class="p-3 text-right text-emerald-800 text-base">
                                Rp {{ number_format($order->grand_total ?? $order->estimated_total, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Bukti Pengantaran / Surat Jalan / Pembayaran -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bukti Pengantaran (PoD) -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-3">
                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                    Bukti Penerimaan Barang (Proof of Delivery)
                </h4>
                @if($order->pod_photo_url)
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="font-semibold text-gray-800">Penerima Barang di Lokasi:</div>
                        <div class="text-sm font-bold text-emerald-800">{{ $order->received_by_name ?? 'Staf Receiving Klien' }}</div>
                        <div class="text-[10px] text-gray-500 mt-1">Berkas PoD: {{ $order->pod_photo_url }}</div>
                    </div>
                @else
                    <div class="p-6 bg-gray-50 rounded-lg text-center text-gray-400">
                        Foto Surat Jalan bertanda tangan belum diunggah driver.
                    </div>
                @endif
            </div>

            <!-- Bukti Pembayaran -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-3">
                <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                    Rekam Jejak Pelunasan & Mutasi Bank
                </h4>
                @forelse($order->payments as $payment)
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-gray-800">{{ $payment->payment_reference }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">{{ $payment->status }}</span>
                        </div>
                        <div class="text-sm font-bold text-emerald-800">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }} via {{ $payment->payment_method }}
                        </div>
                        <div class="text-[10px] text-gray-500">
                            Diverifikasi: {{ $payment->verifier->name ?? 'Sekretaris' }} pada {{ $payment->paid_at ? date('d/m/Y H:i', strtotime($payment->paid_at)) : '-' }}
                        </div>
                        @if($payment->verification_note)
                            <div class="text-[10px] text-gray-600 italic mt-1">
                                Catatan: {{ $payment->verification_note }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 bg-gray-50 rounded-lg text-center text-gray-400">
                        Belum ada data pembayaran untuk pesanan ini.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-dynamic-component>

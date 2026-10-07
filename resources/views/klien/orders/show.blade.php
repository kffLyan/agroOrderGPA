@extends('layouts.klien')

@section('title', 'Detail & Pelacakan Pesanan ' . $order->order_number)

@php
    $rupiah = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    $kilogram = fn ($value) => number_format($value, 1, ',', '.') . ' kg';

    $statusStyles = [
        'MENUNGGU_VERIFIKASI' => 'bg-warning-soft text-warning-deep ring-warning/40',
        'TERVERIFIKASI'       => 'bg-accent text-ink ring-accent-edge',
        'SIAP_KIRIM'          => 'bg-accent text-ink ring-accent-edge',
        'DALAM_PENGIRIMAN'    => 'bg-brand text-white ring-accent',
        'SELESAI'             => 'bg-accent text-success-ink ring-accent',
        'SELESAI_CATATAN'     => 'bg-warning-soft text-warning-deep ring-warning',
        'BATAL'               => 'bg-surface-pill text-ink-quiet ring-line-board',
    ];

    $statusLabels = [
        'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
        'TERVERIFIKASI'       => 'Diverifikasi Sekretaris',
        'SIAP_KIRIM'          => 'Siap Kirim (Netto Sah)',
        'DALAM_PENGIRIMAN'    => 'Armada Menuju Dock',
        'SELESAI'             => 'Selesai & Diterima',
        'SELESAI_CATATAN'     => 'Selesai (Dengan Catatan)',
        'BATAL'               => 'Pesanan Dibatalkan',
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-6">
        {{-- Navigasi kembali & Header --}}
        <div class="flex flex-col gap-3 border-b border-line-soft pb-4">
            <a href="{{ route('klien.orders.index') }}"
                class="inline-flex w-fit items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-ink-body transition-colors hover:text-success-deep">
                <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                Kembali ke Daftar Pesanan
            </a>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 flex-col gap-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="gpa-section-title text-xl font-extrabold text-ink lg:text-2xl">
                            {{ $order->order_number }}
                        </h1>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset {{ $statusStyles[$order->status] ?? 'bg-surface-pill text-ink' }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>
                    <p class="gpa-body text-ink-body">
                        Dipesan pada {{ $order->created_at->format('d F Y, H:i') }} WIB &bull; Sumber: {{ $order->order_source }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @if ($order->invoice)
                        <a href="{{ route('klien.documents') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-line-soft bg-surface px-3 py-2 text-xs font-semibold text-ink transition-colors hover:bg-surface-muted">
                            <x-gpa.icon name="invoice" class="h-3.5 w-3.5 text-ink" />
                            Lihat Faktur ({{ $order->invoice->invoice_number }})
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- 5 Tahap Visual Tracking Pipeline --}}
        <section class="gpa-card flex flex-col gap-4 p-5 lg:p-6">
            <div class="flex items-center justify-between border-b border-line-hair pb-3">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="truck" class="h-5 w-5 text-success-deep" />
                    <h2 class="font-sans text-base font-bold text-ink">Pelacakan Alur Operasional (5 Tahap)</h2>
                </div>
                <span class="text-xs font-semibold text-ink-quiet">SLA Standar Cold-Chain GPA</span>
            </div>

            <ol class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($trackingSteps as $step)
                    @php
                        $isDone = $step['state'] === 'done';
                        $isActive = $step['state'] === 'active';
                        $isCancelled = $step['state'] === 'cancelled';
                        $isPending = $step['state'] === 'pending';
                    @endphp

                    <li @class([
                        'flex flex-col gap-1.5 rounded-xl border p-3.5 transition-all',
                        'border-accent-edge bg-surface-shell ring-2 ring-accent/30' => $isActive,
                        'border-line-hair bg-surface' => $isDone,
                        'border-danger/30 bg-danger/5' => $isCancelled,
                        'border-transparent bg-surface-pill/50 opacity-60' => $isPending,
                    ])>
                        <div class="flex items-center justify-between">
                            <span @class([
                                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                                'bg-accent text-ink ring-2 ring-brand' => $isActive,
                                'bg-ink text-white' => $isDone,
                                'bg-danger text-white' => $isCancelled,
                                'bg-surface-disabled text-ink-quiet' => $isPending,
                            ])>
                                @if ($isDone)
                                    <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                                @elseif ($isCancelled)
                                    <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                                @else
                                    {{ $step['step'] }}
                                @endif
                            </span>

                            <span class="text-[9px] font-bold uppercase tracking-wider {{ $isActive ? 'text-success-deep' : 'text-ink-quiet' }}">
                                {{ $step['time'] }}
                            </span>
                        </div>

                        <p class="font-sans text-xs font-bold leading-4 {{ $isPending ? 'text-ink-quiet' : 'text-ink' }}">
                            {{ $step['title'] }}
                        </p>
                        <p class="text-[11px] leading-4 text-ink-body">
                            {{ $step['desc'] }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Grid Detail Pesanan & Logistik --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri: Rincian Komoditas --}}
            <div class="flex flex-col gap-6 lg:col-span-2">
                <section class="gpa-card overflow-hidden">
                    <header class="border-b border-line-hair bg-surface-shell p-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <h2 class="font-sans text-base font-bold text-ink">Rincian Komoditas &amp; Hasil Penimbangan</h2>
                            <span class="rounded bg-surface-pill px-2 py-0.5 text-xs font-semibold text-ink-body">
                                {{ $order->orderItems->count() }} Komoditas
                            </span>
                        </div>
                    </header>

                    <div class="gpa-scroll-x">
                        <table class="w-full min-w-[36rem] border-collapse text-left">
                            <thead>
                                <tr class="border-b border-line-hair bg-surface-muted text-xs font-bold uppercase text-ink-body">
                                    <th class="px-4 py-3">Komoditas</th>
                                    <th class="px-4 py-3 text-right">Qty Pesan</th>
                                    <th class="px-4 py-3 text-right">Netto Sah Gudang</th>
                                    <th class="px-4 py-3 text-right">Harga Satuan</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line-hair/60 text-sm">
                                @foreach ($order->orderItems as $item)
                                    @php
                                        $ordered = (float) $item->ordered_qty;
                                        $actual = $item->actual_net_weight ? (float) $item->actual_net_weight : null;
                                        $price = (float) $item->unit_price;
                                        $subtotal = $item->subtotal_final ? (float) $item->subtotal_final : ($actual ? $actual * $price : $ordered * $price);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3.5">
                                            <p class="font-bold text-ink">{{ $item->product->name ?? 'Komoditas' }}</p>
                                            <p class="text-xs text-ink-quiet">SKU: {{ $item->product->sku ?? '-' }} &bull; Grade: {{ $item->product->grade ?? 'A' }}</p>
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-medium text-ink-body">
                                            {{ number_format($ordered, 1, ',', '.') }} {{ $item->product->unit ?? 'kg' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-bold {{ $actual ? 'text-success-deep' : 'italic text-ink-quiet' }}">
                                            @if ($actual)
                                                {{ number_format($actual, 1, ',', '.') }} {{ $item->product->unit ?? 'kg' }}
                                            @else
                                                Belum Ditimbang
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-right text-ink">
                                            {{ $rupiah($price) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-bold text-ink">
                                            {{ $rupiah($subtotal) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-line-soft bg-surface-shell">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right font-bold text-ink">
                                        {{ $order->grand_total ? 'Total Tagihan Sah (Netto):' : 'Total Estimasi Nilai PO:' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-extrabold text-lg text-ink">
                                        {{ $rupiah($order->grand_total ?: $order->estimated_total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Kolom Kanan: Logistik & Pembayaran --}}
            <div class="flex flex-col gap-6">
                {{-- Data Armada & Surat Jalan --}}
                <section class="gpa-card flex flex-col gap-3 p-5">
                    <h2 class="flex items-center gap-2 font-sans text-sm font-bold text-ink border-b border-line-hair pb-2">
                        <x-gpa.icon name="truck" class="h-4 w-4 text-success-deep" />
                        Logistik &amp; Surat Jalan
                    </h2>

                    <dl class="flex flex-col gap-2 text-xs">
                        <div class="flex justify-between">
                            <dt class="text-ink-body">No. Surat Jalan:</dt>
                            <dd class="font-bold text-ink">{{ $order->surat_jalan_number ?: 'Belum Terbit' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-body">Supir Armada:</dt>
                            <dd class="font-semibold text-ink">{{ $order->driver ? $order->driver->name : 'Dalam Penugasan' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-body">Nomor Plat Armada:</dt>
                            <dd class="font-semibold text-ink">{{ $order->vehicle_plate_number ?: '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-body">Target Tiba:</dt>
                            <dd class="font-semibold text-ink">{{ $order->target_delivery_date ? $order->target_delivery_date->format('d M Y') : '-' }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 border-t border-line-hair/60 pt-2">
                            <dt class="text-ink-body">Alamat / Dock Bongkar Muat:</dt>
                            <dd class="font-medium text-ink">{{ $order->delivery_address }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- Bukti Serah Terima / PoD (Jika sudah tersedia) --}}
                @if ($order->pod_photo_url || $order->received_by_name || in_array($order->status, ['SELESAI', 'SELESAI_CATATAN']))
                    <section class="gpa-card flex flex-col gap-3 p-5">
                        <h2 class="flex items-center gap-2 font-sans text-sm font-bold text-ink border-b border-line-hair pb-2">
                            <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                            Bukti Serah Terima (Proof of Delivery / PoD)
                        </h2>

                        <dl class="flex flex-col gap-2 text-xs">
                            <div class="flex justify-between">
                                <dt class="text-ink-body">Penerima Dock / PIC:</dt>
                                <dd class="font-bold text-ink">{{ $order->received_by_name ?: 'PIC Receiving Dock' }}</dd>
                            </div>
                            @if ($order->arrival_time)
                                <div class="flex justify-between">
                                    <dt class="text-ink-body">Waktu Serah Terima:</dt>
                                    <dd class="font-semibold text-ink">{{ $order->arrival_time->format('d M Y, H:i') }} WIB</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($order->pod_photo_url)
                            <div class="mt-2 flex flex-col gap-1.5">
                                <span class="text-[10px] font-semibold text-ink-quiet uppercase">Foto Fisik Dokumen / Timbangan:</span>
                                <a href="{{ asset('storage/' . $order->pod_photo_url) }}" target="_blank"
                                    class="group relative block overflow-hidden rounded-lg border border-line-soft bg-surface-shell">
                                    <img src="{{ asset('storage/' . $order->pod_photo_url) }}" alt="Bukti Serah Terima"
                                        class="h-36 w-full object-cover transition-transform duration-200 group-hover:scale-105" />
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                        <span class="rounded bg-surface px-2.5 py-1 text-xs font-bold text-ink">Buka Foto Penuh</span>
                                    </div>
                                </a>
                            </div>
                        @else
                            <div class="rounded-lg bg-surface-shell p-2.5 text-center text-xs text-ink-quiet">
                                Surat jalan fisik telah ditandatangani &amp; dicap basah oleh pihak penerima.
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Status Pembayaran --}}
                <section class="gpa-card flex flex-col gap-3 p-5">
                    <h2 class="flex items-center gap-2 font-sans text-sm font-bold text-ink border-b border-line-hair pb-2">
                        <x-gpa.icon name="wallet" class="h-4 w-4 text-success-deep" />
                        Status Pembayaran
                    </h2>

                    @forelse ($order->payments as $payment)
                        <div class="flex flex-col gap-1.5 rounded-lg border border-line-soft bg-surface-shell p-3 text-xs">
                            <div class="flex justify-between font-bold">
                                <span>{{ $payment->payment_reference }}</span>
                                <span class="text-success-deep">{{ $payment->status }}</span>
                            </div>
                            <div class="flex justify-between text-ink-body">
                                <span>Metode: {{ $payment->payment_method }}</span>
                                <span class="font-semibold text-ink">{{ $rupiah($payment->amount) }}</span>
                            </div>
                            @if ($payment->proof_url)
                                <a href="{{ asset('storage/' . $payment->proof_url) }}" target="_blank"
                                    class="mt-1 text-xs font-semibold text-brand underline">
                                    Lihat Bukti Transfer
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-ink-body italic">Belum ada catatan transaksi pembayaran untuk pesanan ini.</p>
                    @endforelse

                    <div class="border-t border-line-hair pt-2">
                        <a href="{{ route('klien.documents') }}"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-surface-pill px-3 py-2 text-xs font-bold text-ink transition-colors hover:bg-surface-disabled">
                            <x-gpa.icon name="file-text" class="h-3.5 w-3.5" />
                            Buka Pusat Dokumen &amp; Faktur
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @if (session('success'))
        <script>
            try {
                localStorage.removeItem('gpa_client_po_draft');
                localStorage.setItem('gpa_client_cart_emptied', 'true');
            } catch (e) {}
        </script>
    @endif
@endsection

@extends('layouts.klien')

@section('title', 'Detail & Pelacakan Pesanan ' . $order->order_number)

@php
    $rupiah = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    $kilogram = fn ($value) => number_format($value, 1, ',', '.') . ' kg';

    $hasPendingProof = ($order->status === 'MENUNGGU_PEMBAYARAN' && $order->has_pending_proof);

    $statusStyles = [
        'MENUNGGU_VERIFIKASI' => 'bg-warning-soft text-warning-deep ring-warning/40',
        'MENUNGGU_PEMBAYARAN' => $hasPendingProof ? 'bg-warning-soft text-warning-deep ring-warning/40' : 'bg-brand/10 text-brand ring-brand/30',
        'LUNAS'               => 'bg-accent/40 text-success-deep ring-success/30',
        'SIAP_KIRIM'          => 'bg-accent text-ink ring-accent-edge',
        'DALAM_PENGIRIMAN'    => 'bg-brand text-white ring-accent',
        'SELESAI'             => 'bg-accent text-success-ink ring-accent',
        'SELESAI_CATATAN'     => 'bg-warning-soft text-warning-deep ring-warning',
        'BATAL'               => 'bg-surface-pill text-ink-quiet ring-line-board',
    ];

    $statusLabels = [
        'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi Pesanan',
        'MENUNGGU_PEMBAYARAN' => $hasPendingProof ? 'Menunggu Verifikasi Pembayaran' : 'Menunggu Pembayaran',
        'LUNAS'               => 'Pembayaran Lunas',
        'SIAP_KIRIM'          => 'Surat Jalan Terbit (Siap Kirim)',
        'DALAM_PENGIRIMAN'    => 'Armada Menuju Dock',
        'SELESAI'             => 'Selesai & Diterima',
        'SELESAI_CATATAN'     => 'Selesai (Ada Catatan Retur)',
        'BATAL'               => 'Pesanan Dibatalkan',
    ];

    $statusOrder = $order->status;

    $stepStates = [
        1 => 'done',
        2 => in_array($statusOrder, ['MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'MENUNGGU_VERIFIKASI' ? 'active' : 'pending'),
        3 => in_array($statusOrder, ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'MENUNGGU_PEMBAYARAN' ? 'active' : 'pending'),
        4 => in_array($statusOrder, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'LUNAS' ? 'active' : 'pending'),
        5 => in_array($statusOrder, ['SELESAI', 'SELESAI_CATATAN']) ? 'done' : (in_array($statusOrder, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN']) ? 'active' : 'pending'),
    ];

    if ($statusOrder === 'BATAL') {
        $stepStates = [1 => 'done', 2 => 'cancelled', 3 => 'pending', 4 => 'pending', 5 => 'pending'];
    }
@endphp

@section('content')
    <div x-data="{ podModalOpen: false }" class="flex flex-col gap-6">
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
                    @if ($order->status === 'MENUNGGU_PEMBAYARAN')
                        @if ($hasPendingProof)
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-warning/40 bg-warning-wash px-3 py-1.5 text-xs font-bold text-warning-deep shadow-xs">
                                    <x-gpa.icon name="clock" class="h-4 w-4 text-warning-deep" />
                                    Bukti Terkirim ke Sekretaris
                                </span>
                                <a href="{{ route('klien.payment-proof', ['tagihan' => $order->invoice?->invoice_number, 'order_id' => $order->id]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-line-soft bg-surface px-3 py-2 text-xs font-bold text-ink shadow-xs transition-all hover:bg-surface-muted hover:border-line-board">
                                    <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-ink" />
                                    Lihat / Perbarui Bukti
                                </a>
                            </div>
                        @else
                            <a href="{{ route('klien.payment-proof', ['tagihan' => $order->invoice?->invoice_number, 'order_id' => $order->id]) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-accent px-4 py-2 text-xs font-extrabold text-ink shadow-sub transition-all hover:bg-accent-deep hover:shadow-md">
                                <x-gpa.icon name="wallet" class="h-4 w-4 text-ink" />
                                Bayar Sekarang
                            </a>
                        @endif
                    @endif

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

        {{-- Banner Notifikasi Tagihan Menunggu Pembayaran / Bukti Ditolak --}}
        @if ($order->status === 'MENUNGGU_PEMBAYARAN' && $order->has_rejected_proof)
            @php
                $rejectedPayment = $order->rejected_proof_payment;
            @endphp
            <div class="flex flex-col gap-3 rounded-2xl border-2 border-danger/60 bg-danger-soft/40 p-5 shadow-card sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5 min-w-0">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger text-white shadow-sub">
                        <x-gpa.icon name="alert-triangle" class="h-6 w-6 text-white" />
                    </span>
                    <div class="flex flex-col gap-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-danger px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                Bukti Pembayaran Ditolak Verifikator
                            </span>
                            @if ($rejectedPayment?->payment_reference)
                                <span class="text-xs font-semibold text-danger">
                                    Ref: {{ $rejectedPayment->payment_reference }}
                                </span>
                            @endif
                        </div>
                        <h2 class="font-sans text-base font-extrabold text-danger sm:text-lg">
                            Bukti Transfer Tidak Valid &bull; Tagihan Belum Lunas
                        </h2>
                        <div class="mt-1 rounded-xl bg-white/95 p-3 border border-danger/30 text-xs text-ink max-w-2xl shadow-xs">
                            <p class="font-bold text-danger text-[11px] uppercase tracking-wider mb-0.5 flex items-center gap-1.5">
                                <x-gpa.icon name="file-text" class="h-3.5 w-3.5 text-danger" />
                                Alasan Penolakan dari Sekretariat GPA:
                            </p>
                            <p class="font-semibold text-ink leading-relaxed">
                                "{{ $rejectedPayment?->verification_note ?: 'Bukti transfer tidak valid atau dana belum masuk pada mutasi rekening koran.' }}"
                            </p>
                            @if ($rejectedPayment?->verifier)
                                <p class="mt-1 text-[11px] text-ink-quiet">
                                    Diverifikasi oleh: <strong class="text-ink">{{ $rejectedPayment->verifier->name }}</strong>
                                </p>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-ink-body max-w-2xl leading-relaxed">
                            Pesanan ini belum dapat diterbitkan Surat Jalan sebelum pembayaran diverifikasi sah. Silakan unggah kembali bukti pembayaran yang benar.
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:self-center">
                    <a href="{{ route('klien.payment-proof', ['tagihan' => $order->invoice?->invoice_number, 'order_id' => $order->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-danger px-5 py-2.5 font-sans text-xs font-extrabold uppercase tracking-wider text-white shadow-lg transition-transform hover:scale-105 hover:bg-danger/90">
                        <x-gpa.icon name="upload" class="h-4 w-4 text-white" />
                        Unggah Ulang Bukti Pembayaran
                    </a>
                </div>
            </div>
        @elseif ($order->status === 'MENUNGGU_PEMBAYARAN')
            <div class="flex flex-col gap-3 rounded-2xl border-2 border-brand/40 bg-brand p-5 text-white shadow-card sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent text-ink shadow-sub">
                        <x-gpa.icon name="wallet" class="h-6 w-6 text-ink" />
                    </span>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-accent px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-ink">
                                Menunggu Pembayaran
                            </span>
                            @if ($order->invoice)
                                <span class="text-xs text-line-soft">
                                    Faktur #{{ $order->invoice->invoice_number }}
                                </span>
                            @endif
                        </div>
                        <h2 class="font-sans text-base font-bold text-white sm:text-lg">
                            Pesanan Telah Diverifikasi &bull; Total Tagihan: {{ $rupiah($order->grand_total) }}
                        </h2>
                        <p class="text-xs text-line-soft max-w-2xl leading-relaxed">
                            Sekretariat GPA telah menyetujui pesanan Anda dan mengunci kuota panen kebun. Segera lakukan pembayaran agar Surat Jalan diterbitkan dan armada dikirim tepat waktu.
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:self-center">
                    <a href="{{ route('klien.payment-proof', ['tagihan' => $order->invoice?->invoice_number, 'order_id' => $order->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-accent px-5 py-2.5 font-sans text-xs font-extrabold uppercase tracking-wider text-ink shadow-lg transition-transform hover:scale-105 hover:bg-accent-deep">
                        <x-gpa.icon name="send" class="h-4 w-4 text-ink" />
                        Bayar Sekarang &amp; Unggah Bukti
                    </a>
                </div>
            </div>
        @endif

        {{-- Banner Notifikasi Pesanan Selesai & Diterima di Dock --}}
        @if (in_array($order->status, ['SELESAI', 'SELESAI_CATATAN']))
            <div class="flex flex-col gap-4 rounded-2xl border-2 border-emerald-500/40 bg-gradient-to-r from-emerald-950 via-brand to-brand-deep p-5 text-white shadow-card sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-accent text-ink shadow-sub">
                        <x-gpa.icon name="check-circle" class="h-7 w-7 text-ink" />
                    </span>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-accent px-3 py-0.5 text-[10px] font-black uppercase tracking-wider text-ink">
                                Transaksi Tuntas &bull; Barang Diterima
                            </span>
                            @if ($order->invoice)
                                <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white">
                                    Faktur Lunas: #{{ $order->invoice->invoice_number }}
                                </span>
                            @endif
                        </div>
                        <h2 class="font-sans text-base font-bold text-white sm:text-lg">
                            Pesanan Selesai &amp; Serah Terima Tuntas di Dock Penerima
                        </h2>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-white/90">
                            <span>PIC Penerima: <strong class="text-accent">{{ $order->received_by_name ?: 'Staf Receiving Klien' }}</strong></span>
                            <span>&bull;</span>
                            <span>Waktu Serah Terima: <strong class="text-white">{{ $order->arrival_time ? $order->arrival_time->format('d M Y, H:i') . ' WIB' : 'Telah diverifikasi' }}</strong></span>
                            <span>&bull;</span>
                            <span>No. Surat Jalan: <strong class="text-white font-mono">{{ $order->surat_jalan_number ?: '-' }}</strong></span>
                        </div>
                        @if ($order->status === 'SELESAI_CATATAN')
                            <div class="mt-1 flex items-center gap-2 rounded-lg bg-warning/20 border border-warning/40 px-3 py-1.5 text-xs text-warning-cream">
                                <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0 text-warning" />
                                <span>Terdapat penyesuaian timbangan/retur mutu fisik saat bongkar muat. Rincian tercatat sah pada Berita Acara.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2 sm:self-center">
                    @if ($order->pod_photo_asset_url)
                        <button type="button" @click="document.getElementById('pod-section')?.scrollIntoView({ behavior: 'smooth' })"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-accent px-4 py-2.5 font-sans text-xs font-extrabold uppercase tracking-wider text-ink shadow-md transition-transform hover:scale-105 hover:bg-accent-deep">
                            <x-gpa.icon name="shield" class="h-4 w-4 text-ink" />
                            Lihat Bukti PoD
                        </button>
                    @endif
                    <a href="{{ route('prints.surat-jalan', ['order_id' => $order->id]) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 font-sans text-xs font-bold text-white backdrop-blur-xs transition-colors hover:bg-white/20">
                        <x-gpa.icon name="printer" class="h-4 w-4 text-accent" />
                        Cetak Surat Jalan
                    </a>
                </div>
            </div>
        @endif

        {{-- Banner Notifikasi Bukti Pembayaran Ditolak --}}
        @if ($order->status === 'MENUNGGU_PEMBAYARAN' && $order->has_rejected_proof)
            <div class="flex flex-col gap-3 rounded-2xl border-2 border-danger/60 bg-danger-soft/30 p-5 text-ink shadow-card sm:flex-row sm:items-start sm:justify-between transition-all">
                <div class="flex items-start gap-3.5 min-w-0">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger text-white shadow-sub">
                        <x-gpa.icon name="alert-triangle" class="h-6 w-6 text-white" />
                    </span>
                    <div class="flex flex-col gap-1.5 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-danger px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                Verifikasi Pembayaran Ditolak
                            </span>
                            @if ($order->rejected_proof_payment?->verifier)
                                <span class="text-xs text-ink-body">
                                    Diverifikasi oleh: <strong class="text-ink">{{ $order->rejected_proof_payment->verifier->name }}</strong>
                                </span>
                            @endif
                            <span class="text-xs text-ink-quiet">
                                &bull; Ref: {{ $order->rejected_proof_payment?->payment_reference ?: ('PAY-' . $order->rejected_proof_payment?->id) }}
                            </span>
                        </div>
                        <h2 class="font-sans text-base font-extrabold text-danger sm:text-lg">
                            Verifikasi Pembayaran Ditolak &bull; Mohon Unggah Ulang Bukti Transfer
                        </h2>
                        <div class="rounded-xl border border-danger/30 bg-white/95 p-3.5 text-xs text-ink shadow-xs">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-danger mb-1 flex items-center gap-1.5">
                                <x-gpa.icon name="file-text" class="h-3.5 w-3.5 text-danger" />
                                Catatan Verifikator Sekretariat GPA:
                            </span>
                            <p class="font-semibold text-ink leading-relaxed">
                                "{{ $order->rejected_proof_payment?->verification_note ?: 'Bukti pembayaran tidak sesuai atau tidak terbaca.' }}"
                            </p>
                        </div>
                        <p class="text-[11px] text-ink-body">
                            Pesanan belum dapat diproses ke tahap kemas / cold-chain. Silakan transfer ulang atau unggah bukti transfer yang valid.
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:self-center">
                    <a href="{{ route('klien.payment-proof', ['tagihan' => $order->invoice?->invoice_number, 'order_id' => $order->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-danger px-4 py-2.5 font-sans text-xs font-bold uppercase tracking-wider text-white shadow-md transition-all hover:bg-danger/90">
                        <x-gpa.icon name="upload" class="h-4 w-4 text-white" />
                        Unggah Ulang Bukti Pembayaran
                    </a>
                </div>
            </div>
        @endif

        {{-- Banner Notifikasi Penolakan Pesanan --}}
        @if ($order->status === 'BATAL')
            <div class="flex flex-col gap-3 rounded-2xl border-2 border-danger/40 bg-danger-soft p-5 text-ink shadow-card sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger text-white shadow-sub">
                        <x-gpa.icon name="x" class="h-6 w-6 text-white" />
                    </span>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-danger px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                Pesanan Ditolak &bull; Dibatalkan
                            </span>
                            @if ($order->verifier)
                                <span class="text-xs text-ink-body">
                                    Diverifikasi oleh: <strong class="text-ink">{{ $order->verifier->name }}</strong>
                                </span>
                            @endif
                        </div>
                        <h2 class="font-sans text-base font-bold text-danger sm:text-lg">
                            Verifikasi Pesanan Tidak Disetujui
                        </h2>
                        <div class="rounded-xl border border-danger/20 bg-surface p-3.5 text-xs text-ink shadow-sm">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-danger mb-1">
                                Alasan Penolakan:
                            </span>
                            <p class="font-medium text-ink leading-relaxed">
                                {{ $order->rejection_reason ?: 'Kapasitas armada / kuota stok panen tidak mencukupi untuk memenuhi pesanan ini.' }}
                            </p>
                        </div>
                        <p class="text-[11px] text-ink-body">
                            Pesanan ini tidak dapat dilanjutkan. Silakan lakukan pemesanan ulang dengan penyesuaian komoditas atau hubungi PIC Sekretariat GPA.
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:self-center">
                    <a href="{{ route('klien.catalog') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 font-sans text-xs font-bold uppercase tracking-wider text-accent shadow-md transition-all hover:bg-black">
                        <x-gpa.icon name="cart" class="h-4 w-4 text-accent" />
                        Katalog Pemesanan
                    </a>
                </div>
            </div>
        @endif

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
                {{-- Tahap 1 --}}
                @php $s1 = $stepStates[1]; @endphp
                <li @class([
                    'flex flex-col gap-1.5 rounded-xl border p-3.5 transition-all',
                    'border-accent-edge bg-surface-shell ring-2 ring-accent/30' => $s1 === 'active',
                    'border-line-hair bg-surface' => $s1 === 'done',
                    'border-danger/30 bg-danger/5' => $s1 === 'cancelled',
                    'border-transparent bg-surface-pill/50 opacity-60' => $s1 === 'pending',
                ])>
                    <div class="flex items-center justify-between">
                        <span @class([
                            'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                            'bg-accent text-ink ring-2 ring-brand' => $s1 === 'active',
                            'bg-ink text-white' => $s1 === 'done',
                            'bg-danger text-white' => $s1 === 'cancelled',
                            'bg-surface-disabled text-ink-quiet' => $s1 === 'pending',
                        ])>
                            @if ($s1 === 'done')
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            @elseif ($s1 === 'cancelled')
                                <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                            @else
                                1
                            @endif
                        </span>

                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $s1 === 'active' ? 'text-success-deep' : 'text-ink-quiet' }}">
                            {{ $order->created_at ? $order->created_at->format('d M Y, H:i') . ' WIB' : '-' }}
                        </span>
                    </div>

                    <p class="font-sans text-xs font-bold leading-4 {{ $s1 === 'pending' ? 'text-ink-quiet' : 'text-ink' }}">
                        PO Diajukan Mandiri
                    </p>
                    <p class="text-[11px] leading-4 text-ink-body">
                        Pesanan tercatat pada sistem portal web GPA.
                    </p>
                </li>

                {{-- Tahap 2 --}}
                @php $s2 = $stepStates[2]; @endphp
                <li @class([
                    'flex flex-col gap-1.5 rounded-xl border p-3.5 transition-all',
                    'border-accent-edge bg-surface-shell ring-2 ring-accent/30' => $s2 === 'active',
                    'border-line-hair bg-surface' => $s2 === 'done',
                    'border-danger/30 bg-danger/5' => $s2 === 'cancelled',
                    'border-transparent bg-surface-pill/50 opacity-60' => $s2 === 'pending',
                ])>
                    <div class="flex items-center justify-between">
                        <span @class([
                            'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                            'bg-accent text-ink ring-2 ring-brand' => $s2 === 'active',
                            'bg-ink text-white' => $s2 === 'done',
                            'bg-danger text-white' => $s2 === 'cancelled',
                            'bg-surface-disabled text-ink-quiet' => $s2 === 'pending',
                        ])>
                            @if ($s2 === 'done')
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            @elseif ($s2 === 'cancelled')
                                <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                            @else
                                2
                            @endif
                        </span>

                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $s2 === 'active' ? 'text-success-deep' : 'text-ink-quiet' }}">
                            {{ $order->verifier ? ($order->updated_at ? $order->updated_at->format('d M Y, H:i') . ' WIB' : '-') : 'SLA < 2 Jam' }}
                        </span>
                    </div>

                    <p class="font-sans text-xs font-bold leading-4 {{ $s2 === 'pending' ? 'text-ink-quiet' : 'text-ink' }}">
                        Verifikasi &amp; Kunci Kuota
                    </p>
                    <p class="text-[11px] leading-4 text-ink-body">
                        {{ $order->verifier ? "Diverifikasi oleh Sekretaris: {$order->verifier->name}" : 'Menunggu review dokumen dan stok bebas.' }}
                    </p>
                </li>

                {{-- Tahap 3 --}}
                @php $s3 = $stepStates[3]; @endphp
                <li @class([
                    'flex flex-col gap-1.5 rounded-xl border p-3.5 transition-all',
                    'border-accent-edge bg-surface-shell ring-2 ring-accent/30' => $s3 === 'active',
                    'border-line-hair bg-surface' => $s3 === 'done',
                    'border-danger/30 bg-danger/5' => $s3 === 'cancelled',
                    'border-transparent bg-surface-pill/50 opacity-60' => $s3 === 'pending',
                ])>
                    <div class="flex items-center justify-between">
                        <span @class([
                            'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                            'bg-accent text-ink ring-2 ring-brand' => $s3 === 'active',
                            'bg-ink text-white' => $s3 === 'done',
                            'bg-danger text-white' => $s3 === 'cancelled',
                            'bg-surface-disabled text-ink-quiet' => $s3 === 'pending',
                        ])>
                            @if ($s3 === 'done')
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            @elseif ($s3 === 'cancelled')
                                <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                            @else
                                3
                            @endif
                        </span>

                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $s3 === 'active' ? ($hasPendingProof ? 'text-warning-deep' : 'text-success-deep') : 'text-ink-quiet' }}">
                            {{ in_array($order->status, ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'Lunas Terverifikasi' : ($hasPendingProof ? 'Bukti Sedang Diverifikasi' : 'Menunggu Bayar') }}
                        </span>
                    </div>

                    <p class="font-sans text-xs font-bold leading-4 {{ $s3 === 'pending' ? 'text-ink-quiet' : 'text-ink' }}">
                        Faktur &amp; Konfirmasi Bayar
                    </p>
                    <p class="text-[11px] leading-4 text-ink-body">
                        {{ in_array($order->status, ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
                            ? 'Pembayaran telah disetujui lunas oleh kasir/sekretariat GPA.'
                            : ($hasPendingProof 
                                ? 'Bukti transfer telah dikirim ke Sekretariat. Menunggu pencocokan mutasi kas bank.'
                                : ($order->invoice ? "Faktur #{$order->invoice->invoice_number} terbit. Total tagihan dikunci upfront." : 'Menunggu penerbitan invoice resmi oleh Sekretaris.')) }}
                    </p>
                </li>

                {{-- Tahap 4 --}}
                @php $s4 = $stepStates[4]; @endphp
                <li @class([
                    'flex flex-col gap-1.5 rounded-xl border p-3.5 transition-all',
                    'border-accent-edge bg-surface-shell ring-2 ring-accent/30' => $s4 === 'active',
                    'border-line-hair bg-surface' => $s4 === 'done',
                    'border-danger/30 bg-danger/5' => $s4 === 'cancelled',
                    'border-transparent bg-surface-pill/50 opacity-60' => $s4 === 'pending',
                ])>
                    <div class="flex items-center justify-between">
                        <span @class([
                            'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                            'bg-accent text-ink ring-2 ring-brand' => $s4 === 'active',
                            'bg-ink text-white' => $s4 === 'done',
                            'bg-danger text-white' => $s4 === 'cancelled',
                            'bg-surface-disabled text-ink-quiet' => $s4 === 'pending',
                        ])>
                            @if ($s4 === 'done')
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            @elseif ($s4 === 'cancelled')
                                <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                            @else
                                4
                            @endif
                        </span>

                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $s4 === 'active' ? 'text-success-deep' : 'text-ink-quiet' }}">
                            {{ $order->surat_jalan_number ? 'SJ Terbit' : 'Syarat: Status Lunas' }}
                        </span>
                    </div>

                    <p class="font-sans text-xs font-bold leading-4 {{ $s4 === 'pending' ? 'text-ink-quiet' : 'text-ink' }}">
                        Surat Jalan &amp; Armada
                    </p>
                    <p class="text-[11px] leading-4 text-ink-body">
                        {{ $order->surat_jalan_number ? "Surat Jalan {$order->surat_jalan_number} diterbitkan oleh Sekretaris." : 'Surat jalan diterbitkan setelah pembayaran lunas.' }}
                    </p>
                </li>

                {{-- Tahap 5 --}}
                @php $s5 = $stepStates[5]; @endphp
                <li @class([
                    'flex flex-col gap-1.5 rounded-xl border p-3.5 transition-all',
                    'border-accent-edge bg-surface-shell ring-2 ring-accent/30' => $s5 === 'active',
                    'border-line-hair bg-surface' => $s5 === 'done',
                    'border-danger/30 bg-danger/5' => $s5 === 'cancelled',
                    'border-transparent bg-surface-pill/50 opacity-60' => $s5 === 'pending',
                ])>
                    <div class="flex items-center justify-between">
                        <span @class([
                            'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                            'bg-accent text-ink ring-2 ring-brand' => $s5 === 'active',
                            'bg-ink text-white' => $s5 === 'done',
                            'bg-danger text-white' => $s5 === 'cancelled',
                            'bg-surface-disabled text-ink-quiet' => $s5 === 'pending',
                        ])>
                            @if ($s5 === 'done')
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            @elseif ($s5 === 'cancelled')
                                <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                            @else
                                5
                            @endif
                        </span>

                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $s5 === 'active' ? 'text-success-deep' : 'text-ink-quiet' }}">
                            {{ $order->arrival_time ? $order->arrival_time->format('d M Y, H:i') . ' WIB' : 'Pengantaran & PoD' }}
                        </span>
                    </div>

                    <p class="font-sans text-xs font-bold leading-4 {{ $s5 === 'pending' ? 'text-ink-quiet' : 'text-ink' }}">
                        Pengiriman &amp; Serah Terima
                    </p>
                    <p class="text-[11px] leading-4 text-ink-body">
                        {{ $order->received_by_name ? "Diterima oleh: {$order->received_by_name}" : 'Penerimaan di dock klien, bukti foto digital & serah terima.' }}
                    </p>
                </li>
            </ol>
        </section>

        {{-- Grid Detail Pesanan & Logistik --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Kolom Kiri: Rincian Komoditas --}}
            <div class="flex flex-col gap-6 lg:col-span-2">
                <section class="gpa-card overflow-hidden">
                    <header class="border-b border-line-hair bg-surface-shell p-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <h2 class="font-sans text-base font-bold text-ink">Rincian Komoditas &amp; Harga Upfront</h2>
                            <span class="rounded bg-surface-pill px-2 py-0.5 text-xs font-semibold text-ink-body">
                                {{ $order->orderItems->count() }} Komoditas
                            </span>
                        </div>
                    </header>

                    <div class="gpa-scroll-x">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="border-b border-line-hair bg-surface-muted text-xs font-bold uppercase text-ink-body">
                                    <th class="px-4 py-3">Komoditas</th>
                                    <th class="px-4 py-3 text-right">Kuantitas PO</th>
                                    <th class="px-4 py-3 text-right">Harga Satuan</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-right">Retur / Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line-hair/60 text-sm">
                                @foreach ($order->orderItems as $item)
                                    @php
                                        $ordered = (float) $item->ordered_qty;
                                        $price = (float) $item->unit_price;
                                        $subtotal = (float) ($item->subtotal ?: ($ordered * $price));
                                        $returKg = (float) ($item->returned_weight ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3.5">
                                            <p class="font-bold text-ink">{{ $item->product->name ?? 'Komoditas' }}</p>
                                            <p class="text-xs text-ink-quiet">SKU: {{ $item->product->sku ?? '-' }} &bull; Grade: {{ $item->product->grade ?? 'A' }}</p>
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-medium text-ink-body">
                                            {{ number_format($ordered, 1, ',', '.') }} {{ $item->product->unit ?? 'kg' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right text-ink">
                                            {{ $rupiah($price) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right font-bold text-ink">
                                            {{ $rupiah($subtotal) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            @if ($returKg > 0)
                                                <span class="font-bold text-danger">{{ number_format($returKg, 1, ',', '.') }} kg</span>
                                                <span class="block text-[11px] text-ink-quiet">{{ $item->return_reason ?: 'Catatan retur' }}</span>
                                            @else
                                                <span class="text-xs text-ink-quiet">Sesuai (0 kg)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-line-soft bg-surface-shell">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right font-bold text-ink">
                                        Total Tagihan (Grand Total):
                                    </td>
                                    <td class="px-4 py-3 text-right font-extrabold text-lg text-ink">
                                        {{ $rupiah($order->grand_total) }}
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
                    <section id="pod-section" class="gpa-card flex flex-col gap-3 p-5 scroll-mt-20">
                        <div class="flex items-center justify-between border-b border-line-hair pb-2">
                            <h2 class="flex items-center gap-2 font-sans text-sm font-bold text-ink">
                                <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                                Bukti Serah Terima (Proof of Delivery / PoD)
                            </h2>
                            <span class="rounded bg-accent/40 px-2 py-0.5 text-[9px] font-bold text-success-deep uppercase">
                                Sah &bull; Valid
                            </span>
                        </div>

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
                            <div class="flex justify-between">
                                <dt class="text-ink-body">No. Surat Jalan:</dt>
                                <dd class="font-mono font-bold text-ink">{{ $order->surat_jalan_number ?: '-' }}</dd>
                            </div>
                        </dl>

                        @if ($order->pod_photo_asset_url)
                            <div class="mt-2 flex flex-col gap-1.5">
                                <span class="text-[10px] font-semibold text-ink-quiet uppercase">Foto Fisik Dokumen / Timbangan:</span>
                                <div class="group relative block overflow-hidden rounded-xl border border-line-soft bg-surface-shell cursor-pointer"
                                    @click="podModalOpen = true">
                                    <img src="{{ $order->pod_photo_asset_url }}" alt="Bukti Serah Terima"
                                        class="h-44 w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                        onerror="this.onerror=null;this.style.opacity='0.4';" />
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-surface px-3 py-1.5 text-xs font-bold text-ink shadow-lg">
                                            <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-ink" />
                                            Buka Foto Penuh
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-ink-body">
                                    <span>Stempel basah &amp; paraf penerima</span>
                                    <a href="{{ $order->pod_photo_asset_url }}" target="_blank"
                                        class="font-semibold text-success-deep hover:underline">
                                        Buka di Tab Baru &rarr;
                                    </a>
                                </div>
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
                        @php
                            $isPayRejected = in_array($payment->status, ['DITOLAK', 'REJECTED']);
                            $isPaySuccess = in_array($payment->status, ['LUNAS', 'SUCCESS', 'PAID', 'VERIFIED']);
                        @endphp
                        <div class="flex flex-col gap-1.5 rounded-lg border {{ $isPayRejected ? 'border-danger/40 bg-danger-soft/20' : 'border-line-soft bg-surface-shell' }} p-3 text-xs">
                            <div class="flex justify-between font-bold">
                                <span>{{ $payment->payment_reference }}</span>
                                <span class="{{ $isPayRejected ? 'text-danger font-extrabold' : ($isPaySuccess ? 'text-success-deep' : 'text-warning-deep') }}">
                                    {{ $payment->status === 'DITOLAK' ? 'Ditolak Verifikator' : $payment->status }}
                                </span>
                            </div>
                            <div class="flex justify-between text-ink-body">
                                <span>Metode: {{ $payment->payment_method }}</span>
                                <span class="font-semibold text-ink">{{ $rupiah($payment->amount) }}</span>
                            </div>
                            @if ($isPayRejected && $payment->verification_note)
                                <div class="mt-1 rounded bg-danger-soft/60 p-2 text-[11px] text-danger border border-danger/30 leading-relaxed">
                                    <strong>Alasan Ditolak:</strong> {{ $payment->verification_note }}
                                </div>
                            @endif
                            @if ($payment->proof_url)
                                <a href="{{ asset('storage/' . $payment->proof_url) }}" target="_blank"
                                    class="mt-1 text-xs font-semibold {{ $isPayRejected ? 'text-danger' : 'text-brand' }} underline">
                                    Lihat Bukti Transfer
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-ink-body italic">Belum ada catatan transaksi pembayaran untuk pesanan ini.</p>
                    @endforelse

                    @if ($order->status === 'MENUNGGU_PEMBAYARAN')
                        <div class="rounded-xl border {{ $order->has_rejected_proof ? 'border-danger/40 bg-danger-soft/20' : 'border-accent/60 bg-accent/20' }} p-3 flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-ink">{{ $order->has_rejected_proof ? 'Bukti Bayar Ditolak' : 'Menunggu Pembayaran' }}</span>
                                <span class="rounded {{ $order->has_rejected_proof ? 'bg-danger text-white' : 'bg-accent text-ink' }} px-1.5 py-0.5 text-[9px] font-extrabold uppercase">
                                    {{ $order->has_rejected_proof ? 'REJECTED' : 'UPFRONT' }}
                                </span>
                            </div>
                            <p class="text-xs text-ink-body">Total Tagihan: <span class="font-extrabold text-ink">{{ $rupiah($order->grand_total) }}</span></p>
                            <a href="{{ route('klien.payment-proof', ['tagihan' => $order->invoice?->invoice_number, 'order_id' => $order->id]) }}"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg {{ $order->has_rejected_proof ? 'bg-danger text-white hover:bg-danger/90' : 'bg-accent text-ink hover:bg-accent-deep' }} px-3 py-2 text-xs font-bold shadow-sub transition-colors">
                                <x-gpa.icon name="{{ $order->has_rejected_proof ? 'upload' : 'wallet' }}" class="h-3.5 w-3.5" />
                                {{ $order->has_rejected_proof ? 'Unggah Ulang Bukti Bayar' : 'Bayar Tagihan Ini Sekarang' }}
                            </a>
                        </div>
                    @endif

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

        {{-- Modal Pratinjau Foto PoD --}}
        @if ($order->pod_photo_asset_url)
            <div x-cloak x-show="podModalOpen"
                @keydown.escape.window="podModalOpen = false"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-ink/75 backdrop-blur-xs transition-opacity"
                    @click="podModalOpen = false"></div>

                <div class="relative flex w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-surface shadow-pop ring-1 ring-ink/10"
                    x-show="podModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    <div class="flex items-center justify-between border-b border-line-hair bg-surface-shell px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent text-ink">
                                <x-gpa.icon name="check-circle" class="h-4 w-4" />
                            </span>
                            <div class="flex flex-col">
                                <h3 class="font-sans text-sm font-bold text-ink">
                                    Bukti Serah Terima Fisik (Proof of Delivery / PoD)
                                </h3>
                                <p class="text-[11px] text-ink-body">
                                    Surat Jalan: <strong class="text-ink font-mono">{{ $order->surat_jalan_number ?: '-' }}</strong> &bull; Pesanan: <strong class="text-ink font-mono">{{ $order->order_number }}</strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="podModalOpen = false"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-body transition-colors hover:bg-surface-muted hover:text-ink">
                            <x-gpa.icon name="x" class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="flex max-h-[70vh] items-center justify-center overflow-auto bg-black p-4">
                        <img src="{{ $order->pod_photo_asset_url }}" alt="Bukti Serah Terima Fisik"
                            class="max-h-[65vh] w-auto max-w-full rounded-lg object-contain shadow-2xl">
                    </div>

                    <div class="flex flex-col gap-2 border-t border-line-hair bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col text-xs text-ink-body">
                            <span>PIC Penerima Dock: <strong class="text-ink">{{ $order->received_by_name ?: 'Staf Receiving Klien' }}</strong></span>
                            <span>Waktu Serah Terima: <strong class="text-ink">{{ $order->arrival_time ? $order->arrival_time->format('d M Y, H:i') . ' WIB' : 'Telah diverifikasi' }}</strong></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ $order->pod_photo_asset_url }}" target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-line-soft bg-surface px-3 py-2 text-xs font-bold text-ink shadow-xs transition-colors hover:bg-surface-muted">
                                <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-ink-body" />
                                <span>Buka di Tab Baru</span>
                            </a>
                            <button type="button" @click="podModalOpen = false"
                                class="inline-flex items-center rounded-lg bg-ink px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-brand-deep">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (session('success') || session('info'))
        <script>
            try {
                localStorage.removeItem('gpa.draft.po');
                localStorage.setItem('gpa.cart.emptied', '1');
                localStorage.setItem('gpa.cart.submitted', '1');
                localStorage.removeItem('gpa_client_po_draft');
                localStorage.setItem('gpa_client_cart_emptied', 'true');
                window.dispatchEvent(new CustomEvent('gpa:draft-synced'));
            } catch (e) {}
        </script>
    @endif
@endsection

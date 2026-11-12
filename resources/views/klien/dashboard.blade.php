@extends('layouts.klien')

@section('title', 'Dashboard Ringkasan & Operasional Klien B2B')

@php
    $user = auth()->user();
    $contractNumber = $contract ? $contract->contract_number : ($isContractClient ? ('CTR-B2B-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)) : 'REGULER-HARIAN');
    $dockName = 'DOCK UTAMA — ' . ($user->company_name ?: ($user->name ?: 'KLIEN GPA'));
    $creditBarWidth = max(0, min(100, $creditPercent)) . '%';

    $ordersTable = [];
    if (isset($recentOrders) && $recentOrders->isNotEmpty()) {
        foreach ($recentOrders as $ord) {
            $itemNames = $ord->items->map(fn($it) => ($it->product?->name ?: 'Komoditas') . ' (' . (int) $it->ordered_qty . ' kg)')->join(', ');
            $totalQty = $ord->items->sum('ordered_qty');
            $driverName = $ord->driver?->name ?: 'Armada Logistik GPA';
            $sjNumber = $ord->surat_jalan_number ?: 'SJ-' . str_pad($ord->id, 5, '0', STR_PAD_LEFT);

            $ordersTable[] = [
                'po' => $ord->order_number,
                'date' => $ord->created_at->format('d/m/Y'),
                'commodities' => $itemNames ?: 'Sayuran & Komoditas Agro',
                'packaging' => $ord->delivery_address ?: 'Dock Logistik Mitra',
                'weight' => (int) $totalQty > 0 ? number_format($totalQty, 0, ',', '.') . ' kg' : null,
                'weight_note' => in_array($ord->status, ['SELESAI', 'SELESAI_CATATAN']) ? '(Netto Sah)' : '(Estimasi PO)',
                'scale_note' => match ($ord->status) {
                    'SELESAI', 'SELESAI_CATATAN' => 'Netto Sah Diterima',
                    'SIAP_KIRIM', 'DALAM_PENGIRIMAN' => 'Muatan Siap Kirim',
                    'LUNAS' => 'Pembayaran Terverifikasi',
                    'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                    'BATAL' => 'Pesanan Dibatalkan',
                    default => 'Menunggu Verifikasi Kuota',
                },
                'driver' => in_array($ord->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? $driverName : null,
                'sj' => in_array($ord->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? $sjNumber : null,
                'driver_note' => in_array($ord->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? ($ord->vehicle_plate_number ? "Truk Chiller • {$ord->vehicle_plate_number}" : 'Chiller Truck Ready') : 'Menunggu Alokasi Supir',
                'status' => match ($ord->status) {
                    'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                    'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                    'LUNAS' => 'Lunas Terverifikasi',
                    'SIAP_KIRIM' => 'Surat Jalan Terbit',
                    'DALAM_PENGIRIMAN' => 'Dalam Perjalanan',
                    'SELESAI' => 'Tiba di Dock',
                    'SELESAI_CATATAN' => 'Selesai (Catatan Retur)',
                    'BATAL' => 'Dibatalkan',
                    default => $ord->status,
                },
                'status_tone' => match ($ord->status) {
                    'SELESAI', 'SELESAI_CATATAN', 'DALAM_PENGIRIMAN', 'LUNAS' => 'accent',
                    'SIAP_KIRIM' => 'accent',
                    'MENUNGGU_PEMBAYARAN' => 'brand',
                    'BATAL' => 'warning',
                    default => 'warning',
                },
                'url' => route('klien.pesanan.show', $ord->id),
            ];
        }
    }

    $reorderData = $reorderData ?? [];
@endphp

@section('content')
    <div x-data="clientDashboard(@js($reorderData))" class="flex flex-col gap-5">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Kop halaman ringkasan kontrak --}}
        {{-- ---------------------------------------------------------------- --}}
        <section
            class="gpa-card flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:p-6 ring-1 ring-line-hair shadow-sub">
            <div class="min-w-0 flex flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="gpa-section-title text-ink">
                        {{ $isContractClient ? 'Dashboard Ringkasan & Operasional Klien B2B' : 'Dashboard Ringkasan & Operasional Klien Reguler' }}
                    </h1>
                    <span class="rounded-full bg-accent px-2.5 py-0.5 gpa-micro-bold text-success-ink">
                        {{ $isContractClient ? 'Kontrak Aktif' : 'Klien Reguler' }}
                    </span>
                </div>
                <dl class="flex flex-col gap-1 gpa-meta-lg text-ink-body">
                    <div class="flex flex-wrap items-center gap-x-2">
                        <dt class="text-ink-body">ID Kontrak:</dt>
                        <dd class="font-medium text-ink">{{ $contractNumber }}</dd>
                        <span class="text-ink-body" aria-hidden="true">&bull;</span>
                        <dt class="text-ink-body">Gudang Dock Utama:</dt>
                        <dd class="font-medium uppercase text-ink">{{ $dockName }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('klien.dokumen') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-surface-pill px-3.5 py-2 transition-colors hover:bg-surface-disabled">
                    <x-gpa.icon name="download" class="h-3 w-3 text-ink" />
                    <span class="font-sans text-xs font-semibold text-ink">Unduh Transaksi Bulanan</span>
                </a>
                <a href="{{ route('klien.katalog') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-ink px-4 py-2 shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                    <span class="font-sans text-xs font-bold text-white">Buat Pesanan Baru</span>
                </a>
            </div>
        </section>

        @if (!empty($rejectedOrder))
            {{-- Banner Notifikasi Pengajuan Pesanan / PO Dibatalkan Sekretariat --}}
            <section x-data="{ localDismissed: false }"
                x-show="!localDismissed && !isBannerDismissed('rejected_order_{{ $rejectedOrder->id }}')"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative rounded-2xl border-2 border-danger/60 bg-danger-soft/40 p-4 sm:p-5 shadow-card transition-all">
                
                {{-- Tombol Tutup Silang (X) di Pojok Kanan Atas --}}
                <button type="button"
                    @click="dismissBanner('rejected_order_{{ $rejectedOrder->id }}'); localDismissed = true"
                    class="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-lg text-danger/70 hover:bg-danger/15 hover:text-danger transition-colors cursor-pointer"
                    title="Tutup & jangan tampilkan lagi">
                    <x-gpa.icon name="x" class="h-4 w-4" />
                    <span class="sr-only">Tutup notifikasi</span>
                </button>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pr-8 sm:pr-0">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger text-white shadow-sub">
                            <x-gpa.icon name="x" class="h-6 w-6 text-white" />
                        </span>
                        <div class="flex flex-col gap-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded bg-danger px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                    Pengajuan Pesanan Dibatalkan
                                </span>
                                <span class="text-xs font-semibold text-danger">
                                    PO: {{ $rejectedOrder->order_number }}
                                </span>
                                @if ($rejectedOrder->verifier)
                                    <span class="text-xs text-ink-quiet">
                                        &bull; Oleh Verifikator: <strong class="text-ink">{{ $rejectedOrder->verifier->name }}</strong>
                                    </span>
                                @endif
                            </div>
                            <h2 class="font-sans text-base font-extrabold text-ink sm:text-lg">
                                Pengajuan PO Tidak Dapat Disetujui oleh Sekretariat GPA
                            </h2>
                            <p class="text-xs text-ink-body leading-relaxed max-w-2xl">
                                Alasan penolakan: <strong class="text-danger">"{{ $rejectedOrder->rejection_reason ?: 'Kapasitas armada atau kuota stok panen tidak mencukupi.' }}"</strong>.
                                Pesanan ini telah dibatalkan secara resmi oleh tim verifikasi. Silakan sesuaikan jumlah pesanan Anda pada katalog atau ajukan pesanan baru.
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <button type="button"
                            @click="dismissBanner('rejected_order_{{ $rejectedOrder->id }}'); localDismissed = true"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-line-board bg-surface px-3 py-2 font-sans text-xs font-semibold text-ink-body hover:bg-surface-muted transition cursor-pointer">
                            <span>Tutup Notifikasi</span>
                        </button>
                        <a href="{{ route('klien.pesanan.show', $rejectedOrder->id) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-line-board bg-surface px-3.5 py-2 font-sans text-xs font-bold text-ink hover:bg-surface-muted transition">
                            <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-ink-quiet" />
                            <span>Lihat Detail</span>
                        </a>
                        <a href="{{ route('klien.katalog') }}"
                            @click="dismissBanner('rejected_order_{{ $rejectedOrder->id }}')"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand px-4 py-2 font-sans text-xs font-bold text-accent shadow-sub hover:bg-brand-hover transition">
                            <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                            <span>Buat Pesanan Baru</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        @if (!empty($rejectedPayment))
            {{-- Banner Notifikasi Bukti Pembayaran Ditolak Sekretariat --}}
            <section x-data="{ localDismissed: false }"
                x-show="!localDismissed && !isBannerDismissed('rejected_payment_{{ $rejectedPayment->id }}')"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative rounded-2xl border-2 border-danger/60 bg-danger-soft/30 p-4 sm:p-5 shadow-card transition-all">
                
                {{-- Tombol Tutup Silang (X) di Pojok Kanan Atas --}}
                <button type="button"
                    @click="dismissBanner('rejected_payment_{{ $rejectedPayment->id }}'); localDismissed = true"
                    class="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-lg text-danger/70 hover:bg-danger/15 hover:text-danger transition-colors cursor-pointer"
                    title="Tutup & jangan tampilkan lagi">
                    <x-gpa.icon name="x" class="h-4 w-4" />
                    <span class="sr-only">Tutup notifikasi</span>
                </button>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pr-8 sm:pr-0">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger text-white shadow-sub">
                            <x-gpa.icon name="alert-triangle" class="h-6 w-6 text-white" />
                        </span>
                        <div class="flex flex-col gap-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded bg-danger px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                    Bukti Pembayaran Ditolak Verifikator
                                </span>
                                <span class="text-xs font-semibold text-danger">
                                    Ref: {{ $rejectedPayment->payment_reference ?: ('PAY-' . $rejectedPayment->id) }}
                                </span>
                                <span class="text-xs text-ink-quiet">
                                    &bull; Tagihan: <strong
                                        class="text-ink">{{ $rejectedPayment->invoice?->invoice_number ?: ($rejectedPayment->order?->order_number ?: 'Faktur Klien') }}</strong>
                                </span>
                            </div>
                            <h2 class="font-sans text-base font-extrabold text-ink sm:text-lg">
                                Bukti Pembayaran Belum Dapat Disahkan oleh Sekretariat GPA
                            </h2>
                            <p class="text-xs text-ink-body leading-relaxed max-w-2xl">
                                Alasan penolakan: <strong
                                    class="text-danger">"{{ $rejectedPayment->verification_note ?: 'Bukti transfer tidak valid atau dana belum masuk rekening koran.' }}"</strong>.
                                Silakan konfirmasi ulang transfer Anda dan unggah bukti baru agar pesanan segera diproses.
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <button type="button"
                            @click="dismissBanner('rejected_payment_{{ $rejectedPayment->id }}'); localDismissed = true"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-line-board bg-surface px-3 py-2 font-sans text-xs font-semibold text-ink-body hover:bg-surface-muted transition cursor-pointer">
                            <span>Tutup Notifikasi</span>
                        </button>
                        <a href="{{ route('klien.bukti-pembayaran', ['tagihan' => $rejectedPayment->invoice?->invoice_number, 'order_id' => $rejectedPayment->order_id]) }}"
                            @click="dismissBanner('rejected_payment_{{ $rejectedPayment->id }}')"
                            class="inline-flex items-center gap-2 rounded-xl bg-danger px-4 py-2.5 font-sans text-xs font-bold text-white shadow-lg transition-transform hover:scale-105 hover:bg-danger/90">
                            <x-gpa.icon name="upload" class="h-4 w-4 text-white" />
                            <span>Unggah Ulang Bukti Bayar</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        @if (!$isContractClient)
            {{-- ---------------------------------------------------------------- --}}
            {{-- Banner Notifikasi Khusus Klien Reguler: Ajukan Kontrak B2B / Status Pengajuan --}}
            {{-- ---------------------------------------------------------------- --}}
            @if ($pendingContract)
                {{-- Banner Status Pengajuan Sedang Ditinjau --}}
                <section
                    class="relative overflow-hidden rounded-2xl border border-warning/40 bg-gradient-to-r from-warning/15 via-warning/5 to-surface p-5 lg:p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3.5">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning/20 text-warning-deep">
                                <x-gpa.icon name="clock" class="h-5 w-5 animate-pulse" />
                            </span>
                            <div class="flex flex-col gap-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-warning/30 px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-warning-deep">
                                        Menunggu Pengesahan Direktur
                                    </span>
                                    <span class="font-mono text-xs font-semibold text-ink">No. PKS:
                                        {{ $pendingContract->contract_number }}</span>
                                </div>
                                <h2 class="text-base font-bold text-ink">Pengajuan Kontrak Kemitraan B2B Sedang Ditinjau Direktur Utama</h2>
                                <p class="max-w-3xl text-xs leading-relaxed text-ink-body">
                                    Proposal kemitraan untuk komoditas <strong>{{ $pendingContract->product?->name }}</strong>
                                    (Komitmen <strong>{{ number_format($pendingContract->committed_volume_per_cycle, 0, ',', '.') }}
                                        kg / siklus</strong>, Termin Pembayaran <strong>TOP {{ $pendingContract->top_days }}
                                        Hari</strong>) telah terkirim pada {{ $pendingContract->created_at->format('d M Y') }} dan
                                    sedang dalam tahap evaluasi Direktur Utama GPA. Setelah disetujui, akun Anda otomatis
                                    ditingkatkan menjadi Klien Kontrak B2B.
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="https://wa.me/6281112233445?text=Halo%20Direksi%20GPA,%20saya%20telah%20mengajukan%20kontrak%20B2B%20nomor%20{{ $pendingContract->contract_number }}"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-surface-pill px-4 py-2.5 font-sans text-xs font-bold text-ink outline outline-1 outline-line-hair transition-all hover:bg-surface-disabled">
                                <x-gpa.icon name="message-square" class="h-4 w-4 text-success-deep" />
                                <span>Konfirmasi via WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </section>
            @elseif ($rejectedContract)
                {{-- Banner Pemberitahuan Penolakan Pengajuan Kontrak B2B --}}
                <section x-data="{ localDismissed: false }"
                    x-show="!localDismissed && !isBannerDismissed('rejected_contract_{{ $rejectedContract['contract_number'] ?? 'b2b' }}')"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative overflow-hidden rounded-2xl border border-danger/40 bg-gradient-to-r from-danger/15 via-danger/5 to-surface p-5 lg:p-6 shadow-sm">
                    
                    {{-- Tombol Tutup Silang (X) di Pojok Kanan Atas --}}
                    <button type="button"
                        @click="dismissBanner('rejected_contract_{{ $rejectedContract['contract_number'] ?? 'b2b' }}'); localDismissed = true"
                        class="absolute top-3 right-3 flex h-7 w-7 items-center justify-center rounded-lg text-danger/70 hover:bg-danger/15 hover:text-danger transition-colors cursor-pointer"
                        title="Tutup & jangan tampilkan lagi">
                        <x-gpa.icon name="x" class="h-4 w-4" />
                        <span class="sr-only">Tutup notifikasi</span>
                    </button>

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between pr-8 lg:pr-0">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/20 text-danger">
                                <x-gpa.icon name="alert-triangle" class="h-5 w-5" />
                            </span>
                            <div class="flex flex-col gap-1.5 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-danger/20 border border-danger/30 px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-danger">
                                        Pengajuan Kontrak Ditolak
                                    </span>
                                    <span class="font-mono text-xs font-semibold text-ink">
                                        No. PKS: {{ $rejectedContract['contract_number'] ?? '-' }}
                                    </span>
                                    @if (!empty($rejectedContract['rejected_at']))
                                        <span class="text-[11px] text-ink-quiet font-mono">
                                            &bull; {{ $rejectedContract['rejected_at'] }}
                                        </span>
                                    @endif
                                </div>
                                <h2 class="text-base font-bold text-ink">
                                    Pengajuan Kontrak Kemitraan {{ $rejectedContract['product_name'] ?? 'Komoditas' }} Tidak Disetujui
                                </h2>
                                <p class="max-w-3xl text-xs leading-relaxed text-ink-body">
                                    Proposal kemitraan B2B yang Anda ajukan belum dapat disahkan oleh Direksi GPA pada tahap ini. Anda dapat mempelajari catatan penolakan di bawah untuk mengajukan revisi proposal baru atau berkonsultasi via WhatsApp.
                                </p>

                                {{-- Kotak Alasan Penolakan dari Direktur --}}
                                <div class="mt-1 rounded-xl border border-danger/20 bg-surface p-3.5 shadow-2xs">
                                    <div class="flex items-start gap-2.5">
                                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-danger/10 text-danger mt-0.5">
                                            <x-gpa.icon name="x" class="h-3 w-3" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-mono text-[11px] font-bold uppercase tracking-wider text-danger">
                                                Alasan Penolakan dari {{ $rejectedContract['rejected_by'] ?? 'Direktur Utama GPA' }}:
                                            </p>
                                            <p class="mt-1 text-xs font-medium leading-relaxed text-ink italic">
                                                "{{ $rejectedContract['rejection_reason'] ?? 'Margin usulan harga atau komitmen volume belum memenuhi batas kelayakan.' }}"
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2 pt-1 lg:pt-0">
                            <button type="button"
                                @click="dismissBanner('rejected_contract_{{ $rejectedContract['contract_number'] ?? 'b2b' }}'); localDismissed = true"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-line-board bg-surface px-3 py-2 font-sans text-xs font-semibold text-ink-body hover:bg-surface-muted transition cursor-pointer">
                                <span>Tutup Notifikasi</span>
                            </button>
                            <button type="button" @click="dismissBanner('rejected_contract_{{ $rejectedContract['contract_number'] ?? 'b2b' }}'); showContractModal = true"
                                class="inline-flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 font-sans text-xs font-bold text-accent shadow-sm transition hover:bg-brand-deep">
                                <x-gpa.icon name="plus" class="h-4 w-4 text-accent" />
                                <span>Ajukan Proposal Baru</span>
                            </button>
                            <a href="https://wa.me/6281112233445?text={{ rawurlencode('Halo Tim Kemitraan GPA, saya ingin berkonsultasi mengenai penolakan proposal kontrak PKS ' . ($rejectedContract['contract_number'] ?? '') . ': ' . ($rejectedContract['rejection_reason'] ?? '')) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-xl bg-surface-pill px-4 py-2.5 font-sans text-xs font-bold text-emerald-800 border border-emerald-200 transition hover:bg-emerald-50">
                                <x-gpa.icon name="whatsapp" class="h-4 w-4 text-emerald-700" />
                                <span>Konsultasi WA</span>
                            </a>
                        </div>
                    </div>
                </section>
            @else
                {{-- Banner Penawaran Kemitraan Kontrak B2B --}}
                <section
                    class="relative overflow-hidden rounded-2xl border border-brand/30 bg-gradient-to-r from-brand-deep via-brand/90 to-[#1b4306] p-5 lg:p-6 text-white shadow-card">
                    <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-accent/10 blur-2xl"></div>
                    <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3.5">
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-accent outline outline-1 outline-white/20">
                                <x-gpa.icon name="file-text" class="h-6 w-6" />
                            </span>
                            <div class="flex flex-col gap-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-accent/20 px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-accent outline outline-1 outline-accent/30">
                                        Peluang Kemitraan Kontrak B2B
                                    </span>
                                </div>
                                <h2 class="text-base font-bold text-white lg:text-lg">Tingkatkan Akun ke Kemitraan B2B Kontrak Resmi
                                </h2>
                                <p class="max-w-3xl text-xs leading-relaxed text-white/90">
                                    Dapatkan fasilitas kemitraan prioritas: <strong>Harga Komoditas Terkunci (Bebas
                                        Fluktuasi)</strong>, <strong>Term of Payment (Tempo 14–30 Hari)</strong>, serta jaminan
                                    alokasi pasokan harian langsung dari kebun binaan GPA. Pengajuan akan ditinjau dan disahkan
                                    langsung oleh <strong>Direktur Utama GPA</strong>.
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="button" @click="showContractModal = true"
                                class="inline-flex items-center gap-2 rounded-xl bg-accent px-4 py-2.5 font-sans text-xs font-bold text-ink shadow-lg transition-transform hover:scale-[1.02] active:scale-[0.98]">
                                <x-gpa.icon name="plus" class="h-4 w-4" />
                                <span>Ajukan Kontrak Kemitraan B2B</span>
                            </button>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            <article class="gpa-card flex flex-col justify-between gap-3 p-3 sm:gap-4 sm:p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-1.5">
                        <h2 class="gpa-micro text-ink-body line-clamp-2 leading-tight">
                            {{ $isContractClient ? 'Plafon Kredit & Tempo (Top 30D)' : 'Skema Pemesanan' }}
                        </h2>
                        <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    @if ($isContractClient)
                        <p class="gpa-figure pt-1 text-ink text-base sm:text-[1.75rem] leading-tight">Rp
                            {{ number_format($availableCredit, 0, ',', '.') }}</p>
                        <p class="gpa-body text-ink-body text-xs sm:text-sm leading-tight">
                            Sisa kuota dari total
                            <span class="font-medium text-ink">Rp {{ number_format($creditLimit, 0, ',', '.') }}</span>
                        </p>
                    @else
                        <p class="gpa-figure pt-1 text-ink text-base sm:text-[1.75rem] leading-tight">Klien Reguler</p>
                        <p class="gpa-body text-ink-body text-xs sm:text-sm leading-tight">
                            Transfer Bank sebelum pengiriman (Non-Kontrak)
                        </p>
                    @endif
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-3 sm:gap-4 sm:p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-1.5">
                        <h2 class="gpa-micro text-ink-body line-clamp-2 leading-tight">Pesanan In-Flight Pipeline</h2>
                        <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-0.5 text-ink text-base sm:text-[1.75rem] leading-tight">{{ $pipelineCount }}
                        Pesanan Aktif</p>
                    <p class="gpa-meta-lg font-bold text-success-deep text-xs sm:text-sm leading-tight">Valuasi: Rp
                        {{ number_format($pipelineValue, 0, ',', '.') }}</p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-3 sm:gap-4 sm:p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-1.5">
                        <h2 class="gpa-micro text-ink-body line-clamp-2 leading-tight">
                            {{ $isContractClient ? 'Serapan Kuota Panen (Bulan Ini)' : 'Total Serapan Panen (Bulan Ini)' }}
                        </h2>
                        <x-gpa.icon name="leaf" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="flex flex-wrap items-baseline gap-1 text-ink">
                        <span
                            class="gpa-figure text-base sm:text-[1.75rem] leading-tight">{{ number_format($totalKgHarvest, 0, ',', '.') }}
                            kg</span>
                        @if ($targetKg > 0)
                            <span class="font-inter text-xs sm:text-sm text-ink-body">/
                                {{ number_format($targetKg, 0, ',', '.') }} kg</span>
                        @endif
                    </p>
                    <p class="gpa-body font-bold text-success-deep text-xs sm:text-sm leading-tight">
                        @if ($targetKg > 0)
                            {{ number_format($harvestPercent, 1, ',', '') }}% kuota alokasi bulanan terpenuhi
                        @else
                            Total pembelian komoditas bulan ini
                        @endif
                    </p>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-3 sm:gap-4 sm:p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-1.5">
                        <h2 class="gpa-micro text-ink-body line-clamp-2 leading-tight">Kepatuhan SLA &amp; Mutu Cold-Chain
                        </h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-success-deep text-base sm:text-[1.75rem] leading-tight">100%</p>
                    <p class="gpa-body text-ink-body text-xs sm:text-sm leading-tight">On-Time In-Full Delivery</p>
                </div>
            </article>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pemberitahuan penagihan (hanya tampil jika ada tagihan aktif) --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($latestInvoice)
            <section id="dokumen"
                class="flex flex-col gap-4 rounded-r-xl border-l-4 border-warning bg-warning-wash p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-0.5 h-[18px] w-1 shrink-0 bg-warning" aria-hidden="true"></span>
                    <div class="flex min-w-0 flex-col gap-0.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold leading-5 text-warning-deep">
                                Tagihan {{ $latestInvoice->invoice_number }} Menunggu Pembayaran
                            </h2>
                            <span class="rounded bg-warning px-2 py-0.5 gpa-micro-bold text-warning-soft">
                                {{ $latestInvoice->due_date ? 'Jatuh Tempo: ' . $latestInvoice->due_date->format('d M Y') : 'Jatuh Tempo: TOP 30 Hari' }}
                            </span>
                        </div>
                        <p class="gpa-body text-ink-body">
                            Total kewajiban tempo: Rp {{ number_format((float) $latestInvoice->grand_total, 0, ',', '.') }}
                            dengan netto timbangan sah.
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('klien.dokumen') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-1.5 transition-colors hover:bg-surface-disabled hover:text-ink">
                        <x-gpa.icon name="invoice" class="h-2.5 w-2.5" />
                        <span class="font-sans text-xs font-semibold">Unduh Faktur PDF</span>
                    </a>
                    <a href="{{ route('klien.bukti-pembayaran') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-3.5 py-1.5 shadow-sub transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="upload" class="h-3 w-3.5 text-white" />
                        <span class="font-sans text-xs font-bold text-white">Unggah Bukti Transfer</span>
                    </a>
                </div>
            </section>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Live tracking --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($inTransitOrder)
            <section class="gpa-card flex flex-col gap-6 p-5 lg:p-6">
                <div class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <h2 class="gpa-section-title text-ink">
                                Live Pelacakan Armada Pengiriman:
                                {{ $inTransitOrder->surat_jalan_number ?: 'SJ-' . str_pad($inTransitOrder->id, 5, '0', STR_PAD_LEFT) }}
                            </h2>
                        </div>
                        <p class="gpa-body text-ink-body">
                            Chiller Truck {{ $inTransitOrder->vehicle_plate_number ?: 'GPA Cold-Chain' }} bergerak menuju
                            fasilitas cold-storage Anda.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div
                            class="inline-flex items-center gap-2 rounded-lg bg-brand px-3 py-1.5 ring-1 ring-inset ring-accent/30">
                            <x-gpa.icon name="thermometer" class="h-3.5 w-3.5 text-accent" />
                            <div class="flex flex-col">
                                <span class="gpa-meta-lg text-accent">Telemetri Chiller:</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-xs font-bold leading-4 text-accent">+4.2°C</span>
                                    <span class="gpa-meta-lg font-bold text-accent">[OPTIMAL]</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col rounded-lg bg-surface-pill px-3 py-1.5">
                            <span class="gpa-meta-lg text-ink">Supir:</span>
                            <span
                                class="text-xs font-bold leading-4 text-ink">{{ $inTransitOrder->driver?->name ?: 'Supir Logistik GPA' }}</span>
                        </div>
                    </div>
                </div>

                <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-white">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-ink" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                            Fase 1 &bull; Selesai
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Pengajuan Draf PO</p>
                        <p class="gpa-body text-ink-body">Draf kebutuhan komoditas diajukan mandiri dari katalog web.</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-white">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-ink" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                            Fase 2 &bull; Selesai
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Verifikasi &amp; Kunci Kuota</p>
                        <p class="gpa-body text-ink-body">Disetujui oleh Sekretariat GPA &amp; alokasi kuota panen dikunci.</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-white">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-ink" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                            Fase 3 &bull; Selesai
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Surat Jalan &amp; Armada</p>
                        <p class="gpa-body text-ink-body">Surat Jalan resmi diterbitkan &amp; armada chiller siap berangkat.</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-accent-edge bg-surface-shell p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent text-ink ring-4 ring-brand/20">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-success-deep" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-ink">
                            <span class="h-1.5 w-1.5 rounded-full bg-ink" aria-hidden="true"></span>
                            Fase 4 &bull; Dalam Perjalanan
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Armada On Road</p>
                        <p class="gpa-body font-semibold text-ink">Suhu muatan terpantau stabil, armada menuju receiving dock.
                        </p>
                        <p class="pt-1 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-success-deep">Sedang
                            Berlangsung</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3 opacity-60">
                        <div class="flex items-center gap-2 pb-2">
                            <span
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-surface-disabled text-ink-quiet ring-0">
                                <span class="gpa-meta-lg">5</span>
                            </span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-ink-body">
                            Fase 5 &bull; Menunggu Tiba
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink-quiet">Serah Terima &amp; Bukti PoD</p>
                        <p class="gpa-body text-ink-body">Pemeriksaan fisik komoditas di receiving dock, foto bukti PoD, dan pengesahan surat jalan.
                        </p>
                    </li>
                </ol>
            </section>
        @else
            <section class="gpa-card flex flex-col gap-4 p-5 lg:p-6">
                <div class="flex items-center justify-between border-b border-line-soft pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-ink-quiet" aria-hidden="true"></span>
                        <h2 class="gpa-section-title text-ink">Live Pelacakan Armada Pengiriman</h2>
                    </div>
                    <span class="text-xs text-ink-quiet font-medium">Status Armada: Standby</span>
                </div>
                <div class="py-6 text-center text-sm text-ink-body">
                    <x-gpa.icon name="truck" class="mx-auto h-8 w-8 text-ink-quiet mb-2" />
                    <p class="font-medium text-ink">Tidak ada armada pengiriman yang sedang aktif menuju dock Anda saat ini.</p>
                    <p class="text-xs text-ink-body mt-1">Status pelacakan real-time akan aktif otomatis ketika armada logistik
                        diberangkatkan dari Sentra GPA.</p>
                </div>
            </section>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Tabel pesanan berjalan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="pesanan-berjalan" class="gpa-card overflow-hidden">
            <div
                class="flex flex-col gap-3 border-b border-line-soft p-5 lg:flex-row lg:items-center lg:justify-between lg:p-6">
                <div class="flex min-w-0 flex-col gap-1">
                    <h2 class="gpa-section-title text-ink">Daftar Pesanan Berjalan</h2>
                    <p class="gpa-body text-ink-body">
                        Monitoring status pemenuhan komoditas secara real-time dari ladang panen ke dock Anda.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="filter-status" class="gpa-micro text-ink-body">Filter Status:</label>
                    <select id="filter-status" x-model="statusFilter"
                        class="gpa-control h-auto w-auto min-w-[13rem] rounded-lg border-line-soft bg-canvas py-1.5 pr-8 text-xs">
                        <template x-for="option in statusOptions(@js($ordersTable))" :key="option.value">
                            <option :value="option.value" x-text="option.label"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="hidden md:block gpa-scroll-x">
                <table class="w-full border-collapse">
                    <caption class="sr-only">Daftar pesanan berjalan beserta status pemenuhan komoditas</caption>
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-shell">
                            <th scope="col" class="px-4 py-3.5 text-left gpa-micro-bold text-ink-body">No PO / Order</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Tanggal PO</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Rincian Komoditas &amp;
                                Estimasi</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Berat Riil</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Supir &amp; No SJ</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Status Pesanan</th>
                            <th scope="col" class="px-4 py-3 text-right gpa-micro-bold text-ink-body">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in filteredOrders(@js($ordersTable))" :key="order.po">
                            <tr class="border-b border-line-soft/60 align-top">
                                <td class="px-4 py-4 text-sm font-bold leading-5 text-ink">
                                    <span x-text="order.po"></span>
                                </td>
                                <td class="px-4 py-4 gpa-meta text-ink-body"><span x-text="order.date"></span></td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm font-medium leading-5 text-ink"><span
                                            x-text="order.commodities"></span></p>
                                    <p class="gpa-body text-ink-body"><span x-text="order.packaging"></span></p>
                                </td>
                                <td class="px-4 py-4">
                                    <template x-if="order.weight">
                                        <div class="flex flex-col">
                                            <p class="text-sm font-bold leading-5 text-ink">
                                                <span x-text="order.weight"></span>
                                                <template x-if="order.weight_note">
                                                    <span class="font-normal text-ink-body"
                                                        x-text="order.weight_note"></span>
                                                </template>
                                            </p>
                                            <p class="gpa-micro text-success-deep"><span x-text="order.scale_note"></span>
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="! order.weight && order.weight_note">
                                        <span class="rounded bg-surface-pill px-2 py-0.5 gpa-micro text-ink-body"
                                            x-text="order.weight_note"></span>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5">
                                    <template x-if="order.driver">
                                        <div class="flex flex-col">
                                            <p class="gpa-body font-medium text-ink"><span x-text="order.driver"></span></p>
                                            <template x-if="order.sj">
                                                <p
                                                    class="text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                                    <span x-text="order.sj"></span>
                                                </p>
                                            </template>
                                            <template x-if="order.driver_note">
                                                <p class="gpa-micro font-semibold uppercase leading-3 text-ink-quiet">
                                                    <span x-text="order.driver_note"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="! order.driver">
                                        <p class="gpa-body italic text-ink-body"><span x-text="order.driver_note"></span>
                                        </p>
                                    </template>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 gpa-micro-bold"
                                        :class="{
                                                'bg-accent text-ink ring-1 ring-inset ring-accent-edge': order.status_tone === 'accent',
                                                'bg-surface-pill text-ink-body ring-1 ring-inset ring-line-board': order.status_tone === 'neutral',
                                                'bg-warning-soft text-warning-deep ring-1 ring-inset ring-warning/40': order.status_tone === 'warning',
                                            }">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="{
                                                    'bg-ink': order.status_tone === 'accent',
                                                    'bg-ink-body': order.status_tone === 'neutral',
                                                    'bg-warning-deep': order.status_tone === 'warning',
                                                }"></span>
                                        <span x-text="order.status"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col items-end gap-1">
                                        <a :href="order.url || '{{ route('klien.pesanan.index') }}'"
                                            class="rounded bg-ink px-2.5 py-1 gpa-micro-bold text-white transition-colors hover:bg-brand-deep">
                                            Detail PO
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden flex flex-col gap-2 p-2">
                <template x-for="order in filteredOrders(@js($ordersTable))" :key="order.po">
                    <article class="flex flex-col gap-2.5 rounded-lg border border-line-hair bg-surface p-3 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <span class="min-w-0 break-words font-mono text-xs font-semibold text-ink"
                                x-text="order.po"></span>
                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-1 gpa-micro-bold"
                                :class="{
                                    'bg-accent text-ink ring-1 ring-inset ring-accent-edge': order.status_tone === 'accent',
                                    'bg-surface-pill text-ink-body ring-1 ring-inset ring-line-board': order.status_tone === 'neutral',
                                    'bg-warning-soft text-warning-deep ring-1 ring-inset ring-warning/40': order.status_tone === 'warning',
                                    'bg-brand text-accent ring-1 ring-inset ring-brand': order.status_tone === 'brand',
                                }">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="{
                                    'bg-ink': order.status_tone === 'accent',
                                    'bg-ink-body': order.status_tone === 'neutral',
                                    'bg-warning-deep': order.status_tone === 'warning',
                                    'bg-accent': order.status_tone === 'brand',
                                }"></span>
                                <span x-text="order.status"></span>
                            </span>
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-semibold leading-4 text-ink" x-text="order.commodities"></p>
                            <p class="mt-0.5 gpa-micro text-ink-body" x-text="order.packaging"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-x-3 gap-y-2 border-t border-line-faint pt-2 text-[10px]">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-ink-quiet">Tanggal PO</span>
                                <span class="font-medium text-ink" x-text="order.date"></span>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <span class="text-ink-quiet">Berat Riil</span>
                                <template x-if="order.weight">
                                    <span class="font-semibold text-ink">
                                        <span x-text="order.weight"></span>
                                        <span class="font-normal text-ink-body" x-text="order.weight_note"></span>
                                    </span>
                                </template>
                                <template x-if="! order.weight && order.weight_note">
                                    <span class="text-ink-body" x-text="order.weight_note"></span>
                                </template>
                                <span class="gpa-micro text-success-deep" x-text="order.scale_note"></span>
                            </div>
                            <div class="col-span-2 flex flex-col gap-0.5">
                                <span class="text-ink-quiet">Supir &amp; No. SJ</span>
                                <template x-if="order.driver">
                                    <span class="font-medium text-ink">
                                        <span x-text="order.driver"></span>
                                        <span class="font-normal text-ink-body" x-text="order.driver_note"></span>
                                    </span>
                                </template>
                                <template x-if="! order.driver">
                                    <span class="italic text-ink-body" x-text="order.driver_note"></span>
                                </template>
                                <span x-show="order.sj" class="gpa-micro font-semibold uppercase text-ink-body"
                                    x-text="order.sj"></span>
                            </div>
                        </div>

                        <div class="flex justify-end border-t border-line-faint pt-2">
                            <a :href="order.url || '{{ route('klien.pesanan.index') }}'"
                                class="inline-flex items-center justify-center rounded bg-ink px-3 py-1.5 gpa-micro-bold text-white transition-colors hover:bg-brand-deep">
                                Detail PO
                            </a>
                        </div>
                    </article>
                </template>
            </div>

            <p x-cloak x-show="filteredOrders(@js($ordersTable)).length === 0"
                class="px-6 py-10 text-center gpa-body text-ink-body">
                Tidak ada pesanan berjalan saat ini.
            </p>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Quick reorder --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="quick-reorder" class="gpa-card flex flex-col gap-4 p-4 sm:p-5 lg:p-6">
            <div class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0 flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <h2 class="gpa-section-title text-ink">
                            {{ $isContractClient ? 'Pesan Cepat Kuota Harian' : 'Pesan Cepat Komoditas' }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 grid-cols-2">
                @forelse ($reorderData as $item)
                    <article
                        class="flex flex-col justify-between gap-3 sm:gap-4 rounded-xl border border-line-soft bg-surface overflow-hidden">
                        <div class="relative h-28 sm:h-32 w-full bg-surface-muted">
                            <img src="{{ $item['image_url'] ?? asset('gpaleaves.png') }}" alt="{{ $item['name'] }}"
                                class="h-full w-full object-cover" onerror="this.src='{{ asset('gpaleaves.png') }}'">
                        </div>
                        <div class="flex flex-col gap-1 px-3 sm:px-4">
                            <div class="flex items-center justify-between gap-2">
                                <p class="gpa-micro-bold text-success-deep">{{ $item['grade'] }}</p>
                                <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-ink-quiet" />
                            </div>
                            <h3 class="text-sm sm:text-base font-bold leading-5 sm:leading-6 text-ink line-clamp-2">
                                {{ $item['name'] }}</h3>
                            <p class="flex items-baseline gap-1">
                                <span class="font-inter text-base sm:text-lg font-bold leading-5 sm:leading-6 text-ink">Rp
                                    {{ number_format($item['price'], 0, ',', '.') }}</span>
                                <span class="font-inter text-xs text-ink-body">/ kg</span>
                            </p>
                            <p class="hidden sm:flex items-baseline gap-1 gpa-micro text-ink-body">
                                @if ($isContractClient && !empty($item['is_contract']))
                                    <span>Sisa Alokasi Kontrak:</span>
                                    <span class="font-bold text-ink">{{ number_format($item['remaining'], 0, ',', '.') }} kg</span>
                                @else
                                    <span>Min. Pembelian:</span>
                                    <span class="font-bold text-ink">{{ number_format($item['minimum_order'], 0, ',', '.') }} kg</span>
                                    <span class="font-normal text-ink-quiet">&bull; Stok Siap Kirim</span>
                                @endif
                            </p>
                        </div>

                        <div class="border-t border-line-soft/60 px-3 sm:px-4 pb-3 sm:pb-4 pt-3">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="hidden sm:inline gpa-micro text-ink-body">Jumlah (Kg):</span>
                                    <div
                                        class="flex items-center overflow-hidden rounded bg-canvas ring-1 ring-inset ring-line-board">
                                        <button type="button" @click="step(@js($item), -1)"
                                            aria-label="Kurangi {{ $item['name'] }}"
                                            class="px-2 py-0.5 font-mono text-sm leading-5 text-ink transition-colors hover:bg-surface-muted">
                                            &minus;
                                        </button>
                                        <input type="number" inputmode="numeric" min="{{ $item['initial_qty'] }}" max="{{ $item['remaining'] }}"
                                            step="{{ $item['step'] }}" value="{{ $item['initial_qty'] }}"
                                            x-model.number="quantities['{{ $item['key'] }}']"
                                            @change="setQuantity(@js($item), $event.target.value)"
                                            aria-label="Jumlah kilogram {{ $item['name'] }}"
                                            class="w-10 sm:w-12 border-x border-line-soft bg-transparent py-0.5 text-center font-mono text-[11px] font-bold leading-[14px] text-ink focus:outline-none focus:ring-1 focus:ring-inset focus:ring-brand [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                                        <button type="button" @click="step(@js($item), 1)"
                                            aria-label="Tambah {{ $item['name'] }}"
                                            class="px-2 py-0.5 font-mono text-sm leading-5 text-ink transition-colors hover:bg-surface-muted">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <button type="button" @click="addToDraft(@js($item))"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-ink px-2 sm:px-3 py-2 transition-colors hover:bg-brand-deep">
                                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                                    <span class="gpa-meta-lg font-bold text-white">Tambah</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-ink-body italic">
                        Belum ada komoditas aktif untuk quick reorder saat ini.
                    </div>
                @endforelse
            </div>

            {{-- Ringkasan draft PO --}}
            <div x-cloak x-show="draft.length > 0"
                class="hidden lg:flex flex-col gap-3 rounded-xl border border-accent-edge bg-surface-shell p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h3 class="gpa-label text-ink">Draft PO Berjalan (<span x-text="draftCount()">0</span> item)</h3>
                    <ul class="mt-1.5 flex flex-col gap-0.5">
                        <template x-for="line in draft" :key="line.key">
                            <li class="flex items-center gap-2 gpa-body text-ink-body">
                                <span class="font-medium text-ink" x-text="line.name"></span>
                                <span>&bull;</span>
                                <span x-text="line.qty + ' kg'"></span>
                                <span>&bull;</span>
                                <span class="font-medium text-ink" x-text="money(line.total)"></span>
                                <button type="button" @click="removeFromDraft(line.key)"
                                    class="ml-1 inline-flex items-center gap-1 text-ink-quiet transition-colors hover:text-danger"
                                    :aria-label="'Hapus ' + line.name + ' dari draft'">
                                    <x-gpa.icon name="x" class="h-3 w-3" />
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="flex shrink-0 flex-col items-start gap-2 lg:items-end">
                    <p class="gpa-meta-lg text-ink-body">
                        Total <span class="font-bold text-ink" x-text="draftWeight() + ' kg'"></span>
                        &bull;
                        <span class="font-bold text-success-deep" x-text="money(draftTotal())"></span>
                    </p>
                    <a href="{{ route('klien.keranjang') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="send" class="h-3.5 w-3.5 text-white" />
                        <span class="gpa-meta-lg font-bold text-white">Buka Keranjang PO &amp; Checkout</span>
                    </a>
                </div>
            </div>

            {{-- Draft PO Mobile Floating Bar + Bottom Sheet --}}
            <div x-show="draft.length > 0" x-cloak x-data="{ mobileCartOpen: false }" class="lg:hidden pb-24">
                <div class="fixed inset-x-0 bottom-0 z-40 px-3 pb-[calc(env(safe-area-inset-bottom,0)+0.75rem)]"
                    x-show="draft.length > 0 && !mobileCartOpen" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-y-0 opacity-100"
                    x-transition:leave-end="translate-y-full opacity-0">
                    <button type="button" @click="mobileCartOpen = true"
                        class="mx-auto flex w-full max-w-md items-center justify-between gap-3 rounded-full border border-brand-strong/20 bg-brand-deep px-4 py-2.5 text-white shadow-pop">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="cart" class="h-4 w-4" />
                            <span class="text-xs font-semibold">
                                <span x-text="draftCount()"></span> Item • <span x-text="draftWeight()"></span> kg • <span
                                    x-text="money(draftTotal())"></span>
                            </span>
                        </div>
                        <span class="flex items-center gap-1 text-xs font-bold">
                            Lihat Draft PO
                            <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.5 2.25L8.25 6L4.5 9.75" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </button>
                </div>

                <div x-show="mobileCartOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
                    <div class="absolute inset-0 bg-black/60 transition-opacity duration-300" x-show="mobileCartOpen"
                        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        @click="mobileCartOpen = false"></div>
                    <div class="absolute inset-x-0 bottom-0 flex max-h-[90vh] flex-col rounded-t-2xl bg-surface shadow-2xl will-change-transform"
                        x-show="mobileCartOpen" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full">
                        <div class="flex items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                            <div class="flex items-center gap-2">
                                <x-gpa.icon name="cart" class="h-4 w-4 text-brand" />
                                <h3 class="gpa-section-title text-ink">Draft PO</h3>
                                <span class="rounded-full bg-accent/40 px-2 py-0.5 gpa-micro-bold text-brand">
                                    <span x-text="draftCount()"></span> Item
                                </span>
                            </div>
                            <button type="button" @click="mobileCartOpen = false"
                                class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-surface-shell">
                                <x-gpa.icon name="x" class="h-4 w-4" />
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto px-4 py-3">
                            <ul class="space-y-2">
                                <template x-for="line in draft" :key="line.key">
                                    <li class="rounded-lg border border-line-hair bg-white p-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-ink" x-text="line.name"></p>
                                            <p class="text-sm font-bold text-success-deep" x-text="money(line.total)"></p>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <span class="gpa-micro text-ink-body"><span x-text="line.qty"></span>
                                                    kg</span>
                                            </div>
                                            <button type="button"
                                                @click="removeFromDraft(line.key); if(draft.length===0) mobileCartOpen=false"
                                                class="gpa-micro-bold text-danger">Hapus</button>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div class="border-t border-line-hair px-4 py-3 pb-[calc(env(safe-area-inset-bottom,0)+0.75rem)]">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="gpa-body text-ink-body">Total (<span x-text="draftWeight()"></span> kg)</span>
                                <span class="text-base font-extrabold text-success-deep"
                                    x-text="money(draftTotal())"></span>
                            </div>
                            <a href="{{ route('klien.keranjang') }}" @click="mobileCartOpen = false"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-4 py-2.5 text-sm font-bold text-white">
                                Lanjut ke Keranjang PO
                                <x-gpa.icon name="arrow-right" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Modal Pengajuan Kontrak Kemitraan B2B (Khusus Klien Reguler) --}}
        {{-- ---------------------------------------------------------------- --}}
        @if (!$isContractClient && !$pendingContract)
            <div x-cloak x-show="showContractModal"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-3 sm:p-6 overscroll-contain"
                role="dialog" aria-modal="true"
                @keydown.escape.window="showContractModal = false"
                x-effect="
                    document.body.classList.toggle('overflow-hidden', showContractModal);
                    document.documentElement.classList.toggle('overflow-hidden', showContractModal);
                ">

                {{-- Backdrop Gelap Berfokus Tinggi (Zero-Lag 60fps dengan deep dark overlay & scroll lock) --}}
                <div class="fixed inset-0 bg-black/80 transition-opacity"
                    x-show="showContractModal"
                    x-transition:enter="ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showContractModal = false"
                    @wheel.prevent
                    @touchmove.prevent></div>

                {{-- Kontainer Kartu Modal Berfokus Tajam --}}
                <div class="relative my-auto flex max-h-[92vh] w-full max-w-2xl transform-gpu flex-col rounded-2xl bg-surface p-5 sm:p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.85)] ring-1 ring-white/10 border border-line-board/80 overscroll-contain"
                    x-show="showContractModal"
                    x-transition:enter="ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-98"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-98">

                    <header class="flex shrink-0 items-start justify-between border-b border-line-hair pb-3.5">
                        <div class="flex flex-col gap-1">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-success-deep">
                                FORMULIR PERJANJIAN KERJA SAMA (PKS) B2B &bull; RESMI GPA
                            </span>
                            <h3 class="text-lg font-bold text-ink">Pengajuan Kontrak Kemitraan B2B &amp; Plafon Tempo</h3>
                            <p class="text-xs text-ink-body">
                                Lengkapi keterangan entitas usaha, penanggung jawab (PIC), dan nomor WhatsApp aktif untuk
                                verifikasi langsung Direktur Utama GPA.
                            </p>
                        </div>
                        <button type="button" @click="showContractModal = false"
                            class="rounded-lg p-1.5 text-ink-quiet hover:bg-surface-disabled hover:text-ink transition">
                            <x-gpa.icon name="x" class="h-5 w-5" />
                        </button>
                    </header>

                    <form action="{{ route('klien.pengajuan-kontrak') }}" method="POST"
                        class="mt-3 flex flex-1 flex-col gap-3.5 overflow-y-auto pr-1 gpa-scrollbar overscroll-contain">
                        @csrf

                        {{-- SEKSI 1: IDENTITAS ENTITAS USAHA (Tabel users) --}}
                        <div class="rounded-xl border border-line-hair bg-surface-shell/40 p-3.5 space-y-2.5">
                            <div class="flex items-center gap-1.5 border-b border-line-hair pb-1.5">
                                <x-gpa.icon name="building" class="h-3.5 w-3.5 text-brand-deep" />
                                <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">1. Identitas Badan
                                    Usaha / Pemohon</h4>
                            </div>

                            <div>
                                <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                    Nama Resmi Badan Usaha / Perusahaan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="company_name" required
                                    value="{{ old('company_name', Auth::user()->company_name) }}"
                                    placeholder="Contoh: PT Boga Mandiri Sejahtera / Hotel Grand Pasundan / Resto Dapur Nusantara"
                                    class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                            </div>

                            <div>
                                <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                    Alamat Kantor / Titik Bongkar Muat Utama <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="address" required value="{{ old('address', Auth::user()->address) }}"
                                    placeholder="Contoh: Jl. Sukajadi No. 128, Bandung, Jawa Barat (Gudang Central Kitchen)"
                                    class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                            </div>
                        </div>

                        {{-- SEKSI 2: KREDENSIAL PIC & NO WHATSAPP (Tabel users) --}}
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between border-b border-emerald-200 pb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <x-gpa.icon name="user-check" class="h-3.5 w-3.5 text-emerald-800" />
                                    <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-emerald-950">2.
                                        Penanggung Jawab (PIC) &amp; Verifikasi WhatsApp</h4>
                                </div>
                                <span
                                    class="rounded bg-emerald-200/80 px-2 py-0.5 font-mono text-[9px] font-extrabold text-emerald-900">
                                    WAJIB AKTIF
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                        Nama PIC Pengadaan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="pic_name" required
                                        value="{{ old('pic_name', Auth::user()->pic_name ?? Auth::user()->name) }}"
                                        placeholder="Contoh: Hendra Kusuma (Purchasing)"
                                        class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                                </div>

                                <div>
                                    <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                        Nomor WhatsApp PIC <span class="text-danger">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="pic_phone" required
                                            value="{{ old('pic_phone', Auth::user()->pic_phone ?? Auth::user()->phone) }}"
                                            placeholder="Contoh: 081234567890"
                                            class="h-9 w-full rounded-lg border border-line-strong bg-surface pl-3 pr-8 font-mono text-xs text-ink focus:border-brand focus:ring-0">
                                        <x-gpa.icon name="phone"
                                            class="pointer-events-none absolute right-2.5 top-2.5 h-3.5 w-3.5 text-emerald-700" />
                                    </div>
                                </div>
                            </div>

                            <p class="text-[10px] text-emerald-900 leading-tight">
                                &bull; Nomor WhatsApp di atas akan dihubungi oleh Tim Kemitraan GPA dalam 1x24 jam untuk
                                verifikasi draf PKS dan kelayakan fasilitas tempo.
                            </p>
                        </div>

                        <div x-data="{
                            selectedProductId: '{{ old('product_id', '') }}',
                            proposedVolume: '{{ old('committed_volume_per_cycle', '') }}',
                            proposedPrice: '{{ old('proposed_price_per_kg', '') }}',
                            proposedTop: '{{ old('top_days', '30') }}',
                            products: {{ Js::from($allAvailableProducts->map(fn($p) => ['id' => (string)$p->id, 'name' => $p->name, 'price' => (float)$p->base_price, 'min' => (float)$p->minimum_order])) }},
                            get selectedProduct() {
                                return this.products.find(p => p.id == this.selectedProductId);
                            },
                            get priceDiff() {
                                if (!this.selectedProduct || !this.proposedPrice) return 0;
                                return Number(this.proposedPrice) - this.selectedProduct.price;
                            },
                            get discountPct() {
                                if (!this.selectedProduct || !this.proposedPrice || this.priceDiff >= 0) return 0;
                                return Math.round((Math.abs(this.priceDiff) / this.selectedProduct.price) * 100);
                            },
                            get waMessage() {
                                const pic = '{{ addslashes(Auth::user()->pic_name ?: Auth::user()->name) }}';
                                const company = '{{ addslashes(Auth::user()->company_name ?: Auth::user()->name) }}';
                                const prod = this.selectedProduct ? this.selectedProduct.name : 'Komoditas Sayur';
                                const vol = this.proposedVolume ? this.proposedVolume + ' kg/siklus' : 'volume pasokan rutin';
                                const prc = this.proposedPrice ? 'Rp ' + Number(this.proposedPrice).toLocaleString('id-ID') + '/kg' : (this.selectedProduct ? 'Rp ' + Number(this.selectedProduct.price).toLocaleString('id-ID') + '/kg (harga katalog)' : 'perkiraan harga khusus');
                                return encodeURIComponent(`Halo Tim Kemitraan GPA, saya ${pic} dari ${company} ingin berkonsultasi mengenai proposal kontrak PKS komoditas ${prod}, estimasi volume ${vol}, dengan usulan harga ${prc} (TOP ${this.proposedTop} hari). Mohon informasinya. Terima kasih.`);
                            }
                        }" class="space-y-3.5">
                            {{-- SEKSI 3: KLAUSUL PASOKAN & TERMIN (Tabel contracts) --}}
                            <div class="rounded-xl border border-line-hair bg-surface-shell/40 p-3.5 space-y-2.5">
                                <div class="flex items-center gap-1.5 border-b border-line-hair pb-1.5">
                                    <x-gpa.icon name="package" class="h-3.5 w-3.5 text-brand-deep" />
                                    <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">3. Klausul Pasokan
                                        Komoditas &amp; Finansial</h4>
                                </div>

                                <div>
                                    <label class="mb-1 block font-mono text-xs font-semibold text-ink">Komoditas Utama yang
                                        Dikontrakkan <span class="text-danger">*</span></label>
                                    <select name="product_id" required x-model="selectedProductId"
                                        class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                                        <option value="">-- Pilih Komoditas --</option>
                                        @foreach ($allAvailableProducts as $prod)
                                            <option value="{{ $prod->id }}">
                                                {{ $prod->name }} (Rp {{ number_format($prod->base_price, 0, ',', '.') }}/kg) - Min Order {{ $prod->minimum_order }} kg
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">
                                    {{-- Kolom 1: Volume --}}
                                    <div>
                                        <div class="flex items-center justify-between h-5 mb-1.5">
                                            <label class="font-mono text-xs font-semibold text-ink">
                                                Estimasi Volume <span class="text-danger">*</span>
                                            </label>
                                            <span class="text-[10px] text-ink-quiet font-mono">Kg / siklus</span>
                                        </div>
                                        <input type="number" name="committed_volume_per_cycle" min="50" step="10" required
                                            x-model="proposedVolume"
                                            placeholder="Contoh: 500"
                                            class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 font-mono text-xs text-ink focus:border-brand focus:ring-0">
                                        <div class="mt-1 h-5 flex items-center">
                                            <span class="text-[10px] text-ink-quiet truncate">Minimal 50 kg per siklus kirim</span>
                                        </div>
                                    </div>

                                    {{-- Kolom 2: Usulan Harga --}}
                                    <div>
                                        <div class="flex items-center justify-between h-5 mb-1.5">
                                            <label class="font-mono text-xs font-semibold text-ink">
                                                Usulan Harga (Rp/Kg)
                                            </label>
                                            <span class="rounded bg-surface-muted px-1.5 py-0.5 text-[9px] font-mono font-medium text-ink-quiet">Opsional</span>
                                        </div>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute left-3 top-2 font-mono text-xs font-bold text-ink-quiet">Rp</span>
                                            <input type="number" name="proposed_price_per_kg" min="100" step="100"
                                                x-model="proposedPrice"
                                                :placeholder="selectedProduct ? selectedProduct.price : 'Harga katalog'"
                                                class="h-9 w-full rounded-lg border border-line-strong bg-surface pl-9 pr-3 font-mono text-xs text-ink focus:border-brand focus:ring-0">
                                        </div>
                                        <div class="mt-1 h-5 flex items-center">
                                            <template x-if="selectedProduct && proposedPrice && priceDiff < 0">
                                                <span class="text-[10px] font-bold text-emerald-700 truncate">
                                                    🟢 Diskon -Rp <span x-text="Math.abs(priceDiff).toLocaleString('id-ID')"></span>/kg (-<span x-text="discountPct"></span>%)
                                                </span>
                                            </template>
                                            <template x-if="selectedProduct && proposedPrice && priceDiff > 0">
                                                <span class="text-[10px] font-bold text-blue-700 truncate">
                                                    🔵 Premi +Rp <span x-text="priceDiff.toLocaleString('id-ID')"></span>/kg
                                                </span>
                                            </template>
                                            <template x-if="!proposedPrice">
                                                <span class="text-[10px] text-ink-quiet truncate">
                                                    Default sesuai harga katalog GPA
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between h-5 mb-1.5">
                                        <label class="font-mono text-xs font-semibold text-ink">
                                            Term of Payment (TOP) <span class="text-danger">*</span>
                                        </label>
                                        <span class="text-[10px] text-ink-quiet font-mono">Fasilitas tempo bayar invoice</span>
                                    </div>
                                    <select name="top_days" required x-model="proposedTop"
                                        class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                                        <option value="14">TOP 14 Hari Kalender</option>
                                        <option value="30" selected>TOP 30 Hari Kalender (Standar)</option>
                                        <option value="45">TOP 45 Hari (Kemitraan Khusus)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- SEKSI 4: HOTLINE WHATSAPP KEMITRAAN GPA --}}
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-2xs">
                                        <x-gpa.icon name="whatsapp" class="h-4 w-4 text-white" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-emerald-950 truncate">Konsultasi Langsung via WhatsApp</p>
                                        <p class="text-[11px] text-emerald-800 font-mono">+62 811-1223-3445 &bull; Hotline Tim GPA</p>
                                    </div>
                                </div>
                                <a :href="'https://wa.me/6281112233445?text=' + waMessage"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 font-sans text-xs font-bold text-white shadow-2xs hover:bg-emerald-700 transition">
                                    <x-gpa.icon name="whatsapp" class="h-3.5 w-3.5 text-white" />
                                    <span>Nego WA</span>
                                </a>
                            </div>
                        </div>

                        <footer class="mt-1 flex shrink-0 items-center justify-end gap-2 border-t border-line-hair pt-3">
                            <button type="button" @click="showContractModal = false"
                                class="rounded-lg bg-surface-disabled px-4 py-2 font-mono text-xs font-semibold text-ink hover:bg-surface-muted transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 font-mono text-xs font-bold text-accent shadow-sm hover:bg-brand-deep transition">
                                <x-gpa.icon name="send" class="h-3.5 w-3.5 text-accent" />
                                <span>Kirim Proposal ke Direktur</span>
                            </button>
                        </footer>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

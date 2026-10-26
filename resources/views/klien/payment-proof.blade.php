@extends('layouts.klien')

@section('title', 'Unggah Bukti Pembayaran & Verifikasi Tagihan')

@php
    $user = $user ?? auth()->user();
    $dbInvoices = $dbInvoices ?? collect();
    $dbPayments = $dbPayments ?? collect();
    $creditLimit = isset($contract) && $contract ? (float) $contract->credit_limit : 50000000.0;
    $outstandingTotal = $outstandingTotal ?? (float) $dbInvoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');

    $invoices = $invoices ?? [];
    if (empty($invoices) && $dbInvoices->isNotEmpty()) {
        foreach ($dbInvoices as $inv) {
            $gross = (float) ($inv->subtotal_amount ?: $inv->grand_total);
            $net = (float) $inv->grand_total;
            $creditAdjustment = max(0, $gross - $net);

            $invoices[] = [
                'po' => $inv->invoice_number,
                'option_label' => sprintf(
                    '%s — Periode %s (Rp %s)',
                    $inv->invoice_number,
                    $inv->period_start ? $inv->period_start->format('d/m/Y') : '-',
                    number_format($net, 0, ',', '.')
                ),
                'status_chip' => match ($inv->status) {
                    'PAID' => 'Lunas Sah',
                    'OVERDUE' => 'Lewat Jatuh Tempo',
                    'PARTIAL' => 'Terbayar Sebagian',
                    default => ($inv->has_pending_proof ? 'Menunggu Verifikasi Pembayaran' : 'Menunggu Pembayaran'),
                },
                'due_note' => $inv->due_date ? 'Jatuh tempo pada: ' . $inv->due_date->translatedFormat('d F Y') : 'Jatuh tempo TOP 30 Hari',
                'gross_label' => 'Rp ' . number_format($gross, 0, ',', '.'),
                'credit_label' => 'Penyesuaian Kredit / Retur:',
                'credit_value' => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
                'credit_value_label' => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
                'has_credit' => $creditAdjustment > 0,
                'payable' => (int) $net,
                'payable_label' => 'Rp ' . number_format($net, 0, ',', '.'),
                'payable_digits' => (string) (int) $net,
            ];
        }
    }

    $selectedPo = $tagihanParam ?? ($selected ?? ($invoices[0]['po'] ?? ''));
    $selectedExists = collect($invoices)->contains('po', $selectedPo);
    if (! $selectedExists && ! empty($invoices)) {
        $selectedPo = $invoices[0]['po'];
    }
    $selected = $selectedPo;

    // Deteksi apakah tagihan yang dipilih sudah memiliki bukti pembayaran yang sedang diverifikasi Sekretaris
    $pendingPaymentForSelected = $dbPayments->first(function ($p) use ($selectedPo) {
        $invNum = $p->invoice?->invoice_number ?: ($p->order?->order_number ?: '');
        return $invNum === $selectedPo 
            && $p->status === 'MENUNGGU_VERIFIKASI' 
            && !empty($p->proof_url);
    });

    // Deteksi bukti pembayaran terbaru untuk tagihan yang dipilih
    $latestPaymentForSelected = $dbPayments->first(function ($p) use ($selectedPo) {
        $invNum = $p->invoice?->invoice_number ?: ($p->order?->order_number ?: '');
        return $invNum === $selectedPo;
    });

    // Apakah pembayaran terbaru ditolak dan belum ada pembayaran pengganti yang sedang diverifikasi
    $rejectedPaymentForSelected = ($latestPaymentForSelected && in_array($latestPaymentForSelected->status, ['DITOLAK', 'REJECTED']))
        ? $latestPaymentForSelected
        : null;

    $form = $form ?? [
        'sender_bank' => '',
        'sender_account' => $user?->phone ?: '',
        'transfer_at' => \Carbon\Carbon::now()->format('d/m/Y H:i'),
        'reference' => '',
        'note' => '',
    ];

    $history = $history ?? [];
    if (empty($history) && $dbPayments->isNotEmpty()) {
        foreach ($dbPayments as $pay) {
            $history[] = [
                'id' => 'PAY-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT),
                'invoice' => $pay->invoice?->invoice_number ?: ($pay->order?->order_number ?: '-'),
                'date' => $pay->paid_at ? $pay->paid_at->format('d/m/Y H:i') : $pay->created_at->format('d/m/Y'),
                'amount_label' => 'Rp ' . number_format((float) $pay->amount, 0, ',', '.'),
                'method' => $pay->payment_method ?: 'TRANSFER_BANK',
                'verifier' => $pay->verifier?->name ?: 'Antrean Sistem',
                'verifier_pending' => ! $pay->verifier_id,
                'status' => match ($pay->status) {
                    'LUNAS', 'VERIFIED' => 'Terverifikasi Sah',
                    'DITOLAK', 'REJECTED' => 'Ditolak',
                    default => 'Menunggu Verifikasi',
                },
                'status_tone' => match ($pay->status) {
                    'LUNAS', 'VERIFIED' => 'success',
                    'DITOLAK', 'REJECTED' => 'danger',
                    default => 'warning',
                },
                'receipt' => in_array($pay->status, ['LUNAS', 'VERIFIED']) ? 'KWT-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT) : 'Menunggu Approval',
                'receipt_pending' => ! in_array($pay->status, ['LUNAS', 'VERIFIED']),
                'row_tone' => in_array($pay->status, ['LUNAS', 'VERIFIED']) ? 'plain' : 'muted',
                'note' => $pay->verification_note,
            ];
        }
    }
@endphp

@section('content')
    <div x-data="clientPaymentProof(@js([
        'invoices' => $invoices,
        'account' => [
            'number' => '8210-992-3841',
        ],
        'channels' => $paymentChannels ?? [],
        'fileRules' => [
            'accept' => ['jpg', 'jpeg', 'png', 'pdf'],
            'max_kb' => 5120,
            'max_label' => '5 MB',
        ],
        'form' => $form,
        'selected' => $selected,
    ]))" class="flex flex-col gap-4">
        {{-- ------------------------------------------------------------------ --}}
        {{-- Kembali ke pusat dokumen & kop halaman --}}
        {{-- ------------------------------------------------------------------ --}}
        <a href="{{ route('klien.documents') }}"
            class="inline-flex w-fit items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.55px] text-ink-body transition-colors hover:text-success-deep">
            <x-gpa.icon name="arrow-left" class="h-3 w-3" />
            Kembali ke Pusat Dokumen &amp; Faktur
        </a>

        <section class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-col gap-2">
                <h1 class="gpa-section-title text-ink">Unggah Bukti Pembayaran &amp; Rekonsiliasi Bank</h1>
                <p class="max-w-[60rem] text-sm leading-5 text-ink-body">Konfirmasi pembayaran transfer bank / giro korporat atas faktur tempo konsolidasi komoditas agro B2B.</p>
                <p class="max-w-[60rem] text-xs leading-4 text-ink-quiet">Setiap bukti transfer akan diverifikasi silang dengan mutasi rekening koran BCA/Mandiri GPA oleh Staf Sekretariat.</p>
            </div>

            <span
                class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg bg-warning/10 px-3 py-1.5 ring-1 ring-inset ring-warning/40">
                <x-gpa.icon name="clock" class="h-3 w-3 shrink-0 text-warning-deep" />
                <span class="text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-warning-deep">
                    SLA Verifikasi Pembayaran 1x24 Jam Kerja
                </span>
            </span>
        </section>

        {{-- Banner Pemberitahuan Bukti Pembayaran Ditolak Verifikator --}}
        @if ($rejectedPaymentForSelected && ! $pendingPaymentForSelected)
            <div class="flex flex-col gap-3 rounded-2xl border-2 border-danger/60 bg-danger-soft/30 p-4 sm:p-5 shadow-card transition-all">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger text-white shadow-sub">
                        <x-gpa.icon name="alert-triangle" class="h-6 w-6 text-white" />
                    </span>
                    <div class="flex flex-col gap-1 min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-danger px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                Verifikasi Pembayaran Ditolak
                            </span>
                            <span class="text-xs font-semibold text-danger">
                                Ref: {{ $rejectedPaymentForSelected->payment_reference ?: ('PAY-' . str_pad($rejectedPaymentForSelected->id, 5, '0', STR_PAD_LEFT)) }}
                            </span>
                            <span class="text-xs text-ink-body">
                                &bull; Waktu Penolakan: {{ $rejectedPaymentForSelected->updated_at ? $rejectedPaymentForSelected->updated_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }} WIB
                            </span>
                        </div>
                        <h2 class="font-sans text-base font-extrabold text-danger sm:text-lg">
                            Verifikasi Pembayaran Ditolak &bull; Silakan Unggah Ulang Bukti Pembayaran
                        </h2>

                        <div class="mt-1 rounded-xl bg-white/95 p-3.5 border border-danger/30 text-xs text-ink shadow-xs">
                            <p class="font-bold text-danger text-[11px] uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                <x-gpa.icon name="file-text" class="h-3.5 w-3.5 text-danger" />
                                Alasan Penolakan dari Staf Sekretariat GPA:
                            </p>
                            <p class="text-sm font-semibold text-ink leading-relaxed">
                                "{{ $rejectedPaymentForSelected->verification_note ?: 'Bukti transfer tidak valid atau dana belum masuk pada mutasi rekening koran resmi GPA.' }}"
                            </p>
                            @if ($rejectedPaymentForSelected->verifier)
                                <p class="mt-1 text-[11px] text-ink-quiet">
                                    Petugas Verifikator: <strong class="text-ink">{{ $rejectedPaymentForSelected->verifier->name }}</strong>
                                </p>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-ink-body border-t border-danger/20 pt-2">
                            <span>Status Tagihan: <strong class="text-danger">Belum Lunas</strong></span>
                            <span>&bull;</span>
                            <span class="text-ink-body">Silakan cek mutasi/rekening Anda dan <strong>unggah ulang bukti pembayaran yang sah</strong> pada formulir di bawah ini.</span>
                            @if ($rejectedPaymentForSelected->proof_url)
                                <span>&bull;</span>
                                <a href="{{ asset('storage/' . $rejectedPaymentForSelected->proof_url) }}" target="_blank"
                                    class="font-bold text-danger hover:underline inline-flex items-center gap-1">
                                    <x-gpa.icon name="eye" class="h-3.5 w-3.5" />
                                    Lihat Berkas yang Ditolak Sebelumnya
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="rounded-xl bg-surface/90 p-3 text-[11px] leading-relaxed text-ink-body border border-danger/20 flex items-center gap-2">
                    <x-gpa.icon name="info" class="h-4 w-4 shrink-0 text-danger" />
                    <span><strong>Petunjuk:</strong> Form di bawah telah disiapkan. Masukkan data setoran bank yang benar dan lampirkan foto slip transfer asli yang terbaca jelas.</span>
                </div>
            </div>
        @endif

        {{-- Banner Pemberitahuan Bukti Pembayaran Terkirim ke Sekretaris --}}
        @if ($pendingPaymentForSelected)
            <div class="flex flex-col gap-3 rounded-2xl border-2 border-warning/50 bg-warning-wash p-4 sm:p-5 shadow-card transition-all">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning-deep text-white shadow-sub">
                        <x-gpa.icon name="clock" class="h-6 w-6 text-white" />
                    </span>
                    <div class="flex flex-col gap-1 min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-warning-deep px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                Bukti Pembayaran Terkirim
                            </span>
                            <span class="text-xs font-semibold text-warning-deep">
                                Ref: {{ $pendingPaymentForSelected->payment_reference ?: ('PAY-' . str_pad($pendingPaymentForSelected->id, 5, '0', STR_PAD_LEFT)) }}
                            </span>
                        </div>
                        <h2 class="font-sans text-base font-extrabold text-ink sm:text-lg">
                            Sedang Dalam Antrean Verifikasi Kasir / Sekretariat GPA
                        </h2>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Bukti transfer sebesar <strong class="text-ink">Rp {{ number_format((float) $pendingPaymentForSelected->amount, 0, ',', '.') }}</strong> atas tagihan <strong class="text-ink">{{ $selectedPo }}</strong> telah berhasil diterima sistem pada <span class="font-semibold text-ink">{{ $pendingPaymentForSelected->paid_at ? $pendingPaymentForSelected->paid_at->format('d/m/Y H:i') : $pendingPaymentForSelected->created_at->format('d/m/Y H:i') }} WIB</span>. Staf kami sedang melakukan pencocokan mutasi kas rekening bank.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-ink-body border-t border-warning/30 pt-2">
                            <span>Kanal: <strong class="text-ink">{{ strtoupper(str_replace('_', ' ', $pendingPaymentForSelected->payment_method)) }}</strong></span>
                            <span>&bull;</span>
                            <span>Status: <strong class="text-warning-deep">Menunggu Rekonsiliasi Bank</strong></span>
                            @if ($pendingPaymentForSelected->proof_url)
                                <span>&bull;</span>
                                <a href="{{ asset('storage/' . $pendingPaymentForSelected->proof_url) }}" target="_blank"
                                    class="font-bold text-brand hover:underline inline-flex items-center gap-1">
                                    <x-gpa.icon name="eye" class="h-3.5 w-3.5" />
                                    Lihat Berkas Bukti yang Diunggah
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="rounded-xl bg-surface/80 p-3 text-[11px] leading-relaxed text-ink-body border border-warning/30 flex items-center gap-2">
                    <x-gpa.icon name="check-circle" class="h-4 w-4 shrink-0 text-success-deep" />
                    <span><strong>Pemberitahuan:</strong> Data Anda sudah tersimpan di sistem. Anda <em>tidak perlu</em> mengunggah ulang bukti pembayaran kecuali jika berkas sebelumnya salah atau ingin diperbarui.</span>
                </div>
            </div>
        @endif

        <div class="grid gap-4 xl:grid-cols-3">
            {{-- ---------------------------------------------------------------- --}}
            {{-- Kolom kiri: pilih tagihan + unggah berkas & metadata --}}
            {{-- ---------------------------------------------------------------- --}}
            <div class="flex flex-col gap-4 xl:col-span-2">
                {{-- Langkah 1: pilih tagihan yang akan dibayar --}}
                <section class="gpa-card flex flex-col gap-4 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <h2 class="gpa-section-title text-ink">1. Pilih Tagihan yang Akan Dibayar</h2>
                        </div>
                        <span
                            class="rounded bg-accent px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[1px] text-ink ring-1 ring-inset ring-accent"
                            x-text="current?.status_chip"></span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="pilih-tagihan" class="text-xs font-semibold leading-4 text-ink">
                            Pilih Nomor Faktur / Konsolidasi Periode:
                        </label>
                        <select id="pilih-tagihan" x-model="selected" @change="onInvoiceChange()"
                            class="gpa-control w-full appearance-none bg-surface">
                            @forelse ($invoices as $invoice)
                                <option value="{{ $invoice['po'] }}" @selected($invoice['po'] === $selected)>
                                    {{ $invoice['option_label'] }}
                                </option>
                            @empty
                                <option value="">Tidak ada tagihan yang menunggu pembayaran</option>
                            @endforelse
                        </select>
                        <p class="text-xs leading-4 text-ink-body" x-text="current?.due_note"></p>
                    </div>

                    <div class="flex flex-col gap-2 rounded-xl border border-line-soft bg-surface-shell p-4">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">Nilai Tagihan Kotor (Gross Invoice):</dt>
                            <dd class="text-right font-mono text-sm font-semibold tracking-[0.28px] text-ink"
                                x-text="current?.gross_label"></dd>
                        </div>

                        <div class="flex items-start justify-between gap-3">
                            <dt class="flex items-start gap-1.5 text-xs leading-4 text-danger">
                                <x-gpa.icon name="alert-circle" class="mt-px h-3 w-3 shrink-0" />
                                <span x-text="current?.credit_label"></span>
                            </dt>
                            <dd class="text-right font-mono text-sm font-bold tracking-[0.28px] text-danger"
                                x-text="creditValueLabel"></dd>
                        </div>

                        <div class="border-t border-line-soft py-1"></div>

                        <div class="flex flex-col gap-3 rounded-lg border border-success-deep/30 bg-surface p-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-1">
                                <p class="text-xs font-bold leading-4 text-ink">Total Akurat Wajib Transfer:</p>
                                <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-deep">
                                    Sudah Termasuk Penyesuaian Retur
                                </p>
                                <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-deep">
                                    Rekonsiliasi Bank Koran GPA
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <p class="font-inter text-2xl font-bold leading-8 tracking-tight text-ink"
                                    x-text="current?.payable_label"></p>
                                <button type="button" @click="copyAmount()"
                                    class="inline-flex items-center gap-1 text-[9px] font-bold uppercase tracking-[1.08px] text-success-deep transition-colors hover:text-ink">
                                    <x-gpa.icon name="copy" class="h-2.5 w-2.5" />
                                    Salin Nominal Presisi
                                </button>
                            </div>
                        </div>

                        {{-- Pemilihan Kanal Pembayaran Resmi GPA (Dikelola & Dikustomisasi oleh Direktur) --}}
                        <div class="flex flex-col gap-3 pt-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-ink">
                                    Pilih Kanal Pembayaran Resmi GPA:
                                </label>
                                <span class="rounded bg-accent/30 px-2 py-0.5 text-[10px] font-bold text-success-ink">
                                    Resmi Ditetapkan Direksi
                                </span>
                            </div>

                            {{-- Pilihan Tab / Kartu Kanal --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
                                <template x-for="ch in channels" :key="ch.id">
                                    <button type="button" @click="selectChannel(ch.id)"
                                        class="flex items-center gap-2.5 rounded-xl border p-2.5 text-left transition-all"
                                        :class="selectedChannelId === ch.id 
                                            ? 'border-brand bg-brand text-white shadow-sub ring-2 ring-accent' 
                                            : 'border-line-soft bg-surface text-ink hover:border-line-board hover:bg-surface-muted'">
                                        <div class="flex h-10 w-14 shrink-0 items-center justify-center rounded-lg bg-white p-1.5 shadow-xs border border-line-soft overflow-hidden">
                                            <template x-if="ch.logo_url">
                                                <img :src="ch.logo_url" :alt="ch.name" class="h-full w-full object-contain" />
                                            </template>
                                            <template x-if="!ch.logo_url">
                                                <span class="font-mono text-xs font-extrabold text-ink" x-text="ch.bank_code || (ch.type === 'QRIS' ? 'QRIS' : (ch.type === 'EWALLET' ? 'WALLET' : 'BANK'))"></span>
                                            </template>
                                        </div>
                                        <div class="min-w-0 flex flex-col">
                                            <span class="font-sans text-xs font-bold truncate" :class="selectedChannelId === ch.id ? 'text-white' : 'text-ink'" x-text="ch.name"></span>
                                            <span class="font-mono text-[10px] truncate" :class="selectedChannelId === ch.id ? 'text-accent' : 'text-ink-quiet'" x-text="ch.account_number"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            {{-- Kartu Detail Penerima Terpilih --}}
                            <div class="flex flex-col gap-3 rounded-xl bg-brand p-4 ring-1 ring-inset ring-accent/30 text-white">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <div class="flex h-12 w-20 shrink-0 items-center justify-center rounded-xl bg-white p-2 shadow-sub border border-white/30 overflow-hidden">
                                            <template x-if="selectedChannel?.logo_url">
                                                <img :src="selectedChannel.logo_url" :alt="selectedChannel.name" class="h-full w-full object-contain" />
                                            </template>
                                            <template x-if="!selectedChannel?.logo_url">
                                                <span class="font-mono text-sm font-extrabold text-ink"
                                                    x-text="selectedChannel?.bank_code || (selectedChannel?.type === 'QRIS' ? 'QRIS' : (selectedChannel?.type === 'EWALLET' ? 'WALLET' : 'BANK'))">
                                                </span>
                                            </template>
                                        </div>
                                        <div class="flex min-w-0 flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-accent"
                                                    x-text="selectedChannel?.type === 'QRIS' ? 'QRIS Standar Nasional Indonesia (ASPI)' : (selectedChannel?.type === 'EWALLET' ? 'Dompet Digital Bisnis Resmi' : 'Rekening Giro / Virtual Account Resmi')">
                                                </span>
                                                <span class="rounded bg-accent/20 px-1.5 py-0.2 text-[9px] font-semibold text-accent">TERVERIFIKASI</span>
                                            </div>
                                            <p class="font-mono text-base font-bold tracking-wide text-white"
                                                x-text="selectedChannel?.name + ' : ' + selectedChannel?.account_number">
                                            </p>
                                            <p class="text-xs text-line-soft">
                                                a.n. <strong class="text-white" x-text="selectedChannel?.account_name"></strong>
                                            </p>
                                        </div>
                                    </div>

                                    <button type="button" @click="copyAccount()"
                                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-accent px-4 py-2 font-sans text-xs font-bold uppercase tracking-wider text-ink transition-transform hover:scale-105 hover:bg-accent-deep shadow-sub">
                                        <x-gpa.icon name="copy" class="h-3.5 w-3.5 text-ink" />
                                        <span x-text="selectedChannel?.type === 'QRIS' ? 'Salin NMID' : (selectedChannel?.type === 'EWALLET' ? 'Salin No. HP/ID' : 'Salin Rekening')"></span>
                                    </button>
                                </div>

                                {{-- Pratinjau QRIS jika memilih QRIS (Menggunakan Foto / Barcode Resmi) --}}
                                <div x-show="selectedChannel?.type === 'QRIS'" class="mt-1 flex flex-col sm:flex-row items-center gap-4 rounded-xl bg-surface/10 p-3.5 border border-accent/20">
                                    {{-- Tampilan Barcode: Foto Asli dari Server / Unggahan Direktur --}}
                                    <template x-if="selectedChannel?.image_url">
                                        <div class="group relative h-32 w-32 shrink-0 rounded-xl bg-white p-1.5 flex items-center justify-center shadow-lg border border-accent/40 cursor-pointer overflow-hidden transition-all hover:scale-105"
                                            @click="fullQrisModal = (selectedChannel.image_url || '/storage/payment-channels/qris-gpa-default.png')">
                                            <img :src="selectedChannel.image_url" 
                                                onerror="this.onerror=null;this.src='/storage/payment-channels/qris-gpa-default.png'"
                                                alt="Barcode QRIS Resmi GPA" class="h-full w-full object-contain rounded-lg">
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-xl">
                                                <span class="text-[10px] font-bold text-white bg-black/70 px-2 py-1 rounded">Perbesar Layar</span>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Fallback jika belum ada foto terunggah --}}
                                    <template x-if="!selectedChannel?.image_url">
                                        <div class="h-28 w-28 shrink-0 rounded-lg bg-white p-1.5 flex items-center justify-center shadow-md">
                                            <svg viewBox="0 0 100 100" class="h-full w-full">
                                                <rect width="100" height="100" fill="white" />
                                                <rect x="5" y="5" width="28" height="28" fill="black" />
                                                <rect x="9" y="9" width="20" height="20" fill="white" />
                                                <rect x="13" y="13" width="12" height="12" fill="black" />
                                                <rect x="67" y="5" width="28" height="28" fill="black" />
                                                <rect x="71" y="9" width="20" height="20" fill="white" />
                                                <rect x="75" y="13" width="12" height="12" fill="black" />
                                                <rect x="5" y="67" width="28" height="28" fill="black" />
                                                <rect x="9" y="71" width="20" height="20" fill="white" />
                                                <rect x="13" y="75" width="12" height="12" fill="black" />
                                                <rect x="38" y="10" width="6" height="6" fill="black" />
                                                <rect x="48" y="15" width="6" height="6" fill="black" />
                                                <rect x="56" y="8" width="6" height="6" fill="black" />
                                                <rect x="38" y="24" width="6" height="6" fill="black" />
                                                <rect x="48" y="28" width="6" height="6" fill="black" />
                                                <rect x="10" y="38" width="6" height="6" fill="black" />
                                                <rect x="18" y="46" width="6" height="6" fill="black" />
                                                <rect x="28" y="38" width="6" height="6" fill="black" />
                                                <rect x="38" y="38" width="24" height="24" fill="black" />
                                                <rect x="42" y="42" width="16" height="16" fill="white" />
                                                <rect x="46" y="46" width="8" height="8" fill="#1b4d3e" />
                                                <rect x="68" y="38" width="6" height="6" fill="black" />
                                                <rect x="78" y="46" width="6" height="6" fill="black" />
                                                <rect x="86" y="38" width="6" height="6" fill="black" />
                                                <rect x="38" y="68" width="6" height="6" fill="black" />
                                                <rect x="48" y="76" width="6" height="6" fill="black" />
                                                <rect x="56" y="68" width="6" height="6" fill="black" />
                                                <rect x="68" y="68" width="6" height="6" fill="black" />
                                                <rect x="78" y="76" width="6" height="6" fill="black" />
                                                <rect x="86" y="84" width="6" height="6" fill="black" />
                                            </svg>
                                        </div>
                                    </template>

                                    <div class="flex flex-col text-xs min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-accent">Scan QRIS GPA via M-Banking / Dompet Digital:</p>
                                            <span class="rounded bg-accent/20 px-1.5 py-0.2 text-[9px] font-semibold text-accent">INSTAN</span>
                                        </div>
                                        <p class="mt-0.5 text-line-soft text-[11px] leading-relaxed">
                                            Buka BCA Mobile, Livin' Mandiri, BRImo, BNI Mobile, BSI Mobile, GoPay, OVO, ShopeePay, atau DANA, lalu arahkan kamera ke barcode di samping.
                                        </p>
                                        <p class="mt-1 font-mono text-[11px] text-accent font-semibold" x-text="'NMID: ' + selectedChannel?.account_number"></p>
                                        
                                        <div class="mt-2.5 flex flex-wrap items-center gap-2" x-show="selectedChannel?.image_url">
                                            <a :href="selectedChannel?.image_url" download="QRIS-AgroOrder-GPA.png"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3 py-1.5 font-sans text-xs font-bold text-ink hover:bg-accent-deep transition-all shadow-sub">
                                                <x-gpa.icon name="download" class="h-3.5 w-3.5 text-ink" />
                                                Unduh Barcode QRIS
                                            </a>
                                            <button type="button" @click="fullQrisModal = (selectedChannel.image_url || '/storage/payment-channels/qris-gpa-default.png')"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 bg-white/10 px-3 py-1.5 font-sans text-xs font-semibold text-white hover:bg-white/20 transition-all">
                                                <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-white" />
                                                Perbesar Tampilan Barcode
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-[11px] leading-relaxed text-line-soft" x-text="selectedChannel?.instructions || 'Harap sertakan Nomor Faktur di kolom Berita Acara Transfer untuk rekonsiliasi otomatis perbankan.'"></p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Langkah 2: unggah berkas & metadata transfer --}}
                <section class="gpa-card flex flex-col gap-4 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <h2 class="gpa-section-title text-ink">2. Unggah Berkas &amp; Metadata Transfer</h2>
                        </div>
                        <span class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                            Format: JPG, PNG, atau PDF (Maks. 5 MB)
                        </span>
                    </div>

                    <div class="relative rounded-xl border-2 border-dashed border-ink-quiet bg-surface-shell p-6 text-center"
                        :class="dragActive ? 'border-accent-edge bg-accent/20' : ''"
                        @dragover.prevent="onDragOver()" @dragenter.prevent="onDragOver()"
                        @dragleave.prevent="dragActive = false" @drop.prevent="onDrop($event)">
                        <span
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-accent">
                            <x-gpa.icon name="upload" class="h-7 w-7 text-ink" />
                        </span>
                        <p class="pt-2 text-base font-semibold leading-6 text-ink">
                            Tarik &amp; letakkan foto slip transfer / screenshot m-banking di sini
                        </p>
                        <p class="pt-0.5 text-xs leading-4 text-ink-body">
                            atau klik untuk memilih berkas dari penyimpanan lokal komputer/smartphone Anda
                        </p>
                        <label for="berkas-slip" class="sr-only">Pilih berkas slip transfer</label>
                        <input id="berkas-slip" type="file" class="sr-only" accept="image/jpeg,image/png,application/pdf"
                            @change="onFileInput($event)">
                        <span class="inline-flex pt-3">
                            <label for="berkas-slip"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-ink px-4 py-2 text-[11px] font-bold uppercase leading-[14px] tracking-[0.88px] text-white transition-colors hover:bg-brand-deep">
                                <x-gpa.icon name="upload" class="h-3 w-3" />
                                Pilih Berkas
                            </label>
                        </span>
                    </div>

                    <div class="flex flex-col gap-2 rounded-xl border border-success-deep/40 bg-surface-pill p-2 sm:flex-row sm:items-center sm:justify-between"
                        x-show="hasFile" x-cloak x-transition.opacity>
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-brand">
                                <x-gpa.icon name="file-text" class="h-4 w-4 text-accent" />
                            </span>
                            <div class="flex min-w-0 flex-col">
                                <p class="truncate text-xs font-bold leading-4 text-ink" x-text="file?.name"></p>
                                <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body"
                                    x-text="`Ukuran: ${file?.size_label} • Waktu: ${file?.time_label} • ${file?.hash_label}`"></p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <label for="berkas-slip"
                                class="cursor-pointer text-[11px] font-medium uppercase leading-[14px] tracking-[0.55px] text-ink-body transition-colors hover:text-ink">
                                Ganti File
                            </label>
                            <button type="button" @click="removeFile()" aria-label="Hapus berkas slip transfer"
                                class="text-danger transition-colors hover:text-warning-deep">
                                <x-gpa.icon name="trash" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <p class="rounded-lg bg-danger-soft px-3 py-2 text-xs font-medium leading-4 text-danger"
                        x-show="error" x-cloak x-text="error"></p>

                    <div class="flex flex-col gap-4 pt-2">
                        <div class="flex flex-col gap-1.5">
                            <label for="bank-pengirim" class="text-xs font-semibold leading-4 text-ink">
                                Bank / E-Wallet / Metode Pengirim:
                            </label>
                            <input id="bank-pengirim" type="text" x-model="form.senderBank" value="{{ $form['sender_bank'] }}"
                                placeholder="Contoh: Bank BCA / Mandiri / QRIS / GoPay" class="gpa-control">
                            <span class="text-[10px] text-ink-quiet">Diselaraskan otomatis dengan kanal tujuan terpilih atau sebutkan bank/dompet asal Anda.</span>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="rekening-pengirim" class="text-xs font-semibold leading-4 text-ink">
                                Nomor Rekening / No. HP E-Wallet Pengirim:
                            </label>
                            <input id="rekening-pengirim" type="text" x-model="form.senderAccount" value="{{ $form['sender_account'] }}"
                                placeholder="Nomor rekening asal atau nomor HP akun e-wallet..."
                                class="gpa-control font-mono text-sm font-semibold tracking-[0.28px]">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="nominal-transfer" class="text-xs font-semibold leading-4 text-ink">
                                Nominal Ditransfer:
                            </label>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center rounded-l-lg border border-line-soft bg-surface-pill px-3 font-mono text-sm font-bold text-ink-body">
                                    IDR
                                </span>
                                <input id="nominal-transfer" type="text" inputmode="numeric" x-model="form.amount"
                                    value="{{ $invoices[0]['payable_digits'] ?? '' }}"
                                    class="gpa-control flex-1 rounded-l-none border-l-0 font-mono text-sm font-bold tracking-[0.28px]"
                                    :aria-invalid="amountMatches ? 'false' : 'true'">
                            </div>
                            <p class="font-mono text-[9px] font-bold uppercase leading-3 tracking-[1.08px]"
                                :class="amountMatches ? 'text-success-deep' : 'text-danger'"
                                x-text="amountMatches
                                    ? '✓ Nilai klop sesuai Total Wajib Transfer'
                                    : 'Nominal wajib sama dengan Total Wajib Transfer'"></p>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="waktu-transfer" class="text-xs font-semibold leading-4 text-ink">
                                Tanggal &amp; Jam Transfer:
                            </label>
                            <input id="waktu-transfer" type="text" x-model="form.transferAt" value="{{ $form['transfer_at'] }}" class="gpa-control">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="referensi-bank" class="text-xs font-semibold leading-4 text-ink">
                                Nomor Referensi Bank / No. Bukti Transaksi:
                            </label>
                            <input id="referensi-bank" type="text" x-model="form.reference" value="{{ $form['reference'] }}"
                                class="gpa-control font-mono text-sm font-semibold tracking-[0.28px]">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="catatan-pembayar" class="text-xs font-semibold leading-4 text-ink">
                                Catatan Tambahan Pembayar:
                            </label>
                            <textarea id="catatan-pembayar" rows="3" x-model="form.note"
                                class="gpa-control min-h-[5.5rem] text-sm leading-[1.6]">{{ $form['note'] }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-line-soft bg-surface-shell p-2">
                        <input id="pernyataan-kepatuhan" type="checkbox" x-model="form.compliance"
                            class="mt-0.5 h-4 w-4 shrink-0 rounded border-line-soft text-success-deep focus:ring-success">
                        <p class="text-xs leading-4 text-ink-body">
                            <span class="font-bold text-ink">Pernyataan Kepatuhan Keuangan:</span>
                            Saya menyatakan bahwa bukti transfer yang diunggah adalah sah, dana berasal dari rekening koran resmi perusahaan mitra, dan nominal yang dikirimkan sesuai dengan kewajiban tagihan tempo berjalan.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-line-soft pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" @click="saveDraft()"
                            class="inline-flex items-center justify-center gap-1.5 text-[11px] font-semibold uppercase leading-[14px] tracking-[0.55px] text-ink-body transition-colors hover:text-ink">
                            <x-gpa.icon name="save" class="h-3 w-3" />
                            Batal / Simpan Draf
                        </button>

                        <div class="flex flex-col items-start gap-1.5 sm:items-end">
                            <button type="button" @click="submit()" :disabled="sending"
                                class="inline-flex items-center gap-2 rounded-lg bg-accent px-6 py-3 text-ink shadow-sub transition-colors hover:bg-accent-deep disabled:cursor-not-allowed disabled:opacity-60">
                                <x-gpa.icon name="send" class="h-4 w-4 text-ink" />
                                <span class="text-sm font-bold uppercase leading-[17px] tracking-[0.7px] text-ink"
                                    x-text="sending ? 'Mengirim ke Sekretariat...' : 'Kirim Bukti Pembayaran ke Secretariat GPA'"></span>
                            </button>
                            <p class="text-[10px] leading-4 text-ink-body"
                                x-show="submitted" x-cloak>
                                Bukti terkirim. Status tagihan menunggu pemeriksaan Staf Sekretariat.
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- ---------------------------------------------------------------- --}}
            {{-- Kolom kanan: pipeline verifikasi + ringkasan plafon --}}
            {{-- ---------------------------------------------------------------- --}}
            <div class="flex flex-col gap-4">
                <section class="gpa-card flex flex-col gap-4 p-6">
                    <div class="flex items-center justify-between gap-2 border-b border-line-soft pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <h2 class="gpa-section-title text-ink">Pelacakan Status Verifikasi</h2>
                        </div>
                        <span class="rounded bg-ink px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-[1.08px] text-white">
                            Live Pipeline
                        </span>
                    </div>

                    <ol class="relative flex flex-col gap-6 pl-6">
                        <span class="absolute left-3 top-3 h-[calc(100%-1.5rem)] w-0.5 rounded bg-line-board"
                            aria-hidden="true"></span>

                        {{-- Step 1 --}}
                        <li class="relative flex flex-col gap-1">
                            <span class="absolute -left-6 top-0 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold bg-success-deep text-white ring-4 ring-surface">
                                <x-gpa.icon name="check" class="h-3 w-3" />
                            </span>
                            <div class="flex flex-col gap-1">
                                <p class="text-xs font-bold leading-4 text-ink">1. Unggah Slip &amp; Validasi Form</p>
                                <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                    Mitra Klien B2B
                                </p>
                                <p class="text-xs leading-4 text-ink-body">Pengisian metadata bank pengirim, nominal akurat, dan unggah berkas slip transfer.</p>
                            </div>
                        </li>

                        {{-- Step 2 --}}
                        <li class="relative flex flex-col gap-1">
                            <span class="absolute -left-6 top-0 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold bg-accent text-ink ring-4 ring-surface">
                                <span class="h-2.5 w-2.5 rounded-full bg-ink" aria-hidden="true"></span>
                            </span>
                            <div class="rounded-xl border border-success-deep/40 bg-surface-shell p-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-xs font-bold leading-4 text-ink">
                                        2. Pemeriksaan Mutasi Rekening
                                    </p>
                                    <span class="rounded bg-success-deep px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-[1.08px] text-white">
                                        Dalam Proses
                                    </span>
                                </div>
                                <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-deep">
                                    Staf Sekretariat GPA
                                </p>
                                <p class="pt-0.5 text-xs leading-4 text-ink">Verifikasi silang nomor referensi transaksi dengan rekening koran giro GPA Finance.</p>
                            </div>
                        </li>

                        {{-- Step 3 --}}
                        <li class="relative flex flex-col gap-1">
                            <span class="absolute -left-6 top-0 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold bg-surface-disabled text-ink-body ring-4 ring-surface">
                                3
                            </span>
                            <div class="flex flex-col gap-1 opacity-60">
                                <p class="text-xs font-semibold leading-4 text-ink-body">3. Penerbitan Kuitansi Resmi &amp; Pemulihan Plafon</p>
                                <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                    Sistem Otomatis GPA
                                </p>
                                <p class="text-xs leading-4 text-ink-body">Status faktur berubah menjadi LUNAS dan limit plafon kredit tempo kembali dipulihkan secara instan.</p>
                            </div>
                        </li>
                    </ol>

                    <div class="flex items-start gap-3 rounded-xl bg-brand p-4 ring-1 ring-inset ring-accent/30">
                        <x-gpa.icon name="info" class="h-5 w-5 shrink-0 text-accent" />
                        <div class="flex flex-col gap-1">
                            <p class="text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-accent">
                                Sistem Rekonsiliasi Otomatis (Host-to-Host)
                            </p>
                            <p class="text-xs leading-[1.65] text-surface-disabled">Sistem kami terhubung dengan API perbankan BCA dan Mandiri Corporate. Jika nominal dan nomor referensi klop, verifikasi kuitansi selesai dalam kurun waktu 15 - 30 menit.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-line-soft bg-surface-shell p-3">
                        <div class="flex items-center gap-2.5">
                            <x-gpa.icon name="phone" class="h-4 w-4 shrink-0 text-success-deep" />
                            <div class="flex flex-col">
                                <p class="text-xs font-semibold leading-4 text-ink">Kendala Pembayaran / Selisih Transfer?</p>
                                <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                    WhatsApp Finance GPA: +62 811-2345-6789 (Senin - Sabtu 08:00 - 17:00 WIB)
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="contactPic()"
                            class="rounded border border-line-soft bg-surface-pill px-3 py-1 font-mono text-[9px] font-bold uppercase tracking-[1.08px] text-ink transition-colors hover:bg-surface">
                            Hubungi PIC Keuangan
                        </button>
                    </div>
                </section>

                <section class="gpa-card flex flex-col gap-2 p-4">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body">Kondisi Plafon Kredit Tempo</h2>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1 rounded-lg bg-surface-shell p-3">
                            <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                Plafon Disetujui
                            </p>
                            <p class="font-mono text-sm font-bold tracking-[0.28px] text-ink">
                                Rp {{ number_format($creditLimit, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-1 rounded-lg bg-surface-shell p-3">
                            <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                Total Tagihan Berjalan
                            </p>
                            <p class="font-mono text-sm font-bold tracking-[0.28px] text-success-deep">
                                Rp {{ number_format($outstandingTotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <p class="text-[10px] leading-4 text-ink-body">Setelah bukti diverifikasi sah oleh Sekretariat, sisa limit kredit Anda akan bertambah sebesar nominal yang dilunasi.</p>
                </section>
            </div>
        </div>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Riwayat pembayaran & verifikasi 60 hari terakhir --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card flex flex-col gap-4 p-6">
            <div class="flex flex-col gap-2 border-b border-line-soft pb-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-0.5">
                    <h2 class="gpa-section-title text-ink">Riwayat Pembayaran &amp; Verifikasi Terkini</h2>
                    <p class="text-xs leading-4 text-ink-body">Menampilkan riwayat rekonsiliasi pembayaran dan status validasi kuitansi perbankan.</p>
                </div>
                <button type="button" @click="exportReport()"
                    class="inline-flex w-fit shrink-0 items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.55px] text-success-deep transition-colors hover:text-ink">
                    <x-gpa.icon name="download" class="h-3 w-3" />
                    Unduh Rekap Pembayaran (CSV)
                </button>
            </div>

            <div class="-mx-1 overflow-x-auto px-1">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-shell">
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-left">ID Bayar</th>
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-left">Faktur Terkait</th>
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-left">Tanggal Transfer</th>
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-right">Nominal</th>
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-left">Metode</th>
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-left">Pemeriksa</th>
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-left">Status</th>
                            <th scope="col" class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body text-right">No. Kuitansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $row)
                            <tr class="border-b border-line-soft last:border-b-0 {{ $row['row_tone'] === 'muted' ? 'bg-surface-shell' : 'bg-surface' }}">
                                <td class="px-3 py-3 font-mono text-xs font-bold text-ink">{{ $row['id'] }}</td>
                                <td class="px-3 py-3 font-mono text-xs text-ink-body">{{ $row['invoice'] }}</td>
                                <td class="px-3 py-3 text-xs text-ink-body">{{ $row['date'] }}</td>
                                <td class="px-3 py-3 text-right font-mono text-xs font-bold text-ink">
                                    {{ $row['amount_label'] }}
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center rounded border border-line-soft bg-surface-pill px-2 py-1 font-mono text-[9px] font-semibold uppercase tracking-[1.08px] text-ink">
                                        {{ $row['method'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-xs leading-4 {{ $row['verifier_pending'] ? 'italic text-ink-body' : 'text-ink' }}">{{ $row['verifier'] }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    @php
                                        $badgeClass = match ($row['status_tone']) {
                                            'warning' => 'bg-warning/10 text-warning-deep ring-warning/40',
                                            'danger' => 'bg-danger-soft text-danger ring-danger/40',
                                            default => 'bg-accent text-ink ring-accent',
                                        };
                                    @endphp
                                    <div class="flex flex-col gap-0.5">
                                        <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-2 py-1 text-[9px] font-bold uppercase tracking-[0.45px] ring-1 ring-inset {{ $badgeClass }}">
                                            @if ($row['status_tone'] === 'warning')
                                                <span class="h-1.5 w-1.5 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                            @elseif ($row['status_tone'] === 'danger')
                                                <x-gpa.icon name="x" class="h-2.5 w-2.5" />
                                            @else
                                                <x-gpa.icon name="check" class="h-2.5 w-2.5" />
                                            @endif
                                            {{ $row['status'] }}
                                        </span>
                                        @if (!empty($row['note']) && $row['status_tone'] === 'danger')
                                            <span class="text-[10px] text-danger max-w-[14rem] truncate" title="{{ $row['note'] }}">
                                                Ket: {{ $row['note'] }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span class="font-mono text-[9px] font-semibold uppercase tracking-[1.08px] {{ $row['receipt_pending'] ? 'text-ink-quiet' : 'text-ink' }}">{{ $row['receipt'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-8 text-center text-xs italic text-ink-body">
                                    Belum ada catatan riwayat pembayaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Modal Lightbox Barcode QRIS Layar Penuh --}}
        <template x-teleport="body">
            <div x-show="fullQrisModal" x-cloak 
                class="fixed inset-0 z-[999999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 bg-black/80 backdrop-blur-md"
                @click.self="fullQrisModal = null" 
                @keydown.escape.window="fullQrisModal = null">
                <div class="relative m-auto flex max-h-[90vh] w-full max-w-md flex-col rounded-2xl border border-line-soft bg-surface p-5 shadow-2xl overflow-hidden"
                    @click.stop>
                    {{-- Header Modal --}}
                    <div class="relative flex items-center justify-between pb-3 border-b border-line-soft shrink-0">
                        <div class="text-left">
                            <span class="rounded bg-accent/20 px-2 py-0.5 text-[10px] font-bold text-accent uppercase">QRIS Standar Nasional (ASPI)</span>
                            <h3 class="text-sm font-bold text-ink mt-0.5" x-text="selectedChannel?.name || 'QRIS Resmi AgroOrder GPA'"></h3>
                        </div>
                        <button type="button" @click="fullQrisModal = null" 
                            class="rounded-lg p-1.5 text-ink-quiet hover:bg-surface-muted hover:text-ink transition-colors"
                            title="Tutup Modal">
                            <x-gpa.icon name="x" class="h-5 w-5" />
                        </button>
                    </div>

                    {{-- Area Gambar Barcode: Fleksibel & Tidak Terpotong --}}
                    <div class="min-h-0 flex-1 overflow-y-auto py-3 flex flex-col items-center justify-center">
                        <div class="bg-white p-3 rounded-xl shadow-inner border border-line-soft flex items-center justify-center max-w-full">
                            <img :src="fullQrisModal" 
                                onerror="this.onerror=null;this.src='/storage/payment-channels/qris-gpa-default.png'"
                                alt="Barcode QRIS Full" class="max-h-[46vh] sm:max-h-[50vh] w-auto max-w-full object-contain rounded-lg">
                        </div>
                        <p class="mt-2 text-center text-xs text-ink-quiet">
                            Pindai barcode langsung dari aplikasi m-banking atau e-wallet Anda
                        </p>
                    </div>

                    {{-- Tombol Aksi di Bagian Bawah (Selalu Terlihat / Pinned) --}}
                    <div class="pt-3 border-t border-line-soft flex items-center justify-end gap-2.5 shrink-0">
                        <a :href="fullQrisModal" download="QRIS-AgroOrder-GPA.png" 
                            class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-xs font-bold text-ink hover:bg-accent-deep transition-colors shadow-sub">
                            <x-gpa.icon name="download" class="h-4 w-4" /> Unduh Gambar Barcode
                        </a>
                        <button type="button" @click="fullQrisModal = null" 
                            class="rounded-lg border border-line-soft bg-surface-muted px-4 py-2 text-xs font-semibold text-ink hover:bg-line-soft transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection

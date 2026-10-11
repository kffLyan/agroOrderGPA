@extends('layouts.dashboard')

@section('title', 'Unggah Bukti Pembayaran & Verifikasi Tagihan')

@php
    $nodeStyles = [
        'done' => 'bg-success-deep text-white ring-4 ring-surface',
        'active' => 'bg-accent text-ink ring-4 ring-surface',
        'pending' => 'bg-surface-disabled text-ink-body ring-4 ring-surface',
    ];

    $stepTones = [
        'done' => 'text-ink',
        'active' => 'text-ink',
        'pending' => 'text-ink-body',
    ];

    $historyStatusStyles = [
        'warning' => 'bg-warning/10 text-warning-deep ring-warning/40',
        'success' => 'bg-accent text-ink ring-accent',
    ];

    $historyRowStyles = [
        'muted' => 'bg-surface-shell',
        'plain' => 'bg-surface',
    ];
@endphp

@section('content')
    <div x-data="clientPaymentProof(@js([
        'invoices' => $invoices,
        'account' => $account,
        'fileRules' => $fileRules,
        'form' => $form,
        'file' => $file,
        'compliance' => $compliance,
        'selected' => $selected,
    ]))" class="flex flex-col gap-4">
        {{-- ------------------------------------------------------------------ --}}
        {{-- Kembali ke pusat dokumen & kop halaman + chip PRD Rule 11 --}}
        {{-- ------------------------------------------------------------------ --}}
        <a href="{{ route('documents') }}"
            class="inline-flex w-fit items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.55px] text-ink-body transition-colors hover:text-success-deep">
            <x-gpa.icon name="arrow-left" class="h-3 w-3" />
            Kembali ke Pusat Dokumen &amp; Faktur
        </a>

        <section class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-col gap-2">
                <h1 class="gpa-section-title text-ink">{{ $header['title'] }}</h1>
                <p class="max-w-[60rem] text-sm leading-5 text-ink-body">{{ $header['subtitle'] }}</p>
                <p class="max-w-[60rem] text-xs leading-4 text-ink-quiet">{{ $header['source_note'] }}</p>
            </div>

            <span
                class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg bg-warning/10 px-3 py-1.5 ring-1 ring-inset ring-warning/40">
                <x-gpa.icon name="clock" class="h-3 w-3 shrink-0 text-warning-deep" />
                <span class="text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-warning-deep">
                    {{ $header['rule_chip'] }}
                </span>
            </span>
        </section>

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
                            @foreach ($invoices as $invoice)
                                <option value="{{ $invoice['po'] }}" @selected($invoice['po'] === $selected)>
                                    {{ $invoice['option_label'] }}
                                </option>
                            @endforeach
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

                        <div class="pt-2">
                            <div class="flex flex-col gap-2 rounded-lg bg-brand p-3 ring-1 ring-inset ring-accent/30 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-white font-mono text-sm font-bold text-ink">
                                        {{ $account['short'] }}
                                    </span>
                                    <div class="flex min-w-0 flex-col">
                                        <p class="text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-accent">
                                            {{ $account['eyebrow'] }}
                                        </p>
                                        <p class="font-mono text-sm font-bold leading-5 tracking-[0.35px] text-canvas">
                                            {{ $account['account_label'] }}
                                        </p>
                                        <p class="text-xs leading-4 text-surface-disabled">{{ $account['holder'] }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="copyAccount()"
                                    class="inline-flex shrink-0 items-center gap-1.5 rounded bg-accent px-3 py-1.5 text-ink transition-colors hover:bg-accent-deep">
                                    <x-gpa.icon name="copy" class="h-3 w-3 text-ink" />
                                    <span class="text-[11px] font-bold uppercase leading-[14px] tracking-[0.88px] text-ink">
                                        {{ $account['action'] }}
                                    </span>
                                </button>
                            </div>
                            <p class="pt-1.5 text-[10px] leading-4 text-ink-body">{{ $account['note'] }}</p>
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
                            {{ $fileRules['format_label'] }}
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
                        <input id="berkas-slip" type="file" class="sr-only" accept="{{ $fileRules['mime'] }}"
                            @change="onFileInput($event)">
                        <span class="inline-flex">
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
                                Bank &amp; Nama Akun Pengirim:
                            </label>
                            <input id="bank-pengirim" type="text" x-model="form.senderBank" value="{{ $form['sender_bank'] }}" class="gpa-control">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="rekening-pengirim" class="text-xs font-semibold leading-4 text-ink">
                                Nomor Rekening Pengirim:
                            </label>
                            <input id="rekening-pengirim" type="text" x-model="form.senderAccount" value="{{ $form['sender_account'] }}"
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
                            <span class="font-bold text-ink">{{ $compliance['title'] }}</span>
                            {{ $compliance['body'] }}
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

                        @foreach ($steps as $step)
                            <li class="relative flex flex-col gap-1">
                                <span class="absolute -left-6 top-0 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                                    @class([$nodeStyles[$step['tone']]])>
                                    @if ($step['tone'] === 'done')
                                        <x-gpa.icon name="check" class="h-3 w-3" />
                                    @elseif ($step['tone'] === 'active')
                                        <span class="h-2.5 w-2.5 rounded-full bg-ink" aria-hidden="true"></span>
                                    @else
                                        {{ $step['no'] }}
                                    @endif
                                </span>

                                @if ($step['tone'] === 'active')
                                    <div class="rounded-xl border border-success-deep/40 bg-surface-shell p-2">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-xs font-bold leading-4 text-ink">
                                                {{ $step['no'] }}. {{ $step['title'] }}
                                            </p>
                                            <span class="rounded bg-success-deep px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-[1.08px] text-white">
                                                {{ $step['chip'] }}
                                            </span>
                                        </div>
                                        <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-deep">
                                            {{ $step['meta'] }}
                                        </p>
                                        <p class="pt-0.5 text-xs leading-4 text-ink">{{ $step['body'] }}</p>
                                    </div>
                                @else
                                    <div @class(['flex flex-col gap-1', $step['tone'] === 'pending' ? 'opacity-60' : ''])>
                                        <p @class([
                                            'text-xs leading-4',
                                            'font-bold' => $step['tone'] === 'done',
                                            'font-semibold' => $step['tone'] !== 'done',
                                            $stepTones[$step['tone']],
                                        ])>{{ $step['no'] }}. {{ $step['title'] }}</p>
                                        <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                            {{ $step['meta'] }}
                                        </p>
                                        <p class="text-xs leading-4 text-ink-body">{{ $step['body'] }}</p>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ol>

                    <div class="flex items-start gap-3 rounded-xl bg-brand p-4 ring-1 ring-inset ring-accent/30">
                        <x-gpa.icon name="info" class="h-5 w-5 shrink-0 text-accent" />
                        <div class="flex flex-col gap-1">
                            <p class="text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-accent">
                                {{ $notice['eyebrow'] }}
                            </p>
                            <p class="text-xs leading-[1.65] text-surface-disabled">{{ $notice['body'] }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-line-soft bg-surface-shell p-3">
                        <div class="flex items-center gap-2.5">
                            <x-gpa.icon name="phone" class="h-4 w-4 shrink-0 text-success-deep" />
                            <div class="flex flex-col">
                                <p class="text-xs font-semibold leading-4 text-ink">{{ $hotline['title'] }}</p>
                                <p class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                    {{ $hotline['body'] }}
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="contactPic()"
                            class="rounded border border-line-soft bg-surface-pill px-3 py-1 font-mono text-[9px] font-bold uppercase tracking-[1.08px] text-ink transition-colors hover:bg-surface">
                            {{ $hotline['action'] }}
                        </button>
                    </div>
                </section>

                <section class="gpa-card flex flex-col gap-2 p-4">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body">{{ $plafon['title'] }}</h2>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1 rounded-lg bg-surface-shell p-3">
                            <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                {{ $plafon['approved_label'] }}
                            </p>
                            <p class="font-mono text-sm font-bold tracking-[0.28px] text-ink">
                                {{ $plafon['approved_value'] }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-1 rounded-lg bg-surface-shell p-3">
                            <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                {{ $plafon['outstanding_label'] }}
                            </p>
                            <p class="font-mono text-sm font-bold tracking-[0.28px] text-success-deep">
                                {{ $plafon['outstanding_value'] }}
                            </p>
                        </div>
                    </div>

                    <p class="text-[10px] leading-4 text-ink-body">{{ $plafon['note'] }}</p>
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
                    <p class="text-xs leading-4 text-ink-body">{{ $exportReport['body'] }}</p>
                </div>
                <button type="button" @click="exportReport()"
                    class="inline-flex w-fit shrink-0 items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.55px] text-success-deep transition-colors hover:text-ink">
                    <x-gpa.icon name="download" class="h-3 w-3" />
                    {{ $exportReport['label'] }}
                </button>
            </div>

            <div class="-mx-1 overflow-x-auto px-1">
                <table class="w-full min-w-[64rem] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-shell">
                            @foreach ($historyColumns as $column)
                                <th scope="col"
                                    class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-[0.55px] text-ink-body {{ ($column['align'] ?? 'left') === 'right' ? 'text-right' : '' }}">
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $row)
                            <tr @class([
                                'border-b border-line-soft last:border-b-0',
                                $historyRowStyles[$row['row_tone']],
                            ])>
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
                                    <span @class([
                                        'text-xs leading-4',
                                        'italic text-ink-body' => $row['verifier_pending'],
                                        'text-ink' => ! $row['verifier_pending'],
                                    ])>{{ $row['verifier'] }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-[9px] font-bold uppercase tracking-[0.45px] ring-1 ring-inset"
                                        @class([$historyStatusStyles[$row['status_tone']]])>
                                        @if ($row['status_tone'] === 'warning')
                                            <span class="h-1.5 w-1.5 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                        @else
                                            <x-gpa.icon name="check" class="h-2.5 w-2.5" />
                                        @endif
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span @class([
                                        'font-mono text-[9px] font-semibold uppercase tracking-[1.08px]',
                                        'text-ink-quiet' => $row['receipt_pending'],
                                        'text-ink' => ! $row['receipt_pending'],
                                    ])>{{ $row['receipt'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
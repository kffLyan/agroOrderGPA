@extends('layouts.sekretaris')

@section('title', 'Faktur & Tagihan')

@section('content')
    @php
        $clients = $clients ?? [];
        $ledgerRows = $ledgerRows ?? [];
        $totalInvoices = count($ledgerRows);
        $statusFilters = [
            'all'         => 'Semua Status Tagihan',
            'outstanding' => 'Piutang Berjalan',
            'paid'        => 'Sudah Lunas',
        ];

    @endphp

    <div class="space-y-6" x-data="secretaryInvoicing(@js($clients), @js($ledgerRows), @js($statusFilters), @js($totalInvoices), @js(route('prints.invoice', ['invoice' => '__invoice__'])))"
        @gpa:search.window="query = $event.detail">
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0">
                <x-gpa.icon name="invoice" class="h-3 w-3 shrink-0 text-success-deep" />

                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Manajemen Faktur Tagihan &<br />
                    Pemantauan Tempo Pembayaran
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-5 text-ink-body">
                    Konsolidasi Surat Jalan sayur yang telah selesai dikirim menjadi lembar
                    <span class="font-semibold text-ink">faktur penagihan resmi sesuai kesepakatan tempo bayar (14 / 30 / 45 Hari).</span>
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <a href="{{ route('sekretaris.invoices.export') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-surface-shell px-4 py-3 outline outline-1 outline-line-board transition-colors hover:bg-surface-track gpa-meta-lg font-bold tracking-[0.88px] text-ink">
                    <x-gpa.icon name="download" class="h-[15px] w-[15px] shrink-0" />
                    Unduh Laporan Faktur (CSV)
                </a>

                <button type="button" @click="manualInvoice()"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand px-4 py-3 text-white shadow-sub transition-colors hover:bg-brand-deep gpa-meta-lg font-bold tracking-[0.88px]">
                    <x-gpa.icon name="plus" class="h-[15px] w-[15px] shrink-0 text-accent" />
                    + Terbitkan Faktur Baru
                </button>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Kartu 1: Total Piutang Berjalan --}}
            <article class="flex min-h-[16.4rem] flex-col justify-between gap-6 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        TOTAL PIUTANG BERJALAN
                    </h2>
                    <span class="shrink-0 rounded-lg bg-surface-track p-1.5">
                        <x-gpa.icon name="wallet" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    </span>
                </div>

                <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Rp {{ number_format($receivableTotal ?? 0, 0, ',', '.') }}
                </p>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-body">
                        {{ count($ledgerRows) }} Faktur Aktif
                    </p>
                    <span class="inline-flex shrink-0 rounded bg-surface-pill px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body outline outline-1 outline-line-board/70">
                        Termonitor
                    </span>
                </div>
            </article>

            {{-- Kartu 2: Segera Jatuh Tempo --}}
            <article class="flex min-h-[16.4rem] flex-col justify-between gap-6 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        SEGERA JATUH TEMPO (&lt; 7 HARI)
                    </h2>
                    <span class="shrink-0 rounded-lg bg-warning-soft/40 p-1.5">
                        <x-gpa.icon name="clock" class="h-[15px] w-[15px] shrink-0 text-warning-deep" />
                    </span>
                </div>

                <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-warning-deep">
                    Rp {{ number_format($dueSoonTotal ?? 0, 0, ',', '.') }}
                </p>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-warning-deep">
                        Perlu Diingatkan
                    </p>
                    <span class="inline-flex shrink-0 rounded bg-warning-soft/60 px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-warning-deep outline outline-1 outline-warning/50">
                        Perhatian
                    </span>
                </div>
            </article>

            {{-- Kartu 3: Faktur Lewat Tempo --}}
            <article class="flex min-h-[16.4rem] flex-col justify-between gap-6 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        FAKTUR LEWAT TEMPO
                    </h2>
                    <span class="shrink-0 rounded-lg bg-danger-soft/50 p-1.5">
                        <x-gpa.icon name="alert-triangle" class="h-[15px] w-[15px] shrink-0 text-danger" />
                    </span>
                </div>

                <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-danger">
                    Rp 0
                </p>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">
                        Tidak Ada Keterlambatan
                    </p>
                    <span class="inline-flex shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-ink outline outline-1 outline-success-deep">
                        Aman
                    </span>
                </div>
            </article>

            {{-- Kartu 4: Faktur Lunas Bulan Ini --}}
            <article class="flex min-h-[16.4rem] flex-col justify-between gap-6 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        FAKTUR LUNAS BULAN INI
                    </h2>
                    <span class="shrink-0 rounded-lg bg-accent p-1.5">
                        <x-gpa.icon name="check-circle" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    </span>
                </div>

                <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-success-deep">
                    Rp {{ number_format($paidTotal ?? 0, 0, ',', '.') }}
                </p>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">
                        Pembayaran Berhasil Diterima
                    </p>
                    <span class="inline-flex shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-success-deep">
                        Lancar
                    </span>
                </div>
            </article>
        </div>

        <section class="overflow-hidden rounded-2xl shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-brand px-6 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent">
                        <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-ink" />
                    </span>

                    <div class="min-w-0">
                        <h2 class="font-sans text-lg font-bold leading-6 text-surface">
                            Penerbitan Faktur Tagihan Surat Jalan
                        </h2>
                        <p class="gpa-micro font-semibold uppercase tracking-[1.08px] text-accent">
                            Pilih klien dan gabungkan beberapa surat jalan menjadi 1 faktur tagihan resmi.
                        </p>
                    </div>
                </div>

                <span
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-brand-deep/80 px-3 py-1.5 outline outline-1 outline-accent/30">
                    <span class="gpa-micro-bold uppercase tracking-[1.08px] text-surface-disabled">STATUS:</span>
                    <span class="gpa-micro-bold uppercase tracking-[1.08px] text-accent">FAKTUR GABUNGAN OTOMATIS</span>
                </span>
            </div>

            <div class="flex flex-col gap-6 bg-surface p-6">
                <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-soft">
                    <label for="invoice-client" class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-body">
                        Pilih Pembeli / Klien
                    </label>

                    <select id="invoice-client" @change="switchClient($event.target.value)"
                        class="h-11 w-full rounded-lg bg-surface px-3 text-sm font-semibold text-ink outline outline-1 outline-line-board focus:outline-2 focus:outline-success-deep">
                        @foreach ($clients as $client)
                            <option value="{{ $client['id'] }}" @selected($client['id'] === $clients[0]['id'])}>
                                {{ $client['name'] }} [{{ $client['terms_label'] }} Hari] — NPWP: {{ $client['npwp'] }}
                            </option>
                        @endforeach
                    </select>

                    <div class="mt-2 flex h-11 items-center justify-between gap-4 rounded-lg bg-surface px-3 outline outline-1 outline-line-board">
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                            Batas Plafon Kredit:
                            <span class="font-mono text-ink-body" x-text="'// Plafon: ' + rupiah(creditLimit())"></span>
                        </p>
                        <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">
                            SISA PLAFON:
                            <span class="font-mono" x-text="rupiah(creditSisa())">Rp 85.800.000</span>
                        </p>
                    </div>

                    <div class="mt-1 h-1 w-full rounded-full bg-surface-track" role="presentation">
                        <div class="h-1 rounded-full bg-success-deep transition-all" :style="'width: ' + creditPercent() + '%'"></div>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <h3 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink">
                                Surat Jalan Siap Ditagihkan
                            </h3>
                            <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                <span x-text="documents.length + ' Dokumen Valid'">Dokumen Valid</span>
                            </span>
                        </div>

                        <p class="shrink-0 gpa-micro font-semibold uppercase tracking-[1.08px] text-ink-quiet">
                            Tercatat otomatis dari surat jalan yang sudah terbit
                        </p>
                    </div>

                    <div class="gpa-scroll-x rounded-xl outline outline-1 outline-line-board/60">
                        <table class="w-full min-w-[1080px] border-collapse text-left">
                            <thead class="border-b border-line-soft bg-surface-track">
                                <tr>
                                    <th scope="col" class="w-12 px-4 py-3">
                                        <button type="button" @click="toggleAll()" aria-label="Pilih semua surat jalan"
                                            class="h-4 w-4 rounded border border-brand bg-brand transition-colors"
                                            :class="allSelected() ? 'bg-brand' : 'border-line-board bg-surface'"
                                            role="checkbox" :aria-checked="allSelected()"></button>
                                    </th>

                                    <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">No. Surat Jalan</th>
                                    <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Tanggal Kirim</th>
                                    <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Nama Sayur &amp; Berat Bersih</th>
                                    <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Status Penerimaan</th>
                                    <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body text-right">Total Tagihan</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-line-soft">
                                @foreach ($clients as $client)
                                    @foreach ($client['documents'] as $document)
                                        <tr x-show="belongsToClient(@js($document['sj']))"
                                            @click="toggleDocument(@js($document['sj']))"
                                            class="cursor-pointer transition-colors hover:bg-surface-shell/60"
                                            :class="isSelected(@js($document['sj'])) ? 'bg-accent/10' : ''">
                                            <td class="px-4 py-4">
                                                <span class="inline-block h-4 w-4 rounded border border-brand bg-brand transition-colors"
                                                    :class="isSelected(@js($document['sj'])) ? 'bg-brand' : 'border-line-board bg-surface'"
                                                    role="checkbox" :aria-checked="isSelected(@js($document['sj']))"></span>
                                            </td>

                                            <td class="px-4 py-4">
                                                <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">{{ $document['sj'] }}</p>
                                            </td>

                                            <td class="px-4 py-4">
                                                <p class="gpa-note text-ink-body">{{ $document['date'] }}</p>
                                            </td>

                                            <td class="px-4 py-4">
                                                <p class="font-sans text-sm font-semibold leading-5 text-ink">{{ $document['commodity'] }}</p>
                                                <p class="mt-0.5 font-mono text-xs font-medium tracking-[0.88px] text-ink-body">
                                                    {{ $document['weight_label'] }}
                                                </p>
                                            </td>

                                            <td class="px-4 py-4">
                                                <span class="inline-flex items-center gap-1.5 rounded bg-accent/40 px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent">
                                                    <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                                                    {{ $document['pod'] }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-4 text-right font-mono text-sm font-bold tracking-[0.88px] text-ink">
                                                {{ $document['value_label'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-soft">
                        <div class="flex flex-wrap items-center gap-6">
                            <div>
                                <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                                    Total Surat Jalan Dipilih
                                </p>
                                <p class="mt-0.5 font-sans text-sm font-bold leading-5 text-ink" x-text="documentsLabel()">
                                    3 Surat Jalan (1.970 KG)
                                </p>
                            </div>

                            <div class="border-l border-line-board pl-6">
                                <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                                    Kesepakatan Tempo Pembayaran
                                </p>
                                <p class="mt-0.5 font-sans text-sm font-bold leading-5 text-ink" x-text="dueLabel()">
                                    45 Hari Kalender (Jatuh Tempo: 08 Des 2024)
                                </p>
                            </div>

                            <div class="border-l border-line-board pl-6">
                                <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                                    Total Nilai Faktur Tagihan
                                </p>
                                <p class="mt-0.5 font-sans text-lg font-bold leading-6 text-ink" x-text="rupiah(totalValue())">
                                    Rp 114.200.000
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="generate()" :disabled="! selectedDocuments.length"
                            :class="selectedDocuments.length
                                ? 'bg-accent text-ink hover:bg-accent-deep'
                                : 'cursor-not-allowed bg-surface-disabled text-ink-quiet opacity-70'"
                            class="inline-flex items-center gap-2 rounded-xl px-6 py-3 shadow-sub transition-colors gpa-meta-lg font-bold tracking-[0.88px]">
                            <x-gpa.icon name="invoice" class="h-[15px] w-[15px] shrink-0" />
                            Terbitkan Faktur Tagihan Resmi
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold leading-6 text-ink">
                        <x-gpa.icon name="chart" class="h-[18px] w-[18px] shrink-0 text-ink" />
                        Daftar Faktur Tagihan &amp; Pemantauan Tempo
                    </h2>
                    <p class="mt-0.5 text-xs leading-4 text-ink-body">Pemantauan status pembayaran faktur dan sisa hari sebelum jatuh tempo.</p>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 rounded-xl bg-surface-shell p-1 outline outline-1 outline-line-board/50">
                    <button type="button" @click="setTerms('all')"
                        :class="termsFilter === 'all'
                            ? 'bg-brand text-white'
                            : 'text-ink-body hover:bg-surface-disabled'"
                        class="rounded-lg px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        Semua ({{ $totalInvoices }})
                    </button>

                    @foreach ([14, 30, 45] as $days)
                        <button type="button" @click="setTerms('{{ $days }}')"
                            :class="termsFilter === '{{ $days }}'
                                ? 'bg-brand text-white'
                                : 'text-ink-body hover:bg-surface-disabled'"
                            class="rounded-lg px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                            TOP {{ $days }}
                            <span class="opacity-70" x-text="'(' + termCount({{ $days }}) + ')'"></span>
                        </button>
                    @endforeach

                    <button type="button" @click="cycleStatus()"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body transition-colors hover:bg-surface-disabled">
                        <x-gpa.icon name="filter" class="h-3 w-3 shrink-0" />
                        <span x-text="statusLabel()">Semua Status Tagihan</span>
                    </button>
                </div>
            </div>

            <div class="gpa-scroll-x rounded-xl outline outline-1 outline-line-board/60">
                <table class="w-full min-w-[1100px] border-collapse text-left">
                    <thead class="border-b border-line-soft bg-surface-track">
                        <tr>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">No. Faktur &amp; Tanggal</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Nama Pembeli / Klien</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Surat Jalan Terhubung</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Total Tagihan (Rp)</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Tempo Bayar</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Jatuh Tempo</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Sisa Hari</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Status Tagihan</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-soft">
                        @foreach ($ledgerRows as $row)
                            <tr x-show="matchesFilter(@js($row))" @class($row['row_class'])
                                class="transition-colors hover:bg-surface-shell/50">
                                <td class="px-4 py-4">
                                    <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">{{ $row['id'] }}</p>
                                    <p class="mt-0.5 text-2xs font-medium leading-4 text-ink-quiet">
                                        {{ $row['issued'] }}
                                        @if (! empty($row['issued_note']))
                                            <span class="text-ink-body">{{ $row['issued_note'] }}</span>
                                        @endif
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="font-sans text-sm font-bold leading-5 text-ink">{{ $row['client'] }}</p>
                                    <p @class(['mt-0.5 text-2xs leading-4', $row['contract_class']])}>
                                        Kontrak: {{ $row['contract'] }}
                                        @if (! empty($row['contract_note']))
                                            <span class="font-mono">{{ $row['contract_note'] }}</span>
                                        @endif
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <span class="rounded bg-surface-track px-2 py-1 gpa-micro-bold tracking-[1.08px] text-ink-body">
                                        {{ $row['sj_count'] }} SJ
                                    </span>
                                </td>

                                <td class="px-4 py-4 font-mono text-sm font-bold tracking-[0.88px] {{ $row['total_class'] }}">
                                    {{ $row['total_label'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                        $row['terms_class'],
                                    ])>{{ $row['terms_label'] }}</span>
                                </td>

                                <td class="px-4 py-4 font-mono text-xs font-medium tracking-[0.88px] {{ $row['due_class'] }}">
                                    {{ $row['due'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <p class="flex items-center gap-1.5 text-xs font-semibold leading-4 {{ $row['remaining_class'] }}">
                                        <span class="h-2 w-2 shrink-0 rounded-full {{ $row['remaining_dot'] }}" aria-hidden="true"></span>
                                        {{ $row['remaining'] }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                        $row['status_class'],
                                    ])>
                                        <span class="h-2 w-2 shrink-0 rounded-full bg-current opacity-70" aria-hidden="true"></span>
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="viewInvoice(@js($row))" title="Lihat detail faktur"
                                            aria-label="Lihat detail faktur {{ $row['id'] }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-surface-track text-ink transition-colors hover:bg-surface-disabled">
                                            <x-gpa.icon name="eye" class="h-[15px] w-[15px] shrink-0" />
                                        </button>

                                        <button type="button" @click="printInvoice(@js($row))" title="Cetak faktur resmi"
                                            aria-label="Cetak faktur resmi {{ $row['id'] }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-surface-track text-ink transition-colors hover:bg-surface-disabled">
                                            <x-gpa.icon name="printer" class="h-[15px] w-[15px] shrink-0" />
                                        </button>

                                        <button type="button" @click="followUp(@js($row))" title="Tindak lanjut penagihan"
                                            aria-label="Tindak lanjut penagihan {{ $row['id'] }}"
                                            @class([
                                                'inline-flex h-8 w-8 items-center justify-center rounded-lg transition-colors',
                                                $row['highlight'] !== '' ? $row['highlight'] : 'bg-surface-track text-ink hover:bg-surface-disabled',
                                            ])}>
                                            <x-gpa.icon name="alert-triangle" class="h-[15px] w-[15px] shrink-0" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + visibleRows().length + ' dari ' + {{ $totalInvoices }} + ' baris ledger piutang berjalan'">
                </p>
                <p class="gpa-note text-ink-body">
                    Total Piutang Terkonfirmasi:
                    <span class="font-bold text-success-deep">42 Faktur Aktif</span>
                </p>
            </div>
        </section>
    </div>
@endsection
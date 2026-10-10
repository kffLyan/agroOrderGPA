@extends('layouts.director')

@section('title', 'Laporan Penjualan Eksekutif // Arsip Kuartal IV 2026')

@section('content')
    @php
        // Palet nada mengikuti design "Laporan Penjualan Eksekutif" yang memakai
        // token hijau brand dan aksen lime yang sama dengan modul lain.
        $metaTone = [
            'body' => 'text-ink-body',
            'success' => 'text-success-deep',
            'warning' => 'text-warning',
        ];

        $cardValueTone = [
            'ink' => 'text-ink',
            'success' => 'text-success-deep',
        ];

        $cardChipTone = [
            'accent' => 'bg-accent text-ink',
            'warning' => 'bg-warning text-canvas',
        ];

        $footerTone = [
            'ink' => 'text-ink',
            'success' => 'text-success-deep',
            'warning' => 'text-warning',
            'quiet' => 'text-ink-quiet',
        ];

        $filterChipTone = [
            'warning' => 'bg-warning-cream text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border',
            'neutral' => 'bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line',
        ];

        $statusTone = [
            'lancar' => 'bg-warning-cream text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border',
            'lunas' => 'bg-accent/50 text-success-deep outline outline-1 -outline-offset-1 outline-accent-deep',
        ];

        $ledgerFont = 'font-mono tracking-[0.2px]';
    @endphp

    <div class="flex flex-col gap-5"
        x-data="directorReport(@js($commodities['rows']), @js($clients['rows']), @js($audit['entries']), @js($seal))">

        {{-- ---------- Banner arsip terkunci ---------- --}}
        <section class="relative overflow-hidden rounded-xl bg-brand p-6 text-white shadow-card">

            <div class="relative flex flex-col gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1 text-[10px] font-bold tracking-[0.88px] text-ink gpa-meta">
                        <x-gpa.icon name="lock" class="h-3 w-3" />
                        {{ $lock['badge'] }}
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[10px] font-semibold tracking-[0.88px] text-white/80 gpa-meta">
                        <x-gpa.icon name="shield" class="h-3 w-3" />
                        {{ $lock['certificate_chip'] }}
                    </span>
                </div>

                <div class="flex flex-col gap-3">
                    <h1 class="font-inter text-2xl font-extrabold uppercase tracking-[0.6px] text-white sm:text-[28px]">
                        Laporan Penjualan Eksekutif
                    </h1>
                    <p class="max-w-3xl text-[13px] leading-6 text-white/70">
                        Periode <span class="font-semibold text-white">{{ $lock['period'] }}</span> telah
                        dikunci permanen pada
                        <span class="font-semibold text-accent">{{ $lock['locked_at'] }}</span>
                        oleh <span class="font-semibold text-accent">{{ $lock['signatory'] }}</span>,
                        {{ $lock['signatory_role'] }}. Seluruh transaksi, Surat Jalan, Timbangan Aktual, dan
                        Faktur Tempo berstatus <span class="font-semibold text-white">read-only</span> dan
                        dilindungi hash kriptografi:
                    </p>
                    <p class="{{ $ledgerFont }} max-w-4xl break-all rounded-lg bg-brand-deep px-3 py-2 text-[11px] text-accent">
                        {{ $lock['hash_label'] }} {{ $lock['hash'] }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <button type="button" @click="revealAudit()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-[11px] font-bold tracking-[0.88px] text-ink shadow-sub transition-colors hover:bg-accent-deep gpa-meta">
                        <x-gpa.icon name="clipboard" class="h-3.5 w-3.5" />
                        {{ $lock['audit_button'] }}
                    </button>
                    <button type="button" @click="verifyIntegrity()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-4 py-2 text-[11px] font-bold tracking-[0.88px] text-white transition-colors hover:bg-white/20 gpa-meta">
                        <x-gpa.icon name="badge-check" class="h-3.5 w-3.5" />
                        VERIFY SHA-256
                    </button>
                </div>
            </div>
        </section>

        {{-- ---------- Filter beku ---------- --}}
        <section class="rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-ink-subtle">
                    <x-gpa.icon name="filter" class="h-4 w-4" />
                </span>
                <span class="text-[10px] font-bold uppercase tracking-[1.08px] text-ink-subtle gpa-micro-bold">
                    {{ $filters['label'] }}
                </span>

                @foreach ($filters['items'] as $item)
                    <div
                        class="flex min-w-0 flex-wrap items-center gap-2 rounded-lg border border-line-faint bg-surface-base px-3 py-2">
                        <span class="text-[10px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                            {{ $item['label'] }}
                        </span>
                        <span class="text-[12px] font-semibold text-ink">{{ $item['value'] }}</span>
                        <span
                            class="rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] {{ $filterChipTone[$item['chip_tone']] ?? $filterChipTone['neutral'] }}">
                            {{ $item['chip'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ---------- Empat kartu KPI ---------- --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="KPI eksekutif">
            @foreach ($cards as $card)
                <article class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                            {{ $card['label'] }}
                        </h2>
                        <span
                            class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] {{ $cardChipTone[$card['chip_tone']] ?? $cardChipTone['accent'] }}">
                            {{ $card['chip'] }}
                        </span>
                    </div>

                    <p class="font-inter text-[22px] font-extrabold leading-7 {{ $cardValueTone[$card['value_tone']] ?? $cardValueTone['ink'] }}">
                        {{ $card['value'] }}
                    </p>

                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] leading-5">
                        @foreach ($card['meta'] as $line)
                            <span class="{{ $metaTone[$line['tone']] ?? $metaTone['body'] }}">{{ $line['text'] }}</span>
                        @endforeach
                    </div>

                    <div
                        class="mt-auto flex flex-wrap items-center justify-between gap-x-3 border-t border-line-faint pt-3 text-[10px] leading-4">
                        <span class="font-semibold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                            {{ $card['footer_label'] }}
                        </span>
                        <span class="text-right font-semibold {{ $footerTone[$card['footer_tone']] ?? $footerTone['ink'] }}">
                            {{ $card['footer_value'] }}
                        </span>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- ---------- Tabel 1: Komoditas ---------- --}}
        <section class="overflow-hidden rounded-xl border border-line-hair bg-surface shadow-sub">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <h2 class="text-[13px] font-bold leading-5 text-ink">{{ $commodities['title'] }}</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-warning-cream px-2.5 py-1 text-[9px] font-bold tracking-[0.88px] text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border">
                        {{ $commodities['locked_chip'] }}
                    </span>
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-hair bg-surface-base">
                            @foreach ($commodities['columns'] as $index => $column)
                                <th scope="col"
                                    class="px-4 py-3 text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold {{ $index === 0 ? 'sticky left-0 z-10 bg-surface-base' : 'text-right' }}">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($commodities['rows'] as $row)
                            <tr class="border-b border-line-faint transition-colors hover:bg-surface-base">
                                <th scope="row"
                                    class="sticky left-0 z-10 bg-surface px-4 py-3 text-[12px] font-semibold text-ink">
                                    {{ $row['name'] }}
                                </th>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['po_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-ink {{ $ledgerFont }}">{{ $row['tera_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-warning {{ $ledgerFont }}">{{ $row['deviation_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body">{{ $row['price_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-ink">{{ $row['revenue_label'] }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="flex items-center justify-end gap-2">
                                        <span class="hidden h-1.5 w-12 overflow-hidden rounded-full bg-surface-track sm:block"
                                            aria-hidden="true">
                                            <span class="block h-full rounded-full bg-brand-line"
                                                :style="`width: ${contributionWidth(commodities[{{ $loop->index }}])}`"></span>
                                        </span>
                                        <span class="text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['contribution_label'] }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-[12px] text-success-deep">{{ $row['margin_label'] }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span
                                        class="rounded-full bg-accent/50 px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] text-success-deep outline outline-1 -outline-offset-1 outline-accent-deep">
                                        {{ $commodities['audited_chip'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface-shell">
                        <tr class="border-t-2 border-brand-line">
                            <th scope="row" class="sticky left-0 z-10 bg-surface-shell px-4 py-3 text-[12px] font-bold text-ink">
                                {{ $commodities['total_label'] }}
                            </th>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ $commodities['total_po_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ $commodities['total_tera_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-warning {{ $ledgerFont }}">{{ $commodities['total_deviation_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink">{{ $commodities['total_price_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[13px] font-extrabold text-ink">{{ $commodities['total_revenue_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ $commodities['total_contribution_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-success-deep">{{ $commodities['total_margin_label'] }}</td>
                            <td class="px-4 py-3 text-right">
                                <span
                                    class="rounded-full bg-accent px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] text-ink">
                                    FINAL
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- ---------- Tabel 2: Klien B2B ---------- --}}
        <section class="overflow-hidden rounded-xl border border-line-hair bg-surface shadow-sub">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <h2 class="text-[13px] font-bold leading-5 text-ink">{{ $clients['title'] }}</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-accent px-2.5 py-1 text-[9px] font-bold tracking-[0.88px] text-ink">
                        {{ $clients['audit_chip'] }}
                    </span>
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1160px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-hair bg-surface-base">
                            @foreach ($clients['columns'] as $index => $column)
                                <th scope="col"
                                    class="px-4 py-3 text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold {{ $index === 0 ? 'sticky left-0 z-10 bg-surface-base' : 'text-right' }}">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clients['rows'] as $row)
                            <tr class="border-b border-line-faint transition-colors hover:bg-surface-base">
                                <th scope="row" class="sticky left-0 z-10 bg-surface px-4 py-3">
                                    <span class="block text-[12px] font-semibold leading-5 text-ink">{{ $row['name'] }}</span>
                                    <span class="block text-[10px] leading-4 text-ink-quiet {{ $ledgerFont }}">{{ $row['code'] }}</span>
                                </th>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['po_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['volume_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink">{{ $row['gross_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-success-deep">{{ $row['settled_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-warning">{{ $row['receivable_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body">{{ $row['term_label'] }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] {{ $statusTone[$row['status_tone']] ?? $statusTone['lancar'] }}">
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface-shell">
                        <tr class="border-t-2 border-brand-line">
                            <th scope="row" class="sticky left-0 z-10 bg-surface-shell px-4 py-3 text-[12px] font-bold text-ink">
                                {{ $clients['total_label'] }}
                            </th>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ $clients['total_po_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ $clients['total_volume_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink">{{ $clients['total_gross_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-success-deep">{{ $clients['total_settled_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-warning">{{ $clients['total_receivable_label'] }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink">{{ $clients['average_term_label'] }}</td>
                            <td class="px-4 py-3 text-right">
                                <span
                                    class="rounded-full bg-accent px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] text-ink">
                                    {{ $clients['verified_label'] }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- ---------- Audit trail & sertifikat ---------- --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-5">
            <article class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-sub xl:col-span-2">
                <div class="flex items-start gap-3">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent text-ink-strong ring-1 ring-inset ring-accent-deep">
                        <x-gpa.icon name="qrcode" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-[13px] font-bold leading-5 text-ink">{{ $seal['title'] }}</h2>
                        <p class="text-[10px] leading-4 text-success-deep gpa-meta">{{ $seal['certificate'] }}</p>
                    </div>
                </div>

                <dl class="flex flex-col gap-2 text-[11px] leading-5">
                    <div class="flex flex-col gap-0.5">
                        <dt class="text-[9px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                            {{ $seal['authority_label'] }}
                        </dt>
                        <dd class="font-semibold text-ink">{{ $seal['signatory'] }}</dd>
                        <dd class="text-ink-quiet">{{ $seal['signatory_role'] }}</dd>
                    </div>

                    <div class="flex flex-col gap-0.5">
                        <dt class="text-[9px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                            {{ $seal['lock_time_label'] }}
                        </dt>
                        <dd class="text-ink-body {{ $ledgerFont }}">{{ $seal['lock_time'] }}</dd>
                    </div>

                    <div class="flex flex-col gap-0.5">
                        <dt class="text-[9px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                            {{ $seal['encryption_label'] }}
                        </dt>
                        <dd class="text-ink-body">{{ $seal['encryption'] }}</dd>
                    </div>

                    <div class="flex flex-col gap-0.5">
                        <dt class="text-[9px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                            {{ $seal['hash_label'] }}
                        </dt>
                        <dd class="flex items-start gap-2">
                            <span class="min-w-0 flex-1 break-all rounded-md bg-brand-deep px-2 py-1.5 text-[10px] text-accent {{ $ledgerFont }}">
                                {{ $seal['hash'] }}
                            </span>
                            <button type="button" @click="copyHash()"
                                class="shrink-0 rounded-md bg-surface-track p-2 text-ink-body transition-colors hover:bg-surface-raised hover:text-ink"
                                aria-label="Salin hash SHA-256">
                                <x-gpa.icon name="copy" class="h-3.5 w-3.5" />
                            </button>
                        </dd>
                    </div>
                </dl>

                <div class="mt-auto flex flex-col gap-2 pt-1">
                    <button type="button" @click="exportWorkbook()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-[11px] font-bold tracking-[0.88px] text-ink shadow-sub transition-colors hover:bg-accent-deep gpa-meta">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                        {{ $seal['excel_action'] }}
                    </button>
                    <button type="button" @click="printSeal()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-[11px] font-bold tracking-[0.88px] text-accent shadow-sub transition-colors hover:bg-brand-hover gpa-meta">
                        <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                        {{ $seal['pdf_action'] }}
                    </button>
                    <p class="flex items-center justify-center gap-1.5 text-center text-[9px] font-bold tracking-[0.88px] text-success-deep gpa-micro-bold">
                        <x-gpa.icon name="shield" class="h-3 w-3" />
                        {{ $seal['locked_status'] }}
                    </p>
                </div>
            </article>
        </section>
    </div>
@endsection

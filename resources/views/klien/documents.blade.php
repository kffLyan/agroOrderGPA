@extends('layouts.klien')

@section('title', 'Pusat Dokumen & Faktur Konsolidasi Klien B2B')

@php
    $columnWidths = [
        'no' => 'w-[3.25rem]',
        'invoice' => 'w-[8.5rem]',
        'period' => 'w-[7.5rem]',
        'netto' => 'w-[6.25rem]',
        'total' => 'w-[7rem]',
        'due' => 'w-[6.5rem]',
        'status' => 'w-[10rem]',
        'actions' => 'w-[9rem]',
    ];

    $nettoWeights = [
        400 => 'font-normal',
        500 => 'font-medium',
        600 => 'font-semibold',
        700 => 'font-bold',
    ];

    $totalWeights = $nettoWeights;

    $metricIconTones = [
        'warning' => 'bg-warning-soft text-warning-deep',
        'accent' => 'bg-accent text-ink',
        'accent-soft' => 'bg-accent/50 text-ink',
        'neutral' => 'bg-surface-pill text-ink',
    ];

    $statusStyles = [
        'accent' => 'bg-accent text-success-ink ring-accent',
        'warning' => 'bg-warning-soft text-warning-deep ring-warning/40',
        'muted' => 'bg-surface-pill text-ink-quiet ring-line-board',
    ];

    $rowStyles = [
        'active' => 'bg-canvas border-l-4 border-ink',
        'plain' => 'bg-surface',
        'muted' => 'bg-surface opacity-80',
    ];
@endphp

@section('content')
    <div x-data="clientDocuments(@js([
        'invoices' => $invoices,
        'tabs' => $tabs,
        'counts' => $archiveCounts,
        'periods' => $periods,
        'pagination' => $pagination,
    ]))" class="flex flex-col gap-4">
        {{-- ------------------------------------------------------------------ --}}
        {{-- Kop halaman + aksi unduh rekap pajak & rekonsiliasi --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-col gap-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="gpa-section-title text-ink">{{ $header['title'] }}</h1>
                    <span
                        class="rounded-full bg-accent px-2.5 py-0.5 text-[10px] font-bold uppercase leading-3 tracking-[0.1em] text-ink ring-1 ring-inset ring-accent">
                        {{ $header['chip'] }}
                    </span>
                </div>
                <p class="max-w-[56rem] text-sm leading-5 text-ink-body">{{ $header['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @foreach ($header['actions'] as $action)
                    <button type="button" @click="runAction(@js($action))"
                        @class([
                            'inline-flex items-center gap-1.5 rounded-lg px-4 py-2 transition-colors',
                            'bg-surface-pill text-ink-body shadow-sub hover:bg-surface-disabled hover:text-ink' => $action['variant'] === 'outline',
                            'bg-ink text-white hover:bg-brand-deep' => $action['variant'] === 'solid',
                        ])>
                        <x-gpa.icon :name="$action['icon']"
                            class="h-3 w-3 {{ $action['variant'] === 'solid' ? 'text-white' : 'text-ink-body' }}" />
                        <span @class([
                            'gpa-meta-lg uppercase tracking-[0.55px]',
                            'font-medium' => $action['variant'] === 'outline',
                            'font-bold' => $action['variant'] === 'solid',
                        ])>{{ $action['label'] }}</span>
                    </button>
                @endforeach

                {{-- Dropdown "Unggah Bukti": jalan pintas ke formulir bukti pembayaran --}}
                <div class="relative" @click.outside="proofMenuOpen = false" @keydown.escape.window="proofMenuOpen = false">
                    <button type="button" @click="toggleProofMenu()" :aria-expanded="proofMenuOpen.toString()"
                        aria-haspopup="true"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="upload" class="h-3 w-3 text-ink" />
                        <span class="gpa-meta-lg font-bold uppercase tracking-[0.55px] text-ink">Unggah Bukti</span>
                        <span :class="proofMenuOpen ? 'rotate-180' : ''" class="transition-transform">
                            <x-gpa.icon name="chevron-down" class="h-3 w-3 text-ink" />
                        </span>
                    </button>

                    <div x-cloak x-show="proofMenuOpen" x-transition.origin.top.right
                        class="absolute right-0 z-30 mt-2 w-[19rem] overflow-hidden rounded-xl border border-line-soft bg-surface shadow-pop">
                        @foreach ($proofMenu as $item)
                            @if ($item['href'])
                                <a href="{{ $item['href'] }}"
                                    class="flex items-start gap-2.5 border-b border-line-soft px-3 py-2.5 transition-colors last:border-b-0 hover:bg-surface-shell">
                                    <span @class([
                                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                                        'bg-accent text-ink' => $item['variant'] === 'primary',
                                        'bg-surface-pill text-ink-body' => $item['variant'] !== 'primary',
                                    ])>
                                        <x-gpa.icon :name="$item['icon']" class="h-3.5 w-3.5" />
                                    </span>
                                    <span class="flex min-w-0 flex-col">
                                        <span class="text-xs font-bold leading-4 text-ink">{{ $item['label'] }}</span>
                                        <span class="text-[10px] leading-4 text-ink-body">{{ $item['note'] }}</span>
                                    </span>
                                </a>
                            @else
                                <button type="button"
                                    @click="{{ $item['icon'] === 'copy' ? 'copySettlementAccount()' : 'runProofMenuAction(@js($item))' }}"
                                    class="flex w-full items-start gap-2.5 border-b border-line-soft px-3 py-2.5 text-left transition-colors last:border-b-0 hover:bg-surface-shell">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-pill text-ink-body">
                                        <x-gpa.icon :name="$item['icon']" class="h-3.5 w-3.5" />
                                    </span>
                                    <span class="flex min-w-0 flex-col">
                                        <span class="text-xs font-bold leading-4 text-ink">{{ $item['label'] }}</span>
                                        <span class="text-[10px] leading-4 text-ink-body">{{ $item['note'] }}</span>
                                    </span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Empat kartu metrik payable, settled, vault, adjustment --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="gpa-card flex flex-col justify-between gap-3 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro-bold uppercase text-ink-body">{{ $metric['label'] }}</h2>
                        <span @class([
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-lg',
                            $metricIconTones[$metric['tone']],
                        ])>
                            <x-gpa.icon :name="$metric['icon']" class="h-3.5 w-3.5" />
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <p class="text-xs font-medium leading-4 text-ink-body">{{ $metric['title'] }}</p>
                        <p class="pb-1 text-2xl font-bold leading-8 text-ink">{{ $metric['value'] }}</p>
                    </div>
                    <dl class="flex items-start justify-between gap-2 border-t border-line-soft/60 pt-3">
                        <dt class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                            {{ $metric['foot_left'] }}
                        </dt>
                        <dd @class([
                            'text-right text-[9px] font-semibold uppercase leading-3 tracking-[1.08px]',
                            'text-success-ink' => $metric['tone'] !== 'neutral',
                            'text-success-deep' => $metric['tone'] === 'neutral',
                        ])>{{ $metric['foot_right'] }}</dd>
                    </dl>
                </article>
            @endforeach
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Tab arsip dokumen + filter periode rekap --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="flex flex-col gap-3 border-b border-line-soft pb-1 xl:flex-row xl:items-center xl:justify-between">
            <div class="gpa-scroll-x overflow-hidden" role="tablist" aria-label="Arsip dokumen & faktur">
                <div class="flex items-center gap-2">
                    @foreach ($tabs as $tab)
                        <button type="button" role="tab" @click="selectTab(@js($tab['value']))"
                            :aria-selected="tab === @js($tab['value']) ? 'true' : 'false'"
                            class="inline-flex shrink-0 items-center gap-1.5 border-b-2 px-4 py-3 transition-colors"
                            :class="tab === @js($tab['value'])
                                ? 'border-ink bg-surface-shell/50'
                                : 'border-transparent hover:border-line-soft hover:bg-surface-muted'">
                            <x-gpa.icon :name="$tab['icon']" class="h-3.5 w-3.5 shrink-0"
                                x-bind:class="tab === @js($tab['value']) ? 'text-ink' : 'text-ink-quiet'" />
                            <span class="text-[11px] uppercase leading-[14px] tracking-[0.55px]"
                                x-bind:class="tab === @js($tab['value']) ? 'font-bold text-ink' : 'font-medium text-ink-quiet'">
                                {{ $tab['label'] }}
                            </span>
                            @if (! empty($tab['badge']))
                                <span class="rounded bg-accent px-1.5 py-0.5 text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink">
                                    {{ $tab['badge'] }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <label class="flex shrink-0 items-center gap-2 pb-2 xl:pb-0">
                <span class="gpa-meta-lg text-ink-body">Periode:</span>
                <span class="relative inline-flex items-center">
                    <x-gpa.icon name="calendar" class="pointer-events-none absolute left-2 h-3.5 w-3.5 text-ink-body" />
                    <select x-model="period"
                        class="gpa-control h-7 w-[15.5rem] appearance-none rounded border-line-soft bg-surface py-1 pl-8 pr-2 text-xs font-medium text-ink">
                        @foreach ($periods as $period)
                            <option value="{{ $period['value'] }}">{{ $period['label'] }}</option>
                        @endforeach
                    </select>
                </span>
            </label>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Tabel faktur konsolidasi --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line-soft bg-surface-shell/50 p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-2">
                    <x-gpa.icon name="invoice" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-ink" />
                    <div class="flex min-w-0 flex-col">
                        <h2 class="gpa-section-title text-ink" x-text="archiveLabel()">
                            {{ $tabs[0]['label'] }}
                        </h2>
                        <p class="gpa-meta-lg text-ink-body">Menampilkan tagihan periodik berlisensi resmi GPA Finance</p>
                        <p x-cloak x-show="tabNotice()" x-text="tabNotice()" class="pt-1 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-quiet"></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative flex w-full items-center sm:w-64">
                        <span class="sr-only">Cari No. Faktur / SJ...</span>
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-3 h-3 w-3 text-ink-body" />
                        <input type="search" x-model.debounce.200ms="search" placeholder="Cari No. Faktur / SJ..."
                            class="gpa-control h-8 w-full rounded-lg border-line-soft bg-surface pl-8 pr-3 text-xs">
                    </label>
                    <button type="button" @click="resetFilters()" x-cloak x-show="hasFilters()"
                        class="inline-flex h-8 items-center rounded-lg border border-line-soft bg-surface px-2 transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="filter" class="h-3 w-3 text-ink-body" />
                        <span class="sr-only">Reset filter periode dan pencarian</span>
                    </button>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[68rem] border-collapse">
                    <caption class="sr-only">Daftar faktur konsolidasi tempo bulanan beserta netto riil, jatuh tempo, dan status pembayaran</caption>
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-pill">
                            @foreach ($columns as $column)
                                <th scope="col" @class([
                                        $columnWidths[$column['key']] ?? '',
                                        'px-4 py-3 gpa-micro-bold uppercase tracking-[0.45px] text-ink-body',
                                        'text-right' => $column['align'] === 'right',
                                        'text-left' => $column['align'] !== 'right' && $column['align'] !== 'center',
                                        'text-center' => $column['align'] === 'center',
                                    ])>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr @click="selectInvoice(@js($invoice['po']))"
                                x-show="isVisible(@js($invoice))"
                                class="cursor-pointer border-b border-line-soft/70 transition-shadow"
                                :class="isSelected(@js($invoice['po'])) ? 'ring-2 ring-inset ring-accent' : ''"
                                @class([
                                    'border-l-4 border-ink bg-canvas' => $invoice['row_tone'] === 'active',
                                    'bg-surface' => $invoice['row_tone'] !== 'active',
                                    'opacity-80' => $invoice['row_tone'] === 'muted',
                                ])>
                                <td class="px-4 py-4 text-[11px] font-semibold tracking-[0.88px] text-ink-body">
                                    {{ $invoice['no'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-xs font-bold leading-4 text-ink">{{ $invoice['po'] }}</p>
                                    <p class="text-[10px] leading-4 text-ink-body">Cetak: {{ $invoice['printed'] }}</p>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-xs font-medium leading-4 text-ink">{{ $invoice['period_label'] }}</p>
                                    <span @class([
                                        'mt-1 inline-block rounded px-1.5 py-0.5 text-[10px] leading-4',
                                        'bg-surface-pill font-semibold text-ink-body' => ! $invoice['sj_chip_muted'],
                                        'font-normal text-ink-quiet' => $invoice['sj_chip_muted'],
                                    ])>{{ $invoice['sj_chip'] }}</span>
                                </td>

                                <td class="px-4 py-4 text-right text-xs leading-4 text-ink {{ $nettoWeights[$invoice['netto_weight']] ?? 'font-normal' }}">
                                    {{ $invoice['netto_value'] }} {{ $invoice['netto_unit'] }}
                                </td>

                                <td class="px-4 py-4 text-right text-xs leading-4 text-ink {{ $totalWeights[$invoice['total_weight']] ?? 'font-normal' }}">
                                    {{ $invoice['total_label'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <p @class([
                                        'text-xs font-medium leading-4',
                                        'text-ink' => ! $invoice['due_muted'],
                                        'text-ink-body' => $invoice['due_muted'] && ! $invoice['due_struck'],
                                        'text-ink-quiet' => $invoice['due_muted'],
                                        'line-through' => $invoice['due_struck'],
                                    ])>{{ $invoice['due'] }}</p>
                                    <p @class([
                                        'text-[10px] font-bold leading-4',
                                        'text-success-deep' => $invoice['due_note_tone'] === 'success',
                                        'text-ink-quiet' => $invoice['due_note_tone'] !== 'success',
                                    ])>{{ $invoice['due_note'] }}</p>
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] ring-1 ring-inset',
                                        $statusStyles[$invoice['status_tone']],
                                    ])>
                                        @if ($invoice['status_kind'] === 'dot')
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                        @elseif ($invoice['status_kind'] === 'check')
                                            <x-gpa.icon name="check" class="h-2.5 w-2.5 shrink-0" />
                                        @endif
                                        {{ $invoice['status'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @foreach ($invoice['actions'] as $action)
                                            @if ($action['shape'] === 'label')
                                                <a href="{{ $action['href'] ?? route('klien.payment-proof', ['tagihan' => $invoice['po']]) }}"
                                                    @click.stop
                                                    class="inline-block rounded bg-ink px-2.5 py-1 text-[10px] font-bold uppercase leading-4 text-white transition-colors hover:bg-brand-deep">
                                                    {{ $action['label'] }}
                                                </a>
                                            @else
                                                <button type="button" @click.stop="runAction(@js($action), @js($invoice))"
                                                    class="inline-flex h-6 w-6 items-center justify-center rounded p-1 text-ink transition-colors hover:bg-surface-muted">
                                                    <x-gpa.icon :name="$action['icon']" class="h-3.5 w-3.5" />
                                                    <span class="sr-only">{{ $action['label'] }} — {{ $invoice['po'] }}</span>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p x-cloak x-show="visibleCount() === 0" class="px-6 py-10 text-center gpa-body text-ink-body">
                Tidak ada faktur yang cocok dengan filter aktif.
            </p>

            <div class="flex flex-col gap-3 border-t border-line-soft bg-surface-shell px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="gpa-micro-bold uppercase leading-3 tracking-[1.08px] text-ink-body" x-text="summaryLabel()">
                    Menampilkan {{ $pagination['per_page'] }} dari {{ $pagination['total'] }} total faktur konsolidasi (2026)
                </p>

                <nav class="flex items-center gap-1" aria-label="Navigasi halaman faktur">
                    <button type="button" @click="page > 1 && goToPage(page - 1)" :disabled="page === 1"
                        class="rounded bg-surface px-2 py-1 text-xs leading-4 text-ink-body outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-50">
                        Sebelumnya
                    </button>

                    <template x-for="number in pageNumbers()" :key="number">
                        <button type="button" @click="goToPage(number)"
                            class="rounded px-2 py-1 text-xs leading-4 transition-colors"
                            :class="page === number ? 'bg-ink font-bold text-white' : 'bg-surface-pill text-ink-body hover:bg-surface-disabled'"
                            :aria-current="page === number ? 'page' : null"
                            x-text="number"></button>
                    </template>

                    @if ($pagination['last_page'] > 3)
                        <span class="px-1 text-xs leading-4 text-ink-body" aria-hidden="true">...</span>
                    @endif

                    <button type="button" @click="goToPage(page + 1)" :disabled="page === lastPage"
                        class="rounded bg-surface px-2 py-1 text-xs leading-4 text-ink-body outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-50">
                        Selanjutnya
                    </button>
                </nav>
            </div>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Panel lampiran faktur resmi (berikutan dengan baris tabel terpilih) --}}
        {{-- ------------------------------------------------------------------ --}}
        @foreach ($invoices as $invoice)
            <section x-cloak x-show="isSelected(@js($invoice['po']))"
                class="gpa-card overflow-hidden ring-2 ring-inset ring-ink">
                <div class="flex flex-col gap-2 bg-brand p-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 flex-col gap-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="gpa-section-title text-white">{{ $detail['title'] }}</h2>
                            <span class="rounded bg-accent px-1.5 py-1 text-[9px] font-bold uppercase leading-3 text-ink">
                                {{ $detail['badge'] }}
                            </span>
                        </div>
                        <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-accent/60">
                            No. Faktur:
                            <span class="text-accent">{{ $invoice['po'] }}</span>
                            &bull; Tempo {{ $invoice['tempo_days'] }} Hari
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <button type="button" @click="runAction(@js(['label' => 'Cetak Lampiran', 'variant' => 'ghost']), @js($invoice))"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-ink transition-colors hover:bg-brand-deep">
                            <x-gpa.icon name="save" class="h-3.5 w-3.5 text-white" />
                            <span class="sr-only">Cetak lampiran {{ $invoice['po'] }}</span>
                        </button>
                        <button type="button" @click="runAction(@js(['label' => 'Unduh Lampiran PDF', 'variant' => 'ghost']), @js($invoice))"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-ink transition-colors hover:bg-brand-deep">
                            <x-gpa.icon name="download" class="h-3 w-3 text-white" />
                            <span class="sr-only">Unduh lampiran {{ $invoice['po'] }}</span>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-b border-line-soft bg-surface-pill px-4 py-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-medium leading-4 text-ink-body">
                        Rekap {{ $invoice['sj_count'] }} Surat Jalan Fisik ({{ $detail['strip'] }}):
                    </p>
                    <p class="text-xs font-bold leading-4 text-ink">{{ $invoice['netto_net_label'] }}</p>
                </div>

                <div class="flex flex-col gap-2.5 p-4">
                    @foreach ($invoice['attachments'] as $item)
                        <article class="flex items-start justify-between gap-3 rounded-xl border border-line-soft bg-surface-pill p-2.5">
                            <div class="flex min-w-0 items-start gap-2">
                                <span class="flex h-6 w-7 shrink-0 items-center justify-center rounded bg-accent/50 text-sm font-bold leading-5 text-success-deep">
                                    {{ $item['no'] }}
                                </span>
                                <div class="flex min-w-0 flex-col">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="text-xs font-bold leading-4 text-ink">{{ $item['sj'] }}</p>
                                        <span class="inline-flex items-center gap-1 rounded bg-accent px-1 py-0.5">
                                            <x-gpa.icon name="check" class="h-2 w-2 text-success-ink" />
                                            <span class="text-[9px] font-bold leading-3 text-success-ink">{{ $item['pod_chip'] }}</span>
                                        </span>
                                    </div>
                                    <p class="pt-0.5 text-xs font-medium leading-4 text-ink-body">{{ $item['commodity'] }}</p>
                                    <p class="text-[11px] leading-5 text-ink-body">
                                        {{ $item['hub'] }} &bull; TTD: {{ $item['ttd'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-col items-end text-right">
                                <p class="text-xs font-bold leading-4 text-ink">{{ $item['amount_label'] }}</p>
                                <p class="text-xs font-semibold leading-4 text-success-deep">{{ $item['weight_label'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-col gap-3 border-t border-line-soft bg-surface-shell p-4">
                    <dl class="flex flex-col gap-1.5 border-b border-line-soft pb-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">Subtotal {{ $invoice['sj_count'] }} Surat Jalan:</dt>
                            <dd class="text-xs font-semibold leading-4 text-ink">{{ $invoice['subtotal_label'] }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">{{ $invoice['ppn_label'] }}:</dt>
                            <dd class="text-xs leading-4 text-ink-body">{{ $invoice['ppn_value'] }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">{{ $invoice['credit_label'] }}:</dt>
                            <dd class="text-xs font-semibold leading-4 text-success-deep">{{ $invoice['credit_value'] }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 pt-1">
                            <dt class="text-base font-bold leading-6 text-ink">Total Tagihan Tempo (TOP 30D):</dt>
                            <dd class="rounded bg-ink px-2 py-0.5 text-base font-bold leading-6 text-white">
                                {{ $invoice['grand_total_label'] }}
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-col gap-3 rounded-xl border border-line-soft bg-surface p-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 flex-col">
                            <p class="text-[10px] font-bold uppercase leading-5 text-ink-body">{{ $bank['label'] }}</p>
                            <p class="text-xs font-bold leading-4 text-ink">{{ $bank['bank'] }}</p>
                            <p class="text-xs font-semibold leading-4 tracking-[0.6px] text-ink">{{ $bank['account'] }}</p>
                            <p class="text-[10px] leading-5 text-ink-body">{{ $bank['holder'] }}</p>
                        </div>
                        <a href="{{ route('klien.payment-proof', ['tagihan' => $invoice['po']]) }}"
                            class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-accent px-3 py-1.5 text-ink transition-colors hover:bg-accent-deep">
                            <x-gpa.icon name="upload" class="h-3 w-3 text-ink" />
                            <span class="text-[11px] font-bold uppercase leading-[14px] tracking-[0.88px] text-ink">
                                {{ $bank['action'] }}
                            </span>
                        </a>
                    </div>
                </div>
            </section>
        @endforeach

        {{-- ------------------------------------------------------------------ --}}
        {{-- Jaminan validasi hukum & timbangan riil --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card mt-6 flex flex-col gap-3 p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand">
                    <x-gpa.icon name="shield" class="h-6 w-6 text-accent" />
                </span>
                <div class="flex min-w-0 flex-col gap-0.5">
                    <h2 class="gpa-section-title text-ink">{{ $assurance['title'] }}</h2>
                    <p class="max-w-[42rem] text-xs leading-4 text-ink-body">{{ $assurance['body'] }}</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @foreach ($assurance['chips'] as $index => $chip)
                    <button type="button" @click="contactFinance()"
                        @class([
                            'rounded-lg px-3 py-1.5 text-[11px] uppercase leading-[14px] tracking-[0.88px] transition-colors',
                            'border border-line-soft bg-surface font-semibold text-ink hover:bg-surface-muted' => $index === 0,
                            'bg-ink font-bold text-white hover:bg-brand-deep' => $index === 1,
                        ])>{{ $chip }}</button>
                @endforeach
            </div>
        </section>
    </div>
@endsection

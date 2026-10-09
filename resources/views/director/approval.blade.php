@extends('layouts.director')

@section('title', 'Persetujuan Kontrak // Otorisasi Tier-1')

@section('content')
    @php
        $cardTone = [
            'success' => 'text-success-deep',
            'warning' => 'text-warning-caution',
            'danger' => 'text-danger',
            'ink' => 'text-ink',
        ];

        $cardChip = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'accent' => 'bg-accent-deep text-white outline outline-1 -outline-offset-1 outline-accent-deep',
            'warning' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution',
            'danger' => 'bg-danger-soft/40 text-danger outline outline-1 -outline-offset-1 outline-danger',
        ];

        $clientTone = [
            'caution' => 'text-warning-caution',
            'success' => 'text-success-deep',
            'solid' => 'bg-ink text-accent',
        ];

        // Argumen Alpine disiapkan sebagai literal JS agar loop tabel tetap
        // bersih dan tidak membutuhkan directive `@php` tambahan per baris.
        $contractArguments = array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row['contract']),
            $queue['rows'],
        );

        $filterArguments = array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row['filter_key']),
            $queue['rows'],
        );

        $filterButtonArguments = array_map(
            static fn (array $filter): string => (string) \Illuminate\Support\Js::from($filter['key']),
            $queue['filters'],
        );
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorApproval(@js($queue['rows']), @js($queue))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-center justify-between gap-4 border-b border-line-board/80 pb-4">
            <div class="min-w-0 space-y-1">
                <p class="gpa-eyebrow flex items-center gap-2">
                    <x-gpa.icon name="badge-check" class="h-3.5 w-3.5 text-success-deep" />
                    {{ $header['eyebrow'] }}
                </p>
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $header['title'] }}
                </h1>
                <p class="max-w-3xl text-sm leading-5 text-ink-body">{{ $header['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <p class="flex flex-col rounded-lg bg-surface px-3 py-2 text-right outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="gpa-note text-ink-body">PERIODE LAPORAN</span>
                    <span class="gpa-meta-lg font-bold text-ink">{{ $header['period'] }}</span>
                </p>
                <button type="button" @click="printAuthorization()"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 shadow-sub outline outline-1 -outline-offset-1 outline-success transition-colors hover:bg-brand-hover">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-white" />
                    <span class="text-xs font-semibold leading-4 text-white">Cetak Antrian Otorisasi</span>
                </button>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="gpa-micro-bold {{ $cardTone[$card['tone']] }}">{{ $card['label'] }}</h2>
                            <x-gpa.icon :name="$card['icon']" class="h-4 w-4 shrink-0 {{ $cardTone[$card['tone']] }}" />
                        </div>

                        <p class="gpa-figure text-[26px] leading-8 text-ink">{{ $card['value'] }}</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $cardChip[$card['chip_tone']] }}">
                            {{ $card['chip'] }}
                        </span>
                        <span class="gpa-note font-semibold {{ $cardTone[$card['meta_tone']] ?? 'text-ink-body' }}">
                            {{ $card['meta'] }}
                        </span>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Authorization queue --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-4 border-b border-line-board pb-4">
                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded bg-danger px-2 py-0.5 gpa-note font-bold text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-white" aria-hidden="true"></span>
                            {{ $queue['badge'] }}
                        </span>
                    </div>
                    <h2 class="gpa-section-title text-ink">{{ $queue['title'] }}</h2>
                </div>

                <div class="flex shrink-0 flex-col gap-1">
                    <p class="inline-flex items-center gap-2 rounded bg-surface-shell px-3 py-1.5 outline outline-1 -outline-offset-1 outline-line-board">
                        <span class="gpa-note text-ink-body">SELEKSI BATCH:</span>
                        <span class="gpa-meta font-bold text-ink" x-text="selectedLabel()">0 / {{ count($queue['rows']) }} Terpilih</span>
                        <span class="gpa-meta font-bold text-success-deep" x-text="selectedValueLabel()">Rp 0</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="approveSelected()" :disabled="selectedCount === 0"
                            class="inline-flex items-center gap-1.5 rounded bg-accent px-3.5 py-1.5 gpa-meta-lg font-bold text-ink shadow-sub outline outline-1 -outline-offset-1 outline-brand-deep/30 transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                            {{ $queue['batch_approve'] }}
                        </button>
                        <button type="button" @click="selectAll()"
                            class="inline-flex items-center rounded bg-surface-shell px-3 py-1.5 text-center gpa-meta-lg font-medium text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                            {{ $queue['batch_select_all'] }}
                        </button>
                    </div>
                </div>
            </header>

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 py-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">{{ $header['filter_label'] }}</span>
                    @foreach ($queue['filters'] as $index => $filter)
                        <button type="button" @click="setFilter({{ $filterButtonArguments[$index] }})"
                            :class="filterClass({{ $filterButtonArguments[$index] }})"
                            class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                            {{ $filter['label'] }} ({{ $filter['count'] }})
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <label class="relative flex items-center" for="approval-search">
                        <span class="sr-only">{{ $queue['search_placeholder'] }}</span>
                        <x-gpa.icon name="search"
                            class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                        <input id="approval-search" type="search" x-model="search"
                            placeholder="{{ $queue['search_placeholder'] }}"
                            class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </label>

                    <span class="inline-flex items-center gap-1 gpa-note font-semibold text-success-deep">
                        <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                        {{ $queue['hsm'] }}
                    </span>
                    <span class="gpa-note text-ink-body" aria-hidden="true">|</span>
                    <span class="inline-flex items-center gap-1 gpa-note font-semibold text-success-deep">
                        <x-gpa.icon name="clock" class="h-3 w-3 shrink-0" />
                        {{ $queue['timeout'] }}
                    </span>
                </div>
            </div>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full min-w-[64rem] border-collapse">
                    <caption class="sr-only">{{ $queue['title'] }}</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="w-9 py-3 pr-2">
                                <span class="sr-only">Pilih pengajuan</span>
                            </th>
                            @foreach ($queue['columns'] as $position => $column)
                                <th scope="col"
                                    class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body {{ $position === count($queue['columns']) - 1 ? 'text-right' : 'text-left' }}">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @foreach ($queue['rows'] as $index => $row)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50"
                                x-show="matches({{ $filterArguments[$index] }}, {{ $index }})">
                                <td class="py-4 pr-2 align-top">
                                    <input type="checkbox" class="gpa-check m-0" value="{{ $row['contract'] }}"
                                        x-model="selected">
                                </td>
                                <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                    <div class="flex items-start gap-2">
                                        <span class="min-w-0">
                                            <span class="block text-[13px] font-semibold leading-5 text-ink">
                                                {{ $row['client'] }}
                                            </span>
                                            <span class="mt-0.5 block text-[11px] leading-4 text-ink-body">
                                                {{ $row['region'] }}
                                            </span>
                                        </span>
                                    </div>
                                    <p class="mt-2 text-[11px] leading-4 text-ink-body">
                                        <span class="font-medium text-ink">{{ $row['contract'] }}</span>
                                    </p>
                                    <p class="text-[11px] leading-4 text-ink-body">
                                        {{ $row['submitted_by'] }} &middot; {{ $row['submitted_at'] }}
                                    </p>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[13px] font-medium leading-5 text-ink">{{ $row['commodity'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">{{ $row['volume'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">{{ $row['segment'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    @if ($row['price_strike'])
                                        <p class="text-[13px] font-semibold leading-5 text-ink">
                                            {{ $row['price'] }}
                                            <span class="text-[11px] font-normal text-ink-body line-through">
                                                {{ $row['price_strike'] }}
                                            </span>
                                        </p>
                                    @else
                                        <p class="text-[13px] font-semibold leading-5 text-ink">{{ $row['price'] }}</p>
                                    @endif
                                    <p @class([
                                        'mt-1 text-[11px] leading-4',
                                        'font-medium text-warning-caution' => $row['exception_tone'] === 'caution',
                                        'text-ink-body' => $row['exception_tone'] === 'neutral',
                                    ])>{{ $row['exception'] }}</p>
                                    <p class="text-[11px] leading-4 text-ink-body">{{ $row['term'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[13px] font-semibold leading-5 text-success-deep">{{ $row['margin'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">
                                        Minimum {{ number_format($row['margin_minimum'], 1, '.', '') }}%
                                    </p>
                                    <p class="mt-1 flex items-start gap-1 text-[11px] leading-4 text-success-deep">
                                        <x-gpa.icon name="check-circle" class="mt-0.5 h-3 w-3 shrink-0" />
                                        <span>{{ $row['margin_flag'] }}</span>
                                    </p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[13px] font-semibold leading-5 text-ink">{{ $row['score'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">{{ $row['score_note'] }}</p>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-1.5">
                                        <button type="button" @click="detail({{ $contractArguments[$index] }})"
                                            class="inline-flex items-center gap-1.5 rounded-md border border-line-board px-2.5 py-1.5 text-[11px] font-medium leading-4 text-ink transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="file-text" class="h-3.5 w-3.5 shrink-0" />
                                            {{ $queue['detail_label'] }}
                                        </button>
                                        <button type="button" @click="approve({{ $contractArguments[$index] }})"
                                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[11px] font-semibold leading-4 transition-opacity hover:opacity-90"
                                            @class([
                                                'bg-ink text-white' => $row['approve_tone'] === 'solid',
                                                'bg-accent text-ink' => $row['approve_tone'] === 'accent',
                                            ])>
                                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                                            {{ $row['approve_label'] }}
                                        </button>
                                        <button type="button" @click="revise({{ $contractArguments[$index] }})"
                                            class="text-[11px] font-medium leading-4 text-ink-body underline-offset-2 transition-colors hover:text-ink hover:underline">
                                            {{ $queue['revise_label'] }}
                                        </button>
                                        <button type="button" @click="reject({{ $contractArguments[$index] }})"
                                            class="text-[11px] font-medium leading-4 text-danger underline-offset-2 transition-colors hover:underline">
                                            {{ $queue['reject_label'] }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="gpa-note font-semibold text-danger" x-show="visibleCount() === 0" x-cloak>
                Tidak ada pengajuan yang cocok dengan filter atau kata kunci pencarian.
            </p>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 pt-3">
                <p class="flex items-start gap-2">
                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="max-w-3xl gpa-note font-semibold text-ink-body">{{ $queue['audit_note'] }}</span>
                </p>
                <div class="flex flex-col items-end gap-1">
                    <p class="gpa-note font-bold text-ink">{{ $queue['total_note'] }}</p>
                    <p class="gpa-note text-ink-body" x-text="visibleLabel()">{{ $queue['shown_label'] }}</p>
                </div>
            </footer>
        </section>
    </div>
@endsection

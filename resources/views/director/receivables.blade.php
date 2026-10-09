@extends('layouts.director')

@section('title', 'Piutang & Tagihan // Faktur Tempo & Risiko Kredit B2B')

@section('content')
    @php
        // Palet nada mengikuti design "Piutang & Tagihan" yang memakai token
        // status yang sama dengan modul Laporan dan Volume Komoditas.
        $cardTone = [
            'ink' => [
                'label' => 'text-ink-body',
                'value' => 'text-ink',
                'tile' => 'bg-surface-shell',
                'icon' => 'text-ink',
            ],
            'success' => [
                'label' => 'text-ink-body',
                'value' => 'text-success-deep',
                'tile' => 'bg-accent/50',
                'icon' => 'text-success-ink',
            ],
            'warning' => [
                'label' => 'text-warning-caution',
                'value' => 'text-warning-caution',
                'tile' => 'bg-warning-soft',
                'icon' => 'text-warning-caution',
            ],
            'danger' => [
                'label' => 'text-danger',
                'value' => 'text-danger',
                'tile' => 'bg-danger-soft',
                'icon' => 'text-danger',
            ],
        ];

        $chipTone = [
            'accent' => 'bg-accent text-ink',
            'neutral' => 'bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board',
            'warning' => 'bg-warning-soft text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/30',
            'danger' => 'bg-danger text-white',
        ];

        $metaTone = [
            'muted' => 'text-ink-body',
            'success' => 'text-success-deep',
            'warning' => 'text-warning-caution',
            'danger' => 'text-danger',
        ];

        $stageBarTone = [
            'brand' => 'bg-brand text-accent',
            'success' => 'bg-success-deep text-white',
            'warning' => 'bg-[#C6904A] text-white',
            'danger' => 'bg-danger text-white',
        ];

        $stageCardTone = [
            'brand' => [
                'shell' => 'border-line-board border-l-brand bg-surface-shell',
                'label' => 'text-ink-body',
                'status' => 'text-success-deep',
                'value' => 'text-ink',
                'note' => 'text-ink-body',
            ],
            'success' => [
                'shell' => 'border-line-board border-l-success-deep bg-surface-shell',
                'label' => 'text-ink-body',
                'status' => 'text-success-deep',
                'value' => 'text-ink',
                'note' => 'text-ink-body',
            ],
            'warning' => [
                'shell' => 'border-line-board border-l-[#C6904A] bg-warning-cream',
                'label' => 'text-warning-caution',
                'status' => 'text-warning-caution',
                'value' => 'text-warning-caution',
                'note' => 'text-warning-caution',
            ],
            'danger' => [
                'shell' => 'border-danger/40 border-l-danger bg-danger-soft/30',
                'label' => 'text-danger',
                'status' => 'text-danger',
                'value' => 'text-danger',
                'note' => 'text-danger',
            ],
        ];

        $statusTone = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep',
            'warning' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution',
            'danger' => 'bg-danger text-white',
        ];

        $verificationTone = [
            'success' => 'text-success-deep',
            'warning' => 'text-warning-caution',
            'danger' => 'text-danger',
        ];

        $plafonTone = [
            'success' => ['label' => 'text-success-deep', 'bar' => 'bg-success-deep'],
            'warning' => ['label' => 'text-warning-caution', 'bar' => 'bg-[#C6904A]'],
            'danger' => ['label' => 'text-danger', 'bar' => 'bg-danger'],
        ];

        $ruleTone = [
            'danger' => 'text-danger',
            'warning' => 'text-warning-caution',
        ];

        $mono = 'font-mono tracking-[0.88px]';
    @endphp

    <div class="flex flex-col gap-6 px-0 py-0"
        x-data="directorReceivables(@js($ledger['rows']), @js($policy), @js($verification), @js($ledger['active_contracts']))">

        {{-- ---------- Kop halaman ---------- --}}
        <header class="flex flex-wrap items-end justify-between gap-4 border-b border-line-board pb-2">
            <div class="min-w-0">
                <h1 class="font-sans text-[28px] font-extrabold leading-9 tracking-[0.6px] text-ink sm:text-[32px] sm:leading-10">
                    {{ $header['title'] }}
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded bg-surface-shell px-3 py-1 outline outline-1 -outline-offset-1 outline-line-board">
                    <x-gpa.icon name="calendar" class="h-3 w-3 text-ink-subtle" />
                    <span class="text-[11px] font-bold tracking-[0.88px] text-ink {{ $mono }}">
                        {{ $header['period'] }}
                    </span>
                </span>
                <button type="button" @click="filterTopOnly()"
                    class="inline-flex items-center gap-1 rounded px-2.5 py-1 text-[12px] font-semibold text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="filter" class="h-3 w-3" />
                    {{ $header['filter_label'] }}
                </button>
            </div>
        </header>

        {{-- ---------- Empat kartu KPI ---------- --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan risiko kredit">
            @foreach ($cards as $card)
                @php $tone = $cardTone[$card['tone']] ?? $cardTone['ink']; @endphp
                <article class="flex flex-col gap-2 rounded-2xl bg-surface p-6 shadow-card">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="text-[11px] font-semibold uppercase leading-[14px] tracking-[0.55px] {{ $tone['label'] }} {{ $mono }}">
                            {{ $card['label'] }}
                        </h2>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $tone['tile'] }}">
                            <x-gpa.icon :name="$card['icon']" class="h-3.5 w-3.5 {{ $tone['icon'] }}" />
                        </span>
                    </div>

                    <p class="font-mono text-[28px] font-bold leading-[35px] {{ $tone['value'] }}">{{ $card['value'] }}</p>

                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-line-board pt-1">
                        <span class="inline-flex items-center gap-1 text-[12px] leading-4 {{ $metaTone[$card['meta_tone']] ?? $metaTone['muted'] }}">
                            @if ($card['meta_tone'] !== 'muted')
                                <span class="h-2 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
                            @endif
                            {{ $card['meta'] }}
                        </span>
                        <span
                            class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-3 tracking-[1px] {{ $chipTone[$card['chip_tone']] ?? $chipTone['accent'] }}">
                            {{ $card['chip'] }}
                        </span>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- ---------- Matriks umur piutang ---------- --}}
        <section class="rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-4 pb-2">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-[18px] font-bold leading-6 text-ink">{{ $aging['title'] }}</h2>
                    </div>
                </div>

                <span
                    class="inline-flex shrink-0 items-center gap-1.5 rounded bg-surface-shell px-3 py-1 outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="text-[11px] font-semibold tracking-[0.88px] text-success-deep {{ $mono }}">
                        {{ $aging['collectibility_label'] }}
                    </span>
                </span>
            </header>

            <div class="mt-4 flex flex-col gap-3">
                <div class="flex h-7 gap-0.5 overflow-hidden rounded-lg bg-surface-pill p-0.5 shadow-[inset_0_2px_4px_1px_rgb(0_0_0/0.05)] outline outline-1 -outline-offset-1 outline-line-board"
                    role="img" aria-label="Komposisi umur piutang">
                    @foreach ($aging['stages'] as $stage)
                        <div class="flex items-center justify-center px-1 pb-0.5 {{ $stageBarTone[$stage['tone']] }} {{ $loop->first ? 'rounded-l' : '' }} {{ $loop->last ? 'rounded-r' : '' }}"
                            style="width: {{ $stage['share'] }}%">
                            <span class="font-mono text-[16px] font-bold leading-6">{{ $stage['share_label'] }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-2 pt-1 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($aging['stages'] as $stage)
                        @php $stageTone = $stageCardTone[$stage['tone']] ?? $stageCardTone['brand']; @endphp
                        <article
                            class="flex flex-col gap-0.5 rounded-lg border border-l-4 px-2 py-2 {{ $stageTone['shell'] }}">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-[9px] font-bold uppercase leading-3 tracking-[1.08px] {{ $stageTone['label'] }} {{ $mono }}">
                                    {{ $stage['stage'] }}
                                </h3>
                                <span class="shrink-0 text-[9px] font-bold leading-3 tracking-[1.08px] {{ $stageTone['status'] }} {{ $mono }}">
                                    {{ $stage['status'] }}
                                </span>
                            </div>
                            <p class="pt-0.5 font-mono text-[16px] font-bold leading-6 {{ $stageTone['value'] }}">
                                {{ $stage['value_label'] }}
                            </p>
                            <p class="text-[11px] leading-[16.5px] {{ $stageTone['note'] }}">{{ $stage['note'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ---------- Buku besar faktur tempo ---------- --}}
        <section class="overflow-hidden rounded-2xl bg-surface shadow-card">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board bg-surface-shell/40 p-6">
                <div class="min-w-0">
                    <h2 class="text-[18px] font-bold leading-6 text-ink">{{ $ledger['title'] }}</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative block w-56">
                        <span class="sr-only">Cari klien atau nomor surat jalan</span>
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-subtle" />
                        <input type="search" x-model="query" placeholder="{{ $ledger['search_placeholder'] }}"
                            class="h-10 w-full rounded-lg border-0 bg-surface py-2 pl-9 pr-3 text-[12px] text-ink placeholder:text-ink-subtle focus:ring-2 focus:ring-inset focus:ring-success-deep" />
                    </label>

                    <div class="flex h-10 items-center gap-1 rounded-lg border border-line-board bg-surface px-3">
                        <x-gpa.icon name="filter" class="h-3.5 w-3.5 shrink-0 text-ink" />
                        <div class="flex items-center gap-1">
                            @foreach ([14, 30, 45] as $topDays)
                                <button type="button" @click="setTopFilter('{{ $topDays }}')"
                                    :class="topFilter === '{{ $topDays }}'
                                        ? 'bg-brand text-accent'
                                        : 'text-ink hover:bg-surface-shell'"
                                    class="rounded px-2 py-1 text-[12px] font-semibold leading-4 transition-colors">
                                    {{ $topDays }}
                                </button>
                            @endforeach
                            <button type="button" @click="setTopFilter('all')"
                                :class="topFilter === 'all' ? 'bg-brand text-accent' : 'text-ink hover:bg-surface-shell'"
                                class="rounded px-2 py-1 text-[12px] font-semibold leading-4 transition-colors">
                                {{ $ledger['filter_label'] }}
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1080px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-board bg-surface-shell">
                            @foreach ($ledger['columns'] as $index => $column)
                                <th scope="col"
                                    class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $index === 6 ? 'text-right' : '' }} {{ $mono }}">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (array_slice($ledger['rows'], 0, 4, true) as $rowIndex => $row)
                            @php $plafon = $plafonTone[$row['status_tone'] === 'danger' ? 'danger' : ($row['status_tone'] === 'warning' ? 'warning' : 'success')]; @endphp
                            <tr x-show="rowVisible({{ $rowIndex }})"
                                @class([
                                    'border-l-4 border-danger/40 bg-danger-soft/20' => $row['locked'],
                                    'border-t border-line-board hover:bg-surface-base/40' => ! $row['locked'],
                                ])>
                                <th scope="row" class="px-4 py-3">
                                    <span class="flex items-center gap-2.5">
                                        <span @class([
                                            'text-[14px] font-bold leading-5',
                                            'text-danger' => $row['locked'],
                                            'text-ink' => ! $row['locked'],
                                        ])>{{ $row['name'] }}</span>
                                        @if ($row['locked'])
                                            <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0 text-danger" />
                                        @endif
                                    </span>
                                    <span class="text-[9px] font-semibold leading-3 tracking-[1.08px] {{ $verificationTone[$row['locked'] ? 'danger' : 'success'] }} {{ $mono }}">
                                        {{ $row['client_id'] }}@if ($row['locked']) [LOCKED]@endif
                                    </span>
                                </th>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'font-mono text-[11px] font-semibold leading-[14px] tracking-[0.88px]',
                                        'text-danger' => $row['locked'],
                                        'text-ink' => ! $row['locked'],
                                    ])>{{ $row['sj_count_label'] }}</span>
                                    <span class="block text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body {{ $mono }}">
                                        {{ $row['sj_label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'font-mono text-[16px] font-bold leading-6',
                                        'text-danger' => $row['locked'],
                                        'text-ink' => ! $row['locked'],
                                    ])>{{ $row['outstanding_label'] }}</span>
                                    <span class="mt-2 block text-[9px] font-bold uppercase leading-3 tracking-[1.08px] {{ $verificationTone[$row['verification_tone']] ?? $verificationTone['success'] }} {{ $mono }}">
                                        {{ $row['verification'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px]',
                                        'text-danger' => $row['locked'],
                                        'text-ink' => ! $row['locked'],
                                    ])>{{ $row['top_label'] }}</span>
                                    <span @class([
                                        'mt-0.5 block text-[12px] leading-4',
                                        'font-bold text-danger' => $row['locked'],
                                        'text-ink-body' => ! $row['locked'] && $row['days_remaining'] >= 0,
                                        'font-semibold text-warning-caution' => ! $row['locked'] && $row['days_remaining'] < 0,
                                    ])>
                                        {{ $row['due_label'] }} {{ $row['days_label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 rounded px-2.5 py-1 text-[16px] font-bold uppercase leading-6 {{ $statusTone[$row['status_tone']] ?? $statusTone['success'] }} {{ $mono }}">
                                        <span @class([
                                            'h-1.5 w-1.5 shrink-0 rounded-full',
                                            'bg-success-deep' => $row['status_tone'] === 'success',
                                            'bg-warning-caution' => $row['status_tone'] === 'warning',
                                            'bg-white' => $row['status_tone'] === 'danger',
                                        ]) aria-hidden="true"></span>
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink {{ $mono }}">
                                            {{ $row['limit_label'] }}
                                        </span>
                                        <span class="text-[9px] font-bold leading-3 tracking-[1.08px] {{ $plafon['label'] }} {{ $mono }}">
                                            {{ $row['utilisation_note'] }}
                                        </span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-surface-pill" role="img"
                                        aria-label="Utilisasi plafon {{ $row['utilisation_note'] }}">
                                        <div class="h-full rounded-full {{ $plafon['bar'] }}"
                                            style="width: {{ $row['utilisation_width'] }}%"></div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @foreach ($row['actions'] as $actionIndex => $action)
                                            @php $isPrimary = $actionIndex === 0; @endphp
                                            <button type="button"
                                                @click="{{ match ($action) {
                                                    'Kunci Plafon' => 'toggleFreeze(row)',
                                                    'Lepas Freeze' => 'toggleFreeze(row)',
                                                    'Kirim Reminder WA' => 'sendReminder(row)',
                                                    'Restrukturisasi Tagihan' => 'restructure(row)',
                                                    default => 'inspectInvoice(row)',
                                                } }}"
                                                @class([
                                                    'rounded px-2.5 py-1.5 font-mono text-[11px] font-semibold leading-[14px] tracking-[0.88px] transition-colors',
                                                    'shadow-sub bg-danger text-white hover:bg-danger-ink' => $isPrimary && $row['locked'],
                                                    'bg-accent-deep text-ink hover:bg-accent' => $isPrimary && ! $row['locked'] && $row['status_tone'] === 'warning',
                                                    'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-disabled' => ! $isPrimary || ($isPrimary && ! $row['locked'] && $row['status_tone'] !== 'warning'),
                                                ])>{{ $action }}</button>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-[12px] text-ink-body">
                                    Tidak ada faktur tempo aktif yang dapat dipantau.
                                </td>
                            </tr>
                        @endforelse

                        <tr x-show="visibleCount() === 0" x-cloak>
                            <td colspan="7" class="px-4 py-8 text-center text-[12px] text-ink-body">
                                Tidak ada faktur tempo yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board bg-surface-shell p-4">
                <p class="text-[11px] font-medium leading-[14px] tracking-[0.88px] text-ink-body {{ $mono }}"
                    x-text="shownLabel()">{{ $ledger['shown_label'] }}</p>
                <div class="flex items-center gap-2">
                    <button type="button" disabled
                        class="cursor-not-allowed rounded bg-surface px-3 py-1 text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-ink-body outline outline-1 -outline-offset-1 outline-line-board">
                        Prev
                    </button>
                    <span class="text-[11px] font-bold leading-[14px] tracking-[0.88px] text-ink {{ $mono }}">{{ $ledger['page_label'] }}</span>
                    <button type="button"
                        class="rounded bg-surface px-3 py-1 text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-ink-body outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:text-ink">
                        Next
                    </button>
                </div>
            </footer>
        </section>

        {{-- ---------- Kebijakan pembekuan & protokol verifikasi ---------- --}}
        <section class="flex flex-col gap-4">
            <article class="flex flex-col justify-between gap-6 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-2">
                    <header class="flex items-center gap-2 pb-2">
                        <h2 class="text-[18px] font-bold leading-6 text-ink">{{ $verification['title'] }}</h2>
                    </header>

                    <div class="flex flex-col gap-2 pt-2">
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-2.5">
                            <span class="text-[12px] leading-4 text-ink">{{ $verification['queue_label'] }}</span>
                            <span class="text-[12px] font-bold leading-4 text-ink {{ $mono }}">{{ $verification['queue_value'] }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-2.5">
                            <span class="text-[12px] leading-4 text-ink">{{ $verification['clearing_label'] }}</span>
                            <span class="text-[12px] font-bold leading-4 text-success-deep {{ $mono }}">{{ $verification['clearing_value'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-t border-line-board pt-4">
                    <button type="button" @click="openVerification()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent-deep px-4 py-3 text-[16px] font-bold leading-6 text-ink shadow-sub transition-colors hover:bg-accent">
                        <x-gpa.icon name="clipboard" class="h-3.5 w-3.5 shrink-0" />
                        {{ $verification['action'] }}
                    </button>
                    <p class="text-center font-mono text-[10px] leading-[15px] tracking-[0.88px] text-ink-body">
                        {{ $verification['sla'] }}
                    </p>
                </div>
            </article>
        </section>
    </div>
@endsection

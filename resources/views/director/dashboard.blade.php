@extends('layouts.director')

@section('title', 'Dashboard Eksekutif // Ringkasan Kinerja')

@section('content')
    @php
        $labelTone = [
            'success' => 'text-success-deep',
            'warning' => 'text-warning-caution',
            'danger' => 'text-danger',
        ];

        $badgeTone = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'warning' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution',
        ];

        $splitTone = [
            'ink' => 'text-ink',
            'strong' => 'text-ink font-mono',
            'body' => 'text-ink-body',
            'muted' => 'text-ink-body',
        ];

        $bucketTone = [
            'success' => 'bg-surface-shell text-success-deep outline-line-board',
            'ink' => 'bg-surface-shell text-ink outline-line-board',
            'caution' => 'bg-warning-cream text-warning-caution outline-warning-caution',
            'danger' => 'bg-danger-soft/40 text-danger outline-danger',
        ];
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6">

        {{-- Page header --}}
        <section class="flex flex-wrap items-center justify-between gap-4 border-b border-line-board/80 pb-4">
            <div class="min-w-0 space-y-1">
                <p class="gpa-eyebrow flex items-center gap-2">
                    <x-gpa.icon name="chart" class="h-3.5 w-3.5 text-success-deep" />
                    {{ $header['eyebrow'] }}
                </p>
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $header['title'] }}
                </h1>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <button type="button" @click="printPdf()"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-white shadow-sub outline outline-1 -outline-offset-1 outline-success transition-colors hover:bg-brand-hover">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                    <span class="text-center text-xs font-semibold leading-4 text-white">{{ $header['print_label'] }}</span>
                </button>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($kpis as $kpi)
                <article @class([
                    'relative flex h-full flex-col justify-between overflow-hidden rounded-2xl p-6',
                    'bg-surface shadow-card' => $kpi['variant'] !== 'highlight',
                    'bg-surface-shell shadow-sub outline outline-2 -outline-offset-2 outline-brand' => $kpi['variant'] === 'highlight',
                ])>
                    @if ($kpi['variant'] === 'highlight')
                        <span
                            class="absolute right-0 top-0 rounded-b-lg bg-accent-deep px-3 py-0.5 gpa-micro-bold text-ink">{{ $kpi['badge'] }}</span>
                    @endif

                    <div class="flex flex-col gap-2 @if ($kpi['variant'] === 'highlight') pt-4 @endif">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="gpa-micro-bold {{ $labelTone[$kpi['tone']] }}">{{ $kpi['label'] }}</h2>
                            <x-gpa.icon :name="$kpi['icon']" class="h-4 w-4 shrink-0 {{ $labelTone[$kpi['tone']] }}" />
                        </div>

                        <p class="gpa-figure text-[30px] leading-9 text-ink">
                            {{ $kpi['value'] }}
                            @if (!empty($kpi['value_suffix']))
                                <span class="gpa-meta-lg font-normal text-success-deep">{{ $kpi['value_suffix'] }}</span>
                            @endif
                        </p>

                        @if (!empty($kpi['badge']) && $kpi['variant'] === 'badge')
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span
                                    class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $badgeTone[$kpi['tone']] }}">{{ $kpi['badge'] }}</span>
                                <span class="gpa-meta-lg text-ink-body">{{ $kpi['value_note'] }}</span>
                            </div>
                        @elseif (!empty($kpi['split']))
                            <p class="text-xs font-bold leading-4">
                                @foreach ($kpi['split'] as $part)
                                    <span class="{{ $splitTone[$part['tone']] }}">{{ $part['text'] }}</span>
                                @endforeach
                            </p>
                        @elseif ($kpi['variant'] === 'progress')
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span
                                    class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $badgeTone['success'] }}">{{ $kpi['badge'] }}</span>
                                <span class="gpa-meta-lg text-ink-body">{{ $kpi['value_note'] }}</span>
                            </div>
                        @elseif (!empty($kpi['value_note']))
                            <p class="text-xs leading-4 text-ink-body">{{ $kpi['value_note'] }}</p>
                        @endif
                    </div>

                    <div class="pt-4">
                        @if (isset($kpi['progress']))
                            <div class="flex flex-col gap-1 border-t border-surface-track pt-3">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="gpa-note text-ink-body">{{ $kpi['progress']['label'] }}</span>
                                    <span class="gpa-note font-bold text-ink">{{ $kpi['progress']['value'] }}</span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full bg-success-deep"
                                        style="width: {{ $kpi['progress']['percent'] }}%"></div>
                                </div>
                            </div>
                        @elseif (isset($kpi['cta']))
                            <div class="flex flex-col gap-2 border-t border-line-board/60 pt-3">
                                <a href="{{ route('director.approval') }}"
                                    class="inline-flex items-center justify-center gap-1 rounded bg-brand px-3 py-1.5 gpa-meta font-bold text-accent transition-colors hover:bg-brand-hover">
                                    {{ $kpi['cta'] }}
                                    <x-gpa.icon name="arrow-right" class="h-2.5 w-2.5 shrink-0" />
                                </a>
                            </div>
                        @else
                            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                                <span class="inline-flex items-center gap-1 gpa-note font-bold {{ $labelTone[$kpi['tone']] }}">
                                    <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                                    {{ $kpi['footer_left'] }}
                                </span>
                                <span class="gpa-note font-semibold text-ink-body">{{ $kpi['footer_right'] }}</span>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Weekly trend + commodity distribution --}}
        <section class="grid gap-4 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
            <div class="gpa-panel flex flex-col justify-between gap-6 p-6">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">{{ $weekly['title'] }}</h2>
                    </div>
                    <ul class="flex flex-wrap items-center gap-3">
                        @foreach ($weekly['legend'] as $item)
                            <li class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2 shrink-0 rounded {{ $item['swatch'] }}" aria-hidden="true"></span>
                                <span class="gpa-note text-ink">{{ $item['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </header>

                <div class="flex flex-col gap-4">
                    @foreach ($weekly['rows'] as $row)
                        <div class="flex flex-col gap-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="gpa-meta-lg font-bold text-ink">{{ $row['label'] }}</p>
                                <p @class([
                                    'gpa-meta-lg font-bold',
                                    'text-[#C6904A]' => $row['status'] === 'running',
                                    'text-success-deep' => $row['status'] !== 'running',
                                ])>
                                    Realisasi: {{ $row['realized_label'] }} ({{ $row['ratio_label'] }})
                                </p>
                            </div>

                            <div @class([
                                'relative flex h-7 items-center rounded-lg p-1',
                                'bg-surface-shell' => $row['status'] !== 'running',
                                'bg-[#EFF4DC] outline outline-1 -outline-offset-1 outline-accent-deep' => $row['status'] === 'running',
                            ])>
                                <div @class([
                                    'flex items-center whitespace-nowrap rounded px-2 pb-px text-[16px] leading-6 text-white',
                                    'bg-brand' => $row['status'] !== 'running',
                                    'bg-success' => $row['status'] === 'running',
                                ]) style="width: {{ $row['bar_percent'] }}%">
                                    {{ $row['bar_label'] }}
                                </div>
                                <span @class([
                                    'pointer-events-none absolute top-0.5 whitespace-nowrap text-[16px] font-bold leading-6',
                                    'text-success-deep' => $row['marker_tone'] === 'success',
                                    'text-ink' => $row['marker_tone'] === 'ink',
                                ]) style="left: {{ $row['marker'] }}%">{{ $row['volume_label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <footer>
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board pt-4">
                        <p class="gpa-note text-ink-body">{{ $weekly['source'] }}</p>
                        <p class="gpa-note font-bold text-ink">{{ $weekly['deviation'] }}</p>
                    </div>
                </footer>
            </div>

            <div class="gpa-panel flex flex-col justify-between gap-4 p-6">
                <header class="border-b border-line-board/60 pb-3">
                    <h2 class="gpa-section-title text-ink">{{ $commodities['title'] }}</h2>
                    <p class="mt-1 gpa-meta font-medium text-ink-body">{{ $commodities['subtitle'] }}</p>
                </header>

                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3">
                        @foreach ($commodities['rows'] as $row)
                            <div class="flex flex-col gap-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="gpa-meta font-bold text-ink">{{ $row['name'] }}</p>
                                    <p class="gpa-meta font-semibold text-success-deep">
                                        {{ $row['tons_label'] }} Ton ({{ $row['share'] }}%)
                                    </p>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full {{ $row['bar'] }}" style="width: {{ $row['share'] }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-2 border-t border-line-board pt-6">
                        <p class="gpa-micro-bold text-ink">{{ $channels['title'] }}</p>
                        <div class="flex items-start gap-2">
                            @foreach ($channels['rows'] as $channel)
                                <div
                                    class="flex flex-1 flex-col items-center gap-0.5 rounded bg-surface-shell p-2 text-center outline outline-1 -outline-offset-1 outline-line-board">
                                    <p class="text-[10px] leading-[15px] text-ink-body gpa-meta">{{ $channel['name'] }}</p>
                                    <p class="pt-0.5 text-[14px] font-bold leading-5 text-ink gpa-meta">{{ $channel['share'] }}%
                                    </p>
                                    <p class="text-[9px] leading-[13.5px] text-success-deep gpa-meta">
                                        {{ $channel['tons_label'] }} T
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <footer>
                    <div class="flex flex-wrap items-start justify-between gap-2 border-t border-line-board pt-4">
                        <p class="gpa-note font-semibold text-success-deep">{{ $commodities['footer_left'] }}</p>
                        <p class="gpa-note font-semibold text-success-deep">{{ $commodities['footer_right'] }}</p>
                    </div>
                </footer>
            </div>
        </section>


        {{-- Receivables aging --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">{{ $receivables['title'] }}</h2>
                    <p class="mt-1 max-w-3xl text-xs leading-4 text-ink-body">{{ $receivables['description'] }}</p>
                </div>
                <p class="gpa-meta font-bold text-success-deep">{{ $receivables['total_label'] }}</p>
            </header>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($receivables['buckets'] as $bucket)
                    <article @class([
                        'flex flex-col gap-1 rounded-xl p-3 outline outline-1 -outline-offset-1',
                        $bucketTone[$bucket['tone']],
                    ])>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-meta font-bold">{{ $bucket['label'] }}</h3>
                            <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 opacity-70" />
                        </div>
                        <p class="text-lg font-bold leading-6 font-inter">{{ $bucket['value_label'] }}</p>
                        <p class="gpa-meta-lg leading-4 text-ink-body">{{ $bucket['detail'] }}</p>
                        <p class="pt-1 text-[10px] font-semibold leading-[15px] gpa-meta">{{ $bucket['status'] }}</p>
                    </article>
                @endforeach
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                <p class="flex items-start gap-2">
                    <x-gpa.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0 text-warning-caution" />
                    <span class="text-xs leading-4 text-ink">
                        {{ $receivables['note']['lead'] }}
                        <strong class="font-bold">{{ $receivables['note']['emphasis'] }}</strong>
                        {{ $receivables['note']['tail'] }}
                    </span>
                </p>
                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" @click="openAuditNote()"
                        class="rounded bg-surface-track px-2.5 py-1 text-center text-[12px] font-bold leading-4 text-ink gpa-meta outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-disabled">
                        {{ $receivables['audit_action'] }}
                    </button>
                    <button type="button" @click="dispensation()"
                        class="rounded bg-ink px-2.5 py-1 text-center text-[12px] font-bold leading-4 text-white gpa-meta transition-opacity hover:opacity-90">
                        {{ $receivables['dispensation_action'] }}
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection

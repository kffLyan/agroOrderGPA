@extends('layouts.director')

@section('title', 'Penjualan // Analisis Revenue Eksekutif')

@section('content')
    @php
        $noteTone = [
            'muted' => 'text-success-deep font-medium',
            'ink' => 'text-ink font-bold',
            'caution' => 'text-warning-caution font-semibold',
        ];

        $badgeTone = [
            'accent' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep',
            'neutral' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
        ];

        $statusTone = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep',
            'deep' => 'bg-surface-track text-success-deep outline outline-1 -outline-offset-1 outline-line-board',
            'ink' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
            'caution' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution',
        ];

        $noteToneSettlement = [
            'success' => 'text-success-deep',
            'ink' => 'text-success-deep',
        ];

        // Argumen Alpine disiapkan sebagai literal JS agar loop tabel tidak
        // memerlukan directive `@php` tambahan di tengah view.
        $scopeKeys = array_keys($scopes);

        $scopeArguments = [];

        foreach ($scopeKeys as $scopeKey) {
            $scopeArguments[$scopeKey] = (string) \Illuminate\Support\Js::from($scopeKey);
        }

        $trendArguments = array_values(array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $trend['rows'],
        ));

        $channelArguments = array_values(array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $channels['rows'],
        ));

        $portfolioArguments = array_values(array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $portfolio['rows'],
        ));

        $methodArguments = array_values(array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $settlement['rows'],
        ));
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorSales(@js($scopeKeys), @js($trend['rows']), @js($portfolio['rows']))">

        {{-- Page header + scope pita KPI --}}
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $salesHeader['title'] }}
                </h1>
            </div>

            <div class="flex shrink-0 flex-col items-stretch gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-lg bg-surface-track px-3 py-2 outline outline-1 -outline-offset-1 outline-line-board">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                        @foreach ($scopes as $position => $scope)
                            <span @if ($position !== array_key_first($scopes)) x-cloak @endif
                                x-show="activeScopeKey() === {{ $scopeArguments[$position] }}"
                                class="gpa-meta font-bold text-ink">{{ $scope['period'] }}</span>
                        @endforeach
                    </span>

                    @foreach ($scopes as $position => $scope)
                        <button type="button" @click="setScope({{ $scopeArguments[$position] }})"
                            :class="scopeClass({{ $scopeArguments[$position] }})"
                            class="rounded-lg px-3 py-2 gpa-note font-semibold">{{ $scope['tab'] }}</button>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="exportSummary()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-surface px-4 py-2.5 outline outline-1 -outline-offset-1 outline-ink-quiet transition-colors hover:bg-surface-shell">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0" />
                        <span class="text-xs font-semibold leading-4 text-ink font-inter">{{ $salesHeader['export_label'] }}</span>
                    </button>
                    <button type="button" @click="printRecap()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink-strong px-4 py-2.5 text-white outline outline-1 -outline-offset-1 outline-ink-quiet transition-colors hover:bg-brand-hover">
                        <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                        <span class="text-xs font-semibold leading-4 text-white font-inter">{{ $salesHeader['print_label'] }}</span>
                    </button>
                </div>
            </div>
        </section>

        {{-- KPI band per cakupan periode --}}
        <section class="flex flex-col gap-3">
            @foreach ($scopes as $position => $scope)
                <div @if ($position !== array_key_first($scopes)) x-cloak @endif
                    x-show="activeScopeKey() === {{ $scopeArguments[$position] }}" class="flex flex-col gap-3">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($scope['cards'] as $card)
                            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                                <header class="flex items-start justify-between gap-2">
                                    <h2 class="gpa-note font-semibold text-success-deep">{{ $card['label'] }}</h2>
                                    <span
                                        class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $badgeTone[$card['badge_tone']] }}">{{ $card['badge'] }}</span>
                                </header>

                                <p class="font-inter text-2xl font-bold leading-8 text-ink">
                                    {{ $card['value'] }}
                                </p>

                                <p class="flex flex-wrap items-center gap-1.5">
                                    @foreach ($card['notes'] as $note)
                                        <span class="gpa-meta {{ $noteTone[$note['tone']] }}">{{ $note['text'] }}</span>
                                    @endforeach
                                </p>

                                <div class="flex flex-col gap-2">
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                        <div class="h-full rounded-full {{ $card['progress']['fill'] }}"
                                            style="width: {{ max(2, min(100, $card['progress']['percent'])) }}%"></div>
                                    </div>

                                    <p class="flex flex-wrap items-center justify-between gap-2">
                                        @foreach ($card['footer'] as $item)
                                            <span class="gpa-note {{ $noteTone[$item['tone']] }}">{{ $item['text'] }}</span>
                                        @endforeach
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                </div>
            @endforeach
        </section>

        {{-- Grafik realisasi mingguan vs ceiling & BEP --}}
        <section class="flex flex-col gap-6 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">{{ $trend['title'] }}</h2>
                </div>

                <ul class="flex flex-wrap items-center gap-3">
                    @foreach ($trend['legend'] as $item)
                        <li class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2 shrink-0 rounded {{ $item['swatch'] }}" aria-hidden="true"></span>
                            <span class="gpa-note text-ink">{{ $item['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </header>

            <div class="relative pt-8">
                <span
                    class="absolute left-0 top-0 rounded bg-accent px-2 py-1 gpa-note text-ink-strong">{{ $trend['ceiling_label'] }}</span>

                <div class="flex h-10 items-end gap-3">
                    @foreach ($trend['rows'] as $index => $row)
                        <button type="button" @click="inspectWeek({{ $trendArguments[$index] }})"
                            class="flex-1 text-center">
                            <span class="gpa-note font-bold text-success-deep">{{ $row['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="relative mt-1 h-44">
                    <div class="absolute inset-x-0 top-0 border-t border-dashed border-ink-strong" aria-hidden="true"></div>
                    <div class="absolute inset-x-0 border-t border-dashed border-warning-caution"
                        style="bottom: {{ $trend['bep_percent'] }}%" aria-hidden="true"></div>

                    <span
                        class="absolute left-0 z-10 -translate-y-full rounded bg-warning-cream px-2 py-1 gpa-note text-warning-caution"
                        style="bottom: {{ $trend['bep_percent'] }}%">{{ $trend['bep_label'] }}</span>

                    <div class="flex h-full items-end gap-3">
                        @foreach ($trend['rows'] as $row)
                            <div class="flex h-full flex-1 items-end justify-center">
                                <div class="relative h-full w-12 rounded bg-surface-track">
                                    <div class="absolute inset-x-0 bottom-0 rounded {{ $row['fill'] }}"
                                        style="height: {{ $row['share'] }}%"></div>
                                    @if ($row['status'] === 'running')
                                        <div class="absolute inset-x-0 border-t-2 border-dashed border-ink-strong"
                                            style="bottom: {{ $row['share'] }}%" aria-hidden="true"></div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-1 flex gap-3">
                    @foreach ($trend['rows'] as $row)
                        <p class="flex-1 text-center gpa-meta-lg font-bold text-ink">{{ $row['week'] }}</p>
                    @endforeach
                </div>
            </div>

            <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board/60 pt-4">
                <p class="gpa-note font-bold text-ink">{{ $trend['peak_label'] }}</p>
            </footer>
        </section>

        {{-- Komposisi revenue per kanal --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0 space-y-1">
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">{{ $channels['title'] }}</h2>
                </div>

                <p class="gpa-note font-bold text-ink">{{ $channels['total_label'] }}</p>
            </header>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($channels['rows'] as $index => $row)
                    <button type="button" @click="inspectChannel({{ $channelArguments[$index] }})"
                        class="flex flex-col gap-3 rounded-xl bg-surface-shell p-4 text-left outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <span class="flex items-center gap-2">
                            <span class="h-3 w-3 shrink-0 rounded-full {{ $row['dot'] }}" aria-hidden="true"></span>
                            <span class="font-sans text-base font-semibold leading-6 text-ink">{{ $row['name'] }}</span>
                        </span>

                        <span class="gpa-meta-lg font-bold text-ink">{{ $row['share_label'] }}</span>

                        <span class="flex flex-col gap-1">
                            <span class="gpa-note text-success-deep">Nominal Realisasi:</span>
                            <span class="gpa-meta-lg font-bold text-ink">{{ $row['value_label'] }}</span>
                        </span>

                        <span class="h-2 w-full overflow-hidden rounded-full bg-surface-track" aria-hidden="true">
                            <span class="block h-full rounded-full {{ $row['dot'] }}"
                                style="width: {{ $row['share'] }}%"></span>
                        </span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Neraca portofolio 5 komoditas inti --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board pb-4">
                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                    </div>
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">{{ $portfolio['title'] }}</h2>
                </div>

                <button type="button" @click="verifyPortfolio()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-surface-track px-3 py-2 outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-disabled">
                    <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    <span class="gpa-note font-bold text-ink">{{ $portfolio['verified'] }}</span>
                </button>
            </header>

            <div class="gpa-scroll-x overflow-x-auto py-1">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">{{ $portfolio['title'] }}</caption>
                    <thead>
                        <tr class="border-b border-line-board bg-surface-track">
                            @foreach ($portfolio['columns'] as $position => $column)
                                <th scope="col"
                                    class="px-3 py-3 gpa-micro-bold text-ink {{ $position >= 4 ? 'text-right' : 'text-left' }}">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($portfolio['rows'] as $index => $row)
                            <tr class="border-b border-line-board/60">
                                <th scope="row" class="px-3 py-4 text-left align-middle font-normal">
                                    <span class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                                        <span class="font-inter text-xs font-bold leading-4 text-ink">{{ $row['name'] }}</span>
                                    </span>
                                    <span class="mt-0.5 block gpa-note font-semibold text-success-deep">{{ $row['sku'] }}</span>
                                </th>
                                <td class="px-3 py-4 align-middle">
                                    <span class="gpa-meta font-medium text-ink">{{ $row['volume_label'] }}</span>
                                    <span class="mt-0.5 block gpa-note text-ink-body">kg</span>
                                </td>
                                <td class="px-3 py-4 align-middle">
                                    <span class="gpa-meta font-medium text-ink">{{ $row['list_price_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 align-middle">
                                    <span class="gpa-meta font-medium text-success-deep">{{ $row['price_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span class="gpa-meta-lg font-bold text-ink">{{ $row['revenue_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span class="gpa-meta-lg font-bold text-success-deep">{{ $row['gross_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span
                                        class="inline-block rounded px-2 py-0.5 text-[11px] font-semibold leading-4 gpa-meta {{ $statusTone[$row['status_tone']] }}">{{ $row['margin_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span class="gpa-meta-lg font-bold text-ink">{{ $row['share_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <button type="button" @click="inspectCommodity({{ $portfolioArguments[$index] }})"
                                        class="inline-flex flex-col items-end gap-1">
                                        <span
                                            class="inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $statusTone[$row['status_tone']] }}">{{ $row['status'] }}</span>
                                        <span class="h-1.5 w-16 overflow-hidden rounded-full bg-surface-track" aria-hidden="true">
                                            <span class="block h-full rounded-full bg-success-deep"
                                                style="width: {{ $row['bar_percent'] }}%"></span>
                                        </span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-ink-quiet bg-surface-track">
                            <th scope="row" class="px-3 py-3 text-left align-middle gpa-meta-lg font-bold text-ink">
                                Total 5 Komoditas
                            </th>
                            <td class="px-3 py-3 align-middle">
                                <span class="gpa-meta-lg font-bold text-ink">{{ $portfolio['total_volume_label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle gpa-note text-ink-body">-</td>
                            <td class="px-3 py-3 text-center align-middle gpa-note text-ink-body">-</td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-meta-lg font-bold text-ink">{{ $portfolio['total_revenue_label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-meta-lg font-bold text-ink-strong">{{ $portfolio['total_gross_label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="inline-block rounded bg-ink-strong px-2 py-0.5 text-[11px] font-semibold leading-4 gpa-meta text-accent">{{ $portfolio['total_margin_label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-meta-lg font-bold text-success-deep">{{ $portfolio['total_share_label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-note font-bold text-success-deep">{{ $portfolio['verified'] }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board/60 pt-3">
                <p class="gpa-note font-bold text-ink">MARGIN FLOOR: 15.0%</p>
            </footer>
        </section>

        {{-- Rekonsiliasi cash inflow --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0 space-y-1">
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">{{ $settlement['title'] }}</h2>
                </div>

                <p class="gpa-note font-bold text-ink">{{ $settlement['total_label'] }}</p>
            </header>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($settlement['rows'] as $index => $row)
                    <button type="button" @click="inspectMethod({{ $methodArguments[$index] }})"
                        class="flex flex-col gap-2 rounded-xl bg-surface p-4 text-left transition-colors hover:bg-surface-shell">
                        <span class="flex items-start justify-between gap-2">
                            <span class="font-sans text-base font-semibold leading-6 text-ink">{{ $row['name'] }}</span>
                            <span
                                class="shrink-0 rounded bg-surface-track px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta text-ink">{{ $row['share_label'] }}</span>
                        </span>

                        <span class="font-inter text-sm font-bold leading-5 text-ink">{{ $row['value_label'] }}</span>

                        <span class="flex items-start gap-1.5">
                            <x-gpa.icon name="check-circle"
                                class="mt-0.5 h-3 w-3 shrink-0 {{ $noteToneSettlement[$row['note_tone']] }}" />
                            <span class="gpa-body {{ $noteToneSettlement[$row['note_tone']] }}">{{ $row['note'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-surface-track p-4 outline outline-1 -outline-offset-1 outline-line-board">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-strong">
                        <x-gpa.icon name="leaf" class="h-4 w-4 text-accent" />
                    </span>
                    <span class="min-w-0">
                        <span class="block font-sans text-base font-bold leading-6 text-ink">{{ $settlement['seal']['title'] }}</span>
                        <span class="mt-0.5 block gpa-note text-success-deep">{{ $settlement['seal']['note'] }}</span>
                    </span>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" @click="sealRecap()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3 py-2 gpa-note font-bold text-ink outline outline-1 -outline-offset-1 outline-accent-deep transition-colors hover:bg-accent-deep hover:text-ink">
                        <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0" />
                        Tanda Tangan
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection

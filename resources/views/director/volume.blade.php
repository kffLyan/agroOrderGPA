@extends('layouts.director')

@section('title', 'Volume Komoditas // Stok Panen & Kapasitas Pasokan')

@section('content')
    @php
        // Peta nada visual mengikuti palet design "Volume Komoditas, Stok Panen
        // & Kapasitas Pasokan" yang memakai token warna yang sama dengan modul
        // Penjualan dan Dashboard Direksi.
        $iconTileTone = [
            'neutral' => 'bg-surface-track text-ink',
            'accent' => 'bg-accent/40 text-ink-strong',
        ];

        $noteTone = [
            'muted' => 'text-success-deep',
            'ink' => 'text-ink',
        ];

        $chipTone = [
            'accent' => 'bg-accent text-ink',
            'outlined' => 'bg-accent text-ink-strong outline outline-1 -outline-offset-1 outline-accent-deep',
        ];

        $statusTone = [
            'success' => 'bg-accent text-ink-strong outline outline-1 -outline-offset-1 outline-accent-deep',
            'caution' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution',
        ];

        $headroomTone = [
            'shell' => 'bg-surface-shell text-ink',
            'danger' => 'bg-danger-soft/40 text-danger',
        ];

        $shareTone = [
            'success' => 'text-success-deep',
            'caution' => 'text-warning-caution',
        ];

        $segmentTone = [
            'reserve' => 'bg-accent',
            'yield' => 'bg-accent-deep',
            'demand' => 'bg-ink-strong',
        ];

        $segmentForecastTone = [
            'reserve' => 'bg-accent/60',
            'yield' => 'bg-accent-deep/60',
            'demand' => 'bg-ink-strong/60',
        ];

        // Argumen Alpine disiapkan sebagai literal JS agar loop tabel dan kartu
        // tidak memerlukan directive `@php` tambahan di tengah view.
        $commodityArguments = array_values(array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $commodities['rows'],
        ));

        $weekArguments = array_values(array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $trend['rows'],
        ));

        $partnerArguments = array_values(array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $allocations['rows'],
        ));
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorVolume(@js($commodities['rows']), @js($trend['rows']), @js($allocations['rows']))">

        {{-- Page header + range badge + aksi rekonsiliasi --}}
        <section class="flex flex-wrap items-start justify-between gap-4 pb-6 border-b border-line-board">
            <div class="min-w-0 space-y-1">
                <p class="gpa-eyebrow flex flex-wrap items-center gap-2">
                    <span class="h-3 w-36 rounded bg-surface-track" aria-hidden="true"></span>
                    {{ $volumeHeader['eyebrow_meta'] }}
                </p>
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Monitoring Volume Komoditas, Stok Panen<br>&amp; Kapasitas Pasokan
                </h1>
                <p class="max-w-3xl text-sm leading-5 text-ink-body">{{ $volumeHeader['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 flex-col items-stretch gap-3">
                <span
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-surface px-3 outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="gpa-meta text-success-deep">{{ $volumeHeader['range_label'] }}</span>
                    <span class="gpa-meta font-bold text-ink">{{ $volumeHeader['range_value'] }}</span>
                    <span class="h-3 w-3 rounded-sm bg-success-deep" aria-hidden="true"></span>
                </span>

                <button type="button" @click="reconcile()"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-accent px-4 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                    <x-gpa.icon name="scale" class="h-4 w-4 shrink-0" />
                    <span class="text-sm font-semibold leading-5 text-ink font-inter">
                        {{ $volumeHeader['action_label'] }}
                    </span>
                </button>
            </div>
        </section>

        {{-- Pita KPI neraca volume --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <article class="flex h-full flex-col justify-between gap-3 overflow-hidden rounded-2xl bg-surface p-5 shadow-card">
                    <header class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro-bold uppercase text-success-deep">{{ $card['label'] }}</h2>
                        <span
                            class="inline-flex shrink-0 items-center justify-center rounded-md px-1.5 py-3 {{ $iconTileTone[$card['icon_tone']] }}">
                            <x-gpa.icon :name="$card['icon']" class="h-4 w-4" />
                        </span>
                    </header>

                    <p class="flex flex-wrap items-baseline gap-x-2">
                        @foreach ($card['value_parts'] as $part)
                            @if ($part['emphasis'])
                                <span class="font-mono text-[28px] font-bold leading-7 text-ink">{{ $part['text'] }}</span>
                            @else
                                <span class="font-mono text-xs font-medium leading-4 text-success-deep">{{ $part['text'] }}</span>
                            @endif
                        @endforeach
                    </p>

                    <p class="flex flex-wrap items-center gap-3">
                        @foreach ($card['notes'] as $note)
                            <span class="flex items-center gap-1.5 font-mono text-xs leading-4 {{ $noteTone[$note['tone']] }}">
                                @if ($note['dot'] ?? false)
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                                @endif
                                {{ $note['text'] }}
                            </span>
                        @endforeach
                    </p>

                    <footer
                        class="flex items-center justify-between gap-2 border-t border-surface-track pt-2.5">
                        <span class="gpa-micro-bold text-success-deep">{{ $card['footer']['label'] }}</span>
                        <span class="rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold {{ $chipTone[$card['footer']['chip_tone']] }}">
                            {{ $card['footer']['chip'] }}
                        </span>
                    </footer>
                </article>
            @endforeach
        </section>

        {{-- Neraca volume lima komoditas inti --}}
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-line-board">
                <div class="flex min-w-0 flex-col gap-1">
                    <p class="flex items-center gap-2">
                        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-success-deep">
                            <x-gpa.icon name="package" class="h-3 w-3 text-accent" />
                        </span>
                        <h2 class="font-inter text-lg font-semibold leading-6 text-ink">{{ $commodities['title'] }}</h2>
                    </p>
                    <p class="text-xs leading-4 text-ink-body">{{ $commodities['subtitle'] }}</p>
                </div>

                <span class="rounded bg-surface-track px-2 py-1 gpa-micro-bold text-success-deep">
                    {{ $volumeHeader['refresh_label'] }}
                </span>
            </header>

            <div class="gpa-scroll-x overflow-x-auto py-1">
                <div class="flex min-w-[980px] gap-4">
                    @foreach ($commodities['rows'] as $index => $row)
                        <button type="button" @click="inspectCommodity({{ $commodityArguments[$index] }})"
                            class="flex h-full w-[196px] shrink-0 flex-col justify-between gap-6 rounded-2xl bg-surface p-4 text-left shadow-card transition-colors hover:bg-surface-shell">
                            <span class="flex flex-col gap-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span
                                        class="rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold text-ink outline outline-1 -outline-offset-1 outline-line-board bg-surface-shell">
                                        {{ $row['code'] }}
                                    </span>
                                    <span class="text-[11px] font-bold leading-[14px] tracking-[0.88px] font-mono {{ $shareTone[$row['share_tone']] }}">
                                        {{ $row['share_label'] }}
                                    </span>
                                </span>

                                <span class="block pt-1.5 font-sans text-lg font-semibold leading-6 text-ink">
                                    {{ $row['name'] }}
                                </span>
                                <span class="block font-sans text-xs leading-4 text-ink-body">{{ $row['grade'] }}</span>

                                <span
                                    class="mt-5 flex flex-col gap-2 border-y border-line-board/60 py-2">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-xs leading-4 text-ink-body">Realisasi:</span>
                                        <span class="text-right font-mono text-base font-bold leading-6 text-ink">
                                            {{ $row['value_label'] }}<br>Ton
                                        </span>
                                    </span>

                                    <span class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-xs leading-4 text-success-deep">&bull; Binaan:</span>
                                        <span class="font-mono text-xs leading-4 text-ink">{{ $row['farmer_label'] }}</span>
                                    </span>

                                    <span class="flex items-center justify-between gap-2">
                                        <span class="font-mono text-xs leading-4 text-success-deep">&bull; Buffer:</span>
                                        <span class="font-mono text-xs leading-4 text-ink">{{ $row['buffer_label'] }}</span>
                                    </span>

                                    <span
                                        class="flex items-center justify-between gap-2 border-t border-surface-track pt-1">
                                        <span class="font-mono text-xs font-medium leading-4 text-ink-body">
                                            Sisa Kuota<br>Bebas:
                                        </span>
                                        <span
                                            class="rounded px-1.5 py-0.5 font-mono text-xs font-bold leading-4 {{ $headroomTone[$row['headroom_tone']] }}">
                                            {{ $row['headroom_label'] }}
                                        </span>
                                    </span>
                                </span>
                            </span>

                            <span
                                class="w-full rounded px-1.5 py-1 text-center text-[9px] font-bold leading-3 gpa-micro-bold {{ $statusTone[$row['status_tone']] }}">
                                {{ $row['status'] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Grafik mingguan + matriks alokasi kontrak B2B --}}
        <section class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <header class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-line-board">
                    <div class="flex min-w-0 flex-col gap-1">
                        <p class="flex items-center gap-2">
                            <span
                                class="rounded bg-surface-track px-2 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold text-ink">
                                {{ $trend['badge'] }}
                            </span>
                            <h2 class="font-sans text-lg font-semibold leading-6 text-ink">{{ $trend['title'] }}</h2>
                        </p>
                        <p class="text-xs leading-4 text-ink-body">{{ $trend['subtitle'] }}</p>
                    </div>

                    <ul class="flex flex-wrap items-center gap-3">
                        @foreach ($trend['legend'] as $item)
                            <li class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-sm {{ $item['swatch'] }}"
                                    aria-hidden="true"></span>
                                <span class="gpa-micro-bold text-ink">{{ $item['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </header>

                <div class="flex items-end gap-3 border-b border-line-board px-2 pt-6 pb-2">
                    @foreach ($trend['rows'] as $index => $row)
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                            <span
                                class="rounded px-1 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold {{ $row['status'] === 'running' ? 'bg-accent text-ink-strong' : ($row['status'] === 'forecast' ? 'text-success-deep' : 'text-ink') }}">
                                {{ $row['total_label'] }}
                            </span>

                            <div class="flex h-52 w-full max-w-[42px] items-end">
                                <button type="button" @click="inspectWeek({{ $weekArguments[$index] }})"
                                    class="flex w-full flex-col justify-end overflow-hidden rounded-t bg-surface-track {{ $row['status'] === 'forecast' ? 'opacity-70' : '' }} {{ $row['status'] === 'running' ? 'shadow-[0_0_0_2px_#aabd06]' : '' }}"
                                    style="height: {{ $row['bar_percent'] }}%">
                                    @foreach ($row['segments'] as $segment)
                                        <span
                                            class="w-full {{ $row['status'] === 'forecast' ? $segmentForecastTone[$segment['key']] : $segmentTone[$segment['key']] }}"
                                            style="height: {{ round($segment['tons'] / $row['total'] * 100, 2) }}%"></span>
                                    @endforeach
                                </button>
                            </div>

                            <span
                                class="text-[9px] font-bold leading-3 tracking-[1.08px] font-mono {{ $row['status'] === 'running' ? 'text-ink' : 'text-success-deep' }}">
                                {{ $row['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-2">
                    <p class="gpa-micro-bold text-success-deep">{{ $trend['metrology'] }}</p>
                    <p class="gpa-micro-bold text-ink">{{ $trend['peak_label'] }}</p>
                    <p class="gpa-micro-bold text-ink">{{ $trend['confidence_label'] }}</p>
                </div>
            </div>

            <div class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-4">
                    <header class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-line-board">
                        <div class="flex min-w-0 flex-col gap-1">
                            <p class="flex items-center gap-2">
                                <span
                                    class="rounded bg-surface-track px-2 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold text-ink">
                                    {{ $allocations['badge'] }}
                                </span>
                                <h2 class="font-sans text-lg font-semibold leading-6 text-ink">
                                    {{ $allocations['title'] }}
                                </h2>
                            </p>
                            <p class="text-xs leading-4 text-ink-body">{{ $allocations['subtitle'] }}</p>
                        </div>

                        <span class="rounded bg-accent px-2 py-1 text-[9px] font-bold leading-3 gpa-micro-bold text-ink-strong">
                            {{ $allocations['serap_label'] }}
                        </span>
                    </header>

                    <div class="flex flex-col gap-3">
                        @foreach ($allocations['rows'] as $index => $row)
                            <button type="button" @click="inspectPartner({{ $partnerArguments[$index] }})"
                                class="flex items-center justify-between gap-3 rounded-xl bg-surface-shell p-3 text-left outline outline-1 -outline-offset-1 outline-line-board/60 transition-colors hover:bg-surface-muted">
                                <span class="flex min-w-0 flex-col gap-0.5">
                                    <span class="font-sans text-sm font-bold leading-5 text-ink">{{ $row['name'] }}</span>
                                    <span class="font-mono text-xs leading-4 text-success-deep">
                                        SENTRA PRODUKSI: {{ $row['sentra'] }}
                                    </span>
                                </span>

                                <span class="flex shrink-0 flex-col items-end gap-0.5">
                                    <span class="text-right font-mono text-sm font-bold leading-5 tracking-[0.28px] text-ink">
                                        {{ $row['commitment_label'] }}
                                    </span>
                                    <span
                                        class="text-right font-mono text-xs font-bold leading-4 tracking-[0.28px] text-ink">
                                        Ton/mgg
                                    </span>
                                    <span class="text-right font-inter text-[9px] font-semibold leading-3 tracking-[1.08px] text-success-deep">
                                        {{ $row['priority'] }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <footer class="border-t border-line-board pt-3">
                    <div class="flex items-center justify-between gap-3 border-t border-line-board pt-3">
                        <span class="text-[11px] font-medium leading-[14px] tracking-[0.88px] font-mono text-success-deep">
                            {{ $allocations['total_label'] }}
                        </span>
                        <span
                            class="rounded bg-accent/60 px-2 py-0.5 text-[11px] font-bold leading-[14px] tracking-[0.88px] font-mono text-ink outline outline-1 -outline-offset-1 outline-accent-deep">
                            {{ $allocations['total_value_label'] }}
                        </span>
                    </div>
                </footer>
            </div>
        </section>
    </div>
@endsection

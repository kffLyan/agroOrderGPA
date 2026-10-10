@extends('layouts.armada')

@section('title', 'Daftar Tugas Armada')

@section('content')
    @php
        $sequenceTone = [
            'ink' => 'bg-ink text-accent',
            'neutral' => 'bg-surface-disabled text-ink-body',
        ];

        $stateTone = [
            'road' => 'bg-accent text-ink',
            'queued' => 'bg-surface-track text-ink-quiet',
        ];

        $timeTone = [
            'warning' => 'text-warning-deep',
            'ink' => 'text-ink-body',
        ];

        $telemetryTone = [
            'success' => 'text-success-deep',
            'ink' => 'text-ink-body',
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-[448px] flex-col gap-5 px-4 pb-20"
        x-data="armadaTasks(@js($filters))">

        {{-- Identitas supir bertugas --}}
        <section class="flex flex-col gap-3 rounded-xl bg-surface p-4 shadow-card">
            <header class="flex items-start justify-between gap-3 border-surface-disabled pb-1">
                <div class="flex min-w-0 flex-col gap-1">
                    <p class="gpa-micro-bold text-ink-quiet">{{ $assignment['label'] }}</p>

                    <p class="flex items-center gap-1.5">
                        <span class="text-lg font-semibold leading-6 text-ink">{{ $assignment['name'] }}</span>
                        <span class="h-3.5 w-3.5 shrink-0 bg-success-deep" aria-hidden="true"></span>
                    </p>

                    <p class="gpa-meta text-ink-body">
                        {{ $assignment['code'] }} &bull; {{ $assignment['unit'] }}<br>{{ $assignment['slot'] }}
                    </p>
                </div>

                <div class="relative w-[125px] shrink-0">
                    <span class="absolute right-0 top-0 flex flex-col rounded bg-accent px-4 py-1 text-right">
                        <span class="font-mono text-base font-bold uppercase leading-6 tracking-[0.8px] text-ink">{{ $assignment['status_label'] }}</span>
                        <span class="font-mono text-base font-bold uppercase leading-6 tracking-[0.8px] text-ink">{{ $assignment['status_tail'] }}</span>
                    </span>

                    <span class="absolute bottom-0 left-0 flex flex-col items-end">
                        <span class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-warning-deep">
                            {{ $assignment['deadline_label'] }} {{ $assignment['deadline'] }}
                        </span>
                    </span>
                </div>
            </header>

        </section>

        {{-- Filter status antrean --}}
        <section class="flex items-center justify-between gap-2 overflow-hidden border-b border-line-board pb-2">
            @foreach ($filters as $filter)
                <button type="button" @click="selectFilter('{{ $filter['key'] }}')"
                    :aria-pressed="filter === '{{ $filter['key'] }}' ? 'true' : 'false'"
                    @class([
                        'h-8 flex-1 rounded-full font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] transition-colors',
                        'bg-accent text-ink shadow-sub outline outline-1 -outline-offset-1 outline-success-deep' => $filter['active'],
                        'bg-surface text-ink-body outline outline-1 -outline-offset-1 outline-line-board' => ! $filter['active'],
                    ])>
                    {{ $filter['label'] }} ({{ $filter['count'] }})
                </button>
            @endforeach
        </section>

        {{-- Judul antrean --}}
        <section class="flex items-center justify-between gap-2 pt-1">
            <p class="flex items-center gap-1.5">
                <span class="text-lg font-semibold leading-6 text-ink">{{ $queue_title }}</span>
            </p>

        </section>

        {{-- Kartu titik bongkar --}}
        <div class="flex flex-col gap-4">
            @foreach ($drops as $drop)
                <article @class([
                    'relative flex flex-col gap-3 rounded-2xl bg-surface p-4',
                    'outline outline-2 -outline-offset-2 outline-success-deep' => ! $drop['muted'],
                    'opacity-90 shadow-card' => $drop['muted'],
                ]) x-show="filter === 'all' || filter === '{{ $drop['state'] }}'">

                    <header class="flex items-start justify-between gap-2 border-b border-surface-disabled pb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] {{ $sequenceTone[$drop['sequence_tone']] }}">
                                {{ $drop['sequence_label'] }}
                            </span>

                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] {{ $stateTone[$drop['state']] }}">
                                {{ $drop['state_label'] }}
                            </span>
                        </div>

                        <div class="flex flex-col items-end gap-1 pt-1">
                            <p class="text-right font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] {{ $timeTone[$drop['time_tone']] }}">
                                {{ $drop['time_label'] }} {{ $drop['time'] }}
                            </p>
                            <p class="text-right font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet">
                                {{ $drop['priority'] }}
                            </p>
                        </div>
                    </header>

                    <div class="flex flex-col gap-1">
                        <p class="flex items-center justify-between gap-2">
                            <span class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet">NO. SURAT JALAN:</span>
                            <span class="rounded bg-surface-pill px-1 font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink">{{ $drop['sj'] }}</span>
                        </p>

                        <p class="text-lg font-semibold leading-6 text-ink">{{ $drop['client'] }}</p>

                        <p class="flex items-start gap-1">
                            <x-gpa.icon name="map-pin" class="mt-0.5 h-3 w-2.5 shrink-0 text-ink-quiet" />
                            <span class="text-xs font-normal leading-4 text-ink-body">{{ $drop['address'] }}</span>
                        </p>
                    </div>

                    <div class="flex flex-col gap-1.5 rounded-lg bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board">
                        <p class="flex items-center justify-between gap-2 border-b border-line-board/60 pb-1">
                            <span class="font-mono text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink-quiet">{{ $drop['cargo_label'] }}</span>
                            <span class="font-mono text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink">{{ $drop['cargo_net'] }}</span>
                        </p>

                        <div class="flex flex-col gap-1 pb-0.5">
                            @foreach ($drop['items'] as $item)
                                <p class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-medium leading-4 text-ink">{{ $item['label'] }}</span>
                                    <span class="font-mono text-xs font-bold leading-4 text-ink">{{ $item['weight'] }}</span>
                                </p>
                            @endforeach
                        </div>

                        <p @class([
                            'flex items-center justify-between gap-2 border-t border-line-board/40 pt-1',
                            'text-warning-deep' => $drop['window_tone'] === 'warning',
                        ])>
                            <span class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">{{ $drop['window_label'] }}</span>
                            <span class="text-right font-mono text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink">{{ $drop['window'] }}</span>
                        </p>
                    </div>

                    <footer class="flex flex-col gap-1 pt-1">
                        <button type="button" @click="processPod(@js($drop))"
                            @disabled($drop['primary']['locked'] ?? false)
                            @class([
                                'flex h-11 w-full items-center justify-center gap-1.5 rounded-lg font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px]',
                                'bg-accent text-ink shadow-sub' => ! ($drop['primary']['locked'] ?? false),
                                'cursor-not-allowed bg-surface-pill text-ink-quiet outline outline-1 -outline-offset-1 outline-line-board' => $drop['primary']['locked'] ?? false,
                            ])>
                            <x-gpa.icon :name="$drop['primary']['icon']" class="h-[15px] w-[15px] shrink-0" />
                            {{ $drop['primary']['label'] }}
                        </button>

                        <button type="button" @click="checkRoute(@js($drop))"
                            class="flex h-11 w-full items-center justify-center gap-1 rounded-lg bg-surface font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] text-ink outline outline-1 -outline-offset-1 outline-line-strong">
                            <x-gpa.icon :name="$drop['secondary']['icon']" class="h-3 w-3 shrink-0" />
                            {{ $drop['secondary']['label'] }}
                        </button>
                    </footer>
                </article>
            @endforeach
        </div>

        {{-- Bantuan darurat dispatch --}}
        <section class="flex flex-col gap-3 rounded-xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-3">
                <p class="flex items-center gap-2">
                    <x-gpa.icon name="headset" class="h-[15px] w-4 shrink-0 text-ink" />

                    <span class="flex flex-col gap-0.5">
                        <span class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet">{{ $support['label'] }}</span>
                        <span class="font-mono text-sm font-bold leading-5 tracking-[0.28px] text-ink">{{ $support['phone'] }}</span>
                    </span>
                </p>

                <button type="button" @click="callSupport()"
                    class="flex h-9 items-center gap-1 rounded bg-ink px-3 font-mono text-[10px] font-bold leading-3 tracking-[1px] text-white">
                    <x-gpa.icon name="phone" class="h-[10.5px] w-[10.5px] shrink-0" />
                    {{ $support['action'] }}
                </button>
            </header>
        </section>
    </div>
@endsection

@extends('layouts.armada')

@section('title', 'Daftar Tugas Pengantaran Armada')

@section('content')
    @php
        $sequenceTone = [
            'ink'     => 'bg-ink text-accent',
            'neutral' => 'bg-surface-disabled text-ink-body',
        ];

        $stateTone = [
            'road'   => 'bg-accent text-ink',
            'queued' => 'bg-surface-track text-ink-quiet',
            'ready'  => 'bg-accent/40 text-ink',
            'done'   => 'bg-surface-disabled text-ink-body',
        ];

        $timeTone = [
            'warning' => 'text-warning-deep',
            'ink'     => 'text-ink-body',
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-[480px] flex-col gap-4 px-4 pb-24"
        x-data="armadaTasks(@js($filters))">

        {{-- Identitas supir bertugas --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-start justify-between gap-3 border-b border-surface-shell pb-3">
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <p class="gpa-micro-bold text-ink-quiet uppercase tracking-wider text-[10px]">{{ $assignment['label'] }}</p>

                    <p class="flex items-center gap-2">
                        <span class="text-base font-bold text-ink">{{ $assignment['name'] }}</span>
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-deep" title="Status Aktif"></span>
                    </p>

                    <p class="gpa-meta text-xs text-ink-body">
                        {{ $assignment['code'] }} &bull; {{ $assignment['unit'] }}
                    </p>
                </div>

                <div class="flex flex-col items-end gap-1">
                    <span class="flex flex-col items-end rounded-lg bg-accent px-3 py-1 shadow-sub">
                        <span class="font-mono text-xs font-extrabold uppercase leading-4 text-ink">{{ $assignment['status_label'] }}</span>
                        <span class="font-mono text-xs font-extrabold uppercase leading-4 text-ink">{{ $assignment['status_tail'] }}</span>
                    </span>

                    <span class="font-mono text-[10px] font-semibold text-warning-deep">
                        {{ $assignment['deadline_label'] }} {{ $assignment['deadline'] }}
                    </span>
                </div>
            </header>
        </section>

        {{-- Filter status antrean --}}
        <section class="flex items-center justify-between gap-1.5 overflow-hidden border-b border-line-hair pb-3">
            @foreach ($filters as $filter)
                <button type="button" @click="selectFilter('{{ $filter['key'] }}')"
                    :aria-pressed="filter === '{{ $filter['key'] }}' ? 'true' : 'false'"
                    :class="filter === '{{ $filter['key'] }}'
                        ? 'bg-accent text-ink shadow-sub font-bold outline outline-1 -outline-offset-1 outline-success-deep'
                        : 'bg-surface text-ink-body font-medium outline outline-1 -outline-offset-1 outline-line-board hover:text-ink'"
                    class="h-9 flex-1 rounded-xl font-mono text-[11px] uppercase transition-all">
                    {{ $filter['label'] }} ({{ $filter['count'] }})
                </button>
            @endforeach
        </section>

        {{-- Judul antrean --}}
        <section class="flex items-center justify-between gap-2 pt-1">
            <h2 class="text-base font-bold text-ink">{{ $queue_title }}</h2>
            <span class="rounded bg-surface-disabled px-2 py-0.5 font-mono text-[10px] font-semibold text-ink-body">
                Total: {{ count($drops) }} Titik
            </span>
        </section>

        {{-- Kartu titik pengantaran --}}
        <div class="flex flex-col gap-3.5">
            @forelse ($drops as $drop)
                <article @class([
                    'relative flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card transition-all',
                    'outline outline-2 -outline-offset-2 outline-accent' => ! $drop['muted'],
                    'opacity-80' => $drop['muted'],
                ]) x-show="filter === 'all' || filter === '{{ $drop['state'] }}'">

                    <header class="flex items-start justify-between gap-2 border-b border-surface-shell pb-2.5">
                        <div class="flex items-center gap-1.5">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase {{ $sequenceTone[$drop['sequence_tone']] ?? 'bg-ink text-accent' }}">
                                {{ $drop['sequence_label'] }}
                            </span>

                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase {{ $stateTone[$drop['state']] ?? 'bg-surface-track text-ink' }}">
                                {{ $drop['state_label'] }}
                            </span>
                        </div>

                        <div class="flex flex-col items-end gap-0.5">
                            <p class="font-mono text-xs font-bold {{ $timeTone[$drop['time_tone']] ?? 'text-ink' }}">
                                {{ $drop['time_label'] }}: {{ $drop['time'] }}
                            </p>
                            <p class="font-mono text-[9px] font-semibold text-ink-quiet">
                                {{ $drop['priority'] }}
                            </p>
                        </div>
                    </header>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-[10px] font-semibold text-ink-quiet">NO. SURAT JALAN:</span>
                            <span class="rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                {{ $drop['sj'] }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-ink">{{ $drop['client'] }}</h3>

                        <p class="flex items-start gap-1.5 text-xs text-ink-body">
                            <x-gpa.icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-accent-deep" />
                            <span>{{ $drop['address'] }}</span>
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-1.5">
                            <span class="font-mono text-[10px] font-bold text-ink-body">{{ $drop['cargo_label'] }}</span>
                            <span class="font-mono text-[10px] font-bold text-ink">{{ $drop['cargo_net'] }}</span>
                        </div>

                        <div class="flex flex-col gap-1">
                            @foreach ($drop['items'] as $item)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="text-ink">{{ $item['label'] }}</span>
                                    <span class="font-mono font-bold text-ink">{{ $item['weight'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between gap-2 border-t border-line-hair pt-1.5">
                            <span class="font-mono text-[10px] text-ink-body">{{ $drop['window_label'] }}</span>
                            <span class="text-right font-mono text-[10px] font-semibold text-ink">{{ $drop['window'] }}</span>
                        </div>
                    </div>

                    <footer class="flex flex-col gap-2 pt-1">
                        @if ($drop['state'] === 'road')
                            <a href="{{ route('armada.pod', ['order_id' => $drop['id']]) }}"
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-accent font-mono text-xs font-bold uppercase tracking-wider text-ink shadow-sub transition-transform active:scale-[0.98]">
                                <x-gpa.icon name="qrcode" class="h-4 w-4 shrink-0" />
                                PROSES SCAN POD (SELESAIKAN)
                            </a>
                        @elseif ($drop['state'] === 'ready')
                            <a href="{{ route('armada.dispatch', ['order_id' => $drop['id']]) }}"
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-ink font-mono text-xs font-bold uppercase tracking-wider text-accent shadow-sub transition-transform active:scale-[0.98]">
                                <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0" />
                                LIHAT SURAT JALAN & BERANGKAT
                            </a>
                        @else
                            <div class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-surface-disabled font-mono text-xs font-bold text-ink-quiet">
                                <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                                PENGANTARAN SELESAI
                            </div>
                        @endif

                        <button type="button" @click="checkRoute(@js($drop))"
                            class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-surface font-mono text-xs font-bold text-ink outline outline-1 -outline-offset-1 outline-line-strong transition-colors hover:bg-surface-disabled">
                            <x-gpa.icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-ink" />
                            BUKA NAVIGASI PETA LOKASI
                        </button>
                    </footer>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-surface p-8 text-center shadow-card">
                    <x-gpa.icon name="package" class="h-10 w-10 text-ink-quiet" />
                    <p class="text-sm font-bold text-ink">Belum Ada Tugas Pengantaran</p>
                    <p class="text-xs text-ink-body">Semua pesanan telah diselesaikan atau menunggu jadwal dispatch berikutnya.</p>
                </div>
            @endforelse
        </div>

        {{-- Bantuan darurat dispatch --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-disabled text-ink">
                        <x-gpa.icon name="headset" class="h-4 w-4 shrink-0" />
                    </span>

                    <div class="flex flex-col">
                        <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">{{ $support['label'] }}</span>
                        <span class="font-mono text-xs font-bold text-ink">{{ $support['phone'] }}</span>
                    </div>
                </div>

                <button type="button" @click="callSupport('{{ $support['phone'] }}')"
                    class="flex h-9 items-center gap-1.5 rounded-lg bg-ink px-3.5 font-mono text-xs font-bold text-white shadow-sub transition-transform active:scale-95">
                    <x-gpa.icon name="phone" class="h-3 w-3 shrink-0" />
                    {{ $support['action'] }}
                </button>
            </header>
        </section>
    </div>
@endsection

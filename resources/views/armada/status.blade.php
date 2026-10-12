@extends('layouts.armada')

@section('title', 'Status Armada & Kelaikan Kendaraan')

@section('content')
    @php
        $tagTone = [
            'neutral' => 'bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board',
            'accent'  => 'bg-accent/40 text-ink outline outline-1 -outline-offset-1 outline-accent',
        ];

        $cardTone = [
            'success' => 'bg-surface-shell text-success-deep outline outline-1 -outline-offset-1 outline-line-board',
            'neutral' => 'bg-surface-shell text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
            'warning' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/30',
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-4 px-4 pb-24"
        x-data="armadaStatus">

        {{-- Status telemetri operasional kendaraan --}}
        <section class="flex items-center justify-between gap-3 rounded-2xl bg-surface p-4 shadow-card border-l-4 border-accent">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-ink shadow-sm">
                    <x-gpa.icon :name="$vehicle['icon']" class="h-5 w-5 shrink-0" />
                </span>

                <div class="flex flex-col">
                    <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">{{ $vehicle['label'] }}</span>
                    <span class="text-base font-bold text-ink">{{ $vehicle['status'] }} &bull; {{ $vehicle['status_tail'] }}</span>
                </div>
            </div>

            <button type="button" @click="refreshTelemetry()"
                class="shrink-0 rounded-xl bg-accent px-3 py-1.5 font-mono text-[10px] font-bold text-ink shadow-sub transition-transform active:scale-95">
                {{ $vehicle['badge'] }}<br><span x-text="syncLabel()">{{ $vehicle['badge_tail'] }}</span>
            </button>
        </section>

        {{-- Data pengemudi & kendaraan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">{{ $driver['section'] }}</span>
                <span class="rounded bg-surface-shell px-2 py-0.5 font-mono text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    {{ $driver['code_label'] }}{{ $driver['code'] }}
                </span>
            </header>

            <div class="flex items-center gap-3 pt-1">
                <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand text-accent font-inter text-xl font-bold shadow-sub">
                    {{ $driver['initials'] }}
                    <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-accent ring-2 ring-white"></span>
                </div>

                <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                    <span class="truncate text-base font-bold text-ink">{{ $driver['name'] }}</span>
                    <span class="font-mono text-xs text-ink-body">{{ $driver['unit_code'] }}</span>

                    <div class="flex flex-wrap gap-1 pt-1">
                        @foreach ($driver['tags'] as $tag)
                            <span class="rounded px-2 py-0.5 font-mono text-[9px] font-bold {{ $tagTone[$tag['tone']] ?? 'bg-surface text-ink' }}">
                                {{ $tag['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 border-t border-surface-shell pt-3">
                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <span class="font-mono text-[9px] font-bold text-ink-quiet uppercase">{{ $driver['specs']['label'] }}</span>
                    @foreach ($driver['specs']['lines'] as $line)
                        <span class="font-mono text-xs font-bold text-ink">{{ $line }}</span>
                    @endforeach
                    <span class="text-[10px] text-ink-body">{{ $driver['specs']['foot'] }}</span>
                </div>

                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <span class="font-mono text-[9px] font-bold text-ink-quiet uppercase">{{ $driver['plate_label'] }} {{ $driver['plate_tail'] }}</span>
                    <span class="font-mono text-sm font-extrabold text-ink">{{ $driver['plate'] }}</span>
                    <span class="text-[10px] text-ink-body">{{ $driver['plate_note'] }}</span>
                </div>
            </div>
        </section>

        {{-- Telemetri IoT kendaraan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">{{ $telemetry['section'] }}</span>
                <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                    {{ $telemetry['badge'] }} {{ $telemetry['badge_tail'] }}
                </span>
            </header>

            {{-- Suhu chiller --}}
            <div class="flex flex-col gap-1 rounded-xl bg-brand p-3.5 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="thermometer" class="h-4 w-4 text-accent" />
                        <span class="font-mono text-[10px] font-bold text-accent uppercase">{{ $telemetry['chiller']['label'] }}</span>
                    </div>
                    <span class="rounded bg-accent px-2 py-0.5 font-mono text-[9px] font-bold text-ink">
                        {{ $telemetry['chiller']['badge'] }}
                    </span>
                </div>
                <div class="flex items-baseline justify-between pt-1">
                    <span class="font-inter text-2xl font-extrabold text-white">{{ $telemetry['chiller']['value'] }}</span>
                    <span class="text-xs text-white/80 font-mono">{{ $telemetry['chiller']['range_label'] }} {{ $telemetry['chiller']['range_min'] }} {{ $telemetry['chiller']['range_max'] }}</span>
                </div>
            </div>

            {{-- Kapasitas muat box --}}
            <div class="flex flex-col gap-1.5 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-mono text-[10px] font-bold text-ink-quiet uppercase">{{ $telemetry['capacity']['label'] }}</span>
                    <span class="font-mono font-bold text-ink">{{ $telemetry['capacity']['value'] }} {{ $telemetry['capacity']['percent'] }}</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-surface-disabled overflow-hidden">
                    <div class="h-full rounded-full bg-accent" style="width: {{ $telemetry['capacity']['percent_value'] }}%"></div>
                </div>
            </div>

            {{-- Sensor bahan bakar & GPS --}}
            <div class="grid grid-cols-2 gap-2">
                @foreach ($telemetry['cards'] as $card)
                    <div class="flex flex-col gap-1 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/50">
                        <div class="flex items-center gap-1.5 text-ink-quiet">
                            <x-gpa.icon :name="$card['icon']" class="h-3.5 w-3.5" />
                            <span class="font-mono text-[9px] font-bold uppercase">{{ $card['label'] }}</span>
                        </div>
                        <span class="font-mono text-sm font-bold text-ink">{{ $card['value'] }}</span>
                        <span class="text-[10px] text-ink-body">{!! $card['note'] !!}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Audit pre-trip kelaikan pagi --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">{{ $trip['section'] }}</span>
                    <span class="text-sm font-bold text-ink">{{ $trip['title'] }} &bull; {{ $trip['subtitle'] }}</span>
                </div>
                <span class="rounded bg-accent px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                    {{ $trip['badge'] }}
                </span>
            </header>

            <div class="flex flex-col gap-1.5">
                @foreach ($trip['items'] as $item)
                    <div class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-ink text-accent">
                                <x-gpa.icon name="check" class="h-3 w-3" />
                            </span>
                            <span class="text-xs text-ink">{{ $item['label'] }}{{ $item['tail'] ? ' ' . $item['tail'] : '' }}</span>
                        </div>
                        <span class="font-mono text-[10px] font-semibold text-ink-quiet">{{ $item['time'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between rounded-xl bg-surface-disabled p-2.5 text-xs">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                    <span class="font-medium text-ink">{{ $trip['mechanic_label'] }}: <strong>{{ $trip['mechanic'] }}</strong></span>
                </div>
                <span class="font-mono text-[10px] text-ink-quiet">{{ $trip['sign_label'] }}: {{ $trip['sign_tail'] }}</span>
            </div>
        </section>

        {{-- Rekap kinerja shift --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">{{ $performance['section'] }}</span>
                    <span class="text-sm font-bold text-ink">{{ $performance['subtitle'] }}</span>
                </div>
                <span class="rounded bg-surface-shell px-2 py-0.5 font-mono text-[9px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    {{ $performance['badge'] }} {{ $performance['badge_tail'] }}
                </span>
            </header>

            <div class="grid grid-cols-2 gap-2">
                @foreach ($performance['cards'] as $card)
                    <div @class([
                        'flex flex-col gap-1 rounded-xl p-3 min-h-[95px] justify-between',
                        $cardTone[$card['tone']],
                    ])>
                        <span class="font-mono text-[9px] font-bold uppercase">{{ $card['label'] }}</span>
                        <span class="font-inter text-base font-bold text-ink">{{ $card['value'] }}</span>
                        <span class="text-[9px] text-ink-body font-mono">{{ $card['note'][0] }} &bull; {{ $card['note'][1] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Dukungan operasional & hotline --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">{{ $support['section'] }} {{ $support['section_tail'] }}</span>
                <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[9px] font-bold text-ink">
                    {{ $support['badge'] }} {{ $support['badge_tail'] }}
                </span>
            </header>

            <button type="button" @click="openReport()"
                class="flex items-center justify-center gap-2 rounded-xl p-3 outline outline-2 -outline-offset-2 outline-warning-caution bg-warning-cream/20 text-warning-deep font-bold text-xs uppercase hover:bg-warning-cream/40 transition-colors">
                <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0" />
                <span>{{ $support['report_label'] }}</span>
            </button>

            <button type="button" @click="callDispatch('021-890-1234')"
                class="flex items-center justify-between rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-disabled transition-colors">
                <div class="flex flex-col text-left">
                    <span class="font-mono text-[9px] text-ink-quiet uppercase">{{ $support['dispatch_label'] }}</span>
                    <span class="font-mono text-xs font-bold text-ink">{{ $support['dispatch_value'] }}</span>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-ink text-accent">
                    <x-gpa.icon name="phone" class="h-4 w-4" />
                </span>
            </button>

            <button type="button" @click="openSop()"
                class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-disabled transition-colors">
                <div class="flex items-center gap-2 text-xs text-ink">
                    <x-gpa.icon name="file-text" class="h-4 w-4 text-ink-body" />
                    <span>{{ $support['sop_label'] }} {{ $support['sop_tail'] }}</span>
                </div>
                <span class="rounded bg-surface-disabled px-2 py-0.5 font-mono text-[9px] font-bold text-ink-body">
                    {{ $support['sop_badge'] }}
                </span>
            </button>

            {{-- Form tutup shift --}}
            <form id="close-shift-form" method="POST" action="{{ route('armada.status.close-shift') }}" class="hidden">
                @csrf
            </form>

            <div class="flex flex-col gap-2 border-t border-surface-shell pt-3">
                <button type="button" @click="closeShift(); document.getElementById('close-shift-form')?.submit();"
                    :disabled="closed"
                    class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-accent font-mono text-xs font-bold uppercase tracking-wider text-ink shadow-pop transition-transform active:scale-[0.98]">
                    <x-gpa.icon name="check-circle" class="h-4 w-4" />
                    <span>{{ $closure['action'] }} {{ $closure['action_tail'] }}</span>
                </button>

                <p class="text-center font-mono text-[10px] text-ink-body">
                    {{ $closure['note'] }} {{ $closure['note_tail'] }}
                </p>
            </div>
        </section>

        <p class="text-center font-mono text-[10px] text-ink-quiet pt-2">
            {!! $closure['footer'] !!} {!! $closure['footer_tail'] !!}
        </p>
    </div>
@endsection

@extends('layouts.armada')

@section('title', 'Status Armada')

@section('content')
    @php
        $tagTone = [
            'neutral' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
            'accent' => 'bg-accent/40 text-ink outline outline-1 -outline-offset-1 outline-accent',
        ];

        $cardTone = [
            'success' => 'bg-surface-shell text-success-deep outline outline-1 -outline-offset-1 outline-line-board',
            'neutral' => 'bg-surface-shell text-ink-body outline outline-1 -outline-offset-1 outline-line-board',
            'warning' => 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/30',
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-5 px-4 pb-20"
        x-data="armadaStatus">

        {{-- Status telomeri kendaraan --}}
        <section class="flex items-center justify-between gap-3 rounded-xl bg-surface py-4 pl-4 pr-4 shadow-sub border-l-4 border-accent">
            <p class="flex items-center">
                <span class="flex h-8 w-[30px] shrink-0 items-center justify-center rounded-lg bg-accent">
                    <x-gpa.icon :name="$vehicle['icon']" class="h-[15px] w-4 shrink-0 text-ink" />
                </span>

                <span class="flex flex-col pl-3">
                    <span class="font-mono text-[9px] font-semibold leading-3 tracking-[0.45px] text-ink-body">{{ $vehicle['label'] }}</span>
                    <span class="text-lg font-semibold leading-6 text-ink">{{ $vehicle['status'] }}<br>{{ $vehicle['status_tail'] }}</span>
                </span>
            </p>

            <button type="button" @click="refreshTelemetry()"
                class="shrink-0 rounded bg-accent px-2 py-0.5 font-mono text-[10px] font-bold leading-3 tracking-[1px] text-ink outline outline-1 -outline-offset-1 outline-accent-deep">
                {{ $vehicle['badge'] }}<br><span x-text="syncLabel()">{{ $vehicle['badge_tail'] }}</span>
            </button>
        </section>

        {{-- Data pengemudi dan unit --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-track pb-2">
                <p class="font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] text-ink-body">
                    {{ $driver['section'] }}
                </p>

                <span class="rounded bg-surface-pill px-2 py-0.5 font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink">
                    {{ $driver['code_label'] }}<br>{{ $driver['code'] }}
                </span>
            </header>

            <div class="flex items-start">
                <span class="relative flex shrink-0 items-center justify-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-xl bg-brand shadow-sub outline outline-2 -outline-offset-2 outline-accent">
                        <span class="font-inter text-xl font-bold leading-7 text-accent">{{ $driver['initials'] }}</span>
                    </span>
                    <span class="absolute bottom-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-accent outline outline-2 -outline-offset-2 outline-surface" aria-hidden="true">
                        <span class="h-2 w-2 rounded-full bg-ink"></span>
                    </span>
                </span>

                <span class="flex min-w-0 flex-1 flex-col gap-0.5 pl-4">
                    <span class="flex items-center">
                        <span class="truncate text-lg font-semibold leading-6 text-ink">{{ $driver['name'] }}</span>
                        <span class="h-[13px] w-[13.5px] shrink-0 bg-success-deep" aria-hidden="true"></span>
                    </span>

                    <span class="font-mono text-[11px] font-medium leading-[14px] tracking-[0.88px] text-ink-body">{{ $driver['unit_code'] }}</span>

                    <span class="flex flex-col gap-1 pt-1">
                        @foreach ($driver['tags'] as $tag)
                            <span class="h-[18px] w-fit rounded px-1.5 py-0.5 font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] {{ $tagTone[$tag['tone']] }}">
                                {{ $tag['label'] }}
                            </span>
                        @endforeach
                    </span>
                </span>
            </div>

            <div class="flex items-stretch gap-1 border-t border-surface-track pt-1">
                <div class="flex flex-1 flex-col gap-1 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board">
                    <p class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{{ $driver['specs']['label'] }}</p>

                    @foreach ($driver['specs']['lines'] as $line)
                        <p class="font-mono text-sm font-bold leading-5 tracking-[0.28px] text-ink">{{ $line }}</p>
                    @endforeach

                    <p class="text-xs font-normal leading-4 text-ink-body">{{ $driver['specs']['foot'] }}</p>
                </div>

                <div class="flex flex-1 flex-col gap-0.5 rounded-lg bg-surface-shell px-2 py-2 outline outline-1 -outline-offset-1 outline-line-board">
                    <p class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                        {{ $driver['plate_label'] }}<br>{{ $driver['plate_tail'] }}
                    </p>

                    <p class="font-mono text-sm font-bold leading-5 tracking-[0.28px] text-ink">{{ $driver['plate'] }}</p>
                    <p class="text-xs font-normal leading-4 text-ink-body">{{ $driver['plate_note'] }}</p>
                </div>
            </div>
        </section>

        {{-- Telemetri IoT --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-track pb-2">
                <p class="flex items-center">
                    <x-gpa.icon name="gauge" class="h-[13.5px] w-[7px] shrink-0 text-ink" />
                    <span class="pl-1.5 font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] text-ink-body">{{ $telemetry['section'] }}</span>
                </p>

                <span class="rounded bg-accent px-2 py-0.5 font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink outline outline-1 -outline-offset-1 outline-accent-deep">
                    {{ $telemetry['badge'] }}<br>{{ $telemetry['badge_tail'] }}
                </span>
            </header>

            <div class="flex flex-col gap-2 rounded-xl bg-success-soft p-4 outline outline-1 -outline-offset-1 outline-accent-deep">
                <div class="flex items-start justify-between gap-2">
                    <p class="flex flex-col gap-1">
                        <span class="font-mono text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-success">{{ $telemetry['chiller']['label'] }}</span>

                        <span class="flex items-center gap-2">
                            <span class="font-inter text-4xl font-bold leading-10 text-ink">{{ $telemetry['chiller']['value'] }}</span>
                            <span class="rounded bg-accent px-2 py-0.5 font-mono text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-brand outline outline-1 -outline-offset-1 outline-accent-deep">
                                {{ $telemetry['chiller']['badge'] }}
                            </span>
                        </span>
                    </p>

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-surface shadow-sub outline outline-1 -outline-offset-1 outline-line-board">
                        <x-gpa.icon :name="$telemetry['chiller']['icon']" class="h-4 w-4 shrink-0 text-ink" />
                    </span>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-accent-deep/30 pt-2">
                    <p class="flex flex-col font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                        {{ $telemetry['chiller']['range_label'] }}
                        <span class="font-bold">{{ $telemetry['chiller']['range_min'] }}</span>
                        {{ $telemetry['chiller']['range_max'] }}
                    </p>

                    <p class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                        {{ $telemetry['chiller']['commodity'] }}<br>{{ $telemetry['chiller']['commodity_tail'] }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 rounded-xl bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board">
                <div class="flex items-center justify-between gap-2">
                    <p class="flex items-center">
                        <x-gpa.icon name="package" class="h-[10.5px] w-[9.5px] shrink-0 text-ink" />
                        <span class="pl-1 font-mono text-[11px] font-medium leading-[14px] tracking-[0.88px] text-ink">{{ $telemetry['capacity']['label'] }}</span>
                    </p>

                    <p class="text-right font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px]">
                        <span class="block text-ink">{{ $telemetry['capacity']['value'] }}</span>
                        <span class="block text-success-deep">{{ $telemetry['capacity']['percent'] }}</span>
                    </p>
                </div>

                <span class="relative block h-3 overflow-hidden rounded-full bg-surface-disabled outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="absolute left-[3px] top-[3px] h-1.5 rounded-full bg-ink"
                        :style="'width: calc(' + {{ $telemetry['capacity']['percent_value'] }} + '% - 6px)'"></span>
                </span>

                <div class="flex items-start justify-between gap-2">
                    @foreach ($telemetry['capacity']['foot'] as $note)
                        <p class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                            {{ $note['label'] }}<br>{{ $note['tail'] }}
                        </p>
                    @endforeach
                </div>
            </div>

            <div class="flex items-stretch gap-2">
                @foreach ($telemetry['cards'] as $card)
                    <div class="flex flex-1 flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board">
                        <p class="flex items-center">
                            <x-gpa.icon :name="$card['icon']" class="h-3 w-3 shrink-0 text-ink-body" />
                            <span class="pl-1 font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">{{ $card['label'] }}</span>
                        </p>

                        <p class="pt-0.5 text-lg font-bold leading-6 text-ink">{{ $card['value'] }}</p>
                        <p class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                            {{ $card['note'] }}<br>{{ $card['note_tail'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Audit pre-trip --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-track pb-2">
                <p class="font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] text-ink-body">
                    {{ $trip['section'] }}<br>{{ $trip['title'] }}<br>
                    <span class="text-lg font-semibold normal-case tracking-normal text-ink">
                        {{ $trip['subtitle'] }}<br>{{ $trip['subtitle_tail'] }}
                    </span>
                </p>

                <span class="rounded bg-accent px-2 py-0.5 text-right font-mono text-[10px] font-bold leading-3 tracking-[1px] text-brand outline outline-1 -outline-offset-1 outline-accent-deep">
                    {{ $trip['badge'] }}<br>{{ $trip['badge_tail'] }}
                </span>
            </header>

            <div class="flex flex-col gap-2">
                @foreach ($trip['items'] as $item)
                    <div class="flex items-center justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board">
                        <p class="flex items-center">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-ink">
                                <x-gpa.icon name="check" class="h-[6px] w-2 shrink-0 text-accent" />
                            </span>

                            <span class="pl-2 text-xs font-normal leading-4 text-ink">
                                {{ $item['label'] }}{{ $item['tail'] ? ' '.$item['tail'] : '' }}
                            </span>
                        </p>

                        <p class="shrink-0 font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                            {{ $item['time'] }}<br>{{ $item['time_tail'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-track p-2.5 outline outline-1 -outline-offset-1 outline-line-board">
                <p class="flex items-center">
                    <x-gpa.icon name="badge-check" class="h-[13.5px] w-3 shrink-0 text-ink" />

                    <span class="flex flex-col gap-1.5 pl-2">
                        <span class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{{ $trip['mechanic_label'] }}</span>
                        <span class="font-mono text-[11px] font-medium leading-[14px] tracking-[0.88px] text-ink">
                            {{ $trip['mechanic'] }}
                        </span>
                    </span>
                </p>

                <span class="shrink-0 rounded bg-ink px-3 py-2 text-right font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-accent">
                    {{ $trip['sign_label'] }}<br>{{ $trip['sign_tail'] }}
                </span>
            </div>
        </section>

        {{-- Rekap kinerja --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-track pb-2">
                <p class="font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] text-ink-body">
                    {{ $performance['section'] }}<br>{{ $performance['title'] }}<br>
                    <span class="font-inter text-lg font-semibold normal-case leading-6 tracking-normal text-ink">
                        {!! $performance['subtitle'] !!}
                    </span>
                </p>

                <span class="rounded bg-surface-track px-2 py-0.5 text-right font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    {{ $performance['badge'] }}<br>{{ $performance['badge_tail'] }}
                </span>
            </header>

            <div class="flex flex-col gap-2">
                @foreach ($performance['cards'] as $card)
                    <div @class([
                        'flex flex-col gap-0.5 rounded-xl p-2.5',
                        $cardTone[$card['tone']],
                        'min-h-[110px] justify-start',
                    ])>
                        <p class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px]">
                            {{ $card['label'] }}{{ isset($card['label_tail']) ? '<br>'.$card['label_tail'] : '' }}
                        </p>

                        <p class="font-inter text-lg font-bold leading-6 text-ink">{{ $card['value'] }}</p>

                        <p class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px]">
                            {{ $card['note'][0] }}<br>{{ $card['note'][1] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Dukungan operasional --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-track pb-2">
                <p class="font-mono text-[10px] font-bold uppercase leading-3 tracking-[1px] text-ink-body">
                    {{ $support['section'] }}<br>{!! $support['section_tail'] !!}
                </p>

                <span class="rounded bg-danger-soft px-2 py-0.5 text-right font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-danger-ink">
                    {{ $support['badge'] }}<br>{{ $support['badge_tail'] }}
                </span>
            </header>

            <button type="button" @click="openReport()"
                class="flex items-center justify-center gap-2 rounded-xl px-3 py-2 outline outline-2 -outline-offset-2 outline-warning-caution">
                <x-gpa.icon name="alert-triangle" class="h-4 w-[18px] shrink-0 text-warning-caution" />
                <span class="text-left text-lg font-semibold leading-6 text-warning-caution">{{ $support['report_label'] }}</span>
            </button>

            <p class="text-center font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                {{ $support['report_note'] }}<br>{{ $support['report_note_tail'] }}
            </p>

            <button type="button" @click="callDispatch()"
                class="flex items-center justify-between gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                <span class="flex flex-col gap-1.5 text-left">
                    <span class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{{ $support['dispatch_label'] }}</span>
                    <span class="font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] text-ink">{{ $support['dispatch_value'] }}</span>
                </span>

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-ink">
                    <x-gpa.icon name="phone" class="h-[14px] w-[14px] shrink-0 text-accent" />
                </span>
            </button>

            <button type="button" @click="openSop()"
                class="flex items-center justify-between gap-2 rounded-xl bg-surface-track p-2.5 outline outline-1 -outline-offset-1 outline-line-board">
                <p class="flex items-center">
                    <x-gpa.icon name="file-text" class="h-[11px] w-[15px] shrink-0 text-ink" />
                    <span class="pl-2 text-left text-xs font-normal leading-4 text-ink">
                        {{ $support['sop_label'] }}<br>{{ $support['sop_tail'] }}
                    </span>
                </p>

                <span class="shrink-0 rounded bg-surface-disabled px-1.5 py-0.5 font-mono text-[10px] font-bold leading-3 tracking-[1px] text-ink-body">
                    {{ $support['sop_badge'] }}
                </span>
            </button>

            <div class="flex flex-col gap-2 border-t border-surface-track pt-2">
                <button type="button" @click="closeShift()"
                    :disabled="closed"
                    :class="closed
                        ? 'cursor-not-allowed bg-surface-pill text-ink-quiet outline outline-1 -outline-offset-1 outline-line-board'
                        : 'bg-accent-deep text-brand-deep shadow-pop'"
                    class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3">
                    <x-gpa.icon name="check-circle" class="h-[18px] w-[14px] shrink-0" />
                    <span class="text-center text-lg font-bold leading-6">{{ $closure['action'] }}<br>{{ $closure['action_tail'] }}</span>
                </button>

                <p class="text-center font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">
                    {{ $closure['note'] }}<br>{{ $closure['note_tail'] }}
                </p>
            </div>
        </section>

        <p class="pb-4 text-center font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body/80">
            {!! $closure['footer'] !!}<br>{!! $closure['footer_tail'] !!}
        </p>
    </div>
@endsection
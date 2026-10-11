@extends('layouts.armada')

@section('title', 'Surat Jalan Digital')

@section('content')
    @php
        $toneText = [
            'success' => 'text-success-deep',
            'muted' => 'text-ink-body',
            'quiet' => 'text-ink-quiet',
            'strong' => 'text-ink',
        ];

        $toneDot = [
            'success' => 'bg-success-deep',
            'quiet' => 'bg-ink-quiet',
        ];

        $toneName = [
            'strong' => 'font-semibold',
            'muted' => 'font-normal',
        ];

        $chainShell = [
            'muted' => 'bg-surface-soft',
            'accent' => 'bg-accent',
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-[576px] flex-col gap-5 px-4 pb-24 pt-2">

        {{-- Judul halaman --}}
        <section class="flex flex-col gap-0.5 rounded-lg bg-surface p-4 shadow-card">
            <h1 class="text-2xl font-bold leading-8 text-ink">{!! $intro['title'] !!}</h1>
        </section>

        {{-- Rute pengantaran --}}
        <section class="flex flex-col gap-2 rounded-lg bg-surface px-4 py-[18px] shadow-card">
            <header class="flex items-center justify-between gap-2">
                <p class="font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink-body">{{ $route['label'] }}</p>
                <p class="font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-success-deep">{{ $route['badge'] }}</p>
            </header>

            <div class="flex w-full items-stretch gap-1">
                @foreach ($route['stops'] as $stop)
                    <div @class([
                        'relative flex h-28 min-w-0 flex-1 flex-col items-start gap-0.5 overflow-hidden p-2',
                        'rounded-lg',
                        $stop['active']
                            ? 'bg-surface shadow-sub outline outline-2 -outline-offset-2 outline-ink'
                            : 'bg-surface-shell opacity-80 outline outline-1 -outline-offset-1 outline-line-board',
                    ])>
                        <p class="flex items-center gap-1">
                            <span @class(['h-1.5 w-1.5 shrink-0 rounded-full', $toneDot[$stop['dot']]])></span>
                        </p>

                        <p @class([
                            'pt-0.5 font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px]',
                            $toneText[$stop['number_tone']],
                        ])>{{ $stop['number'] }}</p>

                        <p @class([
                            'overflow-hidden pb-1.5 text-xs leading-4',
                            $toneName[$stop['client_tone']],
                            $toneText[$stop['client_tone']],
                        ])>{{ $stop['client'] }}</p>

                        <p @class([
                            'mt-auto flex w-full items-baseline justify-between gap-2 border-t pt-1',
                            $stop['active'] ? 'border-surface-soft' : 'border-line-board/60',
                        ])>
                            <span @class([
                                'font-mono text-[9px] font-semibold leading-3 tracking-[1.08px]',
                                $toneText[$stop['weight_tone']],
                            ])>{{ $stop['weight_label'] }}</span>

                            <span @class([
                                'font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px]',
                                $toneText[$stop['weight_tone']],
                            ])>{{ $stop['weight'] }}</span>
                        </p>

                        <span @class([
                            'absolute right-0 top-0 rounded-bl px-2 py-0.5 font-mono text-[9px] font-bold leading-3 tracking-[1.08px]',
                            $stop['badge_tone'] === 'ink' ? 'bg-ink text-accent' : 'bg-surface-disabled text-ink-body',
                        ])>{{ $stop['badge'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Surat jalan--}}
        <section class="flex flex-col gap-2 rounded-lg bg-surface px-4 py-[18px] shadow-card">
            <header class="flex items-start justify-between gap-3 border-b border-surface-soft pb-4">
                <div class="flex min-w-0 flex-col items-start">
                    <span class="rounded bg-accent px-1.5 py-0.5 font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink">
                        {{ $letter['validated'] }}
                    </span>

                    <span class="pt-1.5 font-inter text-lg font-semibold leading-6 text-ink">{{ $letter['number'] }}</span>

                    <span class="font-mono text-[11px] font-medium leading-[14px] tracking-[0.88px]">
                        <span class="text-ink-body">{{ $letter['po_label'] }}</span><span class="text-ink">{{ $letter['po_number'] }}</span>
                    </span>

                    <a href="{{ route('prints.surat-jalan') }}"
                        class="mt-1.5 inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-disabled">
                        <x-gpa.icon name="printer" class="h-2.5 w-2.5 shrink-0" />
                        CETAK SJ A4
                    </a>
                </div>

                <div class="flex shrink-0 flex-col items-end">
                    <span class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{{ $letter['dispatch_label'] }}</span>
                    <span class="font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink">{{ $letter['dispatch_date'] }}</span>
                    <span class="font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-success-deep">{{ $letter['dispatch_time'] }}</span>
                </div>
            </header>

            <div class="flex flex-col gap-2 pt-1">
                @foreach ($letter['chain'] as $node)
                    <div class="flex items-start gap-2">
                        <span class="flex h-[26px] w-6 shrink-0 flex-col items-start pt-0.5">
                            <span @class([
                                'flex h-6 w-6 items-center justify-center rounded',
                                $chainShell[$node['shell']],
                            ])>
                                <x-gpa.icon :name="$node['icon']" class="h-3 w-3 shrink-0 text-ink" />
                            </span>
                        </span>

                        <span class="flex min-w-0 flex-1 flex-col items-start">
                            <span @class([
                                'font-mono text-[9px] font-bold uppercase leading-3 tracking-[1.08px]',
                                $toneText[$node['role_tone']],
                            ])>{{ $node['role_label'] }}</span>

                            <span class="text-base font-bold leading-6 text-ink">{{ $node['name'] }}</span>
                            <span class="text-xs font-normal leading-4 text-ink-body">{!! $node['address'] !!}</span>
                        </span>
                    </div>

                    @if (! $loop->last)
                        <span class="flex h-4 w-full flex-col justify-center pl-3">
                            <span class="h-full flex-1 border-l-2 border-line-board"></span>
                        </span>
                    @endif
                @endforeach
            </div>

            <div class="flex flex-col gap-1 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board">
                <div class="flex items-center justify-between gap-2">
                    <p class="flex items-center gap-1">
                        <x-gpa.icon name="user" class="h-[11px] w-[11px] shrink-0 text-ink" />

                        <span class="flex flex-col gap-1 pb-0.5">
                            <span class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{{ $letter['pic']['label'] }}</span>
                            <span class="text-xs font-bold leading-4 text-ink">{{ $letter['pic']['name'] }}</span>
                        </span>
                    </p>

                    <p class="font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] text-success-deep">{{ $letter['pic']['phone'] }}</p>
                </div>

                <div class="flex items-stretch gap-1 pt-1">
                    <button type="button" @click="callPic()"
                        class="flex h-9 flex-1 items-center justify-center gap-1 rounded bg-surface outline outline-1 -outline-offset-1 outline-ink-quiet">
                        <x-gpa.icon name="phone" class="h-[11px] w-[11px] shrink-0 text-ink" />
                        <span class="font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] text-ink">Hubungi PIC</span>
                    </button>

                    <button type="button" @click="openMap()"
                        class="flex h-9 flex-1 items-center justify-center gap-2 rounded bg-ink">
                        <x-gpa.icon name="navigation" class="h-[11px] w-[11px] shrink-0 text-accent" />
                        <span class="text-center font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] text-white">Buka Navigasi<br>Peta</span>
                    </button>
                </div>
            </div>
        </section>

        {{-- Rincian muatan--}}
        <section class="flex flex-col gap-2 rounded-lg bg-surface px-4 py-[18px] shadow-card">

            <div class="flex flex-col gap-2">
                @foreach ($cargo['items'] as $item)
                    <div class="flex flex-col gap-1.5 rounded-lg bg-canvas p-2 outline outline-1 -outline-offset-1 outline-line-board">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-col items-start">
                                <span class="rounded bg-surface-disabled px-1.5 font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink">{{ $item['sku'] }}</span>
                                <span class="pt-1.5 text-xs font-bold leading-4 text-ink">{{ $item['name'] }}</span>
                                <span class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{!! $item['spec'] !!}</span>
                            </div>

                            <span class="flex shrink-0 items-center gap-0.5 rounded bg-accent py-0.5 pl-1.5 pr-2">
                                <span class="font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink">{!! $item['badge'] !!}</span>
                                <x-gpa.icon name="check" class="h-2.5 w-2.5 shrink-0 text-ink" />
                            </span>
                        </div>

                        <div class="flex items-start gap-1 rounded border-t border-line-board bg-surface px-1.5 pb-1.5 pt-3">
                            @foreach ($item['columns'] as $column)
                                <div @class([
                                    'flex-1 flex-col items-center px-1 text-center',
                                    $loop->first ? '' : ($loop->last ? '' : 'border-x border-line-board'),
                                ])>
                                    <span @class([
                                        'font-mono text-[9px] font-semibold leading-3 tracking-[1.08px]',
                                        $toneText[$column['label_tone']],
                                    ])>{{ $column['label'] }}</span>

                                    <span class="font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] @if ($column['value_tone'] === 'success') text-success-deep @else text-ink @endif">
                                        {{ $column['value'] }}<br>{{ $column['value_tail'] ?? '' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between gap-2 px-1">
                            <span class="font-mono text-xs font-normal leading-4 text-ink-body">{{ $item['containers'] }}</span>
                            <span class="font-mono text-xs font-normal leading-4 text-ink-body">{{ $item['tare'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-2 rounded-lg bg-brand p-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-accent">{{ $cargo['total']['label'] }}</span>
                    <x-gpa.icon name="calculator" class="h-[13px] w-[13px] shrink-0 text-accent" />
                </div>

                <div class="flex items-start gap-2 border-b border-line-hair pb-2">
                    @foreach ($cargo['total']['columns'] as $column)
                        <div class="flex flex-1 flex-col items-start gap-1">
                            <span @class([
                                'font-mono text-[9px] font-semibold leading-3 tracking-[1.08px]',
                                $column['label_tone'] === 'accent' ? 'text-accent' : 'text-surface-soft',
                            ])>{{ $column['label'] }}</span>

                            @foreach ($column['values'] as $value)
                                <span @class([
                                    'font-inter text-sm font-bold leading-5 tracking-[0.28px]',
                                    $column['value_tone'] === 'accent' ? 'text-accent' : 'text-white',
                                ])>{{ $value }}</span>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between gap-2 pt-0.5">
                    <span class="font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-surface-soft">{!! $cargo['total']['containers'] !!}</span>

                    <p class="flex items-center gap-1">
                        <x-gpa.icon name="thermometer" class="h-[9px] w-[9px] shrink-0 text-accent" />
                        <span class="font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-accent">{!! $cargo['total']['cold_chain'] !!}</span>
                    </p>
                </div>
            </div>
        </section>

        {{-- Gate pass dock--}}
        <section class="flex flex-col gap-2 rounded-lg bg-surface px-4 py-[18px] outline outline-2 -outline-offset-2 outline-ink">
            <header class="flex items-center justify-between gap-2 border-b border-surface-soft pb-2">
                <p class="flex items-center gap-1.5">
                    <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-ink" />
                    <span class="font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] text-ink">{{ $gatePass['title'] }}</span>
                </p>

                <span class="rounded bg-accent px-2 py-0.5 font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink">
                    {{ $gatePass['expiry_label'] }} <span x-text="expired() ? 'EXPIRED' : expiryLabel()">{{ $gatePass['expiry_minutes'] }}:00</span>
                </span>
            </header>

            <div class="flex flex-col items-center rounded-lg bg-canvas pb-3 pt-2 outline outline-1 -outline-offset-1 outline-brand/30">
                <div class="relative mx-auto mt-1 flex h-44 w-44 items-center justify-center bg-surface p-2 shadow-sub outline outline-2 -outline-offset-2 outline-ink">
                    <span class="flex w-full flex-1 flex-col gap-0.5 rounded bg-ink p-1.5">
                        @foreach (range(1, 11) as $row)
                            <span @class([
                                'block h-full w-full',
                                'bg-canvas',
                                in_array($row, [1, 3, 8], true) ? 'outline outline-2 -outline-offset-2 outline-ink' : '',
                            ])></span>
                        @endforeach
                    </span>

                    <span class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 items-center rounded bg-accent px-2 py-0.5 outline outline-1 -outline-offset-1 outline-ink">
                        <span class="font-mono text-[9px] font-bold leading-3 tracking-[1.08px] text-ink">AGRO-PASS</span>
                    </span>
                </div>

                <p class="pt-2 font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] text-ink">{{ $gatePass['token'] }}</p>

                <p class="pt-0.5 text-center font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{!! $gatePass['hint'] !!}</p>
            </div>

            <button type="button" @click="verifySeal()"
                class="flex w-full items-center justify-between gap-2 rounded bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board">
                <p class="flex items-center gap-2">
                    <x-gpa.icon name="lock" class="h-[18px] w-[13px] shrink-0 text-success-deep" />

                    <span class="flex flex-col items-start">
                        <span class="font-mono text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">{{ $gatePass['seal']['label'] }}</span>
                        <span class="text-xs font-bold leading-4 text-ink">{{ $gatePass['seal']['value'] }}</span>
                    </span>
                </p>

                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-deep"></span>
            </button>

            <div class="flex flex-col gap-1 pt-1">
                <button type="button" @click="startRoute()"
                    :disabled="started"
                    :class="started
                        ? 'cursor-not-allowed bg-surface-pill text-ink-quiet outline outline-1 -outline-offset-1 outline-line-board'
                        : 'bg-ink text-accent'"
                    class="flex h-12 w-full items-center justify-center gap-2 rounded-lg">
                    <span class="text-center text-lg font-bold leading-6">{{ $gatePass['action'] }}</span>
                    <x-gpa.icon name="arrow-right" class="h-[13px] w-[13px] shrink-0" />
                </button>

                <p class="text-center font-mono text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body">{!! $gatePass['action_note'] !!}</p>

                <button type="button" @click="reportIssue()"
                    class="flex h-10 w-full items-center justify-center gap-1 rounded-lg bg-surface outline outline-1 -outline-offset-1 outline-warning-border">
                    <x-gpa.icon name="alert-triangle" class="h-[13px] w-[15px] shrink-0 text-warning-deep" />
                    <span class="text-center font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px] text-warning-deep">{{ $gatePass['report'] }}</span>
                </button>
            </div>
        </section>
    </div>
@endsection

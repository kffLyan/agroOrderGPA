@extends('layouts.armada')

@section('title', 'Surat Jalan Digital & Rincian Muatan')

@section('content')
    @php
        $toneText = [
            'success' => 'text-success-deep',
            'muted'   => 'text-ink-body',
            'quiet'   => 'text-ink-quiet',
            'strong'  => 'text-ink',
        ];

        $toneDot = [
            'success' => 'bg-success-deep',
            'quiet'   => 'bg-ink-quiet',
        ];

        $toneName = [
            'strong' => 'font-semibold',
            'muted'  => 'font-normal',
        ];

        $chainShell = [
            'muted'  => 'bg-surface-disabled text-ink',
            'accent' => 'bg-accent text-ink',
        ];
    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-4 px-4 pb-24"
        x-data="armadaDispatch(@js($gatePass))">

        {{-- Judul halaman --}}
        <section class="flex flex-col gap-1 rounded-2xl bg-surface p-4 shadow-card">
            <h1 class="text-xl font-bold leading-7 text-ink">{!! $intro['title'] !!}</h1>
            <p class="text-xs text-ink-body">{{ $intro['subtitle'] }}</p>
        </section>

        {{-- Rute pengantaran --}}
        <section class="flex flex-col gap-2 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-shell pb-2">
                <p class="font-mono text-[10px] font-bold text-ink-body uppercase tracking-wider">{{ $route['label'] }}</p>
                <p class="font-mono text-[10px] font-bold text-success-deep uppercase tracking-wider">{{ $route['badge'] }}</p>
            </header>

            <div class="flex w-full items-stretch gap-2 overflow-x-auto pt-1">
                @foreach ($route['stops'] as $stop)
                    <div @class([
                        'relative flex min-w-[200px] flex-1 flex-col items-start gap-1 overflow-hidden p-3 rounded-xl transition-all',
                        $stop['active']
                            ? 'bg-surface shadow-sub outline outline-2 -outline-offset-2 outline-accent'
                            : 'bg-surface-shell opacity-80 outline outline-1 -outline-offset-1 outline-line-board',
                    ])>
                        <div class="flex items-center gap-1.5">
                            <span @class(['h-2 w-2 shrink-0 rounded-full', $toneDot[$stop['dot']]])></span>
                            <span class="font-mono text-[10px] font-bold uppercase text-ink-body">{{ $stop['status'] }}</span>
                        </div>

                        <p class="pt-0.5 font-mono text-xs font-bold text-ink">{{ $stop['number'] }}</p>
                        <p class="text-xs font-medium text-ink truncate w-full">{{ $stop['client'] }}</p>

                        <div class="mt-auto flex w-full items-baseline justify-between gap-2 border-t border-line-hair pt-1.5">
                            <span class="font-mono text-[9px] text-ink-quiet">{{ $stop['weight_label'] }}</span>
                            <span class="font-mono text-xs font-bold text-ink">{{ $stop['weight'] }}</span>
                        </div>

                        <span @class([
                            'absolute right-0 top-0 rounded-bl-lg px-2 py-0.5 font-mono text-[9px] font-bold',
                            $stop['badge_tone'] === 'ink' ? 'bg-ink text-accent' : 'bg-surface-disabled text-ink-body',
                        ])>{{ $stop['badge'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Surat jalan resmi --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-start justify-between gap-3 border-b border-surface-shell pb-3">
                <div class="flex min-w-0 flex-col items-start gap-0.5">
                    <span class="rounded bg-accent px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-ink">
                        {{ $letter['validated'] }}
                    </span>

                    <span class="pt-1 font-mono text-base font-bold text-ink">{{ $letter['number'] }}</span>

                    <span class="font-mono text-xs text-ink-body">
                        <span>{{ $letter['po_label'] }}</span><span class="font-bold text-ink">{{ $letter['po_number'] }}</span>
                    </span>

                    <button type="button" onclick="window.print()"
                        class="mt-1.5 inline-flex items-center gap-1.5 rounded-lg bg-surface-shell px-2.5 py-1 font-mono text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-disabled">
                        <x-gpa.icon name="printer" class="h-3 w-3 shrink-0" />
                        CETAK SURAT JALAN
                    </button>
                </div>

                <div class="flex shrink-0 flex-col items-end text-right">
                    <span class="font-mono text-[9px] font-semibold text-ink-quiet uppercase">{{ $letter['dispatch_label'] }}</span>
                    <span class="font-mono text-xs font-bold text-ink">{{ $letter['dispatch_date'] }}</span>
                    <span class="font-mono text-xs font-bold text-success-deep">{{ $letter['dispatch_time'] }}</span>
                </div>
            </header>

            {{-- Alur pengirim & penerima --}}
            <div class="flex flex-col gap-2 pt-1">
                @foreach ($letter['chain'] as $node)
                    <div class="flex items-start gap-3">
                        <span @class([
                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm',
                            $chainShell[$node['shell']],
                        ])>
                            <x-gpa.icon :name="$node['icon']" class="h-3.5 w-3.5 shrink-0" />
                        </span>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <span class="font-mono text-[9px] font-bold uppercase text-ink-quiet">{{ $node['role_label'] }}</span>
                            <span class="text-sm font-bold text-ink">{{ $node['name'] }}</span>
                            <span class="text-xs text-ink-body">{!! $node['address'] !!}</span>
                        </div>
                    </div>

                    @if (! $loop->last)
                        <div class="ml-3.5 h-3 border-l-2 border-line-hair"></div>
                    @endif
                @endforeach
            </div>

            {{-- Kontak PIC penerima --}}
            <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="user" class="h-4 w-4 shrink-0 text-ink-body" />
                        <div class="flex flex-col">
                            <span class="font-mono text-[9px] text-ink-quiet uppercase">{{ $letter['pic']['label'] }}</span>
                            <span class="text-xs font-bold text-ink">{{ $letter['pic']['name'] }}</span>
                        </div>
                    </div>

                    <span class="font-mono text-xs font-bold text-success-deep">{{ $letter['pic']['phone'] }}</span>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="button" @click="callPic('{{ $letter['pic']['phone'] }}')"
                        class="flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg bg-surface font-mono text-xs font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board shadow-sm hover:bg-surface-disabled">
                        <x-gpa.icon name="phone" class="h-3 w-3 shrink-0" />
                        Hubungi PIC
                    </button>

                    <button type="button" @click="openMap('{{ $letter['chain'][1]['address'] }}')"
                        class="flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg bg-ink font-mono text-xs font-bold text-accent shadow-sm hover:opacity-90">
                        <x-gpa.icon name="navigation" class="h-3 w-3 shrink-0" />
                        Navigasi Peta
                    </button>
                </div>
            </div>
        </section>

        {{-- Rincian muatan sayur --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold text-ink-body uppercase">{{ $cargo['rule'] }}</span>
                <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[9px] font-bold text-ink">SAH TERA</span>
            </header>

            <div class="flex flex-col gap-2.5">
                @foreach ($cargo['items'] as $item)
                    <div class="flex flex-col gap-2 rounded-xl bg-canvas p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-col">
                                <span class="w-fit rounded bg-surface px-1.5 py-0.5 font-mono text-[9px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                    {{ $item['sku'] }}
                                </span>
                                <span class="pt-1 text-sm font-bold text-ink">{{ $item['name'] }}</span>
                                <span class="text-[10px] text-ink-body">{!! $item['spec'] !!}</span>
                            </div>

                            <span class="flex items-center gap-1 rounded bg-accent px-2 py-0.5 font-mono text-[9px] font-bold text-ink">
                                <span>{{ $item['badge'] }}</span>
                                <x-gpa.icon name="check" class="h-2.5 w-2.5" />
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 rounded-lg bg-surface p-2 text-center outline outline-1 -outline-offset-1 outline-line-hair">
                            @foreach ($item['columns'] as $column)
                                <div class="flex flex-col">
                                    <span class="font-mono text-[9px] text-ink-quiet">{{ $column['label'] }}</span>
                                    <span @class([
                                        'font-mono text-xs font-bold',
                                        $column['value_tone'] === 'success' ? 'text-success-deep' : 'text-ink',
                                    ])>
                                        {{ $column['value'] }}
                                        @if(!empty($column['value_tail']))
                                            <span class="text-[9px] font-normal block">{{ $column['value_tail'] }}</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between text-[11px] font-mono text-ink-body px-1">
                            <span>{{ $item['containers'] }}</span>
                            <span>{{ $item['tare'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Total akumulasi netto --}}
            <div class="flex flex-col gap-2.5 rounded-xl bg-brand p-3.5 text-white">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-accent">{{ $cargo['total']['label'] }}</span>
                    <x-gpa.icon name="calculator" class="h-4 w-4 text-accent" />
                </div>

                <div class="grid grid-cols-3 gap-2 border-b border-white/20 pb-2.5">
                    @foreach ($cargo['total']['columns'] as $col)
                        <div class="flex flex-col">
                            <span class="font-mono text-[9px] text-white/70">{{ $col['label'] }}</span>
                            @foreach ($col['values'] as $val)
                                <span @class([
                                    'font-mono text-sm font-bold',
                                    $col['value_tone'] === 'accent' ? 'text-accent' : 'text-white',
                                ])>{{ $val }}</span>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between text-xs font-mono text-white/90">
                    <span>{!! $cargo['total']['containers'] !!}</span>
                    <span class="flex items-center gap-1 text-accent">
                        <x-gpa.icon name="thermometer" class="h-3.5 w-3.5" />
                        <span>{!! $cargo['total']['cold_chain'] !!}</span>
                    </span>
                </div>
            </div>
        </section>

        {{-- Gate pass keluar dock & Mulai Pengiriman --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card outline outline-2 -outline-offset-2 outline-ink">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2.5">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="shield" class="h-4 w-4 text-ink" />
                    <span class="font-mono text-xs font-bold text-ink">{{ $gatePass['title'] }}</span>
                </div>

                <span class="rounded bg-accent px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                    {{ $gatePass['expiry_label'] }} <span x-text="expired() ? 'EXPIRED' : expiryLabel()">{{ $gatePass['expiry_minutes'] }}:00</span>
                </span>
            </header>

            <div class="flex flex-col items-center gap-1 rounded-xl bg-canvas p-4 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-ink text-accent">
                    <x-gpa.icon name="qrcode" class="h-12 w-12" />
                </div>
                <p class="pt-2 font-mono text-xs font-bold text-ink">{{ $gatePass['token'] }}</p>
                <p class="text-[10px] text-ink-body">{!! $gatePass['hint'] !!}</p>
            </div>

            <button type="button" @click="verifySeal()"
                class="flex items-center justify-between rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                <div class="flex items-center gap-2.5">
                    <x-gpa.icon name="lock" class="h-4 w-4 text-success-deep" />
                    <div class="flex flex-col text-left">
                        <span class="font-mono text-[9px] text-ink-quiet uppercase">{{ $gatePass['seal']['label'] }}</span>
                        <span class="text-xs font-bold text-ink">{{ $gatePass['seal']['value'] }}</span>
                    </div>
                </div>
                <span class="h-2.5 w-2.5 rounded-full bg-success-deep"></span>
            </button>

            @if (!empty($gatePass['order_id']))
                <form id="start-route-form" method="POST" action="{{ route('armada.dispatch.start', $gatePass['order_id']) }}" class="hidden">
                    @csrf
                </form>
            @endif

            <div class="flex flex-col gap-2 pt-1">
                @if ($gatePass['is_started'])
                    <div class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-accent font-mono text-sm font-bold text-ink shadow-sub">
                        <x-gpa.icon name="truck" class="h-4 w-4 text-ink" />
                        PENGIRIMAN SEDANG BERJALAN
                    </div>
                    <a href="{{ route('armada.pod', ['order_id' => $gatePass['order_id']]) }}"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-ink font-mono text-xs font-bold text-accent shadow-sub hover:opacity-90">
                        <x-gpa.icon name="qrcode" class="h-4 w-4" />
                        MENUJU PENYERAHAN & SCAN POD
                    </a>
                @else
                    <button type="button" @click="startRoute()"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-ink font-mono text-sm font-bold text-accent shadow-pop transition-transform active:scale-[0.98]">
                        <span>{{ $gatePass['action'] }}</span>
                        <x-gpa.icon name="arrow-right" class="h-4 w-4" />
                    </button>
                @endif

                <p class="text-center font-mono text-[9px] text-ink-body">{!! $gatePass['action_note'] !!}</p>

                <button type="button" @click="reportIssue()"
                    class="flex h-9 w-full items-center justify-center gap-1.5 rounded-xl bg-surface font-mono text-xs font-bold text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border hover:bg-warning-cream/30">
                    <x-gpa.icon name="alert-triangle" class="h-3.5 w-3.5" />
                    {{ $gatePass['report'] }}
                </button>
            </div>
        </section>
    </div>
@endsection

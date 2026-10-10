@extends('layouts.coordinator')

@section('title', 'Manajemen Stok Gudang')

@section('content')
    <div class="space-y-6" x-data="coordinatorStock(@js($stockData['commodities']), @js($stockData['filters']['options']), @js($stockData['intake']['fields']), @js($stockData['logs']['items']), @js($stockData['logs']['total']))">
        @php
            $header = $stockData['header'];
            $protocol = $stockData['protocol'];
            $kpis = $stockData['kpis'];
            $filters = $stockData['filters'];
            $commodities = $stockData['commodities'];
            $intake = $stockData['intake'];
            $logs = $stockData['logs'];
        @endphp

        {{-- Page header --}}
        <section class="gpa-panel flex flex-wrap items-center justify-between gap-4 p-6">
            <div class="min-w-0">

                <h1 class="mt-2 font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                    {{ $header['title_before'] }}<br class="hidden sm:block">
                    {{ $header['title_after'] }}
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @foreach ($header['actions'] as $action)
                    <button type="button" @click="act(@js($action['key']))"
                        @class([
                            'inline-flex h-10 items-center gap-1.5 rounded-lg px-3 transition-colors',
                            'bg-surface-shell text-ink outline outline-1 outline-line-board hover:bg-surface-muted' => $action['variant'] === 'ghost',
                            'bg-ink text-accent shadow-sub hover:bg-ink-muted' => $action['variant'] === 'ink',
                        ])>
                        <x-gpa.icon :name="$action['icon']"
                            class="h-3.5 w-3.5 shrink-0 {{ $action['variant'] === 'ghost' ? 'text-ink' : 'text-accent' }}" />
                        <span class="gpa-meta-lg font-bold">{{ $action['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                @php
                    $segmentBase = ($kpi['segment_mode'] ?? null) === 'equal'
                        ? count($kpi['segments'])
                        : ($kpi['bar_total'] ?? array_sum(array_column($kpi['segments'], 'value')));
                @endphp
                <article class="gpa-panel flex flex-col gap-2 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">{{ $kpi['label'] }}</h2>
                        <span @class([
                            'shrink-0 rounded p-1.5',
                            'bg-surface-shell' => ($kpi['icon_tone'] ?? 'shell') === 'shell',
                            'bg-accent' => ($kpi['icon_tone'] ?? 'shell') === 'accent',
                        ])>
                            <x-gpa.icon :name="$kpi['icon']"
                                @class([
                                    'block h-4 w-4',
                                    'text-success-deep' => ($kpi['icon_tone'] ?? 'shell') === 'shell',
                                    'text-ink' => ($kpi['icon_tone'] ?? 'shell') === 'accent',
                                ]) />
                        </span>
                    </div>

                    <p class="flex items-baseline gap-1.5">
                        @if ($kpi['key'] === 'available')
                            <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="kg(totalStock())">14.850</span>
                        @elseif ($kpi['key'] === 'reserved')
                            <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="kg(reserved())">8.900</span>
                        @elseif ($kpi['key'] === 'free')
                            <span class="font-mono text-3xl font-bold leading-10 text-success-deep" x-text="`+${kg(freeStock())}`">+5.950</span>
                        @else
                            <span class="font-mono text-3xl font-bold leading-10 text-ink">{{ $kpi['value'] }}</span>
                        @endif
                        <span class="gpa-meta-lg font-bold {{ $kpi['tone'] === 'success' ? 'text-success-deep' : 'text-ink-body' }}">{{ $kpi['unit'] }}</span>
                    </p>

                    <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        @if ($kpi['key'] === 'available')
                            <p class="min-w-0">
                                <span class="block gpa-note text-success-deep"
                                    x-text="`Binaan: ${kg(builtianStock())} KG (${percent(builtianPercent(), 1)})`">Binaan: 10.200 KG (68.7%)</span>
                                <span class="mt-0.5 block gpa-note text-ink-body"
                                    x-text="`Buffer: ${kg(bufferStock())} KG`">Buffer: 4.650 KG</span>
                            </p>
                        @elseif ($kpi['key'] === 'reserved')
                            <p class="min-w-0">
                                <span class="block gpa-note text-ink">{{ $kpi['footnote_left'] }}</span>
                                <span class="mt-0.5 block gpa-note text-ink-quiet">B2B & Retail Commitment</span>
                            </p>
                            <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink">100% TERIKAT</span>
                        @elseif ($kpi['key'] === 'free')
                            <p class="min-w-0">
                                <span class="block gpa-note text-ink">{{ $kpi['footnote_left'] }}</span>
                                <span class="mt-0.5 block gpa-note text-success-deep">{{ $kpi['footnote_right'] }}</span>
                            </p>
                        @else
                            <p class="min-w-0">
                                <span class="block gpa-note text-ink">{{ $kpi['footnote_left'] }}</span>
                                <span class="mt-0.5 block gpa-note text-success-deep">{{ $kpi['footnote_right'] }}</span>
                            </p>
                        @endif
                    </div>

                    <div class="flex h-1.5 w-full gap-0.5 overflow-hidden rounded-full bg-surface-pill">
                        @foreach ($kpi['segments'] as $segment)
                            <span class="block h-full rounded-full {{ $segment['class'] }}"
                                style="width: {{ round($segment['value'] / $segmentBase * 100, 1) }}%"></span>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Commodity filter --}}
        <section class="gpa-panel flex flex-wrap items-center justify-between gap-3 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="gpa-micro-bold text-ink-body">{{ $filters['label'] }}</span>
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach ($filters['options'] as $option)
                        <button type="button" @click="filter = @js($option['key'])"
                            :aria-pressed="filter === @js($option['key']) ? 'true' : 'false'"
                            :class="filter === @js($option['key'])
                                ? 'bg-ink text-accent'
                                : 'bg-surface-shell text-ink-body hover:bg-surface-muted'"
                            class="rounded px-2.5 py-1 transition-colors gpa-meta-lg font-medium">
                            {{ $option['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                @foreach ($filters['legend'] as $legend)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full {{ $legend['class'] }}"></span>
                        <span class="gpa-note text-ink">{{ $legend['label'] }}</span>
                    </span>
                @endforeach
            </div>
        </section>

        {{-- Commodity cards --}}
        <section class="grid gap-4 2xl:grid-cols-2">
            @foreach ($commodities as $commodity)
                <article class="gpa-panel p-4" x-show="visible.includes(@js($commodity['key']))"
                    x-transition.opacity.duration.150ms>
                    <header class="flex items-start justify-between gap-4 border-b border-line-soft/60 pb-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span @class([
                                'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-surface-shell outline outline-1 outline-line-soft',
                                'text-success-deep' => $commodity['icon_tone'] === 'success',
                                'text-danger' => $commodity['icon_tone'] === 'danger',
                            ])>
                                <x-gpa.icon :name="$commodity['icon']" class="h-6 w-6" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-sans text-lg font-bold leading-6 tracking-[-0.01em] text-ink">{{ $commodity['name'] }}</h3>
                                    <span @class([
                                        'rounded px-1.5 py-0.5 gpa-micro-bold',
                                        'bg-accent text-success-deep' => $commodity['grade_tone'] === 'accent',
                                        'bg-surface-pill text-ink-body' => $commodity['grade_tone'] === 'pill',
                                    ])>{{ $commodity['grade'] }}</span>
                                </div>
                                <p class="mt-1 gpa-mono-xs text-ink-body">KODE: {{ $commodity['code'] }} | SENTRA: {{ $commodity['hub'] }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="gpa-meta-lg font-bold text-ink">
                                <span x-text="kg(c.total)">{{ number_format($commodity['total'], 0, ',', '.') }}</span> KG TOTAL
                            </p>
                            <p @class([
                                'mt-1 gpa-note',
                                'text-success-deep' => $commodity['rtp_tone'] === 'success',
                                'text-danger' => $commodity['rtp_tone'] === 'danger',
                            ])>
                                RTP (BEBAS):
                                <span x-text="signed(rtp(c))">+{{ number_format($commodity['rtp'], 0, ',', '.') }}</span> KG
                                @if (! empty($commodity['rtp_note']))
                                    <span class="font-bold">{{ $commodity['rtp_note'] }}</span>
                                @endif
                            </p>
                        </div>
                    </header>

                    <div class="grid gap-3 border-b border-line-soft/40 py-3 sm:grid-cols-2 2xl:grid-cols-4">
                        @foreach ($commodity['tiles'] as $tile)
                            @php $tileIndex = $loop->index; @endphp
                            <div class="rounded-lg bg-surface-shell/60 p-2.5">
                                <p class="gpa-micro-bold text-ink-quiet">{{ $tile['label'] }}</p>
                                @if ($tileIndex === 0)
                                    <p class="mt-1 font-mono text-sm font-bold leading-4 text-ink" x-text="kg(c.builtian)">
                                        {{ number_format($tile['value'], 0, ',', '.') }} KG
                                    </p>
                                @elseif ($tileIndex === 1)
                                    <p class="mt-1 font-mono text-sm font-bold leading-4 text-ink" x-text="kg(c.buffer)">
                                        {{ number_format($tile['value'], 0, ',', '.') }} KG
                                    </p>
                                @elseif ($tileIndex === 2)
                                    <p class="mt-1 font-mono text-sm font-bold leading-4 text-danger" x-text="kg(c.po)">
                                        {{ number_format($tile['value'], 0, ',', '.') }} KG
                                    </p>
                                @else
                                    <p @class([
                                        'mt-1 font-mono text-sm font-bold leading-4',
                                        'text-success-deep' => $tile['tone'] === 'success',
                                        'text-ink' => $tile['tone'] !== 'success',
                                    ])>{{ $tile['value'] }}</p>
                                @endif
                                <p @class([
                                    'mt-1 gpa-note',
                                    'text-success-deep' => $tile['note_tone'] === 'success',
                                    'text-ink' => $tile['note_tone'] === 'ink',
                                    'text-ink-quiet' => $tile['note_tone'] === 'muted',
                                ])>{{ $tile['note'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <footer class="flex flex-wrap items-end justify-between gap-3 pt-3">
                        <div class="min-w-[12rem] flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <p class="gpa-note text-ink-body">
                                    Utilisasi Alokasi:
                                    <span class="font-bold text-ink" x-text="`${percent(utilization(c), 1)} Terjadwal`">{{ $commodity['utilization_note'] }}</span>
                                </p>
                                <p @class([
                                    'gpa-note',
                                    'text-ink' => $commodity['utilization_status_tone'] === 'ink',
                                    'text-warning' => $commodity['utilization_status_tone'] === 'warning',
                                ])>
                                    <span x-text="c.buffer_need > 0 && !c.bufferRequested ? c.utilization_status : (c.locked ? 'Terkunci' : c.utilization_status)">
                                        {{ $commodity['utilization_status'] }}
                                    </span>
                                </p>
                            </div>
                            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-surface-pill">
                                <span @class([
                                    'block h-full rounded-full transition-[width] duration-300',
                                    'bg-success-deep' => $commodity['utilization_tone'] === 'success',
                                    'bg-brand' => $commodity['utilization_tone'] === 'brand',
                                ]) :style="`width: ${Math.min(utilization(c), 100)}%`"
                                    style="width: {{ $commodity['utilization'] }}%"></span>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            @if ($commodity['injection'])
                                <button type="button" @click="requestBuffer(c)"
                                    class="inline-flex h-8 items-center gap-1 rounded-lg bg-accent px-2.5 text-success-ink outline outline-1 outline-success-deep transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="lockedAll || c.locked || c.bufferRequested">
                                    <x-gpa.icon name="plus" class="h-3 w-3" />
                                    <span class="gpa-meta-lg font-bold" x-text="c.bufferRequested ? 'Injeksi Diproses' : '+ Injeksi Buffer Segera'">
                                        + Injeksi Buffer Segera
                                    </span>
                                </button>
                            @else
                                <button type="button" @click="requestBuffer(c)"
                                    class="inline-flex h-8 items-center gap-1 rounded-lg bg-surface-shell px-2.5 text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-muted disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="lockedAll || c.locked">
                                    <x-gpa.icon name="plus" class="h-3 w-3" />
                                    <span class="gpa-meta-lg font-semibold">+ Minta Buffer</span>
                                </button>
                            @endif

                            <button type="button" @click="lockStock(c)"
                                class="inline-flex h-8 items-center gap-1 rounded-lg bg-ink px-2.5 text-accent shadow-sub transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="lockedAll || c.locked">
                                <x-gpa.icon name="lock" class="h-3 w-3" />
                                <span class="gpa-meta-lg font-bold" x-text="c.locked ? 'Terkunci' : 'Kunci Stok'">Kunci Stok</span>
                            </button>
                        </div>
                    </footer>
                </article>
            @endforeach
        </section>

        {{-- Quick intake ledger --}}
        <section class="gpa-panel p-6">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/40 pb-3">
                <div class="flex items-center gap-2">
                    <div>
                        <h2 class="font-sans text-lg font-bold leading-6 tracking-[-0.01em] text-ink">{{ $intake['title'] }}</h2>
                    </div>
                </div>
                <span class="rounded bg-ink px-1.5 py-0.5 gpa-micro-bold text-accent">{{ $intake['badge'] }}</span>
            </header>

            <form class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3" @submit.prevent="submitIntake()">
                @foreach ($intake['fields'] as $field)
                    <div @class(['min-w-0', 'md:col-span-2 xl:col-span-3' => $field['type'] === 'text'])>
                        <label class="gpa-label" for="intake-{{ $field['key'] }}">{{ $field['label'] }}</label>

                        @if ($field['type'] === 'select')
                            <select id="intake-{{ $field['key'] }}" class="gpa-control mt-1" x-model="form.{{ $field['key'] }}">
                                @foreach ($field['options'] as $option)
                                    <option value="{{ $option['key'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        @elseif ($field['type'] === 'number')
                            <div class="mt-1 flex items-center gap-2">
                                <input id="intake-{{ $field['key'] }}" type="number" min="1" step="1"
                                    value="{{ $field['value'] }}" class="gpa-control" x-model.number="form.{{ $field['key'] }}" />
                                <span class="gpa-meta-lg font-bold text-ink-body">{{ $field['unit'] }}</span>
                            </div>
                        @elseif ($field['type'] === 'date')
                            <input id="intake-{{ $field['key'] }}" type="date" value="{{ $field['value'] }}"
                                class="gpa-control mt-1" x-model="form.{{ $field['key'] }}" />
                        @else
                            <input id="intake-{{ $field['key'] }}" type="text" value="{{ $field['value'] }}"
                                class="gpa-control mt-1" x-model="form.{{ $field['key'] }}" />
                        @endif

                        @if (! empty($field['hint']))
                            <span class="gpa-hint mt-1">{{ $field['hint'] }}</span>
                        @endif
                    </div>
                @endforeach

                <div class="flex flex-wrap items-center justify-between gap-3 md:col-span-2 xl:col-span-3">
                    <p class="inline-flex items-center gap-2 rounded-lg bg-surface-shell px-2.5 py-2 outline outline-1 outline-line-soft">
                        <x-gpa.icon name="scale" class="h-3.5 w-3.5 text-ink-body" />
                        <span class="gpa-meta-lg text-ink">{{ $intake['port_label'] }}</span>
                        <span class="gpa-micro-bold text-success-deep">{{ $intake['port_status'] }}</span>
                    </p>

                    <button type="submit"
                        class="inline-flex h-11 items-center gap-2 rounded-lg bg-accent px-4 text-success-ink outline outline-1 outline-success-deep transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="lockedAll">
                        <x-gpa.icon name="save" class="h-4 w-4" />
                        <span class="gpa-meta-lg font-bold">{{ $intake['submit'] }}</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- Inbound log --}}
        <section class="gpa-panel p-4">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/40 pb-2">
                <div class="flex items-center gap-2">
                    <h2 class="font-sans text-lg font-bold leading-6 tracking-[-0.01em] text-ink">{{ $logs['title'] }}</h2>
                </div>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 animate-pulse-ring rounded-full bg-success-deep"></span>
                    <span class="gpa-micro-bold text-success-deep">{{ $logs['badge'] }}</span>
                </span>
            </header>

            <div class="mt-3 space-y-2">
                <template x-for="entry in pendingLogs" :key="entry.lot">
                    <div class="rounded-lg border-l-2 border-success-deep bg-surface-shell/80 p-2.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="gpa-mono-xs font-bold text-ink" x-text="entry.lot"></span>
                            <span class="gpa-note text-ink-quiet" x-text="entry.time"></span>
                        </div>
                        <p class="mt-1 text-xs font-medium leading-4 text-ink" x-text="entry.entry"></p>
                        <p class="mt-1 flex flex-wrap items-center gap-2 gpa-note">
                            <span class="text-ink-quiet" x-text="entry.note"></span>
                            <span class="font-bold text-success-deep" x-text="entry.audit"></span>
                        </p>
                    </div>
                </template>

                @foreach ($logs['items'] as $log)
                    <div @class([
                        'rounded-lg border-l-2 bg-surface-shell/80 p-2.5',
                        'border-success-deep' => $log['tone'] === 'success',
                        'border-brand' => $log['tone'] === 'brand',
                    ])>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="gpa-mono-xs font-bold text-ink">{{ $log['lot'] }}</span>
                            <span class="gpa-note text-ink-quiet">{{ $log['time'] }}</span>
                        </div>
                        <p class="mt-1 text-xs font-medium leading-4 text-ink">{{ $log['entry'] }}</p>
                        <p class="mt-1 flex flex-wrap items-center gap-2 gpa-note">
                            <span class="text-ink-quiet">{{ $log['note'] }}</span>
                            <span class="font-bold text-success-deep">{{ $log['audit'] }}</span>
                        </p>
                    </div>
                @endforeach
            </div>

            <footer class="mt-3 rounded-lg py-2 text-center outline outline-1 outline-line-soft">
                <button type="button" @click="showAllLogs()"
                    class="gpa-note text-success-deep transition-opacity hover:opacity-80">
                    <span x-show="!allLogs">Lihat Seluruh <span x-text="logTotal">{{ $logs['total'] }}</span> Log Hari Ini →</span>
                    <span x-show="allLogs" x-cloak>Menampilkan <span x-text="pendingLogs.length + {{ count($logs['items']) }}"></span> log terbaru →</span>
                </button>
            </footer>
        </section>
    </div>
@endsection

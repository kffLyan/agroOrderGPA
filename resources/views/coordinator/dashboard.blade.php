@extends('layouts.coordinator')

@section('title', 'Dashboard Koordinator Lapangan')

@section('content')
    <div class="space-y-6" x-data="coordinatorConsole(@js($supply['rows']), @js($packing['orders']), @js($gate['logs']))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-end justify-between gap-4 border-b border-line-soft pb-6">
            <div class="min-w-0">
                <p class="gpa-eyebrow">{{ $heading['eyebrow'] }}</p>
                <h1 class="mt-1.5 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $heading['title_before'] }}<br class="hidden sm:block">
                    {{ $heading['title_after'] }}
                </h1>
                <p class="mt-2 max-w-2xl text-xs leading-4 text-ink-body">{{ $heading['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                @foreach ($heading['actions'] as $action)
                    <button type="button" @click="act(@js($action['key']))"
                        @class([
                            'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 transition-colors',
                            'bg-surface text-ink shadow-sub outline outline-1 outline-line-board hover:bg-surface-muted' => $action['variant'] === 'ghost',
                            'bg-ink text-accent hover:bg-ink-muted' => $action['variant'] === 'ink',
                        ])>
                        <x-gpa.icon :name="$action['icon']"
                            class="h-3 w-3 shrink-0 {{ $action['variant'] === 'ghost' ? 'text-ink' : 'text-accent' }}" />
                        <span class="gpa-meta-lg font-medium">{{ $action['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="gpa-panel flex flex-col gap-1 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="max-w-[10rem] gpa-micro-bold leading-3 text-ink-body">{{ $metric['label'] }}</h2>
                        <x-gpa.icon :name="$metric['icon']" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>

                    <p class="flex items-baseline gap-1 pb-1">
                        <span class="font-mono text-3xl font-bold leading-10 text-ink">{{ $metric['value'] }}</span>
                        <span class="gpa-meta-lg font-bold {{ $metric['unit_tone'] }}">{{ $metric['unit'] }}</span>
                    </p>

                    <div
                        class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        <p class="min-w-0">
                            <span class="block text-xs leading-4 text-ink-body">{{ $metric['foot_label'] }}</span>
                            <span class="block text-xs font-semibold leading-4 text-ink">{{ $metric['foot_value'] }}</span>
                        </p>

                        @if (! empty($metric['chip']))
                            <span class="shrink-0 rounded px-1.5 py-0.5 gpa-micro-bold {{ $metric['chip']['class'] }}">
                                {{ $metric['chip']['label'] }}
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Manajemen Stok Panen & Buffer Stock --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line-soft bg-surface-shell px-4 py-4">
                <div class="flex min-w-0 items-center gap-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-ink px-2">
                        <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-accent" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">
                            {{ $supply['title'] }}<br class="hidden md:block">
                            <span class="text-ink-body">{{ $supply['subtitle'] }}</span>
                        </h2>
                        <p class="mt-1 max-w-2xl text-xs leading-4 text-ink-body">{{ $supply['description'] }}</p>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center rounded-lg bg-surface px-2 py-1.5 shadow-sub">
                    @foreach ($supply['totals'] as $index => $total)
                        @if ($index > 0)
                            <span class="mx-2 h-6 w-px bg-line-soft" aria-hidden="true"></span>
                        @endif
                        <span class="px-1">
                            <span class="block gpa-micro text-ink-body">{{ $total['label'] }}</span>
                            <span class="block gpa-meta-lg font-bold {{ $total['tone'] }}">{{ $total['value'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[68rem] border-collapse text-left">
                    <caption class="sr-only">Kontrol pasokan panen, order aktif, dan buffer stok per komoditas inti.</caption>
                    <thead>
                        <tr class="bg-surface-pill/60">
                            @foreach ($supply['columns'] as $index => $column)
                                <th scope="col"
                                    class="px-4 py-3 gpa-micro-bold text-ink-body {{ $index === 6 ? 'text-right' : '' }}">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-soft/60">
                        <template x-for="(row, index) in visibleRows()" :key="row.key">
                            <tr class="transition-colors hover:bg-surface-shell/60">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="h-8 w-[5px] shrink-0 rounded-full"
                                            :class="row.accent ? 'bg-accent-deep' : 'bg-success-deep'"
                                            :style="'width: ' + row.bar + 'px'"></span>
                                        <span class="min-w-0">
                                            <span class="block text-xs font-semibold leading-4 text-ink"
                                                x-text="row.name"></span>
                                            <span class="block gpa-note text-ink-body" x-text="row.batch"></span>
                                            <span class="block gpa-note text-ink-body" x-text="row.grade"></span>
                                        </span>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <span class="gpa-mono-xs font-semibold text-ink" x-text="kg(row.order)"></span>
                                    <span class="ml-1 gpa-note text-ink-quiet">KG</span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="block gpa-meta-lg font-bold text-ink" x-text="kg(row.supply)"></span>
                                    <span class="mt-0.5 block text-2xs leading-3 text-ink-body" x-text="row.group"></span>
                                    <span class="block text-2xs leading-3 text-ink-body" x-text="row.partner"></span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="block gpa-meta text-ink-body" x-text="kg(row.buffer)"></span>
                                    <span class="mt-0.5 block gpa-note text-success-deep" x-text="'(' + row.zone + ')'"></span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="block gpa-meta-lg font-bold text-ink" x-text="kg(rowTotal(row))"></span>
                                    <span class="mt-1 block h-1.5 w-24 overflow-hidden rounded-full bg-surface-track">
                                        <span class="block h-full rounded-full bg-success-deep transition-all duration-300"
                                            :style="'width: ' + allocation(row) + '%'"></span>
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                                        <x-gpa.icon name="check-circle" class="h-2.5 w-2.5 shrink-0" />
                                        <span x-text="gapLabel(row)"></span>
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <button type="button" @click="toggleLock(row.key)"
                                        :class="isLocked(row.key)
                                            ? 'bg-accent text-success-ink'
                                            : 'bg-surface text-ink shadow-sub outline outline-1 outline-line-board hover:bg-surface-muted'"
                                        class="rounded px-2 py-1 gpa-micro-bold transition-colors"
                                        x-text="isLocked(row.key) ? 'Terkunci' : 'Kunci Stok'">
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <p x-cloak x-show="visibleRows().length === 0"
                    class="px-4 py-8 text-center">
                    <span class="block text-xs font-semibold text-ink">Tidak ada komoditas yang cocok.</span>
                    <span class="mt-1 block gpa-note text-ink-quiet">Ubah kata kunci pencarian pada topbar.</span>
                </p>
            </div>
        </section>

        {{-- Packing & Gate log --}}
        <div class="grid items-start gap-4">
            {{-- Persiapan Pesanan & Packing Cold-Chain --}}
            <section class="gpa-panel p-4">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft pb-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-success-deep" />
                        <div class="min-w-0">
                            <h2 class="gpa-section-title text-ink">{{ $packing['title'] }}</h2>
                            <p class="mt-1 gpa-note text-ink-body">{{ $packing['subtitle'] }}</p>
                        </div>
                    </div>

                    <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold text-success-ink">
                        {{ $packing['chip'] }}
                    </span>
                </div>

                <div class="mt-4 grid gap-4">
                    <template x-for="order in visibleOrders()" :key="order.po">
                        <article class="rounded-lg bg-surface-shell/60 p-2 outline outline-1 -outline-offset-1 outline-line-soft/50">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-1">
                                    <span class="rounded bg-surface px-2 py-0.5 gpa-meta-lg font-bold text-ink shadow-sub"
                                        x-text="order.po"></span>
                                    <span class="truncate text-xs font-semibold leading-4 text-ink"
                                        x-text="order.client"></span>
                                </div>

                                <span class="shrink-0 rounded px-2 py-0.5 gpa-micro-bold"
                                    :class="order.dispatch_tone === 'ink' ? 'bg-ink text-accent' : 'bg-accent text-ink'"
                                    x-text="order.dispatch"></span>
                            </div>

                            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                <div class="min-w-0">
                                    <p class="text-2xs font-semibold uppercase leading-3 tracking-[0.1em] text-ink-body">Alokasi
                                        Total</p>
                                    <p class="gpa-meta text-ink" x-text="order.allocation"></p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-2xs font-semibold uppercase leading-3 tracking-[0.1em] text-ink-body">Bay /
                                        Pack Station</p>
                                    <p class="gpa-meta text-success-deep" x-text="order.bay"></p>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-2xs font-semibold uppercase leading-3 tracking-[0.1em] text-ink-body">Status
                                        SOP Pack</p>
                                    <p class="gpa-meta text-ink" x-text="order.status"></p>
                                </div>
                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-track"
                                role="img" :aria-label="order.po + ' progres ' + order.percent + ' persen'">
                                <div class="h-full rounded-full bg-success-deep transition-all duration-300"
                                    :style="'width: ' + order.percent + '%'"></div>
                            </div>
                        </article>
                    </template>

                    <p x-cloak x-show="visibleOrders().length === 0"
                        class="rounded-lg bg-surface-shell/60 px-3 py-6 text-center">
                        <span class="block text-xs font-semibold text-ink">Tidak ada batch staging yang cocok.</span>
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="gpa-note text-ink-body">{{ $packing['footer']['label'] }}</p>
                    <button type="button" @click="openColdHub()"
                        class="inline-flex items-center gap-1 gpa-meta-lg font-bold text-success-deep transition-colors hover:text-success">
                        {{ $packing['footer']['link'] }}
                        <x-gpa.icon name="external-link" class="h-3 w-3 shrink-0" />
                    </button>
                </div>
            </section>

            {{-- Log Intake Timbangan Gate-01 --}}
            <section class="gpa-panel p-4">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft pb-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <x-gpa.icon name="check-circle" class="h-4 w-4 shrink-0 text-success-deep" />
                        <div class="min-w-0">
                            <h2 class="gpa-section-title text-ink">{{ $gate['title'] }}</h2>
                            <p class="mt-1 gpa-note text-ink-body">{{ $gate['subtitle'] }}</p>
                        </div>
                    </div>

                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                </div>

                <div class="mt-4 grid gap-2">
                    <template x-for="log in visibleLogs()" :key="log.ticket">
                        <article
                            class="flex items-start justify-between gap-3 rounded-lg bg-surface-shell/40 p-2 outline outline-1 -outline-offset-1 outline-line-soft/40">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1">
                                    <span class="rounded bg-ink px-1.5 py-0.5 gpa-micro-bold text-accent"
                                        x-text="log.ticket"></span>
                                    <span class="gpa-note text-ink-body" x-text="log.time"></span>
                                </div>

                                <p class="mt-1 text-sm font-semibold leading-5 text-ink">
                                    <span x-text="log.name"></span>
                                    <span class="text-ink-body">&bull;</span>
                                    <span x-text="log.commodity"></span>
                                </p>

                                <p class="gpa-mono-xs text-ink-body">
                                    <span x-text="'Bruto: ' + kg(log.gross) + ' | Tara: ' + kg(log.tare) + ' |'"></span>
                                    <br>
                                    <span class="font-semibold text-ink" x-text="'Netto: ' + kg(log.net)"></span>
                                </p>

                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink"
                                        x-text="log.qc"></span>
                                    <span class="gpa-note text-ink-quiet" x-text="log.note"></span>
                                </div>
                            </div>

                            <button type="button" @click="printSlip(log)"
                                class="shrink-0 rounded bg-surface px-2 py-1 gpa-micro-bold text-ink shadow-sub outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                Cetak Slip
                            </button>
                        </article>
                    </template>

                    <p x-cloak x-show="visibleLogs().length === 0"
                        class="rounded-lg bg-surface-shell/40 px-3 py-6 text-center">
                        <span class="block text-xs font-semibold text-ink">Tidak ada tiket timbangan yang cocok.</span>
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="gpa-note text-ink-body">{{ $gate['footer']['label'] }}</p>
                    <button type="button" @click="act('ticket')"
                        class="inline-flex items-center gap-1 rounded bg-ink px-3 py-1.5 text-accent transition-colors hover:bg-ink-muted">
                        <x-gpa.icon name="plus" class="h-2.5 w-2.5 shrink-0" />
                        <span class="gpa-micro-bold">{{ $gate['footer']['action'] }}</span>
                    </button>
                </div>
            </section>
        </div>

        {{-- Status strip --}}
        <section class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft pt-4">
            <div class="flex flex-wrap items-center gap-4">
                @foreach ($footer['left'] as $item)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $item['tone'] === 'success' ? 'bg-success-deep' : 'bg-ink-subtle' }}"
                            aria-hidden="true"></span>
                        <span class="gpa-note text-ink-body">{{ $item['label'] }}</span>
                    </span>
                @endforeach
            </div>

            <p class="gpa-note text-ink-quiet">{{ $footer['right'] }}</p>
        </section>
    </div>
@endsection
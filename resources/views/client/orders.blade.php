@extends('layouts.dashboard')

@section('title', 'Daftar Pesanan Saya (Orders Management)')

@php
    $rupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $kilogram = fn ($value) => number_format($value, 1, ',', '.');

    $statusStyles = [
        'accent' => 'bg-accent text-ink ring-accent-edge',
        'brand' => 'bg-accent text-success-ink',
        'warning' => 'bg-warning-soft text-warning-deep ring-warning/40',
        'pill' => 'bg-surface-pill text-ink-body ring-line-board',
        'track' => 'bg-surface-pill text-ink-body ring-line-board/60',
    ];

    $statusDotStyles = [
        'accent' => 'bg-ink',
        'brand' => 'bg-accent',
        'warning' => 'bg-warning-deep',
        'pill' => 'bg-success-deep',
        'track' => 'bg-ink-quiet',
    ];

    $gradeStyles = [
        'success' => 'text-success-deep',
        'danger' => 'text-danger',
        'default' => 'text-ink-body',
    ];
@endphp

@section('content')
    <div x-data="clientOrders(@js([
        'orders' => $orders,
        'tabs' => $statusTabs,
        'filters' => $filters,
        'pagination' => $pagination,
    ]))" class="flex flex-col gap-4">
        {{-- ------------------------------------------------------------------ --}}
        {{-- Kop halaman + aksi export / buat pesanan --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="flex flex-col gap-3 border-b border-line-soft/40 pb-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="gpa-section-title text-ink">{{ $header['title'] }}</h1>
                    <span
                        class="rounded-full bg-accent px-2.5 py-0.5 gpa-micro-bold text-success-ink">
                        {{ $header['chip'] }}
                    </span>
                </div>
                <p class="max-w-[56rem] gpa-body text-ink-body">{{ $header['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="exportRecap()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-4 py-2 text-ink-body shadow-sub transition-colors hover:bg-surface-disabled hover:text-ink">
                    <x-gpa.icon name="download" class="h-3 w-3 text-ink" />
                    <span class="gpa-meta-lg text-ink">{{ $header['export'] }}</span>
                </button>
                <a href="{{ route('cart') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                    <span class="gpa-meta-lg font-bold text-white">{{ $header['create'] }}</span>
                </a>
            </div>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Tab status pesanan --}}
        {{-- ------------------------------------------------------------------ --}}
        <div class="gpa-scroll-x overflow-hidden pb-1">
            <div class="flex items-center gap-2" role="tablist" aria-label="Filter status pesanan">
                @foreach ($statusTabs as $tab)
                    @php
                        $isActive = $loop->first;
                    @endphp
                    <button type="button" role="tab" @click="selectTab(@js($tab['value']))"
                        :aria-selected="status === @js($tab['value']) ? 'true' : 'false'"
                        @class([
                            'inline-flex shrink-0 items-center gap-2 rounded-lg px-4 py-2 shadow-sub transition-colors',
                            'bg-ink' => $isActive,
                            'bg-surface-pill hover:bg-surface-disabled' => ! $isActive,
                        ])>
                        <span @class([
                            'gpa-meta-lg uppercase',
                            'font-bold text-white' => $isActive,
                            'font-medium text-ink-body' => ! $isActive,
                        ])>{{ $tab['label'] }}</span>
                        <span @class([
                            'rounded px-1.5 py-0.5 text-[9px] font-bold uppercase leading-3 tracking-[0.12em]',
                            'bg-surface-disabled text-ink-body' => $isActive,
                            'bg-surface-pill text-ink-body' => ! $isActive,
                        ])>{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Filter rentang tanggal, gudang, pembayaran, dan pencarian --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card flex flex-col gap-3 p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                @foreach (['range', 'hub', 'payment'] as $key)
                    <label class="flex h-[5.5rem] flex-col justify-center gap-1 rounded-lg border border-line-soft/70 bg-canvas px-3 py-1.5">
                        <span class="gpa-micro text-ink-body">{{ $filters[$key]['label'] }}</span>
                        <select @if ($key === 'range') x-model="range" @elseif ($key === 'hub') x-model="hub"
                            @else x-model="payment" @endif
                            class="-ml-1 w-full min-w-[7.5rem] cursor-pointer border-0 bg-transparent p-0 pr-6 text-xs font-semibold leading-4 text-ink focus:ring-0">
                            @foreach ($filters[$key]['options'] as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                @endforeach

                <button type="button" @click="deviationOnly = ! deviationOnly" role="switch"
                    :aria-checked="deviationOnly ? 'true' : 'false'" aria-label="{{ $filters['deviation_label'] }}"
                    class="flex h-[5.5rem] items-center gap-2 rounded-lg border border-line-soft/70 bg-canvas px-3 py-1.5 transition-colors hover:border-brand"
                    :class="deviationOnly ? 'border-brand bg-surface-muted' : ''">
                    <span
                        class="flex h-[18px] w-[18px] shrink-0 items-center justify-center rounded bg-ink text-white">
                        <x-gpa.icon name="gauge" class="h-3 w-3" />
                    </span>
                    <span class="max-w-[9rem] text-left font-mono text-[11px] font-medium leading-[14px] text-ink">
                        {{ $filters['deviation_label'] }}
                    </span>
                </button>

                <button type="button" x-cloak x-show="hasFilters()" @click="resetFilters()"
                    class="h-[5.5rem] rounded-lg px-2 text-[11px] font-semibold leading-[14px] text-ink-quiet underline underline-offset-4 transition-colors hover:text-ink">
                    Reset
                    filter
                </button>
            </div>

            <label class="relative flex w-full items-center lg:w-72">
                <span class="sr-only">{{ $filters['search'] }}</span>
                <x-gpa.icon name="search" class="pointer-events-none absolute left-3.5 h-3.5 w-3.5 text-ink-body" />
                <input type="search" x-model.debounce.200ms="search"
                    placeholder="{{ $filters['search'] }}"
                    class="gpa-control h-10 w-full rounded-lg border-line-soft bg-surface pl-10 text-sm">
            </label>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Tabel pesanan --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card overflow-hidden">
            <div class="gpa-scroll-x">
                <table class="w-full min-w-[73.75rem] border-collapse">
                    <caption class="sr-only">Daftar pesanan klien beserta hasil timbangan netto, surat jalan, status, dan tagihan final</caption>
                    <thead>
                        <tr class="border-b border-line-soft/80 bg-surface-shell">
                            @foreach ($columns as $column)
                                <th scope="col"
                                    @class([
                                        'px-4 py-3 gpa-micro-bold uppercase tracking-[0.1em] text-ink-body',
                                        'text-right' => $column['align'] === 'right',
                                        'text-left' => $column['align'] !== 'right',
                                    ])>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr x-show="isVisible(@js($order))"
                                class="border-b border-line-soft/40 align-top">
                                <td class="w-[9.28rem] px-4 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <x-gpa.icon name="receipt"
                                            class="h-3.5 w-3.5 shrink-0 {{ in_array($order['stage'], ['verifikasi', 'timbang'], true) ? 'text-warning-deep' : 'text-success-deep' }}" />
                                        <span class="text-xs font-bold leading-4 text-ink">{{ $order['po'] }}</span>
                                    </div>
                                    <p class="pb-1 gpa-micro text-ink-body">{{ $order['date'] }}, {{ $order['time'] }}</p>
                                    <span class="mt-0.5 inline-block rounded bg-surface-pill px-1.5 py-0.5 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink">
                                        {{ $order['hub_chip'] }}
                                    </span>
                                </td>

                                <td class="w-[12.25rem] px-4 py-4">
                                    <p class="text-xs font-semibold leading-4 text-ink">{{ $order['commodities'] }}</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                            Total Estimasi:
                                        </span>
                                        <span class="rounded bg-surface-pill px-1.5 py-0.5 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">
                                            {{ $order['estimated_label'] }} KG
                                        </span>
                                    </div>
                                    <p @class([
                                        'mt-1 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em]',
                                        $gradeStyles[$order['grade_tone']],
                                    ])>{{ $order['grade_note'] }}</p>
                                </td>

                                <td class="w-[12.8rem] px-4 py-4">
                                    @if ($order['weight'] !== null)
                                        @if (! empty($order['shipment_breakdown']))
                                            <p class="flex flex-wrap items-baseline text-xs leading-4 text-ink">
                                                @foreach ($order['shipment_breakdown'] as $part)
                                                    <span @class([
                                                        'font-semibold' => $part['label'] === 'Kirim:',
                                                        'font-normal' => $part['label'] !== 'Kirim:',
                                                        'text-danger' => $part['tone'] === 'danger',
                                                    ])>{{ $part['label'] }} {{ $part['value'] }}</span>
                                                @endforeach
                                            </p>
                                        @endif
                                        <div class="mt-1 flex items-baseline gap-1.5">
                                            <span @class([
                                                'gpa-figure',
                                                'text-warning-deep' => ($order['weight_tone'] ?? 'default') === 'warning',
                                                'text-ink' => ($order['weight_tone'] ?? 'default') !== 'warning',
                                            ])>{{ $order['weight_label_value'] }}</span>
                                            <span class="gpa-meta-lg font-bold text-ink-body">{{ $order['weight_label'] }}</span>
                                        </div>
                                        <div class="mt-1 flex items-start gap-1">
                                            <x-gpa.icon name="scale"
                                                class="mt-px h-3 w-3 shrink-0 {{ ($order['deviation_percent'] ?? 0) > 1 ? 'text-warning-deep' : 'text-success-deep' }}" />
                                            <span @class([
                                                'text-[9px] font-bold uppercase leading-3 tracking-[0.12em]',
                                                'text-warning-deep' => ($order['deviation_percent'] ?? 0) > 1,
                                                'text-success-deep' => ($order['deviation_percent'] ?? 0) <= 1,
                                            ])>{{ $order['deviation_label'] }}</span>
                                        </div>
                                        <p @class([
                                            'mt-1 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em]',
                                            'text-warning-deep' => ($order['scale_tone'] ?? 'default') === 'warning',
                                            'text-ink-body' => ($order['scale_tone'] ?? 'default') !== 'warning',
                                        ])>{{ $order['scale_note'] }}</p>
                                    @elseif (! empty($order['weight_chip']))
                                        <span class="inline-flex items-center gap-1.5 rounded bg-surface-pill px-2 py-1 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink ring-1 ring-inset ring-line-board/60">
                                            <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                                            {{ $order['weight_chip'] }}
                                        </span>
                                        <p class="mt-1.5 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                            {{ $order['scale_note'] }}
                                        </p>
                                        <p class="text-[9px] font-medium uppercase leading-3 tracking-[0.12em] text-success-deep">
                                            {{ $order['scale_target'] }}
                                        </p>
                                    @else
                                        <p class="text-xs italic leading-4 text-ink-body">{{ $order['weight_pending'] }}</p>
                                        <p @class([
                                            'mt-1 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em]',
                                            $gradeStyles[$order['scale_tone'] ?? 'default'],
                                        ])>{{ $order['scale_note'] }}</p>
                                    @endif
                                </td>

                                <td class="w-[10.95rem] px-4 py-4">
                                    @if (! empty($order['sj']))
                                        <p class="text-xs font-bold leading-4 text-ink">{{ $order['sj'] }}</p>
                                        <p class="pt-0.5 font-inter text-xs font-medium leading-4 text-ink">{{ $order['vehicle'] }}</p>
                                    @else
                                        <p class="text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                            {{ $order['sj_pending'] }}
                                        </p>
                                        @if (! empty($order['vehicle']))
                                            <p class="pt-0.5 font-inter text-xs font-medium leading-4 text-ink">{{ $order['vehicle'] }}</p>
                                        @endif
                                    @endif
                                    <p class="text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                        {{ $order['driver_note'] }}
                                    </p>
                                    <p @class([
                                        'pt-1 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em]',
                                        'text-warning-deep' => ($order['shipment_tone'] ?? 'default') === 'warning',
                                        'text-success-deep' => ($order['shipment_tone'] ?? 'default') === 'success',
                                    ])>{{ $order['shipment_note'] ?? '' }}</p>
                                </td>

                                <td class="w-[10.09rem] px-4 py-4">
                                    <span @class([
                                        'inline-flex items-center gap-1.5 rounded px-2 py-0.5 text-[9px] font-bold uppercase leading-3 tracking-[0.45px] ring-1 ring-inset',
                                        $statusStyles[$order['status_tone']],
                                    ])>
                                        <span @class([
                                            'h-1.5 w-1.5 shrink-0 rounded-full',
                                            $statusDotStyles[$order['status_tone']],
                                        ]) aria-hidden="true"></span>
                                        {{ $order['status'] }}
                                    </span>
                                    <p class="pt-1.5 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">
                                        {{ $order['status_meta'] }}
                                    </p>
                                    <p class="text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                        {{ $order['status_note'] }}
                                    </p>
                                </td>

                                <td class="w-[9.71rem] px-4 py-4">
                                    <p class="text-sm font-bold leading-5 text-ink">{{ $rupiah($order['total']) }}</p>
                                    <p class="pb-0.5 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                        {{ $order['total_note'] }}
                                    </p>
                                    @if (! empty($order['total_chip']))
                                        <p @class([
                                            'text-[9px] font-bold uppercase leading-3 tracking-[0.12em]',
                                            'text-success-deep' => $order['total_chip_tone'] === 'success',
                                            'text-ink-body' => $order['total_chip_tone'] !== 'success',
                                        ])>{{ $order['total_chip'] }}</p>
                                    @endif
                                </td>

                                <td class="w-[8.67rem] px-4 py-4">
                                    <div class="flex flex-col items-stretch gap-1.5">
                                        @foreach ($order['actions'] as $action)
                                            <button type="button" @click="notifyAction(@js($order), @js($action))"
                                                class="inline-flex items-center justify-center gap-1 rounded px-2.5 py-1.5 text-[9px] font-semibold uppercase leading-3 tracking-[0.45px] transition-colors"
                                                :class="actionTone(@js($action['variant']))">
                                                <x-gpa.icon :name="$action['icon']" class="h-3 w-3 shrink-0" />
                                                {{ $action['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p x-cloak x-show="visibleCount() === 0"
                class="px-6 py-10 text-center gpa-body text-ink-body">
                Tidak ada pesanan yang cocok dengan filter aktif.
            </p>

            <div
                class="flex flex-col gap-3 border-t border-line-soft/60 bg-surface-shell px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2 gpa-meta-lg text-ink-body">
                    <span x-text="summaryLabel()">{{ $pagination['total'] }} pesanan aktif</span>
                    <span class="text-line-board" aria-hidden="true">|</span>
                    <span>{{ $header['timezone'] }}</span>
                </div>

                <nav class="flex items-center gap-1" aria-label="Navigasi halaman pesanan">
                    <button type="button" @click="page > 1 && goToPage(page - 1)"
                        :disabled="page === 1"
                        class="rounded bg-canvas px-2.5 py-1 gpa-meta-lg font-semibold text-ink outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-40">
                        Sebelumnya
                    </button>

                    <template x-for="number in pageNumbers()" :key="number">
                        <button type="button" @click="goToPage(number)"
                            :class="page === number ? 'bg-ink text-white' : 'bg-surface-pill text-ink-body hover:bg-surface-disabled'"
                            class="rounded px-2.5 py-1 gpa-meta-lg font-semibold transition-colors"
                            :aria-current="page === number ? 'page' : null"
                            x-text="number"></button>
                    </template>

                    @if ($pagination['last_page'] > 3)
                        <span class="px-1 gpa-meta-lg text-ink-body" aria-hidden="true">...</span>
                    @endif

                    <button type="button" @click="goToPage(page + 1)"
                        :disabled="page === lastPage"
                        class="rounded bg-canvas px-2.5 py-1 gpa-meta-lg font-semibold text-ink outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-40">
                        Selanjutnya
                    </button>
                </nav>
            </div>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Banner integritas Rule 04 & 05 --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card flex flex-col gap-3 bg-brand p-4 ring-1 ring-inset ring-accent/30 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-accent">
                    <x-gpa.icon name="scale" class="h-4 w-4 text-ink" />
                </span>
                <div class="flex min-w-0 flex-col">
                    <p class="gpa-meta-lg font-bold uppercase tracking-[0.7px] text-accent">{{ $integrity['title'] }}</p>
                    <p class="max-w-[56rem] text-[9px] font-semibold uppercase leading-[14.63px] tracking-[0.12em] text-surface-disabled">
                        {{ $integrity['body'] }}
                    </p>
                </div>
            </div>
            <span
                class="inline-flex shrink-0 items-center gap-1 rounded bg-accent px-3 py-1.5 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">
                <x-gpa.icon name="shield" class="h-3 w-3" />
                {{ $integrity['chip'] }}
            </span>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Lima kartu ringkasan operasional --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($metrics as $metric)
                <article class="gpa-card flex min-h-[7.875rem] flex-col justify-between gap-3 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink-body">
                            {{ $metric['label'] }}
                        </h2>
                        <x-gpa.icon :name="$metric['icon']"
                            class="h-4 w-4 shrink-0 {{ $metric['tone'] === 'success' ? 'text-success-deep' : 'text-success-deep' }}" />
                    </div>
                    <div class="pt-1">
                        <p @class([
                            'gpa-figure',
                            'text-success-deep' => $metric['tone'] === 'success',
                            'text-ink' => $metric['tone'] !== 'success',
                        ])>{{ $metric['value'] }}</p>
                        @if (! empty($metric['unit']))
                            <p @class([
                                'text-[9px] font-bold uppercase leading-3 tracking-[0.12em]',
                                $metric['tone'] === 'success' ? 'text-success-deep' : 'text-success-deep',
                            ])>{{ $metric['unit'] }}</p>
                        @endif
                        @if (! empty($metric['note']))
                            <p class="text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                {{ $metric['note'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    </div>
@endsection
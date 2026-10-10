@extends('layouts.dashboard')

@section('title', 'Dashboard Ringkasan & Operasional Klien B2B')

@php
    $rupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $kilogram = fn ($value) => number_format($value, 0, ',', '.').' kg';
    $creditBarWidth = max(0, min(100, $credit['percent'])).'%';
@endphp

@section('content')
    <div class="flex flex-col gap-5">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Kop halaman + ringkasan kontrak --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:p-6">
            <div class="min-w-0 flex flex-col gap-2">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="gpa-section-title text-ink">Dashboard Ringkasan &amp; Operasional Klien B2B</h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 text-[12px] font-semibold text-success-ink ring-1 ring-inset ring-accent-edge">
                        <span class="h-1.5 w-1.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        Kontrak Aktif
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px]">
                    <span class="flex items-baseline gap-1.5">
                        <span class="text-ink-subtle">ID Kontrak</span>
                        <span class="font-semibold text-ink">{{ $contract['id'] }}</span>
                    </span>
                    <span class="h-3 w-px bg-line-soft" aria-hidden="true"></span>
                    <span class="flex items-baseline gap-1.5">
                        <span class="text-ink-subtle">Dock Utama</span>
                        <span class="font-semibold uppercase text-ink">{{ $contract['dock'] }}</span>
                    </span>
                    <span class="h-3 w-px bg-line-soft" aria-hidden="true"></span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <span class="text-ink-subtle">Live Sync</span>
                        <span class="font-medium tabular-nums text-ink-body" x-text="syncedAt">{{ $syncedAt->format('H:i:s') }} WIB</span>
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="#dokumen"
                    class="inline-flex items-center gap-2 rounded-lg bg-surface-pill px-3.5 py-2 text-[13px] font-semibold text-ink transition-colors hover:bg-surface-disabled">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                    Unduh Statement Bulanan
                </a>
                <a href="#quick-reorder"
                    class="inline-flex items-center gap-2 rounded-lg bg-ink px-4 py-2 text-[13px] font-semibold text-white shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="plus" class="h-3.5 w-3.5" />
                    Buat Pesanan Baru / PO
                </a>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-[13px] font-medium text-ink-subtle">Plafon Kredit &amp; Tempo (Top 30D)</h2>
                        <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-ink">{{ $rupiah($credit['available']) }}</p>
                    <p class="gpa-body text-ink-body">
                        Sisa kuota dari total
                        <span class="font-medium text-ink">{{ $rupiah($credit['limit']) }}</span>
                    </p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-2 text-[13px]">
                            <span class="font-semibold text-success-deep">{{ $credit['status'] }}</span>
                            <span class="font-medium tabular-nums text-ink-body">{{ number_format($credit['percent'], 1, ',', '') }}% tersedia</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-surface-pill" role="img"
                            aria-label="Kuota tersedia {{ number_format($credit['percent'], 1, ',', '') }} persen">
                            <div class="h-full rounded-full bg-success-deep"
                                style="width: {{ $creditBarWidth }}"></div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-[13px] font-medium text-ink-subtle">Pesanan In-Flight Pipeline</h2>
                        <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-0.5 text-ink">{{ $pipeline['count'] }} Pesanan Aktif</p>
                    <p class="text-[13px] font-semibold text-success-deep">Valuasi {{ $rupiah($pipeline['value']) }}</p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <dl class="flex flex-col gap-1.5">
                        @foreach ($pipeline['stages'] as $stage)
                            <div class="flex items-center justify-between gap-2 text-[13px]">
                                <dt class="text-ink-subtle">{{ $stage['label'] }}</dt>
                                <dd class="shrink-0 font-semibold tabular-nums text-ink">{{ $stage['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-[13px] font-medium text-ink-subtle">Serapan Kuota Panen (Bulan Ini)</h2>
                        <x-gpa.icon name="leaf" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="flex flex-wrap items-baseline gap-1 text-ink">
                        <span class="gpa-figure">{{ $kilogram($harvest['weight']) }}</span>
                        <span class="font-inter text-sm text-ink-body">/ {{ $kilogram($harvest['target']) }}</span>
                    </p>
                    <p class="gpa-body font-bold text-success-deep">
                        {{ number_format($harvest['percent'], 1, ',', '') }}% kuota alokasi bulanan terpenuhi
                    </p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <dl class="flex flex-col gap-1.5">
                        @foreach ($harvest['commodities'] as $commodity)
                            <div class="flex items-center justify-between gap-2 text-[13px]">
                                <dt class="text-ink-subtle">{{ $commodity['name'] }}</dt>
                                <dd class="shrink-0 font-semibold tabular-nums text-ink">{{ $kilogram($commodity['weight']) }}</dd>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2 text-[13px]">
                            <dt class="text-ink-subtle">Deviasi timbang riil</dt>
                            <dd class="shrink-0 font-semibold tabular-nums text-success-deep">
                                {{ number_format($harvest['deviation'], 1, ',', '') }}%
                            </dd>
                        </div>
                        <p class="text-[12px] text-ink-quiet">
                            Toleransi aman s.d. {{ number_format($harvest['deviation_tolerance'], 1, ',', '') }}%
                        </p>
                    </dl>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-[13px] font-medium text-ink-subtle">Kepatuhan SLA &amp; Mutu Cold-Chain</h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-success-deep">{{ number_format($sla['otif'], 1, ',', '') }}%</p>
                    <p class="gpa-body text-ink-body">On-Time In-Full Delivery (OTIF)</p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <dl class="flex flex-col gap-1.5 text-[13px]">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-ink-subtle">Insiden cold-chain</dt>
                            <dd class="shrink-0 font-semibold tabular-nums text-ink">{{ $sla['incidents'] }} insiden</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-ink-subtle">Durasi bongkar dock</dt>
                            <dd class="shrink-0 font-semibold tabular-nums text-ink">{{ $sla['unload_minutes'] }} menit (avg)</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-ink-subtle">QC reject rate</dt>
                            <dd class="shrink-0 font-semibold tabular-nums text-success-deep">
                                {{ number_format($sla['reject_rate'], 2, ',', '') }}%
                                <span class="font-normal text-ink-quiet">(target &lt; 1%)</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </article>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pemberitahuan penagihan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="dokumen"
            class="flex flex-col gap-4 rounded-r-xl border-l-4 border-warning bg-warning-wash p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <span class="mt-0.5 h-[18px] w-1 shrink-0 bg-warning" aria-hidden="true"></span>
                <div class="flex min-w-0 flex-col gap-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-bold leading-5 text-warning-deep">{{ $invoice['title'] }}</h2>
                        <span class="rounded-full bg-warning px-2.5 py-0.5 text-[12px] font-semibold text-warning-soft">
                            {{ $invoice['due'] }}
                        </span>
                    </div>
                    <p class="gpa-body text-ink-body">{{ $invoice['body'] }}</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="#dokumen"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-1.5 text-[13px] font-semibold text-ink transition-colors hover:bg-surface-disabled">
                    <x-gpa.icon name="invoice" class="h-3 w-3" />
                    Unduh Faktur PDF
                </a>
                <button type="button" @click="$dispatch('gpa:toast', { title: 'Unggah bukti transfer', message: 'Formulir bukti transfer dibuka di tab Dokumen & Faktur.', tone: 'info' })"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-3.5 py-1.5 text-[13px] font-semibold text-white shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="upload" class="h-3.5 w-3.5" />
                    Unggah Bukti Transfer Manual
                </button>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Live tracking --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-6 p-5 lg:p-6">
            <div class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <h2 class="gpa-section-title text-ink">{{ $shipment['title'] }}</h2>
                    </div>
                    <p class="gpa-body text-ink-body">{{ $shipment['summary'] }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 rounded-lg bg-brand px-3 py-1.5 ring-1 ring-inset ring-accent/30">
                        <x-gpa.icon name="thermometer" class="h-4 w-4 text-accent" />
                        <div class="flex items-baseline gap-2">
                            <span class="text-[12px] text-accent/90">Telemetri Chiller:</span>
                            <span class="text-sm font-bold text-accent">{{ $shipment['temperature'] }}</span>
                            <span class="text-[12px] font-semibold text-accent">{{ strtoupper($shipment['temperature_state']) }}</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 rounded-lg bg-surface-pill px-3 py-2">
                        <span class="text-[12px] text-ink-subtle">Estimasi Tiba:</span>
                        <span class="text-sm font-bold text-ink">{{ $shipment['eta'] }}</span>
                        <span class="text-[12px] text-ink-subtle">({{ $shipment['eta_note'] }})</span>
                    </div>
                </div>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($shipment['steps'] as $index => $step)
                    @php
                        $isActive = $step['state'] === 'active';
                        $isPending = $step['state'] === 'pending';
                        $isLast = $loop->last;
                        $lineColor = $isActive ? 'bg-success-deep' : ($isPending ? 'bg-line-board' : 'bg-ink');
                        $textColor = $isPending ? 'text-ink-quiet' : 'text-ink';
                    @endphp

                    <li @class([
                        'flex flex-col gap-1 rounded-xl border p-3',
                        'border-accent-edge bg-surface-shell' => $isActive,
                        'border-transparent' => ! $isActive,
                        'opacity-60' => $isPending,
                    ])>
                        <div class="flex items-center gap-2 pb-2">
                            <span @class([
                                'flex h-7 w-7 shrink-0 items-center justify-center rounded-full',
                                'bg-accent text-ink ring-4 ring-brand/20' => $isActive,
                                'bg-ink text-white' => ! $isActive,
                                'bg-surface-disabled text-ink-quiet ring-0' => $isPending,
                            ])>
                                @if ($isPending)
                                    <span class="gpa-meta-lg">{{ $index + 1 }}</span>
                                @else
                                    <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                                @endif
                            </span>
                            @unless ($isLast)
                                <span class="h-0.5 flex-1 {{ $lineColor }}" aria-hidden="true"></span>
                            @endunless
                        </div>

                        <p @class([
                            'flex items-center gap-1.5 text-[12px] font-semibold',
                            'text-ink' => $isActive,
                            'text-success-deep' => ! $isActive && ! $isPending,
                            'text-ink-body' => $isPending,
                        ])>
                            @if ($isActive)
                                <span class="h-1.5 w-1.5 rounded-full bg-ink" aria-hidden="true"></span>
                            @endif
                            {{ $step['phase'] }} &middot; {{ $step['status'] }}
                        </p>

                        <p class="pt-0.5 text-sm font-bold leading-5 {{ $textColor }}">{{ $step['title'] }}</p>
                        <p class="gpa-body {{ $textColor }} {{ $isActive ? 'font-semibold' : 'text-ink-body' }}">
                            {{ $step['detail'] }}
                        </p>
                        <p class="pt-1 font-mono text-[12px] leading-4 {{ $isActive ? 'font-semibold text-success-deep' : 'text-ink-quiet' }}">
                            {{ $step['time'] }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Tabel pesanan berjalan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="pesanan-berjalan" class="gpa-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line-soft p-5 lg:flex-row lg:items-center lg:justify-between lg:p-6">
                <div class="flex min-w-0 flex-col gap-1">
                    <h2 class="gpa-section-title text-ink">Pesanan Berjalan</h2>
                    <p class="gpa-body text-ink-body">
                        Status pesanan Anda dari panen sampai tiba di gudang.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="filter-status" class="text-[13px] text-ink-subtle">Tampilkan status</label>
                    <select id="filter-status" x-model="statusFilter"
                        class="gpa-control h-auto w-auto min-w-[12rem] rounded-lg border-line-soft bg-canvas py-1.5 pr-8 text-[13px]">
                        <template x-for="option in statusOptions(@js($orders))" :key="option.value">
                            <option :value="option.value" x-text="option.label"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[56rem] border-collapse text-left">
                    <caption class="sr-only">Daftar pesanan berjalan beserta status pemenuhan komoditas</caption>
                    <thead>
                        <tr class="border-b border-line-soft">
                            <th scope="col" class="px-4 py-3 gpa-meta-lg whitespace-nowrap text-ink-quiet">No. PO</th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg whitespace-nowrap text-ink-quiet">Tanggal</th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg text-ink-quiet">Komoditas</th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg whitespace-nowrap text-ink-quiet">Berat</th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg text-ink-quiet">Supir</th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg whitespace-nowrap text-ink-quiet">Status</th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg whitespace-nowrap text-right text-ink-quiet">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in filteredOrders(@js($orders))" :key="order.po">
                            <tr class="border-t border-line-hair align-middle transition-colors hover:bg-surface-shell/70">
                                <td class="px-4 py-3.5 font-mono text-sm font-semibold tabular-nums tracking-tight text-ink">
                                    <span x-text="order.po"></span>
                                </td>
                                <td class="px-4 py-3.5 text-[13px] tabular-nums text-ink-body"><span x-text="order.date"></span></td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm font-medium leading-5 text-ink"><span x-text="order.commodities"></span></p>
                                    <p class="mt-0.5 text-xs leading-4 text-ink-subtle"><span x-text="order.packaging"></span></p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <template x-if="order.weight">
                                        <div class="flex flex-col gap-1">
                                            <p class="whitespace-nowrap text-sm font-semibold leading-5 tabular-nums text-ink">
                                                <span x-text="order.weight"></span>
                                            </p>
                                            <template x-if="order.weight_note">
                                                <p class="text-xs leading-4 text-ink-subtle" x-text="order.weight_note"></p>
                                            </template>
                                            <template x-if="order.scale_note">
                                                <p class="text-xs font-medium leading-4 text-success-deep" x-text="order.scale_note"></p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="! order.weight && order.weight_note">
                                        <span class="inline-block rounded-md bg-surface-pill px-2 py-0.5 text-xs text-ink-body"
                                            x-text="order.weight_note"></span>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5">
                                    <template x-if="order.driver">
                                        <div class="flex flex-col gap-1">
                                            <p class="whitespace-nowrap text-sm font-medium leading-5 text-ink"><span x-text="order.driver"></span></p>
                                            <template x-if="order.sj">
                                                <p class="whitespace-nowrap font-mono text-xs leading-4 text-ink-subtle">
                                                    <span x-text="order.sj"></span>
                                                </p>
                                            </template>
                                            <template x-if="order.driver_note">
                                                <p class="text-xs leading-4 text-ink-quiet">
                                                    <span x-text="order.driver_note"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="! order.driver">
                                        <p class="text-sm italic text-ink-subtle"><span x-text="order.driver_note"></span></p>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="{
                                            'bg-accent text-success-ink ring-1 ring-inset ring-accent-edge': order.status_tone === 'accent',
                                            'bg-surface-pill text-ink-body ring-1 ring-inset ring-line-board': order.status_tone === 'neutral',
                                            'bg-warning-soft/40 text-warning ring-1 ring-inset ring-warning-soft': order.status_tone === 'warning'
                                        }">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="{
                                                'bg-success-deep': order.status_tone === 'accent',
                                                'bg-ink-quiet': order.status_tone === 'neutral',
                                                'bg-warning-deep': order.status_tone === 'warning'
                                            }"></span>
                                        <span x-text="order.status"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex justify-end gap-1.5">
                                        <template x-for="action in order.actions" :key="action.label">
                                            <button type="button"
                                                @click="$dispatch('gpa:toast', { title: action.label, message: order.po + ' - modul ini sedang disiapkan.', tone: 'info' })"
                                                class="whitespace-nowrap rounded-md px-2.5 py-1 text-xs font-medium transition-colors"
                                                :class="action.variant === 'solid'
                                                    ? 'bg-ink text-white hover:bg-brand-deep'
                                                    : 'bg-surface-pill text-ink-body hover:bg-surface-disabled hover:text-ink'"
                                                x-text="action.label"></button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p x-cloak x-show="filteredOrders(@js($orders)).length === 0"
                class="px-6 py-12 text-center text-[13px] text-ink-body">
                Tidak ada pesanan dengan status ini.
            </p>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Quick reorder --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="quick-reorder" class="gpa-card flex flex-col gap-4 p-5 lg:p-6">
            <div class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="badge-check" class="h-4 w-5 shrink-0 text-success-deep" />
                        <h2 class="gpa-section-title text-ink">Quick Reorder Kontrak (Pesan Cepat Kuota Harian)</h2>
                    </div>
                    <p class="gpa-body text-ink-body">
                        Reorder otomatis komoditas rutin dengan harga kontrak tetap tanpa perlu membuat PO manual dari awal.
                    </p>
                </div>
                <span
                    class="w-fit rounded-lg border border-line-soft bg-surface-shell px-3 py-1 text-[12px] font-semibold text-success-deep">
                    Harga Kontrak Dikunci s.d. 31 Des 2024
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($reorder as $item)
                    <article class="flex flex-col justify-between gap-4 rounded-xl border border-line-soft bg-surface p-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[12px] font-semibold text-success-deep">{{ $item['grade'] }}</p>
                                <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-ink-quiet" />
                            </div>
                            <h3 class="text-base font-bold leading-6 text-ink">{{ $item['name'] }}</h3>
                            <p class="flex items-baseline gap-1">
                                <span class="font-inter text-lg font-bold leading-6 text-ink">{{ $rupiah($item['price']) }}</span>
                                <span class="font-inter text-xs text-ink-body">/ kg</span>
                            </p>
                            <p class="flex items-baseline justify-between gap-2 text-[13px]">
                                <span class="text-ink-subtle">Sisa alokasi kontrak</span>
                                <span class="font-semibold tabular-nums text-ink">{{ number_format($item['remaining'], 0, ',', '.') }} kg</span>
                            </p>
                        </div>

                        <div class="border-t border-line-soft/60 pt-3">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[13px] text-ink-subtle">Jumlah (kg)</span>
                                    <div class="flex items-center overflow-hidden rounded bg-canvas ring-1 ring-inset ring-line-board">
                                        <button type="button" @click="step(@js($item), -1)"
                                            aria-label="Kurangi {{ $item['name'] }}"
                                            class="px-2 py-0.5 font-mono text-sm leading-5 text-ink transition-colors hover:bg-surface-muted">
                                            &minus;
                                        </button>
                                        <input type="number" inputmode="numeric" min="0"
                                            max="{{ $item['remaining'] }}" step="{{ $item['step'] }}"
                                            value="{{ $item['initial_qty'] }}"
                                            x-model.number="quantities['{{ $item['key'] }}']"
                                            @change="setQuantity(@js($item), $event.target.value)"
                                            aria-label="Jumlah kilogram {{ $item['name'] }}"
                                            class="w-12 border-x border-line-soft bg-transparent py-0.5 text-center font-mono text-[11px] font-bold leading-[14px] text-ink focus:outline-none focus:ring-1 focus:ring-inset focus:ring-brand [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                                        <button type="button" @click="step(@js($item), 1)"
                                            aria-label="Tambah {{ $item['name'] }}"
                                            class="px-2 py-0.5 font-mono text-sm leading-5 text-ink transition-colors hover:bg-surface-muted">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <button type="button" @click="addToDraft(@js($item))"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-ink px-3 py-2 text-[13px] font-semibold text-white transition-colors hover:bg-brand-deep">
                                    <x-gpa.icon name="plus" class="h-3.5 w-3.5" />
                                    Tambah ke Draft PO
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Ringkasan draft PO --}}
            <div x-cloak x-show="draft.length > 0" x-transition
                class="flex flex-col gap-3 rounded-xl border border-accent-edge bg-surface-shell p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h3 class="gpa-label text-ink">Draft PO Berjalan (<span x-text="draftCount()">0</span> item)</h3>
                    <ul class="mt-1.5 flex flex-col gap-0.5">
                        <template x-for="line in draft" :key="line.key">
                            <li class="flex items-center gap-2 gpa-body text-ink-body">
                                <span class="font-medium text-ink" x-text="line.name"></span>
                                <span>&bull;</span>
                                <span x-text="line.qty + ' kg'"></span>
                                <span>&bull;</span>
                                <span class="font-medium text-ink" x-text="money(line.total)"></span>
                                <button type="button" @click="removeFromDraft(line.key)"
                                    class="ml-1 inline-flex items-center gap-1 text-ink-quiet transition-colors hover:text-danger"
                                    :aria-label="'Hapus ' + line.name + ' dari draft'">
                                    <x-gpa.icon name="x" class="h-3 w-3" />
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="flex shrink-0 flex-col items-start gap-2 lg:items-end">
                    <p class="text-[13px] text-ink-body">
                        Total <span class="font-semibold tabular-nums text-ink" x-text="draftWeight() + ' kg'"></span>
                        <span class="text-ink-quiet">&middot;</span>
                        <span class="font-semibold text-success-deep" x-text="money(draftTotal())"></span>
                    </p>
                    <button type="button" @click="submitDraft()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 text-[13px] font-semibold text-white transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="send" class="h-3.5 w-3.5" />
                        Kirim Draft PO ke Admin
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection
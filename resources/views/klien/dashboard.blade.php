@extends('layouts.klien')

@section('title', 'Dashboard Ringkasan & Operasional Klien B2B')

@php
    $rupiah = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    $kilogram = fn ($value) => number_format($value, 0, ',', '.') . ' kg';
    $creditBarWidth = max(0, min(100, $credit['percent'])) . '%';
@endphp

@section('content')
    <div x-data="clientDashboard(@js($reorder))" class="flex flex-col gap-5">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Kop halaman + ringkasan kontrak --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:p-6">
            <div class="min-w-0 flex flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="gpa-section-title text-ink">Dashboard Ringkasan &amp; Operasional Klien B2B</h1>
                    <span
                        class="rounded-full bg-accent px-2.5 py-0.5 gpa-micro-bold text-success-ink">
                        Kontrak Aktif
                    </span>
                </div>
                <dl class="flex flex-col gap-1 gpa-meta-lg text-ink-body">
                    <div class="flex flex-wrap items-center gap-x-2">
                        <dt class="text-ink-body">ID Kontrak:</dt>
                        <dd class="font-medium text-ink">{{ $contract['id'] }}</dd>
                        <span class="text-ink-body" aria-hidden="true">&bull;</span>
                        <dt class="text-ink-body">Gudang Dock Utama:</dt>
                        <dd class="font-medium uppercase text-ink">{{ $contract['dock'] }}</dd>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 text-ink-body">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <dt>Live Sync:</dt>
                        <dd class="font-medium text-ink-body" x-text="syncedAt">{{ $syncedAt->format('H:i:s') }} WIB</dd>
                    </div>
                </dl>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('klien.documents') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-surface-pill px-3.5 py-2 transition-colors hover:bg-surface-disabled">
                    <x-gpa.icon name="download" class="h-3 w-3 text-ink" />
                    <span class="gpa-meta-lg text-ink">[ Unduh Statement Bulanan ]</span>
                </a>
                <a href="{{ route('klien.catalog') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-ink px-4 py-2 shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                    <span class="gpa-meta-lg font-bold text-white">[ + Buat Pesanan Baru / PO ]</span>
                </a>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:min-h-[17.125rem]">
            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Plafon Kredit &amp; Tempo (Top 30D)</h2>
                        <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-ink">{{ $rupiah($credit['available']) }}</p>
                    <p class="gpa-body text-ink-body">
                        Sisa kuota dari total
                        <span class="font-medium text-ink">{{ $rupiah($credit['limit']) }}</span>
                    </p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <p class="gpa-micro-bold text-success-deep">Status: {{ $credit['status'] }}</p>
                            <p class="gpa-micro text-ink-body">{{ number_format($credit['percent'], 1, ',', '') }}% Tersedia</p>
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
                        <h2 class="gpa-micro text-ink-body">Pesanan In-Flight Pipeline</h2>
                        <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-0.5 text-ink">{{ $pipeline['count'] }} Pesanan Aktif</p>
                    <p class="gpa-meta-lg font-bold text-success-deep">Valuasi: {{ $rupiah($pipeline['value']) }}</p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <dl class="flex flex-col gap-1">
                        @foreach ($pipeline['stages'] as $stage)
                            <div class="flex items-start justify-between gap-2">
                                <dt class="gpa-micro text-ink-body">{{ $stage['label'] }}:</dt>
                                <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">{{ $stage['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Serapan Kuota Panen (Bulan Ini)</h2>
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
                    <div class="flex flex-col gap-1">
                        <div class="flex flex-wrap gap-x-6">
                            @foreach (array_slice($harvest['commodities'], 0, 2) as $commodity)
                                <p class="gpa-micro text-ink-body">
                                    {{ $commodity['name'] }}: {{ $kilogram($commodity['weight']) }}
                                </p>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap gap-x-6">
                            @foreach (array_slice($harvest['commodities'], 2) as $commodity)
                                <p class="gpa-micro text-ink-body">
                                    {{ $commodity['name'] }}: {{ $kilogram($commodity['weight']) }}
                                </p>
                            @endforeach
                        </div>
                        <div class="mt-0.5 flex items-start justify-between gap-2 border-t border-line-soft/40 pt-1.5">
                            <p class="gpa-micro font-semibold text-ink">Deviasi Timbang Riil:</p>
                            <p class="gpa-micro font-bold text-success-deep">
                                {{ number_format($harvest['deviation'], 1, ',', '') }}%
                                (&lt; {{ number_format($harvest['deviation_tolerance'], 1, ',', '') }}% Toleransi Aman)
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Kepatuhan SLA &amp; Mutu Cold-Chain</h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-success-deep">{{ number_format($sla['otif'], 1, ',', '') }}%</p>
                    <p class="gpa-body text-ink-body">On-Time In-Full Delivery (OTIF)</p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <dl class="flex flex-col gap-1">
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Insiden Kritis Cold-Chain:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">
                                {{ $sla['incidents'] }} Insiden
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Durasi Bongkar Dock:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">
                                {{ $sla['unload_minutes'] }} Menit (Avg)
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">QC Reject Rate Lapangan:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-success-deep">
                                {{ number_format($sla['reject_rate'], 2, ',', '') }}% (Target &lt;&lt; 1%)
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
                        <span class="rounded bg-warning px-2 py-0.5 gpa-micro-bold text-warning-soft">
                            {{ $invoice['due'] }}
                        </span>
                    </div>
                    <p class="gpa-body text-ink-body">{{ $invoice['body'] }}</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('klien.documents') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-1.5 transition-colors hover:bg-surface-disabled hover:text-ink">
                    <x-gpa.icon name="invoice" class="h-2.5 w-2.5" />
                    <span class="gpa-meta-lg">[ Unduh Faktur PDF ]</span>
                </a>
                <a href="{{ route('klien.payment-proof') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-3.5 py-1.5 shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="upload" class="h-3 w-3.5 text-white" />
                    <span class="gpa-meta-lg font-bold text-white">[ Unggah Bukti Transfer Manual ]</span>
                </a>
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
                        <x-gpa.icon name="thermometer" class="h-3.5 w-3.5 text-accent" />
                        <div class="flex flex-col">
                            <span class="gpa-meta-lg text-accent">Telemetri Chiller:</span>
                            <span class="flex items-baseline gap-1.5">
                                <span class="text-xs font-bold leading-4 text-accent">{{ $shipment['temperature'] }}</span>
                                <span class="gpa-meta-lg font-bold text-accent">[{{ strtoupper($shipment['temperature_state']) }}]</span>
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-col rounded-lg bg-surface-pill px-3 py-1.5">
                        <span class="gpa-meta-lg text-ink">Estimasi Tiba:</span>
                        <span class="flex items-baseline gap-1.5">
                            <span class="text-xs font-bold leading-4 text-ink">{{ $shipment['eta'] }}</span>
                            <span class="text-[11px] font-bold leading-4 text-ink">({{ $shipment['eta_note'] }})</span>
                        </span>
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
                            'flex items-center gap-1.5 gpa-micro-bold',
                            'text-ink' => $isActive,
                            'text-success-deep' => ! $isActive && ! $isPending,
                            'text-ink-body' => $isPending,
                        ])>
                            @if ($isActive)
                                <span class="h-1.5 w-1.5 rounded-full bg-ink" aria-hidden="true"></span>
                            @endif
                            {{ $step['phase'] }} &bull; {{ $step['status'] }}
                        </p>

                        <p class="pt-0.5 text-sm font-bold leading-5 {{ $textColor }}">{{ $step['title'] }}</p>
                        <p class="gpa-body {{ $textColor }} {{ $isActive ? 'font-semibold' : 'text-ink-body' }}">
                            {{ $step['detail'] }}
                        </p>
                        <p class="pt-1 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] {{ $isActive ? 'text-success-deep' : 'text-ink-quiet' }}">
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
                    <h2 class="gpa-section-title text-ink">Daftar Pesanan Berjalan (In-Flight Orders)</h2>
                    <p class="gpa-body text-ink-body">
                        Monitoring status pemenuhan komoditas secara real-time dari ladang panen ke dock Anda.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="filter-status" class="gpa-micro text-ink-body">Filter Status:</label>
                    <select id="filter-status" x-model="statusFilter"
                        class="gpa-control h-auto w-auto min-w-[13rem] rounded-lg border-line-soft bg-canvas py-1.5 pr-8 text-xs">
                        <template x-for="option in statusOptions(@js($orders))" :key="option.value">
                            <option :value="option.value" x-text="option.label"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[60rem] border-collapse">
                    <caption class="sr-only">Daftar pesanan berjalan beserta status pemenuhan komoditas</caption>
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-shell">
                            <th scope="col" class="px-4 py-3.5 text-left gpa-micro-bold text-ink-body">No PO / Order</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Tanggal PO</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Rincian Komoditas &amp; Estimasi</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Berat Riil (Netto Sah)</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Supir &amp; No SJ</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Status Pesanan</th>
                            <th scope="col" class="px-4 py-3 text-right gpa-micro-bold text-ink-body">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in filteredOrders(@js($orders))" :key="order.po">
                            <tr class="border-b border-line-soft/60 align-top">
                                <td class="px-4 py-4 text-sm font-bold leading-5 text-ink">
                                    <span x-text="order.po"></span>
                                </td>
                                <td class="px-4 py-4 gpa-meta text-ink-body"><span x-text="order.date"></span></td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm font-medium leading-5 text-ink"><span x-text="order.commodities"></span></p>
                                    <p class="gpa-body text-ink-body"><span x-text="order.packaging"></span></p>
                                </td>
                                <td class="px-4 py-4">
                                    <template x-if="order.weight">
                                        <div class="flex flex-col">
                                            <p class="text-sm font-bold leading-5 text-ink">
                                                <span x-text="order.weight"></span>
                                                <template x-if="order.weight_note">
                                                    <span class="font-normal text-ink-body" x-text="order.weight_note"></span>
                                                </template>
                                            </p>
                                            <p class="gpa-micro text-success-deep"><span x-text="order.scale_note"></span></p>
                                        </div>
                                    </template>
                                    <template x-if="! order.weight && order.weight_note">
                                        <span class="rounded bg-surface-pill px-2 py-0.5 gpa-micro text-ink-body"
                                            x-text="order.weight_note"></span>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5">
                                    <template x-if="order.driver">
                                        <div class="flex flex-col">
                                            <p class="gpa-body font-medium text-ink"><span x-text="order.driver"></span></p>
                                            <template x-if="order.sj">
                                                <p class="text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                                    <span x-text="order.sj"></span>
                                                </p>
                                            </template>
                                            <template x-if="order.driver_note">
                                                <p class="gpa-micro font-semibold uppercase leading-3 text-ink-quiet">
                                                    <span x-text="order.driver_note"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="! order.driver">
                                        <p class="gpa-body italic text-ink-body"><span x-text="order.driver_note"></span></p>
                                    </template>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 gpa-micro-bold"
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
                                    <div class="flex flex-col items-end gap-1">
                                        <a :href="order.url || '{{ route('klien.orders.index') }}'"
                                            class="rounded bg-ink px-2.5 py-1 gpa-micro-bold text-white transition-colors hover:bg-brand-deep">
                                            Detail PO
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p x-cloak x-show="filteredOrders(@js($orders)).length === 0"
                class="px-6 py-10 text-center gpa-body text-ink-body">
                Tidak ada pesanan pada status terpilih.
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
                    class="w-fit rounded-lg border border-line-soft bg-surface-shell px-3 py-1 gpa-micro-bold text-success-deep">
                    Harga Kontrak Dikunci: Berlisensi GPA
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($reorder as $item)
                    <article class="flex flex-col justify-between gap-4 rounded-xl border border-line-soft bg-surface p-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="gpa-micro-bold text-success-deep">{{ $item['grade'] }}</p>
                                <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-ink-quiet" />
                            </div>
                            <h3 class="text-base font-bold leading-6 text-ink">{{ $item['name'] }}</h3>
                            <p class="flex items-baseline gap-1">
                                <span class="font-inter text-lg font-bold leading-6 text-ink">{{ $rupiah($item['price']) }}</span>
                                <span class="font-inter text-xs text-ink-body">/ kg</span>
                            </p>
                            <p class="flex items-baseline gap-1 gpa-micro text-ink-body">
                                <span>Sisa Alokasi Kontrak:</span>
                                <span class="font-bold text-ink">{{ number_format($item['remaining'], 0, ',', '.') }} kg</span>
                            </p>
                        </div>

                        <div class="border-t border-line-soft/60 pt-3">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="gpa-micro text-ink-body">Jumlah (Kg):</span>
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
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-ink px-3 py-2 transition-colors hover:bg-brand-deep">
                                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                                    <span class="gpa-meta-lg font-bold text-white">Tambah ke Draft PO</span>
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
                    <p class="gpa-meta-lg text-ink-body">
                        Total <span class="font-bold text-ink" x-text="draftWeight() + ' kg'"></span>
                        &bull;
                        <span class="font-bold text-success-deep" x-text="money(draftTotal())"></span>
                    </p>
                    <a href="{{ route('klien.cart') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="send" class="h-3.5 w-3.5 text-white" />
                        <span class="gpa-meta-lg font-bold text-white">Buka Keranjang PO &amp; Checkout</span>
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection

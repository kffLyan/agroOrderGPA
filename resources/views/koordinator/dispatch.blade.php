@extends('layouts.coordinator')

@section('title', 'Surat Jalan & Dispatch Armada Logistik')

@section('content')
    @php
        $standbyCount = count(array_filter($manifestRows, fn($r) => $r['dock_state'] === 'standby'));
        $movingCount = count(array_filter($manifestRows, fn($r) => $r['dock_state'] === 'moving'));
        $readyDocs = count($queueItems);
    @endphp

    <div class="space-y-6"
        x-data="coordinatorDispatch(@js($queueItems), @js($manifestRows), [
            { key: 'all', label: 'SEMUA DOKUMEN ({{ count($manifestRows) }})' },
            { key: 'standby', label: 'STANDBY DOCK ({{ $standbyCount }})' },
            { key: 'moving', label: 'SEDANG BERJALAN ({{ $movingCount }})' }
        ], {{ $readyDocs }})">
        {{-- Page header --}}
        <section class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="gpa-eyebrow flex items-center gap-2">
                    <x-gpa.icon name="truck" class="h-4 w-4 text-success-deep" />
                    Pengiriman &amp; Logistik
                </p>
                <h1 class="mt-2 font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                    Penerbitan Surat Jalan &amp;<br class="hidden sm:block">
                    Pengiriman Sayur ke Klien
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-5 text-ink-body">
                    Pencetakan surat jalan resmi berdasarkan hasil timbangan bersih dan penugasan supir pengantar.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="act('log')"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg px-3 transition-colors bg-surface text-ink outline outline-1 outline-line-board shadow-sub hover:bg-surface-muted">
                    <x-gpa.icon name="file-text" class="h-3.5 w-3.5 shrink-0 text-ink" />
                    <span class="gpa-meta-lg font-bold">Riwayat Surat Jalan</span>
                </button>
                <button type="button" @click="act('thermal')"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg px-3 transition-colors bg-ink text-white outline outline-1 outline-brand-line shadow-sub hover:bg-ink-muted">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-accent" />
                    <span class="gpa-meta-lg font-bold">
                        Cetak Surat Jalan <span x-text="`(${thermal})`">({{ $readyDocs }})</span>
                    </span>
                </button>
            </div>
        </section>

        {{-- Rule 05 integrity banner --}}
        <section
            class="flex flex-col gap-4 rounded-xl border border-l-4 border-warning bg-warning-cream p-4 shadow-sub lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="shrink-0 rounded-lg bg-warning p-1.5">
                    <x-gpa.icon name="alert-triangle" class="h-4 w-4 text-warning-soft" />
                </span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="gpa-micro-bold text-warning-deep">ATURAN RESMI PENGIRIMAN</h2>
                        <span
                            class="rounded bg-warning/10 px-1.5 py-0.5 gpa-micro-bold text-warning outline outline-1 outline-warning/30">
                            WAJIB TIMBANG BERSIH
                        </span>
                    </div>
                    <p class="mt-1.5 max-w-4xl text-xs font-medium leading-4 text-ink">
                        Surat jalan hanya dapat dicetak setelah berat bersih sayur selesai ditimbang dan dikunci di sistem. Ini memastikan jumlah sayur yang dikirim sama dengan data pesanan.
                    </p>
                </div>
            </div>

            <div class="shrink-0 rounded bg-surface px-3 py-1 outline outline-1 outline-line-hair">
                <p class="gpa-micro-bold text-warning">STANDAR:</p>
                <p class="gpa-micro-bold text-warning">MUTU TERJAMIN</p>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- KPI 1: Ready docs --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">SURAT JALAN SIAP CETAK</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent/40">
                        <x-gpa.icon name="file-text" class="block h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="docCount()">{{ $readyDocs }}</span>
                    <span class="gpa-meta-lg font-bold text-ink-body">Dokumen</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="gpa-note text-ink-quiet">Total Berat Bersih:</p>
                    <p class="gpa-meta-lg font-bold text-success-deep" x-text="`${kg(tonase())} KG`">{{ number_format($totalTonase, 2, ',', '.') }} KG</p>
                </div>
            </article>

            {{-- KPI 2: Fleet --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">ARMADA MOBIL SIAP JALAN</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-shell">
                        <x-gpa.icon name="truck" class="block h-4 w-4 text-ink" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink">{{ $fleetCount }}</span>
                    <span class="gpa-meta-lg font-bold text-ink-body">Mobil Ready</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded bg-surface-shell px-1.5 py-0.5 gpa-note text-ink-body outline outline-1 outline-line-board/40">
                            Blind Van Chiller
                        </span>
                        <span class="rounded bg-surface-shell px-1.5 py-0.5 gpa-note text-ink-body outline outline-1 outline-line-board/40">
                            Engkel Box Pendingin
                        </span>
                    </div>
                </div>
            </article>

            {{-- KPI 3: Departure --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">JADWAL BERANGKAT AWAL</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-warning-soft/30">
                        <x-gpa.icon name="clock" class="block h-4 w-4 text-warning-deep" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink">04:30</span>
                    <span class="gpa-meta-lg font-bold text-success-deep">WIB</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="gpa-note text-ink-quiet">Waktu Kirim:</p>
                    <span class="rounded bg-accent/50 px-1.5 py-0.5 gpa-micro-bold text-ink">Pagi Hari (Segar)</span>
                </div>
            </article>

            {{-- KPI 4: Scale readiness --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">STATUS KESIAPAN SURAT JALAN</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent">
                        <x-gpa.icon name="badge-check" class="block h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-success-deep">100%</span>
                    <span class="gpa-meta-lg font-bold text-ink">Siap Cetak</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="gpa-note text-ink-quiet">Status Mesin Cetak:</p>
                    <p class="inline-flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                        <span class="h-1.5 w-1.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        TERHUBUNG &amp; SIAP
                    </p>
                </div>
            </article>
        </section>

        {{-- Locked weighing print queue --}}
        <section class="space-y-4">
            <header class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-brand p-1.5">
                        <x-gpa.icon name="printer" class="h-4 w-4 text-accent" />
                    </span>
                    <h2 class="gpa-section-title text-ink">Antrean Penerbitan Surat Jalan</h2>
                </div>
                <p class="gpa-note text-ink-quiet" x-text="queueMeta()">{{ count($queueItems) }} ORDER MENUNGGU DOKUMEN CETAK</p>
            </header>

            <div class="grid gap-4 2xl:grid-cols-2">
                @forelse ($queueItems as $item)
                    <article class="gpa-panel flex flex-col justify-between gap-4 p-4">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-4 border-b border-line-soft/60 pb-3">
                                <div class="min-w-0">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border border-success-deep bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                                        {{ $item['status_chip'] }}
                                    </span>
                                    <p class="mt-2 font-sans text-lg font-bold leading-6 text-ink">{{ $item['po'] }}</p>
                                    <p class="mt-0.5 text-xs leading-4 text-ink-body">{{ $item['customer'] }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="gpa-note text-ink-quiet">NETTO AKTUAL SAH</p>
                                    <p class="mt-0.5">
                                        <span class="font-mono text-sm font-bold text-ink">{{ number_format($item['net'], 2, '.', ',') }}</span>
                                        <span class="gpa-meta-lg font-bold text-ink">KG</span>
                                    </p>
                                    <p class="gpa-note mt-0.5 text-success-deep">
                                        Tara: {{ number_format($item['tara'], 2, '.', ',') }} KG | Gross:
                                        {{ number_format($item['gross'], 2, '.', ',') }} KG
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/30 sm:flex-row sm:gap-8">
                                <div class="min-w-0">
                                    <p class="gpa-micro-bold text-ink-quiet">SUPIR BERTUGAS</p>
                                    <p class="mt-0.5 text-xs font-semibold text-ink">{{ $item['driver'] }}</p>
                                    <p class="gpa-note mt-0.5 text-ink-quiet">{{ $item['driver_id'] }}</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="gpa-micro-bold text-ink-quiet">ALOKASI KENDARAAN</p>
                                    <p class="mt-0.5 text-xs font-semibold text-ink">{{ $item['vehicle'] }}</p>
                                    <p class="gpa-meta-lg mt-0.5 font-bold text-success-deep">{{ $item['plate'] }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                <p class="text-xs leading-4 text-ink-body">{{ $item['commodity'] }}</p>
                                <p class="gpa-note text-ink-quiet">{{ $item['dock'] }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft/60 pt-4">
                            <p class="gpa-note text-ink-quiet">LOCK STAMP: {{ $item['lock_stamp'] }}</p>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('prints.surat-jalan') }}" target="_blank"
                                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-surface px-4 outline outline-1 outline-line-board transition-colors hover:bg-surface-muted">
                                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-ink" />
                                    <span class="gpa-meta-lg font-bold text-ink">Cetak SJ</span>
                                </a>
                                <button type="button" @click="issue(@js($item['sj']))"
                                    :disabled="isIssued(@js($item['sj'])) || !canIssue(@js($item['sj']))"
                                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-accent px-4 shadow-sub outline outline-1 outline-success-deep transition-colors hover:bg-accent-deep disabled:cursor-not-allowed disabled:bg-surface-pill disabled:text-ink-quiet disabled:outline-line-board">
                                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-ink" />
                                    <span class="gpa-meta-lg font-bold text-ink"
                                        x-text="isIssued(@js($item['sj'])) ? @js($item['issued_label']) : @js($item['issue_label'])">{{ $item['issue_label'] }}</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-line-board/70 bg-surface-shell/40 p-8 text-center">
                        <p class="text-sm font-semibold text-ink">Tidak ada antrean penerbitan surat jalan saat ini.</p>
                        <p class="gpa-note mt-1 text-ink-quiet">Semua surat jalan telah diproses atau belum ada pesanan yang siap kirim.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Dispatch manifest --}}
        <section class="gpa-panel p-4">
            <header class="flex flex-wrap items-start justify-between gap-4 border-b border-line-soft/60 pb-3">
                <div class="min-w-0">
                    <h2 class="gpa-section-title flex items-center gap-2 text-ink">
                        <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        Daftar Pengiriman Armada Hari Ini
                    </h2>
                    <p class="mt-1 max-w-3xl text-xs leading-4 text-ink-body">
                        Pemantauan status pengiriman sayur, nomor surat jalan, dan armada supir pengantar.
                    </p>
                </div>

                <div class="relative shrink-0">
                    <span class="gpa-micro-bold text-ink-quiet">FILTER STATUS:</span>
                    <button type="button" @click="filterOpen = !filterOpen" @click.outside="filterOpen = false"
                        :aria-expanded="filterOpen ? 'true' : 'false'"
                        class="mt-1 flex h-8 w-full items-center justify-between gap-2 rounded border border-line-board bg-surface-shell px-2 outline outline-1 outline-line-board/60 transition-colors hover:bg-surface-muted sm:w-56">
                        <span class="gpa-meta-lg font-bold text-ink" x-text="filterLabel()">SEMUA DOKUMEN ({{ count($manifestRows) }})</span>
                        <x-gpa.icon name="chevron-down" class="h-3 w-3 shrink-0 text-ink-body" />
                    </button>

                    <div x-cloak x-show="filterOpen" x-transition
                        class="absolute right-0 z-20 mt-1 w-56 rounded-lg border border-line-board bg-surface p-1 shadow-pop">
                        <button type="button" @click="setFilter('all')"
                            class="flex w-full items-center justify-between gap-2 rounded px-2 py-1.5 text-left transition-colors"
                            :class="filter === 'all' ? 'bg-ink gpa-meta-lg font-bold text-accent' : 'gpa-meta-lg font-medium text-ink-body hover:bg-surface-shell'">
                            <span x-text="filterLabel('all')">SEMUA DOKUMEN ({{ count($manifestRows) }})</span>
                        </button>
                        <button type="button" @click="setFilter('standby')"
                            class="flex w-full items-center justify-between gap-2 rounded px-2 py-1.5 text-left transition-colors"
                            :class="filter === 'standby' ? 'bg-ink gpa-meta-lg font-bold text-accent' : 'gpa-meta-lg font-medium text-ink-body hover:bg-surface-shell'">
                            <span x-text="filterLabel('standby')">STANDBY DOCK ({{ $standbyCount }})</span>
                        </button>
                        <button type="button" @click="setFilter('moving')"
                            class="flex w-full items-center justify-between gap-2 rounded px-2 py-1.5 text-left transition-colors"
                            :class="filter === 'moving' ? 'bg-ink gpa-meta-lg font-bold text-accent' : 'gpa-meta-lg font-medium text-ink-body hover:bg-surface-shell'">
                            <span x-text="filterLabel('moving')">SEDANG BERJALAN ({{ $movingCount }})</span>
                        </button>
                    </div>
                </div>
            </header>

            <div class="gpa-scroll-x -mx-4 overflow-x-auto">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">Daftar Pengiriman Armada Hari Ini</caption>
                    <thead>
                        <tr class="border-b border-line-hair bg-surface-shell/70">
                            <th scope="col" class="px-3 py-4 gpa-micro-bold text-ink-quiet text-left">NO. SURAT JALAN</th>
                            <th scope="col" class="px-3 py-4 gpa-micro-bold text-ink-quiet text-left">SUPIR &amp; ARMADA</th>
                            <th scope="col" class="px-3 py-4 gpa-micro-bold text-ink-quiet text-left">BERAT BERSIH</th>
                            <th scope="col" class="px-3 py-4 gpa-micro-bold text-ink-quiet text-left">JADWAL BERANGKAT</th>
                            <th scope="col" class="px-3 py-4 gpa-micro-bold text-ink-quiet text-left">STATUS BERKAS / QR</th>
                            <th scope="col" class="px-3 py-4 gpa-micro-bold text-ink-quiet text-left">STATUS PARKIR DOCK</th>
                            <th scope="col" class="px-3 py-4 gpa-micro-bold text-ink-quiet text-right">TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($manifestRows as $row)
                            @php($rowId = \Illuminate\Support\Js::from($row['id']))
                            <tr @class([
                                'border-b border-line-hair/70',
                                'bg-accent/10' => $row['row_tone'] === 'highlight',
                            ]) x-show="isVisible({{ $rowId }})">
                                <th scope="row" class="px-3 py-4 text-left align-top">
                                    <span class="gpa-meta font-bold text-ink">{{ $row['id'] }}</span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet">REF: {{ $row['ref'] }}</span>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <span class="block text-xs font-semibold text-ink">{{ $row['driver'] }}</span>
                                    <span class="mt-0.5 block gpa-note text-success-deep">{{ $row['plate'] }} ({{ $row['vehicle'] }})</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span @class([
                                        'gpa-meta-lg font-bold',
                                        'text-success-deep' => $row['net_tone'] === 'success',
                                        'text-ink' => $row['net_tone'] === 'ink',
                                    ])>{{ number_format($row['net'], 2, '.', ',') }} KG</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span class="block gpa-meta font-medium text-ink">{{ $row['time'] }}</span>
                                    <span @class([
                                        'mt-0.5 block gpa-note',
                                        'text-success-deep' => $row['slot_tone'] === 'success',
                                        'text-ink' => $row['slot_tone'] === 'ink',
                                    ])>{{ $row['slot'] }}</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span @class([
                                            'rounded px-1.5 py-0.5 gpa-micro-bold',
                                            'bg-accent text-success-ink outline outline-1 outline-success/40' => $row['copies_tone'] === 'ok',
                                            'bg-surface-pill text-ink-quiet' => $row['copies_tone'] === 'muted',
                                            'bg-accent/50 text-success-deep outline outline-1 outline-success/40' => $row['copies_tone'] === 'active',
                                        ])>{{ $row['copies'] }}</span>
                                        <span @class([
                                            'inline-flex items-center gap-1 rounded px-1.5 py-0.5 gpa-micro-bold',
                                            'bg-surface-pill text-ink outline outline-1 outline-line-board/40' => $row['qr_icon'],
                                            'bg-accent/50 text-success-deep' => ! $row['qr_icon'],
                                        ])>
                                            @if ($row['qr_icon'])
                                                <x-gpa.icon name="qrcode" class="h-3 w-3 shrink-0" />
                                            @endif
                                            {{ $row['qr'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 outline outline-1"
                                        :class="state({{ $rowId }}) === 'moving'
                                            ? 'bg-brand text-accent outline-brand/40'
                                            : 'bg-accent/30 text-ink outline-success/30'">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="state({{ $rowId }}) === 'moving' ? 'bg-accent' : 'bg-success-deep'"
                                            aria-hidden="true"></span>
                                        <span class="gpa-note" x-text="dockText({{ $rowId }})">{{ $row['dock'] }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <button type="button" @click="actRow({{ $rowId }})"
                                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg px-3 transition-colors"
                                        :class="actionVariant({{ $rowId }})">
                                        <x-gpa.icon name="send"
                                            x-show="actionOf({{ $rowId }}) === 'handover'"
                                            ::class="actionIconTone({{ $rowId }})"
                                            @class([
                                                'h-3.5 w-3.5 shrink-0',
                                                'x-cloak' => 'handover' !== $row['action'],
                                            ]) />
                                        <x-gpa.icon name="printer"
                                            x-show="actionOf({{ $rowId }}) === 'reprint'"
                                            ::class="actionIconTone({{ $rowId }})"
                                            @class([
                                                'h-3.5 w-3.5 shrink-0',
                                                'x-cloak' => 'reprint' !== $row['action'],
                                            ]) />
                                        <x-gpa.icon name="map-pin"
                                            x-show="actionOf({{ $rowId }}) === 'track'"
                                            ::class="actionIconTone({{ $rowId }})"
                                            @class([
                                                'h-3.5 w-3.5 shrink-0',
                                                'x-cloak' => 'track' !== $row['action'],
                                            ]) />
                                        <span class="gpa-meta-lg font-bold"
                                            x-text="actionLabel({{ $rowId }})">{{ $row['action_label'] }}</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-sm font-medium text-ink-quiet">
                                    Tidak ada data pengiriman armada untuk ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Batch operations --}}
        <section class="sticky bottom-4 z-30 gpa-panel flex flex-wrap items-center justify-between gap-4 p-4 shadow-2xl">
            <div class="flex items-center gap-3">
                <span
                    class="flex h-10 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-shell outline outline-1 outline-line-board/40">
                    <x-gpa.icon name="clipboard" class="h-4 w-4 text-ink" />
                </span>
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Cetak &amp; Ekspor Bersama</h2>
                    <p class="mt-1 max-w-xl text-xs leading-4 text-ink-body">Unduh data manifest pengiriman harian atau cetak semua surat jalan sekaligus.</p>
                </div>
            </div>

            <div class="grid w-full gap-2 sm:w-auto sm:grid-cols-2">
                <button type="button" @click="batch('export')"
                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg px-4 transition-colors bg-surface text-ink outline outline-1 outline-line-board/60 shadow-sub hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0 text-ink" />
                    <span class="gpa-meta-lg font-bold">Unduh Laporan Hari Ini (.csv)</span>
                </button>
                <button type="button" @click="batch('audit-gate')"
                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg px-4 transition-colors bg-ink text-white outline outline-1 outline-brand-line shadow-sub hover:bg-ink-muted">
                    <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0 text-accent" />
                    <span class="gpa-meta-lg font-bold">Cek Gerbang Keluar Gudang</span>
                </button>
                <button type="button" @click="batch('print-all')"
                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg px-4 transition-colors bg-accent text-ink outline outline-1 outline-success-deep shadow-pop hover:bg-accent-deep sm:col-span-2">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-ink" />
                    <span class="gpa-meta-lg font-bold">Cetak Semua Surat Jalan</span>
                </button>
            </div>
        </section>
    </div>
@endsection

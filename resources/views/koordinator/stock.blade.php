@extends('layouts.coordinator')

@section('title', 'Manajemen Stok Gudang')

@section('content')
    @php
        $suppliersList = $distinctSuppliers ?? [];
        $logTotal = count($logItems ?? []);
        $intakeFields = [
            ['key' => 'vendor', 'type' => 'select', 'value' => $suppliersList[0] ?? 'Petani Binaan'],
            ['key' => 'commodity', 'type' => 'select', 'value' => ($commodities ?? [])[0]['key'] ?? 'selada-romaine'],
            ['key' => 'grade', 'type' => 'select', 'value' => 'Grade A (Premium)'],
            ['key' => 'category', 'type' => 'select', 'value' => 'Panen Binaan Inti'],
            ['key' => 'quantity', 'type' => 'number', 'value' => 500],
            ['key' => 'date', 'type' => 'date', 'value' => now()->toDateString()],
            ['key' => 'notes', 'type' => 'text', 'value' => 'Lolos sortir manual, suhu pra-dingin cold room terjaga.'],
        ];
    @endphp

    <div class="space-y-6" x-data="coordinatorStock(
        @js($commodities ?? []),
        [
            { key: 'all', label: 'Semua ({{ count($commodities ?? []) }})' },
            { key: 'daun', label: 'Sayuran Daun' },
            { key: 'buah', label: 'Buah & Solanaceae' },
            { key: 'cruciferae', label: 'Cruciferae' }
        ],
        @js($intakeFields),
        @js($logItems ?? []),
        {{ $logTotal }}
    )">

        {{-- Page header --}}
        <section class="gpa-panel flex flex-wrap items-center justify-between gap-4 p-6">
            <div class="min-w-0">
                <p class="gpa-eyebrow flex items-center gap-2">
                    <x-gpa.icon name="package" class="h-4 w-4 text-success-deep" />
                    Sub-06 // Manajemen Stok
                </p>
                <h1 class="mt-2 font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                    Manajemen Stok Panen &amp; Alokasi<br class="hidden sm:block">
                    Buffer Stock Gudang
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-5 text-ink-body">
                    Monitoring ketersediaan pasokan panen petani binaan dan pasokan penyangga (buffer stock) mitra luar untuk pencegahan overselling (PRD Section 5.1, 7.3, Rule 02 &amp; 03).
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="act('export')"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg px-3 transition-colors bg-surface-shell text-ink outline outline-1 outline-line-board hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0 text-ink" />
                    <span class="gpa-meta-lg font-bold">EXPORT LEDGER .CSV</span>
                </button>
                <button type="button" @click="act('lock-all')"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg px-3 transition-colors bg-ink text-accent shadow-sub hover:bg-ink-muted">
                    <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0 text-accent" />
                    <span class="gpa-meta-lg font-bold">KUNCI ALOKASI PASOKAN</span>
                </button>
            </div>
        </section>

        {{-- Quality & stock integrity protocol --}}
        <section
            class="flex flex-col gap-4 rounded-xl border border-l-4 border-success-deep bg-accent/30 p-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <x-gpa.icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-success-deep" />
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="gpa-micro-bold text-success-deep">PROTOKOL PENJAGA MUTU &amp; INTEGRITAS STOK (PRD SECTION 7.3 &amp; 8.2)</h2>
                        <span class="rounded bg-ink px-1.5 py-0.5 gpa-micro-bold text-accent">HARD-LOCK ENABLED</span>
                    </div>
                    <p class="mt-1.5 max-w-4xl text-xs leading-5 text-ink-body">
                        Setiap PO yang diterbitkan otomatis memotong kuota komoditas secara real-time. Komitmen stok binaan terkunci 100%. Apabila alokasi binaan kurang dari permintaan PO, sistem mewajibkan injeksi buffer mitra tervalidasi dengan selisih susut bobot toleransi maks ≤ 2.5%.
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-left lg:text-right">
                <p class="gpa-micro-bold text-ink-body">VALIDASI INTEGRITAS</p>
                <p class="mt-1 font-mono text-lg font-bold leading-6 text-success-deep">0 ANOMALI OVERSELL</p>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- KPI 1: Total Stok Tersedia --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">TOTAL STOK TERSEDIA</h2>
                    <span class="shrink-0 rounded p-1.5 bg-surface-shell">
                        <x-gpa.icon name="package" class="block h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="kg(totalStock())">14.850</span>
                    <span class="gpa-meta-lg font-bold text-ink-body">KG</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="min-w-0">
                        <span class="block gpa-note text-success-deep"
                            x-text="`Binaan: ${kg(builtianStock())} KG (${percent(builtianPercent(), 1)})`">
                            Binaan: {{ number_format($totalBinaan ?? 0, 0, ',', '.') }} KG ({{ ($totalStock ?? 0) > 0 ? round((($totalBinaan ?? 0) / $totalStock) * 100, 1) : 0 }}%)
                        </span>
                        <span class="mt-0.5 block gpa-note text-ink-body"
                            x-text="`Buffer: ${kg(bufferStock())} KG`">
                            Buffer: {{ number_format($totalBuffer ?? 0, 0, ',', '.') }} KG
                        </span>
                    </p>
                </div>
                <div class="flex h-1.5 w-full gap-0.5 overflow-hidden rounded-full bg-surface-pill">
                    <span class="block h-full rounded-full bg-success-deep"
                        style="width: {{ round(($totalBinaan ?? 0) / max(1, $totalStock ?? 1) * 100, 1) }}%"></span>
                    <span class="block h-full rounded-full bg-brand"
                        style="width: {{ round(($totalBuffer ?? 0) / max(1, $totalStock ?? 1) * 100, 1) }}%"></span>
                </div>
            </article>

            {{-- KPI 2: Stok Terpesan / PO --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">STOK TERPESAN / PO</h2>
                    <span class="shrink-0 rounded p-1.5 bg-surface-shell">
                        <x-gpa.icon name="file-text" class="block h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="kg(reserved())">8.900</span>
                    <span class="gpa-meta-lg font-bold text-ink-body">KG</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="min-w-0">
                        <span class="block gpa-note text-ink">{{ $activePoCount ?? 0 }} Kontrak PO Aktif</span>
                        <span class="mt-0.5 block gpa-note text-ink-quiet">B2B &amp; Retail Commitment</span>
                    </p>
                    <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink">100% TERIKAT</span>
                </div>
                <div class="flex h-1.5 w-full gap-0.5 overflow-hidden rounded-full bg-surface-pill">
                    <span class="block h-full rounded-full bg-ink"
                        style="width: {{ round(($totalReserved ?? 0) / max(1, $totalStock ?? 1) * 100, 1) }}%"></span>
                </div>
            </article>

            {{-- KPI 3: Sisa Bebas --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">SISA BEBAS (FREE STOCK)</h2>
                    <span class="shrink-0 rounded p-1.5 bg-accent">
                        <x-gpa.icon name="gauge" class="block h-4 w-4 text-ink" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-success-deep" x-text="`+${kg(freeStock())}`">+5.950</span>
                    <span class="gpa-meta-lg font-bold text-success-deep">KG</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="min-w-0">
                        <span class="block gpa-note text-ink">Ready to Promise (RTP)</span>
                        <span class="mt-0.5 block gpa-note text-success-deep">{{ ($freeStock ?? 0) >= 0 ? 'AMAN DARI DEFISIT' : 'PERINGATAN DEFISIT' }}</span>
                    </p>
                </div>
                <div class="flex h-1.5 w-full gap-0.5 overflow-hidden rounded-full bg-surface-pill">
                    <span class="block h-full rounded-full bg-accent-edge"
                        style="width: {{ round(max(0, $freeStock ?? 0) / max(1, $totalStock ?? 1) * 100, 1) }}%"></span>
                </div>
            </article>

            {{-- KPI 4: Hub Pasokan Aktif --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">HUB PASOKAN AKTIF</h2>
                    <span class="shrink-0 rounded p-1.5 bg-surface-shell">
                        <x-gpa.icon name="truck" class="block h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink">3</span>
                    <span class="gpa-meta-lg font-bold text-ink-body">LOKASI SENTRA</span>
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <p class="min-w-0">
                        <span class="block gpa-note text-ink">Cianjur, Lembang, Pangalengan</span>
                        <span class="mt-0.5 block gpa-note text-success-deep">100% ONLINE</span>
                    </p>
                </div>
                <div class="flex h-1.5 w-full gap-0.5 overflow-hidden rounded-full bg-surface-pill">
                    <span class="block h-full rounded-full bg-success-deep" style="width: 33.3%"></span>
                    <span class="block h-full rounded-full bg-success-deep" style="width: 33.3%"></span>
                    <span class="block h-full rounded-full bg-success-deep" style="width: 33.3%"></span>
                </div>
            </article>
        </section>

        {{-- Commodity filter --}}
        <section class="gpa-panel flex flex-wrap items-center justify-between gap-3 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="gpa-micro-bold text-ink-body">FILTER KOMODITAS:</span>
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="filter = 'all'"
                        :aria-pressed="filter === 'all' ? 'true' : 'false'"
                        :class="filter === 'all' ? 'bg-ink text-accent' : 'bg-surface-shell text-ink-body hover:bg-surface-muted'"
                        class="rounded px-2.5 py-1 transition-colors gpa-meta-lg font-medium">
                        Semua ({{ count($commodities ?? []) }})
                    </button>
                    <button type="button" @click="filter = 'daun'"
                        :aria-pressed="filter === 'daun' ? 'true' : 'false'"
                        :class="filter === 'daun' ? 'bg-ink text-accent' : 'bg-surface-shell text-ink-body hover:bg-surface-muted'"
                        class="rounded px-2.5 py-1 transition-colors gpa-meta-lg font-medium">
                        Sayuran Daun
                    </button>
                    <button type="button" @click="filter = 'buah'"
                        :aria-pressed="filter === 'buah' ? 'true' : 'false'"
                        :class="filter === 'buah' ? 'bg-ink text-accent' : 'bg-surface-shell text-ink-body hover:bg-surface-muted'"
                        class="rounded px-2.5 py-1 transition-colors gpa-meta-lg font-medium">
                        Buah &amp; Solanaceae
                    </button>
                    <button type="button" @click="filter = 'cruciferae'"
                        :aria-pressed="filter === 'cruciferae' ? 'true' : 'false'"
                        :class="filter === 'cruciferae' ? 'bg-ink text-accent' : 'bg-surface-shell text-ink-body hover:bg-surface-muted'"
                        class="rounded px-2.5 py-1 transition-colors gpa-meta-lg font-medium">
                        Cruciferae
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-success-deep"></span>
                    <span class="gpa-note text-ink">Binaan Terikat</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                    <span class="gpa-note text-ink">Buffer Siaga</span>
                </span>
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
                    <span class="rounded bg-accent p-1.5">
                        <x-gpa.icon name="clipboard" class="block h-4 w-4 text-ink" />
                    </span>
                    <div>
                        <h2 class="font-sans text-lg font-bold leading-6 tracking-[-0.01em] text-ink">Quick Intake Ledger</h2>
                        <p class="gpa-micro-bold mt-0.5 text-ink-quiet">PENCATATAN PANEN MASUK LAPANGAN</p>
                    </div>
                </div>
                <span class="rounded bg-ink px-1.5 py-0.5 gpa-micro-bold text-accent">POS-01 AUTO-CAPTURE</span>
            </header>

            <form class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3" @submit.prevent="submitIntake()">
                {{-- Field: Vendor --}}
                <div class="min-w-0">
                    <label class="gpa-label" for="intake-vendor">PETANI BINAAN / MITRA VENDOR *</label>
                    <select id="intake-vendor" class="gpa-control mt-1" x-model="form.vendor">
                        @foreach ($suppliersList as $supplier)
                            <option value="{{ $supplier }}">{{ $supplier }}</option>
                        @endforeach
                    </select>
                    <span class="gpa-hint mt-1">Kelompok Tani Binaan GPA / Mitra Buffer Resmi</span>
                </div>

                {{-- Field: Commodity --}}
                <div class="min-w-0">
                    <label class="gpa-label" for="intake-commodity">KOMODITAS INTI *</label>
                    <select id="intake-commodity" class="gpa-control mt-1" x-model="form.commodity">
                        @foreach ($products ?? [] as $product)
                            <option value="{{ $product->slug ?: \Illuminate\Support\Str::slug($product->name) }}">
                                {{ $product->name }} ({{ $product->grade ?: 'Grade A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Field: Grade --}}
                <div class="min-w-0">
                    <label class="gpa-label" for="intake-grade">GRADE MUTU</label>
                    <select id="intake-grade" class="gpa-control mt-1" x-model="form.grade">
                        <option value="Grade A+ (Super)">Grade A+ (Super)</option>
                        <option value="Grade A (Premium)">Grade A (Premium)</option>
                        <option value="Grade AA (Wajib Panas)">Grade AA (Wajib Panas)</option>
                        <option value="Grade B (Sortir)">Grade B (Sortir)</option>
                    </select>
                </div>

                {{-- Field: Category --}}
                <div class="min-w-0">
                    <label class="gpa-label" for="intake-category">KATEGORI PASOKAN</label>
                    <select id="intake-category" class="gpa-control mt-1" x-model="form.category">
                        <option value="Panen Binaan Inti">Panen Binaan Inti</option>
                        <option value="Injeksi Buffer Mitra">Injeksi Buffer Mitra</option>
                        <option value="Retur Sortir">Retur Sortir</option>
                    </select>
                </div>

                {{-- Field: Quantity --}}
                <div class="min-w-0">
                    <label class="gpa-label" for="intake-quantity">KUANTITAS (NETTO)</label>
                    <div class="mt-1 flex items-center gap-2">
                        <input id="intake-quantity" type="number" min="1" step="1" value="500"
                            class="gpa-control" x-model.number="form.quantity" />
                        <span class="gpa-meta-lg font-bold text-ink-body">KG</span>
                    </div>
                </div>

                {{-- Field: Date --}}
                <div class="min-w-0">
                    <label class="gpa-label" for="intake-date">TANGGAL TEBAS/PETIK</label>
                    <input id="intake-date" type="date" value="{{ now()->toDateString() }}"
                        class="gpa-control mt-1" x-model="form.date" />
                </div>

                {{-- Field: Notes --}}
                <div class="min-w-0 md:col-span-2 xl:col-span-3">
                    <label class="gpa-label" for="intake-notes">BRIX / MOISTURE / QC NOTES</label>
                    <input id="intake-notes" type="text" value="Lolos sortir manual, suhu pra-dingin cold room terjaga."
                        class="gpa-control mt-1" x-model="form.notes" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 md:col-span-2 xl:col-span-3">
                    <p class="inline-flex items-center gap-2 rounded-lg bg-surface-shell px-2.5 py-2 outline outline-1 outline-line-soft">
                        <x-gpa.icon name="scale" class="h-3.5 w-3.5 text-ink-body" />
                        <span class="gpa-meta-lg text-ink">Port Timbangan: COM-4 (Netto Stabil)</span>
                        <span class="gpa-micro-bold text-success-deep">CONNECTED</span>
                    </p>

                    <button type="submit"
                        class="inline-flex h-11 items-center gap-2 rounded-lg bg-accent px-4 text-success-ink outline outline-1 outline-success-deep transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="lockedAll">
                        <x-gpa.icon name="save" class="h-4 w-4" />
                        <span class="gpa-meta-lg font-bold">SIMPAN DATA PASOKAN</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- Inbound log --}}
        <section class="gpa-panel p-4">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/40 pb-2">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="clock" class="h-4 w-4 text-success-deep" />
                    <h2 class="font-sans text-lg font-bold leading-6 tracking-[-0.01em] text-ink">Log Inbound &amp; Realtime</h2>
                </div>
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 animate-pulse-ring rounded-full bg-success-deep"></span>
                    <span class="gpa-micro-bold text-success-deep">LIVE FEED</span>
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

                @foreach ($logItems ?? [] as $log)
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
                    <span x-show="!allLogs">Lihat Seluruh <span x-text="logTotal">{{ $logTotal }}</span> Log Hari Ini &rarr;</span>
                    <span x-show="allLogs" x-cloak>Menampilkan <span x-text="pendingLogs.length + {{ count($logItems ?? []) }}"></span> log terbaru &rarr;</span>
                </button>
            </footer>
        </section>
    </div>
@endsection

@extends('layouts.coordinator')

@section('title', 'Monitoring & Validasi Proof of Delivery (PoD)')

@section('content')
    @php
        $kg = fn ($value) => rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.');
        $defaultEntry = ($dossierEntries ?? [])[$defaultSj ?? ''] ?? (($dossierEntries ?? []) ? reset($dossierEntries) : null);
        $checkLabels = [
            'signature' => 'Tanda Tangan Penerima',
            'stamp' => 'Cap / Stempel Basah',
            'weight' => 'Kesesuaian Timbangan Netto' . ($defaultEntry ? ' ('.$kg($defaultEntry['net_kg']).' kg)' : ''),
            'gps' => 'Toleransi Radius GPS' . ($defaultEntry ? ' < '.$kg($defaultEntry['gps_tolerance_m']).'m Dari Dock' : ''),
        ];
    @endphp

    <div class="space-y-4"
        x-data="coordinatorMonitoring(@js($dispatchRows ?? []), @js([
            'entries' => $dossierEntries ?? [],
            'checks' => [
                ['key' => 'signature', 'label' => 'Tanda Tangan Penerima'],
                ['key' => 'stamp', 'label' => 'Cap / Stempel Basah'],
                ['key' => 'weight', 'label' => 'Kesesuaian Timbangan Netto'],
                ['key' => 'gps', 'label' => 'Toleransi Radius GPS'],
            ],
        ]), @js([
            'defaultSj' => $defaultSj ?? '',
            'dropoffBase' => $verifiedCount ?? 0,
            'dropoffTotal' => $totalOrdersCount ?? 0,
        ]))">
        {{-- PRD Rule 06 & 12: hard gate PoD --}}
        <section class="gpa-card flex flex-wrap items-start justify-between gap-4 border border-line-hair border-l-4 border-l-brand p-4">
            <div class="flex min-w-0 items-start gap-3">
                <span class="flex h-10 w-9 shrink-0 items-center justify-center rounded-lg bg-brand text-accent">
                    <x-gpa.icon name="shield-check" class="h-4 w-5 shrink-0" />
                </span>
                <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="gpa-section-title text-ink">MANDAT VALIDASI PROOF OF DELIVERY (PoD) — KOORDINATOR</h1>
                        <span
                            class="shrink-0 rounded bg-accent px-2 py-0.5 gpa-micro-bold text-ink outline outline-1 outline-success-deep">
                            GATEKEEPER PENAGIHAN
                        </span>
                    </div>
                    <p class="max-w-4xl text-sm leading-5 text-ink-body">
                        Setiap armada yang tiba wajib menyelesaikan audit digital sebelum status beralih ke <strong class="font-semibold text-ink">SELESAI (CLOSED)</strong>. Kegagalan validasi berkas fisik, cap basah, atau selisih timbangan otomatis menahan penerbitan faktur B2B.
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-right">
                <p class="gpa-micro-bold text-ink-quiet">PROTOKOL AUDIT</p>
                <p class="mt-1 rounded bg-surface-shell px-4 py-1 font-mono text-[11px] font-bold text-ink outline outline-1 outline-line-board/50">
                    SOP-GPA-LOG-09
                </p>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- KPI 1: Armada operasional --}}
            <article class="gpa-card flex flex-col justify-between gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">ARMADA OPERASIONAL</h2>
                    <x-gpa.icon name="truck" class="block h-5 w-5 shrink-0 text-success-deep" />
                </div>
                <p class="flex items-baseline gap-2">
                    <span class="font-inter text-3xl font-bold leading-10 text-ink">{{ $totalArmada ?? 2 }}</span>
                    <span class="text-xs font-medium text-ink-body">Unit Terdaftar</span>
                </p>
                <footer class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <span class="inline-flex items-center gap-1.5 gpa-micro-bold text-ink-body">
                        <span class="h-3 w-1 rounded-full bg-accent" aria-hidden="true"></span>
                        <span>Engkel &amp; Blind Van</span>
                    </span>
                    <span class="gpa-micro-bold text-success-deep">GPS AKTIF</span>
                </footer>
            </article>

            {{-- KPI 2: Dropoff selesai --}}
            <article class="gpa-card flex flex-col justify-between gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">DROPOFF SELESAI</h2>
                    <x-gpa.icon name="check-circle-2" class="block h-5 w-5 shrink-0 text-success-deep" />
                </div>
                <p class="flex items-baseline gap-2">
                    <span class="font-inter text-3xl font-bold leading-10 text-ink" x-text="dropoffValue()">{{ $verifiedCount ?? 0 }}</span>
                    <span class="text-xs font-medium text-ink-body" x-text="dropoffUnit()">/ {{ $totalOrdersCount ?? 0 }} Drop</span>
                </p>
                <footer class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <span class="inline-flex items-center gap-1.5 gpa-micro-bold text-ink-body">
                        <span class="h-3 w-1 rounded-full bg-accent" aria-hidden="true"></span>
                        <span x-text="dropoffNote()">Target On-Time 100%</span>
                    </span>
                    <span class="gpa-micro-bold text-success-deep">{{ round(($totalOrdersCount ?? 0) > 0 ? (($verifiedCount ?? 0) / $totalOrdersCount) * 100 : 0) }}% SLA</span>
                </footer>
            </article>

            {{-- KPI 3: PoD tervalidasi --}}
            <article class="gpa-card flex flex-col justify-between gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">POD TERVALIDASI</h2>
                    <x-gpa.icon name="shield-check" class="block h-5 w-5 shrink-0 text-success-deep" />
                </div>
                <p class="flex items-baseline gap-2">
                    <span class="font-inter text-3xl font-bold leading-10 text-ink">{{ $verifiedCount ?? 0 }}</span>
                    <span class="text-xs font-medium text-ink-body">Berkas Sah</span>
                </p>
                <footer class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <span class="inline-flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                        <span class="h-3 w-1 rounded-full bg-accent" aria-hidden="true"></span>
                        <span>Cap Basah &amp; Foto</span>
                    </span>
                    <span class="gpa-micro-bold text-success-deep">SIAP INVOICE</span>
                </footer>
            </article>

            {{-- KPI 4: Retur --}}
            <article class="gpa-card flex flex-col justify-between gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-warning-deep">INSIDEN / RETUR</h2>
                    <x-gpa.icon name="alert-triangle" class="block h-5 w-5 shrink-0 text-danger" />
                </div>
                <p class="flex items-baseline gap-2">
                    <span class="font-inter text-3xl font-bold leading-10 text-danger">{{ $returCount ?? 0 }}</span>
                    <span class="text-xs font-medium text-danger">Kasus Terbuka</span>
                </p>
                <footer class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                    <span class="inline-flex items-center gap-1.5 gpa-micro-bold text-danger">
                        <span>Berita Acara Wajib</span>
                    </span>
                    <span class="gpa-micro-bold text-warning-caution">PERLU INVESTIGASI</span>
                </footer>
            </article>
        </section>

        {{-- Dispatch log + PoD dossier --}}
        <section class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_23rem]">
            {{-- Dispatch log --}}
            <div class="gpa-card flex flex-col gap-4 p-4">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/40 pb-3">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title flex items-center gap-2 text-ink">
                            <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-success-deep" />
                            LOG PERJALANAN &amp; STATUS PENGIRIMAN HARI INI
                        </h2>
                        <p class="mt-1 font-mono text-[11px] font-medium leading-4 text-ink-body">PILIH BARIS UNTUK MEMERIKSA BERKAS POD &amp; MEMBUKA BERITA ACARA JIKA TERDAPAT RETUR</p>
                    </div>
                    <button type="button" @click="refreshTelemetry()"
                        class="shrink-0 rounded-lg bg-surface-shell px-3 py-1 gpa-note text-ink outline outline-1 outline-line-board/50 transition-colors hover:bg-surface-muted">
                        TELEMETRI GPS AKTIF
                    </button>
                </header>

                <div class="gpa-scroll-x overflow-x-auto">
                    <table class="w-full min-w-[56rem] border-collapse">
                        <caption class="sr-only">LOG PERJALANAN &amp; STATUS PENGIRIMAN HARI INI</caption>
                        <thead>
                            <tr class="border-b border-line-board/40 bg-surface-shell">
                                <th scope="col" class="px-3 py-2 gpa-micro-bold text-ink-body text-left">ARMADA / WAKTU</th>
                                <th scope="col" class="px-3 py-2 gpa-micro-bold text-ink-body text-left">SURAT JALAN &amp; DROP POINT</th>
                                <th scope="col" class="px-3 py-2 gpa-micro-bold text-ink-body text-right">TIMBANGAN (KG)</th>
                                <th scope="col" class="px-3 py-2 gpa-micro-bold text-ink-body text-left">STATUS PENGIRIMAN</th>
                                <th scope="col" class="px-3 py-2 gpa-micro-bold text-ink-body text-center">TINDAKAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dispatchRows ?? [] as $row)
                                <tr @click="select(@js($row['sj']))"
                                    class="cursor-pointer border-b border-line-hair/70 align-middle transition-colors hover:bg-surface-shell"
                                    :class="isActive(@js($row['sj'])) ? 'bg-accent/20' : ''">
                                    <th scope="row"
                                        class="border-l-4 border-transparent px-3 py-3 text-left align-middle"
                                        :class="isActive(@js($row['sj'])) ? '!border-brand' : ''">
                                        <span class="block text-xs font-bold leading-4 text-ink">{{ $row['driver'] }}</span>
                                        <span class="mt-0.5 block gpa-note text-ink-quiet">{{ $row['vehicle'] }}</span>
                                        <span @class([
                                            'mt-1 block gpa-note font-semibold',
                                            'text-success-deep' => $row['arrival_tone'] === 'success-deep',
                                            'text-warning-deep' => $row['arrival_tone'] === 'warning-deep',
                                        ])>{{ $row['arrival'] }}</span>
                                    </th>
                                    <td class="px-3 py-3 align-middle">
                                        <span class="block font-mono text-[11px] font-bold leading-4 text-ink">{{ $row['sj'] }}</span>
                                        <span @class([
                                            'mt-0.5 block text-xs leading-4',
                                            'font-bold text-ink' => $row['customer_tone'] === 'strong',
                                            'font-medium text-ink-body' => $row['customer_tone'] === 'body',
                                        ])>{{ $row['customer'] }}</span>
                                        @if ($row['discrepancy'])
                                            <span class="mt-0.5 block gpa-note font-semibold text-danger">{{ $row['discrepancy'] }}</span>
                                        @else
                                            <span class="mt-0.5 block gpa-note text-ink-quiet">{{ $row['destination'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right align-middle">
                                        <span @class([
                                            'block font-mono text-[11px] font-bold leading-4',
                                            'text-danger' => $row['manifest_kg'] !== null,
                                            'text-ink' => $row['manifest_kg'] === null,
                                        ]) x-text="cargoLabel(@js($row['sj']))">{{ $kg($row['cargo_kg']) }} kg</span>
                                        <span @class([
                                                'mt-0.5 block gpa-note',
                                                'line-through' => $row['manifest_kg'] !== null,
                                            ])
                                            :class="[cargoNoteTone(@js($row['sj'])), manifestStrike(@js($row['sj']))]"
                                            x-text="cargoNote(@js($row['sj']))">{{ $row['manifest_kg'] !== null ? 'Manifest: '.$kg($row['manifest_kg']).' kg' : $row['commodity'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle">
                                        <span class="inline-flex items-center rounded px-2 py-1 outline outline-1 gpa-micro-bold"
                                            :class="statusTone(@js($row['sj']))" x-text="statusLabel(@js($row['sj']))">{{ $row['status'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 align-middle">
                                        <div class="flex flex-col items-center gap-1">
                                            @foreach ($row['actions'] as $action)
                                                <button type="button" @click.stop="rowAction(@js($action['key']), @js($row['sj']))"
                                                    @class([
                                                        'inline-flex w-full items-center justify-center gap-1.5 rounded px-2 py-1.5 gpa-meta-lg transition-colors',
                                                        'bg-brand text-accent shadow-sub outline outline-1 outline-brand-line hover:bg-brand-hover' => $action['variant'] === 'primary',
                                                        'bg-surface text-ink outline outline-1 outline-line-board/70 hover:bg-surface-muted' => $action['variant'] === 'outline',
                                                        'bg-surface-shell text-ink outline outline-1 outline-line-board/40 hover:bg-surface-muted' => $action['variant'] === 'soft',
                                                        'bg-surface text-danger outline outline-1 outline-danger/50 hover:bg-danger-soft' => $action['variant'] === 'danger',
                                                    ])>
                                                    <x-gpa.icon :name="$action['icon']"
                                                        @class([
                                                            'h-3 w-3 shrink-0',
                                                            'text-accent' => $action['variant'] === 'primary',
                                                        ]) />
                                                    <span class="font-semibold">{{ $action['label'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-sm font-medium text-ink-quiet">
                                        Belum ada jadwal perjalanan atau data pengiriman armada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer class="space-y-1 border-t border-line-board/40 pt-2">
                    <p class="flex items-center gap-2 gpa-micro-bold text-ink-quiet">
                        <span class="h-2 w-2 rounded-full bg-accent" aria-hidden="true"></span>
                        Data tersinkron otomatis via mobile web armada setiap titik drop-off selesai.
                    </p>
                    <p class="gpa-micro-bold text-ink-quiet">Toleransi keterlambatan: 15 menit sebelum eskalasi ke Koordinator Wilayah.</p>
                </footer>
            </div>

            {{-- PoD verification dossier --}}
            <div class="gpa-card flex flex-col gap-3 p-4">
                <header class="flex flex-wrap items-start justify-between gap-2 border-b border-line-board/40 pb-2">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title flex items-center gap-2 text-ink">
                            <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-success-deep" />
                            BERKAS PROOF OF DELIVERY (PoD)
                        </h2>
                        <p class="mt-1 font-mono text-[11px] font-medium leading-4 text-ink-body">VERIFIKASI FISIK SEBELUM STATUS DIUBAH KE SELESAI</p>
                    </div>
                    <span class="shrink-0 rounded px-2 py-0.5 gpa-micro-bold text-ink outline outline-1"
                        :class="hasDossier() ? 'bg-accent outline-success-deep' : 'bg-surface-shell text-ink-quiet outline-line-board/50'"
                        x-text="hasDossier() ? 'BERKAS LENGKAP' : 'BERKAS MENUNGGU'">BERKAS LENGKAP</span>
                </header>

                <div x-cloak x-show="!hasDossier()"
                    class="space-y-1 rounded-lg bg-surface-shell p-3 outline outline-1 outline-line-board/40">
                    <p class="flex items-center gap-1.5 gpa-micro-bold text-warning-deep">
                        <x-gpa.icon name="clock" class="h-3.5 w-3.5 shrink-0" />
                        BERKAS BELUM TERSEDIA
                    </p>
                    <p class="text-xs leading-5 text-ink-body">Armada masih dalam perjalanan atau belum mengunggah foto surat jalan dan berita acara.</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40"
                    :class="hasDossier() ? '' : 'opacity-50'">
                    <div class="min-w-0">
                        <p class="gpa-micro-bold text-ink-quiet">NO. REFERENSI POD</p>
                        <p class="font-mono text-[11px] font-bold text-ink" x-text="reference()">{{ $defaultEntry['ref'] ?? '-' }}</p>
                    </div>
                    <div class="min-w-0 text-right">
                        <p class="gpa-micro-bold text-ink-quiet">SURAT JALAN</p>
                        <p class="font-mono text-[11px] font-bold text-success-deep" x-text="associatedSj()">{{ $defaultEntry['sj'] ?? '-' }}</p>
                    </div>
                </div>

                <div x-show="hasDossier()" class="grid gap-2 sm:grid-cols-2 2xl:grid-cols-1">
                    @foreach ($defaultEntry['exhibits'] ?? [] as $index => $exhibit)
                        <article class="space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="gpa-micro-bold text-ink" x-text="exhibitField({{ $index }}, 'code')">{{ $exhibit['code'] }}</h3>
                                <p class="gpa-micro-bold text-success-deep" x-text="exhibitField({{ $index }}, 'meta')">{{ $exhibit['meta'] }}</p>
                            </div>
                            <div class="overflow-hidden rounded-lg bg-surface-track outline outline-1 outline-line-board/60">
                                <div class="flex aspect-[156/174] w-full flex-col items-center justify-center gap-2 p-2">
                                    <x-gpa.icon :name="$exhibit['icon']" class="h-5 w-5 text-ink-quiet" />
                                    <p class="gpa-note text-center text-ink-quiet">BUKTI FOTOGRAFI {{ $index + 1 }} DARI {{ count($defaultEntry['exhibits']) }}</p>
                                </div>
                                <div class="flex items-center justify-between gap-2 bg-brand/85 px-1.5 py-1.5">
                                    <span class="truncate gpa-note text-white" x-text="exhibitField({{ $index }}, 'caption')">{{ $exhibit['caption'] }}</span>
                                    <span class="shrink-0 gpa-micro-bold text-accent" x-text="exhibitField({{ $index }}, 'status')">{{ $exhibit['status'] }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div x-show="hasDossier()"
                    class="space-y-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/60">
                    <p class="gpa-micro-bold text-ink">CHECKLIST VERIFIKASI FISIK</p>
                    <div class="space-y-1.5">
                        <button type="button" @click="toggleCheck('signature')" class="flex w-full items-start gap-2 text-left">
                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded" :class="checkTone('signature')">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0" />
                            </span>
                            <span class="text-xs leading-5 text-ink" x-text="checkLabel('signature')">{{ $checkLabels['signature'] }}</span>
                        </button>
                        <button type="button" @click="toggleCheck('stamp')" class="flex w-full items-start gap-2 text-left">
                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded" :class="checkTone('stamp')">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0" />
                            </span>
                            <span class="text-xs leading-5 text-ink" x-text="checkLabel('stamp')">{{ $checkLabels['stamp'] }}</span>
                        </button>
                        <button type="button" @click="toggleCheck('weight')" class="flex w-full items-start gap-2 text-left">
                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded" :class="checkTone('weight')">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0" />
                            </span>
                            <span class="text-xs leading-5 text-ink" x-text="checkLabel('weight')">{{ $checkLabels['weight'] }}</span>
                        </button>
                        <button type="button" @click="toggleCheck('gps')" class="flex w-full items-start gap-2 text-left">
                            <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded" :class="checkTone('gps')">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0" />
                            </span>
                            <span class="text-xs leading-5 text-ink" x-text="checkLabel('gps')">{{ $checkLabels['gps'] }}</span>
                        </button>
                    </div>
                </div>

                <div x-show="hasDossier()" class="space-y-1">
                    <p class="gpa-micro-bold text-ink-quiet">CATATAN KOORDINATOR</p>
                    <textarea x-model="notes" rows="3"
                        class="w-full resize-none rounded-lg border border-line-board bg-surface p-2 text-xs leading-5 text-ink outline-none transition-colors focus:border-brand focus:ring-2 focus:ring-accent/30"
                        placeholder="Catatan koordinator gudang untuk audit trail...">{{ $defaultEntry['notes'] ?? '' }}</textarea>
                </div>

                <footer class="space-y-2 border-t border-line-board/40 pt-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/60">
                        <p class="font-mono text-[11px] font-bold text-ink">KEPUTUSAN AUDIT</p>
                        <div class="flex flex-wrap items-center gap-4">
                            <button type="button" @click="setDecision('approved')"
                                class="inline-flex items-center gap-1.5"
                                :aria-pressed="isDecision('approved') ? 'true' : 'false'">
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                    :class="isDecision('approved') ? 'bg-brand' : 'bg-surface outline outline-2 outline-line-board'">
                                    <span x-show="isDecision('approved')" class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                                </span>
                                <span class="font-mono text-[11px] font-semibold text-success-deep">SETUJUI (VALID)</span>
                            </button>
                            <button type="button" @click="setDecision('rejected')"
                                class="inline-flex items-center gap-1.5"
                                :aria-pressed="isDecision('rejected') ? 'true' : 'false'">
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                    :class="isDecision('rejected') ? 'bg-brand' : 'bg-surface outline outline-2 outline-line-board'">
                                    <span x-show="isDecision('rejected')" class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                                </span>
                                <span class="font-mono text-[11px] font-semibold text-danger">TOLAK (RETUR / SELISIH)</span>
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="dossierAction('validate')"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand px-4 text-accent shadow-pop outline outline-1 outline-brand-line transition-colors hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-surface-disabled disabled:text-ink-quiet disabled:shadow-sub disabled:outline-line-board"
                        :disabled="!canValidate()">
                        <x-gpa.icon name="check-circle-2" class="h-4 w-4 shrink-0 text-accent" />
                        <span class="gpa-label font-bold text-accent">VALIDASI POD &amp; TUTUP ORDER</span>
                    </button>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <button type="button" @click="dossierAction('call-pic')"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors bg-surface text-ink outline outline-1 outline-line-board/70 hover:bg-surface-muted">
                            <x-gpa.icon name="phone" class="h-3.5 w-3.5 shrink-0" />
                            Hubungi PIC Klien
                        </button>
                        <a href="{{ route('prints.return-act') }}" target="_blank"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors bg-surface text-danger outline outline-1 outline-danger/50 hover:bg-danger-soft">
                            <x-gpa.icon name="clipboard" class="h-3.5 w-3.5 shrink-0" />
                            Cetak BAP Retur
                        </a>
                    </div>
                </footer>
            </div>
        </section>
    </div>
@endsection

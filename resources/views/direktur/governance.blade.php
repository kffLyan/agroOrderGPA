@extends('layouts.direktur')

@section('title', 'Pengaturan Tata Kelola // Kebijakan & Audit Trail')

@section('content')
    @php
        $auditRows = [];
        if (!empty($storedLogs)) {
            foreach ($storedLogs as $l) {
                $timeCarbon = isset($l['timestamp']) ? \Carbon\Carbon::parse($l['timestamp']) : (isset($l['created_at']) ? \Carbon\Carbon::parse($l['created_at']) : now());
                $isBlocked = in_array($l['status'] ?? '', ['REJECTED', 'INTERCEPTED']);
                $tone = 'neutral';
                if ($isBlocked) {
                    $tone = 'danger';
                } elseif (str_contains(strtoupper($l['action_category'] ?? ''), 'APPROVAL')) {
                    $tone = 'success';
                } elseif (str_contains(strtoupper($l['action_category'] ?? ''), 'GOVERNANCE') || str_contains(strtoupper($l['action_category'] ?? ''), 'LOCK')) {
                    $tone = 'dark';
                }

                $auditRows[] = [
                    'date'        => $timeCarbon->format('d-M-Y'),
                    'time'        => $timeCarbon->format('H:i:s') . ' WIB',
                    'actor'       => $l['actor'] ?? 'System',
                    'actor_meta'  => $l['actor_uid'] ?? ($l['clearance'] ?? 'UID: ROOT'),
                    'action'      => str_replace('_', ' ', $l['action_category'] ?? 'ACTION'),
                    'action_tone' => $tone,
                    'document'    => $l['document_ref'] ?? '-',
                    'detail'      => $l['details'] ?? '-',
                    'hash'        => substr($l['hash'] ?? 'sha256...', 0, 8) . '...' . substr($l['hash'] ?? '0000', -4),
                    'block'       => $l['block_id'] ?? ($l['block_number'] ?? 'BLOCK [SEALED]'),
                    'blocked'     => $isBlocked,
                ];
            }
        }

        $auditActions = array_values(array_unique(array_column($auditRows, 'action')));
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorGovernance(@js($auditRows))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                    Pengaturan Tata Kelola, Kebijakan Bisnis &amp; Audit Trail Sistem
                </h1>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('director.access') }}"
                    class="inline-flex items-center gap-1.5 rounded bg-surface-shell px-3 py-2 gpa-meta font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="users" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    Kelola Akun Pengguna &amp; RBAC
                </a>
                <button type="button" @click="exportAudit()"
                    class="inline-flex items-center gap-1.5 rounded bg-surface-shell px-3 py-2 gpa-meta font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    Export Full Audit Log (.CSV)
                </button>
                <button type="button" @click="verifyIntegrity()"
                    class="inline-flex items-center gap-1.5 rounded bg-accent px-3 py-2 gpa-meta font-bold text-ink shadow-sub outline outline-1 -outline-offset-1 outline-success-deep transition-opacity hover:opacity-90">
                    <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0" />
                    Uji Integritas Kriptografi
                </button>
                <button type="button" @click="lockPeriod()"
                    class="inline-flex items-center gap-1.5 rounded bg-ink px-3 py-2 gpa-meta font-bold text-accent shadow-sub transition-opacity hover:opacity-90">
                    <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0" />
                    Kunci Sistem Periode
                </button>
            </div>
        </section>

        {{-- Metric row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Metric 1: Integritas Ledger --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-ink-body">INTEGRITAS LEDGER</h2>
                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            ACTIVE GUARD
                        </span>
                    </div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">100% VALID</p>
                    <p class="text-[11px] leading-4 text-ink-body">SHA-256 Checksum Verified</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="gpa-note text-ink-body">Anomali Terdeteksi</span>
                    <span class="gpa-note font-bold text-success-deep">0 Anomali (Zero Breach)</span>
                </div>
            </article>

            {{-- Metric 2: Kontrol Kredit B2B --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-ink-body">KONTROL KREDIT B2B</h2>
                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                            CEILING LIMIT
                        </span>
                    </div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">Rp 2.500.000.000</p>
                    <p class="text-[11px] leading-4 text-ink-body">Plafon kredit korporat maksimum</p>
                    <div class="flex flex-col gap-1">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                            <div class="h-full rounded-full bg-success-deep" style="width: 47.4%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="gpa-note text-ink-body">Terutilisasi</span>
                    <span class="gpa-note font-bold text-success-deep">Rp 1.185.000.000</span>
                </div>
                <p class="gpa-note font-semibold text-success-deep">47.4% Exposure Aman</p>
            </article>

            {{-- Metric 3: Dir Approval Status --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-ink-body">DIR APPROVAL STATUS</h2>
                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            VERIFIED
                        </span>
                    </div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">3 Kontrak Disetujui</p>
                    <p class="text-[11px] leading-4 text-ink-body">Threshold Policy Margin Min. {{ number_format($settings['min_margin_percent'] ?? 15.0, 1) }}%</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="gpa-note text-ink-body">Pending Otorisasi Dir</span>
                    <span class="gpa-note font-bold text-success-deep">0 Pending Dir (Clear)</span>
                </div>
            </article>

            {{-- Metric 4: Hard-Guard Compliance --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-ink-body">HARD-GUARD COMPLIANCE</h2>
                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                            POLICY ENGINE
                        </span>
                    </div>
                    <p class="gpa-figure text-[22px] leading-7 text-ink">0 Pelanggaran</p>
                    <p class="text-[11px] leading-4 text-ink-body">Hard-Guard Rules 02, 03, 05, 06 Enforced</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="gpa-note text-ink-body">Status Intersepsi</span>
                    <span class="gpa-note font-bold text-ink">100% Intercepted</span>
                </div>
            </article>
        </section>

        {{-- Rule engine parameters --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Konfigurasi Parameter Bisnis &amp; Rule Engine</h2>
                </div>

                <button type="button" @click="editParameters()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded bg-ink px-3.5 py-2 gpa-meta font-bold text-accent transition-opacity hover:opacity-90">
                    <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0" />
                    Edit Parameter Kebijakan (PIN Master)
                </button>
            </header>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {{-- Parameter Card 1 --}}
                <article class="flex h-full flex-col gap-3 rounded-xl bg-surface-shell/60 p-4 outline outline-1 -outline-offset-1 outline-line-board">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            ENFORCED
                        </span>
                    </div>
                    <h3 class="text-[13px] font-bold leading-5 text-ink">Toleransi Deviasi Susut Gudang</h3>
                    <p class="text-[11px] leading-4 text-ink-body">Ambang batas perbedaan timbangan asal versus timbangan terima di fasilitas pergudangan.</p>

                    <dl class="flex flex-col gap-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Maks Hard Limit:</dt>
                            <dd class="gpa-meta text-right font-bold text-ink">{{ number_format($settings['warehouse_loss_max_percent'] ?? 2.0, 1) }}%</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Warning Threshold:</dt>
                            <dd class="gpa-meta text-right font-bold text-warning-caution">{{ number_format($settings['warehouse_loss_warning_percent'] ?? 1.5, 1) }}%</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Action Triggered:</dt>
                            <dd class="gpa-meta text-right font-bold text-danger">Auto-Hold Invoice</dd>
                        </div>
                    </dl>

                    <footer class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-semibold text-ink-body">
                            <x-gpa.icon name="bolt" class="h-3 w-3 shrink-0 text-success-deep" />
                            SLA Validasi: Real-Time
                        </span>
                        <span class="gpa-micro-bold text-ink-body">CFG-DEV-0413</span>
                    </footer>
                </article>

                {{-- Parameter Card 2 --}}
                <article class="flex h-full flex-col gap-3 rounded-xl bg-surface-shell/60 p-4 outline outline-1 -outline-offset-1 outline-line-board">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            ENFORCED
                        </span>
                    </div>
                    <h3 class="text-[13px] font-bold leading-5 text-ink">Plafon Kredit Default TOP Klien Baru</h3>
                    <p class="text-[11px] leading-4 text-ink-body">Kebijakan limit terms of payment (TOP) untuk mitra agribisnis atau buyer yang belum diaudit Direksi.</p>

                    <dl class="flex flex-col gap-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Default Plafon:</dt>
                            <dd class="gpa-meta text-right font-bold text-ink">Rp {{ number_format($settings['default_top_credit_ceiling'] ?? 100000000, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Term of Payment:</dt>
                            <dd class="gpa-meta text-right font-bold text-ink">{{ $settings['default_top_days'] ?? 30 }} Hari Kalender</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Min Skor Kredit:</dt>
                            <dd class="gpa-meta text-right font-bold text-success-deep">{{ $settings['min_credit_score'] ?? 75 }} Poin Sistem</dd>
                        </div>
                    </dl>

                    <footer class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-semibold text-ink-body">
                            <x-gpa.icon name="bolt" class="h-3 w-3 shrink-0 text-success-deep" />
                            Escalation: Direksi Keuangan
                        </span>
                        <span class="gpa-micro-bold text-ink-body">CFG-KRD-1503</span>
                    </footer>
                </article>

                {{-- Parameter Card 3 --}}
                <article class="flex h-full flex-col gap-3 rounded-xl bg-surface-shell/60 p-4 outline outline-1 -outline-offset-1 outline-line-board">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            MANDATORY
                        </span>
                    </div>
                    <h3 class="text-[13px] font-bold leading-5 text-ink">Syarat Wajib Terbit Surat Jalan</h3>
                    <p class="text-[11px] leading-4 text-ink-body">Gerbang verifikasi fisik dan digital sebelum armada angkut logistik diizinkan keluar depot/gudang.</p>

                    <dl class="flex flex-col gap-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Net Weight Tera Sah:</dt>
                            <dd class="gpa-meta text-right font-bold text-success-deep">Mandatori Otomatis</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Lolos QC Laboratorium:</dt>
                            <dd class="gpa-meta text-right font-bold text-ink">100% Grade A/B</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Dispensasi Bypass:</dt>
                            <dd class="gpa-meta text-right font-bold text-danger">0 Toleransi (Dilarang)</dd>
                        </div>
                    </dl>

                    <footer class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-semibold text-ink-body">
                            <x-gpa.icon name="bolt" class="h-3 w-3 shrink-0 text-success-deep" />
                            Interlock: Gate Barrier IoT
                        </span>
                        <span class="gpa-micro-bold text-ink-body">CFG-DISP-0005</span>
                    </footer>
                </article>

                {{-- Parameter Card 4 --}}
                <article class="flex h-full flex-col gap-3 rounded-xl bg-surface-shell/60 p-4 outline outline-1 -outline-offset-1 outline-line-board">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            ENFORCED
                        </span>
                    </div>
                    <h3 class="text-[13px] font-bold leading-5 text-ink">Batas Margin Minimum Penjualan B2B</h3>
                    <p class="text-[11px] leading-4 text-ink-body">Floor margin proteksi profitabilitas bruto komoditas pangan segar terhadap fluktuasi harga lelang petani.</p>

                    <dl class="flex flex-col gap-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Margin Minimum Statutori:</dt>
                            <dd class="gpa-meta text-right font-bold text-ink">{{ number_format($settings['min_margin_percent'] ?? 15.0, 1) }}%</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Order &lt; {{ number_format($settings['min_margin_percent'] ?? 15.0, 1) }}%:</dt>
                            <dd class="gpa-meta text-right font-bold text-warning-caution">Auto-Eskalasi Direktur</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Sales Bypass Right:</dt>
                            <dd class="gpa-meta text-right font-bold text-danger">Locked (No Access)</dd>
                        </div>
                    </dl>

                    <footer class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-semibold text-ink-body">
                            <x-gpa.icon name="bolt" class="h-3 w-3 shrink-0 text-success-deep" />
                            Formula: Net Realisasi COGS
                        </span>
                        <span class="gpa-micro-bold text-ink-body">CFG-MRG-1600</span>
                    </footer>
                </article>

                {{-- Parameter Card 5 --}}
                <article class="flex h-full flex-col gap-3 rounded-xl bg-surface-shell/60 p-4 outline outline-1 -outline-offset-1 outline-line-board">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            IMMUTABLE CORE
                        </span>
                    </div>
                    <h3 class="text-[13px] font-bold leading-5 text-ink">Anti-Overselling Guard (Buffer Fisik + Buffer Mitra)</h3>
                    <p class="text-[11px] leading-4 text-ink-body">Mencegah komitmen kontrak jual melampaui agregasi stok fisik gudang dan buffer pasokan mitra tervalidasi sensor silo.</p>

                    <dl class="flex flex-col gap-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Alokasi Buffer Fisik:</dt>
                            <dd class="gpa-meta text-right font-bold text-ink">Standby Real-Time WMS</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Agregasi Mitra Tani:</dt>
                            <dd class="gpa-meta text-right font-bold text-ink">Telemetri Terverifikasi</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-note text-ink-body">Intervensi Sistem:</dt>
                            <dd class="gpa-meta text-right font-bold text-danger">Auto-Block Order Terlampaui</dd>
                        </div>
                    </dl>

                    <footer class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-semibold text-ink-body">
                            <x-gpa.icon name="bolt" class="h-3 w-3 shrink-0 text-success-deep" />
                            Latency Verifikasi: &lt; 35ms
                        </span>
                        <span class="gpa-micro-bold text-ink-body">CFG-OVS-0203</span>
                    </footer>
                </article>
            </div>

            <p class="flex items-start gap-2 border-t border-line-board/60 pt-3">
                <x-gpa.icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-ink-body" />
                <span class="gpa-note text-ink-body">Pengubahan parameter memerlukan PIN Master Direktur Utama.</span>
            </p>
        </section>

        {{-- Audit trail --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Tabel Audit Trail Forensik &amp; Riwayat Aksi Direksi / Admin</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative flex items-center" for="audit-search">
                        <span class="sr-only">Cari Dokumen / Hash...</span>
                        <x-gpa.icon name="search"
                            class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                        <input id="audit-search" type="search" x-model="search"
                            placeholder="Cari Dokumen / Hash..."
                            class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </label>

                    <button type="button" @click="setAction('all')"
                        :class="filterClass('all')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Semua Kategori
                    </button>
                    @foreach ($auditActions as $auditAction)
                        <button type="button" @click="setAction(@js($auditAction))"
                            :class="filterClass(@js($auditAction))"
                            class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                            {{ $auditAction }}
                        </button>
                    @endforeach
                </div>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">Tabel Audit Trail Forensik &amp; Riwayat Aksi Direksi / Admin</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                TIMESTAMP
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                AKTOR &amp; USER ID
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                KATEGORI AKSI
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                DOKUMEN TERKAIT
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                RINCIAN PERUBAHAN
                            </th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                STATUS HASH SHA-256
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($auditRows as $row)
                            <tr @class([
                                'align-top transition-colors hover:bg-surface-shell/50',
                                'bg-danger-soft/25' => $row['blocked'],
                            ]) x-show="matches(@js($row))">
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta font-bold text-ink">{{ $row['date'] }}</p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['time'] }}</p>
                                </td>
                                <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                    <p class="text-[13px] font-semibold leading-5 text-ink">{{ $row['actor'] }}</p>
                                    <p class="mt-0.5 text-[11px] leading-4 text-ink-body">{{ $row['actor_meta'] }}</p>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <span @class([
                                        'inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta',
                                        'bg-danger-soft/40 text-danger outline outline-1 -outline-offset-1 outline-danger' => $row['action_tone'] === 'danger',
                                        'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep' => $row['action_tone'] === 'success',
                                        'bg-ink text-accent outline outline-1 -outline-offset-1 outline-ink' => $row['action_tone'] === 'dark',
                                        'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board' => $row['action_tone'] === 'neutral',
                                    ])>
                                        {{ $row['action'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta font-semibold text-ink">{{ $row['document'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="max-w-md text-[12px] leading-5 text-ink-body">{{ $row['detail'] }}</p>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <p class="gpa-meta font-semibold text-success-deep">{{ $row['hash'] }}</p>
                                    <p class="mt-0.5 gpa-note font-bold {{ $row['blocked'] ? 'text-danger' : 'text-ink-body' }}">
                                        {{ $row['block'] }}
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-xs text-ink-body">
                                    Belum ada entri riwayat audit trail tercatat di sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="gpa-note font-semibold text-danger" x-show="visibleCount() === 0" x-cloak>
                Tidak ada entri audit yang cocok dengan pencarian atau filter kategori.
            </p>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 pt-3">
                <p class="flex items-start gap-2">
                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="gpa-note font-bold text-success-deep">LEDGER STATUS: CONTINUOUS APPEND // PREVIOUS BLOCK HASH MATCHED 100%</span>
                </p>
                <div class="flex flex-col items-end gap-1">
                    <p class="gpa-note text-ink-body">Halaman 1 dari 1</p>
                    <p class="gpa-note font-bold text-ink">Menampilkan {{ count($auditRows) }} Aksi Terverifikasi</p>
                </div>
            </footer>
        </section>

    </div>
@endsection

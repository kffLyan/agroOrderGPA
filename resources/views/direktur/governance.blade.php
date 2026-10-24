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

    <div class="mx-auto flex w-full min-w-0 max-w-[1280px] flex-col gap-6"
        x-data="directorGovernance(@js($auditRows))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                    Pengaturan Tata Kelola, Kebijakan Bisnis &amp; Audit Trail Sistem
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('direktur.users.index') }}"
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
                    <p class="text-[11px] leading-4 text-ink-body">SOP Operasional &amp; Kontrol Sistem Aktif</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="gpa-note text-ink-body">Status Intersepsi</span>
                    <span class="gpa-note font-bold text-ink">100% Intercepted</span>
                </div>
            </article>
        </section>

        {{-- Parameter kebijakan bisnis --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Konfigurasi Parameter Bisnis &amp; Kebijakan Sistem</h2>
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

        {{-- Kustomisasi Kanal Pembayaran Resmi GPA (Direktur Master Control) --}}
        <section class="flex flex-col gap-5 rounded-2xl bg-surface p-6 shadow-card border border-line"
            x-data="{
                channels: @js($paymentChannels ?? []),
                editing: false,
                modalImage: null,
                modalTitle: '',
                openModal(url, title) {
                    this.modalImage = url;
                    this.modalTitle = title || 'Barcode QRIS Resmi';
                },
                previewImage(event, channel) {
                    const file = event.target.files[0];
                    if (file) {
                        channel.preview_url = URL.createObjectURL(file);
                    }
                },
                addChannel(type = 'BANK') {
                    const id = (type === 'QRIS' ? 'qris_' : (type === 'EWALLET' ? 'ewallet_' : 'bank_')) + Date.now();
                    const template = {
                        id: id,
                        type: type,
                        name: type === 'QRIS' ? 'QRIS Resmi GPA' : (type === 'EWALLET' ? 'Dompet Digital Bisnis GPA' : 'Bank Baru'),
                        bank_code: type === 'QRIS' ? 'QRIS' : (type === 'EWALLET' ? 'EWALLET' : 'BANK'),
                        account_number: type === 'QRIS' ? 'ID1020039182948' : '1234-5678-90',
                        account_name: 'PT GUNTUR PANDU AGRO',
                        description: type === 'QRIS' ? 'Pindai barcode QRIS via m-banking & e-wallet apa saja' : 'Rekening Giro Korporat Resmi',
                        instructions: type === 'QRIS' ? 'Pindai barcode QRIS resmi GPA di bawah dan simpan bukti tangkapan layar transaksi.' : 'Sertakan nomor faktur di berita transfer.',
                        image_url: type === 'QRIS' ? '/storage/payment-channels/qris-gpa-default.png' : null,
                        preview_url: null,
                        is_active: true
                    };
                    this.channels.push(template);
                    this.editing = true;
                },
                removeChannel(index) {
                    if (this.channels.length <= 1) {
                        alert('Minimal harus ada satu kanal pembayaran.');
                        return;
                    }
                    this.channels.splice(index, 1);
                },
                loadPopularPresets() {
                    if (confirm('Terapkan template lengkap seluruh metode pembayaran terpopuler di Indonesia (Bank BCA, Mandiri, BRI, BNI, BSI, QRIS Standar Nasional dengan Barcode, & E-Wallet)? Anda dapat menyesuaikan kembali sebelum menekan tombol Simpan.')) {
                        this.channels = [
                            {
                                id: 'bca',
                                type: 'BANK',
                                name: 'Bank BCA',
                                bank_code: 'BCA',
                                account_number: '8210-992-3841',
                                account_name: 'PT Guntur Pandu Agro (Divisi Keuangan B2B)',
                                description: 'Rekening Virtual & Giro Korporat Utama',
                                instructions: 'Sertakan Nomor Faktur di berita transfer untuk rekonsiliasi otomatis perbankan.',
                                image_url: null,
                                logo_url: '/images/payments/bca.svg',
                                preview_url: null,
                                is_active: true
                            },
                            {
                                id: 'mandiri',
                                type: 'BANK',
                                name: 'Bank Mandiri',
                                bank_code: 'MANDIRI',
                                account_number: '137-00-1928374-1',
                                account_name: 'PT Guntur Pandu Agro',
                                description: 'Giro Korporat & Mandiri Cash Management (MCM)',
                                instructions: 'Transfer via Mandiri Livin, Kopra by Mandiri, atau Kliring SKN/BI-FAST.',
                                image_url: null,
                                logo_url: '/images/payments/mandiri.svg',
                                preview_url: null,
                                is_active: true
                            },
                            {
                                id: 'bri',
                                type: 'BANK',
                                name: 'Bank BRI',
                                bank_code: 'BRI',
                                account_number: '0341-01-001928-30-2',
                                account_name: 'PT Guntur Pandu Agro',
                                description: 'Rekening Operasional Agribisnis & BritAma Bisnis',
                                instructions: 'Transfer via BRImo, Cash Management BRI, atau Teller BRI seluruh Indonesia.',
                                image_url: null,
                                logo_url: '/images/payments/bri.svg',
                                preview_url: null,
                                is_active: true
                            },
                            {
                                id: 'bni',
                                type: 'BANK',
                                name: 'Bank BNI',
                                bank_code: 'BNI',
                                account_number: '0819-283-746',
                                account_name: 'PT Guntur Pandu Agro',
                                description: 'Rekening Giro Bisnis & BNI Direct',
                                instructions: 'Transfer via BNI Direct, BNI Mobile Banking, atau ATM BNI.',
                                image_url: null,
                                logo_url: '/images/payments/bni.svg',
                                preview_url: null,
                                is_active: true
                            },
                            {
                                id: 'bsi',
                                type: 'BANK',
                                name: 'Bank Syariah Indonesia (BSI)',
                                bank_code: 'BSI',
                                account_number: '719-283-9481',
                                account_name: 'PT Guntur Pandu Agro',
                                description: 'Rekening Giro Wadiah Syariah Bebas Riba',
                                instructions: 'Transfer via BSI Mobile, BSI Net Banking, atau Kliring BI-FAST.',
                                image_url: null,
                                logo_url: '/images/payments/bsi.svg',
                                preview_url: null,
                                is_active: true
                            },
                            {
                                id: 'qris',
                                type: 'QRIS',
                                name: 'QRIS GPA (Semua E-Wallet & Mobile Banking)',
                                bank_code: 'QRIS',
                                account_number: 'ID1020039182948',
                                account_name: 'PT GUNTUR PANDU AGRO',
                                description: 'Pindai QRIS via BCA Mobile, Livin, BRImo, GoPay, OVO, ShopeePay, DANA',
                                instructions: 'Scan barcode QRIS resmi GPA di bawah dan simpan bukti tangkapan layar transaksi.',
                                image_url: '/storage/payment-channels/qris-gpa-default.png',
                                logo_url: '/images/payments/qris.svg',
                                preview_url: null,
                                is_active: true
                            },
                            {
                                id: 'ewallet',
                                type: 'EWALLET',
                                name: 'Dompet Digital Bisnis (GoPay & DANA GPA)',
                                bank_code: 'EWALLET',
                                account_number: '0812-8888-9281',
                                account_name: 'PT GUNTUR PANDU AGRO',
                                description: 'Merchant Dompet Digital Resmi Terverifikasi',
                                instructions: 'Transfer saldo dompet digital ke nomor merchant resmi PT Guntur Pandu Agro.',
                                image_url: null,
                                logo_url: '/images/payments/ewallet.svg',
                                preview_url: null,
                                is_active: true
                            }
                        ];
                        this.editing = true;
                    }
                }
            }">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-accent px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-ink">
                            Otoritas Direktur
                        </span>
                        <span class="text-xs text-ink-quiet">Gateway &amp; Settlement Rekening &amp; QRIS Resmi</span>
                    </div>
                    <h2 class="mt-1 font-sans text-xl font-bold text-ink">
                        Kustomisasi Rekening Bank &amp; Foto Barcode QRIS Pembayaran Klien
                    </h2>
                    <p class="text-xs text-ink-body max-w-3xl">
                        Atur rekening perbankan giro korporat dan unggah foto/barcode QRIS resmi yang ditampilkan kepada mitra klien saat melakukan konfirmasi pembayaran faktur atau pesanan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="editing = !editing"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface-shell px-3.5 py-2 text-xs font-bold text-ink transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="edit" class="h-3.5 w-3.5 text-success-deep" />
                        <span x-text="editing ? 'Tutup Mode Edit' : 'Edit Kanal Pembayaran'"></span>
                    </button>

                    <div class="flex items-center gap-1" x-show="editing">
                        <button type="button" @click="addChannel('BANK')"
                            class="inline-flex items-center gap-1 rounded-lg bg-surface-pill px-2.5 py-2 text-xs font-bold text-ink transition-colors hover:bg-surface-disabled">
                            <x-gpa.icon name="plus" class="h-3.5 w-3.5" />
                            + Bank
                        </button>
                        <button type="button" @click="addChannel('QRIS')"
                            class="inline-flex items-center gap-1 rounded-lg bg-ink text-accent px-2.5 py-2 text-xs font-bold transition-colors hover:bg-black">
                            <x-gpa.icon name="plus" class="h-3.5 w-3.5" />
                            + QRIS (Foto)
                        </button>
                        <button type="button" @click="addChannel('EWALLET')"
                            class="inline-flex items-center gap-1 rounded-lg bg-surface-pill px-2.5 py-2 text-xs font-bold text-ink transition-colors hover:bg-surface-disabled">
                            <x-gpa.icon name="plus" class="h-3.5 w-3.5" />
                            + E-Wallet
                        </button>
                    </div>
                </div>
            </header>

            <form method="POST" action="{{ route('direktur.governance.payment-channels') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
                    <template x-for="(channel, idx) in channels" :key="channel.id">
                        <article class="flex flex-col justify-between rounded-xl border p-4 transition-all"
                            :class="channel.is_active ? 'border-line-soft bg-surface shadow-sub' : 'border-line-soft/40 bg-surface-muted/40 opacity-75'">
                            
                            <input type="hidden" :name="'channels[' + idx + '][id]'" :value="channel.id">
                            <input type="hidden" :name="'channels[' + idx + '][logo_url]'" :value="channel.logo_url || ''">

                            <div class="flex flex-col gap-3">
                                <div class="flex items-center justify-between gap-2 border-b border-line-soft/60 pb-2.5">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <div class="flex h-9 w-12 shrink-0 items-center justify-center rounded-lg bg-white p-1 shadow-xs border border-line-soft overflow-hidden">
                                            <template x-if="channel.logo_url">
                                                <img :src="channel.logo_url" :alt="channel.name" class="h-full w-full object-contain" />
                                            </template>
                                            <template x-if="!channel.logo_url">
                                                <span class="font-mono text-xs font-extrabold text-ink"
                                                    x-text="channel.bank_code || (channel.type === 'QRIS' ? 'QRIS' : (channel.type === 'EWALLET' ? 'WALLET' : 'BANK'))">
                                                </span>
                                            </template>
                                        </div>
                                        <div class="min-w-0" x-show="!editing">
                                            <h3 class="font-sans text-sm font-bold text-ink truncate" x-text="channel.name"></h3>
                                            <span class="text-[10px] uppercase tracking-wider text-ink-quiet block truncate" x-text="channel.type === 'QRIS' ? 'QRIS Standar Nasional' : (channel.type === 'EWALLET' ? 'Dompet Digital' : 'Rekening Bank')"></span>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <label class="relative inline-flex cursor-pointer items-center">
                                            <input type="checkbox" :name="'channels[' + idx + '][is_active]'" value="1"
                                                x-model="channel.is_active" class="sr-only peer">
                                            <div class="w-9 h-5 bg-surface-pill peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-line-soft after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-success-deep"></div>
                                        </label>
                                        <span class="text-[10px] font-bold uppercase"
                                            :class="channel.is_active ? 'text-success-deep' : 'text-ink-quiet'"
                                            x-text="channel.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                                    </div>
                                </div>

                                {{-- Mode Tampilan Normal --}}
                                <div class="flex flex-col gap-2 text-xs" x-show="!editing">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] uppercase text-ink-quiet font-semibold" x-text="channel.type === 'QRIS' ? 'Kode NMID / Barcode:' : (channel.type === 'EWALLET' ? 'Nomor E-Wallet / HP:' : 'Nomor Rekening / VA:')"></span>
                                        <span class="font-mono text-sm font-extrabold text-ink tracking-wide" x-text="channel.account_number"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] uppercase text-ink-quiet font-semibold">Atas Nama (a.n.):</span>
                                        <span class="font-medium text-ink" x-text="channel.account_name"></span>
                                    </div>

                                    {{-- Pratinjau Foto Barcode QRIS / Gambar Kanal di Mode View --}}
                                    <div x-show="channel.type === 'QRIS' || channel.image_url" class="mt-1 flex items-center gap-2.5 rounded-xl border border-line-soft bg-surface-muted/60 p-2.5">
                                        <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-line-soft bg-white shadow-xs cursor-pointer group"
                                            @click="openModal(channel.image_url || '/storage/payment-channels/qris-gpa-default.png', channel.name)">
                                            <img :src="channel.image_url || '/storage/payment-channels/qris-gpa-default.png'" alt="Barcode QRIS" class="h-full w-full object-contain p-0.5">
                                            <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <x-gpa.icon name="eye" class="h-4 w-4 text-white" />
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="inline-flex items-center gap-1 rounded bg-accent/30 px-1.5 py-0.5 text-[9px] font-bold text-ink max-w-full">
                                                <x-gpa.icon name="check" class="h-2.5 w-2.5 shrink-0 text-success-deep" />
                                                <span class="truncate">FOTO BARCODE TERPASANG</span>
                                            </span>
                                            <p class="mt-0.5 text-[11px] font-semibold text-ink truncate">Klik foto untuk perbesar layar</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col border-t border-line-soft/40 pt-2 text-[11px] text-ink-body">
                                        <span class="font-semibold text-ink" x-text="channel.description"></span>
                                        <span class="text-ink-quiet italic" x-text="channel.instructions"></span>
                                    </div>
                                </div>

                                {{-- Mode Edit --}}
                                <div class="flex flex-col gap-2.5 text-xs pt-1" x-show="editing">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet">Tipe Kanal:</label>
                                        <select :name="'channels[' + idx + '][type]'" x-model="channel.type" class="gpa-control mt-1 text-xs py-1">
                                            <option value="BANK">Rekening Bank (Giro / Virtual Account)</option>
                                            <option value="QRIS">QRIS Statis / Dinamis (Unggah Foto Barcode)</option>
                                            <option value="EWALLET">Dompet Digital / E-Wallet Bisnis (GoPay/DANA/OVO)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet">Nama Bank / Label Kanal:</label>
                                        <input type="text" :name="'channels[' + idx + '][name]'" x-model="channel.name" class="gpa-control mt-1 text-xs py-1" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet">Kode Singkat (3-8 huruf):</label>
                                        <input type="text" :name="'channels[' + idx + '][bank_code]'" x-model="channel.bank_code" class="gpa-control mt-1 text-xs py-1">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet" x-text="channel.type === 'QRIS' ? 'Nomor NMID Barcode:' : (channel.type === 'EWALLET' ? 'Nomor Akun / No. HP Merchant:' : 'Nomor Rekening / Virtual Account:')"></label>
                                        <input type="text" :name="'channels[' + idx + '][account_number]'" x-model="channel.account_number" class="gpa-control mt-1 font-mono text-xs py-1" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet">Atas Nama (Beneficiary Name):</label>
                                        <input type="text" :name="'channels[' + idx + '][account_name]'" x-model="channel.account_name" class="gpa-control mt-1 text-xs py-1" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet">Keterangan / Deskripsi:</label>
                                        <input type="text" :name="'channels[' + idx + '][description]'" x-model="channel.description" class="gpa-control mt-1 text-xs py-1">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet">Petunjuk Transfer untuk Klien:</label>
                                        <textarea :name="'channels[' + idx + '][instructions]'" x-model="channel.instructions" rows="2" class="gpa-control mt-1 text-xs py-1"></textarea>
                                    </div>

                                    {{-- Input Unggah Foto / Barcode Khusus QRIS atau Logo Kanal --}}
                                    <div class="mt-1 rounded-xl border border-dashed border-line-soft bg-surface-muted/40 p-3">
                                        <label class="block text-[10px] font-bold uppercase text-ink-quiet">
                                            <span x-text="channel.type === 'QRIS' ? 'Unggah Foto / Barcode QRIS Resmi:' : 'Unggah Foto / Logo Kanal (Opsional):'"></span>
                                        </label>

                                        {{-- Pratinjau Gambar di Mode Edit --}}
                                        <div class="mt-2 flex items-center gap-3" x-show="channel.preview_url || channel.image_url || channel.type === 'QRIS'">
                                            <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-line-soft bg-white shadow-xs">
                                                <img :src="channel.preview_url || channel.image_url || '/storage/payment-channels/qris-gpa-default.png'" 
                                                     alt="Pratinjau" 
                                                     class="h-full w-full object-contain p-1">
                                            </div>
                                            <div class="min-w-0 flex-1 text-[10px]">
                                                <p x-show="channel.preview_url" class="font-bold text-success-deep">
                                                    ✓ Foto baru siap diunggah &amp; disimpan
                                                </p>
                                                <p x-show="!channel.preview_url && channel.image_url" class="text-ink-body font-medium">
                                                    Foto aktif saat ini tersimpan di server
                                                </p>
                                                <p x-show="!channel.preview_url && !channel.image_url" class="text-ink-quiet">
                                                    Menggunakan barcode QRIS template bawaan
                                                </p>
                                            </div>
                                        </div>

                                        <input type="hidden" :name="'channels[' + idx + '][image_url]'" :value="channel.image_url || ''">

                                        <div class="mt-2">
                                            <input type="file" 
                                                   :name="'channels[' + idx + '][image]'" 
                                                   accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                                   @change="previewImage($event, channel)"
                                                   class="block w-full text-[11px] text-ink file:mr-2 file:rounded-lg file:border-0 file:bg-surface-pill file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-ink hover:file:bg-surface-disabled cursor-pointer">
                                            <p class="mt-1 text-[10px] text-ink-quiet">Format: PNG, JPG, JPEG, WEBP. Maks 10MB.</p>
                                        </div>
                                    </div>

                                    <div class="pt-1 text-right">
                                        <button type="button" @click="removeChannel(idx)" class="text-[11px] font-bold text-danger hover:underline">
                                            Hapus Kanal Ini
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft pt-4" x-show="editing">
                    <p class="text-xs text-ink-body">
                        Perubahan kanal pembayaran dan foto barcode QRIS akan langsung termutasi ke formulir pembayaran seluruh Klien secara real-time.
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editing = false"
                            class="rounded-lg border border-line-soft bg-surface px-4 py-2 text-xs font-semibold text-ink hover:bg-surface-muted transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-5 py-2 font-sans text-xs font-extrabold text-ink shadow-sub hover:bg-accent-deep transition-colors">
                            <x-gpa.icon name="check" class="h-4 w-4 text-ink" />
                            Simpan Perubahan Kanal Pembayaran &amp; Foto QRIS
                        </button>
                    </div>
                </div>
            </form>

            {{-- Modal Lightbox Pratinjau Layar Penuh Barcode QRIS --}}
            <template x-teleport="body">
                <div x-show="modalImage" x-cloak 
                    class="fixed inset-0 z-[999999] flex items-center justify-center overflow-y-auto p-4 sm:p-6 bg-black/80 backdrop-blur-md" 
                    @click.self="modalImage = null" 
                    @keydown.escape.window="modalImage = null">
                    <div class="relative m-auto flex max-h-[90vh] w-full max-w-md flex-col rounded-2xl border border-line-soft bg-surface p-5 shadow-2xl overflow-hidden"
                        @click.stop>
                        <div class="relative flex items-center justify-between pb-3 border-b border-line-soft shrink-0">
                            <div class="text-left">
                                <span class="rounded bg-accent/20 px-2 py-0.5 text-[10px] font-bold text-accent uppercase">QRIS Standar Nasional (ASPI)</span>
                                <h3 class="text-sm font-bold text-ink mt-0.5" x-text="modalTitle || 'Barcode QRIS Resmi GPA'"></h3>
                            </div>
                            <button type="button" @click="modalImage = null" 
                                class="rounded-lg p-1.5 text-ink-quiet hover:bg-surface-muted hover:text-ink transition-colors"
                                title="Tutup Modal">
                                <x-gpa.icon name="x" class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto py-3 flex flex-col items-center justify-center">
                            <div class="bg-white p-3 rounded-xl shadow-inner border border-line-soft flex items-center justify-center max-w-full">
                                <img :src="modalImage" 
                                    onerror="this.onerror=null;this.src='/storage/payment-channels/qris-gpa-default.png'"
                                    alt="Barcode QRIS Full" class="max-h-[46vh] sm:max-h-[50vh] w-auto max-w-full object-contain rounded-lg">
                            </div>
                            <p class="mt-2 text-center text-xs text-ink-quiet">
                                Pindai barcode langsung dari aplikasi m-banking atau e-wallet
                            </p>
                        </div>

                        <div class="pt-3 border-t border-line-soft flex items-center justify-end gap-2.5 shrink-0">
                            <a :href="modalImage" download="QRIS-AgroOrder-GPA.png" 
                                class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-xs font-bold text-ink hover:bg-accent-deep transition-colors shadow-sub">
                                <x-gpa.icon name="download" class="h-4 w-4" /> Unduh Gambar Barcode
                            </a>
                            <button type="button" @click="modalImage = null" 
                                class="rounded-lg border border-line-soft bg-surface-muted px-4 py-2 text-xs font-semibold text-ink hover:bg-line-soft transition-colors">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </section>

        {{-- Audit trail --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card border border-line">
            <header class="space-y-4 border-b border-line pb-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-bold text-ink">Tabel Audit Trail Forensik &amp; Riwayat Aksi Direksi / Admin</h2>
                        <p class="text-xs text-ink-subtle mt-0.5">Pencatatan kriptografis seluruh aktivitas operasional, verifikasi, dan tata kelola sistem</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="exportAudit()"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink hover:bg-surface-muted transition-colors shadow-sm">
                            <x-gpa.icon name="download" class="h-3.5 w-3.5 text-ink-subtle" />
                            Ekspor CSV
                        </button>
                        <button type="button" @click="verifyIntegrity()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-brand hover:bg-brand-hover px-3.5 py-1.5 text-xs font-bold text-surface transition-colors shadow-sm">
                            <x-gpa.icon name="shield" class="h-3.5 w-3.5" />
                            Uji Integritas
                        </button>
                    </div>
                </div>

                {{-- Filter & Search Bar --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <div class="flex flex-wrap items-center gap-2.5 flex-1 min-w-0">
                        <div class="relative flex items-center min-w-[220px] max-w-xs flex-1">
                            <x-gpa.icon name="search" class="pointer-events-none absolute left-3 h-4 w-4 text-ink-subtle" />
                            <input id="audit-search" type="search" x-model="search" @input="onSearchInput()"
                                placeholder="Cari Dokumen / Hash / Aktor..."
                                class="w-full rounded-xl border border-line bg-surface-muted/60 py-2 pl-9 pr-3 text-xs font-semibold text-ink placeholder:text-ink-subtle focus:bg-surface focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition-all">
                        </div>

                        <div class="relative">
                            <select x-model="actionFilter" @change="page = 1"
                                class="rounded-xl border border-line bg-surface-muted/60 py-2 pl-3 pr-8 text-xs font-semibold text-ink focus:bg-surface focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer">
                                <option value="all">Semua Kategori Aksi ({{ count($auditRows) }})</option>
                                @foreach ($auditActions as $auditAction)
                                    <option value="{{ $auditAction }}">{{ $auditAction }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" x-show="search || actionFilter !== 'all'" @click="resetFilters()"
                            class="rounded-xl border border-line bg-surface px-3 py-2 text-xs font-semibold text-ink-subtle hover:text-ink hover:bg-surface-muted transition-colors">
                            Reset Filter
                        </button>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-ink-subtle shrink-0">
                        <span>Tampilkan:</span>
                        <select x-model.number="perPage" @change="page = 1"
                            class="rounded-lg border border-line bg-surface-muted/40 px-2 py-1 text-xs font-semibold text-ink focus:border-brand focus:outline-none cursor-pointer">
                            <option value="10">10 baris</option>
                            <option value="25">25 baris</option>
                            <option value="50">50 baris</option>
                        </select>
                    </div>
                </div>
            </header>

            {{-- Tabel Pas Sesuai Lebar Container (Dengan Horizontal Scroll Internal jika layar sempit) --}}
            <div class="rounded-xl border border-line bg-surface overflow-x-auto">
                <table class="w-full min-w-[720px] table-fixed text-left text-xs border-collapse">
                    <caption class="sr-only">Tabel Audit Trail Forensik &amp; Riwayat Aksi Direksi / Admin</caption>
                    <thead>
                        <tr class="border-b border-line bg-surface-muted/60">
                            <th scope="col" class="w-[14%] px-3.5 py-3 text-[10px] font-bold uppercase tracking-wider text-ink-subtle">
                                Waktu
                            </th>
                            <th scope="col" class="w-[17%] px-3.5 py-3 text-[10px] font-bold uppercase tracking-wider text-ink-subtle">
                                Aktor &amp; ID
                            </th>
                            <th scope="col" class="w-[18%] px-3.5 py-3 text-[10px] font-bold uppercase tracking-wider text-ink-subtle">
                                Kategori Aksi
                            </th>
                            <th scope="col" class="w-[13%] px-3.5 py-3 text-[10px] font-bold uppercase tracking-wider text-ink-subtle">
                                Dokumen
                            </th>
                            <th scope="col" class="w-[26%] px-3.5 py-3 text-[10px] font-bold uppercase tracking-wider text-ink-subtle">
                                Rincian Perubahan
                            </th>
                            <th scope="col" class="w-[12%] px-3.5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-ink-subtle">
                                Status Hash
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        <template x-for="(row, idx) in paginatedRows()" :key="row.hash + '_' + idx">
                            <tr :class="row.blocked ? 'bg-red-50/40 align-top transition-colors hover:bg-surface-muted/40' : 'align-top transition-colors hover:bg-surface-muted/40'">
                                <td class="px-3.5 py-3 align-top">
                                    <p class="font-bold text-ink truncate" x-text="row.date"></p>
                                    <p class="mt-0.5 text-2xs text-ink-subtle font-mono truncate" x-text="row.time"></p>
                                </td>
                                <td class="px-3.5 py-3 align-top">
                                    <p class="font-bold text-ink truncate" :title="row.actor" x-text="row.actor"></p>
                                    <p class="mt-0.5 text-2xs text-ink-subtle truncate" :title="row.actor_meta" x-text="row.actor_meta"></p>
                                </td>
                                <td class="px-3.5 py-3 align-top">
                                    <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-2xs font-bold border truncate max-w-full"
                                        :class="actionBadgeClass(row.action_tone)" :title="row.action" x-text="row.action">
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 align-top">
                                    <p class="font-bold text-ink truncate" :title="row.document" x-text="row.document"></p>
                                </td>
                                <td class="px-3.5 py-3 align-top">
                                    <p class="text-xs leading-relaxed text-ink-muted line-clamp-2" :title="row.detail" x-text="row.detail"></p>
                                </td>
                                <td class="px-3.5 py-3 text-right align-top">
                                    <p class="font-mono text-xs font-bold text-brand-line truncate" :title="row.hash" x-text="row.hash"></p>
                                    <p class="mt-0.5 text-2xs font-bold truncate" :class="row.blocked ? 'text-danger' : 'text-ink-subtle'" x-text="row.block"></p>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="visibleCount() === 0" x-cloak>
                            <td colspan="6" class="px-4 py-8 text-center text-xs text-ink-subtle">
                                Tidak ada entri audit yang cocok dengan filter atau kata kunci pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-bold text-brand-line">
                        Status Ledger: Rantai Blok Terverifikasi 100%
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <span class="text-xs text-ink-muted" x-show="visibleCount() > 0">
                        Menampilkan <strong class="text-ink" x-text="startIndex()"></strong>-<strong class="text-ink" x-text="endIndex()"></strong> dari <strong class="text-ink" x-text="visibleCount()"></strong> data
                    </span>

                    {{-- Tombol Navigasi Pagination --}}
                    <div class="flex items-center gap-1" x-show="totalPages() > 1">
                        <button type="button" @click="prevPage()" :disabled="page === 1"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-line bg-surface text-ink-subtle transition hover:bg-surface-muted hover:text-ink disabled:opacity-40 disabled:cursor-not-allowed">
                            <span class="sr-only">Sebelumnya</span>
                            <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                        </button>

                        <template x-for="p in pageNumbers()">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="inline-flex h-8 w-8 items-center justify-center text-xs text-ink-subtle">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button" @click="setPage(p)"
                                        :class="page === p ? 'bg-brand text-surface font-bold border-brand' : 'bg-surface text-ink border-line hover:bg-surface-muted'"
                                        class="inline-flex h-8 min-w-[32px] px-2 items-center justify-center rounded-lg border text-xs transition"
                                        x-text="p">
                                    </button>
                                </template>
                            </div>
                        </template>

                        <button type="button" @click="nextPage()" :disabled="page === totalPages()"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-line bg-surface text-ink-subtle transition hover:bg-surface-muted hover:text-ink disabled:opacity-40 disabled:cursor-not-allowed">
                            <span class="sr-only">Berikutnya</span>
                            <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </footer>
        </section>

    </div>
@endsection

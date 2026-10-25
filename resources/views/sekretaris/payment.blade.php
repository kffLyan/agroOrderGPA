@extends('layouts.sekretaris')

@section('title', 'Verifikasi Pembayaran')

@section('content')
    @php
        $user = auth()->user();
        $operatorCode = $user ? 'SK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'SK-001';
        $queueRows = $queueRows ?? [];
        $auditRows = $auditRows ?? [];
        $default = $queueRows[0] ?? null;
    @endphp

    <div class="space-y-6" x-data="secretaryPayments(@js($queueRows), [
            {key: 'all', label: 'Semua Bukti'},
            {key: 'bank', label: 'Transfer Bank BCA / Mandiri'},
            {key: 'qris', label: 'QRIS'},
            {key: 'giro', label: 'Giro / Tempo B2B'}
        ], @js($operatorCode), {{ count($auditRows) }})"
        @gpa:search.window="query = $event.detail">
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0">
                <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                    <x-gpa.icon name="banknote" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    Pemeriksaan Pembayaran &amp; Kas
                </p>

                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Verifikasi Bukti Pembayaran &amp;<br />
                    Pencocokan Rekening Bank
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-5 text-ink-body">
                    Pemeriksaan dan persetujuan bukti transfer bank / QRIS dari pembeli untuk
                    <span class="font-semibold text-ink">memastikan uang pembayaran telah benar-benar masuk ke rekening resmi GPA.</span>
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-3 rounded-xl bg-surface px-4 py-3 shadow-card outline outline-1 outline-line-hair">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                <span class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Status Rekening:</span>
                <span class="gpa-meta-lg font-bold tracking-[0.88px] text-success-deep">Rekening Bank BCA / Mandiri</span>
            </div>
        </section>

        <section class="flex flex-col gap-3 rounded-xl bg-brand-deep p-4 outline outline-2 outline-accent-deep/40 sm:flex-row sm:items-center">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent-deep">
                <x-gpa.icon name="receipt" class="h-5 w-5 shrink-0 text-ink" />
            </span>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-accent-deep">
                        Ketentuan Pemeriksaan Pembayaran:
                    </p>
                    <span class="rounded bg-success/40 px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                        Wajib Diperiksa Admin
                    </span>
                </div>

                <p class="mt-1.5 text-sm leading-5 text-white">
                    Bukti transfer yang diunggah klien
                    <span class="font-semibold text-accent-deep underline decoration-accent-deep underline-offset-2">
                        belum langsung dianggap lunas otomatis
                    </span>
                    . Sekretaris wajib mencocokkan nominal pada slip dengan mutasi rekening koran bank sebelum menyetujui transaksi.
                </p>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        Menunggu Verifikasi
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-warning-soft/40">
                        <x-gpa.icon name="clock" class="h-[15px] w-[15px] shrink-0 text-warning-caution" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-baseline gap-2">
                        <span class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                            {{ $pendingCount ?? 0 }}
                        </span>
                        <span class="gpa-note text-ink-body">Bukti Transfer</span>
                    </p>
                    <p class="mt-1 font-mono text-sm font-bold tracking-[0.88px] text-ink">Rp {{ number_format($pendingTotal ?? 0, 0, ',', '.') }}</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">
                        Perlu Tindakan
                    </p>
                    <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-warning-soft text-warning-deep outline outline-1 outline-warning/60">
                        Menunggu Review
                    </span>
                </div>
            </article>

            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        Disetujui Hari Ini
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent/60">
                        <x-gpa.icon name="check-circle" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-baseline gap-2">
                        <span class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-success-deep">
                            {{ $verifiedCount ?? 0 }}
                        </span>
                        <span class="gpa-note text-ink-body">Transaksi Sah</span>
                    </p>
                    <p class="mt-1 font-mono text-sm font-bold tracking-[0.88px] text-success-deep">Rp {{ number_format($verifiedTotal ?? 0, 0, ',', '.') }}</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">
                        Buku Kas Sah
                    </p>
                    <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-accent text-success-deep outline outline-1 outline-success-deep">
                        Lunas Tercatat
                    </span>
                </div>
            </article>

            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        Bukti Belum Sesuai
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-danger-soft/60">
                        <x-gpa.icon name="alert-triangle" class="h-[15px] w-[15px] shrink-0 text-danger" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-baseline gap-2">
                        <span class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-danger">
                            {{ $rejectedCount ?? 0 }}
                        </span>
                        <span class="gpa-note text-ink-body">Transaksi</span>
                    </p>
                    <p class="mt-1 gpa-note text-danger">Nominal tidak sesuai / belum masuk</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-warning-caution">
                        Perlu Konfirmasi
                    </p>
                    <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-surface-pill text-ink-quiet outline outline-1 outline-line-board">
                        Tertahan
                    </span>
                </div>
            </article>

            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        Total Pembayaran Masuk
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-track">
                        <x-gpa.icon name="banknote" class="h-[15px] w-[15px] shrink-0 text-ink" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-baseline gap-2">
                        <span class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] font-mono text-2xl text-brand">
                            Rp {{ number_format(($verifiedTotal ?? 0) + ($pendingTotal ?? 0), 0, ',', '.') }}
                        </span>
                    </p>
                    <p class="mt-1 gpa-note text-ink-body">Rekonsiliasi mutasi kas masuk resmi GPA</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">
                        Cocok 100%
                    </p>
                    <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-accent/60 text-success-deep outline outline-1 outline-accent-deep">
                        Saldo Cocok
                    </span>
                </div>
            </article>
        </div>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_30rem]">
            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-track">
                            <x-gpa.icon name="wallet" class="h-[18px] w-[18px] shrink-0 text-ink" />
                        </span>

                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold leading-6 text-ink">Daftar Antrean Bukti Pembayaran</h2>
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">{{ count($queueRows) }} Bukti Transfer Menunggu Pemeriksaan</p>
                        </div>
                    </div>

                    <button type="button" @click="sync()"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-surface-shell px-3 py-2 outline outline-1 outline-line-hair transition-colors hover:bg-surface-track gpa-micro-bold uppercase tracking-[1.08px] text-ink-body">
                        <x-gpa.icon name="refresh" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                        Pembaruan
                        <span class="font-mono text-ink-quiet">Otomatis</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="setChannel('all')"
                        :class="channel === 'all' ? 'bg-brand text-white' : 'bg-surface-track text-ink-body hover:bg-surface-disabled'"
                        class="rounded-full px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        Semua Bukti
                        <span class="font-mono opacity-80" x-text="'(' + filterCount('all') + ')'">{{ count($queueRows) }}</span>
                    </button>
                    <button type="button" @click="setChannel('bank')"
                        :class="channel === 'bank' ? 'bg-brand text-white' : 'bg-surface-track text-ink-body hover:bg-surface-disabled'"
                        class="rounded-full px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        Transfer Bank BCA / Mandiri
                        <span class="font-mono opacity-80" x-text="'(' + filterCount('bank') + ')'">{{ count($queueRows) }}</span>
                    </button>
                    <button type="button" @click="setChannel('qris')"
                        :class="channel === 'qris' ? 'bg-brand text-white' : 'bg-surface-track text-ink-body hover:bg-surface-disabled'"
                        class="rounded-full px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        QRIS
                        <span class="font-mono opacity-80" x-text="'(' + filterCount('qris') + ')'">{{ count($queueRows) }}</span>
                    </button>
                    <button type="button" @click="setChannel('giro')"
                        :class="channel === 'giro' ? 'bg-brand text-white' : 'bg-surface-track text-ink-body hover:bg-surface-disabled'"
                        class="rounded-full px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        Giro / Tempo B2B
                        <span class="font-mono opacity-80" x-text="'(' + filterCount('giro') + ')'">{{ count($queueRows) }}</span>
                    </button>
                </div>

                <ul class="flex flex-col gap-2.5">
                    @forelse ($queueRows as $row)
                        <li>
                            <button type="button" @click="inspect(@js($row))" :class="rowClass(@js($row))"
                                class="flex w-full flex-col gap-2 rounded-xl p-4 text-left transition-colors">
                                <span class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="rounded bg-brand px-2 py-1 gpa-micro-bold tracking-[1.08px] text-accent">{{ $row['id'] }}</span>
                                        <span class="font-mono text-2xs font-medium tracking-[0.88px] text-ink-quiet">{{ $row['invoice'] }}</span>
                                    </span>

                                    <span @if (! $loop->first) style="display:none" @endif
                                        x-show="paymentId === @js($row['id'])"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-success px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                                        Inspeksi Aktif
                                    </span>

                                    <span style="display:none" x-show="isSettled(@js($row))"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep">
                                        Settled Lunas
                                    </span>

                                    <span style="display:none" x-show="isRejected(@js($row))"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-danger-soft px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-danger-ink outline outline-1 outline-danger/60">
                                        Ditolak
                                    </span>

                                    <span @if ($loop->first) style="display:none" @endif
                                        x-show="paymentId !== @js($row['id']) && ! isSettled(@js($row)) && ! isRejected(@js($row))"
                                        class="rounded-full bg-surface-pill px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body outline outline-1 outline-line-board">
                                        Pending
                                    </span>
                                </span>

                                <span class="flex flex-wrap items-end justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block font-sans text-sm font-bold leading-5 text-ink">{{ $row['client'] }}</span>
                                        <span class="mt-0.5 flex items-center gap-1.5 font-mono text-2xs font-medium tracking-[0.88px] text-ink-quiet">
                                            <x-gpa.icon name="clock" class="h-3 w-3 shrink-0" />
                                            {{ $row['channel_label'] }} &bull; Jam {{ $row['time'] }} WIB
                                        </span>
                                    </span>

                                    <span class="text-right">
                                        <span class="block font-mono text-sm font-bold tracking-[0.88px] {{ $row['status'] === 'inspect' ? 'text-brand' : 'text-ink' }}">
                                            {{ $row['amount_label'] }}
                                        </span>
                                        <span @class([
                                            'mt-0.5 block gpa-micro-bold uppercase tracking-[1.08px]',
                                            $row['status'] === 'inspect' ? 'text-success-deep' : 'text-ink-quiet',
                                        ])>{{ $row['evidence'] }}</span>
                                    </span>
                                </span>
                            </button>
                        </li>
                    @empty
                        <li class="rounded-xl border border-line-soft bg-surface-shell p-6 text-center text-sm text-ink-quiet">
                            Tidak ada antrean pembayaran yang perlu diverifikasi saat ini.
                        </li>
                    @endforelse
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-surface-shell px-4 py-3">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-body"
                        x-text="'Memori Antrean: ' + visibleRows().length + ' dari ' + queue.length + ' Ditampilkan'">
                        Menampilkan seluruh bukti pembayaran
                    </p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Urutan: Bukti Pertama Masuk Diproses Dahulu</p>
                </div>
            </section>

            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card outline outline-2 outline-success min-w-0 overflow-hidden">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold leading-6 text-ink">Pemeriksaan Bukti &amp; Konfirmasi Lunas</h2>
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                <span x-text="inspectionRef">Ref Kasir: {{ $default['id'] ?? '' }}</span><span> // Konfirmasi Kasir {{ $operatorCode }}</span>
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-accent px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep">
                        <x-gpa.icon name="shield" class="h-3 w-3 shrink-0" />
                        Siap Diperiksa
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-hair sm:grid-cols-4">
                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Nama Pembeli / Klien</p>
                        <p class="mt-1 font-sans text-sm font-bold leading-5 text-ink" x-text="field(selected, 'client_short')">{{ $default['client_short'] ?? '' }}</p>
                    </div>

                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Nomor Faktur / Pesanan</p>
                        <p class="mt-1 font-mono text-xs font-bold tracking-[0.88px] text-ink" x-text="field(selected, 'invoice')">{{ $default['invoice'] ?? '' }}</p>
                    </div>

                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Jumlah Pembayaran</p>
                        <p class="mt-1 font-mono text-sm font-bold tracking-[0.88px] text-brand" x-text="field(selected, 'amount_label')">{{ $default['amount_label'] ?? '' }}</p>
                    </div>

                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Keterangan</p>
                        <p class="mt-1 font-sans text-sm font-bold leading-5 text-warning-caution" x-text="field(selected, 'due')">{{ $default['due'] ?? '' }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 min-w-0">
                    <div class="flex flex-col gap-3 rounded-xl bg-surface p-3 outline outline-1 outline-line-hair min-w-0 overflow-hidden">
                        <div class="flex items-center justify-between gap-2 min-w-0">
                            <div class="flex min-w-0 items-center gap-2">
                                <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-ink" />
                                <p class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink truncate">Foto Bukti Transfer Klien</p>
                            </div>
                            <span class="shrink-0 rounded bg-surface-track px-2 py-1 gpa-micro-bold tracking-[1.08px] text-ink-body"
                                x-text="field(selected, 'evidence_type')">{{ $default['evidence_type'] ?? '' }}</span>
                        </div>

                        <div class="flex flex-col items-center gap-2.5 rounded-lg bg-brand-deep p-3 text-center outline outline-1 outline-success min-w-0 w-full overflow-hidden">
                            {{-- Pratinjau gambar jika berkas tersedia --}}
                            <template x-if="selected && (selected.evidence_url || selected.proof_url) && selected.is_image">
                                <div class="group relative flex w-full cursor-pointer justify-center py-0.5" @click="openProof(selected)" title="Klik untuk perbesar layar penuh">
                                    <img :src="selected.evidence_url || selected.proof_url" alt="Pratinjau Bukti Transfer"
                                        class="h-28 w-auto max-w-full rounded-md border border-accent/30 bg-black/50 object-contain shadow-sm transition-transform duration-150 group-hover:scale-105" />
                                    <div class="absolute inset-0 flex items-center justify-center rounded-md bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                        <span class="flex items-center gap-1 rounded bg-surface/90 px-2 py-1 text-2xs font-bold text-ink shadow">
                                            <x-gpa.icon name="eye" class="h-3 w-3 text-brand" /> Perbesar
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!selected || !(selected.evidence_url || selected.proof_url) || !selected.is_image">
                                <x-gpa.icon name="receipt" class="h-6 w-6 shrink-0 text-accent" />
                            </template>

                            <div class="w-full min-w-0 px-1">
                                <p class="block w-full min-w-0 truncate text-center font-mono text-2xs font-bold tracking-[0.88px] text-accent"
                                    :title="field(selected, 'evidence_file')"
                                    x-text="field(selected, 'evidence_file')">{{ $default['evidence_file'] ?? '' }}</p>
                            </div>

                            <dl class="w-full space-y-0.5 text-left font-mono text-[10px] leading-4 text-white/80 min-w-0">
                                <div class="truncate">
                                    <dt class="inline text-white/50">Pengirim: </dt>
                                    <dd class="inline text-white font-medium" x-text="field(selected, 'slip_sender')">{{ $default['slip_sender'] ?? '' }}</dd>
                                </div>
                                <div class="truncate">
                                    <dt class="inline text-white/50">Rek Asal: </dt>
                                    <dd class="inline text-white font-medium" x-text="field(selected, 'slip_account')">{{ $default['slip_account'] ?? '' }}</dd>
                                </div>
                                <div class="truncate">
                                    <dt class="inline text-white/50">Nominal: </dt>
                                    <dd class="inline font-bold text-accent" x-text="field(selected, 'slip_nominal')">{{ $default['slip_nominal'] ?? '' }}</dd>
                                </div>
                                <div class="truncate">
                                    <dt class="inline text-white/50">Stempel: </dt>
                                    <dd class="inline text-white/90" x-text="field(selected, 'slip_stamp')">{{ $default['slip_stamp'] ?? '' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-1 min-w-0">
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep truncate"
                                x-text="field(selected, 'evidence_note')">{{ $default['evidence_note'] ?? '' }}</p>
                            <button type="button" @click="openProof(selected)"
                                class="shrink-0 font-sans text-xs font-bold text-success-deep underline decoration-success-deep/40 underline-offset-2 hover:text-brand cursor-pointer">
                                Lihat Ukuran Penuh
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 rounded-xl bg-success-soft/40 p-3 outline outline-1 outline-success min-w-0 overflow-hidden">
                        <div class="flex items-center justify-between gap-2 min-w-0">
                            <div class="flex min-w-0 items-center gap-2">
                                <x-gpa.icon name="check-circle" class="h-4 w-4 shrink-0 text-success-deep" />
                                <p class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-success-deep truncate">Catatan Mutasi Rekening Bank (Langsung)</p>
                            </div>
                            <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep"
                                x-text="mutationChip">Cocok 100%</span>
                        </div>

                        <dl class="flex flex-col gap-2 rounded-lg bg-surface p-3 min-w-0">
                            <div class="flex items-center justify-between gap-2 min-w-0">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet shrink-0">Rekening Tujuan</dt>
                                <dd class="font-mono text-2xs font-bold tracking-[0.88px] text-ink truncate text-right" x-text="field(selected, 'giro_account')">{{ $default['giro_account'] ?? '' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2 min-w-0">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet shrink-0">Waktu Transfer</dt>
                                <dd class="font-mono text-2xs font-bold tracking-[0.88px] text-ink truncate text-right" x-text="field(selected, 'giro_time')">{{ $default['giro_time'] ?? '' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2 min-w-0">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet shrink-0">Jenis Dana</dt>
                                <dd class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep truncate text-right" x-text="field(selected, 'giro_entry')">{{ $default['giro_entry'] ?? '' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2 min-w-0">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet shrink-0">Nominal Masuk</dt>
                                <dd class="font-mono text-sm font-bold tracking-[0.88px] text-brand truncate text-right" x-text="field(selected, 'giro_nominal')">{{ $default['giro_nominal'] ?? '' }}</dd>
                            </div>
                        </dl>

                        <p class="rounded-lg bg-accent/40 px-3 py-2 gpa-note font-medium text-ink-body outline outline-1 outline-accent-deep/50"
                            x-text="mutationNote">Jumlah uang di slip sama persis dengan mutasi bank.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-2.5 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-hair">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink">Panduan Pemeriksaan Pembayaran:</p>
                        <span class="rounded bg-success-soft px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-success/50"
                            x-text="progressLabel">3/3 Terpenuhi</span>
                    </div>

                    <ul class="flex flex-col gap-2">
                        <li class="flex items-start gap-2.5">
                            <button type="button" @click="toggleCheck(0)" role="checkbox"
                                :aria-checked="isChecked(0)"
                                :class="isChecked(0) ? 'bg-brand' : 'bg-surface outline outline-1 outline-line-board'"
                                class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-sm text-white transition-colors">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0" x-show="isChecked(0)" />
                            </button>

                            <p class="gpa-note leading-5 text-ink-body">
                                Nominal di slip sama dengan dana yang masuk di bank (<span class="font-mono font-bold text-ink"
                                    x-text="field(selected, 'amount_label')">{{ $default['amount_label'] ?? '' }}</span>)
                            </p>
                        </li>

                        <li class="flex items-start gap-2.5">
                            <button type="button" @click="toggleCheck(1)" role="checkbox"
                                :aria-checked="isChecked(1)"
                                :class="isChecked(1) ? 'bg-brand' : 'bg-surface outline outline-1 outline-line-board'"
                                class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-sm text-white transition-colors">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0" x-show="isChecked(1)" />
                            </button>

                            <p class="gpa-note leading-5 text-ink-body">
                                Nomor rekening tujuan tepat sesuai rekening resmi GPA (<span class="font-mono font-bold text-ink"
                                    x-text="field(selected, 'giro_account')">{{ $default['giro_account'] ?? '' }}</span>)
                            </p>
                        </li>

                        <li class="flex items-start gap-2.5">
                            <button type="button" @click="toggleCheck(2)" role="checkbox"
                                :aria-checked="isChecked(2)"
                                :class="isChecked(2) ? 'bg-brand' : 'bg-surface outline outline-1 outline-line-board'"
                                class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-sm text-white transition-colors">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0" x-show="isChecked(2)" />
                            </button>

                            <p class="gpa-note leading-5 text-ink-body">
                                Waktu transaksi valid pada catatan bank (<span class="font-mono font-bold text-ink"
                                    x-text="field(selected, 'giro_time_short')">{{ $default['giro_time_short'] ?? '' }}</span>)
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink">Catatan Pemeriksaan Sekretaris:</p>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Catatan Resmi</p>
                    </div>

                    <textarea x-model="note" rows="2"
                        class="w-full rounded-lg bg-surface p-3 text-xs leading-5 text-ink outline outline-1 outline-line-hair focus:outline-2 focus:outline-success-deep">{{ $default['note'] ?? '' }}</textarea>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <button type="button" @click="saveNote()"
                            class="inline-flex items-center gap-2 rounded-xl bg-surface-track px-4 py-2.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink transition-colors hover:bg-surface-disabled">
                            <x-gpa.icon name="save" class="h-[15px] w-[15px] shrink-0" />
                            Simpan Catatan Audit
                        </button>

                        <button type="button" @click="reject()"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl px-4 gpa-meta-lg font-bold tracking-[0.88px] text-danger outline outline-2 outline-danger transition-colors hover:bg-danger-soft">
                            <x-gpa.icon name="x" class="h-4 w-4 shrink-0" />
                            Tolak Bukti Pembayaran
                        </button>

                        <button type="button" @click="approve()" :disabled="! checkComplete || ! selected"
                            :class="checkComplete
                                ? 'bg-accent-deep text-ink hover:bg-accent'
                                : 'cursor-not-allowed bg-surface-disabled text-ink-quiet opacity-70'"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl px-4 gpa-meta-lg font-bold tracking-[0.88px] transition-colors sm:flex-none">
                            <x-gpa.icon name="check" class="h-4 w-4 shrink-0" />
                            Setujui &amp; Tandai Lunas
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-track">
                        <x-gpa.icon name="scale" class="h-[18px] w-[18px] shrink-0 text-ink" />
                    </span>

                    <div class="min-w-0">
                        <h2 class="font-sans text-lg font-bold leading-6 text-ink">Riwayat Pembayaran Terakhir</h2>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Pencatatan kas resmi dan riwayat persetujuan pembayaran.</p>
                    </div>
                </div>

                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-surface-track px-3 py-1.5 outline outline-1 outline-surface-disabled">
                    <x-gpa.icon name="lock" class="h-[15px] w-[15px] shrink-0 text-ink-body" />
                    <span class="font-mono text-2xs font-bold tracking-[0.88px] text-ink-body">Kode Bukti: VERIF-KAS-{{ date('Ym') }}</span>
                </span>
            </div>

            <div class="gpa-scroll-x rounded-xl outline outline-1 outline-line-board/60">
                <table class="w-full border-collapse text-left">
                    <thead class="border-b border-line-soft bg-surface-shell">
                        <tr>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Waktu</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">ID Bayar</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">No. Faktur / Pesanan</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Klien</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Nominal</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Status &amp; Hasil</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Petugas</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Catatan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-soft">
                        @forelse ($auditRows as $row)
                            <tr @class(['transition-colors hover:bg-surface-shell/50', $row['status'] === 'rejected' ? 'bg-danger-soft/20' : ''])>
                                <td class="px-4 py-4 font-mono text-2xs font-medium tracking-[0.88px] text-ink-quiet">
                                    {{ $row['timestamp'] }}
                                </td>

                                <td class="px-4 py-4 font-mono text-xs font-bold tracking-[0.88px] text-ink">{{ $row['id'] }}</td>

                                <td class="px-4 py-4 font-mono text-2xs font-medium tracking-[0.88px] text-ink-body">{{ $row['invoice'] }}</td>

                                <td class="px-4 py-4 font-sans text-sm font-semibold leading-5 text-ink">{{ $row['client'] }}</td>

                                <td class="px-4 py-4 font-mono text-xs font-bold tracking-[0.88px] {{ $row['amount_class'] }}">
                                    {{ $row['amount_label'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                        $row['status_class'],
                                    ])>{{ $row['status_label'] }}</span>
                                </td>

                                <td class="px-4 py-4 font-mono text-2xs font-bold tracking-[0.88px] text-ink-body">{{ $row['operator'] }}</td>

                                <td @class([
                                    'px-4 py-4 gpa-note leading-5',
                                    $row['status'] === 'rejected' ? 'text-danger' : 'text-ink-body',
                                ])>{{ $row['note'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-ink-quiet">
                                    Belum ada riwayat pembayaran yang tercatat dalam sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft pt-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + {{ count($auditRows) }} + ' dari ' + auditTotal + ' Transaksi Rekonsiliasi Hari Ini'">
                    Menampilkan {{ count($auditRows) }} dari {{ count($auditRows) }} Transaksi Rekonsiliasi Hari Ini
                </p>

                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="goToPage(auditPage - 1)"
                        class="rounded-lg px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body transition-colors hover:bg-surface-track">
                        Sebelumnya
                    </button>

                    <button type="button" @click="goToPage(1)"
                        :class="auditPage === 1 ? 'bg-brand text-white' : 'bg-surface-track text-ink-body hover:bg-surface-disabled'"
                        class="h-8 min-w-8 rounded-lg px-2 gpa-micro-bold tracking-[1.08px] transition-colors">1</button>

                    <button type="button" @click="goToPage(auditPage + 1)"
                        class="rounded-lg px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body transition-colors hover:bg-surface-track">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </section>

        {{-- Modal Pratinjau Bukti Pembayaran / Transfer Ukuran Penuh --}}
        <div x-cloak x-show="proofModalOpen"
            @keydown.escape.window="proofModalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog" aria-modal="true">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-ink/75 backdrop-blur-xs transition-opacity"
                @click="proofModalOpen = false"></div>

            <div class="relative flex w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-surface shadow-pop ring-1 ring-ink/10"
                x-show="proofModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-line-hair bg-surface-shell px-5 py-3.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent text-ink">
                            <x-gpa.icon name="receipt" class="h-4 w-4" />
                        </span>
                        <div class="flex flex-col min-w-0">
                            <h3 class="font-sans text-sm font-bold text-ink truncate" x-text="proofModalTitle">
                                Bukti Transfer Klien
                            </h3>
                            <p class="text-[11px] text-ink-body truncate">
                                <span x-text="proofModalSubtitle"></span> &bull; Nominal: <strong class="text-brand font-mono" x-text="proofModalNominal"></strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="proofModalOpen = false"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-ink-body transition-colors hover:bg-surface-muted hover:text-ink cursor-pointer">
                        <x-gpa.icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                {{-- Modal Body Image Container --}}
                <div class="flex max-h-[70vh] min-h-[300px] items-center justify-center overflow-auto bg-black/95 p-4">
                    <template x-if="proofModalUrl && proofModalIsImage">
                        <img :src="proofModalUrl" alt="Foto Fisik Bukti Transfer"
                            class="max-h-[65vh] w-auto max-w-full rounded-lg object-contain shadow-2xl">
                    </template>
                    <template x-if="proofModalUrl && !proofModalIsImage">
                        <div class="flex flex-col items-center gap-3 p-8 text-center text-white">
                            <x-gpa.icon name="file-text" class="h-16 w-16 text-accent" />
                            <p class="font-bold text-sm" x-text="proofModalFileName">Dokumen PDF Bukti Transfer</p>
                            <p class="text-xs text-white/70">Format dokumen non-gambar. Silakan buka dokumen di tab baru untuk menelaah.</p>
                            <a :href="proofModalUrl" target="_blank"
                                class="mt-2 inline-flex items-center gap-2 rounded-xl bg-accent px-4 py-2.5 text-xs font-bold text-ink hover:bg-accent-deep">
                                <x-gpa.icon name="eye" class="h-4 w-4 text-ink" />
                                Buka Dokumen PDF di Tab Baru
                            </a>
                        </div>
                    </template>
                    <template x-if="!proofModalUrl">
                        <div class="flex flex-col items-center gap-2 p-8 text-center text-white/80">
                            <x-gpa.icon name="alert-triangle" class="h-12 w-12 text-warning-caution" />
                            <p class="font-bold text-sm">Pratinjau Berkas Belum Tersedia</p>
                            <p class="text-xs text-white/60">Klien belum melampirkan berkas foto fisik transfer atau file tidak ditemukan di server.</p>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer Metadata & Aksi --}}
                <div class="flex flex-col gap-3 border-t border-line-hair bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col text-xs text-ink-body min-w-0">
                        <span class="truncate">Pengirim: <strong class="text-ink" x-text="proofModalSender"></strong> (Rek: <span x-text="proofModalAccount"></span>)</span>
                        <span class="truncate">Waktu Upload: <strong class="text-ink" x-text="proofModalStamp"></strong> &bull; Berkas: <span class="font-mono text-2xs" x-text="proofModalFileName"></span></span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <template x-if="proofModalUrl">
                            <div class="flex items-center gap-2">
                                <a :href="proofModalUrl" target="_blank"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-soft bg-surface px-3 py-2 text-xs font-bold text-ink shadow-xs transition-colors hover:bg-surface-muted">
                                    <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-ink-body" />
                                    <span>Buka Tab Baru</span>
                                </a>
                                <a :href="proofModalUrl" :download="proofModalFileName"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-soft bg-surface px-3 py-2 text-xs font-bold text-ink shadow-xs transition-colors hover:bg-surface-muted">
                                    <x-gpa.icon name="download" class="h-3.5 w-3.5 text-ink-body" />
                                    <span>Unduh</span>
                                </a>
                            </div>
                        </template>
                        <button type="button" @click="proofModalOpen = false"
                            class="inline-flex items-center rounded-lg bg-ink px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-brand-deep cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
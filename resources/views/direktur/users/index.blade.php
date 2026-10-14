@extends('layouts.direktur')

@section('title', 'Kelola Akun Pengguna & RBAC // Matriks Otorisasi')

@section('content')
    @php
        $currentUser = auth()->user();

        $directoryRows = [];
        foreach ($allUsers as $u) {
            $roleKey = match (strtoupper($u->role)) {
                'DIREKTUR'    => 'director',
                'SEKRETARIS'  => 'admin',
                'KOORDINATOR' => 'coordinator',
                'ARMADA'      => 'driver',
                default       => 'client',
            };

            $badge = match ($roleKey) {
                'director'    => 'DIR',
                'admin'       => 'ADM',
                'coordinator' => 'KRD',
                'driver'      => 'DVR',
                default       => 'KLN',
            };

            $division = match ($roleKey) {
                'director'    => 'Direksi Pusat / Policy',
                'admin'       => 'Central Office / Keuangan',
                'coordinator' => 'STA & Timbangan Lapangan',
                'driver'      => 'Armada & Distribusi Fisik',
                default       => $u->company_name ?: 'Mitra B2B Buyer',
            };

            $hub = match ($roleKey) {
                'director'    => 'HQ Jakarta / All Hub',
                'admin'       => 'Kantor Pusat GPA',
                'coordinator' => 'STA Cianjur & Lembang',
                'driver'      => 'Pool Distribusi Bandung',
                default       => 'Jawa Barat & Jabodetabek',
            };

            $directoryRows[] = [
                'id'            => $u->id,
                'role_key'      => $roleKey,
                'badge'         => $badge,
                'name'          => $u->name,
                'code'          => strtoupper($badge) . '-' . str_pad($u->id, 3, '0', STR_PAD_LEFT),
                'role'          => $u->role,
                'division'      => $division,
                'nik'           => '3201-' . str_pad($u->id, 4, '0', STR_PAD_LEFT) . '-0001',
                'hub'           => $hub,
                'phone'         => $u->phone,
                'is_active'     => (bool) $u->is_active,
                'online'        => $u->id === $currentUser?->id,
                'login'         => $u->id === $currentUser?->id ? 'Online Now' : 'Hari ini',
                'login_meta'    => $u->email,
                'show_url'      => route('direktur.users.show', $u->id),
                'edit_url'      => route('direktur.users.edit', $u->id),
                'delete_url'    => route('direktur.users.destroy', $u->id),
                'reset_pwd_url' => route('direktur.users.reset-password', $u->id),
            ];
        }
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorAccess(@js($directoryRows))">
        @if (session('success'))
            <div class="rounded-xl border border-accent bg-accent/20 px-4 py-3 text-xs font-semibold text-success-deep shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep shrink-0" />
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-xs font-semibold text-red-800 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="alert-circle" class="h-4 w-4 text-red-600 shrink-0" />
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- Page header + ringkasan metrik --}}
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Kelola Akun Pengguna &amp; RBAC</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        Kelola Akun dan Hak Akses Pengguna
                    </h1>
                    <p class="text-xs text-ink-body max-w-2xl">
                        Pusat Kontrol Otorisasi Pengguna Berjenjang, Pembagian Peran, Token Sesi Kriptografis, &amp; Audit Akses Keamanan.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('direktur.users.rbac.export') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0" />
                        Unduh Matriks Akses (.CSV)
                    </a>
                    <a href="{{ route('direktur.users.create') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                        + Tambah Pengguna Baru
                    </a>
                </div>
            </header>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {{-- Metric 1: Total Akun --}}
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">TOTAL AKUN TERDAFTAR</h3>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                5 Role Terisolasi
                            </span>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ $totalUsers }}</p>
                        <p class="text-[11px] leading-4 text-ink-body">PENGGUNA AKTIF</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">5 Role Terisolasi</span>
                        <span class="gpa-note font-bold text-success-deep">0 DITANGGUHKAN</span>
                    </div>
                </article>

                {{-- Metric 2: Sesi Aktif --}}
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">SESI AKTIF REALTIME</h3>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                                LIVE PING
                            </span>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">14 NODE LOGIN</p>
                        <p class="text-[11px] leading-4 text-ink-body">DISTRIBUSI PERAN AKTIF</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">2 Dir | 4 Adm</span>
                        <span class="gpa-note font-bold text-success-deep">3 Koord | 5 Supir</span>
                    </div>
                </article>

                {{-- Metric 3: Otentikasi 2FA --}}
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">OTENTIKASI 2FA &amp; TOKEN</h3>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                                TERPROTEKSI
                            </span>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">100%</p>
                        <p class="text-[11px] leading-4 text-ink-body">CAKUPAN AKUN WAJIB 2FA</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">Enkripsi RSA-4096</span>
                        <span class="gpa-note font-bold text-success-deep">Biometrik Aktif</span>
                    </div>
                </article>

                {{-- Metric 4: Log Pelanggaran --}}
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">LOG PELANGGARAN HAK AKSES</h3>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-ink text-accent">
                                RULE 08 ENFORCED
                            </span>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">0 INSIDEN</p>
                        <p class="text-[11px] leading-4 text-ink-body">STATUS BULANAN (0%)</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">Rule 08 Enforcement</span>
                        <span class="gpa-note font-bold text-success-deep">TAMPER-PROOF</span>
                    </div>
                </article>
            </div>
        </section>

        {{-- RBAC matrix --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Hak Akses &amp; Wewenang 5 Role</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="syncRbacPolicy()"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded bg-surface-shell px-3 py-2 gpa-meta font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="refresh" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                        Sync RBAC Policy
                    </button>
                </div>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full min-w-[68rem] border-collapse">
                    <caption class="sr-only">Hak Akses &amp; Wewenang 5 Role</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                MODUL &amp; FITUR OPERASIONAL
                            </th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                KLIEN / BUYER
                            </th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                SEKRE / ADMIN
                            </th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                KOORDINATOR LAPANGAN
                            </th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                ARMADA / SUPIR
                            </th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                DIREKTUR / OWNER
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        {{-- Row 1: Order B2B --}}
                        <tr class="transition-colors hover:bg-surface-shell/50">
                            <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                <p class="text-[12px] leading-5 text-ink font-semibold">Katalog &amp; Buat Order B2B</p>
                                <p class="mt-0.5 text-[10px] leading-4 text-ink-body">Order PO-distribusi klien korporat dan price list kontrak</p>
                            </th>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">Read / Create</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Form WA / Manual (Create)</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold text-ink-body">No Access</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold text-ink-body">No Access</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold bg-ink text-accent">Full Monitoring (Read)</span>
                            </td>
                        </tr>

                        {{-- Row 2: Timbangan Aktual --}}
                        <tr class="transition-colors hover:bg-surface-shell/50">
                            <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                <p class="text-[12px] leading-5 text-ink font-semibold">Timbangan Aktual &amp; Netto Panen</p>
                                <p class="mt-0.5 text-[10px] leading-4 text-ink-body">Certifikasi bobot bersih timbangan elektronik</p>
                            </th>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Read-Only</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Read Riil</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">Lock &amp; Certify</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold text-ink-body">No Access</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold bg-ink text-accent">Audit Calibration (Read)</span>
                            </td>
                        </tr>

                        {{-- Row 3: Surat Jalan --}}
                        <tr class="transition-colors hover:bg-surface-shell/50">
                            <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                <p class="text-[12px] leading-5 text-ink font-semibold">Terbitkan Surat Jalan (Rule 05)</p>
                                <p class="mt-0.5 text-[10px] leading-4 text-ink-body">Dokumen Predicate legalitas pengiriman kargo</p>
                            </th>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold text-ink-body">No Access</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">Create &amp; Sign</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Read (Manifest)</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Read Digital</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold bg-ink text-accent">Read &amp; Invalidation</span>
                            </td>
                        </tr>

                        {{-- Row 4: Faktur, Bayar & Rekonsiliasi --}}
                        <tr class="transition-colors hover:bg-surface-shell/50">
                            <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                <p class="text-[12px] leading-5 text-ink font-semibold">Faktur, Bayar &amp; Rekonsiliasi Kas</p>
                                <p class="mt-0.5 text-[10px] leading-4 text-ink-body">Settlement termin dan kliring rekening korporat</p>
                            </th>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Read / Upload Bukti</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">Approve &amp; Reconcile</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold text-ink-body">No Access</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold text-ink-body">No Access</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold bg-ink text-accent">Executive Ledger Read</span>
                            </td>
                        </tr>

                        {{-- Row 5: PoD Digital --}}
                        <tr class="transition-colors hover:bg-surface-shell/50">
                            <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                <p class="text-[12px] leading-5 text-ink font-semibold">PoD Digital, Foto &amp; Catat Retur</p>
                                <p class="mt-0.5 text-[10px] leading-4 text-ink-body">Bukti penerimaan barang dan catatan sengketa</p>
                            </th>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">Digital Sign PoD</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Verify Dispute</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">Verify &amp; Stock Sync</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Upload &amp; Submit</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold bg-ink text-accent">Dispute Final Arbiter</span>
                            </td>
                        </tr>

                        {{-- Row 6: Approval Kontrak & Kunci Audit --}}
                        <tr class="transition-colors hover:bg-surface-shell/50 bg-accent/25">
                            <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                <p class="text-[12px] leading-5 text-ink font-bold">Approval Kontrak &amp; Kunci Audit</p>
                                <p class="mt-0.5 text-[10px] leading-4 text-ink-body">Otorisasi monograf final dan sealing ledger</p>
                            </th>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold text-ink">NONE (BLOCKED)</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-semibold bg-surface-track text-ink">Draft / Review</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold text-ink">NONE (BLOCKED)</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold text-ink">NONE (BLOCKED)</span>
                            </td>
                            <td class="px-3 py-4 text-center align-top">
                                <span class="inline-block rounded px-2 py-1 text-[11px] leading-4 font-bold bg-ink text-accent outline outline-1 -outline-offset-1 outline-accent">SOLE APPROVER / LOCK</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- User directory --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Direktori Pengguna &amp; Status Kredensial</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative flex items-center" for="directory-search">
                        <span class="sr-only">Cari NIK, Nama, atau ID...</span>
                        <x-gpa.icon name="search"
                            class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                        <input id="directory-search" type="search" x-model="userSearch"
                            placeholder="Cari NIK, Nama, atau ID..."
                            class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </label>

                    <button type="button" @click="setRole('all')"
                        :class="roleClass('all')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Semua Role ({{ $totalUsers }})
                    </button>
                    <button type="button" @click="setRole('director')"
                        :class="roleClass('director')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Direktur
                    </button>
                    <button type="button" @click="setRole('admin')"
                        :class="roleClass('admin')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Sekre/Admin
                    </button>
                    <button type="button" @click="setRole('coordinator')"
                        :class="roleClass('coordinator')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Koordinator
                    </button>
                    <button type="button" @click="setRole('driver')"
                        :class="roleClass('driver')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Supir
                    </button>
                    <button type="button" @click="setRole('client')"
                        :class="roleClass('client')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Klien
                    </button>
                </div>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">Direktori Pengguna &amp; Status Kredensial</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                ID &amp; NAMA LENGKAP
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                ROLE &amp; DIVISI
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                NIK / IDENTITAS RESMI
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                WILAYAH / HUB
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                STATUS 2FA
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                LOGIN TERAKHIR
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-right">
                                AKSI OTORITAS
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @foreach ($directoryRows as $row)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50"
                                x-show="matchesUser(@js($row))">
                                <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                    <div class="flex items-start gap-2.5">
                                        <span @class([
                                            'mt-0.5 shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-micro',
                                            'bg-ink text-accent' => $row['role_key'] === 'director',
                                            'bg-surface-track text-ink-body' => $row['role_key'] !== 'director',
                                        ])>{{ $row['badge'] }}</span>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-bold leading-5 text-ink">{{ $row['name'] }}</p>
                                            <p class="mt-0.5 gpa-note font-semibold text-ink-body">{{ $row['code'] }}</p>
                                        </div>
                                    </div>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[12px] font-semibold leading-5 text-ink">{{ $row['role'] }}</p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['division'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta font-semibold text-ink">{{ $row['nik'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta text-ink">{{ $row['hub'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span @class([
                                        'inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta',
                                        'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep' => $row['role_key'] === 'director',
                                        'bg-surface-track text-ink-body' => $row['role_key'] !== 'director',
                                    ])>2FA Aktif</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p @class([
                                        'gpa-meta font-bold',
                                        'text-success-deep' => $row['online'],
                                        'text-ink' => ! $row['online'],
                                    ])>{{ $row['login'] }}</p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['login_meta'] }}</p>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <a href="{{ $row['show_url'] }}"
                                            title="Lihat Detail Akun"
                                            class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                            Detail
                                        </a>
                                        <a href="{{ $row['edit_url'] }}"
                                            title="Ubah Data &amp; Hak Akses"
                                            class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="settings" class="h-3 w-3 shrink-0" />
                                            Edit
                                        </a>
                                        <form action="{{ $row['reset_pwd_url'] }}" method="POST" class="inline"
                                            onsubmit="return confirm('Reset kata sandi pengguna ini?')">
                                            @csrf
                                            <button type="submit"
                                                title="Reset Sandi ke password123"
                                                class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                                <x-gpa.icon name="key" class="h-3 w-3 shrink-0" />
                                                Reset
                                            </button>
                                        </form>
                                        @if ($row['id'] !== auth()->id())
                                            <form action="{{ $row['delete_url'] }}" method="POST" class="inline"
                                                onsubmit="return confirm('Hapus akun pengguna {{ addslashes($row['name']) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    title="Hapus Pengguna"
                                                    class="inline-flex items-center gap-1 rounded bg-red-50 text-red-700 px-2 py-1 gpa-note font-semibold outline outline-1 -outline-offset-1 outline-red-200 transition-colors hover:bg-red-100">
                                                    <x-gpa.icon name="trash" class="h-3 w-3 shrink-0 text-red-600" />
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="gpa-note font-semibold text-danger" x-show="visibleUsers().length === 0" x-cloak>
                Tidak ada pengguna yang cocok dengan pencarian atau filter role.
            </p>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 pt-3">
                <p class="flex items-start gap-2">
                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="gpa-note font-bold text-success-deep">Seluruh perubahan disimpan dalam ledger tamper-proof SHA-256.</span>
                </p>
                <div class="flex items-center gap-2">
                    <span class="gpa-note text-ink-body">Menampilkan {{ count($directoryRows) }} dari {{ $totalUsers }} entri otorisasi terdaftar.</span>
                    <span class="gpa-micro-bold text-ink">1 / 1</span>
                    <button type="button" @click="pageDirectory(-1)" disabled
                        class="inline-flex items-center gap-1 rounded bg-surface-track px-2 py-1 gpa-note font-semibold text-ink-body outline outline-1 -outline-offset-1 outline-line-board disabled:cursor-not-allowed disabled:opacity-45">
                        <x-gpa.icon name="arrow-left" class="h-3 w-3 shrink-0" />
                        Sebelumnya
                    </button>
                    <button type="button" @click="pageDirectory(1)"
                        class="inline-flex items-center gap-1 rounded bg-ink px-2 py-1 gpa-note font-semibold text-accent transition-opacity hover:opacity-90">
                        Selanjutnya
                        <x-gpa.icon name="arrow-right" class="h-3 w-3 shrink-0" />
                    </button>
                </div>
            </footer>
        </section>

    </div>
@endsection

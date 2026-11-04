@extends('layouts.direktur')

@section('title', 'Detail Akun Pengguna // Direktur')

@section('content')
    <div class="mx-auto flex max-w-4xl flex-col gap-6">
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Direktori Pengguna</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        Rincian Akun: {{ $user->name }}
                    </h1>
                    <p class="text-xs text-ink-body max-w-2xl">
                        Rincian profil, peran, dan informasi kredensial pengguna.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('direktur.users.index') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 shrink-0" />
                        Kembali
                    </a>
                    <a href="{{ route('direktur.users.edit', $user->id) }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="settings" class="h-3.5 w-3.5 shrink-0" />
                        Ubah Akun
                    </a>
                </div>
            </header>
        </section>

        <section class="rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="flex items-start gap-3">
                    <span @class([
                        'mt-0.5 shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-micro',
                        'bg-ink text-accent' => strtoupper($user->role) === 'DIREKTUR',
                        'bg-surface-track text-ink-body' => strtoupper($user->role) !== 'DIREKTUR',
                    ])>{{ strtoupper(substr($user->role, 0, 3)) }}</span>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold leading-6 text-ink">{{ $user->name }}</h2>
                        <p class="mt-0.5 text-[11px] leading-4 text-ink-body font-mono">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span @class([
                        'inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta',
                        'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep' => $user->is_active,
                        'bg-surface-track text-ink-body' => ! $user->is_active,
                    ])>{{ $user->is_active ? 'AKTIF' : 'NON-AKTIF' }}</span>
                    <span class="inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                        {{ $user->role }}
                    </span>
                </div>
            </header>

            <div class="divide-y divide-line-hair">
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="px-6 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Nomor Telepon / WA</div>
                    <div class="px-6 py-3 text-[12px] leading-5 text-ink font-mono sm:col-span-2">{{ $user->phone ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="px-6 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Tipe Klien</div>
                    <div class="px-6 py-3 text-[12px] leading-5 text-ink sm:col-span-2">{{ $user->client_type ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="px-6 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Perusahaan / Mitra</div>
                    <div class="px-6 py-3 text-[12px] leading-5 text-ink sm:col-span-2">{{ $user->company_name ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="px-6 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Nama PIC</div>
                    <div class="px-6 py-3 text-[12px] leading-5 text-ink sm:col-span-2">{{ $user->pic_name ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="px-6 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Telepon PIC</div>
                    <div class="px-6 py-3 text-[12px] leading-5 text-ink font-mono sm:col-span-2">{{ $user->pic_phone ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="px-6 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Alamat</div>
                    <div class="px-6 py-3 text-[12px] leading-5 text-ink whitespace-pre-line sm:col-span-2">{{ $user->address ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div class="px-6 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Terdaftar Sejak</div>
                    <div class="px-6 py-3 text-[12px] leading-5 text-ink sm:col-span-2">{{ $user->created_at->format('d M Y H:i') }} WIB</div>
                </div>
            </div>

            <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line-board/60 px-6 py-3">
                <a href="{{ route('direktur.users.edit', $user->id) }}"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                    <x-gpa.icon name="settings" class="h-3.5 w-3.5 shrink-0" />
                    Ubah Data &amp; Hak Akses
                </a>
            </footer>
        </section>
    </div>
@endsection

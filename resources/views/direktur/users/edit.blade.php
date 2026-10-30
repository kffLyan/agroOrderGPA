@extends('layouts.direktur')

@section('title', 'Ubah Akun Pengguna // Direktur')

@section('content')
    <div class="mx-auto flex max-w-4xl flex-col gap-6">
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Direktori Pengguna</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        Ubah Data Akun: {{ $user->name }}
                    </h1>
                    <p class="text-xs text-ink-body max-w-2xl">
                        Perbarui profil, peran, status keaktifan, dan kredensial pengguna.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('direktur.users.index') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 shrink-0" />
                        Kembali
                    </a>
                    <a href="{{ route('direktur.users.show', $user->id) }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="eye" class="h-3.5 w-3.5 shrink-0" />
                        Lihat Detail
                    </a>
                </div>
            </header>
        </section>

        @if($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-xs text-red-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <x-gpa.icon name="alert-circle" class="h-4 w-4 text-red-600 shrink-0 mt-0.5" />
                    <div class="flex flex-col gap-1">
                        <p class="font-semibold">Gagal memperbarui akun</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <section class="rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="border-b border-line-board/60 px-6 py-4">
                <h2 class="gpa-section-title text-ink">Formulir Perubahan Data</h2>
                <p class="mt-1 text-xs text-ink-body">Akun: <span class="font-semibold text-ink">{{ $user->name }}</span> ({{ $user->email }})</p>
            </header>

            <form action="{{ route('direktur.users.update', $user->id) }}" method="POST" class="px-6 py-5 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Nama Lengkap <span class="text-red-600">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Alamat Email <span class="text-red-600">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Nomor Telepon / WA <span class="text-red-600">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Wewenang Peran <span class="text-red-600">*</span></label>
                        <select name="role" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="DIREKTUR" {{ old('role', $user->role) === 'DIREKTUR' ? 'selected' : '' }}>DIREKTUR</option>
                            <option value="SEKRETARIS" {{ old('role', $user->role) === 'SEKRETARIS' ? 'selected' : '' }}>SEKRETARIS</option>
                            <option value="KOORDINATOR" {{ old('role', $user->role) === 'KOORDINATOR' ? 'selected' : '' }}>KOORDINATOR</option>
                            <option value="ARMADA" {{ old('role', $user->role) === 'ARMADA' ? 'selected' : '' }}>ARMADA</option>
                            <option value="KLIEN" {{ old('role', $user->role) === 'KLIEN' ? 'selected' : '' }}>KLIEN</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Status Keaktifan <span class="text-red-600">*</span></label>
                        <select name="is_active" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="1" {{ old('is_active', (string)(int)$user->is_active) === '1' ? 'selected' : '' }}>AKTIF</option>
                            <option value="0" {{ old('is_active', (string)(int)$user->is_active) === '0' ? 'selected' : '' }}>NON-AKTIF</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Jenis Klien (Jika Role KLIEN)</label>
                        <select name="client_type"
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="">-- Bukan Klien --</option>
                            <option value="REGULER" {{ old('client_type', $user->client_type) === 'REGULER' ? 'selected' : '' }}>REGULER</option>
                            <option value="B2B_KONTRAK" {{ old('client_type', $user->client_type) === 'B2B_KONTRAK' ? 'selected' : '' }}>B2B_KONTRAK</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Nama Usaha / Perusahaan</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}"
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Alamat Domisili / Pengiriman</label>
                    <textarea name="address" rows="3"
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">{{ old('address', $user->address) }}</textarea>
                </div>

                <div class="rounded-xl border border-line-board bg-surface-shell/40 p-4 space-y-2">
                    <p class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Ubah Kata Sandi (Opsional)</p>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengganti kata sandi"
                        class="w-full rounded bg-surface py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    <p class="text-[11px] leading-4 text-ink-body">Jika diisi, kata sandi pengguna akan diganti dengan nilai baru.</p>
                </div>

                <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line-board/60 pt-4">
                    <a href="{{ route('direktur.users.index') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                        Simpan Perubahan
                    </button>
                </footer>
            </form>
        </section>
    </div>
@endsection

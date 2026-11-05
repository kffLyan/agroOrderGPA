@php
    $role = auth()->user()->role ?? 'KLIEN';
    $layout = match($role) {
        'DIREKTUR' => 'layouts.direktur',
        'SEKRETARIS' => 'layouts.sekretaris',
        'KOORDINATOR' => 'layouts.koordinator',
        'ARMADA' => 'layouts.armada',
        default => 'layouts.klien',
    };

    $portalName = match($role) {
        'ARMADA' => 'Portal Armada',
        'DIREKTUR' => 'Portal Direksi',
        'SEKRETARIS' => 'Portal Sekretariat',
        default => 'Portal Klien',
    };

    $roleBadge = match($role) {
        'ARMADA' => 'Awak Armada Logistik',
        'DIREKTUR' => 'Direksi Koperasi GPA',
        'SEKRETARIS' => 'Sekretariat Operasional',
        'KOORDINATOR' => 'Koordinator Panen',
        default => ($user->client_type === 'B2B_KONTRAK' || $contract) ? 'Klien Kontrak B2B' : 'Klien Reguler',
    };

    $titleHeading = match($role) {
        'ARMADA' => 'Profil Awak Armada',
        'DIREKTUR' => 'Profil Direksi Koperasi',
        'SEKRETARIS' => 'Profil Petugas Sekretariat',
        default => 'Profil Akun Klien',
    };

    $titleSubtitle = match($role) {
        'ARMADA' => 'Kelola informasi identitas supir pengiriman dan keamanan kredensial akun logistik Anda.',
        'DIREKTUR' => 'Kelola informasi identitas eksekutif dan keamanan akun pengesahan bisnis Anda.',
        'SEKRETARIS' => 'Kelola informasi staf administrasi operasional dan keamanan kredensial sistem Anda.',
        default => 'Kelola informasi profil perwakilan pengadaan, alamat pengiriman dapur komoditas, dan keamanan akun B2B Anda.',
    };

    $idBadge = match($role) {
        'ARMADA' => 'ID Supir: ARM-' . str_pad($user->id, 2, '0', STR_PAD_LEFT),
        'DIREKTUR' => 'ID Direksi: DIR-' . str_pad($user->id, 2, '0', STR_PAD_LEFT),
        'SEKRETARIS' => 'ID Staf: SEK-' . str_pad($user->id, 2, '0', STR_PAD_LEFT),
        default => 'ID Klien: ' . ($clientId ?? ('BYR-KPN-' . str_pad($user->id, 3, '0', STR_PAD_LEFT))),
    };
@endphp
@extends($layout)

@section('title', $titleHeading . ' · AgroOrder GPA')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- BREADCRUMB & HEADER TITLE --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2 font-mono text-[11px] text-ink-subtle">
                <span>{{ $portalName }}</span>
                <span>&gt;</span>
                <span class="text-ink">Profil Akun</span>
                <span class="rounded bg-ink px-2 py-0.5 font-bold uppercase tracking-wider text-accent text-[10px]">
                    [{{ $roleBadge }}]
                </span>
            </div>
            <h1 class="mt-2 font-sans text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">
                {{ $titleHeading }}
            </h1>
            <p class="mt-1 text-xs text-ink-subtle sm:text-sm">
                {{ $titleSubtitle }}
            </p>
        </div>
        <div class="self-start">
            <span class="inline-flex items-center gap-2 rounded-lg border border-line bg-white px-3.5 py-2 font-mono text-xs font-bold text-ink shadow-sm">
                <span class="h-2 w-2 rounded-full bg-brand"></span>
                <span>{{ $idBadge }}</span>
            </span>
        </div>
    </div>

    {{-- FLASH NOTIFICATIONS --}}
    @if (session('status') === 'profile-updated')
        <div class="mt-5 flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-50 p-4 text-emerald-800">
            <div class="flex items-center gap-2.5">
                <x-gpa.icon name="check-circle" class="h-5 w-5 text-emerald-600" />
                <span class="font-mono text-xs font-bold">Data profil dan keamanan berhasil diperbarui serta disinkronkan ke sistem.</span>
            </div>
            <span class="font-mono text-[10px] uppercase font-bold text-emerald-700">TERSIMPAN</span>
        </div>
    @endif

    @if (session('status') === 'email-verified-directly')
        <div class="mt-5 flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-50 p-4 text-emerald-800">
            <div class="flex items-center gap-2.5">
                <x-gpa.icon name="check-circle" class="h-5 w-5 text-emerald-600" />
                <span class="font-mono text-xs font-bold">Alamat email berhasil diverifikasi langsung! Akun Anda kini berstatus Terverifikasi Resmi.</span>
            </div>
            <span class="font-mono text-[10px] uppercase font-bold text-emerald-700">VERIFIKASI SUKSES</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-500/30 bg-red-50 p-4 text-red-800">
            <div class="flex items-center gap-2 text-xs font-bold font-mono">
                <x-gpa.icon name="alert-triangle" class="h-4 w-4 text-red-600" />
                <span>Terjadi kesalahan validasi pada formulir profil:</span>
            </div>
            <ul class="mt-2 list-inside list-disc text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFO SYNC BANNER --}}
    @if ($role === 'ARMADA')
        <div class="mt-6 flex flex-col gap-3 rounded-xl border border-line bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <span class="font-mono text-xs font-bold uppercase text-ink">info</span>
                <p class="font-mono text-xs text-ink">
                    Akun Pengemudi Armada Logistik GPA &bull; Terdaftar resmi di pangkalan <strong>Gudang Sentral Hub</strong>.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center rounded border border-line bg-canvas px-2.5 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-ink">
                    ARMADA LOGISTIK
                </span>
            </div>
        </div>
    @elseif ($role === 'DIREKTUR' || $role === 'SEKRETARIS')
        <div class="mt-6 flex flex-col gap-3 rounded-xl border border-line bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <span class="font-mono text-xs font-bold uppercase text-ink">info</span>
                <p class="font-mono text-xs text-ink">
                    Akun pengguna internal tingkat <strong>{{ $role === 'DIREKTUR' ? 'Eksekutif Direksi' : 'Staf Sekretariat' }}</strong> AgroOrder GPA.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center rounded border border-line bg-canvas px-2.5 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-ink">
                    INTERNAL KOPERASI
                </span>
            </div>
        </div>
    @elseif ($isB2b)
        <div class="mt-6 flex flex-col gap-3 rounded-xl border border-line bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <span class="font-mono text-xs font-bold uppercase text-ink">info</span>
                <p class="font-mono text-xs text-ink">
                    Data profil disinkronisasi dengan kontrak aktif <span class="font-bold underline decoration-accent decoration-2 underline-offset-2">{{ $contractNumber }}</span>.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center rounded border border-line bg-canvas px-2.5 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-ink">
                    STATUS: AKTIF B2B
                </span>
            </div>
        </div>
    @else
        <div class="mt-6 flex flex-col gap-3 rounded-xl border border-line bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <span class="font-mono text-xs font-bold uppercase text-ink">info</span>
                <p class="font-mono text-xs text-ink">
                    Status akun saat ini adalah <strong class="text-brand">Klien Reguler (Spot Market)</strong>. Transaksi pemesanan menggunakan metode pembayaran di muka.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center rounded border border-line bg-canvas px-2.5 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-ink-subtle">
                    KLIEN REGULER
                </span>
            </div>
        </div>
    @endif

    {{-- MAIN FORM & SIDEBAR GRID --}}
    <form method="POST" action="{{ route('profile.update') }}" class="mt-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- LEFT COLUMN: PROFILE FORM (COL-SPAN-8) --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- BOX 01: INFORMASI PROFIL --}}
                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-line pb-4">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="user" class="h-4 w-4 text-ink-subtle" />
                            <h2 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                                01. INFORMASI PROFIL {{ $role === 'ARMADA' ? 'PENGEMUDI' : ($role === 'KLIEN' ? '& ENTITAS USAHA' : 'PENGGUNA') }}
                            </h2>
                        </div>
                        <span class="rounded border border-line bg-canvas px-2.5 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-ink-subtle">
                            TERVERIFIKASI SISTEM
                        </span>
                    </div>

                    <div class="mt-5 space-y-5">
                        {{-- Nama Lengkap --}}
                        <div>
                            <label for="name" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                Nama Lengkap {{ $role === 'ARMADA' ? 'Awak Pengemudi' : ($role === 'KLIEN' ? 'PIC (Penanggung Jawab Pengadaan)' : 'Pengguna') }} <span class="text-red-500">*</span>
                            </label>
                            <input id="name" name="name" type="text" required
                                value="{{ old('name', $user->name) }}"
                                placeholder="Contoh: {{ $role === 'ARMADA' ? 'Asep Supriatna' : 'Hendra Kurniawan' }}"
                                class="mt-1.5 block w-full rounded-lg border border-line bg-canvas/40 px-3.5 py-2.5 font-sans text-sm text-ink placeholder:text-ink-subtle/60 focus:border-brand focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand">
                            @error('name')
                                <p class="mt-1 font-mono text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email & Telepon Grid --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div x-data="{ editingEmail: false }">
                                <label for="email" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                    Alamat Email Resmi <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1.5">
                                    <input id="email" name="email" type="email" required
                                        value="{{ old('email', $user->email) }}"
                                        :readonly="!editingEmail"
                                        :class="!editingEmail ? 'bg-canvas/80 cursor-not-allowed text-ink font-medium' : 'bg-white focus:border-brand focus:ring-1 focus:ring-brand'"
                                        placeholder="purchasing@kulinerprima.co.id"
                                        class="block w-full rounded-lg border border-line px-3.5 py-2.5 font-sans text-sm text-ink placeholder:text-ink-subtle/60 focus:outline-none transition-colors">
                                </div>

                                {{-- Status Verifikasi & Tombol Ubah di Bawah Input (Tidak Menutupi Teks Email) --}}
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    @if ($user->hasVerifiedEmail())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 font-mono text-[10px] font-bold text-emerald-700 border border-emerald-300 shadow-xs">
                                            <x-gpa.icon name="check-circle" class="h-3 w-3 text-emerald-600" />
                                            <span>TERVERIFIKASI OTP</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 font-mono text-[10px] font-bold text-amber-700 border border-amber-300 shadow-xs">
                                            <x-gpa.icon name="alert-triangle" class="h-3 w-3 text-amber-600" />
                                            <span>BELUM TERVERIFIKASI</span>
                                        </span>
                                    @endif

                                    <button type="button" @click="editingEmail = !editingEmail"
                                        class="font-mono text-[11px] font-semibold text-brand hover:underline">
                                        <span x-text="editingEmail ? 'Batal Ubah' : 'Ubah Email'"></span>
                                    </button>
                                </div>

                                <p x-show="editingEmail" class="mt-1.5 font-mono text-[10px] text-amber-700">
                                    Perhatian: Mengubah email akan memperbarui kontak korespondensi resmi akun Anda.
                                </p>
                                @if (! $user->hasVerifiedEmail())
                                    <div x-show="!editingEmail" class="mt-2 flex items-center justify-between rounded-lg border border-amber-300 bg-amber-50/90 px-3 py-2 text-xs text-amber-900 shadow-xs">
                                        <div class="flex items-center gap-2">
                                            <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0 text-amber-600" />
                                            <span class="font-medium">Email belum terverifikasi di sistem.</span>
                                        </div>
                                        <button type="submit" form="direct-verify-email-form"
                                            class="inline-flex items-center gap-1 rounded bg-brand px-2.5 py-1 font-mono text-[11px] font-bold text-white hover:bg-brand-hover shadow-xs transition-colors">
                                            <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 text-accent" />
                                            <span>Verifikasi Sekarang</span>
                                        </button>
                                    </div>
                                @endif
                                @error('email')
                                    <p class="mt-1 font-mono text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                    Nomor Telepon / WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input id="phone" name="phone" type="text"
                                    value="{{ old('phone', $user->phone ?? '0812-3456-7890') }}"
                                    placeholder="0812-3456-7890"
                                    class="mt-1.5 block w-full rounded-lg border border-line bg-canvas/40 px-3.5 py-2.5 font-sans text-sm text-ink placeholder:text-ink-subtle/60 focus:border-brand focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand">
                                @error('phone')
                                    <p class="mt-1 font-mono text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Nama Usaha / Unit --}}
                        <div>
                            <label for="company_name" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                {{ $role === 'ARMADA' ? 'Pangkalan / Home Base Armada' : ($role === 'KLIEN' ? 'Nama Usaha / Badan Hukum PT' : 'Instansi / Divisi') }} <span class="font-normal text-ink-subtle">(Opsional)</span>
                            </label>
                            <input id="company_name" name="company_name" type="text"
                                value="{{ old('company_name', $user->company_name ?? ($role === 'ARMADA' ? 'Gudang Sentral Hub Bogor' : 'PT Kuliner Prima Nusantara')) }}"
                                placeholder="{{ $role === 'ARMADA' ? 'Gudang Sentral Hub Bogor' : 'PT Kuliner Prima Nusantara' }}"
                                class="mt-1.5 block w-full rounded-lg border border-line bg-canvas/40 px-3.5 py-2.5 font-sans text-sm text-ink placeholder:text-ink-subtle/60 focus:border-brand focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand">
                            @error('company_name')
                                <p class="mt-1 font-mono text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alamat Lengkap --}}
                        <div>
                            <label for="address" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                {{ $role === 'ARMADA' ? 'Alamat Domisili Pengemudi' : ($role === 'KLIEN' ? 'Alamat Lengkap Pengiriman / Receiving Dapur' : 'Alamat Domisili') }} <span class="text-red-500">*</span>
                            </label>
                            <textarea id="address" name="address" rows="3"
                                placeholder="{{ $role === 'ARMADA' ? 'Jl. Ciwidey No. 12, Bandung' : 'Jl. Lapangan Tembak No. 45, Ciracas, Jakarta Timur 13740' }}"
                                class="mt-1.5 block w-full resize-none rounded-lg border border-line bg-canvas/40 p-3.5 font-sans text-sm text-ink placeholder:text-ink-subtle/60 focus:border-brand focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand">{{ old('address', $user->address ?? ($role === 'ARMADA' ? 'Pangkalan Sentral Hub Bogor' : 'Jl. Lapangan Tembak No. 45, Ciracas, Pasar Rebo, Jakarta Timur 13740')) }}</textarea>
                            <p class="mt-1.5 font-mono text-[11px] text-ink-subtle">
                                {{ $role === 'ARMADA' ? 'Alamat domisili operasional awak pengemudi armada.' : 'Alamat ini dijadikan lokasi default tujuan pengiriman pesanan AgroOrder GPA.' }}
                            </p>
                            @error('address')
                                <p class="mt-1 font-mono text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BOX 02: PENGATURAN KATA SANDI (SECURITY) --}}
                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-line pb-4">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="lock" class="h-4 w-4 text-ink-subtle" />
                            <h2 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                                02. PENGATURAN KATA SANDI (SECURITY)
                            </h2>
                        </div>
                        <span class="rounded border border-line bg-canvas px-2.5 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-ink-subtle">
                            OPSIONAL
                        </span>
                    </div>

                    <p class="mt-4 font-mono text-xs text-ink-subtle">
                        Kosongkan kolom di bawah jika Anda tidak ingin memperbarui kata sandi portal.
                    </p>

                    <div class="mt-4 space-y-4">
                        {{-- Kata Sandi Saat Ini --}}
                        <div x-data="{ show: false }">
                            <label for="current_password" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                Kata Sandi Saat Ini
                            </label>
                            <div class="relative mt-1.5">
                                <input :type="show ? 'text' : 'password'" id="current_password" name="current_password"
                                    placeholder="••••••••••••"
                                    class="block w-full rounded-lg border border-line bg-canvas/40 px-3.5 py-2.5 pr-24 font-mono text-sm text-ink placeholder:text-ink-subtle/60 focus:border-brand focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 font-mono text-[11px] text-ink-subtle hover:text-brand">
                                    <span x-show="!show" class="flex items-center gap-1">
                                        <x-gpa.icon name="eye" class="h-4 w-4" />
                                        <span>Lihat</span>
                                    </span>
                                    <span x-show="show" x-cloak class="flex items-center gap-1 text-brand">
                                        <x-gpa.icon name="eye-off" class="h-4 w-4" />
                                        <span>Tutup</span>
                                    </span>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 font-mono text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kata Sandi Baru & Konfirmasi Grid --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div x-data="{ show: false }">
                                <label for="password" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                    Kata Sandi Baru
                                </label>
                                <div class="relative mt-1.5">
                                    <input :type="show ? 'text' : 'password'" id="password" name="password" autocomplete="new-password"
                                        placeholder="Min. 8 karakter"
                                        class="block w-full rounded-lg border border-line bg-canvas/40 px-3.5 py-2.5 pr-20 font-mono text-sm text-ink placeholder:text-ink-subtle/60 focus:border-brand focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand">
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 font-mono text-[11px] text-ink-subtle hover:text-brand">
                                        <span x-show="!show" class="flex items-center gap-1">
                                            <x-gpa.icon name="eye" class="h-4 w-4" />
                                            <span>Lihat</span>
                                        </span>
                                        <span x-show="show" x-cloak class="flex items-center gap-1 text-brand">
                                            <x-gpa.icon name="eye-off" class="h-4 w-4" />
                                            <span>Tutup</span>
                                        </span>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 font-mono text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-data="{ show: false }">
                                <label for="password_confirmation" class="block font-mono text-[11px] font-bold uppercase tracking-wider text-ink">
                                    Konfirmasi Kata Sandi Baru
                                </label>
                                <div class="relative mt-1.5">
                                    <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                                        placeholder="Ulangi kata sandi baru"
                                        class="block w-full rounded-lg border border-line bg-canvas/40 px-3.5 py-2.5 pr-20 font-mono text-sm text-ink placeholder:text-ink-subtle/60 focus:border-brand focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand">
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 font-mono text-[11px] text-ink-subtle hover:text-brand">
                                        <span x-show="!show" class="flex items-center gap-1">
                                            <x-gpa.icon name="eye" class="h-4 w-4" />
                                            <span>Lihat</span>
                                        </span>
                                        <span x-show="show" x-cloak class="flex items-center gap-1 text-brand">
                                            <x-gpa.icon name="eye-off" class="h-4 w-4" />
                                            <span>Tutup</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACTION BUTTON BAR --}}
                <div class="flex flex-col-reverse items-stretch justify-between gap-3 pt-2 sm:flex-row sm:items-center">
                    <button type="reset"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-white px-5 py-2.5 font-mono text-xs font-semibold text-ink hover:bg-canvas transition-colors shadow-sm">
                        <x-gpa.icon name="x" class="h-3.5 w-3.5 text-ink-subtle" />
                        <span>[ Batal / Reset ]</span>
                    </button>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-ink px-6 py-2.5 font-mono text-xs font-bold text-white hover:bg-brand transition-colors shadow-sm">
                        <x-gpa.icon name="save" class="h-3.5 w-3.5 text-accent" />
                        <span>[ Simpan Perubahan Profil ]</span>
                    </button>
                </div>
            </div>

            {{-- RIGHT COLUMN: SIDEBAR METRICS & SUPPORT (COL-SPAN-4) --}}
            <div class="space-y-6 lg:col-span-4">
                @if ($role === 'ARMADA')
                    {{-- CARD: STATUS OPERASIONAL ARMADA --}}
                    <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-4">
                            <div class="flex items-center gap-2">
                                <x-gpa.icon name="truck" class="h-4 w-4 text-ink-subtle" />
                                <h2 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                                    STATUS OPERASIONAL
                                </h2>
                            </div>
                            <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                                AKTIF BERTUGAS
                            </span>
                        </div>

                        <div class="mt-4">
                            <p class="font-mono text-[11px] text-ink-subtle">Peran Pengguna:</p>
                            <p class="mt-0.5 font-sans text-sm font-bold text-ink">
                                Awak Supir Logistik GPA
                            </p>
                            <p class="mt-1 font-mono text-[11px] text-ink-subtle">
                                Pangkalan: <span class="text-ink font-semibold">Gudang Sentral Hub</span>
                            </p>
                        </div>

                        <div class="mt-5 space-y-3 border-t border-line pt-4 font-mono text-xs">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Lisensi Supir:</span>
                                <span class="text-right font-semibold text-ink">SIM B1 Umum</span>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Unit Kendaraan:</span>
                                <span class="text-right font-semibold text-ink">Truk Boks Angkut CDD</span>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Status Kelaikan:</span>
                                <span class="text-right font-semibold text-success-deep">Lolos Uji Kir &amp; Medis</span>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Dispatch Sentral:</span>
                                <span class="text-right font-semibold text-ink">Hub Bogor</span>
                            </div>
                        </div>
                    </div>
                @elseif ($role === 'DIREKTUR' || $role === 'SEKRETARIS')
                    {{-- CARD: OTORITAS SISTEM INTERNAL --}}
                    <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-4">
                            <div class="flex items-center gap-2">
                                <x-gpa.icon name="shield" class="h-4 w-4 text-ink-subtle" />
                                <h2 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                                    OTORITAS SISTEM
                                </h2>
                            </div>
                            <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                                INTERNAL GPA
                            </span>
                        </div>

                        <div class="mt-4">
                            <p class="font-mono text-[11px] text-ink-subtle">Tingkat Hak Akses:</p>
                            <p class="mt-0.5 font-sans text-sm font-bold text-ink">
                                {{ $role === 'DIREKTUR' ? 'Dewan Direksi Eksekutif' : 'Staf Administrasi Sekretariat' }}
                            </p>
                            <p class="mt-1 font-mono text-[11px] text-ink-subtle">
                                Divisi: <span class="text-ink font-semibold">{{ $role === 'DIREKTUR' ? 'Manajemen & Tata Kelola' : 'Operasional & Verifikasi' }}</span>
                            </p>
                        </div>

                        <div class="mt-5 space-y-3 border-t border-line pt-4 font-mono text-xs">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Entitas Bisnis:</span>
                                <span class="text-right font-semibold text-ink">Koperasi GPA</span>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Akses Audit Trail:</span>
                                <span class="text-right font-semibold text-ink">Penuh &amp; Terverifikasi</span>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- CARD: RINGKASAN KEMITRAAN KLIEN --}}
                    <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-line pb-4">
                            <div class="flex items-center gap-2">
                                <x-gpa.icon name="shield" class="h-4 w-4 text-ink-subtle" />
                                <h2 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                                    RINGKASAN KEMITRAAN
                                </h2>
                            </div>
                            <span class="rounded {{ $isB2b ? 'bg-ink text-accent' : 'bg-canvas border border-line text-ink' }} px-2 py-0.5 font-mono text-[10px] font-bold uppercase">
                                {{ $isB2b ? 'Mitra Kontrak B2B' : 'Klien Reguler' }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <p class="font-mono text-[11px] text-ink-subtle">Tipe Akun:</p>
                            <p class="mt-0.5 font-sans text-sm font-bold text-ink">
                                {{ $accountType }}
                            </p>
                            @if ($isB2b)
                                <p class="mt-1 font-mono text-[11px] text-ink-subtle">
                                    No. PKS: <span class="text-ink font-semibold">{{ $contractNumber }}</span>
                                </p>
                            @else
                                <p class="mt-1 font-mono text-[11px] text-ink-subtle">
                                    Metode Pembayaran: <span class="text-brand font-semibold">Spot Market (Bayar di Muka)</span>
                                </p>
                            @endif
                        </div>

                        @if ($isB2b)
                            <div class="mt-5 border-t border-line pt-4">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-mono text-[11px] text-ink-subtle">Plafon Kredit:</span>
                                    <span class="font-mono font-bold text-ink">Rp {{ number_format($creditLimit, 0, ',', '.') }}</span>
                                </div>

                                {{-- Progress Bar Plafon --}}
                                <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-canvas border border-line">
                                    <div class="h-full rounded-full bg-ink transition-all" style="width: {{ 100 - $creditPercent }}%"></div>
                                </div>

                                <div class="mt-2 flex items-center justify-between text-xs">
                                    <span class="font-mono text-[11px] text-ink-subtle">Sisa Kuota:</span>
                                    <span class="font-mono font-bold text-ink">Rp {{ number_format($availableCredit, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="mt-5 border-t border-line pt-4">
                                <div class="rounded-lg bg-surface-shell/50 border border-line p-3.5">
                                    <p class="font-mono text-[11px] font-bold text-ink">Fasilitas Kontrak B2B Belum Aktif</p>
                                    <p class="mt-1 font-sans text-xs text-ink-subtle leading-relaxed">
                                        Fasilitas plafon kredit TOP Tempo 14–30 hari dan harga terkunci komoditas hanya berlaku untuk mitra kontrak yang telah disahkan Direktur GPA.
                                    </p>
                                    <a href="{{ route('klien.dashboard') }}"
                                        class="mt-3 flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 font-mono text-xs font-bold text-white hover:bg-brand-hover transition-colors shadow-sm">
                                        <span>[ Ajukan Kemitraan Kontrak B2B ]</span>
                                        <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 text-white" />
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div class="mt-5 space-y-3 border-t border-line pt-4 font-mono text-xs">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Incoterms:</span>
                                <span class="text-right font-semibold text-ink">Franco Gudang Dapur</span>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Verifikasi Akun:</span>
                                <span class="text-right font-semibold text-ink">
                                    {{ $isB2b ? 'Terverifikasi (OTP & PKS Resmi)' : 'Terverifikasi (Email OTP)' }}
                                </span>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-ink-subtle text-[11px]">Layanan Bantuan:</span>
                                <span class="text-right font-semibold text-ink">Dedicated AM GPA</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- CARD: BUTUH BANTUAN PROFIL? --}}
                <div class="rounded-xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="headset" class="h-4 w-4 text-ink-subtle" />
                        <h2 class="font-sans text-sm font-bold text-ink">
                            Butuh Bantuan Profil?
                        </h2>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-ink-subtle">
                        {{ $role === 'ARMADA' ? 'Pertanyaan terkait rute pengantaran atau surat tugas armada dapat dikoordinasikan langsung ke Dispatch Hub.' : 'Perubahan data entitas resmi memerlukan peninjauan oleh tim administrasi AgroOrder GPA.' }}
                    </p>
                    <a href="{{ route('public.kontak') }}"
                        class="mt-4 inline-flex items-center gap-1.5 font-mono text-xs font-bold text-ink hover:text-brand hover:underline">
                        <span>[ Hubungi Layanan Bantuan GPA ]</span>
                        <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 text-ink" />
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- FORM TERSEMBUNYI UNTUK VERIFIKASI EMAIL INSTAN LANGSUNG DARI PROFIL --}}
    <form id="direct-verify-email-form" method="POST" action="{{ route('profile.verify-email-now') }}" class="hidden">
        @csrf
    </form>

    {{-- BOTTOM AUDIT & COMPLIANCE BAR --}}
    <div class="mt-12 flex flex-col items-center justify-between gap-2 border-t border-line pt-6 font-mono text-[11px] text-ink-subtle sm:flex-row">
        <div>
            AgroOrder GPA Enterprise Commerce | Identity: <span class="font-semibold text-ink">{{ $user->company_name ?? $user->name }}</span> ({{ $idBadge }})
        </div>
        <div class="flex items-center gap-3">
            <span>Sesi Terenkripsi TLS 1.3</span>
            <span class="rounded bg-canvas border border-line px-1.5 py-0.5 text-[10px] font-bold text-ink">
                ISO 27001 COMPLIANT
            </span>
        </div>
    </div>
</div>
@endsection

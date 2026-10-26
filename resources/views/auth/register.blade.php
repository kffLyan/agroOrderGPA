<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun Klien Baru - AgroOrder GPA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=JetBrains+Mono:wght@400;500;600&amp;display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased selection:bg-surface-raised">

    {{-- MOBILE: brand green background + centered form card --}}
    <div class="relative flex min-h-screen flex-col justify-between overflow-hidden bg-brand p-4 sm:p-6 lg:hidden">
        <div class="pointer-events-none absolute left-[244px] top-[-96px] h-96 w-96 rounded-full bg-[rgba(78,122,3,0.20)] blur-[32px]"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-0 top-[491px] h-80 w-80 rounded-full bg-[rgba(170,189,6,0.10)] blur-[20px]"
            aria-hidden="true"></div>

        <main class="relative z-10 mx-auto my-auto w-full max-w-md py-6">
            <div class="rounded-3xl bg-surface p-6 shadow-2xl shadow-black/25 sm:p-8">
                <div class="mx-auto flex w-full max-w-xl flex-col">
                    <div>
                        <div class="mb-6">
                            <h2 class="text-center text-xl font-semibold tracking-tight text-ink">
                                Daftar Akun Klien Baru
                            </h2>
                            <p class="mt-1 text-center text-xs text-ink">
                                Lengkapi informasi berikut untuk membuat akun dan mengakses pemesanan AgroOrder GPA.
                            </p>
                        </div>

                        @if (session('status'))
                            <div
                                class="mb-4 flex items-start gap-2 rounded-sm border border-success/40 bg-success-soft px-3 py-2 text-xs text-success-ink">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div role="alert"
                                class="mb-4 flex items-start gap-2 rounded-sm border border-danger/40 bg-danger-soft px-3 py-2 text-xs text-danger">
                                <span class="material-symbols-outlined text-sm">error</span>
                                <ul class="space-y-0.5">
                                    @foreach ($errors->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="registrationOtp('{{ old('email') }}')" @submit="submitCheck($event)">
                            @csrf

                            <div>
                                <label for="name-mobile"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Nama Lengkap / Kontak PIC *
                                </label>
                                <input id="name-mobile" name="name" type="text" required autocomplete="name"
                                    value="{{ old('name') }}" placeholder="Contoh: Budi Santoso"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('name')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between">
                                    <label for="email-mobile"
                                        class="font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                        Alamat Email *
                                    </label>
                                    <template x-if="otpState === 'idle'">
                                        <button type="button" @click="sendOtp()"
                                            class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold text-brand hover:text-brand-hover hover:underline transition-colors">
                                            <span class="material-symbols-outlined text-[13px]">send</span>
                                            <span>Kirim Kode OTP</span>
                                        </button>
                                    </template>
                                    <template x-if="otpState === 'sending'">
                                        <span class="inline-flex items-center gap-1 font-mono text-[11px] text-ink-subtle">
                                            <svg class="h-3 w-3 animate-spin text-brand" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>Mengirim OTP...</span>
                                        </span>
                                    </template>
                                    <template x-if="otpState === 'sent' || otpState === 'verifying'">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="sendOtp(true)" :disabled="countdown > 0"
                                                class="font-mono text-[11px] font-semibold transition-colors"
                                                :class="countdown > 0 ? 'text-ink-subtle cursor-not-allowed' : 'text-brand hover:underline'">
                                                <span x-show="countdown > 0">Kirim Ulang (<span x-text="countdown"></span>s)</span>
                                                <span x-show="countdown <= 0">Kirim Ulang Kode</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="otpState === 'verified'">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-soft px-2 py-0.5 font-mono text-[10px] font-bold text-success-ink border border-success/30">
                                            <span class="material-symbols-outlined text-[12px] text-success">verified</span>
                                            TERVERIFIKASI
                                        </span>
                                    </template>
                                </div>
                                <input id="email-mobile" name="email" type="email" autocomplete="email" required
                                    x-model="email"
                                    :readonly="otpState === 'verified'"
                                    :class="otpState === 'verified' ? 'bg-surface-raised cursor-not-allowed text-ink font-semibold' : ''"
                                    placeholder="nama@perusahaan.co.id"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">

                                <!-- Inline OTP Input Box (Muncul ketika OTP terkirim/verifikasi) -->
                                <div x-show="otpState === 'sent' || otpState === 'verifying'" x-cloak x-transition
                                    class="mt-2.5 rounded-sm border border-brand/30 bg-[#F1F6EC] p-3 text-ink">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-1.5 font-mono text-[11px] font-semibold text-brand">
                                            <span class="material-symbols-outlined text-[15px]">mark_email_read</span>
                                            <span>Masukkan 6 Digit Kode OTP</span>
                                        </div>
                                        <span class="font-mono text-[10px] text-ink-subtle">Cek Inbox / Spam</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="otpCode" maxlength="6" inputmode="numeric"
                                            placeholder="000000"
                                            @keyup.enter="verifyOtp()"
                                            class="h-9 w-32 rounded-sm border-line-strong bg-white px-2.5 text-center font-mono text-sm tracking-widest text-ink placeholder:tracking-normal focus:border-brand focus:ring-0">
                                        <button type="button" @click="verifyOtp()" :disabled="otpState === 'verifying'"
                                            class="flex h-9 items-center justify-center gap-1 rounded-sm bg-brand px-3.5 font-mono text-xs font-semibold text-white transition-colors hover:bg-brand-hover active:translate-y-px disabled:opacity-60">
                                            <svg x-show="otpState === 'verifying'" class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span x-text="otpState === 'verifying' ? 'Memverifikasi...' : 'Verifikasi'"></span>
                                        </button>
                                    </div>
                                    <p x-show="error" x-text="error" class="mt-2 font-mono text-[11px] text-danger"></p>
                                    <p x-show="success && otpState !== 'verified'" x-text="success" class="mt-1.5 font-mono text-[10px] text-brand"></p>
                                </div>

                                <!-- Notifikasi Status Sukses Terverifikasi -->
                                <div x-show="otpState === 'verified'" x-cloak x-transition
                                    class="mt-2 flex items-center justify-between rounded-sm border border-success/30 bg-success-soft px-3 py-2 text-xs text-success-ink">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-success">check_circle</span>
                                        <span class="font-mono text-[11px]">Email terverifikasi resmi oleh GPA.</span>
                                    </div>
                                    <button type="button" @click="resetVerification()"
                                        class="font-mono text-[10px] text-ink-subtle underline hover:text-ink">
                                        Ganti Email
                                    </button>
                                </div>

                                <!-- Error Alert jika validasi gagal -->
                                <div x-show="error && otpState !== 'sent' && otpState !== 'verifying'" x-cloak class="mt-1.5">
                                    <p x-text="error" class="font-mono text-[11px] text-danger"></p>
                                </div>

                                @error('email')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone-mobile"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Nomor Telepon / WhatsApp *
                                </label>
                                <input id="phone-mobile" name="phone" type="tel" required autocomplete="tel"
                                    value="{{ old('phone') }}" placeholder="+62 812-3456-7890"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('phone')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="business_name-mobile"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Nama Usaha / PT *
                                </label>
                                <input id="business_name-mobile" name="business_name" type="text" required
                                    autocomplete="organization" value="{{ old('business_name') }}"
                                    placeholder="Contoh: PT Kuliner Prima Nusantara"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('business_name')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>


                            <div>
                                <label for="address-mobile"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Alamat Lengkap Pengiriman *
                                </label>
                                <textarea id="address-mobile" name="address" required rows="3" autocomplete="street-address"
                                    placeholder="Alamat lengkap gudang / dapur penerima pengiriman..."
                                    class="h-20 w-full resize-none rounded-sm border-line-strong bg-surface p-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password-mobile"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Kata Sandi *
                                </label>
                                <input id="password-mobile" name="password" type="password" required
                                    autocomplete="new-password" placeholder="Minimal 8 karakter"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('password')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation-mobile"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Konfirmasi Kata Sandi *
                                </label>
                                <input id="password_confirmation-mobile" name="password_confirmation" type="password" required
                                    autocomplete="new-password" placeholder="Ulangi kata sandi"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('password_confirmation')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-1">
                                <label class="inline-flex cursor-pointer items-start gap-2">
                                    <input type="checkbox" name="terms" value="1" required
                                        class="h-3.5 w-3.5 mt-0.5 shrink-0 cursor-pointer rounded-sm border-line-strong bg-surface text-brand focus:ring-0 focus:ring-offset-0">
                                    <span class="text-xs leading-snug text-ink">Saya menyetujui Ketentuan Layanan dan Kebijakan Privasi AgroOrder GPA.</span>
                                </label>
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="flex h-10 w-full items-center justify-center space-x-2 rounded-sm border border-brand bg-brand font-mono text-xs font-semibold uppercase tracking-wider text-white transition-colors hover:bg-brand-hover active:translate-y-px">
                                    <span>DAFTAR AKUN SEKARANG</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div
                        class="mt-8 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4 text-center sm:text-left">
                        <span class="text-xs text-ink">Sudah memiliki akun?</span>
                        <a href="{{ route('login') }}"
                            class="flex items-center gap-1 font-mono text-xs font-semibold text-ink underline underline-offset-4 hover:text-brand">
                            <span>Masuk ke Portal Akun</span>
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="relative z-10 w-full py-2 text-center text-xs text-white/70">
            <p>&copy; 2026 Green Pasundan Agriculture. Hak Cipta Dilindungi Undang-Undang. Metrologi Legal &amp; Cold-Chain Logistics.</p>
        </footer>
    </div>

    {{-- DESKTOP: split grid view --}}
    <main class="hidden min-h-screen w-full flex-col lg:flex">
        <div class="grid w-full flex-grow grid-cols-1 border-b border-line lg:grid-cols-12">

            {{-- LEFT PANEL: Corporate & Security Trust Column --}}
            <section
                class="relative flex flex-col justify-center overflow-hidden border-b border-[#245204] bg-brand p-8 md:p-12 lg:col-span-5 lg:border-b-0 lg:border-r lg:p-14">
                <div class="pointer-events-none absolute left-[244px] top-[-96px] h-96 w-96 rounded-full bg-[rgba(78,122,3,0.20)] blur-[32px]"
                    aria-hidden="true"></div>
                <div class="pointer-events-none absolute left-0 top-[491px] h-80 w-80 rounded-full bg-[rgba(170,189,6,0.10)] blur-[20px]"
                    aria-hidden="true"></div>

                <div class="relative flex flex-col gap-3 pb-10">
                    <div class="flex flex-col gap-3 pt-3">
                        <h1 class="text-[30px] font-bold leading-9 text-white">
                            Portal Terpadu Rantai Pasok<br>Agribisnis
                        </h1>
                        <p class="max-w-lg text-sm leading-5 text-surface-disabled/80">
                            Satu platform digital untuk logistik pertanian, penimbangan presisi, dan verifikasi mutu real-time.
                        </p>
                    </div>

                    {{-- Highlight Cards --}}
                    <div class="flex max-w-lg flex-col gap-4 pt-5">
                        <div
                            class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                            <span class="pt-0.5">
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                                    <span class="material-symbols-outlined text-[11px] text-accent-deep">database</span>
                                </span>
                            </span>
                            <div class="flex flex-col gap-1.5 pl-3">
                                <h2 class="text-sm font-semibold leading-5 text-white">Data Rantai Pasok Terpusat</h2>
                                <p class="text-xs leading-4 text-line-board">
                                    Data dari lahan produksi, hub konsolidasi, hingga titik bongkar pembeli selalu tersinkron.
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                            <span class="pt-0.5">
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                                    <span class="material-symbols-outlined text-[11px] text-accent-deep">scale</span>
                                </span>
                            </span>
                            <div class="flex flex-col gap-1.5 pl-3">
                                <h2 class="text-sm font-semibold leading-5 text-white">Penimbangan Akurat</h2>
                                <p class="text-xs leading-4 text-line-board">
                                    Jembatan timbang digital bersertifikat metrologi legal mengurangi selisih berat barang.
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                            <span class="pt-0.5">
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                                    <span class="material-symbols-outlined text-[11px] text-accent-deep">description</span>
                                </span>
                            </span>
                            <div class="flex flex-col gap-1.5 pl-3">
                                <h2 class="text-sm font-semibold leading-5 text-white">Riwayat Audit Permanen</h2>
                                <p class="text-xs leading-4 text-line-board">
                                    Setiap perubahan manifest, mutu, dan faktur tercatat permanen serta tidak dapat diubah.
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                            <span class="pt-0.5">
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                                    <span class="material-symbols-outlined text-[11px] text-accent-deep">group</span>
                                </span>
                            </span>
                            <div class="flex flex-col gap-1.5 pl-3">
                                <h2 class="text-sm font-semibold leading-5 text-white">Akses Sesuai Peran</h2>
                                <p class="text-xs leading-4 text-line-board">
                                    Setiap peran hanya mendapat akses sesuai tugasnya demi tata kelola yang rapi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- RIGHT PANEL: Registration Form --}}
            <section
                class="flex flex-col items-center justify-center bg-surface px-6 py-10 md:px-12 md:py-14 lg:col-span-7">
                <div class="mx-auto flex w-full max-w-xl flex-col">
                    <div>
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold tracking-tight text-ink">
                                Daftar Akun Klien Baru
                            </h2>
                            <p class="mt-1 text-xs text-ink">
                                Lengkapi informasi berikut untuk membuat akun dan mengakses pemesanan AgroOrder GPA.
                            </p>
                        </div>

                        @if (session('status'))
                            <div
                                class="mb-4 flex items-start gap-2 rounded-sm border border-success/40 bg-success-soft px-3 py-2 text-xs text-success-ink">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div role="alert"
                                class="mb-4 flex items-start gap-2 rounded-sm border border-danger/40 bg-danger-soft px-3 py-2 text-xs text-danger">
                                <span class="material-symbols-outlined text-sm">error</span>
                                <ul class="space-y-0.5">
                                    @foreach ($errors->all() as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}" class="space-y-4" x-data="registrationOtp('{{ old('email') }}')" @submit="submitCheck($event)">
                            @csrf

                            <div>
                                <label for="name"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Nama Lengkap / Kontak PIC *
                                </label>
                                <input id="name" name="name" type="text" required autocomplete="name"
                                    value="{{ old('name') }}" placeholder="Contoh: Budi Santoso"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('name')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between">
                                    <label for="email"
                                        class="font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                        Alamat Email *
                                    </label>
                                    <template x-if="otpState === 'idle'">
                                        <button type="button" @click="sendOtp()"
                                            class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold text-brand hover:text-brand-hover hover:underline transition-colors">
                                            <span class="material-symbols-outlined text-[13px]">send</span>
                                            <span>Kirim Kode OTP</span>
                                        </button>
                                    </template>
                                    <template x-if="otpState === 'sending'">
                                        <span class="inline-flex items-center gap-1 font-mono text-[11px] text-ink-subtle">
                                            <svg class="h-3 w-3 animate-spin text-brand" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span>Mengirim OTP...</span>
                                        </span>
                                    </template>
                                    <template x-if="otpState === 'sent' || otpState === 'verifying'">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="sendOtp(true)" :disabled="countdown > 0"
                                                class="font-mono text-[11px] font-semibold transition-colors"
                                                :class="countdown > 0 ? 'text-ink-subtle cursor-not-allowed' : 'text-brand hover:underline'">
                                                <span x-show="countdown > 0">Kirim Ulang (<span x-text="countdown"></span>s)</span>
                                                <span x-show="countdown <= 0">Kirim Ulang Kode</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="otpState === 'verified'">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-soft px-2 py-0.5 font-mono text-[10px] font-bold text-success-ink border border-success/30">
                                            <span class="material-symbols-outlined text-[12px] text-success">verified</span>
                                            TERVERIFIKASI
                                        </span>
                                    </template>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required
                                    x-model="email"
                                    :readonly="otpState === 'verified'"
                                    :class="otpState === 'verified' ? 'bg-surface-raised cursor-not-allowed text-ink font-semibold' : ''"
                                    placeholder="nama@perusahaan.co.id"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">

                                <!-- Inline OTP Input Box (Muncul ketika OTP terkirim/verifikasi) -->
                                <div x-show="otpState === 'sent' || otpState === 'verifying'" x-cloak x-transition
                                    class="mt-2.5 rounded-sm border border-brand/30 bg-[#F1F6EC] p-3 text-ink">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-1.5 font-mono text-[11px] font-semibold text-brand">
                                            <span class="material-symbols-outlined text-[15px]">mark_email_read</span>
                                            <span>Masukkan 6 Digit Kode OTP</span>
                                        </div>
                                        <span class="font-mono text-[10px] text-ink-subtle">Cek Inbox / Spam</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="otpCode" maxlength="6" inputmode="numeric"
                                            placeholder="000000"
                                            @keyup.enter="verifyOtp()"
                                            class="h-9 w-32 rounded-sm border-line-strong bg-white px-2.5 text-center font-mono text-sm tracking-widest text-ink placeholder:tracking-normal focus:border-brand focus:ring-0">
                                        <button type="button" @click="verifyOtp()" :disabled="otpState === 'verifying'"
                                            class="flex h-9 items-center justify-center gap-1 rounded-sm bg-brand px-3.5 font-mono text-xs font-semibold text-white transition-colors hover:bg-brand-hover active:translate-y-px disabled:opacity-60">
                                            <svg x-show="otpState === 'verifying'" class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span x-text="otpState === 'verifying' ? 'Memverifikasi...' : 'Verifikasi'"></span>
                                        </button>
                                    </div>
                                    <p x-show="error" x-text="error" class="mt-2 font-mono text-[11px] text-danger"></p>
                                    <p x-show="success && otpState !== 'verified'" x-text="success" class="mt-1.5 font-mono text-[10px] text-brand"></p>
                                </div>

                                <!-- Notifikasi Status Sukses Terverifikasi -->
                                <div x-show="otpState === 'verified'" x-cloak x-transition
                                    class="mt-2 flex items-center justify-between rounded-sm border border-success/30 bg-success-soft px-3 py-2 text-xs text-success-ink">
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-success">check_circle</span>
                                        <span class="font-mono text-[11px]">Email terverifikasi resmi oleh GPA.</span>
                                    </div>
                                    <button type="button" @click="resetVerification()"
                                        class="font-mono text-[10px] text-ink-subtle underline hover:text-ink">
                                        Ganti Email
                                    </button>
                                </div>

                                <!-- Error Alert jika validasi gagal -->
                                <div x-show="error && otpState !== 'sent' && otpState !== 'verifying'" x-cloak class="mt-1.5">
                                    <p x-text="error" class="font-mono text-[11px] text-danger"></p>
                                </div>

                                @error('email')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Nomor Telepon / WhatsApp *
                                </label>
                                <input id="phone" name="phone" type="tel" required autocomplete="tel"
                                    value="{{ old('phone') }}" placeholder="+62 812-3456-7890"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('phone')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="business_name"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Nama Usaha / PT *
                                </label>
                                <input id="business_name" name="business_name" type="text" required
                                    autocomplete="organization" value="{{ old('business_name') }}"
                                    placeholder="Contoh: PT Kuliner Prima Nusantara"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('business_name')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>


                            <div>
                                <label for="address"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Alamat Lengkap Pengiriman *
                                </label>
                                <textarea id="address" name="address" required rows="3" autocomplete="street-address"
                                    placeholder="Alamat lengkap gudang / dapur penerima pengiriman..."
                                    class="h-20 w-full resize-none rounded-sm border-line-strong bg-surface p-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Kata Sandi *
                                </label>
                                <input id="password" name="password" type="password" required
                                    autocomplete="new-password" placeholder="Minimal 8 karakter"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('password')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Konfirmasi Kata Sandi *
                                </label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    autocomplete="new-password" placeholder="Ulangi kata sandi"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('password_confirmation')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="pt-1">
                                <label class="inline-flex cursor-pointer items-start gap-2">
                                    <input type="checkbox" name="terms" value="1" required
                                        class="h-3.5 w-3.5 mt-0.5 shrink-0 cursor-pointer rounded-sm border-line-strong bg-surface text-brand focus:ring-0 focus:ring-offset-0">
                                    <span class="text-xs leading-snug text-ink">Saya menyetujui Ketentuan Layanan dan Kebijakan Privasi AgroOrder GPA.</span>
                                </label>
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="flex h-10 w-full items-center justify-center space-x-2 rounded-sm border border-brand bg-brand font-mono text-xs font-semibold uppercase tracking-wider text-white transition-colors hover:bg-brand-hover active:translate-y-px">
                                    <span>DAFTAR AKUN SEKARANG</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div
                        class="mt-8 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4 text-center sm:text-left">
                        <span class="text-xs text-ink">Sudah memiliki akun?</span>
                        <a href="{{ route('login') }}"
                            class="flex items-center gap-1 font-mono text-xs font-semibold text-ink underline underline-offset-4 hover:text-brand">
                            <span>Masuk ke Portal Akun</span>
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <x-gpa.toast />

    <script>
        function registrationOtp(initialEmail = '') {
            return {
                email: initialEmail,
                otpCode: '',
                otpState: 'idle', // 'idle' | 'sending' | 'sent' | 'verifying' | 'verified'
                countdown: 0,
                timer: null,
                error: '',
                success: '',
                devCode: null,

                async sendOtp(isResend = false) {
                    this.error = '';
                    this.success = '';

                    const emailInput = this.email ? this.email.trim() : '';
                    if (!emailInput || !emailInput.includes('@') || !emailInput.includes('.')) {
                        this.error = 'Masukkan format alamat email yang valid sebelum meminta kode OTP.';
                        return;
                    }

                    this.otpState = 'sending';

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch('{{ route('register.otp.send') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify({ email: emailInput })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            const errorMsg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Gagal mengirim kode OTP.');
                            throw new Error(errorMsg);
                        }

                        this.otpState = 'sent';
                        this.devCode = data.dev_code || null;
                        this.success = 'Kode OTP 6-digit berhasil dikirim ke ' + emailInput;
                        this.startTimer(60);

                        if (window.gpaToast) {
                            window.gpaToast('Kode Terkirim', 'Kode OTP 6-digit telah dikirim ke email Anda.', 'success');
                        }
                    } catch (err) {
                        this.otpState = 'idle';
                        this.error = err.message || 'Terjadi gangguan saat mengirim OTP.';
                        if (window.gpaToast) {
                            window.gpaToast('Gagal Mengirim OTP', this.error, 'danger');
                        }
                    }
                },

                async verifyOtp() {
                    this.error = '';
                    this.success = '';

                    const code = (this.otpCode || '').trim();
                    if (code.length !== 6) {
                        this.error = 'Masukkan 6 digit angka kode OTP.';
                        return;
                    }

                    this.otpState = 'verifying';

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch('{{ route('register.otp.verify') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify({
                                email: (this.email || '').trim(),
                                code: code
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            const errorMsg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Kode OTP tidak valid.');
                            throw new Error(errorMsg);
                        }

                        this.otpState = 'verified';
                        this.success = 'Email berhasil diverifikasi!';
                        this.error = '';

                        if (window.gpaToast) {
                            window.gpaToast('Terverifikasi', 'Alamat email berhasil diverifikasi resmi.', 'success');
                        }
                    } catch (err) {
                        this.otpState = 'sent';
                        this.error = err.message || 'Kode OTP salah atau kedaluwarsa.';
                        if (window.gpaToast) {
                            window.gpaToast('Verifikasi Gagal', this.error, 'danger');
                        }
                    }
                },

                startTimer(seconds) {
                    if (this.timer) clearInterval(this.timer);
                    this.countdown = seconds;
                    this.timer = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            clearInterval(this.timer);
                        }
                    }, 1000);
                },

                resetVerification() {
                    this.otpState = 'idle';
                    this.otpCode = '';
                    this.error = '';
                    this.success = '';
                    if (this.timer) clearInterval(this.timer);
                    this.countdown = 0;
                },

                submitCheck(event) {
                    if (this.otpState !== 'verified') {
                        event.preventDefault();
                        this.error = 'Alamat email wajib diverifikasi dengan kode OTP sebelum mendaftar.';
                        if (window.gpaToast) {
                            window.gpaToast('Verifikasi Diperlukan', 'Silakan klik "Kirim Kode OTP" dan verifikasi email Anda terlebih dahulu.', 'warning');
                        }
                        const emailEl = event.target.querySelector('input[name="email"]');
                        if (emailEl) {
                            emailEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            emailEl.focus();
                        }
                        return false;
                    }
                }
            };
        }
    </script>
</body>

</html>
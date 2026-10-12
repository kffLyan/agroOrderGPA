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

                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
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
                                <label for="email-mobile"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Alamat Email *
                                </label>
                                <input id="email-mobile" name="email" type="email" autocomplete="email" required
                                    value="{{ old('email') }}" placeholder="nama@perusahaan.co.id"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
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
                                <label class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Tipe Kemitraan Klien *
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex cursor-pointer items-center gap-2 rounded-sm border border-line-strong bg-surface p-2.5 hover:bg-surface-muted">
                                        <input type="radio" name="client_type" value="REGULER" {{ old('client_type', 'REGULER') === 'REGULER' ? 'checked' : '' }}
                                            class="h-3.5 w-3.5 border-line-strong text-brand focus:ring-0">
                                        <span class="font-mono text-xs font-semibold text-ink">Reguler</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded-sm border border-line-strong bg-surface p-2.5 hover:bg-surface-muted">
                                        <input type="radio" name="client_type" value="B2B_KONTRAK" {{ old('client_type') === 'B2B_KONTRAK' ? 'checked' : '' }}
                                            class="h-3.5 w-3.5 border-line-strong text-brand focus:ring-0">
                                        <span class="font-mono text-xs font-semibold text-ink">Kontrak B2B</span>
                                    </label>
                                </div>
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

                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
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
                                <label for="email"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Alamat Email *
                                </label>
                                <input id="email" name="email" type="email" autocomplete="email" required
                                    value="{{ old('email') }}" placeholder="nama@perusahaan.co.id"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
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
                                <label class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Tipe Kemitraan Klien *
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex cursor-pointer items-center gap-2 rounded-sm border border-line-strong bg-surface p-2.5 hover:bg-surface-muted">
                                        <input type="radio" name="client_type" value="REGULER" {{ old('client_type', 'REGULER') === 'REGULER' ? 'checked' : '' }}
                                            class="h-3.5 w-3.5 border-line-strong text-brand focus:ring-0">
                                        <span class="font-mono text-xs font-semibold text-ink">Reguler (Spot Market)</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded-sm border border-line-strong bg-surface p-2.5 hover:bg-surface-muted">
                                        <input type="radio" name="client_type" value="B2B_KONTRAK" {{ old('client_type') === 'B2B_KONTRAK' ? 'checked' : '' }}
                                            class="h-3.5 w-3.5 border-line-strong text-brand focus:ring-0">
                                        <span class="font-mono text-xs font-semibold text-ink">Kontrak B2B Rutin</span>
                                    </label>
                                </div>
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
</body>

</html>
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
    <main class="flex min-h-screen w-full flex-col">
        <div class="grid w-full flex-grow grid-cols-1 border-b border-line lg:grid-cols-12">
            {{-- LEFT PANEL: Corporate & Security Trust Column --}}
            <x-auth.left-panel />

            {{-- RIGHT PANEL: Registration Form --}}
            <section
                class="flex flex-col items-center justify-center bg-surface px-6 py-10 md:px-12 md:py-14 lg:col-span-7">
                <div class="mx-auto flex w-full max-w-xl flex-col">
                    <div>
                        {{-- FORM HEADER --}}
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

                        {{-- REGISTRATION FORM --}}
                        <form method="POST" action="{{ route('register') }}" class="space-y-4">
                            @csrf

                            {{-- FIELD 1: PIC NAME --}}
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

                            {{-- FIELD 2: EMAIL --}}
                            <div>
                                <label for="email"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Alamat Email *
                                </label>
                                <input id="email" name="email" type="email" autocomplete="email"
                                    value="{{ old('email') }}" placeholder="nama@perusahaan.co.id"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('email')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- FIELD 3: PHONE --}}
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

                            {{-- FIELD 4: COMPANY NAME --}}
                            <div>
                                <label for="business_name"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Nama Usaha / PT *
                                </label>
                                <input id="business_name" name="business_name" type="text" required
                                    autocomplete="organization" value="{{ old('business_name') }}"
                                    placeholder="Contoh: PT Kuliner Prima Nusantara / Restoran Boga Rasa"
                                    class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                                @error('business_name')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- FIELD 5: SHIPPING ADDRESS --}}
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

                            {{-- FIELD 6: PASSWORD --}}
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

                            {{-- FIELD 7: CONFIRM PASSWORD --}}
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

                            {{-- AGREEMENT CHECKBOX --}}
                            <div class="pt-1">
                                <label class="inline-flex cursor-pointer items-start gap-2">
                                    <input type="checkbox" name="terms" value="1" required
                                        class="h-3.5 w-3.5 mt-0.5 shrink-0 cursor-pointer rounded-sm border-line-strong bg-surface text-brand focus:ring-0 focus:ring-offset-0">
                                    <span class="text-xs leading-snug text-ink">Saya menyetujui Ketentuan Layanan dan
                                        Kebijakan Privasi AgroOrder GPA.</span>
                                </label>
                            </div>

                            {{-- PRIMARY SUBMISSION BUTTON --}}
                            <div class="pt-2">
                                <button type="submit"
                                    class="flex h-10 w-full items-center justify-center space-x-2 rounded-sm border border-brand bg-brand font-mono text-xs font-semibold uppercase tracking-wider text-white transition-colors hover:bg-brand-hover active:translate-y-px">
                                    <span>DAFTAR AKUN SEKARANG</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- FOOTER LOGIN CALLOUT --}}
                    <div
                        class="mt-8 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4 text-center sm:text-left">
                        <span class="text-xs text-ink">
                            Sudah memiliki akun AgroOrder GPA?
                        </span>
                        <a href="{{ route('login') }}"
                            class="flex items-center gap-1 font-mono text-xs font-semibold text-ink underline underline-offset-4 hover:text-brand">
                            <span>[ Masuk ke Portal Akun ]</span>
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

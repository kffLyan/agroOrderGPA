<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk / Login Portal Multi-Peran - AgroOrder GPA</title>

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
            <section
                class="flex flex-col justify-center border-b border-white/15 bg-brand p-8 md:p-12 lg:col-span-5 lg:border-b-0 lg:border-r lg:p-14">
                <div class="mb-[48px] flex justify-center md:mb-[56px]">
                    <img src="{{ asset('gpa.png') }}" alt="AgroOrder GPA"
                        class="h-[110px] w-auto max-w-full object-contain md:h-[140px] xl:h-[160px]">
                </div>

                <div>
                    <span
                        class="mb-5 inline-flex items-center gap-1.5 rounded-sm border border-white/25 px-2 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-white/85">
                        <span class="h-1.5 w-1.5 animate-pulse-ring rounded-full bg-accent"></span>
                        Infrastruktur Logistik Agribisnis
                    </span>

                    <h1 class="mb-3 text-xl font-semibold leading-tight tracking-tight text-white md:text-2xl">
                        Portal Terpadu Rantai Pasok Agribisnis
                    </h1>
                    <p class="mb-8 max-w-lg text-xs leading-relaxed text-white/80 md:text-sm">
                        Infrastruktur digital enterprise untuk pengelolaan logistik agrikultur end-to-end, penimbangan
                        metrologi presisi, dan verifikasi sertifikasi batch real-time.
                    </p>

                    <div class="mb-10 max-w-lg space-y-5">
                        @foreach ([
        ['Single Source of Truth Rantai Pasok', 'Sinkronisasi data multi-titik dari lahan produksi, hub konsolidasi, hingga titik bongkar pembeli.'],
        ['Kepatuhan Penimbangan Riil (Rule 04 & 05 Net Weight)', 'Integrasi jembatan timbang digital bersertifikasi metrologi legal mencegah deviasi tara.'],
        ['Audit Trail Forensik Kriptografis SHA-256 (Rule 09)', 'Setiap mutasi manifest, penyesuaian mutu, dan faktur ditandatangani hash permanen.'],
        ['Otorisasi Berbasis Peran (RBAC 5 Aktor)', 'Pemisahan wewenang operasional ketat untuk integritas tata kelola korporat.'],
    ] as [$highlight, $detail])
                            <div class="flex items-start space-x-3">
                                <div
                                    class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-sm border border-white/40 bg-surface">
                                    <span class="material-symbols-outlined text-[13px] text-brand">check</span>
                                </div>
                                <div>
                                    <h2 class="font-sans text-xs font-medium leading-none text-white md:text-sm">
                                        {{ $highlight }}
                                    </h2>
                                    <p class="mt-1 font-sans text-[11px] text-white/85 md:text-xs">
                                        {{ $detail }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- RIGHT PANEL: Clean Full-Height Form View --}}
            <section
                class="flex flex-col items-center justify-center bg-surface px-6 py-10 md:px-12 md:py-14 lg:col-span-7">
                <div class="mx-auto flex w-full max-w-xl flex-col">
                    <div>
                        <span
                            class="mb-4 inline-flex items-center gap-1.5 rounded-sm border border-line bg-surface-muted px-2 py-1 font-mono text-[10px] font-semibold uppercase tracking-wider text-ink-muted">
                            <span class="material-symbols-outlined text-[13px] text-success">shield_person</span>
                            Subsistem Akses Klien B2B
                        </span>

                        <div class="mb-6">
                            <h2 class="text-xl font-semibold tracking-tight text-ink">
                                Masuk ke Portal AgroOrder GPA
                            </h2>
                            <p class="mt-1 text-xs text-ink">
                                Silakan pilih portal akses sesuai peran dan masukkan kredensial akun Anda.
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

                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="email"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Alamat Email Perusahaan / ID Akun GPA *
                                </label>
                                <div class="relative">
                                    <input id="email" name="email" type="email" required autofocus
                                        autocomplete="username" inputmode="email"
                                        value="{{ old('email') }}"
                                        placeholder="nama@perusahaan.co.id atau ID: OP-4091"
                                        class="w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0"
                                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                    <div class="pointer-events-none absolute right-3 top-2.5 text-success">
                                        <span class="material-symbols-outlined text-sm">badge</span>
                                    </div>
                                </div>
                                @error('email')
                                    <p id="email-error" class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password"
                                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                                    Kata Sandi / Kunci Otorisasi *
                                </label>
                                <div class="relative" x-data="{ show: false }">
                                    <input id="password" name="password" type="password" required
                                        autocomplete="current-password" placeholder="••••••••••••••••"
                                        class="w-full rounded-sm border-line-strong bg-surface px-3 pr-24 font-mono text-xs text-ink placeholder:font-mono focus:border-brand focus:ring-0"
                                        x-bind:type="show ? 'text' : 'password'">
                                    <button type="button"
                                        class="absolute right-1.5 top-2 flex items-center gap-1 rounded-sm border border-line bg-surface-muted px-2 py-1 font-mono text-[10px] text-success transition-colors hover:bg-surface-raised hover:text-brand focus:outline-none"
                                        @click="show = ! show">
                                        <span class="material-symbols-outlined text-[12px]"
                                            x-text="show ? 'visibility_off' : 'visibility'"></span>
                                        <span x-text="show ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                <label class="inline-flex cursor-pointer items-center gap-2">
                                    <input name="remember" type="checkbox" value="1"
                                        class="h-3.5 w-3.5 cursor-pointer rounded-sm border-line-strong bg-surface text-brand focus:ring-0 focus:ring-offset-0"
                                        @checked(old('remember'))>
                                    <span class="text-xs text-ink">Ingat Sesi di Perangkat Ini (30 Hari)</span>
                                </label>
                                <a href="{{ route('password.request') }}"
                                    class="font-mono text-[11px] text-success underline underline-offset-2 hover:text-brand">
                                    Lupa Kata Sandi / Reset PIN?
                                </a>
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="flex h-10 w-full items-center justify-center space-x-2 rounded-sm border border-brand bg-brand font-mono text-xs font-semibold uppercase tracking-wider text-white transition-colors hover:bg-brand-hover active:translate-y-px">
                                    <span>Masuk</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div
                        class="mt-8 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4 text-center sm:text-left">
                        <span class="text-xs text-success">Belum memiliki akun Klien B2B?</span>
                        <a href="{{ route('register') }}"
                            class="flex items-center gap-1 font-mono text-xs font-semibold text-ink underline underline-offset-4 hover:text-brand">
                            <span>[ Daftarkan Entitas Perusahaan Anda ]</span>
                            <span class="material-symbols-outlined text-xs">open_in_new</span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <x-gpa.toast />
</body>

</html>
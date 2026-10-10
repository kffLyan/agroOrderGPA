<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal distribusi komoditas pertanian segar Jawa Barat AgroOrder GPA: pendaftaran klien, katalog harian, dan pelacakan pengiriman.">

    <title>@yield('title', config('app.name', 'AgroOrder GPA'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen flex-col bg-canvas font-sans text-ink antialiased selection:bg-surface-raised">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
        Lewati ke konten utama
    </a>

    <header class="sticky top-0 z-40 bg-surface/90 shadow-card backdrop-blur">
        <div class="mx-auto flex h-14 w-full max-w-6xl items-center justify-between gap-3 px-4 sm:gap-4 sm:px-6">
            <x-gpa.brand href="{{ route('login') }}" />

            <div class="flex items-center gap-2">
                <span class="hidden items-center gap-1.5 rounded-sm border border-line bg-surface-muted px-2 py-1 gpa-mono-xs font-semibold uppercase tracking-wider text-ink-muted sm:inline-flex">
                    <span class="h-1.5 w-1.5 animate-pulse-ring rounded-full bg-success"></span>
                    3 Hub Aktif
                </span>
                @auth
                    <x-gpa.btn :href="route('dashboard')" variant="secondary" size="sm">Dashboard</x-gpa.btn>
                @else
                    <x-gpa.btn :href="route('login')" variant="ghost" size="sm">Masuk</x-gpa.btn>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content" class="flex-1">
        {{ $slot }}
    </main>

    <footer class="mt-10 bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="space-y-3">
                    <x-gpa.brand compact />
                    <p class="max-w-sm text-2xs leading-relaxed text-ink-muted">
                        Setiap transaksi komoditas dihitung mutlak berdasarkan hasil penimbangan bersih gudang
                        (Rule 04 &amp; 05) menggunakan timbangan digital terkalibrasi Badan Metrologi Legal RI.
                        Harga pada katalog harian bersifat spot dan dapat berubah tanpa pemberitahuan
                        sebelumnya.
                    </p>
                </div>

                <div class="space-y-2">
                    <p class="gpa-eyebrow">Navigasi Portal</p>
                    <ul class="space-y-1.5">
                        <li><a href="{{ route('login') }}" class="text-2xs text-ink-muted transition-colors hover:text-ink">Masuk ke Portal</a></li>
                        <li><a href="{{ route('register') }}" class="text-2xs text-ink-muted transition-colors hover:text-ink">Pendaftaran Klien Baru</a></li>
                        <li><a href="{{ route('password.request') }}" class="text-2xs text-ink-muted transition-colors hover:text-ink">Atur Ulang Kata Sandi</a></li>
                        <li><a href="{{ url('/') }}" class="text-2xs text-ink-muted transition-colors hover:text-ink">Katalog Komoditas Harian</a></li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <p class="gpa-eyebrow">Informasi Operasional</p>
                    <dl class="space-y-1.5 gpa-mono-xs text-ink-muted">
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-subtle">Jam layanan</dt>
                            <dd class="text-right font-semibold text-ink">03:00 - 21:00 WIB</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-subtle">Gudang induk</dt>
                            <dd class="text-right font-semibold text-ink">Ciwidey / Lembang / Subang</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-subtle">Status sistem</dt>
                            <dd class="flex items-center gap-1.5 text-right font-semibold text-success">
                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span> Normal
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-ink-subtle">Dokumen revisi</dt>
                            <dd class="text-right font-semibold text-ink">REG-GPA-A / 2025.Q2</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div
                class="mt-6 flex flex-col items-start justify-between gap-2 border-t border-line-faint pt-4 gpa-mono-xs text-ink-subtle sm:flex-row sm:items-center">
                <p>&copy; {{ now()->year }} AgroOrder GPA &middot; Seluruh hak cipta dilindungi.</p>
                <p>Data klien diproses sesuai kebijakan privasi GPA.</p>
            </div>
        </div>
    </footer>

    <x-gpa.toast />
</body>

</html>

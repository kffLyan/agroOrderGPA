<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="AgroOrder GPA: mitra rantai pasok hortikultura untuk Horeca, Katering Korporat, dan Ritel Modern dengan standar jaminan berat riil anti-susut, metrologi legal, dan logistik subuh.">

    <title>@yield('title', 'Beranda | AgroOrder GPA')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas font-sans text-ink antialiased selection:bg-surface-raised" x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false">
    <x-gpa.loading-screen />

    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-[70] focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
        Lewati ke konten utama
    </a>

    @php
        $navItems = [
            ['label' => 'Beranda', 'route' => 'public.home', 'pattern' => '/|beranda'],
            ['label' => 'Tentang GPA', 'route' => 'public.about', 'pattern' => 'tentang-gpa'],
            ['label' => 'Rantai Pasok', 'route' => 'public.supply-chain', 'pattern' => 'rantai-pasok'],
            ['label' => 'Komoditas', 'route' => 'public.catalog', 'pattern' => 'katalog-publik'],
            ['label' => 'Mitra Kontrak', 'route' => 'public.mitra-kontrak', 'pattern' => 'mitra-kontrak'],
            ['label' => 'Galeri', 'route' => 'public.gallery', 'pattern' => 'galeri'],
            ['label' => 'Kontak', 'route' => 'public.kontak', 'pattern' => 'kontak'],
        ];
    @endphp

    <div class="sticky top-0 z-50 shadow-sub">
        <header class="border-b border-line-faint bg-brand text-white">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-2 px-3 py-2.5 sm:px-6 sm:gap-3">
                <a href="{{ route('public.home') }}" class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg">
                        <img src="{{ asset('gpaleaves.png') }}" alt="Logo AgroOrder GPA" class="h-8 w-8 object-contain">
                    </span>
                    <span class="flex min-w-0 flex-col leading-none">
                        <span class="text-base font-extrabold uppercase tracking-tight sm:text-lg md:text-xl">AgroOrder</span>
                        <span class="mt-0.5 font-mono text-[9px] uppercase tracking-[0.28em] text-white/60">Green Pasundan Agriculture</span>
                    </span>
                </a>

                <nav aria-label="Navigasi utama" class="hidden flex-1 justify-center lg:flex">
                    <ul class="flex items-center gap-1 whitespace-nowrap">
                        @foreach ($navItems as $item)
                            @php
                                $active = request()->routeIs($item['route']) || request()->is($item['pattern']);
                            @endphp
                            <li>
                                <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                                    @class([
                                        'block rounded px-2.5 py-1.5 font-mono text-[10px] uppercase tracking-[0.18em] transition-colors',
                                        'border-b border-accent text-accent' => $active,
                                        'text-white/90 hover:text-accent' => ! $active,
                                    ])>{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="hidden shrink-0 items-center gap-1.5 text-2xs lg:flex">
                    <x-gpa.btn :href="route('public.catalog')" variant="accent" size="sm">Katalog Publik</x-gpa.btn>
                    <x-gpa.btn :href="route('login')" variant="inverse" size="sm">Login Portal</x-gpa.btn>
                </div>

                <button type="button"
                    class="inline-flex items-center justify-center rounded-lg p-1.5 text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent lg:hidden"
                    @click="mobileOpen = true" :aria-expanded="mobileOpen.toString()" aria-controls="mobile-drawer-nav">
                    <span class="sr-only">Buka navigasi utama</span>
                    <x-gpa.icon name="menu" class="h-5 w-5" />
                </button>
            </div>
        </header>
    </div>

    {{-- Mobile side drawer (3/4 width, overlay) --}}
    <div x-cloak x-show="mobileOpen" class="fixed inset-0 z-[60] lg:hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-[1px]" x-show="mobileOpen" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="mobileOpen = false"></div>

        <aside id="mobile-drawer-nav" class="absolute inset-y-0 right-0 flex h-full w-[78%] max-w-[420px] flex-col bg-brand text-white shadow-2xl"
            x-show="mobileOpen" x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full" @click.away="mobileOpen = false">
            <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg">
                        <img src="{{ asset('gpaleaves.png') }}" alt="Logo AgroOrder GPA" class="h-8 w-8 object-contain">
                    </span>
                    <div class="flex min-w-0 flex-col leading-none">
                        <span class="text-base font-extrabold uppercase tracking-tight">AgroOrder GPA</span>
                        <span class="mt-0.5 font-mono text-[9px] uppercase tracking-[0.28em] text-white/60">Green Pasundan Agriculture</span>
                    </div>
                </div>
                <button type="button"
                    class="inline-flex items-center justify-center rounded-lg p-1.5 text-white transition-colors hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    @click="mobileOpen = false" aria-label="Tutup navigasi">
                    <x-gpa.icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-2 py-2">
                <ul class="flex flex-col gap-1">
                    @foreach ($navItems as $item)
                        @php
                            $active = request()->routeIs($item['route']) || request()->is($item['pattern']);
                        @endphp
                        <li>
                            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                                @click="mobileOpen = false"
                                @class([
                                    'flex items-center justify-between rounded-lg px-3 py-2.5 font-mono text-[11px] uppercase tracking-[0.18em] transition-colors',
                                    'bg-white/10 text-accent' => $active,
                                    'text-white/90 hover:bg-white/5 hover:text-accent' => ! $active,
                                ])>
                                <span>{{ $item['label'] }}</span>
                                @if ($active)
                                    <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="flex flex-col gap-2 border-t border-white/10 px-4 py-4">
                <x-gpa.btn :href="route('public.catalog')" variant="accent" size="sm" class="w-full justify-center" @click="mobileOpen = false">Katalog Publik</x-gpa.btn>
                <x-gpa.btn :href="route('login')" variant="inverse" size="sm" class="w-full justify-center" @click="mobileOpen = false">Login Portal</x-gpa.btn>
            </div>
        </aside>
    </div>

    <main id="main-content">
        @yield('content')
    </main>

    @include('public.partials.footer')

    <x-gpa.toast />
    <x-gpa.notifications />
</body>

</html>

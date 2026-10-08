<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="AgroOrder GPA: mitra rantai pasok hortikultura untuk Horeka, Katering Korporat, dan Ritel Modern dengan standar zero-phantom weight, metrologi legal, dan logistik subuh.">

    <title>@yield('title', 'Beranda | AgroOrder GPA')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas font-sans text-ink antialiased selection:bg-surface-raised">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
        Lewati ke konten utama
    </a>

    <div class="sticky top-0 z-40 shadow-sub">
        @include('public.partials.status-bar', ['statusBar' => $statusBar])

        <header class="border-b border-line-faint bg-brand text-white">
            <div class="mx-auto flex w-full max-w-6xl items-center gap-2 px-4 py-3 sm:gap-3 sm:px-6">
                <a href="{{ route('public.home') }}" class="flex shrink-0 items-center gap-3">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent text-brand-deep">
                        <x-gpa.icon name="leaf" class="h-5 w-5" />
                    </span>
                    <span class="hidden min-w-0 flex-col leading-none sm:flex">
                        <span class="truncate text-lg font-extrabold uppercase tracking-tight md:text-xl">AgroOrder
                            GPA</span>
                        <span class="mt-1 hidden truncate gpa-mono-xs text-white/70 md:block">Green Pasundan Agriculture</span>
                    </span>
                </a>

                <nav aria-label="Navigasi utama" class="min-w-0 flex-1 overflow-x-auto">
                    <ul class="mx-auto flex w-max items-center gap-1 whitespace-nowrap">
                        @foreach ($navigation as $item)
                            @php
                                $href = match (true) {
                                    str_starts_with($item['href'], '#') => $item['href'],
                                    str_starts_with($item['href'], '/') => url($item['href']),
                                    default => route($item['href']),
                                };

                                $active = match (true) {
                                    str_starts_with($item['href'], '#'), str_contains($item['href'], '#') => false,
                                    str_starts_with($item['href'], '/') => request()->is(trim($item['href'], '/')),
                                    default => request()->routeIs($item['href']),
                                };
                            @endphp
                            <li>
                                <a href="{{ $href }}" @if ($active) aria-current="page" @endif
                                    @class([
                                        'block rounded px-2.5 py-1.5 gpa-mono-xs transition-colors',
                                        'border-b-2 border-accent text-accent' => $active,
                                        'text-white hover:text-accent' => ! $active,
                                    ])>{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="flex shrink-0 items-center gap-1.5 text-2xs">
                    <x-gpa.btn :href="route('public.catalog')" variant="accent" size="sm">Katalog Publik</x-gpa.btn>
                    <x-gpa.btn :href="route('login')" variant="inverse" size="sm">Login Portal</x-gpa.btn>
                </div>
            </div>
        </header>
    </div>

    <main id="main-content">
        @yield('content')
    </main>

    @include('public.partials.footer', ['footer' => $footer])

    <x-gpa.toast />
</body>

</html>
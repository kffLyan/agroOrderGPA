@php
    $detectedRole = $detectedRole ?? (auth()->check() ? auth()->user()->role : (
        request()->is('direktur*') || request()->is('*/direktur*') ? 'DIREKTUR' : (
        request()->is('sekretaris*') || request()->is('*/sekretaris*') ? 'SEKRETARIS' : (
        request()->is('koordinator*') || request()->is('*/koordinator*') ? 'KOORDINATOR' : (
        request()->is('armada*') || request()->is('*/armada*') ? 'ARMADA' : (
        request()->is('klien*') || request()->is('*/klien*') ? 'KLIEN' : null
    ))))));

    $dashboardUrl = $dashboardUrl ?? match ($detectedRole) {
        'DIREKTUR'    => route('direktur.dashboard'),
        'SEKRETARIS'  => route('sekretaris.dashboard'),
        'KOORDINATOR' => route('koordinator.dashboard'),
        'ARMADA'      => route('armada.dashboard'),
        'KLIEN'       => route('klien.dashboard'),
        default       => auth()->check() ? route('dashboard') : route('public.home'),
    };

    $dashboardLabel = $dashboardLabel ?? match ($detectedRole) {
        'DIREKTUR'    => 'Kembali ke Dashboard Direktur',
        'SEKRETARIS'  => 'Kembali ke Dashboard Sekretaris',
        'KOORDINATOR' => 'Kembali ke Dashboard Koordinator',
        'ARMADA'      => 'Kembali ke Dashboard Armada',
        'KLIEN'       => 'Kembali ke Dashboard Klien',
        default       => auth()->check() ? 'Kembali ke Dashboard' : 'Kembali ke Beranda',
    };
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') - @yield('title', 'Terjadi Kesalahan') &middot; {{ config('app.name', 'AgroOrder GPA') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg: #f8faf3;
            --brand: #153a01;
            --ink: #1b2412;
            --body: #4a5640;
            --line: #e5ebe0;
            --accent: #bef377;
            --hl: 1;
        }

        body {
            background-color: var(--bg);
            color: var(--ink);
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .gpa-error-scope .wrap {
            max-width: 580px;
            margin: 0 auto;
            text-align: center;
        }

        .gpa-error-scope svg {
            width: 104px;
            height: auto;
            overflow: visible;
            margin: 0 auto;
        }

        .gpa-error-scope .leaf {
            clip-path: inset(0 100% 0 0);
        }

        .gpa-error-scope.run .leaf {
            animation: wipe .6s cubic-bezier(.45, 0, .25, 1) forwards;
        }

        .gpa-error-scope.run .l2 {
            animation-delay: .55s;
        }

        .gpa-error-scope.run .l3 {
            animation-delay: 1.1s;
        }

        @keyframes wipe {
            to {
                clip-path: inset(0 0 0 0);
            }
        }

        .gpa-error-scope .eyebrow {
            margin: 28px 0 0;
            font: 700 11px "JetBrains Mono", ui-monospace, monospace;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--brand);
            opacity: .8;
        }

        .gpa-error-scope .code {
            margin: 12px 0 0;
            font: 800 clamp(68px, 16vw, 104px)/1 "JetBrains Mono", ui-monospace, monospace;
            color: var(--brand);
            letter-spacing: -0.04em;
        }

        .gpa-error-scope .code span {
            position: relative;
            display: inline-block;
            isolation: isolate;
        }

        .gpa-error-scope .code span::after {
            content: "";
            position: absolute;
            inset: auto 0 4px 0;
            height: 16px;
            border-radius: 4px;
            background: var(--accent);
            opacity: var(--hl);
            z-index: -1;
        }

        .gpa-error-scope h1 {
            margin: 20px 0 0;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--ink);
        }

        .gpa-error-scope .desc {
            margin: 14px 0 0;
            color: var(--body);
            line-height: 1.65;
            font-size: 15px;
        }

        .gpa-error-scope .actions {
            margin-top: 32px;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .gpa-error-scope .btn {
            font: 700 12px "JetBrains Mono", ui-monospace, monospace;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            padding: 12px 22px;
            border-radius: 10px;
            border: 1px solid var(--line);
            color: var(--brand);
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all .2s ease;
        }

        .gpa-error-scope .btn:hover {
            background: rgba(190, 243, 119, .25);
            border-color: var(--brand);
            transform: translateY(-1px);
        }

        .gpa-error-scope .btn.accent {
            background: var(--accent);
            border-color: var(--accent);
            color: #153a01;
            font-weight: 800;
        }

        .gpa-error-scope .btn.accent:hover {
            opacity: .95;
            box-shadow: 0 4px 12px rgba(190, 243, 119, 0.5);
        }

        @media (prefers-reduced-motion: reduce) {
            .gpa-error-scope .leaf {
                clip-path: none;
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- Top Minimal Brand Bar: Identitas resmi tanpa menu nav publik yang mengganggu -->
    <header class="w-full px-6 py-6 sm:px-10">
        <div class="mx-auto flex max-w-5xl items-center justify-between">
            <a href="{{ $dashboardUrl }}" class="flex items-center gap-3 no-underline">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface border border-line-hair shadow-sm">
                    <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-6 w-6 object-contain">
                </div>
                <div class="flex flex-col">
                    <span class="font-sans text-base font-extrabold uppercase tracking-tight text-ink">AgroOrder GPA</span>
                    <span class="font-mono text-[10px] font-semibold text-ink-quiet uppercase">Koperasi Produsen Agro Green Pasundan</span>
                </div>
            </a>
            @if ($detectedRole || auth()->check())
                <a href="{{ $dashboardUrl }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 font-mono text-xs font-semibold text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="dashboard" class="h-3.5 w-3.5 text-brand" />
                    <span>Ke Dashboard</span>
                </a>
            @endif
        </div>
    </header>

    <!-- Main Pure Error Canvas -->
    <main class="flex flex-1 items-center justify-center px-5 py-8 gpa-error-scope run">
        <div class="wrap" id="stage">
            <svg viewBox="330 90 1280 1710" aria-hidden="true">
                <defs>
                    <linearGradient id="gl" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#143a00"/><stop offset=".5" stop-color="#2f6600"/><stop offset="1" stop-color="#5b9e04"/></linearGradient>
                    <linearGradient id="gr" x1="0" y1="1" x2="1" y2="0"><stop offset="0" stop-color="#5b9e04"/><stop offset=".6" stop-color="#4a8a00"/><stop offset="1" stop-color="#bddc00"/></linearGradient>
                    <linearGradient id="gt" x1="0" y1="1" x2="1" y2="0"><stop offset="0" stop-color="#163f00"/><stop offset=".4" stop-color="#5f9500"/><stop offset="1" stop-color="#c0dc00"/></linearGradient>
                    <path id="pl" d="M365 918 C620 960 900 1180 962 1540 L965 1775 C650 1700 365 1400 365 918 Z"/>
                    <path id="pt" d="M965 118 C800 340 655 640 650 975 C800 1070 900 1190 965 1325 C1030 1190 1140 1060 1285 975 C1270 640 1130 340 965 118 Z"/>
                    <path id="pr" d="M1568 918 C1312 960 1032 1180 970 1540 L967 1775 C1282 1700 1568 1400 1568 918 Z"/>
                </defs>
                <g fill="currentColor" opacity=".08" style="color:var(--brand)"><use href="#pl"/><use href="#pt"/><use href="#pr"/></g>
                <use class="leaf l1" href="#pl" fill="url(#gl)"/>
                <use class="leaf l2" href="#pt" fill="url(#gt)"/>
                <use class="leaf l3" href="#pr" fill="url(#gr)"/>
            </svg>
            <p class="eyebrow">GPA Error Protocol</p>
            <p class="code"><span>@yield('code')</span></p>
            <h1>@yield('title')</h1>
            <p class="desc">@yield('description')</p>
            <div class="actions">
                @yield('actions')
            </div>
        </div>
    </main>

    <!-- Clean Minimal Footer -->
    <footer class="w-full py-6 text-center text-xs text-ink-quiet">
        <p>&copy; {{ date('Y') }} Koperasi Produsen Agro Green Pasundan. Sistem Ekosistem Rantai Pasok Terpadu.</p>
    </footer>
</body>

</html>

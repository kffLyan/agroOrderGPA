<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Konsol mobile Armada AgroOrder GPA untuk awak pengiriman lapangan.">

    <title>@yield('title', 'Konsol Armada') &middot; {{ config('app.name', 'Green Pasundan Agrikultur') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            header, nav, .no-print { display: none !important; }
            body { background: #fff !important; }
            main { padding: 0 !important; }
        }
    </style>
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    <x-gpa.loading-screen />
    @php
        $operatorRole = $operator['role'] ?? 'Supir Pengiriman';
        $operatorName = $operator['name'] ?? (auth()->user()?->name ?? 'Supir Armada');
        $operatorInitials = $operator['initials'] ?? (auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'SA');
        $armadaPlate = $activePlate ?? ($plate ?? ($header['plate'] ?? '-'));
        $brand = $header['brand'] ?? 'AGROORDER GPA';
        $armadaRole = $header['role'] ?? 'Armada Logistik';
        $headerTone = $header['tone'] ?? 'light';
        $activeKey = $activeNav ?? 'tasks';
        $navigation = $navigation ?? [
            ['key' => 'tasks', 'label' => 'Tugas', 'icon' => 'clipboard'],
            ['key' => 'dispatch', 'label' => 'Surat Jalan', 'icon' => 'file-text'],
            ['key' => 'pod', 'label' => 'Bukti Kirim', 'icon' => 'qrcode'],
            ['key' => 'fleet', 'label' => 'Armada', 'icon' => 'truck'],
            ['key' => 'profile', 'label' => 'Profil', 'icon' => 'user'],
        ];
        $navigationRoutes = [
            'tasks' => 'armada.tugas',
            'dispatch' => 'armada.surat-jalan',
            'pod' => 'armada.bukti-terima',
            'fleet' => 'armada.status-kendaraan',
            'profile' => 'profile.edit',
        ];
        $headerShell = $headerTone === 'light'
            ? 'sticky top-0 z-40 flex h-14 items-center gap-2.5 border-b-2 border-surface-shell bg-surface px-4 text-ink'
            : 'sticky top-0 z-40 flex h-14 items-center gap-2.5 bg-brand px-3 text-white';

        $brandTitle = $headerTone === 'light'
            ? 'truncate font-inter text-sm font-extrabold uppercase leading-5 tracking-[0.7px] text-ink'
            : 'truncate font-inter text-[13px] font-extrabold uppercase leading-4 text-white';

        $brandRole = $headerTone === 'light'
            ? 'truncate font-mono text-[10px] font-semibold leading-3 tracking-[1.08px] text-ink-body'
            : 'truncate text-[10px] font-medium leading-3 text-white/80 gpa-meta';

        $avatarShell = $headerTone === 'light'
            ? 'inline-flex h-8 w-8 items-center justify-center rounded-full bg-surface-disabled font-mono text-xs font-bold leading-6 text-ink outline outline-1 -outline-offset-1 outline-ink-strong'
            : 'inline-flex h-7 w-7 items-center justify-center rounded-full bg-surface-disabled text-[11px] font-bold text-ink ring-1 ring-inset ring-accent';
    @endphp

    <div x-data="{}">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
            Lewati ke konten utama
        </a>

        <header class="{{ $headerShell }}">
            <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-8 w-8 shrink-0 object-contain">

            <div class="flex min-w-0 flex-1 flex-col">
                <span class="{{ $brandTitle }}">{{ $brand }}</span>
                <span class="{{ $brandRole }}">{{ $armadaRole }} &bull; [{{ $armadaPlate }}]</span>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Keluar / Logout"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-surface-disabled text-ink-body transition-colors hover:bg-danger/20 hover:text-danger">
                        <x-gpa.icon name="log-out" class="h-4 w-4" />
                    </button>
                </form>
                <span class="{{ $avatarShell }}" title="{{ $operatorName }} - {{ $operatorRole }}"
                    aria-label="{{ $operatorName }} - {{ $operatorRole }}">{{ $operatorInitials }}</span>
            </div>
        </header>

        <main id="main-content" class="bg-canvas py-4">
            @if (session('success'))
                <div class="mx-auto mb-4 flex w-full max-w-[512px] items-center gap-2 rounded-xl bg-accent/20 p-3 outline outline-1 -outline-offset-1 outline-success-deep text-ink">
                    <x-gpa.icon name="check-circle" class="h-5 w-5 shrink-0 text-success-deep" />
                    <p class="text-xs font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mx-auto mb-4 flex w-full max-w-[512px] items-center gap-2 rounded-xl bg-danger/10 p-3 outline outline-1 -outline-offset-1 outline-danger text-danger">
                    <x-gpa.icon name="alert-triangle" class="h-5 w-5 shrink-0 text-danger" />
                    <p class="text-xs font-semibold">{{ session('error') }}</p>
                </div>
            @endif

            @yield('content')
        </main>

        <nav class="fixed inset-x-0 bottom-0 z-40 border-t-2 border-line-hair bg-surface shadow-pop"
            aria-label="Navigasi armada">
            <div class="mx-auto flex h-16 w-full max-w-[512px] items-start">
                @foreach ($navigation as $item)
                    @php
                        $itemKey = $item['key'] ?? '';
                        $isActive = $itemKey === $activeKey;
                        $isLinked = isset($navigationRoutes[$itemKey]);
                        $iconSize = match ($itemKey) {
                            'tasks' => 'h-[17px] w-[15px]',
                            'dispatch' => 'h-[17px] w-[13px]',
                            'pod' => 'h-[18px] w-[17px]',
                            'fleet' => 'h-[13px] w-[18px]',
                            default => 'h-[14px] w-[14px]',
                        };
                    @endphp

                    @if ($isActive)
                        <span aria-current="page" class="flex h-full flex-1 justify-center">
                            <span class="relative -top-2 flex h-[62px] w-14 shrink-0 flex-col items-center justify-center rounded-b-lg border-t-4 border-accent bg-brand py-1 text-accent shadow-[0px_5px_6px_rgba(0,0,0,0.25)]">
                                <x-gpa.icon :name="$item['icon']" class="{{ $iconSize }} shrink-0" />
                                <span class="pt-0.5 text-center font-mono text-[11px] font-semibold leading-5">{{ $item['label'] }}</span>
                            </span>
                        </span>
                    @elseif ($isLinked)
                        <a href="{{ route($navigationRoutes[$itemKey]) }}"
                            class="relative top-0.5 flex h-[62px] flex-1 flex-col items-center justify-center gap-0.5 bg-surface px-1 py-1 text-ink-body transition-colors hover:text-ink">
                            <x-gpa.icon :name="$item['icon']" class="{{ $iconSize }} shrink-0" />
                            <span class="pt-0.5 text-center font-mono text-[11px] font-normal leading-5">{{ $item['label'] }}</span>
                        </a>
                    @else
                        <a href="{{ route('profile.edit') }}"
                            class="relative top-0.5 flex h-[62px] flex-1 flex-col items-center justify-center gap-0.5 bg-surface px-1 py-1 text-ink-body transition-colors hover:text-ink">
                            <x-gpa.icon :name="$item['icon']" class="{{ $iconSize }} shrink-0" />
                            <span class="pt-0.5 text-center font-mono text-[11px] font-normal leading-5">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>

        <x-gpa.toast />
        <x-gpa.notifications />
        <x-gpa.dialog />
    </div>
</body>

</html>

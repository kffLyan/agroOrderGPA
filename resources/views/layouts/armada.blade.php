<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Konsol mobile Armada AgroOrder GPA untuk awak armada lapangan.">

    <title>@yield('title', 'Konsol Armada').' &middot; '.config('app.name', 'AgroOrder GPA')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    @php
        $operatorRole = $operator['role'] ?? 'Supir Armada';
        $operatorName = $operator['name'] ?? config('app.name');
        $operatorInitials = $operator['initials'] ?? 'SF';
        $armadaPlate = $header['plate'] ?? '-';
        $brand = $header['brand'] ?? 'AGROORDER GPA';
        $armadaRole = $header['role'] ?? 'Armada Logistik';
        $headerTone = $header['tone'] ?? 'brand';
        $activeKey = $activeNav ?? '';
        $navigation = $navigation ?? [];
        $navigationRoutes = [
            'tasks' => 'armada.tasks',
            'dispatch' => 'armada.dispatch',
            'pod' => 'armada.pod',
            'fleet' => 'armada.status',
        ];
        $component = $component ?? 'armadaPod';
        $componentPayload = $componentPayload ?? [];

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
            ? 'inline-flex h-8 w-8 items-center justify-center rounded-full bg-surface-disabled font-mono text-base font-bold leading-6 text-ink outline outline-1 -outline-offset-1 outline-ink-strong'
            : 'inline-flex h-7 w-7 items-center justify-center rounded-full bg-surface-disabled text-[11px] font-bold text-ink ring-1 ring-inset ring-accent';
    @endphp

    <div x-data="{{ $component }}(@js($componentPayload))">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
            Lewati ke konten utama
        </a>

        <header class="{{ $headerShell }}">
            <x-gpa.brand-mark class="h-8 w-8 shrink-0 {{ $headerTone === 'light' ? 'text-brand' : 'text-accent' }}" />

            <div class="flex min-w-0 flex-1 flex-col">
                <span class="{{ $brandTitle }}">{{ $brand }}</span>
                <span class="{{ $brandRole }}">{{ $armadaRole }} - [{{ $armadaPlate }}]</span>
            </div>

            <span class="flex shrink-0 items-center gap-1.5">
                <span class="sr-only">{{ $operatorName }}</span>
                <span class="{{ $avatarShell }}" title="{{ $operatorName }} - {{ $operatorRole }}"
                    aria-label="{{ $operatorName }} - {{ $operatorRole }}">{{ $operatorInitials }}</span>
            </span>
        </header>

        <main id="main-content" class="bg-canvas py-4">
            @yield('content')
        </main>

        <nav class="fixed inset-x-0 bottom-0 z-40 border-t-2 border-line-hair bg-surface shadow-pop"
            aria-label="Navigasi armada">
            <div class="mx-auto flex w-full max-w-[512px] items-stretch">
                @foreach ($navigation as $item)
                    @php
                        $itemKey = $item['key'] ?? '';
                        $isActive = $itemKey === $activeKey;
                        $isLinked = isset($navigationRoutes[$itemKey]);
                    @endphp

                    @if ($isActive)
                        <span aria-current="page"
                            class="flex flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2.5 text-ink-body">
                            <x-gpa.icon :name="$item['icon']" class="h-[18px] w-[18px] shrink-0" />
                            <span class="pt-0.5 text-center font-mono text-[11px] font-normal leading-5">{{ $item['label'] }}</span>
                        </span>
                    @elseif ($isLinked)
                        <a href="{{ route($navigationRoutes[$itemKey]) }}"
                            class="flex flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2.5 text-ink-body">
                            <x-gpa.icon :name="$item['icon']" class="h-[17px] w-[17px] shrink-0" />
                            <span class="pt-0.5 text-center font-mono text-[11px] font-normal leading-5">{{ $item['label'] }}</span>
                        </a>
                    @else
                        <button type="button" @click="openMenu('{{ $item['label'] }}')"
                            class="flex flex-1 flex-col items-center justify-center gap-0.5 px-1 py-2.5 text-ink-body">
                            <x-gpa.icon :name="$item['icon']" class="h-[15px] w-[15px] shrink-0" />
                            <span class="pt-0.5 text-center font-mono text-[11px] font-normal leading-5">{{ $item['label'] }}</span>
                        </button>
                    @endif
                @endforeach
            </div>
        </nav>

        <x-gpa.toast />
    </div>
</body>

</html>
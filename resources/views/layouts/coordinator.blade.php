<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Konsol Koordinator Lapangan AgroOrder GPA.">

    <title>@yield('title', 'Dashboard Koordinator Lapangan').' &middot; '.config('app.name', 'AgroOrder GPA')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    @php
        $operatorRole = $operator['role'] ?? 'Koordinator Lapangan';
        $operatorName = $operator['name'] ?? config('app.name');
        $operatorCode = $operator['code'] ?? '-';
        $operatorInitials = $operator['initials'] ?? 'AT';

        $navigation = [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('coordinator.dashboard'), 'active' => request()->routeIs('coordinator.dashboard')],
            ['label' => 'Rencana Panen', 'icon' => 'calendar', 'href' => route('coordinator.harvest'), 'active' => request()->routeIs('coordinator.harvest')],
            ['label' => 'Timbangan & Sortir', 'icon' => 'scale', 'href' => route('coordinator.weighing'), 'active' => request()->routeIs('coordinator.weighing')],
            ['label' => 'Manajemen Stok', 'icon' => 'package', 'href' => route('coordinator.stock'), 'active' => request()->routeIs('coordinator.stock')],
            ['label' => 'Surat Jalan & Logistik', 'icon' => 'truck', 'href' => route('coordinator.dispatch'), 'active' => request()->routeIs('coordinator.dispatch')],
            ['label' => 'Monitoring', 'icon' => 'chart', 'href' => route('coordinator.monitoring'), 'active' => request()->routeIs('coordinator.monitoring')],
            ['label' => 'Laporan Retur', 'icon' => 'refresh', 'href' => null],
        ];
    @endphp

    <div x-data="coordinatorDashboard">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
            Lewati ke konten utama
        </a>

        <header class="sticky top-0 z-40 border-b border-ink bg-brand text-white">
            <div class="mx-auto flex h-[69px] w-full max-w-shell items-center gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 shrink-0 items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" aria-label="Buka menu navigasi"
                        class="-ml-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white/80 transition-colors hover:bg-white/10 hover:text-white lg:hidden">
                        <x-gpa.icon name="grid" class="h-5 w-5" />
                    </button>

                    <a href="{{ route('coordinator.dashboard') }}" class="flex min-w-0 items-center gap-3">
                        <x-gpa.brand-mark class="h-7 w-7 shrink-0 text-accent" />
                        <span class="flex min-w-0 flex-col">
                            <span class="truncate font-inter text-lg font-extrabold uppercase leading-6 text-white">
                                AgroOrder GPA
                            </span>
                            <span class="truncate text-[10px] font-medium leading-3 text-white/80 gpa-meta">
                                {{ $operatorRole }}
                            </span>
                        </span>
                    </a>
                </div>

                <div class="hidden min-w-0 flex-1 justify-center lg:flex">
                    <label class="relative block w-full max-w-[608px]">
                        <span class="sr-only">Cari nomor PO, klien B2B, resi logistik</span>
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-9 items-center justify-center text-surface-disabled">
                            <x-gpa.icon name="search" class="h-4 w-4" />
                        </span>
                        <input x-ref="globalSearch" type="search" x-model="query" autocomplete="off"
                            placeholder="Cari No. PO, Klien B2B, Resi Logistik..."
                            class="h-10 w-full rounded-lg border border-brand-line bg-brand-deep/70 py-2 pl-9 pr-24 text-sm text-white placeholder:text-line-board transition-colors hover:border-ink-subtle focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30">
                        <kbd
                            class="pointer-events-none absolute right-3 my-auto hidden h-5 items-center rounded bg-ink px-1.5 text-[9px] font-semibold text-surface-disabled sm:inline-flex">
                            Ctrl + K
                        </kbd>
                    </label>
                </div>

                <div class="ml-auto flex shrink-0 items-center gap-3 lg:ml-0">
                    <button type="button" @click="syncScale()"
                        class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-accent px-4 transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="refresh" class="h-3 w-3 text-ink" />
                        <span class="hidden text-[11px] font-bold tracking-[0.28px] text-ink gpa-meta sm:inline">Sync Timbangan</span>
                    </button>

                    <span class="hidden h-6 w-px bg-success lg:block" aria-hidden="true"></span>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="run('Notifikasi', '2 notifikasi baru menunggu tindakan Anda.')"
                            class="relative hidden h-8 w-8 items-center justify-center rounded-lg text-surface-disabled transition-colors hover:bg-white/10 hover:text-white sm:inline-flex"
                            aria-label="Notifikasi">
                            <x-gpa.icon name="bell" class="h-4 w-4" />
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-accent" aria-hidden="true"></span>
                        </button>
                        <button type="button" @click="run('Pusat Bantuan', 'Hotline operasional lapangan: 021-5050-0888.')"
                            class="hidden h-8 w-8 items-center justify-center rounded-lg text-surface-disabled transition-colors hover:bg-white/10 hover:text-white sm:inline-flex"
                            aria-label="Pusat bantuan">
                            <x-gpa.icon name="headset" class="h-4 w-4" />
                        </button>
                        <span
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface-disabled text-[16px] font-bold text-ink ring-1 ring-inset ring-accent">
                            {{ $operatorInitials }}
                        </span>
                        <span class="hidden min-w-0 flex-col sm:flex">
                            <span class="truncate text-[11px] font-semibold tracking-[0.88px] text-canvas gpa-meta">
                                {{ $operatorName }}
                            </span>
                            <span class="truncate text-[9px] font-semibold tracking-[1.08px] text-accent gpa-meta">
                                ID : {{ $operatorCode }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto flex w-full max-w-shell items-start">
            <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" aria-hidden="true"
                class="fixed inset-0 z-30 bg-ink/40 lg:hidden"></div>

            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-50 flex h-full w-72 flex-col justify-between gap-6 overflow-y-auto border-r border-line-hair bg-canvas p-4 shadow-card transition-transform duration-200 lg:sticky lg:top-[69px] lg:bottom-auto lg:left-auto lg:z-0 lg:h-[calc(100vh-69px)] lg:w-72 lg:shrink-0 lg:translate-x-0 lg:shadow-none">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2.5 rounded-xl border border-line-hair bg-surface-shell p-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand">
                            <x-gpa.icon name="user" class="h-4 w-4 text-accent" />
                        </span>
                        <span class="flex min-w-0 flex-col">
                            <span class="block truncate font-inter text-sm font-bold leading-5 text-ink">{{ $operatorRole }}</span>
                            <span class="block truncate text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-success-deep gpa-micro-bold">
                                {{ $operatorName }}
                            </span>
                            <span class="block truncate gpa-note text-ink-quiet">ID {{ $operatorCode }}</span>
                        </span>
                    </div>

                    <a href="{{ route('coordinator.dashboard') }}"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="plus" class="h-4 w-4 shrink-0 text-ink" />
                        <span class="text-[11px] font-bold tracking-[0.88px] text-ink gpa-meta">Input Timbangan Cepat</span>
                    </a>

                    <nav class="flex flex-col gap-1 pt-4" aria-label="Navigasi koordinator">
                        @foreach ($navigation as $item)
                            @php
                                $active = $item['active'] ?? false;
                                $linkClass = 'flex items-center gap-3 rounded-lg px-4 py-2.5 gpa-nav transition-colors'
                                    .($active
                                        ? ' rounded-r-lg border-l-4 border-accent bg-brand font-semibold capitalize text-accent'
                                        : ' text-ink-body hover:bg-surface-shell hover:text-ink');
                            @endphp

                            @if (! empty($item['href']))
                                <a href="{{ $item['href'] }}" class="{{ $linkClass }}"
                                    @if ($active) aria-current="page" @endif>
                                    <x-gpa.icon :name="$item['icon']"
                                        class="h-4 w-4 shrink-0 {{ $active ? 'text-accent' : 'text-ink-body' }}" />
                                    <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                </a>
                            @else
                                <span class="{{ $linkClass }} cursor-not-allowed" aria-disabled="true"
                                    title="Modul sedang disiapkan">
                                    <x-gpa.icon :name="$item['icon']" class="h-4 w-4 shrink-0 text-ink-body" />
                                    <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                </span>
                            @endif
                        @endforeach
                    </nav>
                </div>

                <div class="flex flex-col gap-1 border-t border-line-hair pt-4">
                    <button type="button" @click="run('Pengaturan', 'Modul pengaturan koordinator sedang disiapkan.')"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left gpa-body text-ink-quiet transition-colors hover:bg-surface-shell hover:text-ink">
                        <x-gpa.icon name="settings" class="h-3.5 w-3.5 shrink-0" />
                        Pengaturan
                    </button>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left gpa-body text-ink-quiet transition-colors hover:bg-surface-shell hover:text-ink">
                                <x-gpa.icon name="logout" class="h-3.5 w-3.5 shrink-0" />
                                Keluar Sesi
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 gpa-body text-ink-quiet transition-colors hover:bg-surface-shell hover:text-ink">
                            <x-gpa.icon name="logout" class="h-3.5 w-3.5 shrink-0" />
                            Keluar Sesi
                        </a>
                    @endauth
                </div>
            </aside>

            <main id="main-content" class="min-w-0 flex-1 px-4 py-6 lg:px-8">
                @yield('content')
            </main>
        </div>

        <x-gpa.toast />
    </div>
</body>

</html>

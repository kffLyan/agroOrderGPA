<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Dashboard Eksekutif Monitoring Bisnis &amp; Otorisasi Direktur AgroOrder GPA.">

    <title>@yield('title', 'Dashboard Eksekutif Direktur').' &middot; '.config('app.name', 'AgroOrder GPA')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    @php
        $operatorRole = $operator['role'] ?? 'Direktur';
        $operatorName = $operator['name'] ?? config('app.name');
        $operatorCode = $operator['code'] ?? '-';
        $operatorInitials = $operator['initials'] ?? 'AT';

        $navigation = [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('director.dashboard'), 'active' => request()->routeIs('director.dashboard')],
            ['label' => 'Penjualan', 'icon' => 'banknote', 'href' => route('director.sales'), 'active' => request()->routeIs('director.sales')],
            ['label' => 'Volume Komoditas', 'icon' => 'package', 'href' => route('director.volume'), 'active' => request()->routeIs('director.volume')],
            ['label' => 'Piutang & Tagihan', 'icon' => 'invoice', 'href' => route('director.receivables'), 'active' => request()->routeIs('director.receivables')],
            ['label' => 'Persetujuan Kontrak', 'icon' => 'badge-check', 'href' => route('director.approval'), 'active' => request()->routeIs('director.approval')],
            ['label' => 'Laporan', 'icon' => 'shield', 'href' => route('director.report'), 'active' => request()->routeIs('director.report')],
            ['label' => 'Pengaturan Tata Kelola', 'icon' => 'settings', 'href' => route('director.governance'), 'active' => request()->routeIs('director.governance')],
            ['label' => 'Kelola Akun Pengguna & RBAC', 'icon' => 'users', 'href' => route('director.access'), 'active' => request()->routeIs('director.access')],
        ];
    @endphp

    <div x-data="directorDashboard(@js($operator))">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
            Lewati ke konten utama
        </a>

        <header class="sticky top-0 z-40 border-b border-ink bg-brand text-white">
            <div class="mx-auto flex h-[68px] w-full max-w-shell items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" aria-label="Buka menu navigasi"
                        class="-ml-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white/80 transition-colors hover:bg-white/10 hover:text-white lg:hidden">
                        <x-gpa.icon name="grid" class="h-5 w-5" />
                    </button>

                    <a href="{{ route('director.dashboard') }}" class="flex min-w-0 items-center gap-3">
                        <x-gpa.brand-mark class="h-7 w-7 shrink-0 text-accent" />
                        <span class="flex min-w-0 flex-col">
                            <span class="truncate font-inter text-lg font-extrabold uppercase leading-6 text-white">
                                AgroOrder GPA
                            </span>
                            <span class="truncate text-[10px] font-medium capitalize leading-3 text-white/80 gpa-meta">
                                {{ $operatorRole }}
                            </span>
                        </span>
                    </a>
                </div>

                <div class="ml-auto flex shrink-0 items-center gap-3">
                    <button type="button" @click="exportExcel()"
                        class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-accent px-4 shadow-sub transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="download" class="h-3 w-3 text-ink" />
                        <span class="hidden text-[11px] font-bold tracking-[0.28px] text-ink gpa-meta sm:inline">Export
                            Excel</span>
                    </button>

                    <button type="button" @click="printPdf()"
                        class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-[#FF4438] px-4 shadow-sub transition-colors hover:bg-danger">
                        <x-gpa.icon name="file-text" class="h-3 w-3 text-brand" />
                        <span class="hidden text-[11px] font-bold tracking-[0.28px] text-ink gpa-meta sm:inline">Cetak
                            PDF</span>
                    </button>

                    <span class="hidden h-6 w-px bg-success lg:block" aria-hidden="true"></span>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="run('Notifikasi', '3 pengajuan kontrak tier-1 menunggu tanda tangan Anda.')"
                            class="relative hidden h-8 w-8 items-center justify-center rounded-lg text-surface-disabled transition-colors hover:bg-white/10 hover:text-white sm:inline-flex"
                            aria-label="Notifikasi">
                            <x-gpa.icon name="bell" class="h-4 w-4" />
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-accent" aria-hidden="true"></span>
                        </button>
                        <button type="button" @click="run('Pesan Direksi', 'Tidak ada pesan baru dari sekretaris dan tim operasional.')"
                            class="hidden h-8 w-8 items-center justify-center rounded-lg text-surface-disabled transition-colors hover:bg-white/10 hover:text-white sm:inline-flex"
                            aria-label="Pesan">
                            <x-gpa.icon name="mail" class="h-4 w-4" />
                        </button>
                        <span
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface-disabled text-[16px] font-bold text-ink ring-1 ring-inset ring-ink-body">
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
                class="fixed inset-y-0 left-0 z-40 hidden w-[288px] -translate-x-full flex-col justify-between border-r border-line-hair bg-canvas p-4 transition-transform lg:sticky lg:top-[68px] lg:z-0 lg:flex lg:h-[calc(100vh-68px)] lg:translate-x-0"
                :class="sidebarOpen ? '!translate-x-0' : ''">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2.5 rounded-xl border border-line-hair bg-surface-shell p-3">
                        <x-gpa.brand-mark class="h-6 w-6 shrink-0 text-brand" />
                        <span class="flex min-w-0 flex-col">
                            <span class="block truncate font-inter text-sm font-bold leading-5 text-ink">{{ $operatorRole }}</span>
                            <span class="block truncate text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-success-deep gpa-micro-bold">
                                {{ $operatorName }}
                            </span>
                            <span class="block truncate gpa-note text-success-deep">ID {{ $operatorCode }}</span>
                        </span>
                    </div>

                    <button type="button" @click="auditLog()"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="clipboard" class="h-3.5 w-3.5 shrink-0 text-ink" />
                        <span class="text-[11px] font-bold tracking-[0.88px] text-ink gpa-meta">Audit Log</span>
                    </button>

                    <nav class="flex flex-col gap-1 pt-4" aria-label="Navigasi direktur">
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
                    <button type="button" @click="run('Pengaturan', 'Modul pengaturan tata kelola sedang disiapkan.')"
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

            <main id="main-content" class="min-w-0 flex-1 px-4 py-6 lg:px-6">
                @yield('content')
            </main>
        </div>

        <x-gpa.toast />
    </div>
</body>

</html>

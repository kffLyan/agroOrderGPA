<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Portal Operasional Klien AgroOrder GPA: Plafon kredit, katalog komoditas, pemesanan, pelacakan armada, dan faktur konsolidasi.">

    <title>@yield('title', 'Portal Klien') &middot; {{ config('app.name', 'AgroOrder GPA') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
    @php
        $user = auth()->user();
        $clientName = $user->company_name ?: ($user->name ?: config('app.name'));
        $clientTier = ($user && $user->client_type === 'B2B_KONTRAK') ? 'Mitra B2B Kontrak' : 'Klien Reguler Harian';
        $clientCode = $user ? 'KL-'.str_pad($user->id, 4, '0', STR_PAD_LEFT) : '-';
        $clientTaxStatus = ($user && $user->client_type === 'B2B_KONTRAK') ? 'PKP Terverifikasi' : 'Reguler Retail';
        $clientInitials = strtoupper(substr($user->name ?? 'KL', 0, 2));

        $navigation = [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('klien.dashboard'), 'active' => request()->routeIs('klien.dashboard')],
            ['label' => 'Katalog Komoditas', 'icon' => 'grid', 'href' => route('klien.catalog'), 'active' => request()->routeIs('klien.catalog*')],
            ['label' => 'Keranjang & Draft', 'icon' => 'cart', 'href' => route('klien.cart'), 'badge' => 'draft', 'active' => request()->routeIs('klien.cart*')],
            ['label' => 'Riwayat Pesanan', 'icon' => 'package', 'href' => route('klien.orders.index'), 'active' => request()->routeIs('klien.orders.*')],
            ['label' => 'Dokumen & Faktur', 'icon' => 'file-text', 'href' => route('klien.documents'), 'active' => request()->routeIs('klien.documents*') || request()->routeIs('klien.payment-proof*')],
            ['label' => 'Profil Pengguna', 'icon' => 'user', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
        ];
    @endphp

    <div x-data="{ sidebarOpen: false }">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2 focus:text-xs focus:font-semibold focus:shadow-card">
            Lewati ke konten utama
        </a>

        <header class="sticky top-0 z-40 border-b border-ink bg-brand text-white shadow-sub">
            <div class="mx-auto flex h-[68px] w-full max-w-shell items-center justify-between gap-4 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" aria-label="Buka menu navigasi"
                        class="-ml-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white/80 transition-colors hover:bg-white/10 hover:text-white lg:hidden">
                        <x-gpa.icon name="grid" class="h-5 w-5" />
                    </button>

                    <a href="{{ route('klien.dashboard') }}" class="flex min-w-0 items-center gap-3">
                        <x-gpa.brand-mark class="h-7 w-7 shrink-0 text-accent" />
                        <span class="flex min-w-0 flex-col">
                            <span class="flex min-w-0 items-center gap-2">
                                <span
                                    class="truncate font-inter text-lg font-extrabold uppercase leading-6 text-white">
                                    AgroOrder GPA
                                </span>
                                <span
                                    class="hidden shrink-0 rounded bg-ink px-2 py-0.5 text-[10px] font-bold uppercase leading-3 tracking-[0.1em] text-accent ring-1 ring-inset ring-accent/30 sm:inline-block">
                                    {{ ($user && $user->client_type === 'B2B_KONTRAK') ? 'B2B KONTRAK' : 'REGULER' }}
                                </span>
                            </span>
                            <span class="truncate text-[10px] font-medium leading-3 text-white/80 gpa-meta">
                                {{ $clientName }}
                            </span>
                        </span>
                    </a>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                    <a href="{{ route('klien.orders.index') }}"
                        class="hidden items-center gap-1.5 rounded-lg border border-success px-3 py-1.5 gpa-meta-lg text-white transition-colors hover:bg-white/10 md:inline-flex">
                        <x-gpa.icon name="package" class="h-3.5 w-3.5" />
                        PO Tracking
                    </a>

                    <a href="{{ route('klien.cart') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3.5 py-2 text-ink shadow-pop transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="cart" class="h-3.5 w-3.5 text-ink" />
                        <span class="hidden sm:inline">Keranjang PO</span>
                        <span class="sm:hidden">PO</span>
                    </a>

                    <span class="hidden h-6 w-px bg-success md:block" aria-hidden="true"></span>

                    <div class="flex items-center gap-1">
                        <a href="{{ route('profile.edit') }}"
                            class="ml-2 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface-disabled text-xs font-bold text-ink ring-1 ring-inset ring-accent gpa-meta-lg"
                            aria-label="Profil saya">
                            {{ $clientInitials }}
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto flex w-full max-w-shell items-start">
            <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" aria-hidden="true"
                class="fixed inset-0 z-30 bg-ink/40 lg:hidden"></div>

            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-50 flex h-full w-72 flex-col justify-between gap-6 overflow-y-auto border-r border-line-hair bg-canvas p-4 shadow-card transition-transform duration-200 lg:sticky lg:top-[68px] lg:bottom-auto lg:left-auto lg:z-0 lg:h-[calc(100vh-68px)] lg:w-72 lg:shrink-0 lg:translate-x-0 lg:shadow-none">
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col gap-2.5 rounded-xl border border-line-hair bg-surface-shell p-3">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand">
                                <x-gpa.icon name="building" class="h-4 w-4 text-accent" />
                            </span>
                            <span class="flex min-w-0 flex-col">
                                <span class="block truncate text-sm font-bold leading-5 text-ink">{{ $clientName }}</span>
                                <span
                                    class="block truncate text-[9px] font-bold uppercase leading-3 tracking-[0.45px] text-success-deep gpa-micro-bold">
                                    {{ $clientTier }}
                                </span>
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between gap-2 border-t border-line-hair pt-2 gpa-meta text-ink-quiet">
                            <span>ID: {{ $clientCode }}</span>
                            <span class="font-semibold text-success-deep">{{ $clientTaxStatus }}</span>
                        </div>
                    </div>

                    <a href="{{ route('klien.catalog') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="plus" class="h-4 w-4 text-ink" />
                        <span class="gpa-meta-lg font-bold text-ink">Buat Pesanan Baru</span>
                    </a>

                    <nav class="flex flex-col gap-1 pt-4" aria-label="Navigasi utama">
                        @foreach ($navigation as $item)
                            @php
                                $active = $item['active'] ?? false;
                                $linkClass = 'flex items-center gap-3 rounded-lg px-4 py-2.5 gpa-nav transition-colors'
                                    .($active
                                        ? ' rounded-r-lg border-l-4 border-accent bg-brand font-semibold capitalize text-accent shadow-sub'
                                        : ' text-ink-body hover:bg-surface-shell');
                            @endphp

                            <a href="{{ $item['href'] }}" class="{{ $linkClass }}"
                                @if ($active) aria-current="page" @endif>
                                <x-gpa.icon :name="$item['icon']"
                                    class="h-4 w-4 shrink-0 {{ $active ? 'text-accent' : 'text-ink-body' }}" />
                                <span class="flex-1 truncate">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="flex flex-col gap-1 border-t border-line-hair pt-4">
                    <a href="tel:+622150550088"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 gpa-body text-ink-quiet transition-colors hover:bg-surface-shell hover:text-ink">
                        <x-gpa.icon name="headset" class="h-3.5 w-3.5 shrink-0" />
                        SLA Hotline 24/7
                    </a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 gpa-body text-ink-quiet transition-colors hover:bg-surface-shell hover:text-ink">
                                <x-gpa.icon name="logout" class="h-3.5 w-3.5 shrink-0" />
                                Keluar Sesi
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 gpa-body text-ink-quiet transition-colors hover:bg-surface-shell hover:text-ink">
                            <x-gpa.icon name="login" class="h-3.5 w-3.5 shrink-0" />
                            Masuk Portal
                        </a>
                    @endauth
                </div>
            </aside>

            <main id="main-content" class="min-w-0 flex-1 px-4 py-5 lg:px-6 lg:py-6">
                @if (session('success'))
                    <div class="mb-4 flex items-center gap-3 rounded-xl border border-success/30 bg-accent/20 p-4 text-sm font-semibold text-success-ink shadow-sub">
                        <x-gpa.icon name="check-circle" class="h-5 w-5 shrink-0 text-success-deep" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-danger/30 bg-danger/10 p-4 text-sm text-danger shadow-sub">
                        <div class="flex items-center gap-2 font-bold">
                            <x-gpa.icon name="alert-triangle" class="h-5 w-5 shrink-0 text-danger" />
                            <span>Terjadi kendala dalam pemrosesan data:</span>
                        </div>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <x-gpa.toast />
    </div>
</body>

</html>

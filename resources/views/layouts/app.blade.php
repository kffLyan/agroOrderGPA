<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroOrder GPA - @yield('title', 'Katalog Komoditas')</title>

    {{-- Lapis 2: Tailwind CSS (styling) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@500;700&display=swap"
        rel="stylesheet">

    {{-- Lapis 3: Alpine.js (interaktivitas), 'defer' wajib agar x-data terbaca setelah DOM siap --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

{{-- Lapis 1: Blade menentukan struktur; x-data Alpine membungkus state UI (menu mobile) --}}

<body x-data="{ mobileMenuOpen: false }" class="bg-[#F8FAF3] text-[#121A0F] font-sans">

    {{-- Navbar Shell Utama --}}
    <header class="w-full sticky top-0 z-50 bg-[#153A01] border-b border-[#AABD06]/20">
        <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
            <a href="{{ route('beranda') }}" class="text-white font-extrabold">AGROORDER GPA</a>
            <nav class="hidden md:flex gap-5 font-mono text-xs uppercase text-[#D9E4C0]">
                <a href="{{ route('katalog') }}">Katalog</a>
                <a href="{{ route('pesanan.saya') }}">Pesanan Saya</a>
            </nav>
            @auth
                <span class="text-white text-xs">{{ auth()->user()->name }}</span>
            @else
                <a href="{{ route('login') }}"
                    class="px-3 py-2 bg-[#AABD06] text-[#153A01] rounded-lg font-mono text-xs font-bold">Masuk</a>
            @endauth

            {{-- Tombol hamburger: @click Alpine mengubah state, tanpa reload / tanpa JS terpisah --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white">
                <span x-show="!mobileMenuOpen">&#9776;</span>
                <span x-show="mobileMenuOpen" x-cloak>&times;</span>
            </button>
        </div>

        {{-- Panel menu mobile: x-show + x-transition, murni Alpine, tanpa Blade tambahan --}}
        <nav x-show="mobileMenuOpen" x-transition x-cloak
            class="md:hidden flex flex-col gap-1 px-6 pb-4 font-mono text-xs uppercase text-[#D9E4C0]">
            <a href="{{ route('katalog') }}" @click="mobileMenuOpen = false">Katalog</a>
            <a href="{{ route('pesanan.saya') }}" @click="mobileMenuOpen = false">Pesanan Saya</a>
        </nav>
    </header>

    {{-- Kontainer Konten Dinamis (Panel Switcher Blade) --}}
    <main class="max-w-7xl mx-auto px-6 py-8">
        @yield('content')
    </main>

    <footer class="bg-[#153A01] text-[#D9E4C0] text-center py-6 font-mono text-[11px]">
        &copy; 2026 PT Green Pasundan Agriculture - AgroOrder GPA System
    </footer>
</body>

</html>

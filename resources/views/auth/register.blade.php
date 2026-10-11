<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun Klien Baru - AgroOrder GPA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=JetBrains+Mono:wght@400;500;600&amp;display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased selection:bg-surface-raised">
    {{-- MOBILE: brand green background + centered form card --}}
    <div class="relative flex min-h-screen flex-col justify-between overflow-hidden bg-brand p-4 sm:p-6 lg:hidden">
        <div class="pointer-events-none absolute left-[244px] top-[-96px] h-96 w-96 rounded-full bg-[rgba(78,122,3,0.20)] blur-[32px]"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-0 top-[491px] h-80 w-80 rounded-full bg-[rgba(170,189,6,0.10)] blur-[20px]"
            aria-hidden="true"></div>

        <main class="relative z-10 mx-auto my-auto w-full max-w-md py-6">
            <div class="rounded-3xl bg-surface p-6 shadow-2xl shadow-black/25 sm:p-8">
                <x-auth.register-form prefix="-mobile" />
            </div>
        </main>

        <footer class="relative z-10 w-full py-2 text-center text-xs text-white/70">
            <p>&copy; 2026 Green Pasundan Agriculture. Hak Cipta Dilindungi Undang-Undang. Metrologi Legal &amp; Cold-Chain
                Logistics.</p>
        </footer>
    </div>

    {{-- DESKTOP: split grid view --}}
    <main class="hidden min-h-screen w-full flex-col lg:flex">
        <div class="grid w-full flex-grow grid-cols-1 border-b border-line lg:grid-cols-12">
            {{-- LEFT PANEL: Corporate & Security Trust Column --}}
            <x-auth.left-panel />

            {{-- RIGHT PANEL: Registration Form --}}
            <section
                class="flex flex-col items-center justify-center bg-surface px-6 py-10 md:px-12 md:py-14 lg:col-span-7">
                <x-auth.register-form />
            </section>
        </div>
    </main>

    <x-gpa.toast />
</body>

</html>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Alamat Email &middot; PT Green Pasundan Agriculture</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;family=JetBrains+Mono:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas font-sans text-ink antialiased selection:bg-surface-raised">

    <div class="relative flex min-h-screen flex-col justify-between overflow-hidden bg-brand p-4 sm:p-6">
        <div class="pointer-events-none absolute left-[244px] top-[-96px] h-96 w-96 rounded-full bg-[rgba(78,122,3,0.20)] blur-[32px]"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-0 top-[491px] h-80 w-80 rounded-full bg-[rgba(170,189,6,0.10)] blur-[20px]"
            aria-hidden="true"></div>

        <main class="relative z-10 mx-auto my-auto w-full max-w-lg py-8">
            <div class="rounded-3xl bg-surface p-6 shadow-2xl shadow-black/25 sm:p-10 border border-line-soft">
                <div class="mx-auto flex w-full flex-col text-center">
                    
                    {{-- Logo & Brand --}}
                    <div class="mb-5 flex flex-col items-center">
                        <img src="{{ asset('gpaleaves.png') }}" alt="PT Green Pasundan Agriculture"
                            class="mb-3 h-14 w-14 object-contain">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-accent/20 px-3 py-1 font-mono text-[11px] font-bold text-brand uppercase tracking-wider">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                            Konfirmasi Keamanan Akun
                        </span>
                    </div>

                    {{-- Title --}}
                    <h1 class="text-xl font-extrabold tracking-tight text-ink sm:text-2xl">
                        Verifikasi Alamat Email Anda
                    </h1>

                    <p class="mt-2 text-xs leading-relaxed text-ink-body sm:text-sm">
                        Terima kasih telah mendaftar di <strong>AgroOrder GPA</strong>. Tautan verifikasi telah kami kirimkan ke alamat:
                    </p>

                    <div class="my-3 rounded-xl bg-surface-shell p-3 border border-line-soft font-mono text-xs font-bold text-brand break-all">
                        {{ auth()->user()?->email }}
                    </div>

                    <p class="text-xs text-ink-quiet leading-relaxed">
                        Silakan periksa kotak masuk (<strong>Inbox</strong>) atau folder <strong>Spam</strong> pada email Anda, lalu klik tombol verifikasi untuk mengaktifkan seluruh fitur pemesanan.
                    </p>

                    {{-- Status Banner jika berhasil kirim ulang --}}
                    @if (session('status') == 'verification-link-sent')
                        <div role="status"
                            class="mt-4 flex items-center gap-2 rounded-xl border border-success-ink/30 bg-success-soft px-3.5 py-2.5 text-left text-xs text-success-ink">
                            <x-gpa.icon name="check-circle" class="h-4 w-4 shrink-0 text-success-deep" />
                            <span>Tautan verifikasi baru telah berhasil dikirim ulang ke alamat email Anda.</span>
                        </div>
                    @endif

                    {{-- Tombol Aksi --}}
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-line-soft pt-5">
                        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit"
                                class="inline-flex h-10 w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-brand px-5 font-mono text-xs font-bold text-accent transition-colors hover:bg-brand-hover shadow-sub">
                                <x-gpa.icon name="mail" class="h-3.5 w-3.5" />
                                <span>Kirim Ulang Tautan</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit"
                                class="inline-flex h-10 w-full sm:w-auto items-center justify-center gap-1.5 rounded-xl border border-line-board bg-surface px-4 font-sans text-xs font-semibold text-ink-body transition-colors hover:bg-surface-muted hover:text-danger">
                                <x-gpa.icon name="logout" class="h-3.5 w-3.5" />
                                <span>Keluar / Ganti Akun</span>
                            </button>
                        </form>
                    </div>

                    {{-- Bantuan --}}
                    <p class="mt-6 text-[11px] text-ink-subtle">
                        Butuh bantuan aktivasi? Hubungi Desk Sekretariat GPA di
                        <a href="https://wa.me/6281112233445" target="_blank" class="font-bold text-brand hover:underline">
                            +62 811-1223-3445
                        </a>
                    </p>
                </div>
            </div>
        </main>

        <footer class="relative z-10 py-4 text-center font-mono text-2xs text-white/70">
            &copy; {{ date('Y') }} PT Green Pasundan Agriculture &middot; Rantai Pasok Terstandarisasi Horeka &amp; Ritel
        </footer>
    </div>

</body>
</html>

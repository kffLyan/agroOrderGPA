@props(['prefix' => ''])

<div class="mx-auto flex w-full max-w-xl flex-col">
    <div>
        <img src="{{ asset('gpaleaves.png') }}" alt="AgroOrder GPA"
            class="mx-auto mb-4 h-12 w-12 object-contain">

        <div class="mb-6">
            <h2 class="text-xl font-semibold tracking-tight text-ink text-center">
                Masuk ke Portal AgroOrder GPA
            </h2>
            <p class="mt-1 text-xs text-ink text-center">
                Silakan pilih portal akses sesuai peran dan masukkan kredensial akun Anda.
            </p>
        </div>

        @if (session('status'))
            <div
                class="mb-4 flex items-start gap-2 rounded-sm border border-success/40 bg-success-soft px-3 py-2 text-xs text-success-ink">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert"
                class="mb-4 flex items-start gap-2 rounded-sm border border-danger/40 bg-danger-soft px-3 py-2 text-xs text-danger">
                <span class="material-symbols-outlined text-sm">error</span>
                <ul class="space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email{{ $prefix }}"
                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                    Alamat Email Perusahaan / ID Akun GPA *
                </label>
                <div class="relative">
                    <input id="email{{ $prefix }}" name="email" type="email" required
                        @if ($prefix === '') autofocus @endif autocomplete="username" inputmode="email"
                        value="{{ old('email') }}"
                        placeholder="nama@perusahaan.co.id atau ID: OP-4091"
                        class="w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0"
                        @error('email') aria-invalid="true" aria-describedby="email-error{{ $prefix }}" @enderror>
                    <div class="pointer-events-none absolute right-3 top-2.5 text-success">
                        <span class="material-symbols-outlined text-sm">badge</span>
                    </div>
                </div>
                @error('email')
                    <p id="email-error{{ $prefix }}" class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password{{ $prefix }}"
                    class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                    Kata Sandi / Kunci Otorisasi *
                </label>
                <div class="relative" x-data="{ show: false }">
                    <input id="password{{ $prefix }}" name="password" type="password" required
                        autocomplete="current-password" placeholder="••••••••••••••••"
                        class="w-full rounded-sm border-line-strong bg-surface px-3 pr-24 font-mono text-xs text-ink placeholder:font-mono focus:border-brand focus:ring-0"
                        x-bind:type="show ? 'text' : 'password'">
                    <button type="button"
                        class="absolute right-1.5 top-2 flex items-center gap-1 rounded-sm border border-line bg-surface-muted px-2 py-1 font-mono text-[10px] text-success transition-colors hover:bg-surface-raised hover:text-brand focus:outline-none"
                        @click="show = ! show">
                        <span class="material-symbols-outlined text-[12px]"
                            x-text="show ? 'visibility_off' : 'visibility'"></span>
                        <span x-text="show ? 'Sembunyikan' : 'Tampilkan'"></span>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input name="remember" type="checkbox" value="1"
                        class="h-3.5 w-3.5 cursor-pointer rounded-sm border-line-strong bg-surface text-brand focus:ring-0 focus:ring-offset-0"
                        @checked(old('remember'))>
                    <span class="text-xs text-ink">Ingat Sesi di Perangkat Ini (30 Hari)</span>
                </label>
                <a href="{{ route('password.request') }}"
                    class="font-mono text-[11px] text-success underline underline-offset-2 hover:text-brand">
                    Lupa Kata Sandi / Reset PIN?
                </a>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="flex h-10 w-full items-center justify-center space-x-2 rounded-sm border border-brand bg-brand font-mono text-xs font-semibold uppercase tracking-wider text-white transition-colors hover:bg-brand-hover active:translate-y-px">
                    <span>Masuk</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </div>
        </form>
    </div>

    <div
        class="mt-8 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4 text-center sm:text-left">
        <span class="text-xs text-success">Belum memiliki akun?</span>
        <a href="{{ route('register') }}"
            class="flex items-center gap-1 font-mono text-xs font-semibold text-ink underline underline-offset-4 hover:text-brand">
            <span>Daftarkan Entitas Perusahaan Anda</span>
            <span class="material-symbols-outlined text-xs">open_in_new</span>
        </a>
    </div>
</div>

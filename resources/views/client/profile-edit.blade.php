@section('title', 'Profil Saya - AgroOrder GPA')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap">
@endpush

@section('content')
<div class="flex min-h-screen w-full flex-col">
    <div class="grid w-full flex-grow grid-cols-1 border-b border-line lg:grid-cols-12">
        {{-- LEFT PANEL: Client Brand + Nav --}}
        <x-auth.left-panel />

        {{-- RIGHT PANEL: Profile Form --}}
        <section
            class="flex flex-col items-center justify-center bg-surface px-6 py-10 md:px-12 md:py-14 lg:col-span-7">
            <div class="mx-auto flex w-full max-w-xl flex-col">
                {{-- FORM HEADER --}}
                <div class="mb-6">
                    <h2 class="text-xl font-semibold tracking-tight text-ink">
                        Profil Saya
                    </h2>
                    <p class="mt-1 text-xs text-ink">
                        Lengkapi informasi berikut untuk memperbarui akun Anda.
                    </p>
                </div>

                {{-- FLASH STATUS --}}
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

                {{-- PROFILE FORM --}}
                <form method="POST" action="{{ route('client.profile.update') }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    {{-- FIELD 1: NAME --}}
                    <div>
                        <label for="name"
                            class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                            Nama Lengkap / Kontak PIC *
                        </label>
                        <input id="name" name="name" type="text" required autocomplete="name"
                            value="{{ old('name', Auth::user()?->name ?? '') }}" placeholder="Contoh: Budi Santoso"
                            class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        @error('name')
                            <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- FIELD 2: EMAIL --}}
                    <div>
                        <label for="email"
                            class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                            Alamat Email *
                        </label>
                        <input id="email" name="email" type="email" required autocomplete="email"
                            value="{{ old('email', Auth::user()?->email ?? '') }}" placeholder="nama@perusahaan.co.id"
                            class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        @error('email')
                            <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- FIELD 3: ADDRESS --}}
                    <div>
                        <label for="address"
                            class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                            Alamat Lengkap *
                        </label>
                        <textarea id="address" name="address" required rows="3" autocomplete="street-address"
                            placeholder="Alamat lengkap gudang / dapur penerima pengiriman..."
                            class="h-20 w-full resize-none rounded-sm border-line-strong bg-surface p-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">{{ old('address', Auth::user()?->address ?? '') }}</textarea>
                        @error('address')
                            <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- FIELD 4: PASSWORD --}}
                    <div>
                        <label for="password"
                            class="mb-1.5 block font-mono text-[11px] font-medium uppercase tracking-wider text-ink">
                            Kata Sandi Baru (Kosongkan jika tidak ingin mengubah)
                        </label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                            placeholder="Minimal 8 karakter (opsional)"
                            class="h-10 w-full rounded-sm border-line-strong bg-surface px-3 font-mono text-xs text-ink placeholder:font-mono placeholder:text-[11px] placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        @error('password')
                            <p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-ink-subtle">
                            Kosongkan jika tidak ingin mengubah kata sandi.
                        </p>
                    </div>

                    {{-- AGREEMENT CHECKBOX --}}
                    <div class="pt-1">
                        <label class="inline-flex cursor-pointer items-start gap-2">
                            <input type="checkbox" name="terms" value="1" required
                                class="h-3.5 w-3.5 mt-0.5 shrink-0 cursor-pointer rounded-sm border-line-strong bg-surface text-brand focus:ring-0 focus:ring-offset-0">
                            <span class="text-xs leading-snug text-ink">Saya menyetujui Ketentuan Layanan dan
                                Kebijakan Privasi AgroOrder GPA.</span>
                        </label>
                    </div>

                    {{-- PRIMARY SUBMISSION BUTTON --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="flex h-10 w-full items-center justify-center space-x-2 rounded-sm border border-brand bg-brand font-mono text-xs font-semibold uppercase tracking-wider text-white transition-colors hover:bg-brand-hover active:translate-y-px">
                            <span>SIMPAN PERUBAHAN</span>
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-focus the first input
        const firstInput = document.querySelector('input[name="name"]');
        if (firstInput) {
            firstInput.focus();
        }
    });
</script>
@endpush

<x-gpa.toast />
</body>
</html>
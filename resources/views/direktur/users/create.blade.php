<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Tambah Akun Pengguna Baru
    </x-slot>

    <div class="mx-auto flex max-w-4xl flex-col gap-6">
        <section>
            <header class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-4 shadow-card sm:p-6">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-brand">
                        <x-gpa.icon name="user" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="gpa-micro-bold uppercase tracking-wider text-ink-subtle">Direktori Pengguna</p>
                        <h1 class="mt-0.5 font-sans text-xl font-bold leading-7 text-ink sm:text-2xl">
                            Tambah Pengguna Baru
                        </h1>
                    </div>
                </div>

                <a href="{{ route('direktur.users.index') }}"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-surface-shell px-3 py-2 gpa-meta font-semibold text-ink transition-colors hover:bg-surface-muted sm:w-auto">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 shrink-0" />
                    Kembali ke Daftar
                </a>
            </header>
        </section>

        @if ($errors->any())
            <div class="rounded-xl bg-danger-soft/40 px-4 py-3 text-xs text-danger">
                <div class="flex items-start gap-2">
                    <x-gpa.icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <div>
                        <p class="font-semibold">Gagal menyimpan akun</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl bg-surface shadow-card">
            <header class="px-4 pb-3 pt-5 sm:px-6 sm:pt-6">
                <h2 class="gpa-section-title text-ink">Informasi Akun</h2>
                <p class="mt-1 text-xs leading-4 text-ink-subtle">Lengkapi identitas dan pengaturan akses pengguna.</p>
            </header>

            <form action="{{ route('direktur.users.store') }}" method="POST" class="space-y-6 px-4 pb-5 sm:px-6 sm:pb-6">
                @csrf

                <fieldset class="space-y-4">
                    <legend class="w-full border-b border-line-faint pb-2 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-subtle">
                        Data Pengguna
                    </legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <label for="name" class="text-xs font-semibold text-ink-body">Nama Lengkap <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                placeholder="Contoh: Rina Marlina" required autocomplete="name"
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-subtle focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-xs font-semibold text-ink-body">Alamat Email <span class="text-danger">*</span></label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                placeholder="user@greenpasundan.id" required autocomplete="email"
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-subtle focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="phone" class="text-xs font-semibold text-ink-body">Nomor Telepon / WA <span class="text-danger">*</span></label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                                placeholder="081234567890" required autocomplete="tel"
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-subtle focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="text-xs font-semibold text-ink-body">Kata Sandi Awal <span class="text-danger">*</span></label>
                            <input id="password" type="password" name="password" placeholder="Minimal 8 karakter"
                                required autocomplete="new-password"
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-subtle focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="space-y-4">
                    <legend class="w-full border-b border-line-faint pb-2 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-subtle">
                        Peran &amp; Status
                    </legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <label for="role_select" class="text-xs font-semibold text-ink-body">Wewenang Peran <span class="text-danger">*</span></label>
                            <select name="role" id="role_select" required
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                                <option value="">-- Pilih Peran --</option>
                                <option value="DIREKTUR" {{ old('role') === 'DIREKTUR' ? 'selected' : '' }}>DIREKTUR</option>
                                <option value="SEKRETARIS" {{ old('role') === 'SEKRETARIS' ? 'selected' : '' }}>SEKRETARIS</option>
                                <option value="KOORDINATOR" {{ old('role') === 'KOORDINATOR' ? 'selected' : '' }}>KOORDINATOR</option>
                                <option value="ARMADA" {{ old('role') === 'ARMADA' ? 'selected' : '' }}>ARMADA</option>
                                <option value="KLIEN" {{ old('role') === 'KLIEN' ? 'selected' : '' }}>KLIEN</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="is_active" class="text-xs font-semibold text-ink-body">Status Keaktifan <span class="text-danger">*</span></label>
                            <select id="is_active" name="is_active" required
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                                <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>AKTIF</option>
                                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>NON-AKTIF</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="space-y-4">
                    <legend class="w-full border-b border-line-faint pb-2 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-subtle">
                        Profil Klien <span class="font-normal normal-case tracking-normal">(Opsional)</span>
                    </legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <label for="client_type" class="text-xs font-semibold text-ink-body">Jenis Klien <span class="font-normal text-ink-subtle">(khusus peran KLIEN)</span></label>
                            <select id="client_type" name="client_type"
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                                <option value="">-- Bukan Klien / Opsional --</option>
                                <option value="REGULER" {{ old('client_type') === 'REGULER' ? 'selected' : '' }}>REGULER</option>
                                <option value="B2B_KONTRAK" {{ old('client_type') === 'B2B_KONTRAK' ? 'selected' : '' }}>B2B_KONTRAK</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="company_name" class="text-xs font-semibold text-ink-body">Nama Perusahaan / Entitas Usaha</label>
                            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
                                placeholder="PT AEON Mall / CV Berkah"
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-subtle focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        </div>

                        <div class="flex flex-col gap-1.5 sm:col-span-2">
                            <label for="address" class="text-xs font-semibold text-ink-body">Alamat Domisili / Pengiriman</label>
                            <textarea id="address" name="address" rows="3" placeholder="Alamat lengkap..."
                                class="w-full rounded-lg bg-surface-shell px-3 py-2.5 text-xs leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-subtle focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </fieldset>

                <footer class="flex flex-col-reverse gap-2 border-t border-line-faint pt-4 sm:flex-row sm:justify-end">
                    <a href="{{ route('direktur.users.index') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-surface-shell px-4 py-2.5 text-xs font-semibold text-ink transition-colors hover:bg-surface-muted">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-xs font-bold text-white transition-colors hover:bg-brand-hover">
                        <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                        Simpan Akun
                    </button>
                </footer>
            </form>
        </section>
    </div>
</x-dynamic-component>

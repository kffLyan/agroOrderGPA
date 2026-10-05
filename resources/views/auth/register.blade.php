<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('clients.form.title') }} | AgroOrder GPA</title>

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
    <main class="mx-auto w-full max-w-shell px-4 py-8 sm:px-6 lg:py-10">
        {{-- HEADER FORMULIR --}}
        <header class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-sm border border-line bg-surface px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-ink-muted">
                        <span class="h-1.5 w-1.5 animate-pulse-ring rounded-full bg-success"></span>
                        {{ config('clients.form.status') }}
                    </span>
                    <span
                        class="inline-flex items-center gap-1 rounded-sm border border-brand bg-brand px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-accent">
                        {{ config('clients.form.code') }} / {{ config('clients.form.revision') }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-brand md:text-3xl">
                    {{ config('clients.form.title') }}
                </h1>
                <p class="mt-2 text-sm leading-relaxed text-ink-body">
                    {{ config('clients.form.subtitle') }}
                </p>
            </div>

            <a href="{{ route('login') }}"
                class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-sm border border-line bg-surface px-3 py-2 font-mono text-[11px] font-semibold uppercase tracking-wider text-ink-muted transition-colors hover:bg-surface-muted hover:text-brand lg:self-auto">
                <span class="material-symbols-outlined text-sm">login</span>
                Sudah punya akun? Masuk
            </a>
        </header>

        <form method="POST" action="{{ route('register') }}" class="space-y-6"
            x-data="registrationForm({
                csrf: '{{ csrf_token() }}',
                otpSendUrl: '{{ route('register.otp.send') }}',
                otpResendUrl: '{{ route('register.otp.resend') }}',
                otpVerifyUrl: '{{ route('register.otp.verify') }}',
                name: '{{ old('name') }}',
                business_name: '{{ old('business_name') }}',
                phone: '{{ old('phone') }}',
                email: '{{ old('email') }}',
                address: '{{ old('address') }}',
                delivery_zone: '{{ old('delivery_zone') }}',
                delivery_window: '{{ old('delivery_window') }}',
                vehicle_access: '{{ old('vehicle_access') }}',
                delivery_notes: '{{ old('delivery_notes') }}',
                payment_method: '{{ old('payment_method') }}',
                commodities: {{ Illuminate\Support\Js::from(old('commodities', [])) }},
                password: { value: '', confirmation: '' },
                integrity_accepted: {{ old('integrity_accepted') ? 'true' : 'false' }},
            })" @submit="onSubmit($event)" x-cloak>

            @csrf

            {{-- RINGKASAN ERROR --}}
            <div tabindex="-1" data-gpa-error-summary
                x-show="localErrorMessages().length > 0 || Object.keys(@js($errors?->messages() ?? [])).length > 0"
                class="rounded-lg border border-danger/40 bg-danger-soft p-4" role="alert">
                <p class="flex items-center gap-2 text-xs font-semibold text-danger">
                    <span class="material-symbols-outlined text-sm">error</span>
                    Formulir belum dapat dikirim. Perbaiki poin berikut:
                </p>
                <ul class="mt-2 space-y-1 text-[11px] text-danger">
                    <template x-for="message in localErrorMessages()" :key="message">
                        <li x-text="message"></li>
                    </template>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>

            {{-- DRAF --}}
            <div x-show="draftAvailable" x-cloak
                class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-accent-edge bg-accent/15 px-4 py-3">
                <p class="text-xs text-ink">
                    <span class="font-semibold">Draf formulir terdeteksi</span> pada perangkat ini.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="restoreDraft()"
                        class="rounded-sm border border-brand bg-brand px-3 py-1.5 font-mono text-[10px] font-bold uppercase tracking-wider text-white">
                        Pulihkan Draf
                    </button>
                    <button type="button" @click="discardDraft()"
                        class="rounded-sm border border-line-strong bg-surface px-3 py-1.5 font-mono text-[10px] font-bold uppercase tracking-wider text-ink-muted">
                        Abaikan
                    </button>
                </div>
            </div>

            {{-- BAGIAN 01 // IDENTITAS --}}
            <section class="rounded-xl border border-line bg-surface p-5 shadow-sub md:p-6">
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-line-faint pb-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand font-mono text-xs font-bold text-accent">01</span>
                        <div>
                            <h2 class="text-sm font-bold text-brand">Identitas Penanggung Jawab &amp; Usaha</h2>
                            <p class="font-mono text-[10px] uppercase tracking-wider text-ink-muted">
                                Data PIC dan badan usaha untuk aktivasi akun portal
                            </p>
                        </div>
                    </div>
                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-danger">* Wajib</span>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="name"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Nama Penanggung Jawab <span
                                class="text-danger">*</span></label>
                        <input id="name" name="name" type="text" required x-model="name"
                            value="{{ old('name') }}" autocomplete="name" placeholder="Contoh: Budi Setiawan"
                            class="w-full rounded-sm border-line-strong bg-surface text-sm text-ink placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        @error('name')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label for="business_name"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Nama Usaha / Badan Usaha <span
                                class="text-danger">*</span></label>
                        <input id="business_name" name="business_name" type="text" required x-model="business_name"
                            value="{{ old('business_name') }}" autocomplete="organization"
                            placeholder="Contoh: PT Kuliner Prima Nusantara"
                            class="w-full rounded-sm border-line-strong bg-surface text-sm text-ink placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        @error('business_name')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Nomor WhatsApp PIC <span
                                class="text-danger">*</span></label>
                        <div class="flex" x-ref="phoneField">
                            <span
                                class="inline-flex items-center rounded-l-sm border border-r-0 border-line-strong bg-surface-muted px-3 font-mono text-[11px] font-bold text-ink-body">+62</span>
                            <input id="phone" name="phone" type="tel" inputmode="numeric" required
                                x-model="phone" @input="onPhoneInput($event)"
                                value="{{ old('phone') }}" autocomplete="tel-national" placeholder="812-3456-7890"
                                :aria-invalid="! phoneValid()"
                                class="w-full rounded-r-sm border-line-strong bg-surface font-mono text-xs text-ink placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        </div>
                        @error('phone')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                        <p x-show="localErrors.phone" x-cloak class="mt-1 text-[11px] text-danger"
                            x-text="localErrors.phone"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="email"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Alamat Email (opsional)</label>
                        <input id="email" name="email" type="email" x-model="email" value="{{ old('email') }}"
                            autocomplete="email" placeholder="purchasing@perusahaan.co.id"
                            class="w-full rounded-sm border-line-strong bg-surface text-sm text-ink placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        <p class="mt-1 font-mono text-[10px] text-ink-subtle">
                            Dikosongkan bila hanya login memakai nomor WhatsApp.
                        </p>
                        @error('email')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- OTP --}}
                <div class="mt-5 rounded-lg border border-line bg-surface-muted p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-brand">Verifikasi Nomor WhatsApp (OTP)</p>
                            <p class="mt-0.5 font-mono text-[10px] uppercase tracking-wider text-ink-muted">
                                Wajib terverifikasi sebelum formulir dapat dikirim
                            </p>
                        </div>
                        <button type="button" @click="sendOtp(false)"
                            :disabled="['sending', 'resending'].includes(otp.state) || ! phoneValid()"
                            class="inline-flex items-center gap-1.5 rounded-sm border border-brand bg-brand px-3 py-2 font-mono text-[10px] font-bold uppercase tracking-wider text-white transition-colors hover:bg-brand-hover disabled:cursor-not-allowed disabled:opacity-50">
                            <span class="material-symbols-outlined text-sm" x-text="otp.state === 'sending' ? 'progress_activity' : 'sms'"></span>
                            <span x-text="otp.state === 'sending' ? 'Mengirim...' : 'Kirim Kode OTP'"></span>
                        </button>
                    </div>

                    <div x-show="otp.state === 'sent' || otp.state === 'verifying' || otp.verifiedPhone" x-cloak
                        class="mt-4 flex flex-wrap items-end gap-3 border-t border-line pt-4">
                        <div class="min-w-[200px] flex-1">
                            <label for="otp_code"
                                class="mb-1 block font-mono text-[11px] font-semibold text-ink">Kode OTP 6 Digit</label>
                            <input id="otp_code" type="text" inputmode="numeric" maxlength="6" x-model="otp.code"
                                @input="otp.code = otp.code.replace(/\D/g, '').slice(0, 6)"
                                placeholder="000000"
                                class="w-full rounded-sm border-line-strong bg-surface text-center font-mono text-lg tracking-[0.5em] text-ink placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        </div>
                        <button type="button" @click="verifyOtp()" :disabled="otp.code.length !== 6"
                            class="inline-flex items-center gap-1.5 rounded-sm border border-success bg-success px-3 py-2 font-mono text-[10px] font-bold uppercase tracking-wider text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50">
                            <span class="material-symbols-outlined text-sm"
                                x-text="otp.state === 'verifying' ? 'progress_activity' : 'verified'"></span>
                            <span x-text="otp.state === 'verifying' ? 'Memeriksa...' : 'Verifikasi Kode'"></span>
                        </button>
                        <button type="button" @click="sendOtp(true)" :disabled="otp.resendIn > 0"
                            class="font-mono text-[10px] font-bold uppercase tracking-wider text-success underline underline-offset-2 disabled:cursor-not-allowed disabled:text-ink-subtle disabled:no-underline">
                            <span x-show="otp.resendIn > 0">Kirim Ulang (<span x-text="otp.resendIn"></span>s)</span>
                            <span x-show="otp.resendIn === 0">Kirim Ulang Kode</span>
                        </button>
                    </div>

                    <div x-show="otp.state === 'verified'" x-cloak
                        class="mt-4 flex items-center gap-2 border-t border-line pt-4 text-xs font-semibold text-success-ink">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        Nomor WhatsApp <span class="font-mono">+<span x-text="otp.verifiedPhone"></span></span> terverifikasi.
                    </div>

                    <div x-show="otp.devCode" x-cloak
                        class="mt-3 flex items-center gap-2 rounded-sm border border-warning/40 bg-warning-wash px-3 py-2 font-mono text-[10px] text-warning-deep">
                        <span class="material-symbols-outlined text-sm">science</span>
                        Mode pengembangan &mdash; kode OTP: <span class="font-bold" x-text="otp.devCode"></span>
                    </div>

                    <p x-show="otp.error" x-cloak class="mt-2 text-[11px] text-danger" x-text="otp.error"></p>
                    <p x-show="localErrors.otp" x-cloak class="mt-2 text-[11px] text-danger" x-text="localErrors.otp"></p>
                </div>
            </section>

            {{-- BAGIAN 02 // PENGANTARAN --}}
            <section class="rounded-xl border border-line bg-surface p-5 shadow-sub md:p-6">
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-line-faint pb-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand font-mono text-xs font-bold text-accent">02</span>
                        <div>
                            <h2 class="text-sm font-bold text-brand">Alamat Titik Antar &amp; Parameter Bongkar Muat</h2>
                            <p class="font-mono text-[10px] uppercase tracking-wider text-ink-muted">
                                Penentuan rute armada rantai dingin dan jendela penerimaan dock
                            </p>
                        </div>
                    </div>
                    <span class="hidden font-mono text-[10px] font-bold uppercase tracking-wider text-success sm:block">
                        Logistik Parameters
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label for="address"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Alamat Lengkap Penerimaan <span
                                class="text-danger">*</span></label>
                        <textarea id="address" name="address" rows="3" required x-model="address"
                            placeholder="Jl. Ir. H. Juanda No. 182, Dago, Coblong, Kota Bandung 40135"
                            class="w-full rounded-sm border-line-strong bg-surface p-3 text-sm text-ink placeholder:text-ink-subtle focus:border-brand focus:ring-0">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="delivery_zone"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Zona Pengiriman <span
                                class="text-danger">*</span></label>
                        <select id="delivery_zone" name="delivery_zone" required x-model="delivery_zone"
                            class="w-full rounded-sm border-line-strong bg-surface text-sm text-ink focus:border-brand focus:ring-0">
                            <option value="">-- Pilih zona --</option>
                            @foreach (config('clients.zones') as $key => $zone)
                                <option value="{{ $key }}">
                                    {{ $zone['label'] }} ({{ $zone['code'] }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 font-mono text-[10px] text-ink-subtle">
                            @foreach (config('clients.zones') as $zoneKey => $zone)
                                <span x-show="delivery_zone === '{{ $zoneKey }}'">{{ $zone['cutoff'] }}</span>
                            @endforeach
                        </p>
                        @error('delivery_zone')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="delivery_window"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Jendela Waktu Terima <span
                                class="text-danger">*</span></label>
                        <select id="delivery_window" name="delivery_window" required x-model="delivery_window"
                            class="w-full rounded-sm border-line-strong bg-surface text-sm text-ink focus:border-brand focus:ring-0">
                            <option value="">-- Pilih jendela --</option>
                            @foreach (config('clients.windows') as $key => $window)
                                <option value="{{ $key }}">{{ $window['label'] }} &mdash; {{ $window['note'] }}</option>
                            @endforeach
                        </select>
                        @error('delivery_window')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vehicle_access"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Akses Kendaraan Bongkar Muat <span
                                class="text-danger">*</span></label>
                        <select id="vehicle_access" name="vehicle_access" required x-model="vehicle_access"
                            class="w-full rounded-sm border-line-strong bg-surface text-sm text-ink focus:border-brand focus:ring-0">
                            <option value="">-- Pilih tipe armada --</option>
                            @foreach (config('clients.vehicles') as $key => $vehicle)
                                <option value="{{ $key }}">{{ $vehicle['label'] }} ({{ $vehicle['capacity'] }})</option>
                            @endforeach
                        </select>
                        @error('vehicle_access')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-3">
                        <label for="delivery_notes"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Catatan Khusus Penerimaan</label>
                        <input id="delivery_notes" name="delivery_notes" type="text" x-model="delivery_notes"
                            value="{{ old('delivery_notes') }}" placeholder="Contoh: Masuk lewat gerbang barat, tekan bel 2x."
                            class="w-full rounded-sm border-line-strong bg-surface text-sm text-ink placeholder:text-ink-subtle focus:border-brand focus:ring-0">
                        @error('delivery_notes')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- BAGIAN 03 // KOMODITAS & PEMBAYARAN --}}
            <section class="rounded-xl border border-line bg-surface p-5 shadow-sub md:p-6">
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-line-faint pb-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand font-mono text-xs font-bold text-accent">03</span>
                        <div>
                            <h2 class="text-sm font-bold text-brand">Preferensi Komoditas &amp; Metode Pembayaran</h2>
                            <p class="font-mono text-[10px] uppercase tracking-wider text-ink-muted">
                                Estimasi kebutuhan harian dan skema penyelesaian faktur
                            </p>
                        </div>
                    </div>
                    <span class="hidden font-mono text-[10px] font-bold uppercase tracking-wider text-success sm:block">
                        <span x-text="commodityCount()"></span> Komoditas Dipilih
                    </span>
                </div>

                <label class="mb-3 block font-mono text-[11px] font-semibold text-ink">
                    Pilih Komoditas Inti (minimal 1) <span class="text-danger">*</span>
                </label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (config('clients.commodities') as $key => $commodity)
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-lg border border-line bg-surface-muted p-3 transition-colors hover:bg-surface-raised">
                            <input type="checkbox" name="commodities[]" value="{{ $key }}" x-model="commodities"
                                @checked(in_array($key, old('commodities', []), true))
                                class="mt-0.5 h-4 w-4 shrink-0 rounded-sm border-line-strong text-brand focus:ring-0">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold leading-tight text-brand">{{ $commodity['name'] }}</span>
                                <span class="mt-0.5 block font-mono text-[10px] uppercase tracking-wider text-ink-muted">
                                    MOQ {{ $commodity['moq'] }} {{ $commodity['unit'] }} &middot; {{ $commodity['origin'] }}
                                </span>
                                <span class="mt-0.5 block text-[11px] text-ink-body">{{ $commodity['grade'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('commodities')
                    <p class="mt-2 text-[11px] text-danger">{{ $message }}</p>
                @enderror
                @error('commodities.*')
                    <p class="mt-2 text-[11px] text-danger">{{ $message }}</p>
                @enderror

                <label class="mb-3 mt-6 block font-mono text-[11px] font-semibold text-ink">
                    Metode Pembayaran <span class="text-danger">*</span>
                </label>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    @foreach (config('clients.payment_methods') as $key => $method)
                        <label
                            class="flex cursor-pointer flex-col rounded-lg border border-line bg-surface-muted p-3 transition-colors hover:bg-surface-raised">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="payment_method" value="{{ $key }}" x-model="payment_method"
                                    @checked(old('payment_method') === $key)
                                    class="h-4 w-4 border-line-strong text-brand focus:ring-0">
                                <span class="text-sm font-semibold leading-tight text-brand">{{ $method['name'] }}</span>
                            </span>
                            <span class="mt-1.5 text-[11px] leading-relaxed text-ink-body">{{ $method['description'] }}</span>
                            <span class="mt-1.5 inline-flex w-fit items-center rounded-sm border border-line bg-surface px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-success">
                                Settlement {{ $method['settlement'] }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('payment_method')
                    <p class="mt-2 text-[11px] text-danger">{{ $message }}</p>
                @enderror
            </section>

            {{-- BAGIAN 04 // KREDENSIAL --}}
            <section class="rounded-xl border border-line bg-surface p-5 shadow-sub md:p-6">
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-line-faint pb-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand font-mono text-xs font-bold text-accent">04</span>
                        <div>
                            <h2 class="text-sm font-bold text-brand">Kredensial Akses Portal</h2>
                            <p class="font-mono text-[10px] uppercase tracking-wider text-ink-muted">
                                Kata sandi minimal 8 karakter untuk akses dashboard klien
                            </p>
                        </div>
                    </div>
                    <span x-show="password.value" x-cloak
                        class="inline-flex items-center gap-1.5 rounded-sm border border-line bg-surface-muted px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider">
                        <span class="h-1.5 w-1.5 rounded-full" :class="passwordTone()"></span>
                        <span x-text="passwordLabel()"></span>
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="password"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Kata Sandi <span
                                class="text-danger">*</span></label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            x-model="password.value"
                            class="w-full rounded-sm border-line-strong bg-surface font-mono text-xs text-ink focus:border-brand focus:ring-0">
                        @error('password')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="mb-1 block font-mono text-[11px] font-semibold text-ink">Konfirmasi Kata Sandi <span
                                class="text-danger">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            autocomplete="new-password" x-model="password.confirmation"
                            class="w-full rounded-sm border-line-strong bg-surface font-mono text-xs text-ink focus:border-brand focus:ring-0">
                        <p x-show="password.confirmation && ! passwordsMatch()" x-cloak
                            class="mt-1 text-[11px] text-danger">Konfirmasi kata sandi tidak sama.</p>
                        <p x-show="localErrors.password_confirmation" x-cloak class="mt-1 text-[11px] text-danger"
                            x-text="localErrors.password_confirmation"></p>
                        @error('password_confirmation')
                            <p class="mt-1 text-[11px] text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- BAGIAN 05 // PAKTA INTEGRITAS --}}
            <section class="rounded-xl border border-line bg-surface p-5 shadow-sub md:p-6">
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-line-faint pb-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand font-mono text-xs font-bold text-accent">05</span>
                        <div>
                            <h2 class="text-sm font-bold text-brand">Pakta Integritas Operasional</h2>
                            <p class="font-mono text-[10px] uppercase tracking-wider text-ink-muted">
                                Pernyataan mengikat terkait penimbangan riil dan SLA komplain
                            </p>
                        </div>
                    </div>
                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-success">Rule 04 &amp; 05</span>
                </div>

                <div class="space-y-4 rounded-lg border border-line bg-surface-muted p-4">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" name="integrity_accepted" value="1" required x-model="integrity_accepted"
                            @checked(old('integrity_accepted'))
                            class="mt-1 h-4 w-4 shrink-0 rounded-sm border-line-strong text-brand focus:ring-0">
                        <span class="text-xs leading-relaxed text-ink-body">
                            <span class="mb-0.5 block font-bold text-brand">Klausul Rule 04 &amp; 05: Net Weight Binding &amp; Tera Metrologi Sah</span>
                            Kami menyatakan sepakat bahwa seluruh penagihan final fakta komoditas segar mutlak mengacu pada lembar
                            timbangan riil tera metrologi sah di hub keberangkatan AgroOrder GPA, dengan toleransi susut
                            alami sesuai grade komoditas yang dipilih.
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" disabled
                            class="mt-1 h-4 w-4 shrink-0 rounded-sm border-line-strong bg-surface-raised text-brand">
                        <span class="text-xs leading-relaxed text-ink-body">
                            <span class="mb-0.5 block font-bold text-brand">Kewajiban Pemeriksaan Kualitas &amp; SLA Komplain</span>
                            PIC dock wajib menandatangani Berita Acara Serah Terima dan menyampaikan anomali fisik atau mutu
                            maksimal 4 jam sejak truk selesai dibongkar.
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" disabled
                            class="mt-1 h-4 w-4 shrink-0 rounded-sm border-line-strong bg-surface-raised text-brand">
                        <span class="text-xs leading-relaxed text-ink-body">
                            <span class="mb-0.5 block font-bold text-brand">Otorisasi Pemeriksaan Kelayakan Keuangan</span>
                            Memberikan izin kepada Tim Finansial &amp; Legal GPA untuk memverifikasi riwayat pembiayaan
                            guna penentuan limit pembayaran.
                        </span>
                    </label>
                </div>
                @error('integrity_accepted')
                    <p class="mt-2 text-[11px] text-danger">{{ $message }}</p>
                @enderror
            </section>

            {{-- AKSI --}}
            <section class="rounded-xl border border-line bg-surface p-5 shadow-card md:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-ink-muted">
                                Kelengkapan formulir
                            </p>
                            <p class="font-mono text-[10px] font-bold text-brand">
                                <span x-text="completion()"></span>%
                            </p>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-surface-track">
                            <div class="h-full rounded-full bg-brand transition-all duration-300"
                                :style="`width: ${completion()}%`"></div>
                        </div>
                        <p class="mt-2 font-mono text-[10px] text-ink-subtle">
                            Simpan draf formulir di perangkat ini. Kata sandi tidak ikut disimpan.
                        </p>
                    </div>

                    <div class="flex w-full flex-wrap items-center gap-3 lg:w-auto lg:justify-end">
                        <button type="button" @click="saveDraft()"
                            class="flex-1 rounded-sm border border-line-strong bg-surface-muted px-4 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-ink-muted transition-colors hover:bg-surface-raised lg:flex-none">
                            Simpan Draf Formulir
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="flex flex-1 items-center justify-center gap-2 rounded-sm border border-brand bg-brand px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-accent transition-colors hover:bg-brand-hover active:translate-y-px disabled:cursor-wait disabled:opacity-60 lg:flex-none">
                            <span x-text="submitting ? 'Mengirim...' : 'Daftar & Aktifkan Akun'"></span>
                            <span class="material-symbols-outlined text-base">arrow_forward</span>
                        </button>
                    </div>
                </div>
            </section>
        </form>

        {{-- MATRIKS PERBANDINGAN SKEMA AKUN --}}
        <section aria-label="Matriks Perbandingan Skema Akun" class="mt-10">
            <div class="mb-4">
                <div
                    class="mb-1 inline-flex items-center gap-2 font-mono text-[10px] font-bold uppercase tracking-wider text-success">
                    <span class="h-2 w-2 rounded-full bg-success"></span>
                    Transparent Tier Comparison
                </div>
                <h2 class="text-xl font-bold tracking-tight text-brand md:text-2xl">Matriks Komparasi Skema Akun</h2>
                <p class="mt-1 text-sm text-ink-body">
                    Bagan evaluasi hak operasional, alokasi hasil panen, dan ketentuan finansial antar kategori akun mitra.
                </p>
            </div>

            <div class="overflow-hidden rounded-xl border border-line bg-surface shadow-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-line bg-surface-muted">
                                <th scope="col"
                                    class="px-4 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-ink-muted">
                                    Parameter
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-success">
                                    {{ config('clients.modes.reguler')['code'] }} &middot; Reguler
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-brand">
                                    {{ config('clients.modes.enterprise')['code'] }} &middot; Enterprise
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (config('clients.matrix') as $row)
                                <tr class="border-b border-line-faint last:border-0">
                                    <th scope="row" class="px-4 py-3 text-xs font-semibold text-ink">{{ $row['label'] }}</th>
                                    <td class="px-4 py-3 text-xs text-ink-body">{{ $row['reguler'] }}</td>
                                    <td class="px-4 py-3 text-xs text-ink-body">{{ $row['enterprise'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach (config('clients.modes') as $mode)
                    <div class="rounded-lg border border-line bg-surface p-4 shadow-sub">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-brand">{{ $mode['name'] }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-ink-body">{{ $mode['description'] }}</p>
                            </div>
                            <span
                                class="shrink-0 rounded-sm border border-line bg-surface-muted px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-ink-muted">
                                {{ $mode['code'] }}
                            </span>
                        </div>
                        <p class="mt-3 font-mono text-[10px] font-bold uppercase tracking-wider text-success">
                            {{ $mode['sla'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <x-gpa.toast />
</body>

</html>
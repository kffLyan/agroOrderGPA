@props([
    'validation' => null,
])

<section class="rounded-xl border border-accent-deep/50 bg-surface p-4 shadow-sub sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
        <h2 class="flex items-center gap-2 text-sm font-bold text-ink">
            {{ $validation['title'] ?? 'Validasi Kuota & Kontrak' }}
        </h2>
    </div>

    <div class="mt-3 space-y-1">
        <p class="gpa-mono-xs font-semibold uppercase tracking-wider text-ink-subtle">Ketentuan Pemesanan</p>
        <p class="gpa-mono-xs font-semibold text-brand-strong">Standar Pasokan Komersial & Horeca</p>
    </div>

    <p class="mt-3 flex items-baseline justify-between gap-3 rounded-lg border border-line-hair bg-canvas px-3 py-2">
        <span class="gpa-mono-xs text-ink-body">Batas Waktu Order:</span>
        <span class="gpa-mono-xs font-extrabold text-brand-strong">Cut-Off 13:00 WIB</span>
    </p>

    <div class="mt-4">
        <p class="gpa-micro tracking-wider text-accent-deep">Protokol Logistik & Tera</p>
        <dl class="mt-2 divide-y divide-line-hair rounded-lg border border-line-hair bg-canvas px-3">
            <div class="flex items-baseline justify-between gap-3 py-2">
                <dt class="gpa-mono-xs text-ink-subtle">Penimbangan:</dt>
                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Tera Metrologi Sah</dd>
            </div>
            <div class="flex items-baseline justify-between gap-3 py-2">
                <dt class="gpa-mono-xs text-ink-subtle">Toleransi Susut:</dt>
                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">&lt; 1.5% Netto</dd>
            </div>
            <div class="flex items-baseline justify-between gap-3 py-2">
                <dt class="gpa-mono-xs text-ink-subtle">Jadwal Tiba:</dt>
                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">03:00 - 05:30 Subuh</dd>
            </div>
        </dl>
    </div>

    <x-gpa.btn :href="route('public.supply-chain')" variant="secondary" block class="mt-4">
        <x-slot:icon>
            <x-gpa.icon name="info" />
        </x-slot:icon>
        Pelajari Alur Rantai Pasok
    </x-gpa.btn>
</section>

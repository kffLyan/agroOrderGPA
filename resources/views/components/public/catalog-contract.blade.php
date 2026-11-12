@props([
    'contract' => null,
])

<section class="flex flex-col gap-4 rounded-xl border border-brand-strong bg-brand p-5 text-white shadow-sub lg:flex-row lg:items-center lg:justify-between lg:p-6">
    <div class="flex min-w-0 gap-4">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent text-brand-deep">
            <x-gpa.icon name="users" class="h-5 w-5" />
        </span>

        <div class="min-w-0 max-w-3xl space-y-1.5">
            <h2 class="text-base font-bold leading-snug">{{ $contract['title'] ?? 'Butuh Pasokan Rutin Mingguan atau Harga Flat Kuartalan?' }}</h2>
            <p class="text-xs leading-relaxed text-white/85">{{ $contract['body'] ?? 'Klien korporat (Horeca, Katering Penerbangan, Ritel) dapat mengunci alokasi panen mingguan dengan garansi harga flat dan syarat pembayaran Term of Payment (TOP 14 - 45 hari).' }}</p>
            <p class="gpa-mono-xs text-accent">{{ $contract['note'] ?? 'Prioritas alokasi saat musim panen langka & Jadwal kirim subuh terdedikasi' }}</p>
        </div>
    </div>

    <x-gpa.btn :href="route('public.mitra-kontrak')" variant="accent" size="xl"
        class="w-full max-w-sm whitespace-normal text-center leading-tight lg:w-auto lg:shrink-0">
        <x-slot:icon>
            <x-gpa.icon name="plus" />
        </x-slot:icon>
        Ajukan Registrasi Kemitraan B2B
    </x-gpa.btn>
</section>

@php
    $activeProductsCount = \App\Models\Product::where('is_active', true)->count();
    $totalStock = (float) \App\Models\HarvestBatch::sum('available_quantity');
@endphp

<div class="bg-brand-deep text-white">
    <div
        class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-1 px-4 py-2 gpa-mono-xs sm:px-6">
        <p class="flex min-w-0 flex-wrap items-center gap-x-2">
            <span class="inline-flex items-center gap-1.5 font-bold tracking-wide">
                <span class="h-2 w-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                GPA-NODE: ACTIVE
            </span>
            <span class="text-white/40" aria-hidden="true">•</span>
            <span class="truncate text-white/80">Hub Sentral & Gudang Konsolidasi Ciwidey & Lembang</span>
        </p>

        <dl class="flex flex-wrap items-center gap-x-4 gap-y-1">
            <div class="flex items-center gap-1.5">
                <dt class="text-white/80">Komoditas Terverifikasi:</dt>
                <dd class="text-accent">{{ $activeProductsCount }} Aktif</dd>
            </div>
            <div class="flex items-center gap-1.5">
                <dt class="text-white/80">Buffer Live Stock:</dt>
                <dd class="text-accent">{{ number_format($totalStock, 0, ',', '.') }} kg</dd>
            </div>
        </dl>
    </div>
</div>
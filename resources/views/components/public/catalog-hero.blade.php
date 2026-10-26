@props([
    'hero' => null,
    'breadcrumb' => null,
])

@php
    $crumbs = $breadcrumb['items'] ?? [
        ['label' => 'Beranda', 'href' => '/'],
        ['label' => 'Katalog Komoditas Agribisnis'],
    ];

    $title = $hero['title'] ?? 'Katalog Komoditas Agribisnis';
    $lead = $hero['lead'] ?? 'Ketersediaan pasokan panen segar langsung dari Petani Binaan & Buffer Stock Terpadu PT Green Pasundan Agriculture. Harga transparan per kilogram franco packhouse terverifikasi.';
    $assurances = $hero['assurances'] ?? [
        'Harga Franco Gudang / siap muat',
        'Sinkronisasi stok packhouse real-time',
        'Spesifikasi grade & cold-chain terbuka',
    ];
    $sync = $hero['sync'] ?? [
        'label' => 'Sinkronisasi Stok',
        'status' => 'LIVE',
        'age' => 'Realtime DB',
        'nodeLabel' => 'Node',
        'node' => 'Ciwidey, Pacet & Lembang',
    ];
@endphp

<section class="border-b border-line-hair bg-canvas">
    <div class="mx-auto w-full max-w-6xl px-4 pt-6 pb-8 sm:px-6">
        <nav aria-label="Remah roti">
            <ol class="flex flex-wrap items-center gap-2 gpa-mono-xs">
                @foreach ($crumbs as $crumb)
                    <li>
                        @isset($crumb['href'])
                            <a href="{{ url($crumb['href']) }}"
                                class="font-semibold uppercase tracking-wider text-brand-strong hover:text-accent-deep">{{ $crumb['label'] }}</a>
                        @else
                            <span aria-current="page"
                                class="font-semibold uppercase tracking-wider text-ink-body">{{ $crumb['label'] }}</span>
                        @endisset
                    </li>
                    @unless ($loop->last)
                        <li class="text-ink-subtle" aria-hidden="true">/</li>
                    @endunless
                @endforeach
            </ol>
        </nav>

        <div class="mt-5 overflow-hidden rounded-2xl bg-gradient-to-br from-brand to-brand-deep text-white shadow-pop">
            <div class="flex flex-col gap-6 p-6 md:flex-row md:items-start md:justify-between md:p-8">
                <div class="max-w-2xl space-y-4">
                    <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl">{{ $title }}</h1>
                    <p class="text-sm leading-relaxed text-white/85 md:text-base">{{ $lead }}</p>

                    <ul class="flex flex-wrap gap-2 pt-1">
                        @foreach ($assurances as $assurance)
                            <li
                                class="inline-flex items-center gap-1.5 rounded border border-white/15 bg-white/5 px-2 py-1 gpa-mono-xs text-white/85">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-accent" />
                                {{ $assurance }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="shrink-0 rounded-xl border border-white/15 bg-brand-deep/70 p-4 md:w-64">
                    <p class="gpa-mono-xs font-semibold uppercase tracking-wider text-white/60">
                        {{ $sync['label'] }}
                    </p>
                    <p class="mt-2 flex items-center gap-2 text-sm font-extrabold text-accent">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-accent animate-pulse" aria-hidden="true"></span>
                        {{ $sync['status'] }}
                        <span class="gpa-mono-xs font-medium text-white/70">({{ $sync['age'] }})</span>
                    </p>
                    <p class="mt-3 border-t border-white/10 pt-3 gpa-mono-xs text-white/70">
                        {{ $sync['nodeLabel'] }}:
                        <span class="font-semibold text-white">{{ $sync['node'] }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

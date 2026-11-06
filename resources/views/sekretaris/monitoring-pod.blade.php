@extends('layouts.sekretaris')

@section('title', 'Monitoring PoD (Bukti Serah Terima) // Logistik & Armada')

@section('content')
    <div x-data="{
        photoModalOpen: false,
        activeTab: 'manifest',
        manifestPhotoUrl: '',
        dockPhotoUrl: '',
        activeOrderNum: '',
        activeSjNum: '',
        activeReceiver: '',
        activeTime: '',
        openPhotos(manifestUrl, dockUrl, orderNum, sjNum, receiver, time) {
            this.manifestPhotoUrl = manifestUrl || '';
            this.dockPhotoUrl = dockUrl || '';
            this.activeTab = this.manifestPhotoUrl ? 'manifest' : (this.dockPhotoUrl ? 'dock' : 'manifest');
            this.activeOrderNum = orderNum;
            this.activeSjNum = sjNum;
            this.activeReceiver = receiver;
            this.activeTime = time;
            this.photoModalOpen = true;
        },
        currentPhotoUrl() {
            if (this.activeTab === 'dock' && this.dockPhotoUrl) {
                return this.dockPhotoUrl;
            }
            return this.manifestPhotoUrl || this.dockPhotoUrl || '';
        }
    }" class="flex flex-col gap-3">

        {{-- Header & Aksi Cepat --}}
        <header class="flex flex-col gap-2 rounded-xl bg-surface p-3 sm:p-3.5 shadow-card ring-1 ring-line-hair sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="font-sans text-lg font-bold tracking-tight text-ink sm:text-xl">
                        Monitoring Bukti Serah Terima (PoD)
                    </h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-accent px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-ink ring-1 ring-inset ring-accent-edge">
                        <span class="h-1.5 w-1.5 rounded-full bg-success-deep animate-pulse"></span>
                        Live Audit Armada
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('sekretaris.surat-jalan') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-soft bg-surface px-3 py-2 text-xs font-semibold text-ink shadow-xs transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="truck" class="h-3.5 w-3.5 text-ink-body" />
                    <span>Penerbitan Surat Jalan</span>
                </a>
                <a href="{{ route('sekretaris.laporan') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-3 py-2 text-xs font-bold text-white shadow-xs transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 text-accent" />
                    <span>Rekap Laporan</span>
                </a>
            </div>
        </header>

        {{-- 4 Kartu Metrik Ringkasan --}}
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:gap-4">
            {{-- 1. Total Selesai Diterima --}}
            <div class="flex flex-col gap-2 rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">Pengantaran Selesai</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-accent/30 text-ink">
                        <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-ink">{{ $totalCompleted }}</span>
                    <span class="text-xs font-medium text-ink-quiet">Titik Serah Terima</span>
                </div>
            </div>

            {{-- 2. Serah Terima Bersih (100% Sesuai) --}}
            <div class="flex flex-col gap-2 rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">100% Bersih (No Retur)</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <x-gpa.icon name="check" class="h-4 w-4 text-emerald-600" />
                    </span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-emerald-700">{{ $cleanCount }}</span>
                    <span class="text-xs font-medium text-emerald-600">Dokumen</span>
                </div>
           </div>

            {{-- 3. Ada Catatan Retur / Selisih --}}
            <div class="flex flex-col gap-2 rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">Catatan Retur Mutu</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-warning-soft text-warning-deep">
                        <x-gpa.icon name="alert-triangle" class="h-4 w-4 text-warning-deep" />
                    </span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-warning-deep">{{ $discrepancyCount }}</span>
                    <span class="text-xs font-medium text-warning-deep">Kasus</span>
                </div>
                <p class="mt-1 text-[10px] text-ink-quiet">Total {{ number_format($totalReturnKg, 1, ',', '.') }} kg susut/sortir</p>
            </div>

            {{-- 4. Armada Dalam Pengiriman --}}
            <div class="flex flex-col gap-2 rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">Dalam Pengiriman</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <x-gpa.icon name="truck" class="h-4 w-4 text-blue-600" />
                    </span>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-blue-700">{{ $inTransitCount }}</span>
                    <span class="text-xs font-medium text-blue-600">Armada Menuju Dock</span>
                </div>
            </div>
        </section>

        {{-- Bar Filter & Pencarian --}}
        <section class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-card">
            <form method="GET" action="{{ route('sekretaris.monitoring-pod') }}" class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-1 flex-wrap items-center gap-2 sm:max-w-xl">
                    <div class="relative min-w-[15rem] flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-body">
                            <x-gpa.icon name="search" class="h-3.5 w-3.5" />
                        </span>
                        <input type="search" name="search" value="{{ $search }}"
                            placeholder="Cari No. SJ, PO, Klien, Supir, Plat, PIC..."
                            class="gpa-control h-9 w-full rounded-lg border-line-soft pl-9 pr-3 text-xs placeholder:text-ink-quiet focus:border-accent">
                    </div>

                    <select name="status" onchange="this.form.submit()"
                        class="gpa-control h-9 rounded-lg border-line-soft bg-surface text-xs font-semibold text-ink">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status Pengantaran</option>
                        <option value="clean" {{ $statusFilter === 'clean' ? 'selected' : '' }}>Selesai Bersih (100% Sah)</option>
                        <option value="discrepancy" {{ $statusFilter === 'discrepancy' ? 'selected' : '' }}>Selesai (Ada Catatan Retur)</option>
                        <option value="in_transit" {{ $statusFilter === 'in_transit' ? 'selected' : '' }}>Dalam Pengiriman</option>
                        <option value="ready" {{ $statusFilter === 'ready' ? 'selected' : '' }}>Siap Kirim</option>
                    </select>

                    <select name="driver_id" onchange="this.form.submit()"
                        class="gpa-control h-9 rounded-lg border-line-soft bg-surface text-xs font-semibold text-ink">
                        <option value="all">Semua Supir Armada</option>
                        @foreach ($drivers as $drv)
                            <option value="{{ $drv->id }}" {{ (string)$driverFilter === (string)$drv->id ? 'selected' : '' }}>
                                {{ $drv->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button type="submit"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-ink px-4 text-xs font-bold text-white shadow-xs hover:bg-brand-deep">
                        <x-gpa.icon name="filter" class="h-3.5 w-3.5 text-accent" />
                        <span>Filter Data</span>
                    </button>
                    @if ($search || $statusFilter !== 'all' || $driverFilter !== 'all')
                        <a href="{{ route('sekretaris.monitoring-pod') }}"
                            class="inline-flex h-9 items-center rounded-lg border border-line-soft bg-surface px-3 text-xs font-semibold text-ink-body hover:bg-surface-muted">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Monitoring Data PoD --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Daftar Bukti Serah Terima &amp; Verifikasi Dock</h2>
                    <p class="mt-1 text-xs text-ink-body">Menampilkan {{ $orders->total() }} data hasil pemantauan armada</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                        <span class="h-1.5 w-1.5 rounded-full bg-success-deep"></span>
                        Live Audit Armada
                    </span>
                </div>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <caption class="sr-only">Monitoring PoD Armada</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">No. SJ &amp; Order</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Klien &amp; Titik Bongkar</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Supir &amp; Armada</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Penerima &amp; Waktu Serah Terima</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kondisi Pengantaran</th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Foto PoD</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($orders as $order)
                            @php
                                $sjNum = $order->surat_jalan_number ?: ('SJ-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
                                $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Klien GPA');
                                $driverName = $order->driver ? $order->driver->name : 'Belum Ditugaskan';
                                $plateNumber = $order->vehicle_plate_number ?: '—';
                                $receiver = $order->received_by_name ?: 'Staf Receiving Klien';
                                $arrivalTime = $order->arrival_time ? $order->arrival_time->format('d M Y, H:i') . ' WIB' : ($order->status === 'SELESAI' || $order->status === 'SELESAI_CATATAN' ? 'Telah diverifikasi' : 'Dalam Perjalanan');

                                $isClean = ($order->status === 'SELESAI');
                                $isDiscrepancy = ($order->status === 'SELESAI_CATATAN');
                                $isInTransit = ($order->status === 'DALAM_PENGIRIMAN');
                                $isReady = ($order->status === 'SIAP_KIRIM');

                                $manifestPhoto = $order->pod_manifest_photo_url;
                                $dockPhoto = $order->pod_dock_photo_url;
                                $hasAnyPhoto = ($manifestPhoto || $dockPhoto);
                                $photoCount = ($manifestPhoto ? 1 : 0) + ($dockPhoto && $dockPhoto !== $manifestPhoto ? 1 : 0);

                                $returItems = $order->orderItems->filter(fn($it) => (float)($it->returned_weight ?? 0) > 0);
                            @endphp
                            <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                {{-- 1. No. SJ & Order --}}
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-mono text-[12px] font-bold leading-5 text-ink">{{ $sjNum }}</span>
                                        <span class="gpa-note text-ink-body">PO: {{ $order->order_number }}</span>
                                        <div class="pt-0.5">
                                            @if ($isClean)
                                                <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">DITERIMA BERSIH</span>
                                            @elseif ($isDiscrepancy)
                                                <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">ADA CATATAN RETUR</span>
                                            @elseif ($isInTransit)
                                                <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board">DALAM PENGIRIMAN</span>
                                            @elseif ($isReady)
                                                <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board">SIAP KIRIM</span>
                                            @else
                                                <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line-board">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Klien & Titik Bongkar --}}
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-col gap-0.5 max-w-[280px]">
                                        <p class="text-[12px] font-semibold leading-5 text-ink">{{ $clientName }}</p>
                                        <p class="gpa-note leading-4 text-ink-body line-clamp-2">{{ $order->delivery_address ?: 'Gudang Utama Klien' }}</p>
                                    </div>
                                </td>

                                {{-- 3. Supir & Armada --}}
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        <p class="text-[12px] font-semibold leading-5 text-ink">{{ $driverName }}</p>
                                        <span class="inline-flex w-fit rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[10px] font-bold leading-[15px] text-ink outline outline-1 -outline-offset-1 outline-line-board">{{ $plateNumber }}</span>
                                    </div>
                                </td>

                                {{-- 4. Penerima & Waktu Serah Terima --}}
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-col gap-0.5">
                                        <p class="text-[12px] font-semibold leading-5 text-ink">{{ $receiver }}</p>
                                        <p class="gpa-note leading-4 text-ink-body">{{ $arrivalTime }}</p>
                                    </div>
                                </td>

                                {{-- 5. Kondisi Pengantaran --}}
                                <td class="px-3 py-3.5">
                                    @if ($isDiscrepancy && $returItems->isNotEmpty())
                                        <div class="flex flex-col gap-1.5 rounded-lg border border-line-board bg-surface-shell/40 p-2">
                                            <p class="text-[10px] font-semibold uppercase leading-4 tracking-[0.12em] text-ink-body">Item Diretur</p>
                                            @foreach ($returItems as $ri)
                                                <div class="flex items-start justify-between gap-2 text-[11px] leading-4">
                                                    <span class="text-ink">{{ $ri->product?->name ?? 'Komoditas' }}</span>
                                                    <span class="font-semibold text-ink">{{ number_format($ri->returned_weight, 1, ',', '.') }} kg</span>
                                                </div>
                                                @if ($ri->return_reason)
                                                    <p class="text-[10px] leading-4 text-ink-body italic">"{{ $ri->return_reason }}"</p>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif ($isClean)
                                        <p class="text-[11px] leading-4 text-ink-body">Muatan utuh &amp; sesuai bobot tera</p>
                                    @elseif ($isInTransit)
                                        <p class="text-[11px] leading-4 text-ink-body">Menunggu tiba di lokasi bongkar</p>
                                    @elseif ($isReady)
                                        <p class="text-[11px] leading-4 text-ink-body">Menunggu armada berangkat</p>
                                    @else
                                        <p class="text-[11px] leading-4 text-ink-body">Menunggu serah terima</p>
                                    @endif
                                </td>

                                {{-- 6. Foto PoD --}}
                                <td class="px-3 py-3.5 text-center">
                                    @if ($hasAnyPhoto)
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if ($manifestPhoto)
                                                <button type="button"
                                                    @click="openPhotos('{{ $manifestPhoto }}', '{{ $dockPhoto }}', '{{ $order->order_number }}', '{{ $sjNum }}', '{{ $receiver }}', '{{ $arrivalTime }}'); activeTab = 'manifest';"
                                                    title="Foto 1: Lembar Surat Jalan Berstempel & Tanda Tangan"
                                                    class="group relative block h-12 w-14 overflow-hidden rounded-lg border border-line-board bg-surface-shell shadow-2xs transition-transform hover:scale-105 hover:border-accent">
                                                    <img src="{{ $manifestPhoto }}" alt="Surat Jalan"
                                                        class="h-full w-full object-cover transition-opacity group-hover:opacity-80"
                                                        onerror="this.onerror=null;this.style.opacity='0.4';">
                                                    <div class="absolute inset-0 flex items-center justify-center bg-ink/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                        <x-gpa.icon name="eye" class="h-3 w-3 text-accent" />
                                                    </div>
                                                    <span class="absolute bottom-0 inset-x-0 bg-ink/75 py-0.5 text-[8px] font-bold text-white text-center leading-none">SJ Fisik</span>
                                                </button>
                                            @endif

                                            @if ($dockPhoto && $dockPhoto !== $manifestPhoto)
                                                <button type="button"
                                                    @click="openPhotos('{{ $manifestPhoto }}', '{{ $dockPhoto }}', '{{ $order->order_number }}', '{{ $sjNum }}', '{{ $receiver }}', '{{ $arrivalTime }}'); activeTab = 'dock';"
                                                    title="Foto 2: Penurunan Muatan di Dock / Timbangan"
                                                    class="group relative block h-12 w-14 overflow-hidden rounded-lg border border-line-board bg-surface-shell shadow-2xs transition-transform hover:scale-105 hover:border-accent">
                                                    <img src="{{ $dockPhoto }}" alt="Muatan Dock"
                                                        class="h-full w-full object-cover transition-opacity group-hover:opacity-80"
                                                        onerror="this.onerror=null;this.style.opacity='0.4';">
                                                    <div class="absolute inset-0 flex items-center justify-center bg-ink/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                        <x-gpa.icon name="eye" class="h-3 w-3 text-accent" />
                                                    </div>
                                                    <span class="absolute bottom-0 inset-x-0 bg-emerald-900/80 py-0.5 text-[8px] font-bold text-white text-center leading-none">Bongkar</span>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-[9px] font-mono font-semibold text-ink-quiet">{{ $photoCount }} Berkas Foto</p>
                                    @else
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-semibold leading-[15px] gpa-meta text-ink-body">Belum Ada</span>
                                    @endif
                                </td>

                                {{-- 7. Aksi --}}
                                <td class="px-3 py-3.5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        @if ($hasAnyPhoto)
                                            <button type="button"
                                                @click="openPhotos('{{ $manifestPhoto }}', '{{ $dockPhoto }}', '{{ $order->order_number }}', '{{ $sjNum }}', '{{ $receiver }}', '{{ $arrivalTime }}')"
                                                title="Lihat Berkas Foto PoD Lengkap"
                                                class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                                <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                                Lihat ({{ $photoCount }})
                                            </button>
                                        @endif
                                        @if ($isDiscrepancy || $returItems->isNotEmpty())
                                            <a href="{{ route('prints.return-act', ['order_id' => $order->id]) }}" target="_blank"
                                                title="Cetak Berita Acara Retur"
                                                class="inline-flex items-center gap-1 rounded bg-rose-50 px-2 py-1 gpa-note font-semibold text-rose-700 outline outline-1 -outline-offset-1 outline-rose-300 transition-colors hover:bg-rose-100">
                                                <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                                                BAP Retur
                                            </a>
                                        @endif
                                        <a href="{{ route('prints.surat-jalan', ['order_id' => $order->id]) }}" target="_blank"
                                            title="Cetak Surat Jalan"
                                            class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="printer" class="h-3 w-3 shrink-0" />
                                            Cetak SJ
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="truck" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Tidak ada data pengiriman</p>
                                        <p class="text-xs text-ink-body">Coba ubah kata kunci pencarian atau filter status pengantaran.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 px-6 py-3">
                    <p class="gpa-note text-ink-body">Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} data</p>
                    <div class="gpa-pagination">
                        {{ $orders->links() }}
                    </div>
                </footer>
            @endif
        </section>

        {{-- Modal Pratinjau Foto PoD --}}
        <div x-cloak x-show="photoModalOpen"
            @keydown.escape.window="photoModalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-ink/75 backdrop-blur-xs transition-opacity"
                @click="photoModalOpen = false"></div>

            <div class="relative flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-surface shadow-pop ring-1 ring-ink/10"
                x-show="photoModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-line-hair bg-surface-shell px-5 py-3.5">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent text-ink">
                            <x-gpa.icon name="shield" class="h-4 w-4" />
                        </span>
                        <div class="flex flex-col">
                            <h3 class="font-sans text-sm font-bold text-ink">
                                Audit Berkas Fisik Bukti Serah Terima (PoD Lengkap)
                            </h3>
                            <p class="text-[11px] text-ink-body">
                                Surat Jalan: <strong class="text-ink" x-text="activeSjNum"></strong> &bull; PO: <strong class="text-ink" x-text="activeOrderNum"></strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="photoModalOpen = false"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-body transition-colors hover:bg-surface-muted hover:text-ink">
                        <x-gpa.icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                {{-- Tab Pemilih 2 Berkas: Surat Jalan vs Penurunan Barang --}}
                <div class="flex flex-wrap items-center justify-center gap-2 border-b border-line-hair bg-surface px-4 py-2.5">
                    <template x-if="manifestPhotoUrl">
                        <button type="button" @click="activeTab = 'manifest'"
                            :class="activeTab === 'manifest' ? 'bg-ink text-accent ring-1 ring-ink shadow-xs' : 'bg-surface-shell text-ink-body hover:text-ink border border-line-board'"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all">
                            <x-gpa.icon name="file-text" class="h-3.5 w-3.5" />
                            <span>1. Lembar Surat Jalan Fisik (Tanda Tangan &amp; Cap)</span>
                        </button>
                    </template>
                    <template x-if="dockPhotoUrl">
                        <button type="button" @click="activeTab = 'dock'"
                            :class="activeTab === 'dock' ? 'bg-ink text-accent ring-1 ring-ink shadow-xs' : 'bg-surface-shell text-ink-body hover:text-ink border border-line-board'"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold transition-all">
                            <x-gpa.icon name="truck" class="h-3.5 w-3.5" />
                            <span>2. Bukti Penurunan Barang / Dock &amp; Timbangan</span>
                        </button>
                    </template>
                </div>

                {{-- Modal Body Image Container --}}
                <div class="flex max-h-[65vh] min-h-[280px] items-center justify-center overflow-auto bg-black p-4">
                    <template x-if="activeTab === 'manifest' && manifestPhotoUrl">
                        <img :src="manifestPhotoUrl" alt="Foto Lembar Surat Jalan Berstempel"
                            class="max-h-[60vh] w-auto max-w-full rounded-lg object-contain shadow-2xl">
                    </template>
                    <template x-if="activeTab === 'dock' && dockPhotoUrl">
                        <img :src="dockPhotoUrl" alt="Foto Penurunan Barang / Dock"
                            class="max-h-[60vh] w-auto max-w-full rounded-lg object-contain shadow-2xl">
                    </template>
                    <template x-if="!currentPhotoUrl()">
                        <div class="flex flex-col items-center justify-center gap-2 text-ink-muted">
                            <x-gpa.icon name="image" class="h-8 w-8" />
                            <p class="text-xs">Tidak ada foto tersedia untuk kategori ini.</p>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer Metadata & Aksi --}}
                <div class="flex flex-col gap-2 border-t border-line-hair bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col text-xs text-ink-body">
                        <span>Penerima di Lokasi: <strong class="text-ink" x-text="activeReceiver"></strong></span>
                        <span>Waktu Serah Terima: <strong class="text-ink" x-text="activeTime"></strong></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="currentPhotoUrl()" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-line-soft bg-surface px-3 py-2 text-xs font-bold text-ink shadow-xs transition-colors hover:bg-surface-muted">
                            <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-ink-body" />
                            <span>Buka di Tab Baru</span>
                        </a>
                        <button type="button" @click="photoModalOpen = false"
                            class="inline-flex items-center rounded-lg bg-ink px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-brand-deep">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

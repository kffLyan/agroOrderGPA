@extends('layouts.sekretaris')

@section('title', 'Monitoring PoD (Bukti Serah Terima) // Logistik & Armada')

@section('content')
    <div x-data="{
        photoModalOpen: false,
        activePhotoUrl: '',
        activeOrderNum: '',
        activeSjNum: '',
        activeReceiver: '',
        activeTime: '',
        openPhoto(url, orderNum, sjNum, receiver, time) {
            this.activePhotoUrl = url;
            this.activeOrderNum = orderNum;
            this.activeSjNum = sjNum;
            this.activeReceiver = receiver;
            this.activeTime = time;
            this.photoModalOpen = true;
        }
    }" class="flex flex-col gap-6">

        {{-- Header & Aksi Cepat --}}
        <header class="flex flex-col gap-3 border-b border-line-hair pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="font-sans text-xl font-bold tracking-tight text-ink sm:text-2xl">
                        Monitoring Bukti Serah Terima (PoD)
                    </h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-accent px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-ink ring-1 ring-inset ring-accent-edge">
                        <span class="h-1.5 w-1.5 rounded-full bg-success-deep animate-pulse"></span>
                        Live Audit Armada
                    </span>
                </div>
                <p class="text-xs text-ink-body max-w-2xl">
                    Pemantauan langsung seluruh dokumen fisik tanda terima pengantaran, foto stempel basah penerima dock, dan verifikasi retur muatan sayuran segar dari supir logistik.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('sekretaris.dispatch') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-soft bg-surface px-3 py-2 text-xs font-semibold text-ink shadow-xs transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="truck" class="h-3.5 w-3.5 text-ink-body" />
                    <span>Penerbitan Surat Jalan</span>
                </a>
                <a href="{{ route('sekretaris.reports') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-3 py-2 text-xs font-bold text-white shadow-xs transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 text-accent" />
                    <span>Rekap Laporan</span>
                </a>
            </div>
        </header>

        {{-- 4 Kartu Metrik Ringkasan --}}
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:gap-4">
            {{-- 1. Total Selesai Diterima --}}
            <div class="flex flex-col justify-between rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">Pengantaran Selesai</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-accent/30 text-ink">
                        <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-ink">{{ $totalCompleted }}</span>
                    <span class="text-xs font-medium text-ink-quiet">Titik Serah Terima</span>
                </div>
                <p class="mt-1 text-[10px] text-ink-quiet">PoD &amp; Berita Acara sah</p>
            </div>

            {{-- 2. Serah Terima Bersih (100% Sesuai) --}}
            <div class="flex flex-col justify-between rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">100% Bersih (No Retur)</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                        <x-gpa.icon name="check" class="h-4 w-4 text-emerald-600" />
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-emerald-700">{{ $cleanCount }}</span>
                    <span class="text-xs font-medium text-emerald-600">Dokumen</span>
                </div>
                <p class="mt-1 text-[10px] text-ink-quiet">Muatan diterima utuh &amp; sesuai</p>
            </div>

            {{-- 3. Ada Catatan Retur / Selisih --}}
            <div class="flex flex-col justify-between rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">Catatan Retur Mutu</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-warning-soft text-warning-deep">
                        <x-gpa.icon name="alert-triangle" class="h-4 w-4 text-warning-deep" />
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-warning-deep">{{ $discrepancyCount }}</span>
                    <span class="text-xs font-medium text-warning-deep">Kasus</span>
                </div>
                <p class="mt-1 text-[10px] text-ink-quiet">Total {{ number_format($totalReturnKg, 1, ',', '.') }} kg susut/sortir</p>
            </div>

            {{-- 4. Armada Dalam Pengiriman --}}
            <div class="flex flex-col justify-between rounded-xl border border-line-hair bg-surface p-4 shadow-card">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-body">Dalam Pengiriman</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <x-gpa.icon name="truck" class="h-4 w-4 text-blue-600" />
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-1.5">
                    <span class="font-sans text-2xl font-black text-blue-700">{{ $inTransitCount }}</span>
                    <span class="text-xs font-medium text-blue-600">Armada Menuju Dock</span>
                </div>
                <p class="mt-1 text-[10px] text-ink-quiet">{{ $readyCount }} pesanan siap rilis jalan</p>
            </div>
        </section>

        {{-- Bar Filter & Pencarian --}}
        <section class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-card">
            <form method="GET" action="{{ route('sekretaris.pod.index') }}" class="flex flex-wrap items-center justify-between gap-3">
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
                        <a href="{{ route('sekretaris.pod.index') }}"
                            class="inline-flex h-9 items-center rounded-lg border border-line-soft bg-surface px-3 text-xs font-semibold text-ink-body hover:bg-surface-muted">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Monitoring Data PoD --}}
        <section class="overflow-hidden rounded-xl border border-line-hair bg-surface shadow-card">
            <header class="flex items-center justify-between border-b border-line-hair bg-surface-shell px-4 py-3 sm:px-6">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="shield" class="h-4 w-4 text-success-deep" />
                    <h2 class="font-sans text-xs font-bold uppercase tracking-wider text-ink">
                        Daftar Bukti Serah Terima &amp; Verifikasi Dock ({{ $orders->total() }} Data)
                    </h2>
                </div>
                <span class="text-[10px] text-ink-quiet">Sinkronisasi Data Armada Mobile</span>
            </header>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[64rem] border-collapse text-left text-xs">
                    <thead>
                        <tr class="border-b border-line-hair bg-surface-muted text-[11px] font-bold uppercase tracking-wider text-ink-body">
                            <th scope="col" class="px-4 py-3">No. SJ &amp; Order</th>
                            <th scope="col" class="px-4 py-3">Klien &amp; Titik Bongkar</th>
                            <th scope="col" class="px-4 py-3">Supir &amp; Plat Armada</th>
                            <th scope="col" class="px-4 py-3">PIC Penerima &amp; Waktu Tiba</th>
                            <th scope="col" class="px-4 py-3">Kondisi &amp; Catatan Retur</th>
                            <th scope="col" class="px-4 py-3 text-center">Foto Bukti (PoD)</th>
                            <th scope="col" class="px-4 py-3 text-center">Aksi Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair/70">
                        @forelse ($orders as $order)
                            @php
                                $sjNum = $order->surat_jalan_number ?: ('SJ-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
                                $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Klien GPA');
                                $driverName = $order->driver ? $order->driver->name : 'Belum Ditugaskan';
                                $plateNumber = $order->vehicle_plate_number ?: '—';
                                $receiver = $order->received_by_name ?: 'Staf Receiving Klien';
                                $arrivalTime = $order->arrival_time ? $order->arrival_time->format('d M Y, H:i') . ' WIB' : ($order->status === 'SELESAI' ? 'Telah diverifikasi' : 'Dalam Perjalanan');
                                
                                $isClean = ($order->status === 'SELESAI');
                                $isDiscrepancy = ($order->status === 'SELESAI_CATATAN');
                                $isInTransit = ($order->status === 'DALAM_PENGIRIMAN');
                                $isReady = ($order->status === 'SIAP_KIRIM');

                                $podUrl = $order->pod_photo_asset_url;
                                
                                $returItems = $order->orderItems->filter(fn($it) => (float)($it->returned_weight ?? 0) > 0);
                            @endphp
                            <tr class="transition-colors hover:bg-surface-shell/60">
                                {{-- 1. No. SJ & Order --}}
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-mono text-xs font-bold text-ink">{{ $sjNum }}</span>
                                        <span class="text-[10px] font-semibold text-ink-quiet">PO: {{ $order->order_number }}</span>
                                        <div>
                                            @if ($isClean)
                                                <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[9px] font-bold uppercase text-emerald-800 ring-1 ring-inset ring-emerald-600/30">
                                                    <x-gpa.icon name="check" class="h-2.5 w-2.5 text-emerald-600" />
                                                    Diterima Bersih
                                                </span>
                                            @elseif ($isDiscrepancy)
                                                <span class="inline-flex items-center gap-1 rounded bg-warning-soft px-2 py-0.5 text-[9px] font-bold uppercase text-warning-deep ring-1 ring-inset ring-warning/30">
                                                    <x-gpa.icon name="alert-triangle" class="h-2.5 w-2.5 text-warning-deep" />
                                                    Ada Catatan Retur
                                                </span>
                                            @elseif ($isInTransit)
                                                <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-[9px] font-bold uppercase text-blue-700 ring-1 ring-inset ring-blue-300">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                                    Dalam Pengiriman
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded bg-surface-pill px-2 py-0.5 text-[9px] font-bold uppercase text-ink-body ring-1 ring-inset ring-line-board">
                                                    {{ $order->status }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Klien & Titik Bongkar --}}
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex flex-col gap-0.5 max-w-xs">
                                        <span class="font-bold text-ink">{{ $clientName }}</span>
                                        <span class="text-[11px] text-ink-body line-clamp-2 leading-relaxed">
                                            {{ $order->delivery_address ?: 'Gudang Utama Klien' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 3. Supir & Plat Armada --}}
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-semibold text-ink">{{ $driverName }}</span>
                                        <span class="font-mono text-[11px] font-bold text-ink-quiet bg-surface-pill px-1.5 py-0.5 rounded w-fit">
                                            {{ $plateNumber }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 4. PIC Penerima & Waktu Tiba --}}
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-ink">{{ $receiver }}</span>
                                        <span class="text-[11px] text-ink-body">
                                            {{ $arrivalTime }}
                                        </span>
                                    </div>
                                </td>

                                {{-- 5. Kondisi & Catatan Retur --}}
                                <td class="px-4 py-3.5 align-top">
                                    @if ($isDiscrepancy && $returItems->isNotEmpty())
                                        <div class="flex flex-col gap-1 rounded-lg border border-warning/30 bg-warning-wash p-2 text-[10px] text-ink">
                                            <span class="font-bold text-warning-deep">Item Disortir / Diretur:</span>
                                            @foreach ($returItems as $ri)
                                                <div class="flex justify-between gap-2 border-t border-warning/20 pt-1">
                                                    <span>{{ $ri->product?->name ?? 'Komoditas' }}:</span>
                                                    <span class="font-bold text-danger">{{ number_format($ri->returned_weight, 1, ',', '.') }} kg</span>
                                                </div>
                                                @if ($ri->return_reason)
                                                    <span class="italic text-ink-quiet">"{{ $ri->return_reason }}"</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif ($isClean)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-800">
                                            <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 text-emerald-600" />
                                            Muatan Utuh &amp; Sesuai Bobot Tera
                                        </span>
                                    @else
                                        <span class="text-[11px] text-ink-quiet">Menunggu serah terima dock</span>
                                    @endif
                                </td>

                                {{-- 6. Foto Bukti (PoD) --}}
                                <td class="px-4 py-3.5 align-top text-center">
                                    @if ($podUrl)
                                        <div class="flex flex-col items-center gap-1.5">
                                            <button type="button"
                                                @click="openPhoto('{{ $podUrl }}', '{{ $order->order_number }}', '{{ $sjNum }}', '{{ $receiver }}', '{{ $arrivalTime }}')"
                                                class="group relative block h-14 w-20 overflow-hidden rounded-lg border border-line-soft bg-black shadow-xs transition-transform hover:scale-105">
                                                <img src="{{ $podUrl }}" alt="Bukti PoD"
                                                    class="h-full w-full object-cover transition-opacity group-hover:opacity-80"
                                                    onerror="this.onerror=null;this.style.opacity='0.4';">
                                                <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                                    <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-accent" />
                                                </div>
                                            </button>
                                            <span class="text-[9px] font-bold text-success-deep uppercase">Foto Fisik Valid</span>
                                        </div>
                                    @else
                                        <span class="rounded bg-surface-pill px-2 py-1 text-[10px] text-ink-quiet">
                                            Belum Ada Foto
                                        </span>
                                    @endif
                                </td>

                                {{-- 7. Aksi Dokumen --}}
                                <td class="px-4 py-3.5 align-top text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @if ($podUrl)
                                            <button type="button"
                                                @click="openPhoto('{{ $podUrl }}', '{{ $order->order_number }}', '{{ $sjNum }}', '{{ $receiver }}', '{{ $arrivalTime }}')"
                                                title="Lihat Pratinjau Foto PoD"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-line-soft bg-surface text-ink shadow-xs transition-colors hover:bg-accent hover:border-accent-edge">
                                                <x-gpa.icon name="eye" class="h-3.5 w-3.5" />
                                            </button>
                                        @endif
                                        <a href="{{ route('prints.surat-jalan', ['order_id' => $order->id]) }}" target="_blank"
                                            title="Cetak Surat Jalan Resmi"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-line-soft bg-surface text-ink shadow-xs transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-ink-quiet">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="truck" class="h-8 w-8 text-ink-quiet" />
                                        <p class="font-sans text-sm font-bold text-ink">Tidak Ada Data Pengiriman yang Cocok</p>
                                        <p class="text-xs text-ink-body">Coba ubah kata kunci pencarian atau reset filter status pengantaran di atas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($orders->hasPages())
                <div class="border-t border-line-hair bg-surface px-4 py-3 sm:px-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </section>

        {{-- Modal Pratinjau Foto PoD --}}
        <div x-cloak x-show="photoModalOpen"
            @keydown.escape.window="photoModalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-ink/75 backdrop-blur-xs transition-opacity"
                @click="photoModalOpen = false"></div>

            <div class="relative flex w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-surface shadow-pop ring-1 ring-ink/10"
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
                                Berkas Fisik Bukti Serah Terima (Proof of Delivery)
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

                {{-- Modal Body Image Container --}}
                <div class="flex max-h-[70vh] items-center justify-center overflow-auto bg-black p-4">
                    <img :src="activePhotoUrl" alt="Foto Fisik Bukti Terima"
                        class="max-h-[65vh] w-auto max-w-full rounded-lg object-contain shadow-2xl">
                </div>

                {{-- Modal Footer Metadata & Aksi --}}
                <div class="flex flex-col gap-2 border-t border-line-hair bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col text-xs text-ink-body">
                        <span>Penerima di Lokasi: <strong class="text-ink" x-text="activeReceiver"></strong></span>
                        <span>Waktu Serah Terima: <strong class="text-ink" x-text="activeTime"></strong></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="activePhotoUrl" target="_blank"
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

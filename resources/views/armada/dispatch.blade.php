@extends('layouts.armada')

@section('title', 'Surat Jalan Digital & Rincian Muatan')

@section('content')
    @php
        $isStarted = $currentOrder && in_array($currentOrder->status, ['DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']);

        $gatePass = [
            'expiry_minutes'      => 45,
            'is_started'          => $isStarted,
            'pic_phone'           => $currentOrder?->user?->phone ?: '—',
            'destination_address' => $currentOrder?->delivery_address ?: '',
        ];

        $sjNumber = $currentOrder?->surat_jalan_number
            ?: ($currentOrder ? ('SJ-' . date('Ymd', strtotime($currentOrder->created_at ?: now())) . '-' . str_pad($currentOrder->id, 4, '0', STR_PAD_LEFT)) : '—');

        $poNumber = $currentOrder?->order_number ?: '—';

        $dispatchDate = $currentOrder?->departure_time ? $currentOrder->departure_time->format('d M Y') : date('d M Y');
        $dispatchTime = $currentOrder?->departure_time ? $currentOrder->departure_time->format('H:i \W\I\B') : '—';

        $totalWeight = 0;
        $totalCrates = 0;
        $totalTare = 0;

        if ($currentOrder && $currentOrder->orderItems->isNotEmpty()) {
            foreach ($currentOrder->orderItems as $item) {
                $orderedKg = (float) $item->ordered_qty;
                $crates = (int) ceil($orderedKg / 20);
                $tare = (float) ($crates * 2.0);

                $totalWeight += $orderedKg;
                $totalCrates += $crates;
                $totalTare += $tare;
            }
        }
    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-4 px-4 pb-24"
        x-data="armadaDispatch(@js($gatePass))">

        {{-- Judul halaman --}}
        <section class="no-print flex flex-col gap-1 rounded-2xl bg-surface p-4 shadow-card">
            <h1 class="text-xl font-bold leading-7 text-ink">Surat Jalan Digital & Rincian<br>Muatan Supir</h1>
            <p class="text-xs text-ink-body">Verifikasi kesesuaian fisik muatan & segel timbangan tera sebelum keberangkatan.</p>
        </section>

        {{-- Rute pengantaran --}}
        <section class="no-print flex flex-col gap-2 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-shell pb-2">
                <p class="font-mono text-[10px] font-bold text-ink-body uppercase tracking-wider">RUTE PENGANTARAN ({{ $activeOrders->count() }} TITIK)</p>
                <p class="font-mono text-[10px] font-bold text-success-deep uppercase tracking-wider">MULTI-DROP HARIAN</p>
            </header>

            <div class="flex w-full items-stretch gap-2 overflow-x-auto pt-1">
                @foreach ($activeOrders as $index => $ord)
                    @php
                        $isActive = $currentOrder && $ord->id === $currentOrder->id;
                        $ordNetWeight = (float) $ord->orderItems->sum('ordered_qty');
                        $ordSjNum = $ord->surat_jalan_number ?: ('SJ-' . date('Ymd', strtotime($ord->created_at)) . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT));
                    @endphp
                    <div @class([
                        'relative flex min-w-[200px] flex-1 flex-col items-start gap-1 overflow-hidden p-3 rounded-xl transition-all',
                        $isActive
                            ? 'bg-surface shadow-sub outline outline-2 -outline-offset-2 outline-accent'
                            : 'bg-surface-shell opacity-80 outline outline-1 -outline-offset-1 outline-line-board',
                    ])>
                        <div class="flex items-center gap-1.5">
                            <span @class([
                                'h-2 w-2 shrink-0 rounded-full',
                                $ord->status === 'DALAM_PENGIRIMAN' ? 'bg-success-deep' : 'bg-ink-quiet',
                            ])></span>
                            <span class="font-mono text-[10px] font-bold uppercase text-ink-body">
                                @if ($ord->status === 'DALAM_PENGIRIMAN')
                                    DALAM PENGIRIMAN
                                @elseif (in_array($ord->status, ['SELESAI', 'SELESAI_CATATAN']))
                                    SELESAI TERKIRIM
                                @else
                                    SIAP KIRIM
                                @endif
                            </span>
                        </div>

                        <p class="pt-0.5 font-mono text-xs font-bold text-ink">{{ $ordSjNum }}</p>
                        <p class="text-xs font-medium text-ink truncate w-full">{{ $ord->user?->name ?? 'Klien Penerima' }}</p>

                        <div class="mt-auto flex w-full items-baseline justify-between gap-2 border-t border-line-hair pt-1.5">
                            <span class="font-mono text-[9px] text-ink-quiet">Muatan Bersih:</span>
                            <span class="font-mono text-xs font-bold text-ink">{{ number_format($ordNetWeight, 1, ',', '.') }} kg</span>
                        </div>

                        <span @class([
                            'absolute right-0 top-0 rounded-bl-lg px-2 py-0.5 font-mono text-[9px] font-bold',
                            $isActive ? 'bg-ink text-accent' : 'bg-surface-disabled text-ink-body',
                        ])>STOP #{{ $index + 1 }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Surat jalan resmi --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-start justify-between gap-3 border-b border-surface-shell pb-3">
                <div class="flex min-w-0 flex-col items-start gap-0.5">
                    <span class="rounded bg-accent px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-ink">
                        TERVALIDASI DISPATCH GUDANG
                    </span>

                    <span class="pt-1 font-mono text-base font-bold text-ink">{{ $sjNumber }}</span>

                    <span class="font-mono text-xs text-ink-body">
                        <span>Ref PO: </span><span class="font-bold text-ink">#{{ $poNumber }}</span>
                    </span>

                    <a href="{{ route('prints.surat-jalan', ['order_id' => $currentOrder?->id]) }}" target="_blank"
                        class="no-print mt-1.5 inline-flex items-center gap-1.5 rounded-lg bg-surface-shell px-2.5 py-1 font-mono text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-disabled">
                        <x-gpa.icon name="printer" class="h-3 w-3 shrink-0" />
                        CETAK SJ RESMI A4
                    </a>
                </div>

                <div class="flex shrink-0 flex-col items-end text-right">
                    <span class="font-mono text-[9px] font-semibold text-ink-quiet uppercase">WAKTU DISPATCH</span>
                    <span class="font-mono text-xs font-bold text-ink">{{ $dispatchDate }}</span>
                    <span class="font-mono text-xs font-bold text-success-deep">{{ $dispatchTime }}</span>
                </div>
            </header>

            {{-- Alur pengirim & penerima --}}
            <div class="flex flex-col gap-2 pt-1">
                <div class="flex items-start gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm bg-surface-disabled text-ink">
                        <x-gpa.icon name="building" class="h-3.5 w-3.5 shrink-0" />
                    </span>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <span class="font-mono text-[9px] font-bold uppercase text-ink-quiet">PENGIRIM LOGISTIK</span>
                        <span class="text-sm font-bold text-ink">Green Pasundan Agrikultur</span>
                        <span class="text-xs text-ink-body">Sentral Transhipment Hub - Gudang Dock 03</span>
                    </div>
                </div>

                <div class="ml-3.5 h-3 border-l-2 border-line-hair"></div>

                <div class="flex items-start gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg shadow-sm bg-accent text-ink">
                        <x-gpa.icon name="map-pin" class="h-3.5 w-3.5 shrink-0" />
                    </span>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <span class="font-mono text-[9px] font-bold uppercase text-ink-quiet">DOKUMEN PENERIMA</span>
                        <span class="text-sm font-bold text-ink">{{ $currentOrder?->user?->name ?? 'Klien Penerima Resmi' }}</span>
                        <span class="text-xs text-ink-body">{{ $currentOrder?->delivery_address ?? 'Alamat Penerimaan Klien' }}</span>
                    </div>
                </div>
            </div>

            {{-- Kontak PIC penerima --}}
            <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="user" class="h-4 w-4 shrink-0 text-ink-body" />
                        <div class="flex flex-col">
                            <span class="font-mono text-[9px] text-ink-quiet uppercase">PENANGGUNG JAWAB PENERIMA</span>
                            <span class="text-xs font-bold text-ink">{{ $currentOrder?->received_by_name ?: ($currentOrder?->user?->name ?: '—') }}</span>
                        </div>
                    </div>

                    <span class="font-mono text-xs font-bold text-success-deep">{{ $currentOrder?->user?->phone ?: '—' }}</span>
                </div>

                <div class="no-print flex items-center gap-2 pt-1">
                    <button type="button" @click="callPic('{{ $currentOrder?->user?->phone ?: '' }}')"
                        class="flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg bg-surface font-mono text-xs font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board shadow-sm hover:bg-surface-disabled">
                        <x-gpa.icon name="phone" class="h-3 w-3 shrink-0" />
                        Hubungi PIC
                    </button>

                    <button type="button" @click="openMap('{{ addslashes($currentOrder?->delivery_address ?? '') }}')"
                        class="flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg bg-ink font-mono text-xs font-bold text-accent shadow-sm hover:opacity-90">
                        <x-gpa.icon name="navigation" class="h-3 w-3 shrink-0" />
                        Navigasi Peta
                    </button>
                </div>
            </div>
        </section>

        {{-- Rincian muatan sayur --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold text-ink-body uppercase">RINCIAN MUATAN PESANAN (SURAT JALAN)</span>
                <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[9px] font-bold text-ink">TERVERIFIKASI</span>
            </header>

            <div class="flex flex-col gap-2.5">
                @if ($currentOrder && $currentOrder->orderItems->isNotEmpty())
                    @foreach ($currentOrder->orderItems as $item)
                        @php
                            $orderedKg = (float) $item->ordered_qty;
                            $crates = (int) ceil($orderedKg / 20);
                            $tare = (float) ($crates * 2.0);
                        @endphp
                        <div class="flex flex-col gap-2 rounded-xl bg-canvas p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="w-fit rounded bg-surface px-1.5 py-0.5 font-mono text-[9px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                        SKU: {{ $item->product?->sku ?: ('PROD-' . $item->product_id) }}
                                    </span>
                                    <span class="pt-1 text-sm font-bold text-ink">{{ $item->product?->name ?? 'Sayur Segar Berkualitas' }}</span>
                                    <span class="text-[10px] text-ink-body">Standar Mutu Super &bull; {{ $crates }} Krat Steril</span>
                                </div>

                                <span class="flex items-center gap-1 rounded bg-accent px-2 py-0.5 font-mono text-[9px] font-bold text-ink">
                                    <span>SIAP KIRIM</span>
                                    <x-gpa.icon name="check" class="h-2.5 w-2.5" />
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 rounded-lg bg-surface p-2 text-center outline outline-1 -outline-offset-1 outline-line-hair">
                                <div class="flex flex-col">
                                    <span class="font-mono text-[9px] text-ink-quiet">Bobot Muat</span>
                                    <span class="font-mono text-xs font-bold text-ink">{{ number_format($orderedKg, 1, ',', '.') }} kg</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-mono text-[9px] text-ink-quiet">Kemas Wadah</span>
                                    <span class="font-mono text-xs font-bold text-success-deep">{{ $crates }} Krat</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-mono text-[9px] text-ink-quiet">Estimasi Tara</span>
                                    <span class="font-mono text-xs font-bold text-ink-body">{{ number_format($tare, 1, ',', '.') }} kg</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-xs text-ink-body text-center py-4">Belum ada item muatan terdaftar.</p>
                @endif
            </div>

            {{-- Total akumulasi netto --}}
            <div class="flex flex-col gap-2.5 rounded-xl bg-brand p-3.5 text-white">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-accent">TOTAL AKUMULASI MUATAN SURAT JALAN</span>
                    <x-gpa.icon name="calculator" class="h-4 w-4 text-accent" />
                </div>

                <div class="grid grid-cols-3 gap-2 border-b border-white/20 pb-2.5 text-center">
                    <div class="flex flex-col">
                        <span class="font-mono text-[9px] text-white/70">Total Berat Bersih</span>
                        <span class="font-mono text-sm font-bold text-accent">{{ number_format($totalWeight, 1, ',', '.') }} kg</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-mono text-[9px] text-white/70">Total Kemasan</span>
                        <span class="font-mono text-sm font-bold text-white">{{ $totalCrates }} Krat</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-mono text-[9px] text-white/70">Total Berat Tara</span>
                        <span class="font-mono text-sm font-bold text-white">{{ number_format($totalTare, 1, ',', '.') }} kg</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs font-mono text-white/90">
                    <span>Bruto Muatan: {{ number_format($totalWeight + $totalTare, 1, ',', '.') }} kg</span>
                    <span class="flex items-center gap-1 text-accent">
                        <x-gpa.icon name="thermometer" class="h-3.5 w-3.5" />
                        <span>+3.8°C (Suhu Cold Chain Optimal)</span>
                    </span>
                </div>
            </div>
        </section>

        {{-- Blok Pengesahan Khusus Cetak Fisik Surat Jalan --}}
        <section class="hidden print:flex flex-col gap-4 border-t-2 border-line-board pt-6 mt-4">
            <div class="flex justify-between items-center text-center font-mono text-xs">
                <div class="w-44">
                    <p class="font-bold">Pengirim / Dispatcher</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">Petugas Gudang GPA</p>
                    <p class="text-[10px] text-ink-quiet">Sentral Hub Ciwidey</p>
                </div>
                <div class="w-44">
                    <p class="font-bold">Awak Armada / Supir</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">{{ auth()->user()?->name ?: 'Supir Pengiriman' }}</p>
                    <p class="text-[10px] text-ink-quiet">Plat: [{{ $armadaPlate ?? 'D-8921-GPA' }}]</p>
                </div>
                <div class="w-44">
                    <p class="font-bold">Penerima Barang</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline">{{ $currentOrder?->received_by_name ?: ($currentOrder?->user?->name ?: 'Klien Penerima') }}</p>
                    <p class="text-[10px] text-ink-quiet">Tanda Tangan & Cap Basah</p>
                </div>
            </div>
        </section>

        {{-- Gate pass keluar dock & Mulai Pengiriman --}}
        <section class="no-print flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card outline outline-2 -outline-offset-2 outline-ink">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2.5">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="shield" class="h-4 w-4 text-ink" />
                    <span class="font-mono text-xs font-bold text-ink">GATE PASS KELUAR GUDANG</span>
                </div>

                <span class="rounded bg-accent px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                    BERLAKU: <span x-text="expired() ? 'KEDALUWARSA' : expiryLabel()">45:00</span>
                </span>
            </header>

            <div class="flex flex-col items-center gap-1 rounded-xl bg-canvas p-4 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-ink text-accent">
                    <x-gpa.icon name="qrcode" class="h-12 w-12" />
                </div>
                <p class="pt-2 font-mono text-xs font-bold text-ink">KODE GERBANG: GPA-{{ $currentOrder?->id ?? '1' }}-SEC</p>
                <p class="text-[10px] text-ink-body">Tunjukkan ke pos keamanan gerbang hub utama &amp; dok penerima.</p>
            </div>

            <button type="button" @click="verifySeal()"
                class="flex items-center justify-between rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                <div class="flex items-center gap-2.5">
                    <x-gpa.icon name="lock" class="h-4 w-4 text-success-deep" />
                    <div class="flex flex-col text-left">
                        <span class="font-mono text-[9px] text-ink-quiet uppercase">STATUS SEGEL BOKS KENDARAAN</span>
                        <span class="text-xs font-bold text-ink">Segel Digital Aktif (Terkunci Otomatis)</span>
                    </div>
                </div>
                <span class="h-2.5 w-2.5 rounded-full bg-success-deep"></span>
            </button>

            @if ($currentOrder)
                <form id="start-route-form" method="POST" action="{{ route('armada.dispatch.start', $currentOrder->id) }}" class="hidden">
                    @csrf
                </form>
            @endif

            <div class="flex flex-col gap-2 pt-1">
                @if ($isStarted)
                    <div class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-accent font-mono text-sm font-bold text-ink shadow-sub">
                        <x-gpa.icon name="truck" class="h-4 w-4 text-ink" />
                        PENGIRIMAN SEDANG BERJALAN
                    </div>
                    @if ($currentOrder)
                        <a href="{{ route('armada.pod', ['order_id' => $currentOrder->id]) }}"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-ink font-mono text-xs font-bold text-accent shadow-sub hover:opacity-90">
                            <x-gpa.icon name="qrcode" class="h-4 w-4" />
                            MENUJU PENYERAHAN & SCAN POD
                        </a>
                    @endif
                @else
                    <button type="button" @click="startRoute()"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-ink font-mono text-sm font-bold text-accent shadow-pop transition-transform active:scale-[0.98]">
                        <span>MULAI PENGIRIMAN</span>
                        <x-gpa.icon name="arrow-right" class="h-4 w-4" />
                    </button>
                @endif

                <p class="text-center font-mono text-[9px] text-ink-body">Status GPS Logbook & Pencatat Suhu Reefer Aktif Terkoneksi — Status: Dalam Pengiriman</p>

                <button type="button" @click="reportIssue()"
                    class="flex h-9 w-full items-center justify-center gap-1.5 rounded-xl bg-surface font-mono text-xs font-bold text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border hover:bg-warning-cream/30">
                    <x-gpa.icon name="alert-triangle" class="h-3.5 w-3.5" />
                    Lapor Kendala / Keterlambatan
                </button>
            </div>
        </section>
    </div>
@endsection

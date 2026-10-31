@extends('layouts.armada')

@section('title', 'Daftar Tugas Pengantaran Armada')

@section('content')
    <div class="mx-auto flex w-full max-w-[480px] flex-col gap-4 px-4 pb-24"
        x-data="armadaTasks([
            { key: 'all', label: 'SEMUA', count: {{ $allCount }} },
            { key: 'ready', label: 'SIAP', count: {{ $readyCount }} },
            { key: 'road', label: 'JALAN', count: {{ $roadCount }} },
            { key: 'done', label: 'SELESAI', count: {{ $doneCount }} }
        ])">

        {{-- Identitas supir bertugas --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-start justify-between gap-3 border-b border-surface-shell pb-3">
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    <p class="gpa-micro-bold text-ink-quiet uppercase tracking-wider text-[10px]">SUPIR BERTUGAS</p>

                    <p class="flex items-center gap-2">
                        <span class="text-base font-bold text-ink">{{ auth()->user()?->name ?? 'Supir Bertugas' }}</span>
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-deep" title="Status Aktif"></span>
                    </p>

                    <p class="gpa-meta text-xs text-ink-body">
                        DVR-GPA-{{ str_pad(auth()->user()?->id ?? 1, 2, '0', STR_PAD_LEFT) }} &bull; Unit CDD Reefer
                    </p>
                </div>

                <div class="flex flex-col items-end gap-1">
                    <span class="flex flex-col items-end rounded-lg bg-accent px-3 py-1 shadow-sub">
                        <span class="font-mono text-xs font-extrabold uppercase leading-4 text-ink">{{ $roadCount > 0 ? 'DALAM' : 'SIAP' }}</span>
                        <span class="font-mono text-xs font-extrabold uppercase leading-4 text-ink">{{ $roadCount > 0 ? 'PENGIRIMAN' : 'PENGANTARAN' }}</span>
                    </span>

                    <span class="font-mono text-[10px] font-semibold text-warning-deep">
                        TARGET: 08:30 WIB
                    </span>
                </div>
            </header>
        </section>

        {{-- Filter status antrean --}}
        <section class="flex items-center justify-between gap-1.5 overflow-hidden border-b border-line-hair pb-3">
            <button type="button" @click="selectFilter('all')"
                :aria-pressed="filter === 'all' ? 'true' : 'false'"
                :class="filter === 'all'
                    ? 'bg-accent text-ink shadow-sub font-bold outline outline-1 -outline-offset-1 outline-success-deep'
                    : 'bg-surface text-ink-body font-medium outline outline-1 -outline-offset-1 outline-line-board hover:text-ink'"
                class="h-9 flex-1 rounded-xl font-mono text-[11px] uppercase transition-all">
                SEMUA ({{ $allCount }})
            </button>
            <button type="button" @click="selectFilter('ready')"
                :aria-pressed="filter === 'ready' ? 'true' : 'false'"
                :class="filter === 'ready'
                    ? 'bg-accent text-ink shadow-sub font-bold outline outline-1 -outline-offset-1 outline-success-deep'
                    : 'bg-surface text-ink-body font-medium outline outline-1 -outline-offset-1 outline-line-board hover:text-ink'"
                class="h-9 flex-1 rounded-xl font-mono text-[11px] uppercase transition-all">
                SIAP ({{ $readyCount }})
            </button>
            <button type="button" @click="selectFilter('road')"
                :aria-pressed="filter === 'road' ? 'true' : 'false'"
                :class="filter === 'road'
                    ? 'bg-accent text-ink shadow-sub font-bold outline outline-1 -outline-offset-1 outline-success-deep'
                    : 'bg-surface text-ink-body font-medium outline outline-1 -outline-offset-1 outline-line-board hover:text-ink'"
                class="h-9 flex-1 rounded-xl font-mono text-[11px] uppercase transition-all">
                JALAN ({{ $roadCount }})
            </button>
            <button type="button" @click="selectFilter('done')"
                :aria-pressed="filter === 'done' ? 'true' : 'false'"
                :class="filter === 'done'
                    ? 'bg-accent text-ink shadow-sub font-bold outline outline-1 -outline-offset-1 outline-success-deep'
                    : 'bg-surface text-ink-body font-medium outline outline-1 -outline-offset-1 outline-line-board hover:text-ink'"
                class="h-9 flex-1 rounded-xl font-mono text-[11px] uppercase transition-all">
                SELESAI ({{ $doneCount }})
            </button>
        </section>

        {{-- Judul antrean --}}
        <section class="flex items-center justify-between gap-2 pt-1">
            <h2 class="text-base font-bold text-ink">Antrean Titik Pengantaran</h2>
            <span class="rounded bg-surface-disabled px-2 py-0.5 font-mono text-[10px] font-semibold text-ink-body">
                Total: {{ $orders->count() }} Titik
            </span>
        </section>

        {{-- Kartu titik pengantaran --}}
        <div class="flex flex-col gap-3.5">
            @forelse ($orders as $index => $order)
                @php
                    $state = match ($order->status) {
                        'DALAM_PENGIRIMAN'            => 'road',
                        'SELESAI', 'SELESAI_CATATAN' => 'done',
                        default                       => 'ready',
                    };

                    $stateLabel = match ($order->status) {
                        'DALAM_PENGIRIMAN'            => 'ON ROAD',
                        'SELESAI', 'SELESAI_CATATAN' => 'SELESAI',
                        default                       => 'SIAP KIRIM',
                    };

                    $isDone = in_array($order->status, ['SELESAI', 'SELESAI_CATATAN']);

                    $timeVal = $order->arrival_time
                        ? $order->arrival_time->format('H:i \W\I\B')
                        : ($order->departure_time ? $order->departure_time->format('H:i \W\I\B') : '07:30 WIB');

                    $sjNum = $order->surat_jalan_number
                        ?: ('SJ-' . date('Ymd', strtotime($order->created_at)) . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));

                    $orderNetWeight = 0;
                    foreach ($order->orderItems as $item) {
                        $orderNetWeight += (float) ($item->net_weight ?? $item->quantity ?? 0);
                    }
                @endphp
                <article @class([
                    'relative flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card transition-all',
                    'outline outline-2 -outline-offset-2 outline-accent' => ! $isDone,
                    'opacity-80' => $isDone,
                ]) x-show="filter === 'all' || filter === '{{ $state }}'">

                    <header class="flex items-start justify-between gap-2 border-b border-surface-shell pb-2.5">
                        <div class="flex items-center gap-1.5">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase {{ $state === 'road' ? 'bg-ink text-accent' : 'bg-surface-disabled text-ink-body' }}">
                                DROP KE-{{ $index + 1 }}
                            </span>

                            <span @class([
                                'rounded px-2 py-0.5 font-mono text-[10px] font-bold uppercase',
                                'bg-accent text-ink' => $state === 'road',
                                'bg-accent/40 text-ink' => $state === 'ready',
                                'bg-surface-disabled text-ink-body' => $state === 'done',
                                'bg-surface-track text-ink' => !in_array($state, ['road', 'ready', 'done'], true),
                            ])>
                                {{ $stateLabel }}
                            </span>
                        </div>

                        <div class="flex flex-col items-end gap-0.5">
                            <p class="font-mono text-xs font-bold {{ $state === 'road' ? 'text-warning-deep' : 'text-ink-body' }}">
                                {{ $isDone ? 'Tiba' : 'Target' }}: {{ $timeVal }}
                            </p>
                            <p class="font-mono text-[9px] font-semibold text-ink-quiet">
                                {{ $state === 'road' ? 'PRIORITAS UTAMA' : 'STANDAR' }}
                            </p>
                        </div>
                    </header>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-[10px] font-semibold text-ink-quiet">NO. SURAT JALAN:</span>
                            <span class="rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                {{ $sjNum }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-ink">{{ $order->user?->name ?? 'Klien Penerima' }}</h3>

                        <p class="flex items-start gap-1.5 text-xs text-ink-body">
                            <x-gpa.icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-accent-deep" />
                            <span>{{ $order->delivery_address ?? 'Gudang Penerima Klien' }}</span>
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-1.5">
                            <span class="font-mono text-[10px] font-bold text-ink-body">RINCIAN MUATAN ({{ $order->orderItems->count() }} KOMODITAS)</span>
                            <span class="font-mono text-[10px] font-bold text-ink">NETTO: {{ number_format($orderNetWeight, 1, ',', '.') }} KG</span>
                        </div>

                        <div class="flex flex-col gap-1">
                            @foreach ($order->orderItems as $item)
                                @php
                                    $itemKg = (float) ($item->net_weight ?? $item->quantity ?? 0);
                                @endphp
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="text-ink">{{ $item->product?->name ?? 'Komoditas Sayur' }}</span>
                                    <span class="font-mono font-bold text-ink">{{ number_format($itemKg, 1, ',', '.') }} kg</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between gap-2 border-t border-line-hair pt-1.5">
                            <span class="font-mono text-[10px] text-ink-body">STATUS PENGIRIMAN:</span>
                            <span class="text-right font-mono text-[10px] font-semibold text-ink">
                                @if ($order->status === 'DALAM_PENGIRIMAN')
                                    Armada sedang menuju lokasi bongkar muat
                                @elseif ($order->status === 'SELESAI')
                                    Barang telah diterima utuh di lokasi
                                @elseif ($order->status === 'SELESAI_CATATAN')
                                    Barang telah diterima dengan catatan retur
                                @else
                                    Menunggu keberangkatan dari gudang sentral
                                @endif
                            </span>
                        </div>
                    </div>

                    <footer class="flex flex-col gap-2 pt-1">
                        @if ($state === 'road')
                            <a href="{{ route('armada.bukti-terima', ['order_id' => $order->id]) }}"
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-accent font-mono text-xs font-bold uppercase tracking-wider text-ink shadow-sub transition-transform active:scale-[0.98]">
                                <x-gpa.icon name="qrcode" class="h-4 w-4 shrink-0" />
                                PROSES SCAN POD (SELESAIKAN)
                            </a>
                        @elseif ($state === 'ready')
                            <a href="{{ route('armada.surat-jalan', ['order_id' => $order->id]) }}"
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-ink font-mono text-xs font-bold uppercase tracking-wider text-accent shadow-sub transition-transform active:scale-[0.98]">
                                <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0" />
                                LIHAT SURAT JALAN &amp; BERANGKAT
                            </a>
                        @else
                            <div class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-surface-disabled font-mono text-xs font-bold text-ink-quiet">
                                <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                                PENGANTARAN SELESAI
                            </div>
                        @endif

                        <button type="button" @click="checkRoute({ address: '{{ addslashes($order->delivery_address ?? '') }}', client: '{{ addslashes($order->user?->name ?? '') }}' })"
                            class="flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-surface font-mono text-xs font-bold text-ink outline outline-1 -outline-offset-1 outline-line-strong transition-colors hover:bg-surface-disabled">
                            <x-gpa.icon name="map-pin" class="h-3.5 w-3.5 shrink-0 text-ink" />
                            BUKA NAVIGASI PETA LOKASI
                        </button>
                    </footer>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-surface p-8 text-center shadow-card">
                    <x-gpa.icon name="package" class="h-10 w-10 text-ink-quiet" />
                    <p class="text-sm font-bold text-ink">Belum Ada Tugas Pengantaran</p>
                    <p class="text-xs text-ink-body">Semua pesanan telah diselesaikan atau menunggu jadwal dispatch berikutnya.</p>
                </div>
            @endforelse
        </div>

        {{-- Bantuan darurat dispatch --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-surface-disabled text-ink">
                        <x-gpa.icon name="headset" class="h-4 w-4 shrink-0" />
                    </span>

                    <div class="flex flex-col">
                        <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">BANTUAN OPERASIONAL DISPATCH</span>
                        <span class="font-mono text-xs font-bold text-ink">(021) 890-1234</span>
                    </div>
                </div>

                <a href="tel:0218901234"
                    class="flex h-9 items-center gap-1.5 rounded-lg bg-ink px-3.5 font-mono text-xs font-bold text-white shadow-sub transition-transform active:scale-95">
                    <x-gpa.icon name="phone" class="h-3 w-3 shrink-0" />
                    HUBUNGI
                </a>
            </header>
        </section>
    </div>
@endsection

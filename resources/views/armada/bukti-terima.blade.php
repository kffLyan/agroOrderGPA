@extends('layouts.armada')

@section('title', 'Scan PoD // Bukti Terima Pengantaran')

@section('content')
    @php
        $totalNetto = 0;
        if ($currentOrder) {
            foreach ($currentOrder->orderItems as $item) {
                $totalNetto += (float) ($item->ordered_qty ?: 0);
            }
        }

        $sjNum = $currentOrder?->surat_jalan_number
            ?: ($currentOrder ? ('SJ-' . date('Ymd', strtotime($currentOrder->created_at ?: now())) . '-' . str_pad($currentOrder->id, 4, '0', STR_PAD_LEFT)) : '—');

        $picName = $currentOrder?->received_by_name ?: ($currentOrder?->user?->name ?: '—');

        $statusText = match ($currentOrder?->status) {
            'SELESAI', 'SELESAI_CATATAN' => 'DOKUMEN SELESAI',
            'DALAM_PENGIRIMAN'           => 'DOKUMEN AKTIF BERJALAN',
            default                      => 'SIAP PENYERAHAN',
        };

    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-4 px-3 pb-24"
        x-data="armadaPod()">

        {{-- Target surat jalan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between gap-2 border-b border-surface-shell pb-2.5">
                <p class="gpa-meta font-bold text-ink">TARGET PENYERAHAN #{{ $sjNum }}</p>
                <span class="rounded bg-accent px-2 py-0.5 text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                    {{ $statusText }}
                </span>
            </header>

            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">KLIEN PENERIMA</p>
                    <p class="text-xs font-bold text-ink">{{ $currentOrder?->user?->name ?? 'Klien Penerima Resmi' }}</p>
                    <p class="gpa-note text-[11px] text-ink-body">Green Pasundan Agrikultur B2B</p>
                </div>

                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">TITIK BONGKAR MUAT</p>
                    <p class="text-xs font-bold text-ink">{{ $currentOrder?->delivery_address ?? 'Gudang Penerima Klien' }}</p>
                    <p class="gpa-note text-[11px] text-ink-body">Area Penerimaan Barang</p>
                </div>

                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">TOTAL MUATAN RESMI</p>
                    <p class="flex items-baseline gap-1">
                        <span class="font-inter text-xl font-bold text-ink">{{ number_format($totalNetto, 1, ',', '.') }}</span>
                        <span class="font-mono text-xs font-bold text-success-deep">KG Bersih</span>
                    </p>
                    <p class="gpa-note text-[11px] text-ink-body">Sayuran Segar &amp; Hortikultura Berkualitas</p>
                </div>

                <div class="flex flex-col items-start gap-1 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <p class="gpa-micro-bold text-ink-quiet text-[10px]">PIC PENERIMA RESMI</p>
                    <p class="text-xs font-bold text-ink">{{ $picName }}</p>
                    <span class="rounded bg-brand px-2 py-0.5 text-[9px] font-semibold text-accent">
                        Penerima Barang / Kepala Gudang
                    </span>
                </div>
            </div>
        </section>

        {{-- Status pengiriman --}}
        <section class="flex flex-col gap-2 rounded-2xl bg-accent/15 p-3.5 outline outline-1 -outline-offset-1 outline-accent/40">
            <header class="flex items-center gap-2">
                <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-accent-deep" />
                <h2 class="text-xs font-bold text-ink uppercase tracking-wider">STATUS PENGIRIMAN</h2>
            </header>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 text-[10px] font-bold text-ink shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-success-deep animate-pulse" aria-hidden="true"></span>
                    {{ $statusText }}
                </span>
                <span class="text-xs font-medium text-ink-body">Lokasi: {{ $currentOrder?->delivery_address ?? 'Gudang Penerima Klien' }}</span>
            </div>
        </section>

        {{-- Form submission asli untuk PoD --}}
        @if ($currentOrder)
            <form id="pod-submit-form" method="POST" action="{{ route('armada.bukti-terima.store', $currentOrder->id) }}" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="hidden" name="condition" id="hidden-condition" x-model="condition">
                <input type="hidden" name="received_by_name" value="{{ $picName }}">
                <input type="file" name="manifest_photo" id="photo-input-manifest" accept="image/*" @change="handlePhotoUpload($event, 'manifest')">
                <input type="file" name="dock_photo" id="photo-input-dock" accept="image/*" @change="handlePhotoUpload($event, 'dock')">
            </form>
        @endif

        {{-- Serah terima di dock checklist --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center gap-2 border-b border-surface-shell pb-2">
                <h2 class="text-base font-bold text-ink">CHECKLIST SERAH TERIMA DI LOKASI</h2>
            </header>

            <div class="flex flex-col gap-2">
                <p class="text-xs text-ink-body">Pastikan poin-poin berikut telah dicocokkan bersama perwakilan penerima:</p>

                <div class="flex flex-col gap-2 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'PIC penerima hadir di lokasi', 'info')">
                        <span class="text-xs leading-4 text-ink">PIC penerima hadir dengan identitas &amp; penugasan jelas</span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'Cap basah stempel &amp; tanda tangan', 'info')">
                        <span class="text-xs leading-4 text-ink">Cap basah stempel penerima &amp; paraf penanggung jawab</span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'Muatan diserahkan utuh', 'info')">
                        <span class="text-xs leading-4 text-ink">Muatan sayur segar diserahkan utuh sesuai kemasan krat</span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Serah Terima', 'Berat timbangan sesuai toleransi', 'info')">
                        <span class="text-xs leading-4 text-ink">Timbangan cocok sesuai toleransi susut metrologi (&le; 2%)</span>
                    </label>
                </div>
            </div>
        </section>

        {{-- LANGKAH 1: Foto Fisik Surat Jalan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card scroll-mt-16" id="pod-step-manifest" x-ref="step-manifest">
            <header class="flex items-center justify-between gap-3 border-b border-surface-shell pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-ink text-white font-mono text-xs font-bold">1</span>
                    <h2 class="text-base font-bold text-ink">Foto Fisik Surat Jalan</h2>
                </div>
                <span class="shrink-0 rounded px-2 py-0.5 text-[9px] font-bold bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                    WAJIB CAP STEMPEL
                </span>
            </header>

            <div class="flex min-h-[200px] flex-col justify-between gap-3 overflow-hidden rounded-xl bg-brand p-3 text-white outline outline-2 -outline-offset-2 outline-accent">
                <template x-if="previews.manifest">
                    <div class="relative flex h-48 w-full items-center justify-center overflow-hidden rounded-lg bg-black">
                        <img :src="previews.manifest" alt="Surat Jalan" class="h-full w-full object-contain">
                        <span class="absolute top-2 right-2 rounded bg-success-deep px-2 py-0.5 text-[10px] font-bold text-white">TERSIMPAN</span>
                    </div>
                </template>

                <div x-show="!previews.manifest" class="flex flex-1 flex-col items-center justify-center gap-2 py-4">
                    <x-gpa.icon name="camera" class="h-10 w-10 text-accent" />
                    <p class="text-center font-bold text-xs text-white">Arahkan kamera ke lembar fisik surat jalan</p>
                    <p class="text-center text-[10px] text-accent">Pastikan stempel penerima &amp; tanda tangan terlihat jelas</p>
                </div>

                <div class="flex items-center justify-between text-[10px] font-mono">
                    <span class="rounded bg-black/40 px-2 py-0.5 text-accent">KAMERA RATIO 3:4 FOKUS JELAS</span>
                    <span class="rounded bg-black/40 px-2 py-0.5" x-text="manifestCaptured() ? 'STATUS: FOTO SIAP' : 'STATUS: MENUNGGU FOTO'"></span>
                </div>
            </div>

            <button type="button" @click="capture('manifest')"
                :class="manifestCaptured()
                    ? 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep font-bold'
                    : 'bg-ink text-white font-bold'"
                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl text-xs uppercase tracking-wider shadow-sub transition-transform active:scale-[0.98]">
                <x-gpa.icon name="camera" class="h-4 w-4 shrink-0" />
                <span x-text="manifestCaptured() ? 'Ganti Foto Surat Jalan' : 'Ambil Foto Surat Jalan Bertanda Tangan'"></span>
            </button>

            <div class="flex flex-col gap-1.5 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                <p class="text-[10px] font-bold uppercase text-ink-quiet">CHECKLIST VERIFIKASI DOKUMEN:</p>
                <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                    <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Verifikasi', 'Nomor Surat Jalan &amp; nomor PO cocok', 'info')">
                    <span>Nomor Surat Jalan &amp; nomor PO cocok</span>
                </label>
                <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                    <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Verifikasi', 'Stempel resmi &amp; tanda tangan basah penerima', 'info')">
                    <span>Stempel resmi &amp; tanda tangan basah penerima</span>
                </label>
                <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                    <input type="checkbox" class="gpa-check mt-0.5" checked @change="run('Verifikasi', 'Jumlah krat &amp; berat netto sesuai fisik', 'info')">
                    <span>Jumlah krat &amp; berat netto sesuai fisik</span>
                </label>
            </div>
        </section>

        {{-- LANGKAH 2: Foto Titik Bongkar Dock Klien --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card scroll-mt-16" id="pod-step-dock" x-ref="step-dock">
            <header class="flex items-center justify-between gap-3 border-b border-surface-shell pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-ink text-white font-mono text-xs font-bold">2</span>
                    <h2 class="text-base font-bold text-ink">Foto Titik Bongkar Dock Klien</h2>
                </div>
                <span class="shrink-0 rounded px-2 py-0.5 text-[9px] font-bold bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/40">
                    KONDISI FISIK SAYUR
                </span>
            </header>

            <div class="flex flex-col gap-1.5 rounded-xl bg-ink p-3 text-white">
                <div class="flex items-center justify-between text-[10px] font-mono">
                    <span class="flex items-center gap-1 text-accent">
                        <x-gpa.icon name="map-pin" class="h-3 w-3" />
                        <span>GEO-TAG LOKASI DOCK</span>
                    </span>
                    <span class="rounded bg-accent px-1.5 py-0.5 text-ink font-bold">TERKUNCI GPS</span>
                </div>
                <div class="flex items-center justify-between text-[10px] text-white/70 font-mono">
                    <span>AKURASI: &plusmn; 4 METER</span>
                    <span class="text-accent">WAKTU TERA: REALTIME</span>
                </div>
            </div>

            <div class="flex min-h-[160px] flex-col justify-between gap-3 overflow-hidden rounded-xl bg-brand p-3 text-white outline outline-2 -outline-offset-2 outline-ink">
                <template x-if="previews.dock">
                    <div class="relative flex h-48 w-full items-center justify-center overflow-hidden rounded-lg bg-black">
                        <img :src="previews.dock" alt="Muatan Dock" class="h-full w-full object-contain">
                        <span class="absolute top-2 right-2 rounded bg-success-deep px-2 py-0.5 text-[10px] font-bold text-white">TERSIMPAN</span>
                    </div>
                </template>

                <div x-show="!previews.dock" class="flex flex-1 flex-col items-center justify-center gap-2 py-4">
                    <x-gpa.icon name="package" class="h-10 w-10 text-accent" />
                    <p class="text-center font-bold text-xs text-white">Ambil foto area bongkar muat &amp; tumpukan krat sayur</p>
                    <p class="text-center text-[10px] text-accent">Menunjukkan bukti fisik barang sampai di gudang klien</p>
                </div>
            </div>

            <button type="button" @click="capture('dock')"
                :class="dockCaptured()
                    ? 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep font-bold'
                    : 'bg-ink text-white font-bold'"
                class="flex h-11 w-full items-center justify-center gap-2 rounded-xl text-xs uppercase tracking-wider shadow-sub transition-transform active:scale-[0.98]">
                <x-gpa.icon name="camera" class="h-4 w-4 shrink-0" />
                <span x-text="dockCaptured() ? 'Ganti Foto Serah Terima Muatan' : 'Ambil Foto Bongkar Muat di Lokasi'"></span>
            </button>
        </section>

        {{-- Status kondisi muatan & retur --}}
        <section class="flex flex-col gap-3.5 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center gap-2 border-b border-surface-shell pb-2">
                <h2 class="text-base font-bold text-ink">STATUS KONDISI PENERIMAAN MUATAN</h2>
            </header>

            <div class="flex flex-col gap-2.5">
                <label @class([
                    'flex cursor-pointer items-start gap-3 rounded-xl p-3 transition-colors',
                    'bg-accent/20 outline outline-2 -outline-offset-2 outline-success-deep' => true,
                ])>
                    <input type="radio" name="pod-condition-radio" value="clear" class="sr-only"
                        checked
                        @change="selectCondition('clear')">

                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-ink"
                        :class="condition === 'clear' ? 'bg-ink' : 'bg-surface'">
                        <span class="h-1.5 w-1.5 rounded-full bg-white" x-show="condition === 'clear'"></span>
                    </span>

                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-ink">Muatan Utuh Sesuai Timbangan &amp; PO</span>
                            <span class="shrink-0 rounded px-1.5 py-0.5 font-mono text-[9px] font-bold bg-accent text-ink">LOLOS VERIFIKASI</span>
                        </div>
                        <span class="text-[10px] text-ink-body">Semua komoditas sayur diterima baik, segel sah, tanpa retur / susut berlebih.</span>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 transition-colors bg-surface-shell outline outline-1 -outline-offset-1 outline-line-board">
                    <input type="radio" name="pod-condition-radio" value="discrepancy" class="sr-only"
                        @change="selectCondition('discrepancy')">

                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-ink"
                        :class="condition === 'discrepancy' ? 'bg-ink' : 'bg-surface'">
                        <span class="h-1.5 w-1.5 rounded-full bg-white" x-show="condition === 'discrepancy'"></span>
                    </span>

                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-ink">Ada Catatan Selisih / Retur Fisik</span>
                            <span class="shrink-0 rounded px-1.5 py-0.5 font-mono text-[9px] font-bold bg-warning-cream text-warning-caution">CATATAN KHUSUS</span>
                        </div>
                        <span class="text-[10px] text-ink-body">Terdapat krat/sayur yang rusak saat jalan atau tidak lolos sortir penerimaan klien.</span>
                    </div>
                </label>
            </div>

            {{-- Form input item retur jika ada selisih --}}
            <div x-show="condition === 'discrepancy'" x-cloak class="flex flex-col gap-3 rounded-xl bg-warning-cream/30 p-3.5 border border-warning-caution/40">
                <p class="text-xs font-bold text-ink">Rincian Komoditas Rusak / Retur (kg):</p>
                @if ($currentOrder)
                    @foreach ($currentOrder->orderItems as $item)
                        <div class="flex flex-col gap-1.5 rounded-lg bg-surface p-2.5 border border-line-board/60 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-ink">{{ $item->product->name }}</span>
                                <span class="text-[11px] text-ink-body">Muatan: <strong>{{ number_format($item->ordered_qty, 0) }} kg</strong></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <div>
                                    <label class="block text-[10px] font-semibold text-ink-body mb-0.5">Berat Retur (kg)</label>
                                    <input type="number" step="0.1" min="0" max="{{ $item->ordered_qty }}" name="returns[{{ $item->id }}][returned_weight]" form="pod-submit-form" placeholder="0" class="w-full rounded border border-line-board px-2 py-1 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-ink-body mb-0.5">Alasan Retur</label>
                                    <input type="text" name="returns[{{ $item->id }}][return_reason]" form="pod-submit-form" placeholder="Contoh: Sayur layu/busuk" class="w-full rounded border border-line-board px-2 py-1 text-xs">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <footer class="flex items-center justify-between border-t border-surface-shell pt-2.5">
                <p class="text-xs text-ink-body">Butuh koordinasi penyesuaian timbangan/retur?</p>
                <a href="tel:0218901234"
                    class="flex items-center gap-1.5 rounded-lg bg-surface-disabled px-2.5 py-1 text-xs font-bold text-ink hover:bg-surface-soft">
                    <x-gpa.icon name="headset" class="h-3.5 w-3.5 text-success-deep" />
                    <span>Hubungi Dispatch Sentral Hub</span>
                </a>
            </footer>
        </section>

        {{-- Tombol validasi & selesaikan --}}
        <section class="flex flex-col gap-2 pt-2">
            <button type="button" @click="lockPod()"
                :class="canLock() ? 'bg-accent text-ink shadow-pop' : 'bg-surface-disabled text-ink-quiet cursor-not-allowed'"
                class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-mono text-sm font-bold uppercase tracking-wider outline outline-2 -outline-offset-2 outline-ink transition-all active:scale-[0.98]">
                <x-gpa.icon name="lock" class="h-5 w-5 shrink-0" />
                <span>KUNCI &amp; TERBITKAN BUKTI DIGITAL (POD)</span>
            </button>

            <p x-show="!canLock()" x-cloak
                class="text-center font-mono text-[10px] font-semibold text-danger">
                Langkah wajib belum lengkap: <span x-text="blockers().join(', ')"></span>
            </p>
        </section>
    </div>
@endsection

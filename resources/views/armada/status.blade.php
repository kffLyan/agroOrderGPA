@extends('layouts.armada')

@section('title', 'Status Armada & Kelaikan Kendaraan')

@section('content')
    @php
        $capacityPercent = min(100, ($totalTonaseKg / 3000) * 100);
        $user = auth()->user();
        $driverCode = 'DVR-' . str_pad($user?->id ?? 1, 2, '0', STR_PAD_LEFT);
        $driverInitials = strtoupper(substr($user?->name ?? 'SP', 0, 2));
        $driverName = $user?->name ?? 'Supir Pengiriman GPA';
        $unitCode = 'UNIT-CDD-REEFER-' . str_pad($user?->id ?? 1, 2, '0', STR_PAD_LEFT);
    @endphp

    <div class="mx-auto flex w-full max-w-[512px] flex-col gap-4 px-4 pb-24"
        x-data="armadaStatus">

        {{-- Status telemetri operasional kendaraan --}}
        <section class="flex items-center justify-between gap-3 rounded-2xl bg-surface p-4 shadow-card border-l-4 border-accent">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-ink shadow-sm">
                    <x-gpa.icon name="truck" class="h-5 w-5 shrink-0" />
                </span>

                <div class="flex flex-col">
                    <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">STATUS TELEMETRI KENDARAAN</span>
                    <span class="text-base font-bold text-ink">OPERASIONAL &bull; AKTIF BERJALAN</span>
                </div>
            </div>

            <button type="button" @click="refreshTelemetry()"
                class="shrink-0 rounded-xl bg-accent px-3 py-1.5 font-mono text-[10px] font-bold text-ink shadow-sub transition-transform active:scale-95">
                SINKRONISASI<br><span x-text="syncLabel()">15 DETIK</span>
            </button>
        </section>

        {{-- Data pengemudi & kendaraan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">DATA PENGEMUDI &amp; KENDARAAN</span>
                <span class="rounded bg-surface-shell px-2 py-0.5 font-mono text-[10px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    KODE SUPIR: {{ $driverCode }}
                </span>
            </header>

            <div class="flex items-center gap-3 pt-1">
                <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand text-accent font-inter text-xl font-bold shadow-sub">
                    {{ $driverInitials }}
                    <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-accent ring-2 ring-white"></span>
                </div>

                <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                    <span class="truncate text-base font-bold text-ink">{{ $driverName }}</span>
                    <span class="font-mono text-xs text-ink-body">{{ $unitCode }}</span>

                    <div class="flex flex-wrap gap-1 pt-1">
                        <span class="rounded px-2 py-0.5 font-mono text-[9px] font-bold bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board">
                            PANGKALAN: GUDANG SENTRAL BOGOR
                        </span>
                        <span class="rounded px-2 py-0.5 font-mono text-[9px] font-bold bg-accent/40 text-ink outline outline-1 -outline-offset-1 outline-accent">
                            SIM B1 UMUM: AKTIF RESMI
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 border-t border-surface-shell pt-3">
                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <span class="font-mono text-[9px] font-bold text-ink-quiet uppercase">SPESIFIKASI KENDARAAN</span>
                    <span class="font-mono text-xs font-bold text-ink">Truk Boks Pendingin (CDD)</span>
                    <span class="font-mono text-xs font-bold text-ink">Kompartemen Reefer</span>
                    <span class="text-[10px] text-ink-body">Pendingin Cold-Chain Standar Hortikultura</span>
                </div>

                <div class="flex flex-col gap-0.5 rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <span class="font-mono text-[9px] font-bold text-ink-quiet uppercase">NOMOR POLISI KENDARAAN</span>
                    <span class="font-mono text-sm font-extrabold text-ink">{{ $plate }}</span>
                    <span class="text-[10px] text-ink-body">Uji KIR &amp; STNK Berlaku Sah</span>
                </div>
            </div>
        </section>

        {{-- Telemetri IoT kendaraan --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">TELEMETRI IOT TERKONEKSI</span>
                <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                    LIVE SENSOR
                </span>
            </header>

            {{-- Suhu chiller --}}
            <div class="flex flex-col gap-1 rounded-xl bg-brand p-3.5 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="thermometer" class="h-4 w-4 text-accent" />
                        <span class="font-mono text-[10px] font-bold text-accent uppercase">SUHU BOKS PENDINGIN (SAYUR SEGAR)</span>
                    </div>
                    <span class="rounded bg-accent px-2 py-0.5 font-mono text-[9px] font-bold text-ink">
                        OPTIMAL DINGIN
                    </span>
                </div>
                <div class="flex items-baseline justify-between pt-1">
                    <span class="font-inter text-2xl font-extrabold text-white">+3.8°C</span>
                    <span class="text-xs text-white/80 font-mono">Ambang Batas: +2.0°C s.d. +6.0°C</span>
                </div>
            </div>

            {{-- Kapasitas muat box --}}
            <div class="flex flex-col gap-1.5 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/60">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-mono text-[10px] font-bold text-ink-quiet uppercase">KAPASITAS MUAT BOKS</span>
                    <span class="font-mono font-bold text-ink">{{ number_format($totalTonaseKg, 0, ',', '.') }} / 3.000 kg ({{ number_format($capacityPercent, 1, ',', '.') }}%)</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-surface-disabled overflow-hidden">
                    <div class="h-full rounded-full bg-accent" style="width: {{ $capacityPercent }}%"></div>
                </div>
            </div>

            {{-- Sensor bahan bakar & GPS --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="flex flex-col gap-1 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <div class="flex items-center gap-1.5 text-ink-quiet">
                        <x-gpa.icon name="gauge" class="h-3.5 w-3.5" />
                        <span class="font-mono text-[9px] font-bold uppercase">BAHAN BAKAR</span>
                    </div>
                    <span class="font-mono text-sm font-bold text-ink">78% Solar Dex</span>
                    <span class="text-[10px] text-ink-body">Odometer Terkoneksi</span>
                </div>

                <div class="flex flex-col gap-1 rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <div class="flex items-center gap-1.5 text-ink-quiet">
                        <x-gpa.icon name="map-pin" class="h-3.5 w-3.5" />
                        <span class="font-mono text-[9px] font-bold uppercase">GPS TRACKER</span>
                    </div>
                    <span class="font-mono text-sm font-bold text-ink">TERHUBUNG 4G</span>
                    <span class="text-[10px] text-ink-body">Satelit Aktif GPS &bull; Latensi Rendah</span>
                </div>
            </div>
        </section>

        {{-- Audit pre-trip kelaikan pagi --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">AUDIT KELAIKAN</span>
                    <span class="text-sm font-bold text-ink">PRE-TRIP KENDARAAN &bull; Pemeriksaan Rutin Pagi</span>
                </div>
                <span class="rounded bg-accent px-2 py-0.5 font-mono text-[10px] font-bold text-ink">
                    5/5 LENGKAP TERVERIFIKASI
                </span>
            </header>

            <div class="flex flex-col gap-1.5">
                <div class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-ink text-accent">
                            <x-gpa.icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-xs text-ink">Suhu awal chiller boks stabil (+3.8°C)</span>
                    </div>
                    <span class="font-mono text-[10px] font-semibold text-ink-quiet">05:30 WIB</span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-ink text-accent">
                            <x-gpa.icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-xs text-ink">Tekanan angin ban &amp; fungsi rem aman</span>
                    </div>
                    <span class="font-mono text-[10px] font-semibold text-ink-quiet">05:35 WIB</span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-ink text-accent">
                            <x-gpa.icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-xs text-ink">Kelengkapan dokumen KIR &amp; STNK asli</span>
                    </div>
                    <span class="font-mono text-[10px] font-semibold text-ink-quiet">05:40 WIB</span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-ink text-accent">
                            <x-gpa.icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-xs text-ink">APAR pemadam kebakaran &amp; kotak P3K lengkap</span>
                    </div>
                    <span class="font-mono text-[10px] font-semibold text-ink-quiet">05:45 WIB</span>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board/50">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-ink text-accent">
                            <x-gpa.icon name="check" class="h-3 w-3" />
                        </span>
                        <span class="text-xs text-ink">Kebersihan boks bebas kotoran &amp; bau</span>
                    </div>
                    <span class="font-mono text-[10px] font-semibold text-ink-quiet">05:50 WIB</span>
                </div>
            </div>

            <div class="flex items-center justify-between rounded-xl bg-surface-disabled p-2.5 text-xs">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep" />
                    <span class="font-medium text-ink">PETUGAS PEMERIKSA GUDANG: <strong>Petugas Teknis Sentral Hub</strong></span>
                </div>
                <span class="font-mono text-[10px] text-ink-quiet">JAM PERIKSA: 05:55 WIB</span>
            </div>
        </section>

        {{-- Rekap kinerja shift --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <div class="flex flex-col">
                    <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">REKAP KINERJA PENGANTARAN</span>
                    <span class="text-sm font-bold text-ink">Tugas Shift Berjalan &bull; Sentra Hub</span>
                </div>
                <span class="rounded bg-surface-shell px-2 py-0.5 font-mono text-[9px] font-bold text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    STATUS HARI INI
                </span>
            </header>

            <div class="grid grid-cols-2 gap-2">
                <div class="flex flex-col gap-1 rounded-xl p-3 min-h-[95px] justify-between bg-surface-shell text-success-deep outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="font-mono text-[9px] font-bold uppercase">TITIK PENGIRIMAN</span>
                    <span class="font-inter text-base font-bold text-ink">{{ $dropsCount }} Lokasi</span>
                    <span class="text-[9px] text-ink-body font-mono">{{ $doneCount }} Selesai &bull; {{ max(0, $dropsCount - $doneCount) }} Sedang Berjalan</span>
                </div>

                <div class="flex flex-col gap-1 rounded-xl p-3 min-h-[95px] justify-between bg-surface-shell text-ink-body outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="font-mono text-[9px] font-bold uppercase">TOTAL BERAT MUATAN</span>
                    <span class="font-inter text-base font-bold text-ink">{{ number_format($totalTonaseKg, 1, ',', '.') }} kg</span>
                    <span class="text-[9px] text-ink-body font-mono">Muatan Bersih Riil &bull; Sesuai Surat Jalan</span>
                </div>

                <div class="flex flex-col gap-1 rounded-xl p-3 min-h-[95px] justify-between bg-surface-shell text-success-deep outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="font-mono text-[9px] font-bold uppercase">KETEPATAN WAKTU (SLA)</span>
                    <span class="font-inter text-base font-bold text-ink">100% Tepat</span>
                    <span class="text-[9px] text-ink-body font-mono">Pengantaran Sesuai &bull; Jadwal Pagi</span>
                </div>

                <div class="flex flex-col gap-1 rounded-xl p-3 min-h-[95px] justify-between {{ $returCount > 0 ? 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/30' : 'bg-surface-shell text-success-deep outline outline-1 -outline-offset-1 outline-line-board' }}">
                    <span class="font-mono text-[9px] font-bold uppercase">CATATAN RETUR</span>
                    <span class="font-inter text-base font-bold text-ink">{{ $returCount > 0 ? ($returCount . ' Kasus') : '0 Kasus' }}</span>
                    <span class="text-[9px] text-ink-body font-mono">{{ $returCount > 0 ? 'Ada catatan retur' : 'Nihil retur' }} &bull; Kualitas Sayur Terjaga</span>
                </div>
            </div>
        </section>

        {{-- Dukungan operasional & hotline --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-4 shadow-card">
            <header class="flex items-center justify-between border-b border-surface-shell pb-2">
                <span class="font-mono text-[10px] font-bold uppercase text-ink-quiet">BANTUAN OPERASIONAL &amp; DISPATCH HUB</span>
                <span class="rounded bg-accent/40 px-2 py-0.5 font-mono text-[9px] font-bold text-ink">
                    HOTLINE SIAP
                </span>
            </header>

            <button type="button" @click="openReport()"
                class="flex items-center justify-center gap-2 rounded-xl p-3 outline outline-2 -outline-offset-2 outline-warning-caution bg-warning-cream/20 text-warning-deep font-bold text-xs uppercase hover:bg-warning-cream/40 transition-colors">
                <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0" />
                <span>LAPOR KENDALA KENDARAAN / JALAN</span>
            </button>

            <a href="tel:0218901234"
                class="flex items-center justify-between rounded-xl bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-disabled transition-colors">
                <div class="flex flex-col text-left">
                    <span class="font-mono text-[9px] text-ink-quiet uppercase">KOORDINATOR DISPATCH GUDANG SENTRA</span>
                    <span class="font-mono text-xs font-bold text-ink">Telepon Hotline: (021) 890-1234</span>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-ink text-accent">
                    <x-gpa.icon name="phone" class="h-4 w-4" />
                </span>
            </a>

            <button type="button" @click="openSop()"
                class="flex items-center justify-between rounded-xl bg-surface-shell p-2.5 outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-disabled transition-colors">
                <div class="flex items-center gap-2 text-xs text-ink">
                    <x-gpa.icon name="file-text" class="h-4 w-4 text-ink-body" />
                    <span>Panduan Penanganan Mutu Sayur &amp; Suhu Cold-Chain Pengiriman</span>
                </div>
                <span class="rounded bg-surface-disabled px-2 py-0.5 font-mono text-[9px] font-bold text-ink-body">
                    SOP_GPA
                </span>
            </button>

            {{-- Form tutup shift --}}
            <form id="close-shift-form" method="POST" action="{{ route('armada.status.close-shift') }}" class="hidden">
                @csrf
            </form>

            <div class="flex flex-col gap-2 border-t border-surface-shell pt-3">
                <button type="button" @click="closeShift(); document.getElementById('close-shift-form')?.submit();"
                    :disabled="closed"
                    class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-accent font-mono text-xs font-bold uppercase tracking-wider text-ink shadow-pop transition-transform active:scale-[0.98]">
                    <x-gpa.icon name="check-circle" class="h-4 w-4" />
                    <span>SELESAIKAN SHIFT &amp; TUTUP TUGAS PENGANTARAN</span>
                </button>

                <p class="text-center font-mono text-[10px] text-ink-body">
                    *Pastikan seluruh bukti terima PoD telah diunggah sebelum menutup shift harian.
                </p>
            </div>
        </section>

        <p class="text-center font-mono text-[10px] text-ink-quiet pt-2">
            GREEN PASUNDAN AGRIKULTUR &bull; SISTEM ARMADA LOGISTIK &bull; VERSI LAPANGAN TERHUBUNG
        </p>
    </div>
@endsection

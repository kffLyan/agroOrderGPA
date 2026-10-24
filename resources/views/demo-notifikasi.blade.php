@extends('layouts.public')

@section('title', 'Katalog & Contoh Notifikasi GPA')

@section('content')
<div class="min-h-screen bg-canvas py-10 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-10">

        {{-- Header Showcase --}}
        <div class="rounded-3xl border border-line-hair bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line-hair pb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent/30 px-3 py-1 font-mono text-xs font-bold text-brand uppercase tracking-wider">
                        Komponen Desain GPA
                    </span>
                    <h1 class="mt-2 text-2xl font-extrabold text-ink sm:text-3xl">Showcase & Contoh Notifikasi</h1>
                    <p class="mt-1 text-sm text-ink-body">
                        Koleksi komponen notifikasi resmi AgroOrder GPA: Banner, Top Bar, Corner Pop-up, dan Toast Alert.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sekretaris.dashboard') }}" class="rounded-xl border border-line-hair bg-canvas px-4 py-2 text-xs font-bold text-ink hover:bg-surface transition-colors">
                        &larr; Ke Dashboard
                    </a>
                </div>
            </div>

            {{-- Interactive Test Console --}}
            <div class="mt-6 rounded-2xl bg-surface-muted/60 p-5 border border-line-hair">
                <p class="text-xs font-mono font-bold uppercase tracking-wider text-ink-quiet">Uji Coba Interaktif (Klik Tombol di Bawah)</p>
                <p class="text-xs text-ink-body mt-0.5">Klik untuk memunculkan notifikasi animasi melayang (Toast) secara langsung di sudut kanan atas layar:</p>
                <div class="mt-3 flex flex-wrap gap-2.5">
                    <button type="button" onclick="window.showGpaNotification({ title: 'Verifikasi Berhasil', description: 'Pesanan PO-2026-004 telah disetujui oleh Sekretaris.', type: 'success' })"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand px-4 py-2 text-xs font-bold text-white shadow hover:bg-brand-hover transition-colors">
                        <span class="h-2 w-2 rounded-full bg-accent"></span>
                        Tes Toast Berhasil (Success)
                    </button>

                    <button type="button" onclick="window.showGpaNotification({ title: 'Gagal Memproses Data', description: 'Kapasitas armada pendingin reefer telah penuh untuk jadwal ini.', type: 'danger' })"
                        class="inline-flex items-center gap-2 rounded-xl bg-danger px-4 py-2 text-xs font-bold text-white shadow hover:bg-red-700 transition-colors">
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        Tes Toast Peringatan (Error)
                    </button>

                    <button type="button" onclick="window.showGpaNotification({ title: 'Limit Kredit Menipis', description: 'Sisa plafon kredit kemitraan B2B tersisa 12% dari total komitmen.', type: 'warning' })"
                        style="background-color: #c25e00;"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold text-white shadow hover:opacity-90 transition-opacity">
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        Tes Toast Peringatan (Warning)
                    </button>

                    <button type="button" onclick="window.showGpaNotification({ title: 'Informasi Penimbangan', description: 'Timbangan netto riil armada B-9821-GPA telah dikunci oleh Koordinator.', type: 'info' })"
                        style="background-color: #0284c7;"
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold text-white shadow hover:opacity-90 transition-opacity">
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        Tes Toast Info
                    </button>

                    <button type="button" onclick="window.showGpaNotification({ title: 'Notifikasi Default GPA', description: 'Contoh notifikasi standar dengan palet warna aksen daun GPA.', type: 'accent' })"
                        class="inline-flex items-center gap-2 rounded-xl border border-line-strong bg-white px-4 py-2 text-xs font-bold text-ink shadow hover:bg-canvas transition-colors">
                        <span class="h-2 w-2 rounded-full bg-accent-deep"></span>
                        Tes Toast Default (Accent)
                    </button>

                    <button type="button" onclick="triggerDemoCorner()"
                        class="inline-flex items-center gap-2 rounded-xl border border-accent-deep bg-accent/20 px-4 py-2 text-xs font-bold text-brand shadow hover:bg-accent/40 transition-colors">
                        <span class="h-2 w-2 rounded-full bg-brand"></span>
                        Tes Corner Pop-up (Kanan Bawah)
                    </button>
                </div>
            </div>
        </div>

        <script>
            function triggerDemoCorner() {
                const existing = document.querySelector('[data-gpa-corner]');
                if (existing) {
                    dismissDemoCorner(existing);
                    return;
                }
                const corner = document.createElement('div');
                corner.setAttribute('data-gpa-corner', '');
                corner.className = 'gpa-corner-show fixed bottom-4 left-4 right-4 z-50 sm:left-auto sm:right-5 sm:max-w-[420px] pointer-events-auto';
                corner.innerHTML = `
                    <div role="status" class="flex items-start gap-3 rounded-xl border border-l-4 border-line-hair border-l-success bg-white p-3 shadow-xl sm:items-center sm:p-3.5 transition-all duration-200">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-success/20">
                            <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-5 w-auto shrink-0 object-contain">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="text-xs font-bold text-ink sm:text-sm leading-snug">Verifikasi Pembayaran Masuk</p>
                                <time class="shrink-0 font-mono text-[10px] text-ink-body/70">Baru saja</time>
                            </div>
                            <p class="mt-0.5 text-xs text-ink-body leading-relaxed">Faktur INV-2026-004 telah diunggah bukti transfernya oleh Klien.</p>
                        </div>
                        <button type="button" aria-label="Tutup notifikasi" onclick="dismissDemoCorner(this)"
                            class="ml-1 -mr-1 rounded-lg px-1.5 py-0.5 text-xl leading-none text-ink-body/50 hover:bg-canvas hover:text-ink transition-colors">&times;</button>
                    </div>
                `;
                document.body.appendChild(corner);
            }

            function dismissDemoCorner(btnOrEl) {
                const el = btnOrEl.hasAttribute && btnOrEl.hasAttribute('data-gpa-corner')
                    ? btnOrEl
                    : btnOrEl.closest('[data-gpa-corner]');
                if (!el) return;
                el.classList.remove('gpa-corner-show');
                el.classList.add('gpa-corner-hide');
                setTimeout(() => el.remove(), 260);
            }
        </script>

        {{-- 1. Notification Banner --}}
        <section class="space-y-4">
            <div class="flex items-baseline justify-between">
                <div>
                    <h2 class="text-lg font-bold text-ink">1. Notification Banner (<code class="font-mono text-sm text-brand">&lt;x-gpa.notification-banner&gt;</code>)</h2>
                    <p class="text-xs text-ink-body">Cocok disematkan di dalam kartu, halaman detail, atau panel verifikasi.</p>
                </div>
            </div>

            <div class="grid gap-4">
                {{-- Success --}}
                <x-gpa.notification-banner
                    title="Pesanan PO-2026-081 Berhasil Diverifikasi"
                    description="Seluruh jadwal panen kentang granola dan alokasi armada truk reefer telah disahkan ke sistem."
                    time="Baru saja"
                    type="success"
                    dismissible
                >
                    <a href="#" class="rounded-lg bg-surface px-3 py-1.5 text-xs font-bold text-ink shadow-sm border border-line-hair hover:bg-canvas">
                        Lihat Faktur
                    </a>
                </x-gpa.notification-banner>

                {{-- Default Accent --}}
                <x-gpa.notification-banner
                    title="Pengingat Jadwal Muat Komoditas"
                    description="Armada CDD Reefer Chilled dijadwalkan tiba di Gudang Ciwidey pukul 05:30 WIB."
                    time="10 menit lalu"
                    type="accent"
                    dismissible
                />

                {{-- Danger / Error --}}
                <x-gpa.notification-banner
                    title="Bukti Transfer Perlu Direvisi"
                    description="Nominal transfer pada slip pembayaran tidak sesuai dengan total faktur tagihan (selisih Rp 250.000)."
                    time="1 jam lalu"
                    type="danger"
                    dismissible
                >
                    <a href="#" class="rounded-lg bg-danger px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-red-700">
                        Unggah Ulang
                    </a>
                </x-gpa.notification-banner>

                {{-- Warning --}}
                <x-gpa.notification-banner
                    title="Kapasitas Gudang Mendekati Batas Maksimum"
                    description="Stok kubis brussels dan brokoli mencapai 88% dari daya tampung ruang pendingin cold storage."
                    time="Hari ini"
                    type="warning"
                    dismissible
                />

                {{-- Info --}}
                <x-gpa.notification-banner
                    title="Sinkronisasi Barcode Timbangan Selesai"
                    description="5 batch panen sayuran telah terindeks ke hash audit rantai pasok GPA."
                    time="Kemarin"
                    type="info"
                    dismissible
                />
            </div>
        </section>

        {{-- 2. Notification Bar --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-bold text-ink">2. Notification Bar (<code class="font-mono text-sm text-brand">&lt;x-gpa.notification-bar&gt;</code>)</h2>
                <p class="text-xs text-ink-body">Baris informasi horizontal tipis yang diletakkan di bagian atas halaman/header.</p>
            </div>

            <div class="space-y-3 rounded-2xl border border-line-hair bg-white p-4">
                <x-gpa.notification-bar
                    title="Pengumuman Operasional:"
                    description="Layanan pengiriman rute Bandung Timur mengalami rekayasa jalur cuaca ekstrem."
                    time="Hari ini, 08:00 WIB"
                    type="warning"
                    dismissible
                >
                    <a href="#">Pantau Rute Armada</a>
                </x-gpa.notification-bar>

                <x-gpa.notification-bar
                    title="Pembaruan Sistem:"
                    description="Fitur tanda tangan digital SHA-256 untuk surat jalan telah aktif di semua portal."
                    time="Versi 2.4"
                    type="accent"
                    dismissible
                >
                    <a href="#">Baca Catatan Rilis</a>
                </x-gpa.notification-bar>

                <x-gpa.notification-bar
                    title="Pengingat Audit:"
                    description="Seluruh dokumen surat jalan wajib dicetak rangkap 3 sesuai SOP logistik."
                    time="SOP Resmi"
                    type="info"
                    dismissible
                />
            </div>
        </section>

        {{-- 3. Notification Corner & Toast --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-bold text-ink">3. Notification Corner & Toast Alert</h2>
                <p class="text-xs text-ink-body">Komponen melayang di sudut layar (Corner di kanan bawah, Toast di kanan atas) dengan animasi dan auto-dismiss.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Corner Card Preview --}}
                <div class="rounded-2xl border border-line-hair bg-white p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-brand uppercase">&lt;x-gpa.notification-corner&gt;</span>
                        <span class="text-[11px] text-ink-quiet">Pojok Kanan Bawah</span>
                    </div>
                    <p class="text-xs text-ink-body">Digunakan untuk pemberitahuan yang butuh atensi pengguna tetapi tidak mengganggu alur kerja di tengah layar:</p>
                    <x-gpa.notification-banner
                        title="Surat Jalan Telah Diterbitkan"
                        description="Armada CDD Reefer B-9012-GPA siap berangkat menuju Bandung."
                        time="Baru saja"
                        type="success"
                        class="shadow-md"
                    >
                        <button type="button" class="rounded-lg px-2 text-xl leading-none text-ink-body hover:bg-canvas">&times;</button>
                    </x-gpa.notification-banner>
                </div>

                {{-- Toast Card Preview --}}
                <div class="rounded-2xl border border-line-hair bg-white p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-brand uppercase">&lt;x-gpa.notification-toast&gt;</span>
                        <span class="text-[11px] text-ink-quiet">Pojok Kanan Atas</span>
                    </div>
                    <p class="text-xs text-ink-body">Digunakan untuk feedback instan setelah aksi penyimpanan form atau flash session redirect:</p>
                    <x-gpa.notification-banner
                        title="Data Pesanan Berhasil Disimpan"
                        description="Pesanan manual SK-2026-004 telah tercatat di antrean."
                        time="Baru saja"
                        type="accent"
                        class="shadow-md"
                    >
                        <button type="button" class="rounded-lg px-2 text-xl leading-none text-ink-body hover:bg-canvas">&times;</button>
                    </x-gpa.notification-banner>
                </div>
            </div>
        </section>

        {{-- 4. Panduan Kode & Penggunaan --}}
        <section class="rounded-3xl border border-line-hair bg-white p-6 shadow-sm sm:p-8 space-y-6">
            <h2 class="text-lg font-bold text-ink">Cara Menggunakan Komponen Notifikasi</h2>

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Contoh Blade --}}
                <div class="space-y-2">
                    <p class="text-xs font-mono font-bold uppercase tracking-wider text-ink-quiet">A. Di File Blade View</p>
                    <pre class="overflow-x-auto rounded-xl bg-ink-strong p-4 text-xs font-mono text-white leading-relaxed"><code>{{-- Banner Notifikasi --}}
&lt;x-gpa.notification-banner
    title="Pesanan Disetujui"
    description="Surat jalan siap dicetak."
    type="success"
    time="Baru saja"
    dismissible
/&gt;

{{-- Bar Notifikasi Horizontal --}}
&lt;x-gpa.notification-bar
    title="Info Sistem:"
    description="Jadwal panen telah dikunci."
    type="accent"
/&gt;</code></pre>
                </div>

                {{-- Contoh Controller & JS --}}
                <div class="space-y-2">
                    <p class="text-xs font-mono font-bold uppercase tracking-wider text-ink-quiet">B. Dari Controller / JavaScript</p>
                    <pre class="overflow-x-auto rounded-xl bg-ink-strong p-4 text-xs font-mono text-white leading-relaxed"><code>// 1. Dari Laravel Controller
return redirect()-&gt;back()
    -&gt;with('success', 'Data berhasil disimpan!');

// 2. Dari JavaScript di browser
window.showGpaNotification({
    title: 'Sukses',
    description: 'Data telah disimpan.',
    type: 'success', // 'danger' | 'warning' | 'info'
    duration: 5000
});</code></pre>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekapitulasi Faktur &amp; Tagihan Resmi — {{ $user->company_name ?: $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .a4-landscape {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            .signatures {
                page-break-inside: avoid;
            }
        }

        @media screen {
            body {
                background: #f4f6f0;
            }
        }

        .a4-landscape {
            width: 297mm;
            min-height: 210mm;
            margin: 20px auto;
            background: #ffffff;
            box-shadow: 0 10px 35px -15px rgba(21, 58, 1, 0.35);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 8pt;
            line-height: 1.45;
            color: #191c18;
            padding: 10mm 12mm;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="antialiased">

    {{-- Topbar Kontrol Layar (Tidak Tercetak) --}}
    <div class="no-print sticky top-0 z-30 border-b border-line-board bg-white/95 backdrop-blur shadow-sub">
        <div class="mx-auto flex max-w-[297mm] items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                <a href="{{ route('klien.documents') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-medium text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    Kembali ke Pusat Dokumen
                </a>
                <span class="rounded bg-brand/10 px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-brand">
                    REKAP RESMI FAKTUR KONSOLIDASI (A4 LANDSCAPE)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('klien.documents.export') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-sub transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                    Unduh Excel (CSV)
                </a>
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-3.5 py-1.5 text-xs font-bold text-white shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                    Cetak Dokumen (Ctrl+P)
                </button>
            </div>
        </div>
    </div>

    {{-- Kanvas A4 Landscape --}}
    <main class="a4-landscape">
        {{-- Kop Surat Resmi GPA --}}
        <header class="flex items-start justify-between gap-4 border-b-2 border-brand pb-3">
            <div class="flex items-start gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-brand/30 bg-surface-shell p-1.5">
                    <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-9 w-9 object-contain">
                </div>
                <div>
                    <h1 class="text-sm font-extrabold uppercase tracking-wide text-ink">
                        PT GREEN PASUNDAN AGRICULTURE
                    </h1>
                    <p class="text-[7.5pt] text-ink-body">
                        Divisi Administrasi Penagihan, Faktur Konsolidasi &amp; Pajak Pertanian B2B<br>
                        Sentra Logistik Lembang &amp; Ciwidey &bull; Desa Panundaan, Kab. Bandung 40973 &bull; Hotline Keuangan: +62 811-2345-6789
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-block rounded bg-brand px-2 py-0.5 text-[7pt] font-extrabold uppercase tracking-wider text-white">
                    Rekonsiliasi Sah &bull; NPWP Terdaftar
                </span>
                <p class="mt-1 font-mono text-[7pt] font-semibold text-ink-muted">
                    REG: {{ $docNumber }}
                </p>
                <p class="text-[7pt] text-ink-muted">
                    Dicetak: <span class="font-mono text-ink">{{ $printDate }}</span>
                </p>
            </div>
        </header>

        {{-- Judul Dokumen --}}
        <div class="mt-3 flex items-center justify-between border-b border-line-board pb-2">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-brand">
                    REKAPITULASI RESMI FAKTUR KONSOLIDASI &amp; STATUS TAGIHAN TEMPO
                </h2>
                <p class="text-[7.5pt] text-ink-body">
                    Arsip Digital Penagihan Bulanan Sah, Berita Acara Timbang Gudang &amp; Bukti Rekonsiliasi Bank PT Green Pasundan Agriculture
                </p>
            </div>
            <div class="text-right">
                <span class="rounded border border-brand/40 bg-brand/5 px-2 py-0.5 font-mono text-[7pt] font-bold text-brand uppercase">
                    KONSOLIDASI MITRA: {{ $user->company_name ?: $user->name }}
                </span>
            </div>
        </div>

        {{-- Parameter Mitra & Ringkasan Finansial --}}
        <section class="mt-3 grid grid-cols-2 gap-3 text-[7.5pt]">
            <div class="rounded-lg border border-line-board bg-surface-shell p-2">
                <p class="text-[7pt] font-bold uppercase tracking-wider text-brand">Profil Entitas Penerima Faktur</p>
                <div class="mt-1 grid grid-cols-3 gap-y-0.5">
                    <span class="text-ink-muted">Nama Perusahaan</span>
                    <span class="col-span-2 font-bold text-ink">: {{ $user->company_name ?: $user->name }}</span>

                    <span class="text-ink-muted">Finance / PIC</span>
                    <span class="col-span-2 text-ink">: {{ $user->pic_name ?: $user->name }} ({{ $user->pic_phone ?: ($user->phone ?: '-') }})</span>

                    <span class="text-ink-muted">Alamat Kantor / Hub</span>
                    <span class="col-span-2 text-ink truncate">: {{ $user->address ?: 'Sentra Operasional Klien' }}</span>

                    <span class="text-ink-muted">Ketentuan Termin</span>
                    <span class="col-span-2 font-semibold text-brand">: Term of Payment (TOP 30 Hari) &bull; Transfer Bank BCA</span>
                </div>
            </div>

            <div class="rounded-lg border border-line-board bg-surface-shell p-2">
                <p class="text-[7pt] font-bold uppercase tracking-wider text-brand">Ringkasan Posisi Keuangan Tagihan</p>
                <div class="mt-1 grid grid-cols-4 gap-2 text-center">
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Total Faktur</span>
                        <span class="font-mono text-xs font-bold text-ink">{{ $invoices->count() }}</span>
                    </div>
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Netto Tera</span>
                        <span class="font-mono text-xs font-bold text-ink">{{ number_format($totalNetto, 1, ',', '.') }} kg</span>
                    </div>
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Tempo Berjalan</span>
                        <span class="font-mono text-xs font-extrabold text-amber-700">Rp {{ number_format($unpaidTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Lunas Sah</span>
                        <span class="font-mono text-xs font-extrabold text-brand">Rp {{ number_format($paidTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tabel Data Faktur Konsolidasi Rinci --}}
        <section class="mt-3">
            <table class="w-full border-collapse text-left text-[7.5pt]">
                <thead>
                    <tr class="border-b border-line-board bg-surface-shell">
                        <th class="py-1 px-1.5 text-center font-bold text-ink" style="width: 25px;">No</th>
                        <th class="py-1 px-1.5 font-bold text-ink" style="width: 110px;">No. Faktur Tagihan</th>
                        <th class="py-1 px-1.5 font-bold text-ink" style="width: 80px;">Tgl Terbit</th>
                        <th class="py-1 px-1.5 font-bold text-ink" style="width: 140px;">Periode Konsolidasi</th>
                        <th class="py-1 px-1.5 text-center font-bold text-ink" style="width: 60px;">SJ Terlampir</th>
                        <th class="py-1 px-1.5 text-right font-bold text-ink" style="width: 90px;">Netto Tera (Kg)</th>
                        <th class="py-1 px-1.5 text-center font-bold text-ink" style="width: 85px;">Jatuh Tempo</th>
                        <th class="py-1 px-1.5 text-center font-bold text-ink" style="width: 95px;">Status Faktur</th>
                        <th class="py-1 px-1.5 text-right font-bold text-ink" style="width: 110px;">Total Tagihan (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-hair">
                    @forelse ($invoices as $idx => $inv)
                        @php
                            $invNetto = (float) $inv->orders->sum(function ($o) {
                                return $o->orderItems->sum(fn($i) => $i->actual_net_weight ?? $i->ordered_qty);
                            });
                            $sjCount = $inv->orders->count();
                            $periodStr = ($inv->period_start ? $inv->period_start->format('d/m/Y') : '-') . ' - ' . ($inv->period_end ? $inv->period_end->format('d/m/Y') : '-');
                            $isPaid = $inv->status === 'PAID';
                        @endphp
                        <tr class="{{ $idx % 2 === 1 ? 'bg-surface-shell/40' : '' }}">
                            <td class="py-1 px-1.5 text-center font-mono text-ink-muted">{{ $idx + 1 }}</td>
                            <td class="py-1 px-1.5">
                                <strong class="font-mono text-brand">{{ $inv->invoice_number }}</strong>
                            </td>
                            <td class="py-1 px-1.5 text-ink-muted font-mono">
                                {{ $inv->created_at ? $inv->created_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-1 px-1.5 text-ink-body font-mono text-[7pt]">
                                {{ $periodStr }}
                            </td>
                            <td class="py-1 px-1.5 text-center font-mono text-ink">
                                {{ $sjCount }} Dokumen
                            </td>
                            <td class="py-1 px-1.5 text-right font-mono font-bold text-ink">
                                {{ number_format($invNetto, 1, ',', '.') }}
                            </td>
                            <td class="py-1 px-1.5 text-center font-mono {{ $isPaid ? 'text-ink-muted' : ($inv->due_date && $inv->due_date->isPast() ? 'text-red-700 font-bold' : 'text-ink') }}">
                                {{ $inv->due_date ? $inv->due_date->format('d/m/Y') : 'TOP 30D' }}
                            </td>
                            <td class="py-1 px-1.5 text-center">
                                <span class="inline-block rounded px-1.5 py-0.5 text-[6.5pt] font-bold uppercase tracking-wider {{ $isPaid ? 'bg-brand/10 text-brand' : ($inv->due_date && $inv->due_date->isPast() ? 'bg-red-100 text-red-800' : 'bg-surface-pill text-ink-body') }}">
                                    {{ $isPaid ? 'Lunas' : ($inv->due_date && $inv->due_date->isPast() ? 'Overdue' : 'Tempo 30') }}
                                </span>
                            </td>
                            <td class="py-1 px-1.5 text-right font-mono font-bold text-ink">
                                Rp {{ number_format($inv->grand_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-3 text-center text-ink-muted italic">
                                Belum ada faktur penagihan konsolidasi yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-brand bg-surface-shell font-bold">
                        <td colspan="5" class="py-1 px-1.5 text-right text-brand uppercase tracking-wider text-[7.5pt]">
                            TOTAL AKUMULASI FAKTUR KONSOLIDASI:
                        </td>
                        <td class="py-1 px-1.5 text-right font-mono text-brand">{{ number_format($totalNetto, 1, ',', '.') }} kg</td>
                        <td colspan="2"></td>
                        <td class="py-1 px-1.5 text-right font-mono text-brand text-[8pt]">
                            Rp {{ number_format($totalGrand, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </section>

        {{-- Instruksi Pembayaran Bank & Pajak Pertanian --}}
        <section class="mt-3 grid grid-cols-2 gap-3 text-[7pt] text-ink-body">
            <div class="rounded border border-line-board bg-surface-shell/50 p-2">
                <h4 class="font-bold uppercase tracking-wider text-brand">Rekening Penerima Resmi PT Green Pasundan Agriculture:</h4>
                <p class="mt-0.5">
                    <strong>Bank Central Asia (BCA)</strong> &bull; No. Rekening: <span class="font-mono font-bold text-ink">840-552-1920</span><br>
                    Atas Nama: <strong>PT Green Pasundan Agriculture</strong> (Kantor Cabang Lembang)<br>
                    <span class="text-[6.5pt] text-ink-muted">Cantumkan Nomor Faktur pada berita transfer bank untuk rekonsiliasi otomatis.</span>
                </p>
            </div>
            <div class="rounded border border-line-board bg-surface-shell/50 p-2">
                <h4 class="font-bold uppercase tracking-wider text-brand">Fasilitas Pajak Pertanian &amp; Ketentuan Hukum:</h4>
                <p class="mt-0.5">
                    Berdasarkan PP No. 49 Tahun 2022, penyerahan komoditas sayuran segar primer hasil pertanian dibebaskan dari pengenaan PPN (PPN Dibebaskan). Seluruh Surat Jalan sah terlampir telah teruji melalui kalibrasi timbang digital resmi.
                </p>
            </div>
        </section>

        {{-- Lembar Pengesahan Sah (Tanda Tangan) --}}
        <section class="signatures mt-4 border-t border-line-board pt-3 text-[7.5pt]">
            <div class="grid grid-cols-3 gap-6 text-center">
                <div>
                    <p class="text-ink-muted">Pihak Penerima / Mitra Klien</p>
                    <p class="font-bold text-ink">{{ $user->company_name ?: 'Perusahaan Mitra' }}</p>
                    <div class="h-12 flex items-center justify-center">
                        <span class="rounded border border-dashed border-line-board px-2 py-0.5 font-mono text-[6.5pt] text-ink-quiet">
                            CAP &amp; TANDA TANGAN
                        </span>
                    </div>
                    <p class="font-bold text-ink underline">{{ $user->pic_name ?: $user->name }}</p>
                    <p class="text-[6.5pt] text-ink-muted">Finance / Accounts Payable</p>
                </div>

                <div>
                    <p class="text-ink-muted">Verifikasi Piutang &amp; Rekonsiliasi</p>
                    <p class="font-bold text-ink">Divisi Penagihan GPA</p>
                    <div class="h-12 flex items-center justify-center">
                        <span class="rounded border border-dashed border-brand/50 px-2 py-0.5 font-mono text-[6.5pt] text-brand">
                            RECONCILED &amp; AUDITED
                        </span>
                    </div>
                    <p class="font-bold text-ink underline">Rina Marlina</p>
                    <p class="text-[6.5pt] text-ink-muted">Staf Administrasi &amp; Kas Masuk</p>
                </div>

                <div>
                    <p class="text-ink-muted">Otorisasi Keuangan Pusat</p>
                    <p class="font-bold text-brand">PT Green Pasundan Agriculture</p>
                    <div class="h-12 flex items-center justify-center">
                        <div class="rounded border border-brand/60 px-2 py-0.5 text-center">
                            <span class="block font-mono text-[6pt] font-extrabold tracking-wider text-brand">LEGAL INVOICE PASUNDAN</span>
                        </div>
                    </div>
                    <p class="font-bold text-brand underline">H. Ridwan Permana</p>
                    <p class="text-[6.5pt] text-ink-muted">Direktur / Authorized Signatory</p>
                </div>
            </div>
            <div class="mt-2 text-center text-[6pt] text-ink-quiet font-mono">
                REKAPITULASI RESMI FAKTUR GPA PADA {{ $printDate }} &bull; NO. REGISTRASI {{ $docNumber }} &bull; SAH SECARA ELEKTRONIK
            </div>
        </section>
    </main>

    @if (request()->query('auto_print'))
        <script>
            window.addEventListener('load', () => {
                window.print();
            });
        </script>
    @endif
</body>
</html>

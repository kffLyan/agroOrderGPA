<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita Acara Retur {{ $document['number'] }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        @media print {
            body {
                background: #ffffff;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .print-page {
                width: 100%;
                max-width: 100%;
                min-height: 0;
                margin: 0;
                padding: 10mm 12mm;
                border: 0;
                box-shadow: none;
            }
        }

        @media screen {
            body {
                background: #f8faf3;
            }
        }

        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
        }
    </style>
</head>

<body class="flex min-h-screen flex-col items-center px-4 py-6 font-sans text-ink antialiased">
    <header class="no-print mb-6 flex w-full max-w-[210mm] flex-wrap items-center justify-between gap-3 rounded-lg border border-line-hair bg-surface p-3 shadow-card">
        <a href="{{ route('coordinator.weighing') }}"
            class="inline-flex items-center gap-1.5 rounded-md border border-line-board px-3 py-1.5 gpa-meta font-semibold text-ink transition-colors hover:bg-surface-shell">
            <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
            Kembali ke Timbangan &amp; Sortir
        </a>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-1.5 gpa-meta font-semibold text-white shadow-sub transition-colors hover:bg-brand-hover">
            <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
            Cetak Berita Acara Retur (Ctrl+P)
        </button>
    </header>

    <main class="print-page a4-sheet box-border border border-line-hair bg-surface p-8 shadow-card">
        <div class="flex min-h-[260mm] flex-col">
            <div class="flex-1">
                <header class="mb-4 border-b-2 border-brand pb-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="flex shrink-0 flex-col items-center justify-center border-2 border-brand px-2.5 py-1.5">
                                <span class="font-sans text-xl font-bold leading-none tracking-tight text-brand">GPA</span>
                                <span class="gpa-micro-bold uppercase tracking-widest text-ink-muted">Logistik</span>
                            </div>
                            <div>
                                <h1 class="gpa-meta-lg font-bold uppercase tracking-tight text-ink-strong">{{ $brand }}</h1>
                                @foreach ($company_lines as $line)
                                    <p class="text-[8pt] leading-relaxed text-ink-body">{{ $line }}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="min-w-[235px] border border-brand bg-surface-shell p-2.5 text-right">
                            <span class="gpa-micro-bold block uppercase tracking-wider text-ink-body">Form Dokumen Mutu Kontrol</span>
                            <h2 class="mt-0.5 text-xs font-bold uppercase leading-snug text-brand">{{ $document['title'] }}</h2>
                            <span class="gpa-micro text-ink-muted">(BAP-RETUR)</span>
                            <div class="mt-2 flex items-center justify-between border-t border-line-board pt-1 text-left">
                                <span class="gpa-micro text-ink-body">No. Register:</span>
                                <span class="gpa-meta-lg font-bold text-brand">{{ $document['number'] }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <section class="mb-4 border border-line-board bg-surface-shell p-2.5">
                    <h3 class="mb-2 border-b border-line-board pb-1.5 gpa-micro-bold uppercase tracking-wider text-brand">
                        Data Referensi Surat Jalan &amp; Lokasi Bongkar
                    </h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-1.5">
                        <div class="flex items-baseline justify-between gap-2 border-b border-dotted border-line-board py-0.5">
                            <dt class="text-[8pt] text-ink-muted">Mengacu Surat Jalan</dt>
                            <dd class="font-mono text-[8pt] font-semibold text-ink-strong">{{ $document['delivery'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-2 border-b border-dotted border-line-board py-0.5">
                            <dt class="text-[8pt] text-ink-muted">Nomor Purchase Order</dt>
                            <dd class="font-mono text-[8pt] font-semibold text-ink-strong">{{ $document['order'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-2 border-b border-dotted border-line-board py-0.5">
                            <dt class="text-[8pt] text-ink-muted">Waktu Pemeriksaan</dt>
                            <dd class="font-mono text-[8pt] text-ink-strong">{{ $document['inspection'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-2 border-b border-dotted border-line-board py-0.5">
                            <dt class="text-[8pt] text-ink-muted">Armada &amp; Pengemudi</dt>
                            <dd class="text-right text-[8pt] font-medium text-ink-strong">{{ $document['vehicle'] }}</dd>
                        </div>
                        <div class="col-span-2 flex items-baseline justify-between gap-2 pt-0.5">
                            <dt class="text-[8pt] text-ink-muted">Lokasi Bongkar / Titik QC</dt>
                            <dd class="text-right text-[8pt] font-medium text-ink-strong">{{ $document['location'] }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="mb-4">
                    <div class="mb-1 flex items-center justify-between gap-3">
                        <h3 class="gpa-micro-bold uppercase tracking-wider text-brand">
                            Rekonsiliasi Fisik Timbangan &amp; Penolakan Sebagian (kg)
                        </h3>
                        <span class="gpa-micro text-ink-muted">Satuan netto: kilogram (kg)</span>
                    </div>
                    <div class="overflow-hidden border border-brand">
                        <table class="w-full border-collapse text-[8pt]">
                            <thead>
                                <tr class="border-b-2 border-brand bg-surface-shell text-[7pt] font-bold uppercase text-ink-strong">
                                    <th class="w-7 border-r border-line-board p-1.5 text-center">No</th>
                                    <th class="w-20 border-r border-line-board p-1.5 text-left">Kode</th>
                                    <th class="border-r border-line-board p-1.5 text-left">Nama Komoditas</th>
                                    <th class="w-16 border-r border-line-board p-1.5 text-right">Kirim</th>
                                    <th class="w-16 border-r border-line-board p-1.5 text-right">Retur</th>
                                    <th class="w-20 border-r border-line-board bg-surface-muted p-1.5 text-right">Diterima</th>
                                    <th class="p-1.5 text-left">Keterangan / Alasan QC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $index => $item)
                                    <tr @class([
                                        'border-b border-line-board',
                                        'bg-surface-shell/60' => $index % 2 === 1,
                                    ])>
                                        <td class="border-r border-line-board p-1.5 text-center font-mono text-ink-body">{{ $index + 1 }}</td>
                                        <td class="border-r border-line-board p-1.5 font-mono font-semibold text-ink-strong">{{ $item['code'] }}</td>
                                        <td class="border-r border-line-board p-1.5 font-medium text-ink-strong">{{ $item['name'] }}</td>
                                        <td class="border-r border-line-board p-1.5 text-right font-mono text-ink-body">{{ number_format($item['shipped'], 1, ',', '.') }}</td>
                                        <td @class([
                                            'border-r border-line-board p-1.5 text-right font-mono',
                                            'font-bold text-danger underline' => $item['returned'] > 0,
                                            'text-ink-muted' => $item['returned'] === 0.0,
                                        ])>{{ number_format($item['returned'], 1, ',', '.') }}</td>
                                        <td class="border-r border-line-board bg-surface-shell p-1.5 text-right font-mono font-semibold text-ink-strong">{{ number_format($item['accepted'], 1, ',', '.') }}</td>
                                        <td class="p-1.5 leading-tight {{ $item['returned'] > 0 ? 'text-ink-strong' : 'italic text-ink-muted' }}">{{ $item['reason'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="border-t-2 border-brand bg-surface-shell font-bold">
                                    <th colspan="3" class="border-r border-brand p-2 text-right uppercase tracking-wider text-ink-strong">
                                        Total Akumulasi Fisik Muatan
                                    </th>
                                    <td class="border-r border-brand p-2 text-right font-mono text-ink-strong">{{ number_format($totals['shipped'], 1, ',', '.') }} kg</td>
                                    <td class="border-r border-brand p-2 text-right font-mono text-danger">{{ number_format($totals['returned'], 1, ',', '.') }} kg</td>
                                    <td class="border-r border-brand bg-surface-muted p-2 text-right font-mono text-ink-strong">{{ number_format($totals['accepted'], 1, ',', '.') }} kg</td>
                                    <td class="p-2 text-[7pt] font-medium text-ink-body">Validasi timbangan digital dock penerimaan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="mb-4 border border-line-board bg-surface p-3">
                    <h3 class="mb-2.5 border-b border-line-board pb-1.5 gpa-micro-bold uppercase tracking-wider text-brand">
                        Kesepakatan Kompensasi Retur &amp; Rekonsiliasi Faktur
                    </h3>
                    <div class="grid grid-cols-12 items-center gap-3">
                        <div class="col-span-5 border border-line-board bg-surface-shell p-2">
                            <span class="gpa-micro uppercase text-ink-muted">Kalkulasi Valuasi Retur</span>
                            <div class="mt-1 flex items-baseline justify-between gap-2">
                                <span class="font-mono text-[8pt] text-ink-strong">
                                    {{ number_format($totals['returned'], 1, ',', '.') }} kg × Rp {{ number_format($settlement['returned_price'], 0, ',', '.') }} /kg
                                </span>
                                <strong class="font-mono text-[9pt] text-brand">Rp {{ number_format($settlement['returned_value'], 0, ',', '.') }}</strong>
                            </div>
                            <p class="mt-0.5 text-[7pt] text-ink-muted">Ref: {{ $settlement['price_reference'] }}</p>
                        </div>
                        <div class="col-span-7">
                            <p class="mb-1.5 text-[8pt] font-semibold text-ink-strong">Skema rekonsiliasi yang disepakati para pihak:</p>
                            <div class="border border-brand bg-surface-shell p-1.5">
                                <p class="text-[8pt] font-semibold text-ink-strong">X &nbsp; Opsi A: {{ $settlement['selected_option'] }}</p>
                                <p class="pl-5 text-[7pt] text-ink-body">Dijadwalkan {{ $settlement['schedule'] }}.</p>
                            </div>
                            <div class="mt-1 border border-line-board p-1.5 text-ink-muted">
                                <p class="text-[8pt]">{{ $settlement['alternative'] }}</p>
                                <p class="pl-5 text-[7pt]">Faktur diterbitkan sebesar nilai netto penerimaan barang.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-4 border-l-2 border-brand py-1 pl-2.5 text-[7pt] leading-normal text-ink-body">
                    <p><strong class="text-ink-strong">Klausul Pengesahan:</strong> {{ $clause }}</p>
                </section>
            </div>

            <footer class="mt-auto border-t-2 border-brand pt-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h3 class="gpa-micro-bold uppercase tracking-wider text-brand">Pengesahan Tripartit Lapangan</h3>
                    <span class="gpa-micro uppercase text-ink-muted">Tanggal: {{ $document['date'] }}</span>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="flex h-36 flex-col justify-between border border-line-hair bg-surface p-2">
                        <div class="border-b border-line-board pb-1">
                            <p class="gpa-micro-bold uppercase text-ink-muted">Pihak I (Konsumen)</p>
                            <p class="text-[8pt] font-semibold text-ink-strong">Penerima / Chef Receiving Dock</p>
                            <p class="text-[7pt] text-ink-body">{{ $client['name'] }}</p>
                        </div>
                        <span class="self-center border border-dashed border-line-board px-2 py-1 gpa-micro text-ink-muted">Stempel Penerima</span>
                        <div class="border-t border-dotted border-line-board pt-1">
                            <p class="text-[8pt] font-semibold text-ink-strong underline">{{ $client['contact'] }}</p>
                            <p class="gpa-micro text-ink-muted">{{ $client['role'] }}</p>
                        </div>
                    </div>
                    <div class="flex h-36 flex-col justify-between border border-line-hair bg-surface p-2">
                        <div class="border-b border-line-board pb-1">
                            <p class="gpa-micro-bold uppercase text-ink-muted">Pihak II (Distribusi)</p>
                            <p class="text-[8pt] font-semibold text-ink-strong">Pengangkut / Supir Armada</p>
                            <p class="text-[7pt] text-ink-body">Armada GPA (Sentul Hub)</p>
                        </div>
                        <span class="self-center border border-dashed border-line-board px-2 py-1 gpa-micro text-ink-muted">Tanda Tangan Supir</span>
                        <div class="border-t border-dotted border-line-board pt-1">
                            <p class="text-[8pt] font-semibold text-ink-strong underline">{{ $driver['name'] }}</p>
                            <p class="gpa-micro text-ink-muted">ID {{ $driver['id'] }} ({{ $driver['vehicle'] }})</p>
                        </div>
                    </div>
                    <div class="flex h-36 flex-col justify-between border border-brand bg-surface-shell p-2">
                        <div class="border-b border-line-board pb-1">
                            <p class="gpa-micro-bold uppercase text-ink-muted">Pihak III (Verifikator)</p>
                            <p class="text-[8pt] font-semibold text-ink-strong">Koordinator Lapangan</p>
                            <p class="text-[7pt] text-ink-body">Quality Assurance Hub GPA</p>
                        </div>
                        <span class="self-center border-2 border-brand bg-surface px-3 py-1 gpa-micro-bold text-brand">Verified QA GPA</span>
                        <div class="border-t border-dotted border-line-board pt-1">
                            <p class="text-[8pt] font-semibold text-ink-strong underline">{{ $coordinator['name'] }}</p>
                            <p class="gpa-micro text-ink-muted">{{ $coordinator['role'] }}</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </main>
</body>

</html>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faktur {{ $invoice['id'] }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .no-print {
                display: none !important;
            }

            .a4-canvas {
                margin: 0 auto;
                box-shadow: none;
                page-break-after: auto;
            }
        }

        @media screen {
            body {
                background: #f8faf3;
            }
        }

        .a4-canvas {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            background: #ffffff;
            box-shadow: 0 10px 30px -20px rgba(12, 36, 1, 0.9);
            font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.6;
            color: #191c18;
        }
    </style>
</head>

<body>
    <div class="no-print sticky top-0 z-20 border-b border-line bg-surface/95 backdrop-blur">
        <div class="mx-auto flex max-w-[210mm] items-center justify-between gap-3 px-4 py-2">
            <div class="flex items-center gap-2">
                <a href="{{ route('secretary.invoicing') }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-line bg-surface px-2 py-1 text-[11px] font-medium text-ink transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="arrow-left" class="h-3 w-3" />
                    Kembali
                </a>
                <span class="rounded bg-surface-pill px-2 py-1 font-mono text-[10px] font-semibold uppercase tracking-widest text-ink-muted">
                    FAKTUR / INVOICE
                </span>
            </div>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1 text-[11px] font-semibold text-white shadow-sub transition-colors hover:bg-brand-hover">
                <x-gpa.icon name="printer" class="h-3 w-3" />
                Cetak Faktur (Ctrl+P)
            </button>
        </div>
    </div>

    <main class="a4-canvas">
        <div class="flex h-full flex-col px-8 py-6">
            <header class="flex items-start justify-between gap-6 border-b-2 border-brand pb-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-line-hair bg-surface-muted">
                        <x-gpa.brand-mark class="h-8 w-8 text-brand" />
                    </div>
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-widest text-ink-strong">{{ $brand }}</p>
                        <p class="mt-0.5 text-[8pt] leading-relaxed text-ink-body">
                            @foreach ($company_lines as $line)
                                {{ $line }}<br>
                            @endforeach
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[9pt] font-bold uppercase tracking-widest text-ink-strong">
                        FAKTUR / INVOICE PENAGIHAN
                    </p>
                    <dl class="mt-1 space-y-0.5 text-[8pt]">
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-muted">No. Faktur:</dt>
                            <dd class="font-mono font-semibold text-ink-strong">{{ $invoice['id'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-muted">Kontrak:</dt>
                            <dd class="font-mono font-semibold text-ink-strong">{{ $client['contract'] }}</dd>
                        </div>
                    </dl>
                </div>
            </header>

            <section class="mt-4 grid grid-cols-2 divide-x divide-y divide-line-hair border border-line-hair sm:grid-cols-4 sm:divide-y-0">
                <div class="p-2.5">
                    <span class="gpa-micro uppercase text-ink-muted">Tanggal Terbit</span>
                    <p class="font-mono text-xs font-semibold text-ink-strong">{{ $invoice['issued'] }}</p>
                </div>
                <div class="p-2.5">
                    <span class="gpa-micro uppercase text-ink-muted">Jatuh Tempo</span>
                    <p class="font-mono text-xs font-semibold text-ink-strong">{{ $invoice['due'] }}</p>
                </div>
                <div class="p-2.5">
                    <span class="gpa-micro uppercase text-ink-muted">Syarat Bayar</span>
                    <p class="font-mono text-xs font-semibold text-ink-strong">{{ $invoice['terms_label'] }}</p>
                </div>
                <div class="p-2.5">
                    <span class="gpa-micro uppercase text-ink-muted">Surat Jalan</span>
                    <p class="font-mono text-xs font-semibold text-ink-strong">{{ $totals['document_count'] }} dokumen</p>
                </div>
            </section>

            <section class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="border border-line-hair bg-surface">
                    <div class="border-b border-line-hair bg-brand px-3 py-2">
                        <p class="text-[9pt] font-bold uppercase tracking-widest text-white">Tagihan Kepada</p>
                    </div>
                    <div class="space-y-1 px-3 py-2 text-[8pt]">
                        <p class="font-semibold text-ink-strong">{{ $client['name'] }}</p>
                        <p class="text-ink-body">Kontrak: {{ $client['contract'] }}</p>
                        <p class="text-ink-body">NPWP: {{ $client['npwp'] }}</p>
                    </div>
                </div>
                <div class="border border-line-hair bg-surface">
                    <div class="border-b border-line-hair bg-brand px-3 py-2">
                        <p class="text-[9pt] font-bold uppercase tracking-widest text-white">Rekening Pembayaran</p>
                    </div>
                    <div class="space-y-1 px-3 py-2 text-[8pt]">
                        <p class="text-ink-body">Pembayaran ditujukan ke rekening resmi perusahaan:</p>
                        <p class="flex justify-between gap-2"><span class="text-ink-muted">Bank / Cabang</span><strong class="text-ink-strong">{{ $payment['bank'] }} · {{ $payment['branch'] }}</strong></p>
                        <p class="flex justify-between gap-2"><span class="text-ink-muted">No. Rekening</span><strong class="font-mono text-ink-strong">{{ $payment['account'] }}</strong></p>
                        <p class="flex justify-between gap-2"><span class="text-ink-muted">Atas Nama</span><strong class="text-ink-strong">{{ $payment['name'] }}</strong></p>
                    </div>
                </div>
            </section>

            <section class="mt-5 flex-1">
                <div class="overflow-hidden border border-line-hair">
                    <table class="w-full border-collapse text-[8pt]">
                        <thead>
                            <tr class="bg-brand text-white">
                                <th class="w-10 px-2 py-1.5 text-center font-semibold uppercase tracking-widest">No</th>
                                <th class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">Referensi SJ</th>
                                <th class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">Rincian Komoditas</th>
                                <th class="w-20 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">Netto (kg)</th>
                                <th class="w-24 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">Harga (Rp/kg)</th>
                                <th class="w-28 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr class="border-b border-line-hair {{ $index % 2 === 1 ? 'bg-surface-shell/60' : '' }}">
                                    <td class="px-2 py-1.5 text-center text-ink-body">{{ $index + 1 }}</td>
                                    <td class="px-2 py-1.5 font-mono text-ink-body">{{ $item['code'] }}</td>
                                    <td class="px-2 py-1.5 text-ink-body">{{ $item['name'] }}</td>
                                    <td class="px-2 py-1.5 text-right font-mono text-ink-body">{{ number_format($item['qty'], 1, ',', '.') }}</td>
                                    <td class="px-2 py-1.5 text-right font-mono text-ink-body">{{ number_format($item['price'], 2, ',', '.') }}</td>
                                    <td class="px-2 py-1.5 text-right font-mono text-ink-body">{{ number_format($item['amount'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr class="border-b border-line-hair">
                                    <td colspan="6" class="px-2 py-3 text-center text-ink-muted">
                                        Rincian mengikuti dokumen Surat Jalan terlampir.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-brand text-white">
                                <th colspan="3" class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">
                                    Total Tagihan · {{ $totals['document_count'] }} Surat Jalan
                                </th>
                                <th class="px-2 py-1.5 text-right font-mono font-semibold">
                                    {{ number_format($totals['qty'], 1, ',', '.') }} kg
                                </th>
                                <th class="px-2 py-1.5 text-right">&nbsp;</th>
                                <th class="px-2 py-1.5 text-right font-mono font-semibold">
                                    Rp {{ number_format($totals['amount'], 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-3 flex items-start justify-between gap-4 border border-line-hair bg-surface-shell px-3 py-2">
                    <p class="text-[8pt] text-ink-body">
                        Jatuh tempo {{ $invoice['due'] }} · {{ $invoice['terms_label'] }}. Mohon cantumkan nomor faktur pada bukti pembayaran.
                    </p>
                    <p class="shrink-0 text-right text-[9pt] font-bold text-ink-strong">
                        TOTAL: Rp {{ number_format($totals['amount'], 0, ',', '.') }}
                    </p>
                </div>
            </section>

            <section class="mt-6 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Diterbitkan Oleh', 'role' => 'Sekretariat / Admin Keuangan'],
                    ['label' => 'Disetujui Oleh', 'role' => 'Manajer Keuangan'],
                    ['label' => 'Diterima Oleh', 'role' => 'Perwakilan Klien'],
                ] as $signature)
                    <div class="flex min-h-32 flex-col border border-line-hair bg-surface px-3 py-2">
                        <p class="text-center text-[8pt] font-semibold uppercase tracking-wide text-ink-strong">{{ $signature['label'] }}</p>
                        <p class="text-center text-[7pt] text-ink-muted">{{ $signature['role'] }}</p>
                        <div class="mt-auto border-b border-dashed border-line-strong pt-12"></div>
                        <p class="pt-1 text-center text-[7pt] text-ink-muted">Nama &amp; tanggal</p>
                    </div>
                @endforeach
            </section>
        </div>
    </main>
</body>

</html>

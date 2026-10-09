@props([
    'brand' => 'PT AGRO PASTI ADA',
    'emblem' => null,
    'doc' => [
        'number' => 'SJ-GPA-202610-0115',
        'po' => '#ORD-GPA-202610-0042',
        'date' => '24 Oktober 2026',
        'time' => '04:30 WIB (Pagi)',
    ],
    'sender' => [
        'title' => 'PT AGRO PASTI ADA',
        'lines' => [
            'Jl. Pergudangan Agroniaga Kav. 14',
            'Desa/Kec. Subang, Kab. Subang - Jawa Barat 41285',
            'Telp: (0260) 412-9088 | Fax: -',
        ],
    ],
    'recipient' => [
        'title' => 'TUJUAN PENGIRIMAN / PENERIMA',
        'name' => 'PT KULINER PRIMA NUSANTARA',
        'lines' => [
            'Central Kitchen Ciracas Hub',
            'Jl. Raya Bogor KM 28, Jakarta Timur',
            'PIC: Pak Hendra Gunawan | HP: 0812-3456-7890',
        ],
    ],
    'transporter' => [
        'title' => 'DETAIL PENGANGKUTAN / EKSPEDISI',
        'lines' => [
            'Armada: D 8888 ABC | Type: Reefer 4 Ton',
            'Supir: Budi Santoso | SIM: B 12345678',
            'Sopir HP: 0812-1111-2222',
        ],
    ],
    'items' => [
        [
            'code' => 'VEG-ROM-01',
            'name' => 'Selada Romaine Super',
            'qty' => 497.0,
            'price' => 15550,
            'amount' => 7730650,
        ],
    ],
    'totals' => [
        'qty' => 497.0,
        'amount' => 7730650,
    ],
    'signatures' => [
        [
            'label' => 'Diserahkan Oleh',
            'role' => 'Koordinator Gudang',
            'name' => 'Ir. Bambang Sutrisno',
            'date' => '24/10/2026',
        ],
        [
            'label' => 'Pengangkut / Sopir',
            'role' => 'Driver Logistik',
            'name' => 'Budi Santoso',
            'date' => '24/10/2026',
        ],
        [
            'label' => 'Diterima Lengkap Oleh',
            'role' => 'Penerima Barang',
            'name' => '________________________',
            'date' => '____/____/____',
        ],
    ],
    'footer' => 'Dokumen ini sah diterbitkan oleh PT Agro Pasti Ada. Distribusi Rantai Pasok Segar Agrikultur Nasional.',
    'showSheet' => true,
    'sheetLabel' => 'LEMBAR 1 / 3',
])

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan {{ $doc['number'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-line bg-surface px-2 py-1 text-[11px] font-medium text-ink transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="arrow-left" class="h-3 w-3" />
                    Kembali
                </a>
                @if ($showSheet)
                    <span class="rounded bg-surface-pill px-2 py-1 font-mono text-[10px] font-semibold uppercase tracking-widest text-ink-muted">
                        {{ $sheetLabel }}
                    </span>
                @endif
            </div>
            <button type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1 text-[11px] font-semibold text-white shadow-sub transition-colors hover:bg-brand-hover">
                <x-gpa.icon name="printer" class="h-3 w-3" />
                Cetak Surat Jalan (Ctrl+P)
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
                        <p class="text-sm font-extrabold uppercase tracking-widest text-ink-strong">
                            {{ $brand }}
                        </p>
                        <p class="mt-0.5 text-[8pt] leading-relaxed text-ink-body">
                            @foreach ($sender['lines'] as $line)
                                {{ $line }}<br>
                            @endforeach
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[9pt] font-bold uppercase tracking-widest text-ink-strong">
                        SURAT JALAN
                    </p>
                    <dl class="mt-1 space-y-0.5 text-[8pt]">
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-muted">No. SJ:</dt>
                            <dd class="font-mono font-semibold text-ink-strong">{{ $doc['number'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-muted">No. PO:</dt>
                            <dd class="font-mono font-semibold text-ink-strong">{{ $doc['po'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-muted">Tgl. Kirim:</dt>
                            <dd class="text-ink-body">{{ $doc['date'] }} | {{ $doc['time'] }}</dd>
                        </div>
                    </dl>
                </div>
            </header>

            <section class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="border border-line-hair bg-surface">
                    <div class="border-b border-line-hair bg-brand px-3 py-2">
                        <p class="text-[9pt] font-bold uppercase tracking-widest text-white">
                            {{ $recipient['title'] }}
                        </p>
                    </div>
                    <div class="space-y-1 px-3 py-2 text-[8pt]">
                        <p class="font-semibold text-ink-strong">{{ $recipient['name'] }}</p>
                        @foreach ($recipient['lines'] as $line)
                            <p class="text-ink-body">{{ $line }}</p>
                        @endforeach
                    </div>
                </div>
                <div class="border border-line-hair bg-surface">
                    <div class="border-b border-line-hair bg-brand px-3 py-2">
                        <p class="text-[9pt] font-bold uppercase tracking-widest text-white">
                            {{ $transporter['title'] }}
                        </p>
                    </div>
                    <div class="space-y-1 px-3 py-2 text-[8pt]">
                        @foreach ($transporter['lines'] as $line)
                            <p class="text-ink-body">{{ $line }}</p>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="mt-4 flex-1">
                <div class="overflow-hidden border border-line-hair">
                    <table class="w-full border-collapse text-[8pt]">
                        <thead>
                            <tr class="bg-brand text-white">
                                <th class="w-10 px-2 py-1.5 text-center font-semibold uppercase tracking-widest">
                                    NO
                                </th>
                                <th class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">
                                    KODE BARANG
                                </th>
                                <th class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">
                                    NAMA BARANG
                                </th>
                                <th class="w-24 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">
                                    QTY (KG)
                                </th>
                                <th class="w-28 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">
                                    HARGA (RP)
                                </th>
                                <th class="w-32 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">
                                    JUMLAH (RP)
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr class="border-b border-line-hair">
                                    <td class="px-2 py-1.5 text-center text-ink-body">{{ $index + 1 }}</td>
                                    <td class="px-2 py-1.5 font-mono text-ink-body">{{ $item['code'] ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-ink-body">{{ $item['name'] }}</td>
                                    <td class="px-2 py-1.5 text-right font-mono text-ink-body">
                                        {{ number_format($item['qty'], 1, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-1.5 text-right font-mono text-ink-body">
                                        {{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-1.5 text-right font-mono text-ink-body">
                                        {{ number_format($item['amount'] ?? ($item['qty'] * ($item['price'] ?? 0)), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b border-line-hair">
                                    <td colspan="6" class="px-2 py-3 text-center text-ink-muted">Tidak ada rincian muatan</td>
                                </tr>
                            @endforelse
                            @for ($i = count($items); $i < 3; $i++)
                                <tr class="border-b border-line-hair">
                                    <td class="px-2 py-1.5">&nbsp;</td>
                                    <td class="px-2 py-1.5">&nbsp;</td>
                                    <td class="px-2 py-1.5">&nbsp;</td>
                                    <td class="px-2 py-1.5">&nbsp;</td>
                                    <td class="px-2 py-1.5">&nbsp;</td>
                                    <td class="px-2 py-1.5">&nbsp;</td>
                                </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            <tr class="bg-brand text-white">
                                <th colspan="3" class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">
                                    TOTAL AKUMULASI MUATAN &amp; NILAI BARANG
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
            </section>

            <section class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ($signatures as $signature)
                    <div class="flex flex-col bg-surface">
                        <div class="px-3 py-2">
                            <p class="text-[9pt] font-semibold text-ink-strong text-center">{{ $signature['label'] }}</p>
                            <p class="text-[8pt] font-semibold text-ink-muted text-center">{{ $signature['role'] ?? '' }}</p>
                        </div>
                        <div class="flex flex-1 flex-col items-center justify-end px-3 py-4 text-center">
                            <div class="h-12 w-full border-b border-dashed border-line-strong"></div>
                            <p class="mt-2 text-[8pt] font-semibold text-ink-strong">
                                {{ $signature['name'] }}
                            </p>
                            <p class="text-[8pt] text-ink-muted">Tgl: {{ $signature['date'] ?? '-' }}</p>
                        </div>
                    </div>
                @endforeach
            </section>

        </div>
    </main>
</body>

</html>

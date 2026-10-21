@props([
    'brand' => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
    'emblem' => null,
    'doc' => [
        'number' => 'SJ-GPA-202610-0115',
        'po' => '#ORD-GPA-202610-0042',
        'date' => date('d F Y'),
        'time' => date('H:i') . ' WIB',
    ],
    'sender' => [
        'title' => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN',
        'lines' => [
            'Sentra Agribisnis Sayuran Segar Dataran Tinggi',
            'Desa Panundaan, Kec. Ciwidey, Kab. Bandung - Jawa Barat 40973',
            'Telp: (022) 592-8011 | Email: logistik@agroorder.co.id',
        ],
    ],
    'recipient' => [
        'title' => 'TUJUAN PENGIRIMAN / PENERIMA',
        'name' => 'PT KULINER PRIMA NUSANTARA',
        'lines' => [
            'Central Kitchen Ciracas Hub',
            'Jl. Raya Bogor KM 28, Jakarta Timur',
            'PIC: Hendra Gunawan | HP: 0812-3456-7890',
        ],
    ],
    'transporter' => [
        'title' => 'DETAIL PENGANGKUTAN / EKSPEDISI',
        'lines' => [
            'Armada: D 8921 GPA | Type: Reefer 4 Ton Suhu Terkontrol',
            'Supir: Budi Santoso | SIM: B 12345678',
            'Sopir HP: 0812-1111-2222',
        ],
    ],
    'items' => [
        [
            'code' => 'GPA-ROM-01',
            'name' => 'Selada Romaine Super',
            'qty' => 497.0,
            'price' => 15500,
            'amount' => 7703500,
        ],
    ],
    'totals' => [
        'qty' => 497.0,
        'amount' => 7703500,
    ],
    'signatures' => [
        [
            'label' => 'Diserahkan Oleh',
            'role' => 'Koordinator Gudang GPA',
            'name' => 'Bambang Sugiarto',
            'date' => date('d/m/Y'),
        ],
        [
            'label' => 'Pengangkut / Supir',
            'role' => 'Driver Logistik',
            'name' => 'Budi Santoso',
            'date' => date('d/m/Y'),
        ],
        [
            'label' => 'Diterima Lengkap Oleh',
            'role' => 'Penerima Barang',
            'name' => '________________________',
            'date' => '____/____/____',
        ],
    ],
    'footer' => 'Dokumen ini sah diterbitkan oleh Koperasi Produsen Agro Green Pasundan (GPA). Distribusi Rantai Pasok Segar Agrikultur.',
    'showSheet' => true,
    'sheetLabel' => 'LEMBAR 1 / 3 (ASLI)',
])

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan {{ $doc['number'] }} &middot; GPA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        @media print {
            body {
                background: #ffffff !important;
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
    <div class="no-print sticky top-0 z-20 border-b border-line-board bg-surface/95 backdrop-blur">
        <div class="mx-auto flex max-w-[210mm] items-center justify-between gap-3 px-4 py-2">
            <div class="flex items-center gap-2">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-line-board bg-surface px-2.5 py-1 text-[11px] font-medium text-ink transition-colors hover:bg-surface-shell">
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
                class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-1 text-[11px] font-semibold text-white shadow-sub transition-colors hover:bg-brand-deep">
                <x-gpa.icon name="printer" class="h-3 w-3" />
                Cetak Surat Jalan (Ctrl+P)
            </button>
        </div>
    </div>

    <main class="a4-canvas">
        <div class="flex h-full flex-col px-8 py-6">
            <header class="flex items-start justify-between gap-6 border-b-2 border-brand pb-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-line-hair bg-surface-shell">
                        <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-8 w-8 object-contain">
                    </div>
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-widest text-ink">
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
                    <p class="text-[10pt] font-bold uppercase tracking-widest text-brand">
                        SURAT JALAN PENGIRIMAN
                    </p>
                    <dl class="mt-1 space-y-0.5 text-[8pt]">
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-quiet">No. SJ:</dt>
                            <dd class="font-mono font-semibold text-ink">{{ $doc['number'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-quiet">No. PO:</dt>
                            <dd class="font-mono font-semibold text-ink">{{ $doc['po'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-end gap-2">
                            <dt class="text-ink-quiet">Tgl. Kirim:</dt>
                            <dd class="text-ink-body">{{ $doc['date'] }} | {{ $doc['time'] }}</dd>
                        </div>
                    </dl>
                </div>
            </header>

            <section class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="border border-line-hair bg-surface">
                    <div class="border-b border-line-hair bg-brand px-3 py-1.5">
                        <p class="text-[9pt] font-bold uppercase tracking-widest text-white">
                            {{ $recipient['title'] }}
                        </p>
                    </div>
                    <div class="space-y-1 px-3 py-2 text-[8pt]">
                        <p class="font-semibold text-ink">{{ $recipient['name'] }}</p>
                        @foreach ($recipient['lines'] as $line)
                            <p class="text-ink-body">{{ $line }}</p>
                        @endforeach
                    </div>
                </div>
                <div class="border border-line-hair bg-surface">
                    <div class="border-b border-line-hair bg-brand px-3 py-1.5">
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
                                    KODE SKU
                                </th>
                                <th class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">
                                    NAMA KOMODITAS
                                </th>
                                <th class="w-24 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">
                                    NETTO TERA (KG)
                                </th>
                                <th class="w-28 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">
                                    HARGA SATUAN (RP)
                                </th>
                                <th class="w-32 px-2 py-1.5 text-right font-semibold uppercase tracking-widest">
                                    NILAI TOTAL (RP)
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr class="border-b border-line-hair">
                                    <td class="px-2 py-1.5 text-center text-ink-body">{{ $index + 1 }}</td>
                                    <td class="px-2 py-1.5 font-mono text-ink-body">{{ $item['code'] ?? '-' }}</td>
                                    <td class="px-2 py-1.5 text-ink-body font-medium">{{ $item['name'] }}</td>
                                    <td class="px-2 py-1.5 text-right font-mono font-semibold text-ink">
                                        {{ number_format($item['qty'], 1, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-1.5 text-right font-mono text-ink-body">
                                        Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-1.5 text-right font-mono font-semibold text-ink">
                                        Rp {{ number_format($item['amount'] ?? ($item['qty'] * ($item['price'] ?? 0)), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b border-line-hair">
                                    <td colspan="6" class="px-2 py-3 text-center text-ink-muted">Tidak ada rincian muatan</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-brand text-white">
                                <th colspan="3" class="px-2 py-1.5 text-left font-semibold uppercase tracking-widest">
                                    TOTAL MUATAN BERSIH TERA &amp; VALUASI
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

            <section class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ($signatures as $signature)
                    <div class="flex flex-col border border-line-hair bg-surface">
                        <div class="border-b border-line-hair bg-surface-shell px-3 py-1.5 text-center">
                            <p class="text-[9pt] font-semibold text-ink">{{ $signature['label'] }}</p>
                            <p class="text-[8pt] text-ink-quiet">{{ $signature['role'] ?? '' }}</p>
                        </div>
                        <div class="flex flex-1 flex-col items-center justify-end px-3 py-4 text-center">
                            <div class="h-16 w-full border-b border-dashed border-line-board"></div>
                            <p class="mt-2 text-[8pt] font-semibold text-ink underline">
                                {{ $signature['name'] }}
                            </p>
                            <p class="text-[8pt] text-ink-quiet">Tgl: {{ $signature['date'] ?? '-' }}</p>
                        </div>
                    </div>
                @endforeach
            </section>

            <footer class="mt-8 border-t border-line-hair pt-3 text-center text-[7.5pt] text-ink-quiet">
                <p>{{ $footer }}</p>
            </footer>

        </div>
    </main>
</body>

</html>

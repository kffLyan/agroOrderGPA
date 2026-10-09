<?php

namespace App\Support;

use Illuminate\Support\Str;

class SuratJalanData
{
    /**
     * @return array<string, mixed>
     */
    public static function document(): array
    {
        $row = collect(SecretaryDispatchData::rows())->firstWhere('state', 'ready');
        $draft = $row['document'];
        $letterhead = SecretaryDispatchData::letterhead();
        $security = SecretaryDispatchData::security();

        preg_match('/^(.+?)\s*\((.+)\)$/', $row['issued_at'], $issued);
        $date = $issued[1];
        $time = $issued[2];
        $shortDate = self::shortDate($date);

        $items = collect($draft['lines'])->map(function (array $line): array {
            $commodity = self::commodity($line['name']);
            $qty = (float) str_replace(' kg', '', (string) $line['netto']);

            return [
                'code' => $commodity['sku'],
                'name' => $line['name'],
                'qty' => $qty,
                'price' => $commodity['price'],
                'amount' => $qty * $commodity['price'],
            ];
        })->values()->all();

        return [
            'brand' => strtoupper($letterhead['company']),
            'doc' => [
                'number' => $row['sj'],
                'po' => '#'.$row['id'],
                'date' => $date,
                'time' => $time,
            ],
            'sender' => [
                'title' => strtoupper($letterhead['company']),
                'lines' => [
                    $letterhead['address_line'],
                    $letterhead['address'],
                    $letterhead['contact'],
                ],
            ],
            'recipient' => [
                'title' => 'TUJUAN PENGIRIMAN',
                'name' => strtoupper($draft['client']),
                'lines' => [
                    $draft['drop_point'],
                    $draft['address'],
                    $draft['pic'],
                ],
            ],
            'transporter' => [
                'title' => 'DETAIL PENGANGKUTAN',
                'lines' => [
                    'Armada: '.$draft['armada'].' | No. Polisi: '.$draft['plate'],
                    'Supir: '.$draft['driver'].' | '.$draft['sim'],
                ],
            ],
            'items' => $items,
            'totals' => [
                'qty' => array_sum(array_column($items, 'qty')),
                'amount' => array_sum(array_column($items, 'amount')),
            ],
            'signatures' => collect(SecretaryDispatchData::signatures())
                ->map(fn (array $signature, int $index): array => [
                    'label' => $signature['label'],
                    'role' => $signature['role'],
                    'name' => $signature['name'],
                    'date' => $index < 2 ? $shortDate : '____/____/____',
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{sku: string, price: int}
     */
    private static function commodity(string $lineName): array
    {
        $word = Str::before(trim($lineName), ' ');

        $match = collect(ClientCatalogData::commodities())
            ->first(fn (array $item): bool => Str::startsWith(strtolower($item['name']), strtolower($word)));

        if ($match === null) {
            return ['sku' => '-', 'price' => 0];
        }

        return ['sku' => $match['sku'], 'price' => $match['price']];
    }

    private static function shortDate(string $date): string
    {
        $months = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
        ];

        $parts = preg_split('/\s+/', trim($date));

        if (count($parts) !== 3 || ! isset($months[$parts[1]])) {
            return $date;
        }

        return $parts[0].'/'.$months[$parts[1]].'/'.$parts[2];
    }
}

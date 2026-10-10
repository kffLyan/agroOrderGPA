<?php

namespace App\Support;

use App\Models\User;

class CoordinatorReturnsData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => CoordinatorDashboardData::operator($user),
            'header' => [
                'title' => 'Laporan Rekapitulasi Pasokan & Timbangan',
                'subtitle' => 'Pantau hasil timbang, susut, dan mutasi retur dari seluruh sentra.',
            ],
            'filters' => [
                'periods' => [
                    ['value' => 'month-2024-11', 'label' => 'Bulan Berjalan (November 2024)'],
                    ['value' => 'week-46', 'label' => 'Minggu Ini (W-46)'],
                    ['value' => 'day-2024-11-18', 'label' => 'Hari Ini (18 Nov 2024)'],
                ],
                'hubs' => [
                    ['value' => 'all', 'label' => 'Semua Sentra Jawa Barat'],
                    ['value' => 'subang-04', 'label' => 'Hub Subang-04 (Aktif)'],
                    ['value' => 'lembang-02', 'label' => 'Sentra Lembang (Highland-02)'],
                ],
                'default_period' => 'month-2024-11',
                'default_hub' => 'all',
            ],
            'batch_rows' => self::batchRows(),
            'mutation_rows' => self::mutationRows(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function batchRows(): array
    {
        return [
            [
                'code' => 'BTC-SBG-RM-081',
                'datetime' => '2024-11-18 06:45',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'subang-04',
                'farmer' => 'Poktan Mekar Subur Mandiri',
                'farmer_note' => 'Pak Tatang / Blok C-04 (Binaan GPA)',
                'commodity' => 'Selada Romaine',
                'commodity_note' => 'Hidroponik GreenHouse #2',
                'gross' => 8450,
                'net' => 8360,
                'loss' => 90,
                'loss_percent' => 1.06,
                'status' => 'VALID TERA',
                'status_tone' => 'valid',
                'officer' => 'R. Hidayat',
                'officer_code' => 'NIP. QC-882-01',
            ],
            [
                'code' => 'BTC-SBG-TB-082',
                'datetime' => '2024-11-18 07:30',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'subang-04',
                'farmer' => 'Poktan Sari Alam Ciater',
                'farmer_note' => 'Bu Imas / Lembah Subang (Binaan GPA)',
                'commodity' => 'Tomat Beef',
                'commodity_note' => 'Grade Super 180-220g',
                'gross' => 11200,
                'net' => 11080,
                'loss' => 120,
                'loss_percent' => 1.07,
                'status' => 'VALID TERA',
                'status_tone' => 'valid',
                'officer' => 'A. Sugandi',
                'officer_code' => 'NIP. QC-882-04',
            ],
            [
                'code' => 'BTC-LMB-BR-083',
                'datetime' => '2024-11-18 08:15',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'lembang-02',
                'farmer' => 'Gapoktan Puncak Makmur',
                'farmer_note' => 'Kang Deden / Sub-Station Lembang (Mitra)',
                'commodity' => 'Brokoli Highland',
                'commodity_note' => 'Crown Florette Compact 450g',
                'gross' => 9300,
                'net' => 9190,
                'loss' => 110,
                'loss_percent' => 1.18,
                'status' => 'VALID TERA',
                'status_tone' => 'valid',
                'officer' => 'R. Hidayat',
                'officer_code' => 'NIP. QC-882-01',
            ],
            [
                'code' => 'BTC-CWD-ST-084',
                'datetime' => '2024-11-18 09:10',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'ciwidey-01',
                'farmer' => 'Kelompok Tani Berkah Strawberry',
                'farmer_note' => 'Pak Yayan / Ciwidey Rancabali (Binaan)',
                'commodity' => 'Stroberi Ciwidey',
                'commodity_note' => 'Punnet Pack 250g (Fresh Pick)',
                'gross' => 3400,
                'net' => 3330,
                'loss' => 70,
                'loss_percent' => 2.05,
                'status' => 'RETUR PARSIAL',
                'status_tone' => 'return',
                'officer' => 'M. Firman',
                'officer_code' => 'NIP. QC-882-02',
            ],
            [
                'code' => 'BTC-PGL-KP-085',
                'datetime' => '2024-11-18 10:00',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'pangalengan-01',
                'farmer' => 'Poktan Barokah Tani Pangalengan',
                'farmer_note' => 'H. Koswara / Sentra Pangalengan (Mitra)',
                'commodity' => 'Kol Putih',
                'commodity_note' => 'Head Round Padat Grade A',
                'gross' => 10500,
                'net' => 10420,
                'loss' => 80,
                'loss_percent' => 0.76,
                'status' => 'VALID TERA',
                'status_tone' => 'valid',
                'officer' => 'A. Sugandi',
                'officer_code' => 'NIP. QC-882-04',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function mutationRows(): array
    {
        return [
            [
                'id' => 'BA-RET-091',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'ciwidey-01',
                'source' => 'Poktan Berkah Strawberry',
                'commodity' => 'Stroberi Ciwidey',
                'movement' => '3.330 kg (In)',
                'movement_kg' => 3330,
                'return' => -70,
                'description' => 'Kompensasi kuota tebas minggu depan (kelembapan tinggi saat pengangkutan)',
                'status' => 'BA DITERBITKAN',
                'status_tone' => 'return',
            ],
            [
                'id' => 'BUF-SBG-014',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'subang-04',
                'source' => 'Buffer Holding Bay Subang',
                'commodity' => 'Selada Romaine',
                'movement' => '+500 kg (Buffer)',
                'movement_kg' => 500,
                'return' => 0,
                'description' => 'Stok cadangan pre-cooling untuk dispatch reefer cold-truck Jakarta Barat',
                'status' => 'TERSEDIA',
                'status_tone' => 'valid',
            ],
            [
                'id' => 'BA-RET-092',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'subang-04',
                'source' => 'Poktan Sari Alam Ciater',
                'commodity' => 'Tomat Beef',
                'movement' => '11.080 kg (In)',
                'movement_kg' => 11080,
                'return' => -120,
                'description' => 'Sortir fisik afkir kulit memar di holding bay; dialihkan ke pasar lokal',
                'status' => 'SELESAI SORTIR',
                'status_tone' => 'muted',
            ],
            [
                'id' => 'DSP-JBR-088',
                'date' => '2024-11-18',
                'week' => 46,
                'hub' => 'jakarta-01',
                'source' => 'Pusat Distribusi Sentral Jakarta',
                'commodity' => 'Semua Komoditas Lolos',
                'movement' => '42.380 kg (Out)',
                'movement_kg' => 42380,
                'return' => 0,
                'description' => 'Disegel ke armada reefer, surat jalan & ledger resmi tertutup',
                'status' => 'DISPATCHED',
                'status_tone' => 'valid',
            ],
        ];
    }
}

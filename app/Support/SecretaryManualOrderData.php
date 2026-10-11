<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data formulir "Input Order Manual" (Sub-01 // Verifikasi Pesanan).
 *
 * Seluruh nilai di bawah adalah data simulasi yang mencerminkan design
 * "Input Order Manual" dan terikat pada Rule 02 (credit limit B2B),
 * Rule 03 (anti overselling berbasis stok sistem), Rule 04 (verifikasi bobot
 * neto saat timbang gudang), serta Rule 11 & 15 (bukti transfer + audit trail).
 */
class SecretaryManualOrderData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => SecretaryDashboardData::operator($user),
            'policy' => self::policy(),
            'source' => self::source(),
            'client' => self::client(),
            'commodities' => self::commodities(),
            'catalog' => self::catalog(),
            'logistics' => self::logistics(),
            'payment' => self::payment(),
        ];
    }

    /**
     * Banner kebijakan yang mengunci Supply Chain Guardrails.
     *
     * @return array<string, string>
     */
    public static function policy(): array
    {
        return [
            'eyebrow' => 'Input Order Manual',
            'status' => 'Status: In Draft Entry',
            'body' => 'Seluruh pesanan manual wajib dievaluasi menggunakan basis stok sistem untuk mencegah overselling (Rule 03). Verifikasi fisik bobot neto dilakukan di gudang konsolidasi sebelum penerbitan Surat Jalan resmi (Rule 04 Tolerance).',
            'rules' => 'Rule 02 // Rule 03 // Rule 04',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function source(): array
    {
        return [
            'label' => 'Sumber Order',
            'hint' => 'Kanal resmi penerimaan orderan dari klien B2B.',
            'channels' => [
                ['key' => 'whatsapp', 'label' => 'Chat WhatsApp Bisnis', 'note' => 'Kanal utama orderan B2B.'],
                ['key' => 'phone', 'label' => 'Telepon Langsung', 'note' => 'Order dicatat manual oleh sekretaris.'],
                ['key' => 'memo', 'label' => 'Memo Fisik Lapangan', 'note' => 'Memo bertanda tangan area operasional.'],
            ],
            'active' => 'whatsapp',
            'reference' => 'WA-CHAT-20261024-0891',
            'reference_label' => 'Nomor Referensi / Chat ID',
            'reference_hint' => 'ID chat grup atau|no. thread eksternal.',
            'received' => '24 Okt 2026, 08:15 WIB',
            'received_label' => 'Waktu Chat Masuk',
            'validator' => 'Sekre Siti - ID: OP-4091',
            'validator_label' => 'Petugas Validator',
            'warehouse' => 'HUB-BOGOR-UTARA',
            'warehouse_label' => 'Gudang Pusat',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function client(): array
    {
        return [
            'label' => 'Data Klien',
            'hint' => 'Pilih mode registrasi klien sebelum memasukkan identitas B2B.',
            'modes' => [
                ['key' => 'registered', 'label' => 'Klien Terdaftar B2B'],
                ['key' => 'new', 'label' => 'Klien Baru / Cepat'],
            ],
            'active_mode' => 'registered',
            'name' => 'Hotel Grand Pangrango',
            'code' => 'CLI-B2B-1082',
            'segment' => 'Hotel & Hospitality',
            'contact' => 'Bpk. Ridwan (Executive Chef)',
            'contact_label' => 'PIC Dapur / Penerima',
            'phone' => '0811-901-223',
            'credit' => [
                'label' => 'Kredit & Limit',
                'status' => 'Active Verified',
                'status_note' => 'Legalitas invoice & PO terverifikasi',
                'used' => 11550000,
                'used_label' => 'Limit Terpakai',
                'limit' => 50000000,
                'limit_label' => 'Limit outstanding',
                'remaining' => 38450000,
                'remaining_label' => 'Sisa Plafon Tersedia',
                'percent' => 23.1,
                'percent_label' => 'Realisasi Pemakaian',
                'rule' => 'Rule 02 B2B Credit Limit',
            ],
            'address_label' => 'Titik Bongkar / Alamat Dapur',
            'address' => 'Jl. Raya Pajajaran No. 45, Babakan, Bogor Tengah',
            'dock' => 'Loading Dock B - Truk Engkel Max 4 Ton',
        ];
    }

    /**
     * Baris form awal yang sudah lolos evaluasi stok sistem (Rule 03).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function commodities(): array
    {
        return [
            [
                'key' => 'romaine',
                'name' => 'Selada Romaine',
                'sku' => 'VEG-ROM-002',
                'grade' => 'Grade A Super',
                'unit' => 'kg',
                'min_order' => 10,
                'stock' => 1250,
                'stock_bud' => 950,
                'stock_buffer' => 300,
                'price' => 15000,
                'quantity' => 150,
            ],
            [
                'key' => 'tomat-beef',
                'name' => 'Tomat Beef / Sayur',
                'sku' => 'TOM-BEEF-014',
                'grade' => 'Sortir Standar Hotel',
                'unit' => 'kg',
                'min_order' => 25,
                'stock' => 180,
                'stock_bud' => 150,
                'stock_buffer' => 30,
                'price' => 12500,
                'quantity' => 100,
            ],
            [
                'key' => 'brokoli',
                'name' => 'Brokoli Super',
                'sku' => 'BRK-SUP-007',
                'grade' => 'Kuntum Padat Segar',
                'unit' => 'kg',
                'min_order' => 15,
                'stock' => 450,
                'stock_bud' => 350,
                'stock_buffer' => 100,
                'price' => 28000,
                'quantity' => 100,
            ],
        ];
    }

    /**
     * Katalog Baku untuk aksi "Tambah Baris" pada tabel Komoditas.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function catalog(): array
    {
        return [
            [
                'key' => 'bawang-merah',
                'name' => 'Bawang Merah Super',
                'sku' => 'BWG-MRH-003',
                'grade' => 'Class A Super',
                'unit' => 'kg',
                'min_order' => 20,
                'stock' => 620,
                'stock_bud' => 480,
                'stock_buffer' => 140,
                'price' => 32000,
            ],
            [
                'key' => 'cabai-rawit',
                'name' => 'Cabai Rawit Super',
                'sku' => 'CBR-RWT-009',
                'grade' => 'Cabai Kentang Super',
                'unit' => 'kg',
                'min_order' => 5,
                'stock' => 240,
                'stock_bud' => 180,
                'stock_buffer' => 60,
                'price' => 24500,
            ],
            [
                'key' => 'wortel',
                'name' => 'Wortel Premium',
                'sku' => 'WRT-PRM-005',
                'grade' => 'Dicuci & Potong',
                'unit' => 'kg',
                'min_order' => 15,
                'stock' => 380,
                'stock_bud' => 300,
                'stock_buffer' => 80,
                'price' => 11000,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function logistics(): array
    {
        return [
            'label' => 'Logistik & Pengiriman',
            'hint' => 'Jadwal, kurir, dan catatan khusus tim operasional lapangan.',
            'date_label' => 'Tanggal Rencana Kirim',
            'date' => '10/25/2026',
            'arrival_label' => 'Jendela Tiba di Lokasi',
            'arrival' => 'Pagi (05:00 - 08:00 WIB)',
            'arrival_options' => [
                'Pagi (05:00 - 08:00 WIB)',
                'Siang (11:00 - 13:00 WIB)',
                'Sore (15:00 - 17:00 WIB)',
            ],
            'route_label' => 'Rute Pengiriman',
            'route' => 'RUTE 02 - KORIDOR PAJAJARAN / SUKASARI',
            'route_note' => 'Trayek tetap setiap Tues & Thurs',
            'fleet_label' => 'Alokasi Armada',
            'fleet' => 'ENGKEL-BOX-04 (B-9142-TPA)',
            'fleet_note' => 'Kapasitas 1.2 Ton // Volume 6 Krat',
            'notes_label' => 'Catatan untuk Tim Logistik',
            'notes' => 'Truk box tertutup, jangan tumpuk lebih dari 3 krat tanpa sekat kayu. PIC Dapur Pak Ridwan standby pukul 06:00.',
            'fee_label' => 'Ongkos Kirim (Flat Fee)',
            'fee' => 250000,
            'fee_note' => 'Terhitung',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function payment(): array
    {
        return [
            'label' => 'Metode Pembayaran & Bukti Transfer',
            'hint' => 'Pastikan bukti pembayaran valid sebelum diteruskan ke faktur.',
            'methods' => [
                ['key' => 'transfer', 'label' => 'Transfer Bank Manual'],
                ['key' => 'qris', 'label' => 'QRIS Manual'],
                ['key' => 'cod', 'label' => 'COD (Bayar di Tempat)'],
                ['key' => 'top14', 'label' => 'TOP Tempo 14 Hari (Mitra)'],
            ],
            'active' => 'transfer',
            'evidence_label' => 'Bukti Pembayaran (Evidence)',
            'file' => 'SCREENSHOT_PO_WHATSAPP_RIDWAN.PNG',
            'size' => '840 KB',
            'hash' => '4e9a...b109',
            'hash_note' => 'Timestamp Verified',
            'upload_hint' => 'Unggah bukti transfer (JPG/PNG maks 5MB)',
        ];
    }
}

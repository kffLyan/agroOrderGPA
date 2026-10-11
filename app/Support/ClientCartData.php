<?php

namespace App\Support;

/**
 * Sumber data halaman Keranjang: Formulir Pemesanan & Draft Purchase Order.
 *
 * Angka transaksi masih berupa data simulasi yang mencerminkan design
 * "Formulir Pemesanan & Draft Purchase Order". Baris keranjang dibangun dari
 * ClientCatalogData::commodities() sehingga draft PO di browser tidak pernah
 * menampilkan metadata commodity yang berbeda dengan halaman Katalog.
 */
class ClientCartData
{
    /**
     * @return array<string, mixed>
     */
    public static function draft(): array
    {
        return [
            'breadcrumb' => ['Portal Enterprise', 'Procurement Order'],
            'title' => 'Draft PO #GPA-202410-092',
            'telemetry' => 'Live Telemetry Ready',
            'sync' => 'Sync: Just now',
            'heading' => 'Formulir Pemesanan & Draft Purchase Order',
            'subheading' => 'Review alokasi komoditas agro segar, sesuaikan volume tonase estimasi, dan konfirmasi jadwal armada docking.',
            'cutoff_label' => 'Batas Submit Kirim H+1:',
            'cutoff_value' => '20:00 WIB',
            'po_number' => 'PO-GPA-202410-092',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function warning(): array
    {
        return [
            'chip' => 'Aturan Operasional',
            'title' => 'Peringatan Aturan Bisnis (Rule 04): Estimasi Berat vs Timbangan Real',
            'body' => 'Kuantitas yang Anda masukkan merupakan estimasi pemesanan (Order Quantity). Nilai faktur akhir dan Surat Jalan resmi akan dihitung mutlak berdasarkan Hasil Timbangan Bersih Riil (Actual Net Weight) di gudang sebelum keberangkatan armada (Toleransi susut wajar ±1.5%).',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function itemsSection(): array
    {
        return [
            'step' => '01',
            'title' => 'Daftar Item Komoditas Keranjang',
            'subtitle' => 'terpilih dari alokasi kuota panen dingin Subang & Pangalengan',
            'chip' => 'Cold Chain Direct',
        ];
    }

    /**
     * Baris keranjang contoh untuk mode pratinjau (dipakai hanya ketika draft
     * PO pada browser masih kosong).
     *
     * @return list<array<string, mixed>>
     */
    public static function demoLines(): array
    {
        $quantities = [
            'selada_romaine' => 800,
            'tomat_beef' => 50,
        ];

        $lines = [];

        foreach (ClientCatalogData::commodities() as $commodity) {
            if (! isset($quantities[$commodity['key']])) {
                continue;
            }

            $lines[] = self::line($commodity, $quantities[$commodity['key']]);
        }

        return $lines;
    }

    /**
     * Bentuk baris keranjang yang sama dipakai oleh demo (PHP) maupun draft PO
     * yang disimpan Alpine di localStorage.
     *
     * @param  array<string, mixed>  $commodity
     * @return array<string, mixed>
     */
    public static function line(array $commodity, int $quantity): array
    {
        $quantity = max(0, $quantity);

        return [
            'key' => $commodity['key'],
            'sku' => $commodity['sku'],
            'grade' => $commodity['grade'],
            'name' => $commodity['name'],
            'packaging' => 'Kemasan: '.$commodity['packaging'].' • Origin: '.$commodity['origin'],
            'cold_chain' => $commodity['cold_chain'],
            'price' => $commodity['price'],
            'qty' => $quantity,
            'total' => $commodity['price'] * $quantity,
            'stock' => $commodity['stock'],
            'moq' => $commodity['moq'],
            'step' => $commodity['step'],
            'crate_kg' => $commodity['crate_kg'],
            'pack_label' => $commodity['pack_label'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function delivery(): array
    {
        return [
            'step' => '02',
            'title' => 'Parameter Pengiriman & Logistik',
            'subtitle' => 'Cold-Chain Armada Terintegrasi Termo-Sensor & Jadwal Docking Subuh',
            'date' => [
                'label' => 'Tanggal Pengiriman Armada (Delivery Date)',
                'value' => '25 Oktober 2024',
                'hint' => 'Jadwal panen sore ini sudah dialokasikan otomatis ke batch ini.',
            ],
            'window' => [
                'label' => 'Jendela Waktu Tiba (Receiving Dock Window)',
                'value' => '03:30 - 05:30 WIB',
                'hint' => 'Diselaraskan dengan shift persiapan dapur central (Mencegah thermal shock sayur).',
            ],
            'dock_label' => 'Pilihan Alamat Gudang Penerima / Titik Bongkar (Unloading Dock)',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function docks(): array
    {
        return [
            [
                'key' => 'ciracas',
                'chip' => 'Default Aktif',
                'name' => 'Dock #01: Central Kitchen Ciracas',
                'address' => 'Kawasan Industri Ciracas Blok C2 No. 14, Jakarta Timur',
                'access' => 'Akses Truk: Fuso / CDD Box Reefer Kapasitas 5 Ton',
                'selected' => true,
            ],
            [
                'key' => 'kelapa_gading',
                'chip' => 'Secondary Hub',
                'name' => 'Dock #02: Satellite Commissary Hub',
                'address' => 'Jl. Boulevard Barat Raya No. 88, Kelapa Gading, Jakarta Utara',
                'access' => 'Akses Truk: Blind Van / L300 Box Pendingin',
                'selected' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function receiving(): array
    {
        return [
            'label' => 'PIC Penerima di Dock & QC Lapangan',
            'name' => 'Pak Hendra Gunawan (Supervisor Receiving & QC)',
            'contact' => 'Kontak Operasional: +62 812-3456-7890 • Handover Digital Sign Off Ready',
            'action' => 'Ubah PIC',
            'roster' => [
                [
                    'name' => 'Pak Hendra Gunawan',
                    'role' => 'Supervisor Receiving & QC',
                    'phone' => '+62 812-3456-7890',
                ],
                [
                    'name' => 'Bu Ratna Kusuma',
                    'role' => 'QC Analyst & Lab Gate',
                    'phone' => '+62 813-2210-5566',
                ],
                [
                    'name' => 'Pak Yoga Pratama',
                    'role' => 'Dock Marshal Loading',
                    'phone' => '+62 811-9088-3321',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function driverNote(): array
    {
        return [
            'label' => 'Catatan Instruksi Khusus Supir & SOP Bongkar Muat',
            'value' => 'Wajib menggunakan armada reefer cold-chain terjaga stabil di 8°C - 12°C selama perjalanan. Masuk melalui Pintu Barat Dock 2 membawa Surat Jalan rangkap 3 dan sertifikat uji residu pestisida.',
            'hint' => 'Instruksi akan otomatis tercetak pada Lembar Manifest Dispatch Driver GPA.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function summary(): array
    {
        return [
            'title' => 'Ringkasan Estimasi PO',
            'shipping_label' => 'Estimasi Ongkir Reefer Cold-Chain',
            'shipping_value' => 0,
            'shipping_note' => 'Franco Gudang Klien',
            'tax_label' => 'Pajak Pertambahan Nilai (PPN)',
            'tax_chip' => 'Ditanggung SKB',
            'total_label' => 'Total Estimasi Tagihan',
            'total_note' => 'Sebelum penhitungan final timbang muat',
            'payment_label' => 'Metode Penyelesaian & Pembayaran',
            'payment_chip' => 'B2B Term',
            'submit' => 'Ajukan Pesanan Estimasi (Submit PO)',
            'save' => 'Simpan Sebagai Draf PO',
            'status' => 'Drafting',
            'submitted_status' => 'Menunggu Verifikasi Admin',
            'sla' => '< 2 Jam Kerja',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function paymentMethods(): array
    {
        return [
            [
                'key' => 'top30',
                'name' => 'TOP 30 Hari (Term of Payment)',
                'chip' => 'Terpilih',
                'note' => 'Sisa Limit: Rp 447.600.000 (Mencukupi untuk PO ini). Jatuh tempo 30 hari pasca Surat Jalan & Faktur ditandatangani.',
                'note_tone' => 'success',
                'selected' => true,
            ],
            [
                'key' => 'transfer',
                'name' => 'Transfer Bank Manual (BCA / Mandiri GPA)',
                'chip' => null,
                'note' => 'Upload bukti transfer sebelum dispatch armada.',
                'note_tone' => 'quiet',
                'selected' => false,
            ],
            [
                'key' => 'qris',
                'name' => 'QRIS Dinamis / Statis Manual',
                'chip' => null,
                'note' => 'Scan kode barcode QRIS nominal instan saat SPB keluar.',
                'note_tone' => 'quiet',
                'selected' => false,
            ],
            [
                'key' => 'cod',
                'name' => 'COD (Bayar Tunai di Tempat saat Bongkar)',
                'chip' => null,
                'note' => 'Verifikasi fisik kasir gudang receiving dock.',
                'note_tone' => 'quiet',
                'selected' => false,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function guarantee(): array
    {
        return [
            'title' => 'Jaminan Mutu Horeca Cold Chain',
            'body' => 'Armada reefer dilengkapi data-logger suhu real-time. Komoditas cacat mutu langsung diproses retur di dock penerimaan.',
        ];
    }
}

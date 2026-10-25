<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Seed invoices, orders, order_items, and payments.
     */
    public function run(): void
    {
        DB::table('payments')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('invoices')->truncate();

        // =====================================================================
        // 1. SEED INVOICES (Faktur Tempo & Piutang Berjalan - Aging AR)
        // =====================================================================
        DB::table('invoices')->insert([
            // STAGE 01 (0-15 HARI TERSISA): PT Aerofood ACS Indonesia (Lancar / In-Schedule)
            [
                'id' => 1,
                'invoice_number' => 'INV-GPA-202610-0088',
                'user_id' => 8,
                'period_start' => '2026-10-01',
                'period_end' => '2026-10-15',
                'subtotal_amount' => 114200000.00,
                'grand_total' => 114200000.00,
                'due_date' => now()->addDays(22)->toDateString(), // 22 hari tersisa
                'status' => 'UNPAID',
                'pdf_file_path' => 'invoices/INV-GPA-202610-0088.pdf',
                'created_at' => '2026-10-15 10:00:00',
                'updated_at' => '2026-10-15 10:00:00',
            ],
            // STAGE 02 (16-30 HARI / MENDEKATI TEMPO <7 HARI): Hotel Grand Pangrango
            [
                'id' => 2,
                'invoice_number' => 'INV-GPA-202609-0064',
                'user_id' => 9,
                'period_start' => '2026-09-15',
                'period_end' => '2026-09-30',
                'subtotal_amount' => 59550000.00,
                'grand_total' => 59550000.00,
                'due_date' => now()->addDays(3)->toDateString(), // H-3 Jatuh Tempo
                'status' => 'UNPAID',
                'pdf_file_path' => 'invoices/INV-GPA-202609-0064.pdf',
                'created_at' => '2026-09-30 11:00:00',
                'updated_at' => '2026-09-30 11:00:00',
            ],
            // STAGE 03 (LEWAT TEMPO 1-14 HARI): PT Boga Rasa Kulina Prima
            [
                'id' => 3,
                'invoice_number' => 'INV-GPA-202609-0082',
                'user_id' => 10,
                'period_start' => '2026-09-01',
                'period_end' => '2026-09-20',
                'subtotal_amount' => 56750000.00,
                'grand_total' => 56750000.00,
                'due_date' => now()->subDays(5)->toDateString(), // Overdue 5 Hari
                'status' => 'OVERDUE',
                'pdf_file_path' => 'invoices/INV-GPA-202609-0082.pdf',
                'created_at' => '2026-09-20 14:00:00',
                'updated_at' => '2026-09-20 14:00:00',
            ],
            // STAGE 04 (OVERDUE KRITIS >15 HARI / AUTO-FREEZE PO): PT Segar Makmur Ritelindo
            [
                'id' => 4,
                'invoice_number' => 'INV-GPA-202609-0032',
                'user_id' => 11,
                'period_start' => '2026-08-25',
                'period_end' => '2026-09-10',
                'subtotal_amount' => 18000000.00,
                'grand_total' => 18000000.00,
                'due_date' => now()->subDays(18)->toDateString(), // Overdue 18 Hari
                'status' => 'OVERDUE',
                'pdf_file_path' => 'invoices/INV-GPA-202609-0032.pdf',
                'created_at' => '2026-09-10 15:00:00',
                'updated_at' => '2026-09-10 15:00:00',
            ],
            // FAKTUR TUNTAS TERBAYAR (CASH INFLOW REALISASI)
            [
                'id' => 5,
                'invoice_number' => 'INV-GPA-202608-0010',
                'user_id' => 8, // Aerofood
                'period_start' => '2026-08-01',
                'period_end' => '2026-08-31',
                'subtotal_amount' => 146160000.00,
                'grand_total' => 146160000.00,
                'due_date' => '2026-09-30',
                'status' => 'PAID',
                'pdf_file_path' => 'invoices/INV-GPA-202608-0010.pdf',
                'created_at' => '2026-08-31 16:00:00',
                'updated_at' => '2026-09-25 10:00:00',
            ],
            [
                'id' => 6,
                'invoice_number' => 'INV-GPA-202608-0011',
                'user_id' => 9, // Grand Pangrango
                'period_start' => '2026-08-01',
                'period_end' => '2026-08-31',
                'subtotal_amount' => 107980000.00,
                'grand_total' => 107980000.00,
                'due_date' => '2026-09-30',
                'status' => 'PAID',
                'pdf_file_path' => 'invoices/INV-GPA-202608-0011.pdf',
                'created_at' => '2026-08-31 16:30:00',
                'updated_at' => '2026-09-28 11:00:00',
            ],
            [
                'id' => 7,
                'invoice_number' => 'INV-GPA-202608-0012',
                'user_id' => 13, // Dapur Sunda
                'period_start' => '2026-08-15',
                'period_end' => '2026-09-15',
                'subtotal_amount' => 80180000.00,
                'grand_total' => 80180000.00,
                'due_date' => '2026-09-30',
                'status' => 'PAID',
                'pdf_file_path' => 'invoices/INV-GPA-202608-0012.pdf',
                'created_at' => '2026-09-15 17:00:00',
                'updated_at' => '2026-09-29 14:00:00',
            ],
            [
                'id' => 8,
                'invoice_number' => 'INV-GPA-202608-0013',
                'user_id' => 14, // AEON
                'period_start' => '2026-08-01',
                'period_end' => '2026-08-31',
                'subtotal_amount' => 74010000.00,
                'grand_total' => 74010000.00,
                'due_date' => '2026-09-30',
                'status' => 'PAID',
                'pdf_file_path' => 'invoices/INV-GPA-202608-0013.pdf',
                'created_at' => '2026-08-31 17:30:00',
                'updated_at' => '2026-09-30 16:00:00',
            ],
        ]);

        // =====================================================================
        // 2. SEED ORDERS (Pesanan Riil Sesuai Transaksi & Surat Jalan)
        // =====================================================================
        DB::table('orders')->insert([
            // Order 1: Aerofood ACS (SJ-088)
            [
                'id' => 1,
                'order_number' => 'ORD-GPA-202610-0088',
                'user_id' => 8,
                'invoice_id' => 1,
                'order_source' => 'ROUTINE_SCHEDULE',
                'target_delivery_date' => now()->subDays(2)->toDateString(),
                'delivery_address' => 'Loading Dock Bandara Soekarno-Hatta Hub',
                'grand_total' => 114200000.00,
                'status' => 'SELESAI',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-088',
                'driver_id' => 6,
                'vehicle_plate_number' => 'B 9014 KPA',
                'departure_time' => now()->subDays(2)->setTime(5, 0),
                'arrival_time' => now()->subDays(2)->setTime(8, 30),
                'pod_photo_url' => 'pod/pod_sj_088.jpg',
                'received_by_name' => 'Chef Ridwan (QC Aerofood)',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(2),
            ],
            // Order 2: Grand Pangrango (SJ-064)
            [
                'id' => 2,
                'order_number' => 'ORD-GPA-202609-0064',
                'user_id' => 9,
                'invoice_id' => 2,
                'order_source' => 'WEB_PORTAL',
                'target_delivery_date' => now()->subDays(6)->toDateString(),
                'delivery_address' => 'Gudang Basah Hotel Grand Pangrango Bogor',
                'grand_total' => 59550000.00,
                'status' => 'SELESAI_CATATAN',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-064',
                'driver_id' => 6,
                'vehicle_plate_number' => 'B 9014 KPA',
                'departure_time' => now()->subDays(6)->setTime(4, 30),
                'arrival_time' => now()->subDays(6)->setTime(7, 45),
                'pod_photo_url' => 'pod/pod_sj_064.jpg',
                'received_by_name' => 'Pak Suryana',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(6),
            ],
            // Order 3: PT Boga Rasa Kulina Prima (SJ-082)
            [
                'id' => 3,
                'order_number' => 'ORD-GPA-202609-0082',
                'user_id' => 10,
                'invoice_id' => 3,
                'order_source' => 'ROUTINE_SCHEDULE',
                'target_delivery_date' => now()->subDays(10)->toDateString(),
                'delivery_address' => 'Kawasan Industri Pulogadung Blok B No. 4, Jakarta Timur',
                'grand_total' => 56750000.00,
                'status' => 'SELESAI',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-082',
                'driver_id' => 7,
                'vehicle_plate_number' => 'D 8124 AB',
                'departure_time' => now()->subDays(10)->setTime(4, 0),
                'arrival_time' => now()->subDays(10)->setTime(8, 0),
                'pod_photo_url' => 'pod/pod_sj_082.jpg',
                'received_by_name' => 'Ibu Maya Hartono',
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(10),
            ],
            // Order 4: PT Segar Makmur Ritelindo (SJ-032)
            [
                'id' => 4,
                'order_number' => 'ORD-GPA-202609-0032',
                'user_id' => 11,
                'invoice_id' => 4,
                'order_source' => 'WEB_PORTAL',
                'target_delivery_date' => now()->subDays(20)->toDateString(),
                'delivery_address' => 'Central DC Cikarang Dry Port, Bekasi',
                'grand_total' => 18000000.00,
                'status' => 'SELESAI',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-032',
                'driver_id' => 6,
                'vehicle_plate_number' => 'B 9014 KPA',
                'departure_time' => now()->subDays(20)->setTime(5, 30),
                'arrival_time' => now()->subDays(20)->setTime(9, 15),
                'pod_photo_url' => 'pod/pod_sj_032.jpg',
                'received_by_name' => 'Doni Setiawan',
                'created_at' => now()->subDays(22),
                'updated_at' => now()->subDays(20),
            ],
            // Order 5: Aerofood Settled Inflow (SJ-010)
            [
                'id' => 5,
                'order_number' => 'ORD-GPA-202608-0010',
                'user_id' => 8,
                'invoice_id' => 5,
                'order_source' => 'ROUTINE_SCHEDULE',
                'target_delivery_date' => '2026-08-25',
                'delivery_address' => 'Hub Soekarno Hatta',
                'grand_total' => 146160000.00,
                'status' => 'SELESAI_CATATAN',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-010',
                'driver_id' => 6,
                'vehicle_plate_number' => 'B 9014 KPA',
                'departure_time' => '2026-08-25 04:30:00',
                'arrival_time' => '2026-08-25 08:00:00',
                'pod_photo_url' => 'pod/pod_sj_010.jpg',
                'received_by_name' => 'Chef Ridwan',
                'created_at' => '2026-08-24 10:00:00',
                'updated_at' => '2026-08-25 15:00:00',
            ],
            // Order 6: Grand Pangrango Settled Inflow (SJ-011)
            [
                'id' => 6,
                'order_number' => 'ORD-GPA-202608-0011',
                'user_id' => 9,
                'invoice_id' => 6,
                'order_source' => 'WEB_PORTAL',
                'target_delivery_date' => '2026-08-28',
                'delivery_address' => 'Grand Pangrango Bogor',
                'grand_total' => 107980000.00,
                'status' => 'SELESAI_CATATAN',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-011',
                'driver_id' => 7,
                'vehicle_plate_number' => 'D 8124 AB',
                'departure_time' => '2026-08-28 05:00:00',
                'arrival_time' => '2026-08-28 08:15:00',
                'pod_photo_url' => 'pod/pod_sj_011.jpg',
                'received_by_name' => 'Suryana',
                'created_at' => '2026-08-27 11:00:00',
                'updated_at' => '2026-08-28 16:00:00',
            ],
            // Order 7: Dapur Sunda Settled Inflow (SJ-012)
            [
                'id' => 7,
                'order_number' => 'ORD-GPA-202609-0012',
                'user_id' => 13,
                'invoice_id' => 7,
                'order_source' => 'WEB_PORTAL',
                'target_delivery_date' => '2026-09-12',
                'delivery_address' => 'Jl. Dr. Setiabudi No. 102 Bandung',
                'grand_total' => 80180000.00,
                'status' => 'SELESAI',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-012',
                'driver_id' => 6,
                'vehicle_plate_number' => 'B 9014 KPA',
                'departure_time' => '2026-09-12 06:00:00',
                'arrival_time' => '2026-09-12 07:30:00',
                'pod_photo_url' => 'pod/pod_sj_012.jpg',
                'received_by_name' => 'Ibu Ratna',
                'created_at' => '2026-09-11 12:00:00',
                'updated_at' => '2026-09-12 14:00:00',
            ],
            // Order 8: AEON Settled Inflow (SJ-013)
            [
                'id' => 8,
                'order_number' => 'ORD-GPA-202609-0013',
                'user_id' => 14,
                'invoice_id' => 8,
                'order_source' => 'WEB_PORTAL',
                'target_delivery_date' => '2026-09-20',
                'delivery_address' => 'AEON Mall BSD Store',
                'grand_total' => 74010000.00,
                'status' => 'SELESAI_CATATAN',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-013',
                'driver_id' => 6,
                'vehicle_plate_number' => 'B 9014 KPA',
                'departure_time' => '2026-09-20 05:00:00',
                'arrival_time' => '2026-09-20 08:30:00',
                'pod_photo_url' => 'pod/pod_sj_013.jpg',
                'received_by_name' => 'Dewi Anggraeni',
                'created_at' => '2026-09-19 14:00:00',
                'updated_at' => '2026-09-20 16:00:00',
            ],
            // Order 9: Order Aktif Dalam Pengiriman
            [
                'id' => 9,
                'order_number' => 'ORD-GPA-202610-0099',
                'user_id' => 15, // Pak Hendra (Reguler)
                'invoice_id' => null,
                'order_source' => 'WHATSAPP_ADMIN',
                'target_delivery_date' => now()->toDateString(),
                'delivery_address' => 'Jl. Buah Batu No. 142 Bandung',
                'grand_total' => 4500000.00,
                'status' => 'DALAM_PENGIRIMAN',
                'verified_by' => 2,
                'surat_jalan_number' => 'SJ-099',
                'driver_id' => 7,
                'vehicle_plate_number' => 'D 8124 AB',
                'departure_time' => now()->setTime(6, 0),
                'arrival_time' => null,
                'pod_photo_url' => null,
                'received_by_name' => null,
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 3. SEED ORDER ITEMS (Rincian Bobot Upfront)
        // =====================================================================
        DB::table('order_items')->insert([
            // Order 1: Aerofood ACS (Selada Romaine: 8000 kg)
            [
                'id' => 1,
                'order_id' => 1,
                'product_id' => 1,
                'ordered_qty' => 8000.00,
                'unit_price' => 14275.00,
                'subtotal' => 114200000.00,
                'returned_weight' => 0.00,
                'return_reason' => null,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(2),
            ],
            // Order 2: Grand Pangrango (Tomat Beef: 3970 kg)
            [
                'id' => 2,
                'order_id' => 2,
                'product_id' => 3,
                'ordered_qty' => 3970.00,
                'unit_price' => 15000.00,
                'subtotal' => 59550000.00,
                'returned_weight' => 30.00,
                'return_reason' => 'Sortir memar ringan di kebun',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(6),
            ],
            // Order 3: PT Boga Rasa (Brokoli Super: 2702.38 kg)
            [
                'id' => 3,
                'order_id' => 3,
                'product_id' => 2,
                'ordered_qty' => 2702.38,
                'unit_price' => 21000.00,
                'subtotal' => 56750000.00,
                'returned_weight' => 0.00,
                'return_reason' => null,
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(10),
            ],
            // Order 4: Segar Makmur (Kol Putih: 2400 kg)
            [
                'id' => 4,
                'order_id' => 4,
                'product_id' => 5,
                'ordered_qty' => 2400.00,
                'unit_price' => 7500.00,
                'subtotal' => 18000000.00,
                'returned_weight' => 0.00,
                'return_reason' => null,
                'created_at' => now()->subDays(22),
                'updated_at' => now()->subDays(20),
            ],
            // Order 5: Aerofood Settled Inflow (Selada Romaine: 10080 kg)
            [
                'id' => 5,
                'order_id' => 5,
                'product_id' => 1,
                'ordered_qty' => 10080.00,
                'unit_price' => 14500.00,
                'subtotal' => 146160000.00,
                'returned_weight' => 120.00,
                'return_reason' => 'Deviasi susut sortir gudang -1.18%',
                'created_at' => '2026-08-24 10:00:00',
                'updated_at' => '2026-08-25 15:00:00',
            ],
            // Order 6: Grand Pangrango Settled Inflow (Tomat Beef: 6748.75 kg)
            [
                'id' => 6,
                'order_id' => 6,
                'product_id' => 3,
                'ordered_qty' => 6748.75,
                'unit_price' => 16000.00,
                'subtotal' => 107980000.00,
                'returned_weight' => 51.25,
                'return_reason' => 'Sortir standar hotel bintang lima',
                'created_at' => '2026-08-27 11:00:00',
                'updated_at' => '2026-08-28 16:00:00',
            ],
            // Order 7: Dapur Sunda Settled Inflow (Brokoli: 3644.55 kg)
            [
                'id' => 7,
                'order_id' => 7,
                'product_id' => 2,
                'ordered_qty' => 3644.55,
                'unit_price' => 22000.00,
                'subtotal' => 80180000.00,
                'returned_weight' => 0.00,
                'return_reason' => null,
                'created_at' => '2026-09-11 12:00:00',
                'updated_at' => '2026-09-12 14:00:00',
            ],
            // Order 8: AEON Settled Inflow (Stroberi: 2960.40 pack)
            [
                'id' => 8,
                'order_id' => 8,
                'product_id' => 4,
                'ordered_qty' => 2960.40,
                'unit_price' => 25000.00,
                'subtotal' => 74010000.00,
                'returned_weight' => 39.60,
                'return_reason' => 'Mika retak penataan cold chain',
                'created_at' => '2026-09-19 14:00:00',
                'updated_at' => '2026-09-20 16:00:00',
            ],
            // Order 9: Reguler (Selada: 300 kg)
            [
                'id' => 9,
                'order_id' => 9,
                'product_id' => 1,
                'ordered_qty' => 300.00,
                'unit_price' => 15000.00,
                'subtotal' => 4500000.00,
                'returned_weight' => 0.00,
                'return_reason' => null,
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 4. SEED PAYMENTS (Pelunasan Pembayaran Realisasi Kas)
        // =====================================================================
        DB::table('payments')->insert([
            // Pelunasan Invoice 5 (Aerofood) via Transfer Mandiri Korporat
            [
                'id' => 1,
                'order_id' => 5,
                'invoice_id' => 5,
                'payment_reference' => 'TRF-MND-20260925-8812',
                'payment_method' => 'TRANSFER_BANK',
                'amount' => 146160000.00,
                'proof_url' => 'payments/bukti_transfer_aerofood.pdf',
                'status' => 'LUNAS',
                'verified_by' => 2,
                'paid_at' => '2026-09-25 10:00:00',
                'verification_note' => 'Dana tervalidasi masuk rekening Mandiri Koperasi GPA. Kliring lunas.',
                'created_at' => '2026-09-25 10:00:00',
                'updated_at' => '2026-09-25 10:00:00',
            ],
            // Pelunasan Invoice 6 (Grand Pangrango) via BNI Virtual Account
            [
                'id' => 2,
                'order_id' => 6,
                'invoice_id' => 6,
                'payment_reference' => 'VA-BNI-20260928-3319',
                'payment_method' => 'TRANSFER_BANK',
                'amount' => 107980000.00,
                'proof_url' => 'payments/bukti_va_grandpangrango.pdf',
                'status' => 'LUNAS',
                'verified_by' => 2,
                'paid_at' => '2026-09-28 11:00:00',
                'verification_note' => 'Kliring BNI Virtual Account otomatis H+0.',
                'created_at' => '2026-09-28 11:00:00',
                'updated_at' => '2026-09-28 11:00:00',
            ],
            // Pelunasan Invoice 7 (Dapur Sunda) via Transfer BCA
            [
                'id' => 3,
                'order_id' => 7,
                'invoice_id' => 7,
                'payment_reference' => 'TRF-BCA-20260929-9941',
                'payment_method' => 'TRANSFER_BANK',
                'amount' => 80180000.00,
                'proof_url' => 'payments/bukti_bca_dapursunda.pdf',
                'status' => 'LUNAS',
                'verified_by' => 2,
                'paid_at' => '2026-09-29 14:00:00',
                'verification_note' => 'Verifikasi dua arah rekening koran BCA tuntas.',
                'created_at' => '2026-09-29 14:00:00',
                'updated_at' => '2026-09-29 14:00:00',
            ],
            // Pelunasan Invoice 8 (AEON) via Transfer BCA Korporat
            [
                'id' => 4,
                'order_id' => 8,
                'invoice_id' => 8,
                'payment_reference' => 'TRF-BCA-20260930-1092',
                'payment_method' => 'TRANSFER_BANK',
                'amount' => 74010000.00,
                'proof_url' => 'payments/bukti_aeon_september.pdf',
                'status' => 'LUNAS',
                'verified_by' => 2,
                'paid_at' => '2026-09-30 16:00:00',
                'verification_note' => 'Pelunasan faktur konsolidasi ritel modern AEON tuntas.',
                'created_at' => '2026-09-30 16:00:00',
                'updated_at' => '2026-09-30 16:00:00',
            ],
        ]);
    }
}

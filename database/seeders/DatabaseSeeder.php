<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Kata sandi default untuk seluruh akun pengujian: password123
        $defaultPassword = Hash::make('password123');

        // =====================================================================
        // 1. SEED USERS (5 Aktor Utama Sistem GPA)
        // =====================================================================
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Ibu Dewi Anggraeni',
                'email' => 'dewi.purchasing@aeon.co.id',
                'phone' => '08122334455',
                'password' => $defaultPassword,
                'role' => 'KLIEN',
                'client_type' => 'B2B_KONTRAK',
                'company_name' => 'PT AEON Indonesia (BSD Store)',
                'address' => 'AEON Mall BSD City, Jl. BSD Raya Utama, Tangerang',
                'pic_name' => 'Dewi Anggraeni',
                'pic_phone' => '08122334455',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Pak Hendra Pratama',
                'email' => 'hendra@kateringbarokah.com',
                'phone' => '08198765432',
                'password' => $defaultPassword,
                'role' => 'KLIEN',
                'client_type' => 'REGULER',
                'company_name' => 'CV Barokah Kuliner Bandung',
                'address' => 'Jl. Buah Batu No. 142, Kota Bandung',
                'pic_name' => 'Hendra Pratama',
                'pic_phone' => '08198765432',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Ibu Rina Marlina',
                'email' => 'sekre@greenpasundan.id',
                'phone' => '085220001122',
                'password' => $defaultPassword,
                'role' => 'SEKRETARIS',
                'client_type' => null,
                'company_name' => 'Koperasi GPA Ciwidey',
                'address' => 'Kantor Operasional GPA, Desa Panundaan, Ciwidey',
                'pic_name' => 'Rina Marlina',
                'pic_phone' => '085220001122',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Pak Asep Sunandar',
                'email' => 'asep.lapangan@greenpasundan.id',
                'phone' => '081334455667',
                'password' => $defaultPassword,
                'role' => 'KOORDINATOR',
                'client_type' => null,
                'company_name' => 'Koperasi GPA Ciwidey',
                'address' => 'Packing House GPA, RT 03/RW 02 Panundaan, Ciwidey',
                'pic_name' => 'Asep Sunandar',
                'pic_phone' => '081334455667',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Kang Ujang Koswara',
                'email' => 'ujang.supir@greenpasundan.id',
                'phone' => '087889900112',
                'password' => $defaultPassword,
                'role' => 'ARMADA',
                'client_type' => null,
                'company_name' => 'Koperasi GPA Ciwidey',
                'address' => 'Mess Armada GPA, Jl. Raya Soreang-Ciwidey Km 4',
                'pic_name' => 'Ujang Koswara',
                'pic_phone' => '087889900112',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'H. Ridwan Permana',
                'email' => 'direktur@greenpasundan.id',
                'phone' => '081112233445',
                'password' => $defaultPassword,
                'role' => 'DIREKTUR',
                'client_type' => null,
                'company_name' => 'Koperasi GPA Ciwidey',
                'address' => 'Komplek Griya Pasundan Asri, Soreang, Bandung',
                'pic_name' => 'H. Ridwan Permana',
                'pic_phone' => '081112233445',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 2. SEED PRODUCTS (5 Komoditas Utama GPA)
        // =====================================================================
        DB::table('products')->insert([
            [
                'id' => 1,
                'sku' => 'SKU-LETTUCE-01',
                'name' => 'Selada Keriting (Lettuce Crisphead)',
                'unit' => 'kg',
                'grade' => 'Grade A',
                'base_price' => 15000.00,
                'minimum_order' => 10.00,
                'image_url' => 'products/selada.jpg',
                'description' => 'Selada segar renyah hidroponik dataran tinggi Ciwidey, bebas pestisida kimia.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'sku' => 'SKU-TOMATO-01',
                'name' => 'Tomat Beef Fresh Super',
                'unit' => 'kg',
                'grade' => 'Grade A',
                'base_price' => 12000.00,
                'minimum_order' => 5.00,
                'image_url' => 'products/tomat.jpg',
                'description' => 'Tomat beef berdaging padat dan tebal, standar industri katering dan hotel.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'sku' => 'SKU-BROCCOLI-01',
                'name' => 'Brokoli Hijau Ciwidey Super',
                'unit' => 'kg',
                'grade' => 'Grade A',
                'base_price' => 22000.00,
                'minimum_order' => 5.00,
                'image_url' => 'products/brokoli.jpg',
                'description' => 'Kuntum brokoli hijau padat tanpa ulat, pemetikan pagi hari dari kebun binaan.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'sku' => 'SKU-STRAW-01',
                'name' => 'Stroberi Ciwidey Manis Segar',
                'unit' => 'pack',
                'grade' => 'Grade A',
                'base_price' => 25000.00,
                'minimum_order' => 2.00,
                'image_url' => 'products/stroberi.jpg',
                'description' => 'Stroberi petik langsung dari petani lokal kemasan mika pack 500 gram higienis.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'sku' => 'SKU-CABBAGE-01',
                'name' => 'Kol Putih Krop Padat',
                'unit' => 'kg',
                'grade' => 'Grade A',
                'base_price' => 8000.00,
                'minimum_order' => 10.00,
                'image_url' => 'products/kol.jpg',
                'description' => 'Kol putih krop bulat padat berserat halus standar pasokan supermarket.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 3. SEED CONTRACTS (Kontrak B2B PT AEON Indonesia)
        // =====================================================================
        DB::table('contracts')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'product_id' => 1,
                'contract_number' => 'CTR-GPA-202609-0001',
                'fixed_price_per_kg' => 14000.00,
                'top_days' => 30,
                'committed_volume_per_cycle' => 800.00,
                'status' => 'ACTIVE',
                'approved_by' => 6,
                'approved_at' => '2026-09-01 08:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 4. SEED HARVEST BATCHES (Persediaan Stok Panen Kebun)
        // =====================================================================
        DB::table('harvest_batches')->insert([
            [
                'id' => 1,
                'product_id' => 1,
                'source_type' => 'PETANI_BINAAN',
                'supplier_name' => 'Kelompok Tani Sukamaju Ciwidey',
                'batch_date' => '2026-09-27',
                'initial_quantity' => 1000.00,
                'available_quantity' => 200.00,
                'inputted_by' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'product_id' => 1,
                'source_type' => 'BUFFER_LUAR',
                'supplier_name' => 'Mitra Petani Cadangan Rancabali',
                'batch_date' => '2026-09-27',
                'initial_quantity' => 300.00,
                'available_quantity' => 300.00,
                'inputted_by' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'product_id' => 2,
                'source_type' => 'PETANI_BINAAN',
                'supplier_name' => 'Mang Dadang Panundaan',
                'batch_date' => '2026-09-27',
                'initial_quantity' => 500.00,
                'available_quantity' => 450.00,
                'inputted_by' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'product_id' => 3,
                'source_type' => 'PETANI_BINAAN',
                'supplier_name' => 'Pak Haji Sobur Lebakmuncang',
                'batch_date' => '2026-09-27',
                'initial_quantity' => 400.00,
                'available_quantity' => 400.00,
                'inputted_by' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 5. SEED INVOICES (Faktur Tagihan)
        // =====================================================================
        DB::table('invoices')->insert([
            [
                'id' => 1,
                'invoice_number' => 'INV-GPA-202609-0001',
                'user_id' => 1,
                'period_start' => '2026-09-01',
                'period_end' => '2026-09-30',
                'subtotal_amount' => 11130000.00,
                'grand_total' => 11130000.00,
                'due_date' => '2026-10-30',
                'status' => 'PAID',
                'pdf_file_path' => 'invoices/INV-GPA-202609-0001.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 6. SEED ORDERS (Transaksi Pemesanan Selesai)
        // =====================================================================
        DB::table('orders')->insert([
            [
                'id' => 1,
                'order_number' => 'ORD-GPA-202609-0001',
                'user_id' => 1,
                'invoice_id' => 1,
                'order_source' => 'WEB_PORTAL',
                'target_delivery_date' => '2026-09-27',
                'delivery_address' => 'Loading Dock AEON Mall BSD Store, Tangerang',
                'estimated_total' => 11200000.00,
                'grand_total' => 11130000.00,
                'status' => 'SELESAI',
                'verified_by' => 3,
                'surat_jalan_number' => 'SJ-GPA-202609-0001',
                'driver_id' => 5,
                'vehicle_plate_number' => 'D 8124 AB',
                'departure_time' => '2026-09-27 05:00:00',
                'arrival_time' => '2026-09-27 08:45:00',
                'pod_photo_url' => 'pod/pod_sj_0001_signed.jpg',
                'received_by_name' => 'Pak Joko Santoso (Receiving AEON)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 7. SEED ORDER ITEMS (Item Pesanan & Bobot Riil Hasil Timbang)
        // =====================================================================
        DB::table('order_items')->insert([
            [
                'id' => 1,
                'order_id' => 1,
                'product_id' => 1,
                'ordered_qty' => 800.00,
                'unit_price' => 14000.00,
                'actual_net_weight' => 795.00,
                'subtotal_final' => 11130000.00,
                'weighed_by' => 4,
                'weighed_at' => '2026-09-27 03:45:00',
                'returned_weight' => 0.00,
                'return_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // =====================================================================
        // 8. SEED PAYMENTS (Pelunasan Pembayaran Manual)
        // =====================================================================
        DB::table('payments')->insert([
            [
                'id' => 1,
                'order_id' => 1,
                'invoice_id' => 1,
                'payment_reference' => 'TRF-BCA-20260927-9921',
                'payment_method' => 'TRANSFER_BANK',
                'amount' => 11130000.00,
                'proof_url' => 'payments/bukti_transfer_aeon_0001.jpg',
                'status' => 'LUNAS',
                'verified_by' => 3,
                'paid_at' => '2026-09-27 10:15:00',
                'verification_note' => 'Dana Rp 11.130.000 telah masuk mutasi rekening BCA Koperasi GPA.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

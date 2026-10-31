<?php

namespace Tests\Feature;

use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreationConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. PENGUJIAN GENERATOR NOMOR PESANAN ANTI-BENTROK:
     * Pemanggilan generateOrderNumber beruntun menghasilkan nomor urut yang unik tanpa duplikasi.
     */
    public function test_generate_order_number_is_sequential_and_unique(): void
    {
        $orderService = app(OrderService::class);

        $number1 = $orderService->generateOrderNumber();
        $this->assertNotEmpty($number1);

        // Buat order dengan nomor tersebut
        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        Order::create([
            'order_number'         => $number1,
            'user_id'              => $klien->id,
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Jl. Buah Batu No. 10',
            'grand_total'          => 100000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        $number2 = $orderService->generateOrderNumber();
        $this->assertNotEquals($number1, $number2);

        // Ekstrak angka urut
        $seq1 = (int) substr($number1, -4);
        $seq2 = (int) substr($number2, -4);
        $this->assertEquals($seq1 + 1, $seq2);
    }

    /**
     * 2. PENGUJIAN ANTI-MANIPULASI KUOTA LEWAT DUPLIKASI ITEM:
     * Pengiriman form checkout dengan product_id duplikat dalam array items ditolak validasi.
     */
    public function test_order_submission_rejects_duplicate_product_items(): void
    {
        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $product = Product::create([
            'sku'           => 'GPA-KNT-001',
            'name'          => 'Kentang Granola Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 20000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'batch_date'         => now()->toDateString(),
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Kelompok Tani Ciwidey',
            'initial_quantity'   => 100,
            'available_quantity' => 100,
            'inputted_by'        => $klien->id,
        ]);

        // Coba kirimkan items dengan ID produk yang sama dua kali
        $response = $this->actingAs($klien)->post('/klien/pesanan', [
            'target_delivery_date' => now()->addDays(2)->toDateString(),
            'delivery_address'     => 'Restoran Sunda Rasa',
            'payment_method'       => 'TRANSFER_BANK',
            'items'                => [
                ['product_id' => $product->id, 'quantity' => 10],
                ['product_id' => $product->id, 'quantity' => 10], // Duplikat
            ],
        ]);

        $response->assertSessionHasErrors('items.0.product_id');
        $this->assertDatabaseMissing('orders', ['user_id' => $klien->id]);
    }

    /**
     * 3. PENGUJIAN MASTER FREEZE:
     * Ketika Direktur Utama mengaktifkan sakelar darurat Master Freeze, pemesanan baru diblokir.
     */
    public function test_order_submission_is_blocked_when_master_freeze_is_active(): void
    {
        $gov = app(GovernanceService::class);
        $gov->toggleMasterFreeze(true, 'Audit Inventaris Akhir Bulan', 'Direktur Utama');

        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $product = Product::create([
            'sku'           => 'GPA-WTL-001',
            'name'          => 'Wortel Brastagi',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 12000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'batch_date'         => now()->toDateString(),
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Petani Mitra Ciwidey',
            'initial_quantity'   => 50,
            'available_quantity' => 50,
            'inputted_by'        => $klien->id,
        ]);

        $response = $this->actingAs($klien)->post('/klien/pesanan', [
            'target_delivery_date' => now()->addDays(2)->toDateString(),
            'delivery_address'     => 'Supermarket Segar',
            'payment_method'       => 'TRANSFER_BANK',
            'items'                => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        $response->assertSessionHasErrors('general');
        $this->assertDatabaseMissing('orders', ['user_id' => $klien->id]);

        // Pulihkan kembali status freeze agar tidak mempengaruhi tes lain
        $gov->toggleMasterFreeze(false, null, 'Direktur Utama');
    }
}

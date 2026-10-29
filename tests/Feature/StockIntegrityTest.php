<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Http\Resources\Sekretaris\InventoryResource;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\StockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. PENGUJIAN ANTI-SILENT UNDERSELLING:
     * allocateStock wajib melempar Exception jika stok batch panen tidak mencukupi.
     */
    public function test_allocate_stock_throws_exception_when_physical_stock_is_insufficient(): void
    {
        $admin = User::factory()->create(['role' => 'KOORDINATOR', 'is_active' => true]);
        $product = Product::create([
            'sku'           => 'GPA-KBS-001',
            'name'          => 'Kubis Hijau Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 8000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'batch_date'         => now()->toDateString(),
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Petani Ciwidey',
            'initial_quantity'   => 15,
            'available_quantity' => 15,
            'inputted_by'        => $admin->id,
        ]);

        $stockService = app(StockService::class);

        // Minta alokasi 50 kg padahal stok hanya 15 kg
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stok fisik komoditas {$product->name} di gudang tidak mencukupi");

        $stockService->allocateStock($product->id, 50);
    }

    /**
     * 2. PENGUJIAN PENGEMBALIAN STOK (RESTORE STOCK):
     * rejectOrder mengembalikan kuota panen jika pesanan yang telah disetujui dibatalkan.
     */
    public function test_reject_order_restores_stock_when_order_was_approved(): void
    {
        $admin = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $product = Product::create([
            'sku'           => 'GPA-WTL-002',
            'name'          => 'Wortel Organik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $batch = HarvestBatch::create([
            'product_id'         => $product->id,
            'batch_date'         => now()->toDateString(),
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Kebun Sayur Lembang',
            'initial_quantity'   => 100,
            'available_quantity' => 100,
            'inputted_by'        => $admin->id,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-TEST-RESTORE',
            'user_id'              => $klien->id,
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Restoran Segar',
            'grand_total'          => 300000,
            'status'               => OrderStatus::MENUNGGU_VERIFIKASI->value,
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 20,
            'unit_price'  => 15000,
            'subtotal'    => 300000,
        ]);

        $orderService = app(OrderService::class);

        // 1. Setujui pesanan -> stok berkurang dari 100 menjadi 80
        $orderService->approveOrder($order->id, $admin->id);
        $this->assertEquals(80, (float) $batch->fresh()->available_quantity);
        $this->assertEquals(OrderStatus::MENUNGGU_PEMBAYARAN->value, $order->fresh()->status);

        // 2. Batalkan pesanan -> stok harus kembali dari 80 menjadi 100
        $orderService->rejectOrder($order->id, $admin->id, 'Klien membatalkan pesanan');
        $this->assertEquals(100, (float) $batch->fresh()->available_quantity);
        $this->assertEquals(OrderStatus::BATAL->value, $order->fresh()->status);
    }

    /**
     * 3. PENGUJIAN PERSETUJUAN PESANAN DENGAN STOK INSUFFICIENT:
     * Sekretaris dilarang menyetujui pesanan jika kuota fisik panen di gudang tidak mencukupi.
     */
    public function test_order_approval_fails_when_physical_stock_is_insufficient(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $product = Product::create([
            'sku'           => 'GPA-SLD-001',
            'name'          => 'Selada Keriting',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 10000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-TEST-INSUF',
            'user_id'              => $klien->id,
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Jl. Buah Batu No. 12',
            'grand_total'          => 500000,
            'status'               => OrderStatus::MENUNGGU_VERIFIKASI->value,
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 50,
            'unit_price'  => 10000,
            'subtotal'    => 500000,
        ]);

        $orderService = app(OrderService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stok fisik komoditas {$product->name} di gudang tidak mencukupi");

        $orderService->approveOrder($order->id, $sekretaris->id);
    }

    /**
     * 4. PENGUJIAN ATP PADA INVENTORY RESOURCE (ANTI-DOUBLE DEDUCTION):
     * ATP tidak boleh memotong stok dua kali untuk pesanan yang sudah disetujui.
     */
    public function test_inventory_resource_atp_avoids_double_deduction(): void
    {
        $admin = User::factory()->create(['role' => 'KOORDINATOR', 'is_active' => true]);
        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $product = Product::create([
            'sku'           => 'GPA-BRK-001',
            'name'          => 'Brokoli Segar',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 20000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        // Stok panen sisa fisik di gudang: 50 kg
        HarvestBatch::create([
            'product_id'         => $product->id,
            'batch_date'         => now()->toDateString(),
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Mitra Kebun',
            'initial_quantity'   => 50,
            'available_quantity' => 50,
            'inputted_by'        => $admin->id,
        ]);

        // Pesanan yang SUDAH LUNAS (artinya stok gudang sudah dipotong saat disetujui)
        $paidOrder = Order::create([
            'order_number'         => 'ORD-PAID-001',
            'user_id'              => $klien->id,
            'target_delivery_date' => now()->addDays(1),
            'delivery_address'     => 'Hotel Pasundan',
            'grand_total'          => 200000,
            'status'               => 'LUNAS',
        ]);
        OrderItem::create([
            'order_id'    => $paidOrder->id,
            'product_id'  => $product->id,
            'ordered_qty' => 10,
            'unit_price'  => 20000,
            'subtotal'    => 200000,
        ]);

        $prod = Product::with(['harvestBatches', 'orderItems.order'])->find($product->id);
        $resource = (new InventoryResource($prod))->resolve();

        // ATP harus tetap 50 kg (karena stok pesanan lunas sudah dipotong sebelumnya di batch)
        $this->assertEquals('50 kg', $resource['atp_value']);
        $this->assertEquals(0, $resource['_raw']['locked']);
    }
}

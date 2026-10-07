<?php

namespace Tests\Feature;

use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SekretarisModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_sekretaris_routes(): void
    {
        $response = $this->get('/sekretaris/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/sekretaris/verifikasi');
        $response->assertRedirect('/login');
    }

    public function test_non_sekretaris_cannot_access_sekretaris_routes(): void
    {
        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get('/sekretaris/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($klien)->get('/sekretaris/verifikasi');
        $response->assertStatus(403);
    }

    public function test_sekretaris_can_access_all_eight_modules(): void
    {
        $sekretaris = User::factory()->create([
            'name'      => 'Siti Sekretaris Operasional',
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        // 1. Dashboard
        $response = $this->actingAs($sekretaris)->get('/sekretaris/dashboard');
        $response->assertStatus(200);

        // 2. Verifikasi Pesanan
        $response = $this->actingAs($sekretaris)->get('/sekretaris/verifikasi');
        $response->assertStatus(200);

        // 3. Stok & Buffer Panen Gudang
        $response = $this->actingAs($sekretaris)->get('/sekretaris/stok');
        $response->assertStatus(200);

        // 4. Surat Jalan & Dispatch
        $response = $this->actingAs($sekretaris)->get('/sekretaris/surat-jalan');
        $response->assertStatus(200);

        // 5. Faktur Konsolidasi
        $response = $this->actingAs($sekretaris)->get('/sekretaris/faktur');
        $response->assertStatus(200);

        // 6. Verifikasi Pembayaran & Rekening Koran
        $response = $this->actingAs($sekretaris)->get('/sekretaris/pembayaran');
        $response->assertStatus(200);

        // 7. Rekap Laporan & Jurnal Harian
        $response = $this->actingAs($sekretaris)->get('/sekretaris/laporan');
        $response->assertStatus(200);

        // 8. Input Pesanan Manual Telepon / WA
        $response = $this->actingAs($sekretaris)->get('/sekretaris/pesanan-manual');
        $response->assertStatus(200);
    }

    public function test_sekretaris_can_view_real_orders_in_verification_queue(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Resto Sedap Rasa',
            'is_active'    => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-TMT01',
            'name'          => 'Tomat Cherry Hidroponik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 25000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-9999',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Resto Sedap Rasa, Jakarta Selatan',
            'estimated_total'      => 250000,
            'grand_total'          => 250000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 10,
            'unit_price'  => 25000,
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/verifikasi');
        $response->assertStatus(200);
        $response->assertSee('ORD-GPA-202610-9999');
        $response->assertSee('PT Resto Sedap Rasa');
    }

    public function test_sekretaris_can_approve_order_and_allocate_stock_atomically(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $koordinator = User::factory()->create([
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-WRT01',
            'name'          => 'Wortel Berastagi Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 18000,
            'minimum_order' => 10,
            'is_active'     => true,
        ]);

        // Buat batch panen dengan stok tersedia 100 kg
        $batch = HarvestBatch::create([
            'product_id'         => $product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Kelompok Tani Berastagi',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 100,
            'available_quantity' => 100,
            'inputted_by'        => $koordinator->id,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-0010',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Gudang Klien',
            'estimated_total'      => 360000,
            'grand_total'          => 360000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 20,
            'unit_price'  => 18000,
        ]);

        $response = $this->actingAs($sekretaris)->post("/sekretaris/orders/{$order->id}/approve");

        $response->assertRedirect(route('sekretaris.verification'));
        $response->assertSessionHas('success');

        // Pastikan status pesanan berubah menjadi TERVERIFIKASI
        $order->refresh();
        $this->assertEquals('TERVERIFIKASI', $order->status);
        $this->assertEquals($sekretaris->id, $order->verified_by);

        // Pastikan kuota stok panen berkurang 20 kg (tersisa 80 kg)
        $batch->refresh();
        $this->assertEquals(80, (float) $batch->available_quantity);
    }

    public function test_sekretaris_can_reject_order_with_reason(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-BYM01',
            'name'          => 'Bayam Organik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 12000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-0011',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Dapur Klien',
            'estimated_total'      => 120000,
            'grand_total'          => 120000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'payment_reference' => 'PAY-TEST-0011',
            'payment_method'    => 'TRANSFER_MANUAL',
            'amount'            => 120000,
            'status'            => 'MENUNGGU_VERIFIKASI',
        ]);

        $response = $this->actingAs($sekretaris)->post("/sekretaris/orders/{$order->id}/reject", [
            'rejection_reason' => 'Kapasitas armada logistik rute Bogor telah melebihi batas muat harian.',
        ]);

        $response->assertRedirect(route('sekretaris.verification'));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('BATAL', $order->status);
        $this->assertEquals($sekretaris->id, $order->verified_by);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('DITOLAK', $payment->status);
        $this->assertEquals('Kapasitas armada logistik rute Bogor telah melebihi batas muat harian.', $payment->verification_note);
    }
}

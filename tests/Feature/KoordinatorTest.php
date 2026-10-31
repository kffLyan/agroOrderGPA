<?php

namespace Tests\Feature;

use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KoordinatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_koordinator_routes(): void
    {
        $response = $this->get('/koordinator/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/koordinator/produk');
        $response->assertRedirect('/login');

        $response = $this->get('/koordinator/stok');
        $response->assertRedirect('/login');
    }

    public function test_non_koordinator_cannot_access_koordinator_routes(): void
    {
        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get('/koordinator/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($klien)->get('/koordinator/produk');
        $response->assertStatus(403);
    }

    public function test_koordinator_can_access_all_modules(): void
    {
        $koordinator = User::factory()->create([
            'name'      => 'Agus Koordinator',
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => 'KLIEN', 'name' => 'Klien Test']);
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
            'user_id'              => $client->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'LUNAS',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Jl. Buah Batu No. 123, Bandung',
            'grand_total'          => 2500000,
        ]);

        OrderItem::create([
            'order_id'          => $order->id,
            'product_id'        => $product->id,
            'ordered_qty'       => 100,
            'unit_price'        => 25000,
            'subtotal'          => 2500000,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Poktan Sukamaju',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 500,
            'available_quantity' => 400,
            'inputted_by'        => $koordinator->id,
        ]);

        // 1. Dashboard (Pasokan & Pantau Antrean Pesanan Masuk)
        $response = $this->actingAs($koordinator)->get('/koordinator/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Pantau Antrean Pesanan Masuk');

        // 2. Manajemen Komoditas (CRUD)
        $response = $this->actingAs($koordinator)->get('/koordinator/produk');
        $response->assertStatus(200);
        $response->assertSee('Tomat Cherry Hidroponik');

        // 3. Stok & Quick Intake
        $response = $this->actingAs($koordinator)->get('/koordinator/stok');
        $response->assertStatus(200);
    }

    public function test_koordinator_can_manage_commodity_products_crud(): void
    {
        $koordinator = User::factory()->create([
            'name'      => 'Koordinator Master',
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        // 1. Tampilkan form create
        $response = $this->actingAs($koordinator)->get('/koordinator/produk/create');
        $response->assertStatus(200);

        // 2. Simpan produk baru
        $response = $this->actingAs($koordinator)->post('/koordinator/produk', [
            'sku'           => 'GPA-VGT-WRT01',
            'name'          => 'Wortel Brastagi Organik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 18000,
            'minimum_order' => 10,
            'is_active'     => 1,
            'preset_image'  => 'broccoli.jpg',
            'description'   => 'Wortel segar dipanen langsung dari kebun binaan.',
        ]);

        $response->assertRedirect('/koordinator/produk');
        $this->assertDatabaseHas('products', [
            'sku'  => 'GPA-VGT-WRT01',
            'name' => 'Wortel Brastagi Organik',
        ]);

        $product = Product::where('sku', 'GPA-VGT-WRT01')->first();

        // 3. Tampilkan detail produk
        $response = $this->actingAs($koordinator)->get("/koordinator/produk/{$product->id}");
        $response->assertStatus(200);

        // 4. Tampilkan form edit
        $response = $this->actingAs($koordinator)->get("/koordinator/produk/{$product->id}/edit");
        $response->assertStatus(200);

        // 5. Update produk
        $response = $this->actingAs($koordinator)->put("/koordinator/produk/{$product->id}", [
            'sku'           => 'GPA-VGT-WRT01',
            'name'          => 'Wortel Brastagi Organik Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A+',
            'base_price'    => 20000,
            'minimum_order' => 5,
            'is_active'     => 1,
            'preset_image'  => 'broccoli.jpg',
        ]);

        $response->assertRedirect('/koordinator/produk');
        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'name'  => 'Wortel Brastagi Organik Super',
            'grade' => 'Grade A+',
        ]);

        // 6. Toggle status aktif / nonaktif
        $response = $this->actingAs($koordinator)->post("/koordinator/produk/{$product->id}/toggle-status");
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id'        => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_koordinator_can_record_harvest_intake_batch(): void
    {
        $koordinator = User::factory()->create([
            'name'      => 'Koordinator Intake',
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-KNT01',
            'name'          => 'Kentang Granola Lembang',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 22000,
            'minimum_order' => 10,
            'is_active'     => true,
        ]);

        // Input pasokan panen valid
        $response = $this->actingAs($koordinator)->post('/koordinator/stok', [
            'product_id'       => $product->id,
            'source_type'      => 'PETANI_BINAAN',
            'supplier_name'    => 'Poktan Mitra Asri',
            'batch_date'       => now()->toDateString(),
            'initial_quantity' => 750,
            'grade'            => 'Grade A',
            'qc_notes'         => 'Lolos uji mutu visual dan kadar air optimal.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('harvest_batches', [
            'product_id'         => $product->id,
            'supplier_name'      => 'Poktan Mitra Asri',
            'initial_quantity'   => 750,
            'available_quantity' => 750,
            'source_type'        => 'PETANI_BINAAN',
        ]);
    }
}

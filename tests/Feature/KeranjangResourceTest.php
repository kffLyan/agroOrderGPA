<?php

namespace Tests\Feature;

use App\Http\Resources\Klien\KeranjangKomoditasResource;
use App\Http\Resources\Klien\KeranjangKonfigurasiResource;
use App\Models\Contract;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class KeranjangResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_keranjang_komoditas_resource_maps_regular_product(): void
    {
        $product = Product::create([
            'name'          => 'Wortel Brastagi Super',
            'sku'           => 'GPA-WTL-001',
            'base_price'    => 18500,
            'unit'          => 'KG',
            'minimum_order' => 15,
            'grade'         => 'Grade A',
            'is_active'     => true,
            'image_url'     => 'products/wortel.jpg',
            'description'   => 'Wortel segar Lembang',
        ]);

        $resource = new KeranjangKomoditasResource($product, collect(), [$product->id => 250]);
        $data = $resource->toArray(Request::create('/klien/keranjang'));

        $this->assertEquals((string) $product->id, $data['key']);
        $this->assertEquals($product->id, $data['id']);
        $this->assertEquals('Wortel Brastagi Super', $data['name']);
        $this->assertEquals(18500.0, $data['price']);
        $this->assertEquals(250, $data['stock']);
        $this->assertEquals(15.0, $data['moq']);
        $this->assertEquals('Grade A', $data['grade']);
        $this->assertEquals('Krat Plastik Higienis 10 KG', $data['packaging']);
        $this->assertEquals('Lembang', $data['origin']);
        $this->assertEquals('2-6°C', $data['cold_chain']);
        $this->assertEquals(10, $data['crate_kg']);
        $this->assertEquals('Krat', $data['pack_label']);
        $this->assertEquals('GPA-WTL-001', $data['sku']);
        $this->assertStringContainsString('products/wortel.jpg', $data['image_url']);
    }

    public function test_keranjang_komoditas_resource_uses_contract_price_when_active(): void
    {
        $user = User::factory()->create(['role' => 'KLIEN', 'client_type' => 'B2B_KONTRAK']);
        $product = Product::create([
            'name'          => 'Tomat Beef Super',
            'sku'           => 'GPA-TMT-001',
            'base_price'    => 20000,
            'unit'          => 'KG',
            'minimum_order' => 10,
            'grade'         => 'Grade A',
            'is_active'     => true,
        ]);

        $contract = Contract::create([
            'user_id'                    => $user->id,
            'product_id'                 => $product->id,
            'contract_number'            => 'PKS-TEST-001',
            'fixed_price_per_kg'         => 14500,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500,
            'status'                     => 'ACTIVE',
        ]);

        $activeContracts = collect([$product->id => $contract]);
        $resource = new KeranjangKomoditasResource($product, $activeContracts, [$product->id => 100]);
        $data = $resource->toArray(Request::create('/klien/keranjang'));

        $this->assertEquals(14500.0, $data['price']);
    }

    public function test_keranjang_konfigurasi_resource_structure(): void
    {
        $user = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Hotel Grand Pasundan',
            'address'      => 'Jl. Dr. Setiabudi No. 123, Bandung',
        ]);

        $resource = new KeranjangKonfigurasiResource($user, [
            'default_delivery_date' => '2026-11-15',
            'tax_settings'          => ['tax_ppn_enabled' => true, 'tax_ppn_percent' => 11.0],
            'shipping_settings'     => ['default_shipping_fee' => 25000.0],
            'credit_limit'          => 200000000.0,
            'credit_available'      => 150000000.0,
            'is_frozen'             => false,
        ]);

        $data = $resource->toArray(Request::create('/klien/keranjang'));

        $this->assertEquals('2026-11-15', $data['date']);
        $this->assertEquals('TEMPO_TOP', $data['payment']);
        $this->assertEquals('Jl. Dr. Setiabudi No. 123, Bandung', $data['default_address']);
        $this->assertEquals(200000000.0, $data['credit_limit']);
        $this->assertEquals(150000000.0, $data['credit_available']);
        $this->assertFalse($data['is_frozen']);
        $this->assertNotEmpty($data['docks']);
        $this->assertEquals('dock-utama', $data['dock']);
    }

    public function test_klien_can_view_keranjang_page_with_transformed_resources(): void
    {
        $klien = User::factory()->create([
            'role'        => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active'   => true,
        ]);

        $product = Product::create([
            'name'          => 'Brokoli Segar',
            'sku'           => 'GPA-BRK-001',
            'is_active'     => true,
            'base_price'    => 25000,
            'unit'          => 'KG',
            'minimum_order' => 10,
        ]);

        $contract = Contract::create([
            'user_id'                    => $klien->id,
            'product_id'                 => $product->id,
            'contract_number'            => 'PKS-TEST-002',
            'fixed_price_per_kg'         => 19000,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 200,
            'status'                     => 'ACTIVE',
        ]);

        $response = $this->actingAs($klien)->get('/klien/keranjang');

        $response->assertStatus(200);
        $response->assertViewHas('commodities');
        $response->assertViewHas('cartDefaults');
        $response->assertViewHas('docks');

        $commodities = $response->viewData('commodities');
        $this->assertIsArray($commodities);
        $this->assertNotEmpty($commodities);
        $firstItem = collect($commodities)->firstWhere('id', $product->id);
        $this->assertNotNull($firstItem);
        $this->assertEquals(19000.0, $firstItem['price']);

        $cartDefaults = $response->viewData('cartDefaults');
        $this->assertIsArray($cartDefaults);
        $this->assertEquals('TEMPO_TOP', $cartDefaults['payment']);
        $this->assertEquals('dock-utama', $cartDefaults['dock']);
    }
}

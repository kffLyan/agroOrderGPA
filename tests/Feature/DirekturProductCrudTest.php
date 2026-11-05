<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\HarvestBatch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirekturProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $director;
    protected User $client;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->director = User::factory()->create([
            'name'      => 'Bapak Direktur Utama',
            'email'     => 'direktur@greenpasundan.id',
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        $this->client = User::factory()->create([
            'name'         => 'PT Segar Jaya Resto',
            'email'        => 'procurement@segarjaya.id',
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Segar Jaya Restoran',
            'is_active'    => true,
        ]);

        $this->product = Product::create([
            'sku'           => 'GPA-VGT-TEST01',
            'name'          => 'Selada Romaine Test',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 14500.00,
            'minimum_order' => 10.00,
            'image_url'     => 'lettuce.jpg',
            'description'   => 'Selada hidroponik grade A pasokan resto.',
            'is_active'     => true,
        ]);
    }

    public function test_non_direktur_cannot_access_product_catalog(): void
    {
        // Guest
        $responseGuest = $this->get(route('direktur.produk.index'));
        $responseGuest->assertRedirect(route('login'));

        // Klien
        $responseClient = $this->actingAs($this->client)->get(route('direktur.produk.index'));
        $responseClient->assertStatus(403);
    }

    public function test_direktur_can_view_products_index_with_filters(): void
    {
        $secondProduct = Product::create([
            'sku'           => 'GPA-FRT-TEST02',
            'name'          => 'Tomat Beef Garut Test',
            'unit'          => 'kg',
            'grade'         => 'Grade Super',
            'base_price'    => 20000.00,
            'minimum_order' => 5.00,
            'image_url'     => 'tomato.jpg',
            'description'   => 'Tomat beef segar.',
            'is_active'     => false,
        ]);

        // 1. Tampilkan semua
        $response = $this->actingAs($this->director)->get(route('direktur.produk.index'));
        $response->assertStatus(200);
        $response->assertSee('Selada Romaine Test');
        $response->assertSee('Tomat Beef Garut Test');
        $response->assertSee('Katalog Komoditas & Penetapan Harga Dasar');

        // 2. Filter grade
        $responseGrade = $this->actingAs($this->director)->get(route('direktur.produk.index', ['grade' => 'Grade Super']));
        $responseGrade->assertStatus(200);
        $responseGrade->assertSee('Tomat Beef Garut Test');
        $responseGrade->assertDontSee('Selada Romaine Test');

        // 3. Filter status aktif
        $responseActive = $this->actingAs($this->director)->get(route('direktur.produk.index', ['status' => 'active']));
        $responseActive->assertStatus(200);
        $responseActive->assertSee('Selada Romaine Test');
        $responseActive->assertDontSee('Tomat Beef Garut Test');

        // 4. Pencarian
        $responseSearch = $this->actingAs($this->director)->get(route('direktur.produk.index', ['search' => 'Romaine']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Selada Romaine Test');
        $responseSearch->assertDontSee('Tomat Beef Garut Test');
    }

    public function test_direktur_can_view_create_product_form(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.produk.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Komoditas Baru ke Katalog Resmi');
        $response->assertSee('Kode SKU Komoditas');
        $response->assertSee('Harga Dasar (Rp)');
    }

    public function test_direktur_can_create_a_new_product(): void
    {
        $payload = [
            'sku'           => 'GPA-VGT-KOL99',
            'name'          => 'Kol Ungu Organik',
            'unit'          => 'kg',
            'grade'         => 'Grade Super',
            'base_price'    => 18000.00,
            'minimum_order' => 5.00,
            'preset_image'  => 'cabbage.jpg',
            'description'   => 'Kol ungu organik krop padat bebas residu.',
            'is_active'     => '1',
        ];

        $response = $this->actingAs($this->director)->post(route('direktur.produk.store'), $payload);

        $response->assertRedirect(route('direktur.produk.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku'        => 'GPA-VGT-KOL99',
            'name'       => 'Kol Ungu Organik',
            'grade'      => 'Grade Super',
            'base_price' => 18000.00,
            'image_url'  => 'cabbage.jpg',
            'is_active'  => 1,
        ]);
    }

    public function test_direktur_can_view_product_detail(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.produk.show', $this->product->id));
        $response->assertStatus(200);
        $response->assertSee('Rincian Komoditas: Selada Romaine Test');
        $response->assertSee('GPA-VGT-TEST01');
        $response->assertSee('Rp 14.500');
    }

    public function test_direktur_can_view_edit_product_form(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.produk.edit', $this->product->id));
        $response->assertStatus(200);
        $response->assertSee('Ubah Data Komoditas: Selada Romaine Test');
        $response->assertSee('GPA-VGT-TEST01');
        $response->assertSee('Simpan Perubahan Komoditas');
    }

    public function test_direktur_can_update_product(): void
    {
        $payload = [
            'sku'           => 'GPA-VGT-TEST01-REV',
            'name'          => 'Selada Romaine Super Rev',
            'unit'          => 'kg',
            'grade'         => 'Grade Super',
            'base_price'    => 16000.00,
            'minimum_order' => 15.00,
            'preset_image'  => 'lettuce.jpg',
            'description'   => 'Revisi spesifikasi harga kontrak.',
            'is_active'     => '1',
        ];

        $response = $this->actingAs($this->director)->put(route('direktur.produk.update', $this->product->id), $payload);

        $response->assertRedirect(route('direktur.produk.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id'         => $this->product->id,
            'sku'        => 'GPA-VGT-TEST01-REV',
            'name'       => 'Selada Romaine Super Rev',
            'base_price' => 16000.00,
        ]);
    }

    public function test_direktur_can_toggle_product_status(): void
    {
        $this->assertTrue($this->product->is_active);

        $response = $this->actingAs($this->director)->post(route('direktur.produk.toggle-status', $this->product->id));
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertFalse($this->product->is_active);
    }

    public function test_direktur_can_delete_product_without_relations(): void
    {
        $response = $this->actingAs($this->director)->delete(route('direktur.produk.destroy', $this->product->id));

        $response->assertRedirect(route('direktur.produk.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', [
            'id' => $this->product->id,
        ]);
    }

    public function test_direktur_deactivates_product_when_linked_to_contract(): void
    {
        // Buat kontrak yang mengikat produk ini
        Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-TEST-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->director)->delete(route('direktur.produk.destroy', $this->product->id));

        $response->assertRedirect(route('direktur.produk.index'));
        $response->assertSessionHas('success');

        // Produk tidak dihapus dari database melainkan dinonaktifkan
        $this->assertDatabaseHas('products', [
            'id'        => $this->product->id,
            'is_active' => 0,
        ]);
    }
}

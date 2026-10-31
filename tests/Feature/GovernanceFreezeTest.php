<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GovernanceFreezeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. PENGUJIAN PERSISTENSI KUNCI KREDIT KLIEN:
     * Direktur membekukan plafon kredit klien, status tersimpan permanen di GovernanceService.
     */
    public function test_direktur_toggle_freeze_persists_in_governance_service(): void
    {
        $director = User::factory()->create(['role' => 'DIREKTUR', 'is_active' => true]);
        $client = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $gov = app(GovernanceService::class);
        $this->assertFalse($gov->isClientFrozen($client->id));

        // 1. Direktur membekukan kredit klien
        $response = $this->actingAs($director)->post(route('direktur.piutang.freeze', $client->id));
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertTrue($gov->isClientFrozen($client->id));

        // 2. Direktur mencabut pembekuan kredit klien
        $response2 = $this->actingAs($director)->post(route('direktur.piutang.freeze', $client->id));
        $response2->assertStatus(302);
        $response2->assertSessionHas('success');

        $this->assertFalse($gov->isClientFrozen($client->id));
    }

    /**
     * 2. PENGUJIAN PEMBLOKIRAN PEMESANAN KLIEN YANG DIBEKUKAN:
     * Klien yang dibekukan tidak dapat mengajukan PO baru lewat portal web.
     */
    public function test_frozen_client_cannot_submit_order(): void
    {
        $client = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $product = Product::create([
            'sku'           => 'GPA-WTL-TEST01',
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
            'supplier_name'      => 'Kelompok Tani Cisarua',
            'initial_quantity'   => 100,
            'available_quantity' => 100,
            'inputted_by'        => $client->id,
        ]);

        // Bekukan klien
        $gov = app(GovernanceService::class);
        $gov->toggleClientFreeze($client->id);

        $payload = [
            'submission_token'     => (string) Str::uuid(),
            'target_delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'delivery_address'     => 'Jl. Raya Lembang No. 10',
            'items'                => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ];

        $response = $this->actingAs($client)->post(route('klien.pesanan.store'), $payload);
        $response->assertSessionHasErrors(['general']);
        $this->assertDatabaseMissing('orders', ['user_id' => $client->id]);
    }

    /**
     * 3. PENGUJIAN PENOLAKAN SURAT JALAN UNTUK KLIEN YANG DIBEKUKAN:
     * Penerbitan Surat Jalan oleh Sekretaris wajib ditolak jika plafon kredit klien dibekukan.
     */
    public function test_frozen_client_cannot_receive_surat_jalan(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $client = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $driver = User::factory()->create(['role' => 'ARMADA', 'is_active' => true]);

        $order = Order::create([
            'order_number'         => 'ORD-FREEZE-SJ-001',
            'user_id'              => $client->id,
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Restoran Segar',
            'grand_total'          => 200000,
            'status'               => OrderStatus::LUNAS->value,
        ]);

        // Bekukan kredit klien
        $gov = app(GovernanceService::class);
        $gov->toggleClientFreeze($client->id);

        $response = $this->actingAs($sekretaris)->post(route('sekretaris.surat-jalan.terbitkan', $order->id), [
            'driver_id'            => $driver->id,
            'vehicle_plate_number' => 'D 1234 ABC',
        ]);

        $response->assertSessionHasErrors(['error']);
        $this->assertNull($order->fresh()->surat_jalan_number);
    }

    /**
     * 4. PENGUJIAN PENOLAKAN PESANAN MANUAL UNTUK KLIEN YANG DIBEKUKAN:
     * Sekretaris tidak dapat menginput pesanan manual untuk klien yang plafonnya dibekukan.
     */
    public function test_manual_order_submission_rejects_when_client_is_frozen(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $client = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $product = Product::create([
            'sku'           => 'GPA-KNT-MAN01',
            'name'          => 'Kentang Granola',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'batch_date'         => now()->toDateString(),
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Kelompok Tani Pangalengan',
            'initial_quantity'   => 50,
            'available_quantity' => 50,
            'inputted_by'        => $sekretaris->id,
        ]);

        // Bekukan klien
        $gov = app(GovernanceService::class);
        $gov->toggleClientFreeze($client->id);

        $payload = [
            'client_id'            => $client->id,
            'target_delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'delivery_address'     => 'Hotel Pasundan',
            'items'                => [
                ['product_id' => $product->id, 'quantity' => 20, 'price' => 15000],
            ],
        ];

        $response = $this->actingAs($sekretaris)->post(route('sekretaris.pesanan-manual.store'), $payload);
        $response->assertSessionHasErrors(['client_id']);
        $this->assertDatabaseMissing('orders', ['user_id' => $client->id]);
    }
}

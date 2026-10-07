<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirekturTest extends TestCase
{
    use RefreshDatabase;

    private User $director;
    private User $client;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat User Direktur
        $this->director = User::factory()->create([
            'name'      => 'H. Ridwan Permana',
            'email'     => 'direktur@greenpasundan.id',
            'phone'     => '081234567890',
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        // 2. Buat User Klien
        $this->client = User::factory()->create([
            'name'         => 'Ibu Dewi',
            'email'        => 'dewi@aeon.co.id',
            'role'         => 'KLIEN',
            'company_name' => 'PT AEON Indonesia',
            'client_type'  => 'B2B_KONTRAK',
            'is_active'    => true,
        ]);

        // 3. Buat Produk Sayuran
        $this->product = Product::create([
            'sku'           => 'SKU-LETTUCE-01',
            'name'          => 'Selada Keriting',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000.00,
            'minimum_order' => 10.00,
            'is_active'     => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_direktur_dashboard(): void
    {
        $response = $this->get(route('direktur.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_direktur_user_cannot_access_direktur_dashboard(): void
    {
        $response = $this->actingAs($this->client)->get(route('direktur.dashboard'));
        $response->assertStatus(403);
    }

    public function test_direktur_can_view_dashboard_with_metrics_and_recent_data(): void
    {
        // Buat satu pesanan dan kontrak untuk pengujian metrik
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-TEST-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'ACTIVE',
            'approved_by'                => $this->director->id,
            'approved_at'                => now(),
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-TEST-001',
            'user_id'              => $this->client->id,
            'target_delivery_date' => now()->toDateString(),
            'delivery_address'     => 'Loading Dock AEON',
            'estimated_total'      => 7000000.00,
            'grand_total'          => 7000000.00,
            'status'               => 'SELESAI',
        ]);

        OrderItem::create([
            'order_id'          => $order->id,
            'product_id'        => $this->product->id,
            'ordered_qty'       => 500.00,
            'unit_price'        => 14000.00,
            'actual_net_weight' => 500.00,
            'subtotal_final'    => 7000000.00,
        ]);

        $response = $this->actingAs($this->director)->get(route('direktur.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dasbor Eksekutif Direktur');
        $response->assertSee('ORD-TEST-001');
        $response->assertSee('CTR-TEST-001');
    }

    public function test_direktur_can_view_contracts_index_with_status_filtering(): void
    {
        Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-ACTIVE-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'ACTIVE',
        ]);

        Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-PENDING-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        // 1. Tampilkan semua
        $response = $this->actingAs($this->director)->get(route('direktur.contracts.index'));
        $response->assertStatus(200);
        $response->assertSee('CTR-ACTIVE-001');
        $response->assertSee('CTR-PENDING-001');

        // 2. Filter hanya PENDING_APPROVAL
        $responsePending = $this->actingAs($this->director)->get(route('direktur.contracts.index', ['status' => 'PENDING_APPROVAL']));
        $responsePending->assertStatus(200);
        $responsePending->assertSee('CTR-PENDING-001');
        $responsePending->assertDontSee('CTR-ACTIVE-001');
    }

    public function test_direktur_can_view_create_contract_form(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.contracts.create'));

        $response->assertStatus(200);
        $response->assertSee('Terbitkan Kontrak Kemitraan B2B');
        $response->assertSee($this->client->name);
        $response->assertSee($this->product->name);
    }

    public function test_direktur_can_create_a_new_contract_as_active(): void
    {
        $payload = [
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-NEW-2026-001',
            'fixed_price_per_kg'         => 13500.00,
            'top_days'                   => 45,
            'committed_volume_per_cycle' => 1000.00,
            'status'                     => 'ACTIVE',
        ];

        $response = $this->actingAs($this->director)->post(route('direktur.contracts.store'), $payload);

        $response->assertRedirect(route('direktur.contracts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contracts', [
            'contract_number'    => 'CTR-NEW-2026-001',
            'status'             => 'ACTIVE',
            'approved_by'        => $this->director->id,
            'fixed_price_per_kg' => 13500.00,
        ]);
    }

    public function test_direktur_can_view_contract_detail(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-SHOW-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'ACTIVE',
            'approved_by'                => $this->director->id,
            'approved_at'                => now(),
        ]);

        $response = $this->actingAs($this->director)->get(route('direktur.contracts.show', $contract->id));

        $response->assertStatus(200);
        $response->assertSee('CTR-SHOW-001');
        $response->assertSee($this->client->company_name);
        $response->assertSee($this->product->name);
    }

    public function test_direktur_can_view_edit_contract_form(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-EDIT-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->director)->get(route('direktur.contracts.edit', $contract->id));

        $response->assertStatus(200);
        $response->assertSee('Ubah Parameter Kontrak');
        $response->assertSee('CTR-EDIT-001');
    }

    public function test_direktur_can_update_contract(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-UPDATE-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $payload = [
            'fixed_price_per_kg'         => 13800.00,
            'top_days'                   => 60,
            'committed_volume_per_cycle' => 750.00,
            'status'                     => 'ACTIVE',
        ];

        $response = $this->actingAs($this->director)->put(route('direktur.contracts.update', $contract->id), $payload);

        $response->assertRedirect(route('direktur.contracts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contracts', [
            'id'                 => $contract->id,
            'fixed_price_per_kg' => 13800.00,
            'top_days'           => 60,
            'status'             => 'ACTIVE',
            'approved_by'        => $this->director->id,
        ]);
    }

    public function test_direktur_can_approve_contract(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-APPROVE-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->director)->post(route('direktur.contracts.approve', $contract->id));

        $response->assertRedirect(route('direktur.contracts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contracts', [
            'id'          => $contract->id,
            'status'      => 'ACTIVE',
            'approved_by' => $this->director->id,
        ]);
    }

    public function test_direktur_can_terminate_contract(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-TERM-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'ACTIVE',
            'approved_by'                => $this->director->id,
            'approved_at'                => now(),
        ]);

        $response = $this->actingAs($this->director)->post(route('direktur.contracts.terminate', $contract->id));

        $response->assertRedirect(route('direktur.contracts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contracts', [
            'id'     => $contract->id,
            'status' => 'TERMINATED',
        ]);
    }

    public function test_direktur_can_delete_contract(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'CTR-DEL-001',
            'fixed_price_per_kg'         => 14000.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->director)->delete(route('direktur.contracts.destroy', $contract->id));

        $response->assertRedirect(route('direktur.contracts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('contracts', [
            'id' => $contract->id,
        ]);
    }

    public function test_direktur_can_view_sales_reports(): void
    {
        $order = Order::create([
            'order_number'         => 'ORD-REP-001',
            'user_id'              => $this->client->id,
            'target_delivery_date' => now()->toDateString(),
            'delivery_address'     => 'Loading Dock AEON',
            'estimated_total'      => 5000000.00,
            'grand_total'          => 5000000.00,
            'status'               => 'SELESAI',
        ]);

        OrderItem::create([
            'order_id'          => $order->id,
            'product_id'        => $this->product->id,
            'ordered_qty'       => 300.00,
            'unit_price'        => 15000.00,
            'actual_net_weight' => 300.00,
            'subtotal_final'    => 4500000.00,
        ]);

        $response = $this->actingAs($this->director)->get(route('direktur.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Rekapitulasi Penjualan');
        $response->assertSee('ORD-REP-001');
    }

    public function test_direktur_can_view_reports_print_page(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.reports.download'));

        $response->assertStatus(200);
        $response->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN');
        $response->assertSee('H. Ridwan Permana');
    }

    public function test_direktur_can_export_sales_reports_csv(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.reports.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_direktur_can_inspect_order_detail(): void
    {
        $order = Order::create([
            'order_number'         => 'ORD-INSP-001',
            'user_id'              => $this->client->id,
            'target_delivery_date' => now()->toDateString(),
            'delivery_address'     => 'Loading Dock AEON',
            'estimated_total'      => 4500000.00,
            'grand_total'          => 4500000.00,
            'status'               => 'SELESAI',
        ]);

        OrderItem::create([
            'order_id'          => $order->id,
            'product_id'        => $this->product->id,
            'ordered_qty'       => 300.00,
            'unit_price'        => 15000.00,
            'actual_net_weight' => 300.00,
            'subtotal_final'    => 4500000.00,
        ]);

        $response = $this->actingAs($this->director)->get(route('direktur.orders.show', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Inspeksi Pengawasan Pesanan: ORD-INSP-001');
        $response->assertSee($this->client->company_name);
        $response->assertSee($this->product->name);
    }

    public function test_direktur_can_view_users_index_with_role_filter_and_search(): void
    {
        $secretary = User::factory()->create([
            'name'      => 'Rina Marlina',
            'email'     => 'rina@example.com',
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $armada = User::factory()->create([
            'name'      => 'Pak Ujang Driver',
            'email'     => 'ujang@example.com',
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        // 1. Tampilkan semua
        $response = $this->actingAs($this->director)->get(route('direktur.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Rina Marlina');
        $response->assertSee('Pak Ujang Driver');
        $response->assertSee('Kelola Akun dan Hak Akses Pengguna');

        // 2. Filter role SEKRETARIS
        $responseSecretary = $this->actingAs($this->director)->get(route('direktur.users.index', ['role' => 'SEKRETARIS']));
        $responseSecretary->assertStatus(200);
        $responseSecretary->assertSee('Rina Marlina');
        $responseSecretary->assertDontSee('Pak Ujang Driver');

        // 3. Pencarian nama / email
        $responseSearch = $this->actingAs($this->director)->get(route('direktur.users.index', ['search' => 'Ujang']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Pak Ujang Driver');
        $responseSearch->assertDontSee('Rina Marlina');
    }

    public function test_direktur_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.users.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Akun Pengguna Baru');
        $response->assertSee('DIREKTUR');
        $response->assertSee('SEKRETARIS');
        $response->assertSee('KOORDINATOR');
        $response->assertSee('ARMADA');
        $response->assertSee('KLIEN');
    }

    public function test_direktur_can_create_a_new_user_with_role_and_client_fields(): void
    {
        $payload = [
            'name'                  => 'Budi Koordinator',
            'email'                 => 'budi@greenpasundan.id',
            'phone'                 => '081234567890',
            'role'                  => 'KOORDINATOR',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'is_active'             => '1',
        ];

        $response = $this->actingAs($this->director)->post(route('direktur.users.store'), $payload);

        $response->assertRedirect(route('direktur.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'      => 'Budi Koordinator',
            'email'     => 'budi@greenpasundan.id',
            'role'      => 'KOORDINATOR',
            'is_active' => 1,
        ]);
    }

    public function test_direktur_can_view_user_detail(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.users.show', $this->client->id));
        $response->assertStatus(200);
        $response->assertSee('Rincian Akun: Ibu Dewi');
        $response->assertSee($this->client->name);
        $response->assertSee($this->client->email);
        $response->assertSee($this->client->company_name);
    }

    public function test_direktur_can_view_edit_user_form(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.users.edit', $this->client->id));
        $response->assertStatus(200);
        $response->assertSee('Ubah Data Akun: Ibu Dewi');
        $response->assertSee($this->client->name);
        $response->assertSee('Simpan Perubahan');
    }

    public function test_direktur_can_update_user(): void
    {
        $payload = [
            'name'         => 'Ibu Dewi Updated',
            'email'        => 'dewi_updated@aeon.co.id',
            'phone'        => '089999999999',
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT AEON Indonesia Supermarket',
            'is_active'    => '1',
        ];

        $response = $this->actingAs($this->director)->put(route('direktur.users.update', $this->client->id), $payload);

        $response->assertRedirect(route('direktur.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'           => $this->client->id,
            'name'         => 'Ibu Dewi Updated',
            'email'        => 'dewi_updated@aeon.co.id',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT AEON Indonesia Supermarket',
        ]);
    }

    public function test_direktur_cannot_deactivate_their_own_account(): void
    {
        $payload = [
            'name'      => $this->director->name,
            'email'     => $this->director->email,
            'phone'     => '081234567890',
            'role'      => 'DIREKTUR',
            'is_active' => '0',
        ];

        $response = $this->actingAs($this->director)->put(route('direktur.users.update', $this->director->id), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

        $this->assertDatabaseHas('users', [
            'id'        => $this->director->id,
            'is_active' => 1,
        ]);
    }

    public function test_direktur_cannot_delete_their_own_account(): void
    {
        $response = $this->actingAs($this->director)->delete(route('direktur.users.destroy', $this->director->id));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

        $this->assertDatabaseHas('users', [
            'id' => $this->director->id,
        ]);
    }

    public function test_direktur_can_delete_another_user(): void
    {
        $targetUser = User::factory()->create([
            'name'      => 'Akun Percobaan',
            'email'     => 'trial@example.com',
            'role'      => 'KLIEN',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->director)->delete(route('direktur.users.destroy', $targetUser->id));

        $response->assertRedirect(route('direktur.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_non_direktur_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->client)->get(route('direktur.users.index'));
        $response->assertStatus(403);

        $responsePost = $this->actingAs($this->client)->post(route('direktur.users.store'), [
            'name'                  => 'Hacker',
            'email'                 => 'hacker@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'DIREKTUR',
            'is_active'             => '1',
        ]);
        $responsePost->assertStatus(403);
    }
}


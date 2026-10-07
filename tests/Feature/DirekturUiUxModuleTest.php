<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirekturUiUxModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $director;
    private User $client;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->director = User::factory()->create([
            'name'      => 'Ahmad Sanusi, S.P.',
            'email'     => 'direktur@greenpasundan.id',
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        $this->client = User::factory()->create([
            'name'         => 'PT Aerofood ACS Indonesia',
            'email'        => 'procurement@aerofood.co.id',
            'role'         => 'KLIEN',
            'company_name' => 'PT Aerofood ACS Indonesia',
            'client_type'  => 'B2B_KONTRAK',
            'is_active'    => true,
        ]);

        $this->product = Product::create([
            'sku'           => 'SKU-ROMAINE-01',
            'name'          => 'Selada Romaine Hydro',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 14500.00,
            'minimum_order' => 50.00,
            'is_active'     => true,
        ]);
    }

    public function test_direktur_can_access_sales_monitoring(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.sales.index'));
        $response->assertStatus(200);
        $response->assertSee('Monitoring Penjualan');
    }

    public function test_direktur_can_export_sales_monitoring_csv(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.sales.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_direktur_can_access_commodity_volume(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.commodities.index'));
        $response->assertStatus(200);
        $response->assertSee('Volume Komoditas');
    }

    public function test_direktur_can_export_commodity_volume_csv(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.commodities.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_direktur_can_access_receivables(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.receivables.index'));
        $response->assertStatus(200);
        $response->assertSee('Piutang');
    }

    public function test_direktur_can_send_wa_payment_reminder(): void
    {
        $invoice = Invoice::create([
            'user_id'         => $this->client->id,
            'invoice_number'  => 'INV-TEST-001',
            'period_start'    => now()->subDays(30)->toDateString(),
            'period_end'      => now()->toDateString(),
            'subtotal_amount' => 15000000.00,
            'grand_total'     => 15000000.00,
            'status'          => 'UNPAID',
            'due_date'        => now()->addDays(7)->toDateString(),
        ]);

        $response = $this->actingAs($this->director)
            ->post(route('direktur.receivables.reminder', $invoice->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_direktur_can_toggle_client_credit_freeze(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('direktur.receivables.freeze', $this->client->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_direktur_can_restructure_receivable(): void
    {
        $invoice = Invoice::create([
            'user_id'         => $this->client->id,
            'invoice_number'  => 'INV-TEST-002',
            'period_start'    => now()->subDays(60)->toDateString(),
            'period_end'      => now()->subDays(30)->toDateString(),
            'subtotal_amount' => 20000000.00,
            'grand_total'     => 20000000.00,
            'status'          => 'OVERDUE',
            'due_date'        => now()->subDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($this->director)
            ->post(route('direktur.receivables.restructure', $invoice->id), [
                'extended_days' => 30,
                'notes'         => 'Restrukturisasi disetujui direksi',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_direktur_can_access_contract_approval_page(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'KTR-PENDING-001',
            'fixed_price_per_kg'         => 14500.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 2000.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->director)->get(route('direktur.contracts.approval'));
        $response->assertStatus(200);
        $response->assertSee('KTR-PENDING-001');
    }

    public function test_direktur_can_batch_approve_contracts(): void
    {
        $contract1 = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'KTR-BATCH-001',
            'fixed_price_per_kg'         => 14500.00,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 2000.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->director)
            ->post(route('direktur.contracts.batch-approve'), [
                'selected_ids' => [$contract1->id],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertEquals('ACTIVE', $contract1->fresh()->status);
    }

    public function test_direktur_can_request_contract_revision(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'KTR-REV-001',
            'fixed_price_per_kg'         => 13000.00,
            'top_days'                   => 45,
            'committed_volume_per_cycle' => 1000.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->director)
            ->post(route('direktur.contracts.revision', $contract->id), [
                'revision_notes' => 'Harap naikkan harga dasar',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_direktur_can_reject_contract(): void
    {
        $contract = Contract::create([
            'user_id'                    => $this->client->id,
            'product_id'                 => $this->product->id,
            'contract_number'            => 'KTR-REJ-001',
            'fixed_price_per_kg'         => 10000.00,
            'top_days'                   => 60,
            'committed_volume_per_cycle' => 500.00,
            'status'                     => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->director)
            ->post(route('direktur.contracts.reject', $contract->id), [
                'rejection_reason' => 'Margin di bawah standar',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertEquals('TERMINATED', $contract->fresh()->status);
    }

    public function test_direktur_can_lock_and_unlock_period_report(): void
    {
        // 1. Lock period
        $responseLock = $this->actingAs($this->director)
            ->post(route('direktur.reports.lock'), [
                'period' => 'Kuartal IV (Okt - Des 2026)',
            ]);
        $responseLock->assertStatus(302);
        $responseLock->assertSessionHas('success');

        // 2. Unlock period
        $responseUnlock = $this->actingAs($this->director)
            ->post(route('direktur.reports.unlock'), [
                'period' => 'Kuartal IV (Okt - Des 2026)',
            ]);
        $responseUnlock->assertStatus(302);
        $responseUnlock->assertSessionHas('success');
    }

    public function test_direktur_can_access_governance_and_update_parameters(): void
    {
        $response = $this->actingAs($this->director)->get(route('direktur.governance.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Tata Kelola');

        $responseUpdate = $this->actingAs($this->director)
            ->post(route('direktur.governance.update-parameters'), [
                'warehouse_loss_max_percent'     => 5.0,
                'warehouse_loss_warning_percent' => 2.5,
                'default_top_credit_ceiling'     => 500000000,
                'default_top_days'               => 30,
                'min_margin_percent'             => 15.0,
            ]);

        $responseUpdate->assertStatus(302);
        $responseUpdate->assertSessionHas('success');
    }

    public function test_direktur_can_toggle_master_freeze_switch(): void
    {
        $response = $this->actingAs($this->director)
            ->post(route('direktur.governance.master-freeze'), [
                'reason' => 'Audit tahunan sedang berjalan',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_direktur_can_delegate_plt_mandate(): void
    {
        $secretary = User::factory()->create([
            'name'  => 'Ibu Dewi Sekretaris',
            'email' => 'dewi.sec@example.com',
            'role'  => 'SEKRETARIS',
        ]);

        $response = $this->actingAs($this->director)
            ->post(route('direktur.governance.delegate-plt'), [
                'mandate_number'  => 'SK-DIR-2026-PLT-001',
                'delegate_id'     => $secretary->id,
                'scope'           => 'OPERASIONAL_RUTIN_ONLY',
                'valid_until'     => now()->addDays(3)->toDateString(),
                'rationale_notes' => 'Tugas luar kota dinas agribisnis',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
    }

    public function test_direktur_can_export_audit_trail_csv(): void
    {
        $response = $this->actingAs($this->director)
            ->get(route('direktur.governance.export-audit'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}

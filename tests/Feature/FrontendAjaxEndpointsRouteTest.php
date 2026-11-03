<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\HarvestBatch;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FrontendAjaxEndpointsRouteTest extends TestCase
{
    use RefreshDatabase;

    private User $direktur;
    private User $sekretaris;
    private User $koordinator;
    private User $armada;
    private User $klien;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->direktur = User::factory()->create([
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        $this->sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $this->koordinator = User::factory()->create([
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $this->armada = User::factory()->create([
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $this->klien = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Resto Mitra Segar',
            'is_active'    => true,
            'email_verified_at' => now(),
        ]);

        $this->product = Product::create([
            'sku'           => 'GPA-VGT-AJAX02',
            'name'          => 'Selada Keriting Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        HarvestBatch::create([
            'product_id'         => $this->product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Kelompok Tani Lembang',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 100,
            'available_quantity' => 100,
            'inputted_by'        => $this->koordinator->id,
        ]);
    }

    public function test_direktur_kontrak_ajax_endpoints_work(): void
    {
        $contract = Contract::create([
            'contract_number'            => 'KTR-2026-TEST-01',
            'user_id'                    => $this->klien->id,
            'product_id'                 => $this->product->id,
            'status'                     => 'PENDING_APPROVAL',
            'committed_volume_per_cycle' => 50,
            'fixed_price_per_kg'         => 14000,
            'top_days'                   => 30,
            'start_date'                 => now()->toDateString(),
            'end_date'                   => now()->addMonths(6)->toDateString(),
        ]);

        // 1. Revision endpoint
        $resRev = $this->actingAs($this->direktur)->postJson("/direktur/kontrak/{$contract->id}/revisi", [
            'revision_notes' => 'Harap kurangi komitmen volume menjadi 40 kg.',
        ]);
        $resRev->assertStatus(200);
        $resRev->assertJson(['success' => true]);

        // 2. Batch approve endpoint
        $resBatch = $this->actingAs($this->direktur)->postJson('/direktur/kontrak/setujui-serentak', [
            'selected_ids' => [$contract->id],
        ]);
        $resBatch->assertStatus(200);
        $resBatch->assertJson(['success' => true]);

        // 3. Approve endpoint
        $contract->update(['status' => 'PENDING_APPROVAL']);
        $resApprove = $this->actingAs($this->direktur)->postJson("/direktur/kontrak/{$contract->id}/setujui");
        $resApprove->assertStatus(200);
        $resApprove->assertJson(['success' => true]);

        // 4. Reject endpoint
        $contract->update(['status' => 'PENDING_APPROVAL']);
        $resReject = $this->actingAs($this->direktur)->postJson("/direktur/kontrak/{$contract->id}/tolak", [
            'rejection_reason' => 'Plafon risiko kredit terlampaui.',
        ]);
        $resReject->assertStatus(200);
        $resReject->assertJson(['success' => true]);
    }

    public function test_direktur_piutang_ajax_endpoints_work(): void
    {
        // 1. Send Reminder
        $resReminder = $this->actingAs($this->direktur)->postJson("/direktur/piutang/{$this->klien->id}/reminder");
        $resReminder->assertStatus(200);
        $resReminder->assertJson(['success' => true]);

        // 2. Toggle Freeze
        $resFreeze = $this->actingAs($this->direktur)->postJson("/direktur/piutang/{$this->klien->id}/toggle-freeze");
        $resFreeze->assertStatus(200);
        $resFreeze->assertJson(['success' => true]);

        // 3. Restructure
        $resRestructure = $this->actingAs($this->direktur)->postJson("/direktur/piutang/{$this->klien->id}/restructure", [
            'extended_days' => 14,
        ]);
        $resRestructure->assertStatus(200);
        $resRestructure->assertJson(['success' => true]);
    }

    public function test_sekretaris_verifikasi_and_dispatch_ajax_endpoints_work(): void
    {
        $order = Order::create([
            'order_number'         => 'ORD-2026-AJAX-01',
            'user_id'              => $this->klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Jl. Buah Batu Bandung',
            'grand_total'          => 150000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $this->product->id,
            'ordered_qty' => 10,
            'unit_price'  => 15000,
            'subtotal'    => 150000,
        ]);

        // 1. Sekretaris Verifikasi Setujui
        $resApprove = $this->actingAs($this->sekretaris)->postJson("/sekretaris/verifikasi/{$order->id}/setujui", [
            'shipping_cost' => 50000,
        ]);
        $resApprove->assertStatus(200);
        $resApprove->assertJson(['success' => true]);

        // 2. Sekretaris Assign Driver
        $resDriver = $this->actingAs($this->sekretaris)->postJson("/sekretaris/surat-jalan/{$order->id}/tugaskan-supir", [
            'driver_id' => $this->armada->id,
        ]);
        $resDriver->assertStatus(200);
        $resDriver->assertJson(['success' => true]);
    }

    public function test_sekretaris_pembayaran_ajax_endpoints_work(): void
    {
        $order = Order::create([
            'order_number'         => 'ORD-2026-PAY-01',
            'user_id'              => $this->klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Jl. Riau No. 5 Bandung',
            'grand_total'          => 75000,
            'status'               => 'MENUNGGU_PEMBAYARAN',
        ]);

        $payment = Payment::create([
            'order_id'          => $order->id,
            'payment_reference' => 'PAY-TEST-001',
            'payment_method'    => 'TRANSFER_BANK',
            'amount'            => 75000,
            'status'            => 'MENUNGGU_VERIFIKASI',
        ]);

        // 1. Approve payment
        $resApprove = $this->actingAs($this->sekretaris)->postJson("/sekretaris/pembayaran/{$payment->id}/setujui", [
            'note' => 'Rekening koran cocok.',
        ]);
        $resApprove->assertStatus(200);
        $resApprove->assertJson(['success' => true]);

        // 2. Reject payment
        $payment->update(['status' => 'MENUNGGU_VERIFIKASI']);
        $resReject = $this->actingAs($this->sekretaris)->postJson("/sekretaris/pembayaran/{$payment->id}/tolak", [
            'note' => 'Bukti mutasi tidak ditemukan.',
        ]);
        $resReject->assertStatus(200);
        $resReject->assertJson(['success' => true]);
    }

    public function test_klien_bukti_pembayaran_ajax_endpoint_works(): void
    {
        Storage::fake('public');

        $invoice = Invoice::create([
            'invoice_number'  => 'INV-2026-KLIEN-01',
            'user_id'         => $this->klien->id,
            'period_start'    => now()->toDateString(),
            'period_end'      => now()->addDays(14)->toDateString(),
            'subtotal_amount' => 100000,
            'grand_total'     => 100000,
            'due_date'        => now()->addDays(14)->toDateString(),
            'status'          => 'UNPAID',
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-2026-KLIEN-PAY',
            'user_id'              => $this->klien->id,
            'invoice_id'           => $invoice->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Jl. Asia Afrika Bandung',
            'grand_total'          => 100000,
            'status'               => 'MENUNGGU_PEMBAYARAN',
        ]);

        $fakeImage = UploadedFile::fake()->image('bukti_tf.jpg', 600, 800);

        $response = $this->actingAs($this->klien)->postJson('/klien/bukti-pembayaran', [
            'order_id'       => $order->id,
            'invoice_po'     => $invoice->invoice_number,
            'amount'         => 100000,
            'payment_method' => 'TRANSFER_BANK',
            'sender_bank'    => 'BCA',
            'sender_account' => '1234567890',
            'reference'      => 'TRX-998877',
            'proof_file'     => $fakeImage,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_koordinator_stok_batch_endpoint_works(): void
    {
        $response = $this->actingAs($this->koordinator)->postJson('/koordinator/stok', [
            'product_id'       => $this->product->id,
            'source_type'      => 'PETANI_BINAAN',
            'supplier_name'    => 'Kelompok Tani Cisarua',
            'batch_date'       => now()->toDateString(),
            'initial_quantity' => 45,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }
}

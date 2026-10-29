<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\HarvestBatch;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. PENGUJIAN INTEGRASI ALOKASI STOK PADA PERSETUJUAN PEMBAYARAN LANGSUNG:
     * Jika pesanan masih MENUNGGU_VERIFIKASI dan sekretaris menyetujui pembayaran,
     * sistem wajib memotong stok FIFO dan menerbitkan nomor invoice resmi.
     */
    public function test_payment_approval_allocates_stock_and_issues_invoice_for_unverified_order(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $product = Product::create([
            'sku'           => 'GPA-WTL-PAY01',
            'name'          => 'Wortel Premium',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 10000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $batch = HarvestBatch::create([
            'product_id'         => $product->id,
            'batch_date'         => now()->toDateString(),
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Petani Ciwidey',
            'initial_quantity'   => 100,
            'available_quantity' => 100,
            'inputted_by'        => $sekretaris->id,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-UNVERIF-001',
            'user_id'              => $klien->id,
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Restoran Segar',
            'grand_total'          => 200000,
            'status'               => OrderStatus::MENUNGGU_VERIFIKASI->value,
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 20,
            'unit_price'  => 10000,
            'subtotal'    => 200000,
        ]);

        $payment = Payment::create([
            'order_id'          => $order->id,
            'payment_reference' => 'PAY-DIRECT-001',
            'payment_method'    => 'TRANSFER_BANK',
            'amount'            => 200000,
            'status'            => 'MENUNGGU_VERIFIKASI',
        ]);

        // Sekretaris langsung menyetujui pembayaran di modul Pembayaran
        $response = $this->actingAs($sekretaris)->post('/sekretaris/pembayaran/' . $payment->id . '/approve');
        $response->assertSessionHas('success');

        // Pastikan stok batch panen terpotong dari 100 menjadi 80
        $this->assertEquals(80, (float) $batch->fresh()->available_quantity);

        // Pastikan pesanan berstatus LUNAS dan memiliki nomor invoice
        $orderFresh = $order->fresh();
        $this->assertEquals(OrderStatus::LUNAS->value, $orderFresh->status);
        $this->assertNotNull($orderFresh->invoice_id);
    }

    /**
     * 2. PENGUJIAN PENCEGAHAN UNGGAH BUKTI BAYAR GANDA:
     * Pengunggahan bukti bayar kedua kali dalam 60 detik dialihkan secara aman tanpa membuat entitas dobel.
     */
    public function test_duplicate_payment_proof_submission_is_prevented(): void
    {
        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $invoice = Invoice::create([
            'invoice_number'  => 'INV-GPA-202610-0999',
            'user_id'         => $klien->id,
            'period_end'      => now()->addDays(14),
            'subtotal_amount' => 500000,
            'grand_total'     => 500000,
            'due_date'        => now()->addDays(14),
            'status'          => 'UNPAID',
        ]);

        // Kirim bukti transfer pertama
        $res1 = $this->actingAs($klien)->post('/klien/payment-proof', [
            'invoice_po'     => $invoice->invoice_number,
            'amount'         => 500000,
            'sender_bank'    => 'BCA',
            'sender_account' => '123456789',
        ]);
        $res1->assertSessionHasNoErrors();
        $this->assertEquals(1, Payment::where('invoice_id', $invoice->id)->count());

        // Kirim bukti transfer kedua dengan invoice yang sama dalam hitungan detik
        $res2 = $this->actingAs($klien)->post('/klien/payment-proof', [
            'invoice_po'     => $invoice->invoice_number,
            'amount'         => 500000,
            'sender_bank'    => 'BCA',
            'sender_account' => '123456789',
        ]);

        // Tidak boleh bertambah menjadi 2 baris pembayaran ganda
        $this->assertEquals(1, Payment::where('invoice_id', $invoice->id)->count());
        $res2->assertSessionHas('info');
    }
}

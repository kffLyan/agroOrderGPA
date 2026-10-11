<?php

namespace Tests\Feature\Prints;

use App\Support\SecretaryInvoiceData;
use Tests\TestCase;

class SecretaryInvoicePrintTest extends TestCase
{
    public function test_print_page_renders_a4_invoice_in_secretary_surat_jalan_style(): void
    {
        $invoice = SecretaryInvoiceData::ledgerRows()[0];
        $response = $this->get(route('prints.invoice', ['invoice' => $invoice['id']]));

        $response->assertOk();
        $response->assertSee('FAKTUR / INVOICE PENAGIHAN');
        $response->assertSee($invoice['id']);
        $response->assertSee($invoice['client']);
        $response->assertSee('Rekening Pembayaran');
        $response->assertSee('Bank Central Asia (BCA)');
        $response->assertSee('Total Tagihan · 3 Surat Jalan');
        $response->assertSee('Rp 114.200.000');
        $response->assertSee('a4-canvas');
        $response->assertSee('Cetak Faktur (Ctrl+P)');
    }

    public function test_printed_invoice_items_reconcile_with_ledger_total(): void
    {
        $invoice = SecretaryInvoiceData::ledgerRows()[0];
        $document = SecretaryInvoiceData::printDocument($invoice['id']);

        $this->assertNotNull($document);
        $this->assertSame($invoice['total'], array_sum(array_column($document['items'], 'amount')));
        $this->assertSame($invoice['total'], $document['totals']['amount']);
        $this->assertSame(1970, $document['totals']['qty']);
    }

    public function test_unknown_invoice_print_page_returns_not_found(): void
    {
        $this->get(route('prints.invoice', ['invoice' => 'INV-UNKNOWN']))
            ->assertNotFound();
    }
}

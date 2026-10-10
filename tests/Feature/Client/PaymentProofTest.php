<?php

namespace Tests\Feature\Client;

use App\Models\User;
use App\Support\ClientDocumentsData;
use App\Support\ClientPaymentProofData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_payment_proof_form_inside_documents_module(): void
    {
        $response = $this->get(route('documents.payment-proof'));

        $response->assertOk();
        $response->assertSee('Unggah Bukti Pembayaran &amp; Pelacakan Status Verifikasi Tagihan', false);
        $response->assertSee('Wajib PRD Rule 11: Verifikasi Manual Sekre Diperlukan');
        $response->assertSee('Kembali ke Pusat Dokumen &amp; Faktur', false);
        $response->assertSee(route('documents'), false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_documents_page_links_its_upload_action_to_the_payment_proof_form(): void
    {
        $response = $this->get(route('documents'));

        $response->assertOk();
        $response->assertSee('Unggah Bukti', false);
        $response->assertSee(route('documents.payment-proof'), false);

        foreach (ClientDocumentsData::proofMenu() as $item) {
            $response->assertSee($item['label']);
            $response->assertSee($item['note']);
        }
    }

    public function test_selected_invoice_is_preselected_from_the_documents_bank_box(): void
    {
        $response = $this->get(route('documents.payment-proof', ['tagihan' => 'INV-GPA-202610-04']));

        $response->assertOk();
        $response->assertSee('INV-GPA-202610-04 (Periode 01 Okt - 15 Okt 2026) — Total: Rp 38.650.000 — Jatuh Tempo: 15 Nov 2026', false);
    }

    public function test_unknown_invoice_query_falls_back_to_the_first_unpaid_invoice(): void
    {
        $response = $this->get(route('documents.payment-proof', ['tagihan' => 'INV-GPA-999999-99']));

        $response->assertOk();
        $response->assertSee(ClientPaymentProofData::invoices()[0]['option_label'], false);
    }

    public function test_form_renders_account_and_metadata_fields(): void
    {
        $account = ClientPaymentProofData::account();
        $form = ClientPaymentProofData::form();

        $response = $this->get(route('documents.payment-proof'));

        $response->assertOk();

        foreach ($account as $value) {
            $response->assertSee($value);
        }

        foreach ($form as $value) {
            $response->assertSee($value);
        }

        foreach (ClientPaymentProofData::file() as $value) {
            $response->assertSee($value);
        }

        foreach (ClientPaymentProofData::compliance() as $value) {
            $response->assertSee($value);
        }
    }

    public function test_return_compensation_reduces_the_exact_transfer_amount(): void
    {
        $invoice = collect(ClientPaymentProofData::invoices())
            ->firstWhere('po', 'INV-GPA-202610-04');

        $this->assertNotNull($invoice);
        $this->assertSame(38650000, $invoice['gross']);
        $this->assertSame(-90000, $invoice['credit']);
        $this->assertSame(38560000, $invoice['payable']);
        $this->assertSame('Rp 38.650.000', $invoice['gross_label']);
        $this->assertSame('-Rp 90.000', $invoice['credit_value_label']);
        $this->assertSame('Rp 38.560.000', $invoice['payable_label']);
        $this->assertSame('38.560.000', $invoice['payable_digits']);
        $this->assertTrue($invoice['has_credit']);
    }

    public function test_only_unpaid_invoices_are_offered_for_payment(): void
    {
        $documents = collect(ClientDocumentsData::invoices())->keyBy('po');
        $invoices = collect(ClientPaymentProofData::invoices());

        $this->assertSame(['INV-GPA-202610-04'], $invoices->pluck('po')->all());

        foreach ($invoices as $invoice) {
            $this->assertStringNotContainsString('Lunas', $invoice['status']);
            $this->assertSame($documents[$invoice['po']]['total'], $invoice['gross']);
            $this->assertSame(
                $invoice['gross'] + $invoice['credit'],
                $invoice['payable'],
                'Nominal wajib transfer harus sama dengan tagihan kotor ditambah kompensasi retur.'
            );
        }
    }

    public function test_file_rules_accept_images_and_pdf_up_to_five_megabytes(): void
    {
        $rules = ClientPaymentProofData::fileRules();

        $this->assertSame(['jpg', 'jpeg', 'png', 'pdf'], $rules['accept']);
        $this->assertSame(5120, $rules['max_kb']);
        $this->assertSame('image/jpeg,image/png,application/pdf', $rules['mime']);

        $response = $this->get(route('documents.payment-proof'));

        $response->assertSee('Format: JPG, PNG, PDF (Maks 5 MB)');
        $response->assertSee($rules['mime'], false);
    }

    public function test_verification_pipeline_follows_prd_rule_eleven_sequence(): void
    {
        $steps = ClientPaymentProofData::steps();

        $this->assertSame(['1', '2', '3', '4'], array_column($steps, 'no'));
        $this->assertSame(['done', 'active', 'pending', 'pending'], array_column($steps, 'tone'));

        $response = $this->get(route('documents.payment-proof'));

        $response->assertOk();

        foreach ($steps as $step) {
            $response->assertSee($step['title']);
            $response->assertSee($step['meta']);
            $response->assertSee($step['body']);
        }

        $notice = ClientPaymentProofData::notice();

        $response->assertSee($notice['eyebrow']);
        $response->assertSee($notice['body']);
        $this->assertStringContainsString('BUKAN berarti tagihan otomatis lunas seketika', $notice['body']);
    }

    public function test_hotline_and_plafon_summary_follow_manual_verification_sla(): void
    {
        $hotline = ClientPaymentProofData::hotline();
        $plafon = ClientPaymentProofData::plafon();

        $response = $this->actingAs(User::factory()->create())->get(route('documents.payment-proof'));

        $response->assertOk();
        $response->assertSee($hotline['title']);
        $response->assertSee($hotline['body']);
        $response->assertSee($hotline['action']);
        $response->assertSee($plafon['title']);
        $response->assertSee($plafon['approved_value']);
        $response->assertSee($plafon['outstanding_value']);
        $response->assertSee($plafon['note']);
    }

    public function test_plafon_recovers_by_the_exact_verified_transfer_amount(): void
    {
        $plafon = ClientPaymentProofData::plafon();

        $this->assertSame('150.000.000', $plafon['approved_value']);
        $this->assertSame('42.100.000', $plafon['outstanding_value']);
        $this->assertSame('80.660.000', $plafon['projected_label']);
        $this->assertSame(
            42100000 + 38560000,
            (int) str_replace('.', '', $plafon['projected_label'])
        );
    }

    public function test_history_rows_render_payment_ids_amounts_and_verification_states(): void
    {
        $history = ClientPaymentProofData::history();
        $columns = ClientPaymentProofData::historyColumns();

        $response = $this->get(route('documents.payment-proof'));

        $response->assertOk();

        foreach ($columns as $column) {
            $response->assertSee($column['label']);
        }

        foreach ($history as $row) {
            $response->assertSee($row['id']);
            $response->assertSee($row['invoice']);
            $response->assertSee($row['date']);
            $response->assertSee($row['amount_label']);
            $response->assertSee($row['method']);
            $response->assertSee($row['verifier']);
            $response->assertSee($row['status']);
            $response->assertSee($row['receipt']);
        }

        $response->assertSee(ClientPaymentProofData::exportReport()['body']);
    }

    public function test_header_explains_the_module_is_a_derivative_of_documents_module(): void
    {
        $header = ClientPaymentProofData::header();

        $this->assertSame('Turunan Pusat Dokumen & Faktur', $header['source_label']);
        $this->assertStringContainsString('Dokumen & Faktur', $header['source_note']);
        $this->assertStringContainsString('PRD App-GPA.md Section 15 & Rule 11', $header['subtitle']);
        $this->assertStringContainsString('maks 2 jam kerja', $header['subtitle']);
    }
}

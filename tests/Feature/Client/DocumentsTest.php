<?php

namespace Tests\Feature\Client;

use App\Models\User;
use App\Support\ClientDocumentsData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_documents_with_demo_client(): void
    {
        $response = $this->get(route('documents'));

        $response->assertOk();
        $response->assertSee('Pusat Dokumen &amp; Faktur Konsolidasi Klien B2B', false);
        $response->assertSee('Enterprise Top 30D');
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee(route('documents'), false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_documents_renders_metrics_tabs_and_actions(): void
    {
        $response = $this->get(route('documents'));

        $response->assertOk();

        foreach (ClientDocumentsData::metrics() as $metric) {
            $response->assertSee($metric['label']);
            $response->assertSee($metric['title']);
            $response->assertSee($metric['value']);
            $response->assertSee($metric['foot_left']);
            $response->assertSee($metric['foot_right']);
        }

        foreach (ClientDocumentsData::tabs() as $tab) {
            $response->assertSee($tab['label']);
        }

        foreach (ClientDocumentsData::header()['actions'] as $action) {
            $response->assertSee($action['label']);
        }

        foreach (ClientDocumentsData::periods() as $period) {
            $response->assertSee($period['label']);
        }
    }

    public function test_documents_renders_every_invoice_row_with_amounts_and_actions(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('documents'));

        $response->assertOk();

        foreach (ClientDocumentsData::invoices() as $invoice) {
            $response->assertSee($invoice['po']);
            $response->assertSee($invoice['printed']);
            $response->assertSee($invoice['period_label']);
            $response->assertSee($invoice['sj_chip']);
            $response->assertSee($invoice['total_label']);
            $response->assertSee($invoice['status']);
        }

        foreach (ClientDocumentsData::columns() as $column) {
            $response->assertSee($column['label']);
        }
    }

    public function test_documents_renders_selected_invoice_attachment_panel(): void
    {
        $response = $this->get(route('documents'));

        $response->assertOk();

        $detail = ClientDocumentsData::detail();
        $bank = ClientDocumentsData::bank();

        $response->assertSee($detail['title']);
        $response->assertSee($detail['badge']);

        foreach (ClientDocumentsData::invoices()[0]['attachments'] as $item) {
            $response->assertSee($item['sj']);
            $response->assertSee($item['commodity']);
            $response->assertSee($item['amount_label']);
            $response->assertSee($item['weight_label']);
        }

        $response->assertSee($bank['bank']);
        $response->assertSee($bank['account']);
        $response->assertSee($bank['holder']);
        $response->assertSee($bank['action']);
    }

    public function test_invoice_totals_match_the_sum_of_their_delivery_notes(): void
    {
        foreach (ClientDocumentsData::invoices() as $invoice) {
            $amounts = array_column($invoice['attachments'], 'amount');

            $this->assertSame($invoice['total'], array_sum($amounts), $invoice['po']);
            $this->assertSame($invoice['subtotal'], $invoice['total']);
            $this->assertCount($invoice['sj_count'], $invoice['attachments'], $invoice['po']);
            $this->assertStringStartsWith((string) $invoice['sj_count'], $invoice['sj_chip']);
        }
    }

    public function test_invoice_weight_reconciliation_matches_net_to_riil(): void
    {
        $invoices = ClientDocumentsData::invoices();

        // Faktur 02 & 03: bobot lampiran sama dengan netto riil dokumen.
        foreach (array_slice($invoices, 1) as $invoice) {
            $this->assertSame(
                $invoice['netto'],
                round(array_sum(array_column($invoice['attachments'], 'weight')), 1),
                $invoice['po'],
            );
        }

        // Faktur 04: bobot netto dokumen mengikuti rekap 5 surat jalan (4.120,5 kg).
        $this->assertSame(4120.5, $invoices[0]['netto']);
        $this->assertSame('4.120,5 KG NET', $invoices[0]['netto_net_label']);
    }

    public function test_ppn_and_retur_credit_lines_follow_gpa_finance_policy(): void
    {
        foreach (ClientDocumentsData::invoices() as $invoice) {
            $this->assertSame('PPN Agribisnis Komoditas Segar (0%)', $invoice['ppn_label']);
            $this->assertSame('Rp 0 (Dibebaskan)', $invoice['ppn_value']);
            $this->assertSame('Kredit Penyesuaian Retur', $invoice['credit_label']);
            $this->assertSame('- Rp 0', $invoice['credit_value']);
            $this->assertSame($invoice['total_label'], $invoice['grand_total_label']);
            $this->assertSame(30, $invoice['tempo_days']);
        }
    }

    public function test_status_and_due_date_tones_match_the_design_states(): void
    {
        $invoices = collect(ClientDocumentsData::invoices())->keyBy('po');

        $this->assertSame('warning', $invoices['INV-GPA-202610-04']['status_tone']);
        $this->assertFalse($invoices['INV-GPA-202610-04']['due_struck']);
        $this->assertSame('accent', $invoices['INV-GPA-202609-02']['status_tone']);
        $this->assertTrue($invoices['INV-GPA-202609-02']['due_struck']);
        $this->assertSame('muted', $invoices['INV-GPA-202609-01']['status_tone']);
        $this->assertTrue($invoices['INV-GPA-202609-01']['due_muted']);
        $this->assertSame('muted', $invoices['INV-GPA-202609-01']['row_tone']);
    }

    public function test_active_invoice_is_the_first_unpaid_top_invoice(): void
    {
        $invoices = ClientDocumentsData::invoices();

        $this->assertSame('INV-GPA-202610-04', $invoices[0]['po']);
        $this->assertSame('active', $invoices[0]['row_tone']);
        $this->assertSame('Bayar', $invoices[0]['actions'][2]['label']);
        $this->assertSame('solid', $invoices[0]['actions'][2]['variant']);
    }

    public function test_archive_counts_cover_every_document_tab(): void
    {
        $counts = ClientDocumentsData::archiveCounts();
        $tabs = ClientDocumentsData::tabs();

        $this->assertCount(count($tabs), $counts);

        foreach ($tabs as $tab) {
            $this->assertGreaterThan(0, $counts->get($tab['value']));
        }
    }

    public function test_pagination_metadata_matches_the_design_footer(): void
    {
        $pagination = ClientDocumentsData::pagination();

        $this->assertSame(18, $pagination['total']);
        $this->assertSame(3, $pagination['per_page']);
        $this->assertSame(6, $pagination['last_page']);
        $this->assertSame(3, count(ClientDocumentsData::invoices()));
    }

    public function test_assurance_banner_references_gpa_prd_15_3(): void
    {
        $assurance = ClientDocumentsData::assurance();

        $this->assertStringContainsString('PRD 15.3', $assurance['title']);
        $this->assertStringContainsString('SHA-256', $assurance['body']);
        $this->assertSame(['Kebijakan TOP 30D', 'Kontak Tim Finance'], $assurance['chips']);
    }
}

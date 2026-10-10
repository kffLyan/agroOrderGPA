<?php

namespace Tests\Feature\Prints;

use App\Support\ReturnActData;
use Tests\TestCase;

class ReturnActPrintTest extends TestCase
{
    public function test_print_page_renders_return_act_and_tripartite_signatures(): void
    {
        $response = $this->get(route('prints.return-act'));

        $response->assertOk();
        $response->assertSee('BERITA ACARA RETUR &amp; SELISIH MUTU', false);
        $response->assertSee('BAP-RET-202610-0014');
        $response->assertSee('SJ-GPA-202610-0115');
        $response->assertSee('Kalkulasi Valuasi Retur');
        $response->assertSee('Rp 150.000');
        $response->assertSee('Pengesahan Tripartit Lapangan');
        $response->assertSee('Cetak Berita Acara Retur (Ctrl+P)');
    }

    public function test_return_weights_and_valuation_reconcile(): void
    {
        $document = ReturnActData::document();

        $this->assertSame(497.0, $document['totals']['shipped']);
        $this->assertSame(10.0, $document['totals']['returned']);
        $this->assertSame(487.0, $document['totals']['accepted']);
        $this->assertSame(
            $document['totals']['shipped'],
            $document['totals']['returned'] + $document['totals']['accepted'],
        );
        $this->assertSame(150000.0, $document['settlement']['returned_value']);
    }

    public function test_coordinator_menu_opens_the_return_report(): void
    {
        $response = $this->get(route('coordinator.returns'));

        $response->assertOk();
        $response->assertSee(route('coordinator.returns'), false);
        $response->assertSee(route('prints.return-act'), false);
        $response->assertSee('Laporan Retur');
    }
}

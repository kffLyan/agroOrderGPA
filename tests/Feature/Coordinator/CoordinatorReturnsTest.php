<?php

namespace Tests\Feature\Coordinator;

use App\Models\User;
use App\Support\CoordinatorReturnsData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoordinatorReturnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_coordinator_return_report(): void
    {
        $response = $this->get(route('coordinator.returns'));

        $response->assertOk();
        $response->assertSee('Laporan Rekapitulasi Pasokan &amp; Timbangan', false);
        $response->assertSee('Total Panen Diterima');
        $response->assertSee('Netto Timbangan Sah');
        $response->assertSee('Deviasi / Susut');
        $response->assertSee('Total Retur Lapangan');
        $response->assertSee('Rekapitulasi Mutasi Buffer Stock &amp; Retur Lapangan', false);
        $response->assertSee('BTC-SBG-RM-081');
        $response->assertSee('BA-RET-091');
        $response->assertSee('42.850');
        $response->assertSee('42.380');
    }

    public function test_authenticated_operator_and_return_navigation_render(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('coordinator.returns'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('href="'.route('coordinator.returns').'"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Terapkan');
        $response->assertSee('exportCsv()', false);
        $response->assertSee('window.print()', false);
    }

    public function test_batch_and_mutation_totals_reconcile_with_report_metrics(): void
    {
        $batches = CoordinatorReturnsData::batchRows();
        $mutations = CoordinatorReturnsData::mutationRows();

        $this->assertSame(42850, array_sum(array_column($batches, 'gross')));
        $this->assertSame(42380, array_sum(array_column($batches, 'net')));
        $this->assertSame(470, array_sum(array_column($batches, 'loss')));
        $this->assertSame(57290, array_sum(array_column($mutations, 'movement_kg')));
        $this->assertSame(-190, array_sum(array_column($mutations, 'return')));
    }
}

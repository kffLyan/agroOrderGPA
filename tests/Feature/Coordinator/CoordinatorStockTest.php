<?php

namespace Tests\Feature\Coordinator;

use App\Models\User;
use App\Support\CoordinatorStockData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CoordinatorStockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data Alpine ditulis sebagai `JSON.parse('...')` sehingga nilai yang hanya
     * hidup di payload harus dicocokkan lewat ekspresi `Js::from()`.
     */
    private function assertJsPayload(TestResponse $response, mixed $payload): void
    {
        $response->assertSee((string) Js::from($payload), false);
    }

    public function test_guest_can_preview_stock_console_with_demo_operator(): void
    {
        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee('Manajemen Stok Panen & Alokasi');
        $response->assertSee('Buffer Stock Gudang');
        $response->assertSee('Koordinator Lapangan');
        $response->assertSee('Agus Tusan');
        $response->assertSee('ID : 002');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_sidebar_marks_stock_navigation_as_active(): void
    {
        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('coordinator.stock'), false);
    }

    public function test_console_renders_header_actions_and_integrity_protocol(): void
    {
        $header = CoordinatorStockData::header();
        $protocol = CoordinatorStockData::protocol();

        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee($header['eyebrow']);
        $response->assertSee($header['subtitle']);

        foreach ($header['actions'] as $action) {
            $response->assertSee($action['label']);
        }

        $response->assertSee($protocol['title']);
        $response->assertSee($protocol['badge']);
        $response->assertSee($protocol['body']);
        $response->assertSee($protocol['validation_value']);
        $response->assertSee('0 ANOMALI OVERSELL');
    }

    public function test_console_renders_four_kpi_cards(): void
    {
        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee('TOTAL STOK TERSEDIA');
        $response->assertSee('STOK TERPESAN / PO');
        $response->assertSee('SISA BEBAS (FREE STOCK)');
        $response->assertSee('HUB PASOKAN AKTIF');
        $response->assertSee('14.850');
        $response->assertSee('8.900');
        $response->assertSee('+5.950');
        $response->assertSee('LOKASI SENTRA');
        $response->assertSee('Cianjur, Lembang, Pangalengan');
    }

    public function test_console_renders_commodity_filters_and_legend(): void
    {
        $filters = CoordinatorStockData::filters();

        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee($filters['label']);

        foreach ($filters['options'] as $option) {
            $response->assertSee($option['label']);
            $this->assertJsPayload($response, $option['key']);
        }

        foreach ($filters['legend'] as $legend) {
            $response->assertSee($legend['label']);
        }

        $this->assertJsPayload($response, $filters['options'][0]['key']);
    }

    public function test_console_renders_five_commodity_cards_with_supply_breakdown(): void
    {
        $commodities = CoordinatorStockData::commodities();

        $this->assertCount(5, $commodities);

        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();

        foreach ($commodities as $commodity) {
            $response->assertSee($commodity['name']);
            $response->assertSee($commodity['grade']);
            $response->assertSee($commodity['code']);
            $response->assertSee($commodity['hub']);
            $response->assertSee($commodity['utilization_status']);
            $this->assertJsPayload($response, $commodity['key']);

            foreach ($commodity['tiles'] as $tile) {
                $response->assertSee($tile['label']);

                if (is_numeric($tile['value'])) {
                    $response->assertSee(number_format($tile['value'], 0, ',', '.').' KG');
                } else {
                    $response->assertSee($tile['value']);
                }

                $response->assertSee($tile['note']);
            }
        }
    }

    public function test_commodity_supply_totals_reconcile_with_kpi_reconciliation(): void
    {
        $commodities = CoordinatorStockData::commodities();

        $total = array_sum(array_column($commodities, 'total'));
        $reserved = 0;
        $builtian = 0;
        $buffer = 0;

        foreach ($commodities as $commodity) {
            $reserved += $commodity['tiles'][2]['value'];
            $builtian += $commodity['tiles'][0]['value'];
            $buffer += $commodity['tiles'][1]['value'];

            $this->assertSame(
                $commodity['total'],
                $commodity['tiles'][0]['value'] + $commodity['tiles'][1]['value'],
                'Binaan + buffer harus sama dengan total '.$commodity['name'],
            );
            $this->assertSame(
                $commodity['rtp'],
                $commodity['total'] - $commodity['tiles'][2]['value'],
                'RTP harus sama dengan total dikurangi alokasi PO '.$commodity['name'],
            );
        }

        $this->assertSame(14850, $total);
        $this->assertSame(8900, $reserved);
        $this->assertSame(10200, $builtian);
        $this->assertSame(4650, $buffer);
        $this->assertSame(5950, $total - $reserved);
        $this->assertSame(68.7, round($builtian / $total * 100, 1));
    }

    public function test_brokoli_card_requires_buffer_injection_action(): void
    {
        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee('Brokoli Highland Fresh Cut');
        $response->assertSee('+ Injeksi Buffer Segera');
        $response->assertSee('Perlu Buffer +300kg');
        $response->assertSee('(KETAT)');
        $response->assertSee('Minta Buffer');
    }

    public function test_console_renders_quick_intake_ledger_form(): void
    {
        $intake = CoordinatorStockData::intake();

        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee($intake['title']);
        $response->assertSee($intake['subtitle']);
        $response->assertSee($intake['badge']);
        $response->assertSee($intake['port_label']);
        $response->assertSee($intake['port_status']);
        $response->assertSee($intake['submit']);

        foreach ($intake['fields'] as $field) {
            $response->assertSee($field['label']);
            $response->assertSee('intake-'.$field['key']);

            foreach ($field['options'] ?? [] as $option) {
                $response->assertSee($option['label']);
            }
        }

        $response->assertSee('Pak Endang (Gapoktan Pacet Makmur - Binaan)');
        $response->assertSee('Selada Romaine Highland (CMD-ROM-092)');
        $response->assertSee('Brix 4.5°, Sortir manual 100%, peti kemas plastik #04');
    }

    public function test_console_renders_inbound_log_with_audit_references(): void
    {
        $logs = CoordinatorStockData::logs();

        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee($logs['title']);
        $response->assertSee($logs['badge']);
        $response->assertSee('Lihat Seluruh');
        $response->assertSee((string) $logs['total']);

        foreach ($logs['items'] as $item) {
            $response->assertSee($item['lot']);
            $response->assertSee($item['time']);
            $response->assertSee($item['entry']);
            $response->assertSee($item['note']);
            $response->assertSee($item['audit']);
        }

        $response->assertSee('AUDIT: SEC-OP-882');
        $response->assertSee('AUDIT: SEC-SYS-AUTO');
    }

    public function test_console_mounts_coordinator_stock_component_with_payload(): void
    {
        $stock = CoordinatorStockData::for();

        $response = $this->get(route('coordinator.stock'));

        $response->assertOk();
        $response->assertSee('coordinatorStock(', false);

        $this->assertJsPayload($response, $stock['commodities']);
        $this->assertJsPayload($response, $stock['filters']['options']);
        $this->assertJsPayload($response, $stock['intake']['fields']);
        $this->assertJsPayload($response, $stock['logs']['items']);
        $this->assertJsPayload($response, $stock['logs']['total']);
    }

    public function test_stock_route_does_not_disturb_dashboard_reconciliation(): void
    {
        $dashboard = $this->get(route('coordinator.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('1.840');
        $dashboard->assertSee('1.920');

        $this->get(route('coordinator.stock'))->assertOk();
        $this->get(route('coordinator.weighing'))->assertOk();
    }
}

<?php

namespace Tests\Feature\Client;

use App\Models\User;
use App\Support\ClientOrdersData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_orders_with_demo_client(): void
    {
        $response = $this->get(route('orders'));

        $response->assertOk();
        $response->assertSee('Daftar Pesanan Saya');
        $response->assertSee('LIVE');
        $response->assertSee('CO'.'CKPIT');
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('Masuk Portal');
        $response->assertSee(route('orders'), false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_orders_renders_status_tabs_filters_and_integrity_banner(): void
    {
        $response = $this->get(route('orders'));

        $response->assertOk();

        foreach (ClientOrdersData::statusTabs() as $tab) {
            $response->assertSee($tab['label']);
        }

        foreach (['range', 'hub', 'payment'] as $key) {
            $response->assertSee(ClientOrdersData::filters()[$key]['label']);
        }

        $response->assertSee(ClientOrdersData::filters()['deviation_label']);
        $response->assertSee(ClientOrdersData::filters()['search']);
        $response->assertSee(ClientOrdersData::integrity()['chip']);
    }

    public function test_orders_renders_every_preview_row_with_total_and_actions(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('orders'));

        $response->assertOk();

        foreach (ClientOrdersData::orders() as $order) {
            $response->assertSee($order['po']);
            $response->assertSee(number_format($order['total'], 0, ',', '.'));

            foreach ($order['actions'] as $action) {
                $response->assertSee($action['label']);
            }
        }
    }

    public function test_orders_renders_all_columns_and_summary_metrics(): void
    {
        $response = $this->get(route('orders'));

        $response->assertOk();

        foreach (ClientOrdersData::columns() as $column) {
            $response->assertSee($column['label']);
        }

        foreach (ClientOrdersData::metrics() as $metric) {
            $response->assertSee($metric['label']);
            $response->assertSee($metric['value']);
        }
    }

    public function test_status_tab_counts_match_design_aggregates(): void
    {
        $tabs = collect(ClientOrdersData::statusTabs())->keyBy('value');

        $this->assertSame('all', $tabs->keys()->first());
        $this->assertSame(52, $tabs['all']['count']);
        $this->assertSame(52, ClientOrdersData::pagination()['total']);
        $this->assertSame(11, ClientOrdersData::pagination()['last_page']);
        $this->assertSame(
            array_sum(array_map(
                static fn (array $tab): int => $tab['count'],
                array_filter(ClientOrdersData::statusTabs(), static fn (array $tab): bool => $tab['value'] !== 'all'),
            )),
            $tabs['all']['count'],
        );
    }

    public function test_every_preview_order_maps_to_a_status_tab_stage(): void
    {
        $stages = array_column(ClientOrdersData::statusTabs(), 'value');
        $orders = ClientOrdersData::orders();

        $this->assertCount(5, $orders);

        foreach ($orders as $order) {
            $this->assertContains($order['stage'], $stages);
            $this->assertContains(
                $order['hub'],
                array_column(ClientOrdersData::filters()['hub']['options'], 'value'),
            );
            $this->assertContains(
                $order['payment'],
                array_column(ClientOrdersData::filters()['payment']['options'], 'value'),
            );
            $this->assertGreaterThan(0, $order['estimated']);
            $this->assertGreaterThan(0, $order['total']);
            $this->assertNotEmpty($order['actions']);
        }
    }

    public function test_deviation_percentages_match_design_tolerance_flags(): void
    {
        $orders = collect(ClientOrdersData::orders())->keyBy('po');

        $this->assertSame(0.75, $orders['ORD-GPA-202410-0089']['deviation_percent']);
        $this->assertSame(3.6, $orders['ORD-GPA-202410-0062']['deviation_percent']);
        $this->assertNull($orders['ORD-GPA-202410-0092']['deviation_label']);
        $this->assertNull($orders['ORD-GPA-202410-0095']['deviation_label']);

        $above = $orders->filter(fn (array $order): bool => $order['deviation_percent'] > 1);

        $this->assertCount(1, $above, 'Hanya satu baris yang melewati toleransi 1% sesuai design.');
    }

    public function test_weight_labels_follow_indonesian_number_format(): void
    {
        $orders = collect(ClientOrdersData::orders())->keyBy('po');

        $this->assertSame('794,0', $orders['ORD-GPA-202410-0089']['weight_label_value']);
        $this->assertSame('698,5', $orders['ORD-GPA-202410-0074']['weight_label_value']);
        $this->assertSame('800,0', $orders['ORD-GPA-202410-0089']['estimated_label']);
        $this->assertSame('150,0', $orders['ORD-GPA-202410-0095']['estimated_label']);
    }
}

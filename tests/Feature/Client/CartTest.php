<?php

namespace Tests\Feature\Client;

use App\Models\User;
use App\Support\ClientCartData;
use App\Support\ClientCatalogData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_cart_with_demo_client(): void
    {
        $response = $this->get(route('cart'));

        $response->assertOk();
        $response->assertSee('Formulir Pemesanan &amp; Draft Purchase Order', false);
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('PO-GPA-202410-092');
        $response->assertSee('Masuk Portal');
    }

    public function test_cart_renders_delivery_and_summary_sections(): void
    {
        $response = $this->get(route('cart'));

        $response->assertOk();
        $response->assertSee('Daftar Item Komoditas Keranjang');
        $response->assertSee('Parameter Pengiriman &amp; Logistik', false);
        $response->assertSee('Ringkasan Estimasi PO');
        $response->assertSee('Ajukan Pesanan Estimasi (Submit PO)');
    }

    public function test_cart_renders_all_docks_payment_methods_and_pic_roster(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('cart'));

        $response->assertOk();

        foreach (ClientCartData::docks() as $dock) {
            $response->assertSee($dock['name']);
            $response->assertSee($dock['chip']);
        }

        foreach (ClientCartData::paymentMethods() as $method) {
            $response->assertSee($method['name']);
        }

        foreach (ClientCartData::receiving()['roster'] as $person) {
            $response->assertSee($person['phone']);
        }
    }

    public function test_demo_lines_are_built_from_catalog_commodities(): void
    {
        $lines = ClientCartData::demoLines();
        $commodities = collect(ClientCatalogData::commodities())->keyBy('key');

        $this->assertCount(2, $lines);

        foreach ($lines as $line) {
            $commodity = $commodities->get($line['key']);

            $this->assertNotNull($commodity);
            $this->assertSame($commodity['sku'], $line['sku']);
            $this->assertSame($commodity['grade'], $line['grade']);
            $this->assertSame($commodity['price'], $line['price']);
            $this->assertSame($line['price'] * $line['qty'], $line['total']);
            $this->assertGreaterThanOrEqual($line['moq'], $line['qty']);
            $this->assertLessThanOrEqual($line['stock'], $line['qty']);
            $this->assertStringContainsString('Kemasan: ', $line['packaging']);
        }
    }

    public function test_demo_lines_match_design_totals(): void
    {
        $lines = ClientCartData::demoLines();

        $weight = array_sum(array_column($lines, 'qty'));
        $subtotal = array_sum(array_column($lines, 'total'));

        $this->assertSame(850, $weight);
        $this->assertSame(12625000, $subtotal);
    }

    public function test_cart_lines_can_be_hydrated_from_a_minimal_draft_entry(): void
    {
        $line = ClientCartData::line(ClientCatalogData::commodities()[0], 100);

        $this->assertArrayHasKey('sku', $line);
        $this->assertArrayHasKey('crate_kg', $line);
        $this->assertArrayHasKey('pack_label', $line);
        $this->assertSame(1500000, $line['total']);
    }
}

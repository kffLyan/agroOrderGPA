<?php

namespace Tests\Feature\Client;

use App\Support\ClientCatalogData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_catalog_with_demo_client(): void
    {
        $response = $this->get(route('catalog'));

        $response->assertOk();
        $response->assertSee('Katalog Komoditas &amp; Kuota Pasokan Terikat B2B', false);
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('CTR-2025/GPA-KPN/04');
        $response->assertSee('Masuk Portal');
    }

    public function test_catalog_renders_all_commodity_cards(): void
    {
        $response = $this->get(route('catalog'));

        $response->assertOk();

        foreach (ClientCatalogData::commodities() as $commodity) {
            $response->assertSee($commodity['code']);
            $response->assertSee($commodity['sku']);
        }
    }

    public function test_category_counts_include_every_commodity(): void
    {
        $categories = ClientCatalogData::categories();
        $commodities = ClientCatalogData::commodities();

        $this->assertSame('all', $categories[0]['value']);
        $this->assertSame(count($commodities), $categories[0]['count']);
        $this->assertSame(
            count($commodities),
            array_sum(array_column(array_slice($categories, 1), 'count')),
        );
    }

    public function test_reconciliation_rows_share_commodity_stock_and_price(): void
    {
        $commodities = collect(ClientCatalogData::commodities())->keyBy('key');
        $rows = ClientCatalogData::reconciliation()['rows'];

        $this->assertCount(count($commodities), $rows);

        foreach ($rows as $row) {
            $commodity = $commodities->get($row['key']);

            $this->assertNotNull($commodity);
            $this->assertSame($commodity['stock'], $row['stock']);
            $this->assertSame($commodity['price'], $row['price']);
            $this->assertSame($commodity['moq'], $row['moq']);
        }
    }
}

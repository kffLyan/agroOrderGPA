<?php

namespace Tests\Feature\Public;

use App\Support\PublicCatalogData;
use App\Support\PublicHomeData;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    public function test_guest_sees_public_catalog_page(): void
    {
        $response = $this->get(route('public.catalog'));

        $response->assertOk();
        $response->assertSee('AgroOrder GPA');
        $response->assertSee('Katalog Komoditas Agribisnis');
        $response->assertSee('Petani Binaan');
    }

    public function test_page_is_reachable_at_public_catalog_path(): void
    {
        $this->get('/katalog-publik')->assertOk();
    }

    public function test_title_and_breadcrumb_are_exposed(): void
    {
        $response = $this->get(route('public.catalog'));

        $response->assertSee('<title>Katalog Komoditas Agribisnis | AgroOrder GPA</title>', false);
        $response->assertSee('aria-label="Remah roti"', false);
        $response->assertSee(route('public.home'), false);
        $response->assertSee('Harga Franco Gudang / siap muat', false);
    }

    public function test_navigation_marks_catalog_as_current_page(): void
    {
        $response = $this->get(route('public.catalog'));

        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('public.catalog'), false);
        $response->assertSee('Mitra Kontrak');
        $response->assertSee('Galeri');
    }

    public function test_header_katalog_button_points_to_public_catalog(): void
    {
        $response = $this->get(route('public.catalog'));

        $response->assertSee('href="'.route('public.catalog').'"', false);
        $response->assertDontSee('href="'.route('catalog').'"', false);
    }

    public function test_navigation_links_resolve_to_existing_targets(): void
    {
        $response = $this->get(route('public.catalog'));

        foreach (PublicHomeData::navigation() as $item) {
            $href = $item['href'];

            if (str_starts_with($href, '#')) {
                $response->assertSee($href, false);

                continue;
            }

            if (str_starts_with($href, '/')) {
                $this->assertNotSame('', url($href));

                continue;
            }

            $this->assertNotSame('', route($href, [], false));
        }
    }

    public function test_hero_banner_reports_live_stock_sync(): void
    {
        $response = $this->get(route('public.catalog'));

        $hero = PublicCatalogData::hero();

        $response->assertSee($hero['lead']);
        $response->assertSee($hero['sync']['label']);
        $response->assertSee($hero['sync']['status']);
        $response->assertSee($hero['sync']['node']);

        foreach ($hero['assurances'] as $assurance) {
            $response->assertSee($assurance);
        }
    }

    public function test_toolbar_exposes_category_filter_search_and_sort(): void
    {
        $response = $this->get(route('public.catalog'));

        $toolbar = PublicCatalogData::toolbar();

        foreach ($toolbar['categories'] as $category) {
            $response->assertSee($category['label']);
        }

        foreach ($toolbar['sortOptions'] as $option) {
            $response->assertSee($option['label']);
        }

        $response->assertSee($toolbar['availableLabel']);
        $response->assertSee($toolbar['searchPlaceholder'], false);
        $response->assertSee($toolbar['totalSuffix']);
        $response->assertSee('>5</span>', false);
        $response->assertSee('x-data="clientCatalog(', false);
    }

    public function test_all_five_commodities_render_with_price_and_specs(): void
    {
        $response = $this->get(route('public.catalog'));

        foreach (PublicCatalogData::commodities() as $item) {
            $response->assertSee('id="komoditas-'.$item['key'].'"', false);
            $response->assertSee($item['sku']);
            $response->assertSee($item['name']);
            $response->assertSee($item['botanical']);
            $response->assertSee($item['latin']);
            $response->assertSee($item['franco']);
            $response->assertSee($item['stock_label']);
            $response->assertSee('Rp'.number_format($item['price'], 0, ',', '.'));
            $response->assertSee($item['extra']['value']);
            $response->assertSee("matches('{$item['key']}'", false);
        }
    }

    public function test_out_of_stock_commodity_disables_order_action(): void
    {
        $response = $this->get(route('public.catalog'));

        $soldOut = collect(PublicCatalogData::commodities())->firstWhere('available', false);

        $this->assertNotNull($soldOut);
        $response->assertSee('Stok Habis / Pre-Order Kontak Sekre');
        $response->assertSee($soldOut['stock_label']);
        $this->assertSame('0', (string) $soldOut['stock']);
        $this->assertStringNotContainsString(
            "addToDraft('{$soldOut['key']}'",
            $response->getContent(),
        );
    }

    public function test_contract_banner_links_to_b2b_registration(): void
    {
        $response = $this->get(route('public.catalog'));

        $contract = PublicCatalogData::contract();

        $response->assertSee($contract['title']);
        $response->assertSee($contract['note']);
        $response->assertSee(route($contract['cta']['href']), false);
    }

    public function test_rule08_preview_renders_validation_states_and_logistics(): void
    {
        $response = $this->get(route('public.catalog'));

        $validation = PublicCatalogData::validation();

        $response->assertSee($validation['title']);
        $response->assertSee($validation['commodity']);
        $response->assertSee($validation['ruleValue']);
        $response->assertSee($validation['download']);

        foreach ($validation['logistics'] as $row) {
            $response->assertSee($row['label']);
            $response->assertSee($row['value']);
        }
    }

    public function test_cart_summary_card_links_to_cart_page(): void
    {
        $response = $this->get(route('public.catalog'));

        $cart = PublicCatalogData::cart();

        $response->assertSee($cart['title']);
        $response->assertSee($cart['emptyTitle']);
        $response->assertSee($cart['itemsLabel']);
        $response->assertSee($cart['cta']);
        $response->assertSee('href="'.route('cart').'"', false);
        $response->assertSee('draftTotal()', false);
    }

    public function test_page_avoids_figma_layout_artifacts(): void
    {
        $html = $this->get(route('public.catalog'))->getContent();

        $this->assertStringNotContainsString('position: absolute', $html);
        $this->assertStringNotContainsString('rotate(180deg)', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
        $this->assertStringNotContainsString('https://www.figma.com', $html);
    }

    public function test_page_has_single_main_landmark_and_skip_link(): void
    {
        $response = $this->get(route('public.catalog'));

        $response->assertSee('id="main-content"', false);
        $response->assertSee('Lewati ke konten utama');
        $this->assertSame(1, substr_count($response->getContent(), '<main'));
    }

    public function test_data_class_contracts_are_stable(): void
    {
        $this->assertCount(5, PublicCatalogData::commodities());
        $this->assertCount(5, PublicCatalogData::toolbar()['categories']);
        $this->assertCount(5, PublicCatalogData::toolbar()['sortOptions']);
        $this->assertCount(3, PublicCatalogData::hero()['assurances']);
        $this->assertCount(4, PublicCatalogData::validation()['logistics']);
        $this->assertCount(2, PublicCatalogData::rules()['actions']);
        $this->assertCount(7, PublicHomeData::navigation());
        $this->assertSame(5, PublicCatalogData::toolbar()['total']);
    }
}

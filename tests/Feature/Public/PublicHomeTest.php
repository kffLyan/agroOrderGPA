<?php

namespace Tests\Feature\Public;

use App\Support\PublicHomeData;
use Tests\TestCase;

class PublicHomeTest extends TestCase
{
    public function test_guest_sees_public_beranda(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('AgroOrder GPA');
        $response->assertSee('Sistem Rantai Pasok &');
        $response->assertSee('Dari Petani Hingga Dapur');
    }

    public function test_beranda_is_reachable_at_root_path(): void
    {
        $this->get('/')->assertRedirect(route('public.home'));
    }

    public function test_status_bar_reports_node_and_hub(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('GPA-NODE: ACTIVE');
        $response->assertSee('Hub Sentral &amp; Gudang Konsolidasi Panundaan', false);
        $response->assertSee('Terkalibrasi Realtime');
    }

    public function test_navigation_links_resolve_to_existing_targets(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Tentang GPA');
        $response->assertSee('Rantai Pasok');
        $response->assertSee('Kontak');

        foreach (PublicHomeData::navigation() as $item) {
            $href = $item['href'];

            if (str_starts_with($href, '#')) {
                $response->assertSee($href, false);

                continue;
            }

            if (str_starts_with($href, '/')) {
                $response->assertSee(url($href), false);

                continue;
            }

            $this->assertNotSame('', route($href, [], false));
        }
    }

    public function test_hero_lists_operating_assurances(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Protokol Anti-Overselling:');
        $response->assertSee('Eksplorasi Katalog Komoditas');
        $response->assertSee('Lihat Galeri Operasional');
        $response->assertSee('penagihan tempo B2B yang presisi dan akuntabel.');
    }

    public function test_supply_pipeline_renders_four_stages_in_order(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Farm-to-Table Core Architecture');
        $response->assertSeeInOrder([
            'Petani Binaan & Buffer',
            'QC Timbang Riil',
            'PoD Foto & Stempel Fisik',
            'Faktur B2B (TOP / Manual)',
        ]);
        $response->assertSee('SLA Akurasi: 99.8%');
    }

    public function test_sop_section_renders_six_stages_in_order(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('SOP &amp; METODOLOGI OPERASIONAL', false);
        $response->assertSee('Prinsip Operasional Rantai Pasok Terintegrasi');

        foreach (PublicHomeData::sop()['cards'] as $card) {
            $response->assertSee($card['title']);
            $response->assertSee($card['metric']['value']);
        }

        $response->assertSeeInOrder([
            'Pasokan Terjamin & Buffer Stock',
            'Rekapitulasi Faktur & Tagihan',
        ]);
    }

    public function test_commodity_catalog_renders_all_five_skus(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Komoditas Inti Pertanian GPA');
        $response->assertSee('Update Panen: 04:00 WIB');

        foreach (PublicHomeData::catalog()['cards'] as $card) {
            $response->assertSee($card['sku']);
            $response->assertSee($card['price']);
            $response->assertSee($card['minOrder']);
        }

        $response->assertSee('Kuota Terbatas (Buffer Only)');
        $response->assertSee('Hubungi Desk Pengadaan');
    }

    public function test_governance_section_lists_three_pillars(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Keunggulan Tata Kelola &amp; Akuntabilitas Anti-Selisih', false);

        foreach (PublicHomeData::governance()['cards'] as $card) {
            $response->assertSee($card['title']);
            $response->assertSee($card['badge']);
        }
    }

    public function test_partners_section_lists_six_contracts_in_order(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Mitra &amp; Klien Kontrak Terverifikasi', false);
        $response->assertSee('SLA Kepatuhan Pasokan:');
        $response->assertSee('99.4%');

        foreach (PublicHomeData::partners()['cards'] as $card) {
            $response->assertSee($card['contract']);
            $response->assertSee($card['name']);
            $response->assertSee($card['slaValue']);
        }

        $response->assertSeeInOrder([
            'B2B-1082',
            'B2B-1094',
            'B2B-0821',
            'B2B-2019',
            'B2B-1140',
            'B2B-1205',
        ]);
        $response->assertSee('PORTFOLIO KONTRAK AKTIF // B2B ENTERPRISE', false);
    }

    public function test_sector_section_lists_three_industry_targets(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Solusi Pengadaan Berdasarkan Sektor Industri');

        foreach (PublicHomeData::sectors()['cards'] as $card) {
            $response->assertSee($card['code']);
            $response->assertSee($card['title']);
            $response->assertSee($card['focus']);
        }
    }

    public function test_gallery_renders_nine_items_with_filter_tabs(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Galeri &amp; Dokumentasi Operasional GPA', false);

        foreach (PublicHomeData::gallery()['tabs'] as $tab) {
            $response->assertSee($tab['label']);
        }

        foreach (PublicHomeData::gallery()['cards'] as $card) {
            $response->assertSee($card['title']);
        }

        $response->assertSee('x-data="{ galleryTab: \'semua\' }"', false);
        $response->assertSee('Unduh Ringkasan Kepatuhan');
    }

    public function test_footer_exposes_hubs_contacts_and_legal_links(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('Central Packhouse Panundaan');
        $response->assertSee('Call Center: (062) 8123-4567');
        $response->assertSee('Sertifikat Tera Metrologi');
        $response->assertSee('Semua Server Packhouse Operasional');
        $response->assertSee('Kebijakan Privasi');
    }

    public function test_page_avoids_figma_layout_artifacts(): void
    {
        $html = $this->get(route('public.home'))->getContent();

        $this->assertStringNotContainsString('position: absolute', $html);
        $this->assertStringNotContainsString('rotate(180deg)', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
    }

    public function test_page_has_single_main_landmark_and_skip_link(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertSee('id="main-content"', false);
        $response->assertSee('Lewati ke konten utama');
        $this->assertSame(1, substr_count($response->getContent(), '<main'));
    }

    public function test_data_class_contracts_are_stable(): void
    {
        $this->assertCount(7, PublicHomeData::navigation());
        $this->assertCount(2, PublicHomeData::hero()['headline']);
        $this->assertCount(4, PublicHomeData::hero()['schematic']['flow']);
        $this->assertCount(6, PublicHomeData::sop()['cards']);
        $this->assertCount(5, PublicHomeData::catalog()['cards']);
        $this->assertCount(3, PublicHomeData::governance()['cards']);
        $this->assertCount(6, PublicHomeData::partners()['cards']);
        $this->assertCount(3, PublicHomeData::sectors()['cards']);
        $this->assertCount(5, PublicHomeData::gallery()['tabs']);
        $this->assertCount(9, PublicHomeData::gallery()['cards']);
    }
}

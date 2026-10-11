<?php

namespace Tests\Feature\Public;

use App\Support\PublicGalleryData;
use App\Support\PublicHomeData;
use Tests\TestCase;

class PublicGalleryTest extends TestCase
{
    public function test_guest_sees_public_gallery_page(): void
    {
        $response = $this->get(route('public.gallery'));

        $response->assertOk();
        $response->assertSee('AgroOrder GPA');
        $response->assertSee('Galeri &amp; Dokumentasi Operasional', false);
    }

    public function test_page_is_reachable_at_gallery_path(): void
    {
        $this->get('/galeri')->assertOk();
    }

    public function test_title_is_exposed(): void
    {
        $this->get(route('public.gallery'))
            ->assertSee('<title>Galeri | AgroOrder GPA</title>', false);
    }

    public function test_navigation_marks_gallery_as_current_page(): void
    {
        $response = $this->get(route('public.gallery'));

        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('public.gallery'), false);
    }

    public function test_navigation_links_resolve_to_existing_targets(): void
    {
        $response = $this->get(route('public.gallery'));

        $response->assertSee('Tentang GPA');
        $response->assertSee('Mitra Kontrak');
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

    public function test_hero_reports_archive_headline_and_live_feed(): void
    {
        $response = $this->get(route('public.gallery'));
        $hero = PublicGalleryData::hero();

        $response->assertSee($hero['lead']);
        $response->assertSee('Telusuri Arsip Foto &amp; Video', false);
        $response->assertSee('Protokol Verifikasi Fisik');

        $response->assertSee($hero['feed']['title']);
        $response->assertSee($hero['feed']['status']);

        foreach ($hero['feed']['rows'] as $row) {
            $response->assertSee($row['label']);
            $response->assertSee($row['value']);
            $response->assertSee($row['note']);
        }

        $response->assertSeeInOrder([
            'HARVEST REGION',
            'SCALE CALIBRATION',
            'CHILLER ROOM',
            'DIGITAL PoD',
        ]);
    }

    public function test_hero_actions_target_existing_section_anchors(): void
    {
        $content = $this->get(route('public.gallery'))->getContent();

        foreach (PublicGalleryData::hero()['actions'] as $action) {
            $this->assertStringContainsString('href="'.$action['href'].'"', $content);
            $this->assertStringContainsString('id="'.ltrim($action['href'], '#').'"', $content);
        }
    }

    public function test_metric_cards_render_four_kpis_in_order(): void
    {
        $response = $this->get(route('public.gallery'));

        foreach (PublicGalleryData::metrics() as $metric) {
            $response->assertSee($metric['label']);
            $response->assertSee($metric['value']);
            $response->assertSee($metric['note']);
        }

        $response->assertSeeInOrder([
            'DOKUMEN TERUNGGAH',
            'AKURASI TIMBANGAN',
            'KEPATUHAN TERMAL',
            'KETEPATAN DISTRIBUSI',
        ]);
    }

    public function test_filter_toolbar_lists_five_tabs_with_counts(): void
    {
        $response = $this->get(route('public.gallery'));
        $filter = PublicGalleryData::filter();

        foreach ($filter['tabs'] as $tab) {
            $response->assertSee($tab['label'].' ('.$tab['count'].')');
        }

        $response->assertSee($filter['searchPlaceholder']);
        $response->assertSee($filter['sortLabel']);
        $response->assertSee('x-model="archiveQuery"', false);
    }

    public function test_archive_lists_six_audit_cards_in_order(): void
    {
        $response = $this->get(route('public.gallery'));
        $summary = PublicGalleryData::summary();

        foreach (PublicGalleryData::entries() as $card) {
            $response->assertSee($card['title']);
            $response->assertSee($card['image']);
            $response->assertSee($card['action']);
        }

        $response->assertSeeInOrder([
            'Panen Pagi Selada Romaine & Keriting (Lembang, Kab. Bandung Barat)',
            'Kalibrasi Harian & Penimbangan Riil Netto (Central Packhouse Bogor)',
            'Penataan Cold Storage & Kamar Pendingin +4°C (DC Ciracas)',
            'Loading Subuh Truk Reefer B 9421 TX Menuju Central Kitchen Ciracas',
            'Serah Terima Digital PoD di Loading Dock Hotel Grand Pangrango',
            'Rapid Test Residu Pestisida & Higienitas Komoditas Tomat & Brokoli',
        ]);

        $response->assertSee('MENAMPILKAN', false);
        $response->assertSee('DARI '.$summary['total'].' '.$summary['unit'], false);
        $response->assertSee('gpa:toast', false);
    }

    public function test_archive_filter_and_search_state_is_wired(): void
    {
        $content = $this->get(route('public.gallery'))->getContent();

        $this->assertStringContainsString("archiveTab: 'semua'", $content);
        $this->assertStringContainsString("archiveMatches('kebun', '", $content);
        $this->assertStringContainsString('archiveMatchCount', $content);
        $this->assertStringContainsString('x-cloak', $content);
    }

    public function test_pagination_bar_shows_current_page_and_range(): void
    {
        $response = $this->get(route('public.gallery'));
        $pagination = PublicGalleryData::pagination();

        $response->assertSee($pagination['summary']);
        $response->assertSee($pagination['prev']);
        $response->assertSee($pagination['next']);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('aria-label="Navigasi halaman arsip"', false);

        $response->assertSeeInOrder([
            $pagination['summary'],
            'SEBELUMNYA',
            '2',
            '3',
            '4',
            '8',
            'BERIKUTNYA',
        ]);
    }

    public function test_call_to_action_opens_archive_profile_modal_and_links_registration(): void
    {
        $response = $this->get(route('public.gallery'));
        $callToAction = PublicGalleryData::callToAction();
        $profile = PublicGalleryData::archiveProfile();

        $response->assertSee($callToAction['title']);
        $response->assertSee($callToAction['lead']);

        foreach ($callToAction['checks'] as $check) {
            $response->assertSee($check);
        }

        $response->assertSee(route('register'), false);
        $response->assertSee('x-on:click="$dispatch(\'gpa-modal-open\', \'profil-arsip\')"', false);
        $response->assertSee('role="dialog"', false);

        foreach ($profile['items'] as $item) {
            $response->assertSee($item);
        }

        $response->assertSee('data simulasi untuk demo');
    }

    public function test_page_avoids_figma_layout_artifacts(): void
    {
        $html = $this->get(route('public.gallery'))->getContent();

        $this->assertStringNotContainsString('position: absolute', $html);
        $this->assertStringNotContainsString('rotate(180deg)', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
        $this->assertStringNotContainsString('https://www.figma.com', $html);
    }

    public function test_page_has_single_main_landmark_and_skip_link(): void
    {
        $response = $this->get(route('public.gallery'));

        $response->assertSee('id="main-content"', false);
        $response->assertSee('Lewati ke konten utama');
        $this->assertSame(1, substr_count($response->getContent(), '<main'));
    }

    public function test_data_class_contracts_are_stable(): void
    {
        $this->assertCount(2, PublicGalleryData::hero()['headline']);
        $this->assertCount(4, PublicGalleryData::hero()['feed']['rows']);
        $this->assertCount(4, PublicGalleryData::metrics());
        $this->assertCount(5, PublicGalleryData::filter()['tabs']);
        $this->assertCount(6, PublicGalleryData::entries());
        $this->assertCount(6, PublicGalleryData::pagination()['pages']);
        $this->assertCount(3, PublicGalleryData::callToAction()['checks']);
        $this->assertCount(2, PublicGalleryData::callToAction()['actions']);
        $this->assertCount(5, PublicGalleryData::archiveProfile()['items']);
        $this->assertCount(7, PublicHomeData::navigation());
    }
}

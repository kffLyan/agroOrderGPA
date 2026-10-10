<?php

namespace Tests\Feature\Public;

use App\Support\PublicHomeData;
use App\Support\PublicMitraKontrakData;
use Tests\TestCase;

class PublicMitraKontrakTest extends TestCase
{
    public function test_guest_sees_public_mitra_kontrak_page(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertOk();
        $response->assertSee('AgroOrder GPA');
        $response->assertSee('Portofolio Kemitraan &amp; Klien Kontrak Terverifikasi', false);
    }

    public function test_page_is_reachable_at_mitra_kontrak_path(): void
    {
        $this->get('/mitra-kontrak')->assertOk();
    }

    public function test_title_and_navigation_are_exposed(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('<title>Mitra Kontrak | AgroOrder GPA</title>', false);
        $response->assertSee(route('public.home'), false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_navigation_marks_mitra_kontrak_as_current_page(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('public.mitra-kontrak'), false);
    }

    public function test_navigation_links_resolve_to_existing_targets(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

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

    public function test_hero_reports_accreditation_and_serving_index(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $hero = PublicMitraKontrakData::hero();

        $response->assertSee($hero['lead']);
        $response->assertSee('Ajukan Draf Kontrak B2B');
        $response->assertSee('Format Template PKS');

        $response->assertSee($hero['index']['title']);

        foreach ($hero['index']['metrics'] as $metric) {
            $response->assertSee($metric['label']);
            $response->assertSee($metric['value']);
            $response->assertSee($metric['note']);
        }
    }

    public function test_client_cards_render_three_sectors_in_order(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('Klien &amp; Kontrak Aktif', false);

        foreach (PublicMitraKontrakData::clients()['cards'] as $card) {
            $response->assertSee($card['sector']);
            $response->assertSee($card['title']);
            $response->assertSee($card['body']);
            $response->assertSee($card['badge']);

            foreach ($card['details'] as $detail) {
                $response->assertSee($detail['label']);
                $response->assertSee($detail['value']);
            }
        }

        $response->assertSeeInOrder([
            'Inflight Catering & Central Kitchen',
            'Hotel & Hospitalitas B2B',
            'Retail Modern & Supermarket',
        ]);
        $response->assertSee('Kontrak Aktif');
    }

    public function test_poktan_section_lists_binaan_cards_and_total_area(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('Kelompok Tani Binaan');
        $response->assertSee('142.5 Hektare');
        $response->assertSee('Binaan Aktif');

        foreach (PublicMitraKontrakData::poktan()['cards'] as $card) {
            $response->assertSee($card['name']);
            $response->assertSee($card['region']);
            $response->assertSee($card['area']);
            $response->assertSee($card['members']);
        }

        $response->assertSeeInOrder([
            'Gapoktan Cipanas Berkah',
            'Poktan Mandiri Lembang',
            'Kemitraan Petani Ciwidey',
        ]);
    }

    public function test_onboarding_lists_four_phases_in_order(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('Alur Onboarding Kontrak B2B');
        $response->assertSee('B2B ONBOARDING FLOW', false);

        foreach (PublicMitraKontrakData::onboarding()['phases'] as $phase) {
            $response->assertSee($phase['tag']);
            $response->assertSee($phase['title']);
            $response->assertSee($phase['meta']['value']);
        }

        $response->assertSeeInOrder([
            'Pengajuan & Dokumen',
            'Uji Sampel & Harga',
            'PKS & Alokasi Kuota',
            'Kirim & Monitoring',
        ]);
    }

    public function test_call_to_action_opens_template_modal_and_links_registration(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('Siap Berlangganan Pasokan Kontrak Terverifikasi?');
        $response->assertSee(route('register'), false);
        $response->assertSee('x-on:click="$dispatch(\'gpa-modal-open\', \'template-pks\')"', false);
        $response->assertSee('role="dialog"', false);

        foreach (PublicMitraKontrakData::templatePks()['items'] as $item) {
            $response->assertSee($item);
        }
    }

    public function test_hero_actions_resolve_to_register_route_and_modal(): void
    {
        $content = $this->get(route('public.mitra-kontrak'))->getContent();

        $this->assertStringContainsString('href="'.route('register').'"', $content);
        $this->assertStringContainsString("gpa-modal-open', 'template-pks'", $content);
        $this->assertStringContainsString('role="dialog"', $content);
    }

    public function test_disclaimer_notes_simulated_data(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('Kontrak ditandatangani digital dan diverifikasi sistem sebelum pengiriman dimulai.');
        $response->assertSee('Verifikasi Kontrak');
    }

    public function test_page_avoids_figma_layout_artifacts(): void
    {
        $html = $this->get(route('public.mitra-kontrak'))->getContent();

        $this->assertStringNotContainsString('position: absolute', $html);
        $this->assertStringNotContainsString('rotate(180deg)', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
        $this->assertStringNotContainsString('https://www.figma.com', $html);
    }

    public function test_page_has_single_main_landmark_and_skip_link(): void
    {
        $response = $this->get(route('public.mitra-kontrak'));

        $response->assertSee('id="main-content"', false);
        $response->assertSee('Lewati ke konten utama');
        $this->assertSame(1, substr_count($response->getContent(), '<main'));
    }

    public function test_data_class_contracts_are_stable(): void
    {
        $this->assertCount(4, PublicMitraKontrakData::servingIndex()['metrics']);
        $this->assertCount(3, PublicMitraKontrakData::clients()['cards']);
        $this->assertCount(3, PublicMitraKontrakData::poktan()['cards']);
        $this->assertCount(4, PublicMitraKontrakData::onboarding()['phases']);
        $this->assertCount(2, PublicMitraKontrakData::callToAction()['actions']);
        $this->assertCount(5, PublicMitraKontrakData::templatePks()['items']);
        $this->assertCount(7, PublicHomeData::navigation());
    }
}
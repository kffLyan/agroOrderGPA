<?php

namespace Tests\Feature\Public;

use App\Support\PublicHomeData;
use App\Support\PublicSupplyChainData;
use Tests\TestCase;

class PublicSupplyChainTest extends TestCase
{
    public function test_guest_sees_public_supply_chain_page(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertOk();
        $response->assertSee('AgroOrder GPA');
        $response->assertSee('Arsitektur Rantai Pasok Terintegrasi Dari Petani Hingga');
        $response->assertSee('Dapur Komersial');
    }

    public function test_page_is_reachable_at_supply_chain_path(): void
    {
        $this->get('/rantai-pasok')->assertOk();
    }

    public function test_title_and_breadcrumb_are_exposed(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee('<title>Rantai Pasok Terintegrasi | AgroOrder GPA</title>', false);
        $response->assertSee('aria-label="Remah roti"', false);
        $response->assertSee(route('public.home'), false);
        $response->assertSee('aria-current="page"', false);
    }

    public function test_navigation_marks_supply_chain_as_current_page(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('public.supply-chain'), false);
    }

    public function test_navigation_links_resolve_to_existing_targets(): void
    {
        $response = $this->get(route('public.supply-chain'));

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

    public function test_blueprint_panel_reports_locked_controls(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee('ARCH_BLUEPRINT // PIPELINE');
        $response->assertSee('SYS_REV: 2025.2');
        $response->assertSee('[Input: Gudang Tera Sah]');
        $response->assertSee('ACTUAL_NETTO');
        $response->assertSee('Field Shrinkage Cap');
        $response->assertSee('AUTO-LOCKED');
        $response->assertSee('Sah Ditjen Metrologi');
        $response->assertSee('Terkunci via e-PoD');
    }

    public function test_headline_metrics_render_four_cards(): void
    {
        $response = $this->get(route('public.supply-chain'));

        foreach (PublicSupplyChainData::headlineMetrics() as $metric) {
            $response->assertSee($metric['label']);
            $response->assertSee($metric['value']);
            $response->assertSee($metric['note']);
        }
    }

    public function test_node_rail_lists_six_stages_with_active_checkpoint(): void
    {
        $response = $this->get(route('public.supply-chain'));

        foreach (PublicSupplyChainData::nodes() as $node) {
            $response->assertSee($node['label']);
        }

        $this->assertSame('Timbang Tera Sah', collect(PublicSupplyChainData::nodes())->firstWhere('active', true)['label']);
        $response->assertSee('Timbang Tera Sah');
    }

    public function test_stage_cards_render_in_order_with_critical_badge(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSeeInOrder([
            'Pasokan Terjamin Petani Binaan & Buffer Stock Terkendali',
            'Intake Gate & Sortir Mutu Hub (Bogor, Cianjur, Lembang)',
            'Penimbangan Bersih Riil Gudang Tera Sah Metrologi',
            'Penerbitan Surat Jalan Sah (SJ-GPA-YYYYMM-XXXX)',
            'Distribusi Reefer Dingin (4°C - 8°C) & Serah Terima Subuh',
            'Proof of Delivery (PoD) Digital & Faktur Konsolidasi TOP',
        ]);

        $response->assertSee('Critical Checkpoint');
        $response->assertSee('[SCALE: METROLOGY_SEALED]');
        $response->assertSee('[DOC: SJ-GPA-202502-0891]');
        $response->assertSee('NET_KG = GROSS - TARE_STD');
    }

    public function test_stage_meta_pairs_render_labels_and_values(): void
    {
        $response = $this->get(route('public.supply-chain'));

        foreach (PublicSupplyChainData::stages() as $stage) {
            $response->assertSee($stage['stage']);
            $response->assertSee($stage['token']);

            foreach ($stage['meta'] as $meta) {
                $response->assertSee($meta['label']);
                $response->assertSee($meta['value']);
            }
        }
    }

    public function test_quality_control_table_renders_all_rows(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee('Spesifikasi Standar Kontrol Mutu &amp; Cold-Chain', false);
        $response->assertSee('Parameter Ambang Batas Kontrol Mutu');
        $response->assertSee('<table', false);
        $response->assertSee('<th scope="col"', false);

        foreach (PublicSupplyChainData::qualityControl()['rows'] as $row) {
            $response->assertSee($row['category']);
            $response->assertSee($row['metric']);
            $response->assertSee($row['status']['label']);
        }

        $response->assertSee('AUDIT READY: TRUE');
    }

    public function test_return_protocol_lists_three_steps_and_stamp(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee(PublicSupplyChainData::returnProtocol()['title']);

        foreach (PublicSupplyChainData::returnProtocol()['steps'] as $step) {
            $response->assertSee($step['title']);
        }

        $response->assertSee('[Digital_Verification_Stamp]');
        $response->assertSee('STATUS: PRD_COMPLIANT');
    }

    public function test_infrastructure_section_lists_three_cards(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee('Kapasitas Infrastruktur Fisik Terintegrasi');

        foreach (PublicSupplyChainData::infrastructure()['cards'] as $card) {
            $response->assertSee($card['label']);
            $response->assertSee($card['value']);
        }

        $response->assertSee('42 Kelompok Tani Binaan');
        $response->assertSee('18 Unit Armada Berpendingin');
        $response->assertSee('99.8% On-Time Delivery Subuh');
    }

    public function test_call_to_action_opens_sop_modal_and_links_registration(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee('Siap Mengintegrasikan Kebutuhan Bahan Baku Restoran &amp; Hotel Anda?', false);
        $response->assertSee(route('register'), false);
        $response->assertSee('x-on:click="$dispatch(\'gpa-modal-open\', \'sop\')"', false);
        $response->assertSee('role="dialog"', false);

        foreach (PublicSupplyChainData::callToAction()['procedure']['items'] as $item) {
            $response->assertSee($item);
        }
    }

    public function test_hero_actions_target_existing_section_anchors(): void
    {
        $content = $this->get(route('public.supply-chain'))->getContent();

        foreach (PublicSupplyChainData::hero()['actions'] as $action) {
            $this->assertStringContainsString('href="'.$action['href'].'"', $content);
            $this->assertStringContainsString('id="'.ltrim($action['href'], '#').'"', $content);
        }
    }

    public function test_anchor_links_resolve_to_real_sections_from_any_public_page(): void
    {
        $this->get(route('public.supply-chain'))->assertOk();

        $links = array_merge(PublicHomeData::navigation(), PublicHomeData::footer()['navigation']);
        $this->assertNotEmpty($links);

        foreach ($links as $link) {
            if (! str_contains($link['href'], '#')) {
                continue;
            }

            [$path, $fragment] = explode('#', $link['href'], 2);

            // Fragment kosong ("/#") menunjuk ke bagian paling atas halaman.
            if ($fragment === '') {
                $this->assertNotSame('', $path);
                $this->get(url($path))->assertOk();

                continue;
            }

            $target = $path === ''
                ? $this->get(route('public.home'))->getContent()
                : $this->get(url($path))->getContent();

            $this->assertStringContainsString(
                'id="'.$fragment.'"',
                $target,
                "Link [{$link['label']}] points to a missing section: {$link['href']}"
            );
        }
    }

    public function test_page_avoids_figma_layout_artifacts(): void
    {
        $html = $this->get(route('public.supply-chain'))->getContent();

        $this->assertStringNotContainsString('position: absolute', $html);
        $this->assertStringNotContainsString('rotate(180deg)', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
    }

    public function test_page_has_single_main_landmark_and_skip_link(): void
    {
        $response = $this->get(route('public.supply-chain'));

        $response->assertSee('id="main-content"', false);
        $response->assertSee('Lewati ke konten utama');
        $this->assertSame(1, substr_count($response->getContent(), '<main'));
    }

    public function test_data_class_contracts_are_stable(): void
    {
        $this->assertCount(4, PublicSupplyChainData::headlineMetrics());
        $this->assertCount(6, PublicSupplyChainData::nodes());
        $this->assertCount(6, PublicSupplyChainData::stages());
        $this->assertCount(5, PublicSupplyChainData::qualityControl()['rows']);
        $this->assertCount(3, PublicSupplyChainData::returnProtocol()['steps']);
        $this->assertCount(3, PublicSupplyChainData::infrastructure()['cards']);
        $this->assertCount(1, collect(PublicSupplyChainData::stages())->where('critical', true));
    }
}

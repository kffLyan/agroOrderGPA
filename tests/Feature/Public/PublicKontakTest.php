<?php

namespace Tests\Feature\Public;

use App\Support\PublicHomeData;
use App\Support\PublicKontakData;
use Tests\TestCase;

class PublicKontakTest extends TestCase
{
    public function test_guest_sees_public_kontak_page(): void
    {
        $response = $this->get(route('public.kontak'));

        $response->assertOk();
        $response->assertSee('AgroOrder GPA');
        $response->assertSee('Kontak & Layanan Pengadaan');
    }

    public function test_page_is_reachable_at_kontak_path(): void
    {
        $this->get('/kontak')->assertOk();
    }

    public function test_title_is_exposed(): void
    {
        $this->get(route('public.kontak'))
            ->assertSee('<title>Kontak | AgroOrder GPA</title>', false);
    }

    public function test_navigation_marks_kontak_as_current_page(): void
    {
        $response = $this->get(route('public.kontak'));

        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('public.kontak'), false);
    }

    public function test_navigation_links_resolve_to_existing_targets(): void
    {
        $response = $this->get(route('public.kontak'));

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

    public function test_hero_reports_headline_and_sla(): void
    {
        $response = $this->get(route('public.kontak'));
        $hero = PublicKontakData::hero();

        $response->assertSee($hero['lead']);
        $response->assertSee($hero['sla']['commitment']);
        $response->assertSee($hero['sla']['status']);
        $response->assertSee($hero['sla']['prefix']);
        $response->assertSee($hero['sla']['highlight']);
        $response->assertSee($hero['sla']['suffix']);

        $response->assertSeeInOrder([
            'Kontak & Layanan Pengadaan',
            'Agribisnis Terpadu',
        ]);
    }

    public function test_directory_lists_four_entries_in_order(): void
    {
        $response = $this->get(route('public.kontak'));
        $directory = PublicKontakData::directory();

        $response->assertSee($directory['title']);

        foreach ($directory['cards'] as $card) {
            $response->assertSee($card['title']);
        }

        foreach ($directory['channels']['rows'] as $row) {
            $response->assertSee($row['title']);
            $response->assertSee($row['subtitle']);
            $response->assertSee($row['value']);
        }

        $response->assertSeeInOrder([
            'Kantor Pusat & Sekretariat GPA',
            'Sentral Distribusi & Cold Storage',
            'Hub Pengumpulan Pasokan Kebun',
            'JALUR KOMUNIKASI BERDASARKAN KEBUTUHAN',
        ]);
    }

    public function test_directory_rows_render_contact_details(): void
    {
        $response = $this->get(route('public.kontak'));

        $response->assertSee('Menara Agro Niaga Lt. 8, Jl. TB Simatupang No. 45, Jakarta Selatan');
        $response->assertSee('(021) 7829-4091');
        $response->assertSee('Senin – Jumat');
        $response->assertSee('kemitraan@agropastiada.co.id');
        $response->assertSee('GPA Central Packhouse & Cold-Chain DC, Jl. Raya Mayor Oking No. 118, Cibinong, Kab. Bogor 16918 (Akses Langsung Tol Jagorawi)');
        $response->assertSee('+62 811-9284-019');
        $response->assertSee('Kapasitas Fasilitas: 85 Ton Chiller & Pre-Cooling Unit');
        $response->assertSee('Jl. Raya Ciater No. 22, Subang, Jawa Barat');
        $response->assertSee('Jl. Tangkuban Perahu KM 4, Lembang & Pacet, Cianjur');
        $response->assertSee('Pak Rahmat Hidayat & Tim Agronomis Regional Jawa Barat');
    }

    public function test_form_renders_all_field_groups(): void
    {
        $response = $this->get(route('public.kontak'));
        $form = PublicKontakData::form();

        $response->assertSee($form['title']);
        $response->assertSee($form['lead']);

        foreach ($form['rows'] as $row) {
            foreach ($row['fields'] as $field) {
                $response->assertSee($field['label']);
                $response->assertSee($field['placeholder']);
            }
        }

        $response->assertSee($form['commodities']['label']);
        $response->assertSee($form['commodities']['volumeLabel']);
        $response->assertSee($form['commodities']['volumePlaceholder']);
        $response->assertSee($form['payments']['label']);
        $response->assertSee($form['notes']['label']);
        $response->assertSee($form['notes']['placeholder']);

        $response->assertSeeInOrder([
            '1. NAMA LENGKAP PIC & JABATAN:',
            '2. NAMA PERUSAHAAN / INSTANSI:',
            '3. JENIS USAHA:',
            '4A. EMAIL BISNIS RESMI:',
            '4B. NO. TELEPON / WHATSAPP:',
        ]);
    }

    public function test_form_lists_commodity_and_payment_options_with_defaults(): void
    {
        $response = $this->get(route('public.kontak'));
        $form = PublicKontakData::form();

        foreach ($form['commodities']['options'] as $option) {
            $response->assertSee('value="'.$option['label'].'"', false);
        }

        foreach ($form['payments']['options'] as $option) {
            $response->assertSee('value="'.$option['label'].'"', false);
        }

        $content = $response->getContent();

        $this->assertSame(6, substr_count($content, 'type="checkbox"'));
        $this->assertSame(3, substr_count($content, 'type="radio"'));
        $this->assertSame(2, substr_count($content, 'checked'));
        $this->assertSame('Tomat Beef A', $form['commodities']['options'][1]['label']);
        $this->assertTrue($form['commodities']['options'][1]['checked']);
        $this->assertTrue($form['payments']['options'][0]['checked']);
    }

    public function test_form_submits_as_demo_and_shows_disclaimer(): void
    {
        $response = $this->get(route('public.kontak'));
        $form = PublicKontakData::form();

        $response->assertSee($form['submit']);
        $response->assertSee($form['footnote']);
        $response->assertSee('x-on:submit.prevent', false);
        $response->assertSee('gpa:toast', false);
        $response->assertSee('<form', false);
    }

    public function test_schematic_renders_nodes_arrows_and_notes(): void
    {
        $response = $this->get(route('public.kontak'));
        $schematic = PublicKontakData::schematic();

        $response->assertSee($schematic['title']);

        foreach ($schematic['nodes'] as $node) {
            $response->assertSee($node['label']);
            $response->assertSee($node['title']);
            $response->assertSee($node['desc']);
            $response->assertSee($node['chip']);
        }

        foreach ($schematic['arrows'] as $arrow) {
            $response->assertSee($arrow['top']);
            $response->assertSee($arrow['bottom']);
        }

        $response->assertSee('════►', false);

        foreach ($schematic['notes'] as $note) {
            $response->assertSee($note['label']);
            $response->assertSee($note['text']);
        }

        $response->assertSeeInOrder([
            'HUB LEMBANG & SUBANG',
            '2.5 Jam Transit',
            'DC CIBINONG (KM 27)',
            '~35 Menit (Subuh)',
            'RING-1 JAKARTA & HOTEL',
        ]);
    }

    public function test_page_avoids_figma_layout_artifacts(): void
    {
        $html = $this->get(route('public.kontak'))->getContent();

        $this->assertStringNotContainsString('position: absolute', $html);
        $this->assertStringNotContainsString('rotate(180deg)', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
        $this->assertStringNotContainsString('https://www.figma.com', $html);
    }

    public function test_page_has_single_main_landmark_and_skip_link(): void
    {
        $response = $this->get(route('public.kontak'));

        $response->assertSee('id="main-content"', false);
        $response->assertSee('Lewati ke konten utama');
        $this->assertSame(1, substr_count($response->getContent(), '<main'));
    }

    public function test_data_class_contracts_are_stable(): void
    {
        $this->assertCount(2, PublicKontakData::hero()['headline']);
        $this->assertCount(3, PublicKontakData::directory()['cards']);
        $this->assertCount(3, PublicKontakData::directory()['channels']['rows']);
        $this->assertCount(4, PublicKontakData::form()['rows']);
        $this->assertCount(6, PublicKontakData::form()['commodities']['options']);
        $this->assertCount(3, PublicKontakData::form()['payments']['options']);
        $this->assertCount(3, PublicKontakData::schematic()['nodes']);
        $this->assertCount(2, PublicKontakData::schematic()['arrows']);
        $this->assertCount(3, PublicKontakData::schematic()['notes']);
        $this->assertCount(7, PublicHomeData::navigation());
    }
}

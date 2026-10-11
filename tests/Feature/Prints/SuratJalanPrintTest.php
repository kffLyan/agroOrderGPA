<?php

namespace Tests\Feature\Prints;

use App\Support\ClientCatalogData;
use App\Support\SecretaryDispatchData;
use Tests\TestCase;

class SuratJalanPrintTest extends TestCase
{
    public function test_print_page_renders_canonical_surat_jalan_document(): void
    {
        $response = $this->get(route('prints.surat-jalan'));

        $response->assertOk();
        $response->assertSee('SURAT JALAN');
        $response->assertSee('SJ-GPA-202410-0115');
        $response->assertSee('#ORD-GPA-202410-092');
        $response->assertSee('24 Oktober 2024');
        $response->assertSee('08:40 WIB');
        $response->assertSee('Kembali');
        $response->assertSee('Cetak Surat Jalan (Ctrl+P)');
        $response->assertSee('LEMBAR 1 / 3');
    }

    public function test_print_page_renders_parties_and_transport_details(): void
    {
        $response = $this->get(route('prints.surat-jalan'));

        $response->assertOk();
        $response->assertSee('GREEN PASUNDAN AGRIKULTUR');
        $response->assertSee('Jl. Pergudangan Agroniaga Kav. 14, Subang, Jawa Barat 41285');
        $response->assertSee('TUJUAN PENGIRIMAN');
        $response->assertSee('PT KULINER PRIMA NUSANTARA');
        $response->assertSee('Central Kitchen (CK) Ciracas Blok B-08');
        $response->assertSee('PIC Lapangan: Sdr. Rian Pramono (0812-8891-2311)');
        $response->assertSee('DETAIL PENGANGKUTAN');
        $response->assertSee('Isuzu Elf Box Pendingin (Reefer)');
        $response->assertSee('B 9421 TX');
        $response->assertSee('Joko Widodo');
        $response->assertSee('Supir: Joko Widodo | SIM BII Umum');
    }

    public function test_table_header_and_total_rows_use_brand_color(): void
    {
        $response = $this->get(route('prints.surat-jalan'));

        $response->assertOk();
        $response->assertSee('KODE BARANG');
        $response->assertSee('TOTAL AKUMULASI MUATAN & NILAI BARANG');
        $this->assertSame(
            2,
            substr_count($response->getContent(), '<tr class="bg-brand text-white">'),
        );
    }

    public function test_items_and_totals_reconcile_with_catalog_prices(): void
    {
        $response = $this->get(route('prints.surat-jalan'));

        $response->assertOk();

        $row = collect(SecretaryDispatchData::rows())->firstWhere('sj', 'SJ-GPA-202410-0115');
        $commodities = collect(ClientCatalogData::commodities());
        $totalQty = 0.0;
        $totalAmount = 0.0;

        foreach ($row['document']['lines'] as $line) {
            $word = strtok($line['name'], ' ');
            $commodity = $commodities->first(
                fn (array $item): bool => str_starts_with(strtolower($item['name']), strtolower($word)),
            );
            $qty = (float) str_replace(' kg', '', $line['netto']);
            $amount = $qty * $commodity['price'];
            $totalQty += $qty;
            $totalAmount += $amount;

            $response->assertSee($commodity['sku']);
            $response->assertSee($line['name']);
            $response->assertSee(number_format($qty, 1, ',', '.'));
            $response->assertSee(number_format($commodity['price'], 0, ',', '.'));
            $response->assertSee(number_format($amount, 0, ',', '.'));
        }

        $response->assertSee(number_format($totalQty, 1, ',', '.').' kg');
        $response->assertSee('Rp '.number_format($totalAmount, 0, ',', '.'));
    }

    public function test_print_page_renders_signature_blocks(): void
    {
        $response = $this->get(route('prints.surat-jalan'));

        $response->assertOk();
        $response->assertSee('Menerima');
        $response->assertSee('Pengangkut Armada');
        $response->assertSee('Supir Logistik');
        $response->assertSee('Hormat Kami');
        $response->assertSee('Koperasi Produsen Green Pasundan Agriculture');
        $response->assertSee('Ir. Bambang Sutrisno');
        $response->assertSee('24/10/2024');
        $response->assertSee('____/____/____');
    }
}

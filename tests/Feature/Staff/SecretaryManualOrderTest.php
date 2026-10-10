<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Support\SecretaryManualOrderData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SecretaryManualOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data Alpine ditulis sebagai `JSON.parse('...')` sehingga nilai yang hanya
     * hidup di payload harus dicocokkan lewat ekspresi `Js::from()`.
     */
    private function assertJsPayload(TestResponse $response, mixed $payload): void
    {
        $response->assertSee((string) Js::from($payload), false);
    }

    public function test_guest_can_preview_manual_order_form_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee('Input Order Manual');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_form_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_form_renders_supply_chain_guardrails_policy_banner(): void
    {
        $policy = SecretaryManualOrderData::policy();

        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee($policy['status'], false);
        $response->assertSee($policy['body'], false);
        $response->assertSee('Rule 02 // Rule 03 // Rule 04', false);
    }

    public function test_form_renders_source_channels_and_reference_metadata(): void
    {
        $source = SecretaryManualOrderData::source();

        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();

        foreach ($source['channels'] as $channel) {
            $response->assertSee($channel['label']);
        }

        $response->assertSee($source['reference']);
        $response->assertSee($source['received']);
        $response->assertSee($source['validator'], false);
        $response->assertSee($source['warehouse'], false);
    }

    public function test_form_renders_registered_client_credit_and_unloading_point(): void
    {
        $client = SecretaryManualOrderData::client();

        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();

        foreach ($client['modes'] as $mode) {
            $response->assertSee($mode['label']);
        }

        $response->assertSee($client['name']);
        $response->assertSee($client['code']);
        $response->assertSee($client['contact'], false);
        $response->assertSee($client['credit']['status'], false);
        $response->assertSee($client['credit']['remaining_label']);
        $response->assertSee($client['credit']['limit_label']);
        $this->assertJsPayload($response, $client);
        $response->assertSee($client['dock'], false);
    }

    public function test_credit_figures_stay_consistent_with_maximum_credit_limit(): void
    {
        $credit = SecretaryManualOrderData::client()['credit'];

        $this->assertSame(50000000, $credit['limit']);
        $this->assertSame(11550000, $credit['used']);
        $this->assertSame(38450000, $credit['limit'] - $credit['used']);
        $this->assertSame(23.1, round(($credit['used'] / $credit['limit']) * 100, 1));
    }

    public function test_form_renders_commodity_rows_with_stock_and_minimum_order_basis(): void
    {
        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();

        foreach (SecretaryManualOrderData::commodities() as $commodity) {
            $response->assertSee($commodity['sku'], false);
            $response->assertSee($commodity['grade'], false);
        }

        $this->assertJsPayload($response, SecretaryManualOrderData::commodities());

        $response->assertSee('Stok Sistem');
        $response->assertSee('Min. Order');
        $response->assertSee('Tambah Baris');
        $response->assertSee('Evaluasi Stok [PASS]', false);
    }

    public function test_default_commodity_rows_pass_stock_and_minimum_order_rules(): void
    {
        foreach (SecretaryManualOrderData::commodities() as $commodity) {
            $this->assertGreaterThanOrEqual(
                $commodity['min_order'],
                $commodity['quantity'],
                $commodity['name'].' harus memenuhi minimum order.'
            );

            $this->assertLessThanOrEqual(
                $commodity['stock'],
                $commodity['quantity'],
                $commodity['name'].' tidak boleh melebihi stok sistem.'
            );

            $this->assertSame(
                $commodity['stock'],
                $commodity['stock_bud'] + $commodity['stock_buffer']
            );
        }
    }

    public function test_default_commodity_rows_reconcile_with_design_totals(): void
    {
        $rows = SecretaryManualOrderData::commodities();

        $this->assertSame(3, count($rows));
        $this->assertSame(350, array_sum(array_column($rows, 'quantity')));
        $this->assertSame(
            6300000,
            array_sum(array_map(
                static fn (array $row): int => $row['quantity'] * $row['price'],
                $rows
            ))
        );

        $fee = SecretaryManualOrderData::logistics()['fee'];

        $this->assertSame(250000, $fee);
        $this->assertSame(6550000, 6300000 + $fee);
    }

    public function test_catalog_offers_additional_commodities_for_new_rows(): void
    {
        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();

        foreach (SecretaryManualOrderData::catalog() as $item) {
            $response->assertSee($item['name']);
            $response->assertSee($item['sku'], false);
        }
    }

    public function test_form_renders_logistics_schedule_vehicle_and_flat_fee(): void
    {
        $logistics = SecretaryManualOrderData::logistics();

        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee($logistics['label']);
        $response->assertSee($logistics['date_label']);
        $response->assertSee($logistics['arrival_label']);
        $response->assertSee($logistics['fee_note']);
        $this->assertJsPayload($response, $logistics);
    }

    public function test_form_renders_payment_methods_and_transfer_evidence(): void
    {
        $payment = SecretaryManualOrderData::payment();

        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();

        foreach ($payment['methods'] as $method) {
            $response->assertSee($method['label']);
        }

        $response->assertSee($payment['file'], false);
        $response->assertSee($payment['size'], false);
        $response->assertSee($payment['hash'], false);
        $response->assertSee($payment['hash_note'], false);
        $response->assertSee('Rule 11 &amp; 15', false);
    }

    public function test_form_renders_summary_totals_and_action_buttons(): void
    {
        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee('Total Berat');
        $response->assertSee('Subtotal Komoditas');
        $response->assertSee('Ongkos Kirim');
        $response->assertSee('Total Estimasi');
        $response->assertSee('Belum Termasuk PPN', false);
        $response->assertSee('Batal / Kembali');
        $response->assertSee('Simpan Draf PO');
        $response->assertSee('Verifikasi &amp; Teruskan', false);
    }

    public function test_form_wires_alpine_component_and_live_totals(): void
    {
        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee('secretaryManualOrder(', false);
        $response->assertSee('x-text="kg(totalWeight)"', false);
        $response->assertSee('x-text="rupiah(subtotal)"', false);
        $response->assertSee('x-text="rupiah(total)"', false);
        $response->assertSee('x-model.number="row.quantity"', false);
    }

    public function test_manual_order_page_keeps_verification_menu_item_active(): void
    {
        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.verification').'" class="flex items-center gap-3 rounded-lg px-4 py-2.5 gpa-nav transition-colors rounded-r-lg border-l-4 border-accent bg-brand font-semibold capitalize text-accent"', false);
    }

    public function test_topbar_input_order_button_links_to_manual_order_form(): void
    {
        $response = $this->get(route('secretary.verification.manual'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.verification.manual').'"', false);
        $response->assertDontSee('Formulir order manual dibuka pada modul Pemesanan.', false);
    }

    public function test_verification_console_remains_reachable_at_its_own_route(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee(route('secretary.verification.manual'), false);
    }
}

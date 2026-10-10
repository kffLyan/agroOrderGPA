<?php

namespace Tests\Feature\Client;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_dashboard_with_demo_client(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('Masuk Portal');
    }

    public function test_dashboard_renders_contract_summary_for_authenticated_client(): void
    {
        $user = User::factory()->create([
            'business_name' => 'PT Kuliner Prima Nusantara',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Ringkasan &amp; Operasional Klien B2B', false);
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('CTR-GPA-B2B-2024-08');
        $response->assertSee('ORD-GPA-202410-0089');
        $response->assertSee('Telemetri Chiller');
        $response->assertSee('Quick Reorder Kontrak');
    }

    public function test_dashboard_falls_back_to_contact_name_without_business_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Joko Santoso',
            'business_name' => null,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Joko Santoso');
    }
}

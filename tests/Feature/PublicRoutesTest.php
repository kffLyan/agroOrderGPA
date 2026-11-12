<?php

namespace Tests\Feature;

use App\Models\HarvestBatch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_pages_render_successfully(): void
    {
        $user = \App\Models\User::factory()->create([
            'role' => 'KOORDINATOR',
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-TMT01',
            'name'          => 'Tomat Cherry Hidroponik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 25000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Poktan Sukamaju',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 500,
            'available_quantity' => 400,
            'inputted_by'        => $user->id,
        ]);

        $routes = [
            '/',
            '/beranda',
            '/tentang-gpa',
            '/mitra-kontrak',
            '/katalog-publik',
            '/galeri',
            '/rantai-pasok',
            '/kontak',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_loading_screen_renders_successfully(): void
    {
        $response = $this->get('/loading');
        $response->assertStatus(200);
        $response->assertSee('Memuat');
        $response->assertSee('gL');
    }

    public function test_custom_error_pages_render_correctly(): void
    {
        $response = $this->get('/non-existent-route-for-testing-404');
        $response->assertStatus(404);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('GPA Error Protocol');

        // Test rendering of other error views directly
        $view403 = view('errors.403')->render();
        $this->assertStringContainsString('403', $view403);
        $this->assertStringContainsString('Akses Ditolak', $view403);

        $view419 = view('errors.419')->render();
        $this->assertStringContainsString('419', $view419);
        $this->assertStringContainsString('Sesi Kedaluwarsa', $view419);

        $view429 = view('errors.429')->render();
        $this->assertStringContainsString('429', $view429);
        $this->assertStringContainsString('Terlalu Banyak Permintaan', $view429);

        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('500', $view500);
        $this->assertStringContainsString('Terjadi Kesalahan Server', $view500);
    }
}

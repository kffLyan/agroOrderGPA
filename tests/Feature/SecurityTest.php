<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. PENGUJIAN PROTEKSI CSRF:
     * Middleware ValidateCsrfToken wajib menolak setiap request POST tanpa token CSRF yang valid (TokenMismatchException).
     */
    public function test_post_routes_reject_requests_without_csrf_token(): void
    {
        $middleware = new class(app(), app('encrypter')) extends ValidateCsrfToken {
            protected function runningUnitTests(): bool
            {
                return false; // Simulasi kondisi riil production web (bukan bypass unit-test)
            }
        };

        $request = Request::create('/login', 'POST', [
            'email'    => 'direktur@gpa.co.id',
            'password' => 'password123',
        ]);
        $session = app('session.store');
        $session->start();
        $request->setLaravelSession($session);

        // Middleware CSRF wajib melempar TokenMismatchException saat token tidak ada / tidak cocok
        $this->expectException(TokenMismatchException::class);
        $middleware->handle($request, fn () => response('ok'));
    }

    /**
     * 2. PENGUJIAN ANTI-SQL INJECTION (Authentication Bypass Protection):
     * Serangan SQL Injection umum pada form login ditolak secara aman oleh PDO Prepared Statements.
     */
    public function test_sql_injection_payload_does_not_bypass_authentication(): void
    {
        User::factory()->create([
            'email'    => 'admin@gpa.co.id',
            'password' => bcrypt('secret123'),
        ]);

        $sqlInjectionPayloads = [
            "' OR '1'='1",
            "admin@gpa.co.id' --",
            "' UNION SELECT * FROM users --",
            "admin@gpa.co.id' /*",
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $response = $this->post('/login', [
                'email'    => $payload,
                'password' => "' OR '1'='1",
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertGuest('web');
        }
    }

    /**
     * 3. PENGUJIAN ANTI-SQL INJECTION (Parameterized Queries / ORM):
     * Karakter kutip dan query SQL berbahaya pada parameter pencarian diperlakukan sebagai literal text murni.
     */
    public function test_sql_injection_in_search_query_is_safely_escaped_via_prepared_statements(): void
    {
        $direktur = User::factory()->create([
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        Product::create([
            'sku'           => 'GPA-VGT-001',
            'name'          => 'Selada Romaine Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        // Input payload destructive SQL Injection
        $maliciousSearch = "'; DROP TABLE products; --";

        $response = $this->actingAs($direktur)->get(route('direktur.products.index', ['search' => $maliciousSearch]));

        $response->assertStatus(200);
        // Tabel products tetap aman di database
        $this->assertDatabaseHas('products', ['sku' => 'GPA-VGT-001']);
    }

    /**
     * 4. PENGUJIAN XSS (Cross-Site Scripting HTML Entity Escaping):
     * Input skrip berbahaya otomatis di-encode menjadi entitas HTML aman oleh Blade echo `{{ }}` / `htmlspecialchars`.
     */
    public function test_xss_payload_in_user_input_is_escaped_in_blade_rendering(): void
    {
        $klien = User::factory()->create([
            'name'        => '<script>alert("XSS-ATTACK")</script>',
            'role'        => 'KLIEN',
            'client_type' => 'REGULER',
            'is_active'   => true,
        ]);

        // Akses halaman profile klien
        $response = $this->actingAs($klien)->get(route('profile.edit'));

        $response->assertStatus(200);

        // Raw executable tag <script> tidak boleh pernah muncul mentah
        $response->assertDontSee('<script>alert("XSS-ATTACK")</script>', false);

        // Wajib ter-escape menjadi entitas HTML aman: &lt;script&gt;
        $response->assertSee('&lt;script&gt;alert(&quot;XSS-ATTACK&quot;)&lt;/script&gt;', false);
    }

    /**
     * 5. PENGUJIAN SANITASI FILE UPLOAD (MIME Type & Malicious Extension Rejection):
     * Berkas berbahaya (script PHP, executable, backdoor) langsung ditolak oleh rule validasi `mimes`.
     */
    public function test_malicious_file_upload_is_rejected_by_mime_and_extension_validation(): void
    {
        Storage::fake('public');

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        Invoice::create([
            'invoice_number'  => 'INV-SEC-001',
            'user_id'         => $klien->id,
            'period_start'    => now()->toDateString(),
            'period_end'      => now()->addDays(30)->toDateString(),
            'due_date'        => now()->addDays(30)->toDateString(),
            'subtotal_amount' => 1000000,
            'grand_total'     => 1000000,
            'status'          => 'UNPAID',
        ]);

        // Coba upload file script PHP berbahaya yang menyamar sebagai bukti bayar
        $maliciousFile = UploadedFile::fake()->create('backdoor.php', 100, 'application/x-php');

        $response = $this->actingAs($klien)->post('/klien/payment-proof', [
            'invoice_po'     => 'INV-SEC-001',
            'amount'         => 1000000,
            'sender_bank'    => 'BCA',
            'sender_account' => '1234567890',
            'proof_file'     => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('proof_file');
        Storage::disk('public')->assertMissing('payments/backdoor.php');
    }

    /**
     * 6. PENGUJIAN PENYIMPANAN BERKAS AMAN (Sanitized & Hashed Filenames):
     * File sah disimpan dengan nama berkas acak yang aman, meniadakan risiko Directory Traversal.
     */
    public function test_valid_file_upload_is_stored_with_sanitized_and_safe_filename(): void
    {
        Storage::fake('public');

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        Invoice::create([
            'invoice_number'  => 'INV-SEC-002',
            'user_id'         => $klien->id,
            'period_start'    => now()->toDateString(),
            'period_end'      => now()->addDays(30)->toDateString(),
            'due_date'        => now()->addDays(30)->toDateString(),
            'subtotal_amount' => 1500000,
            'grand_total'     => 1500000,
            'status'          => 'UNPAID',
        ]);

        $validImage = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 400);

        $response = $this->actingAs($klien)->post('/klien/payment-proof', [
            'invoice_po'     => 'INV-SEC-002',
            'amount'         => 1500000,
            'sender_bank'    => 'Bank Mandiri',
            'sender_account' => '987654321',
            'proof_file'     => $validImage,
        ]);

        $response->assertSessionHasNoErrors();

        // Nama file asli user tidak digunakan langsung sebagai path mentah
        Storage::disk('public')->assertMissing('payments/bukti_transfer.jpg');
    }
}

<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Support\SecretaryPaymentData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_payment_verification_console_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahmawati']);

        $response = $this->actingAs($user)->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('SR');
    }

    public function test_console_shows_heading_and_rule_11_policy_banner(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Sub-05 // Modul Verifikasi Pembayaran & Rekonsiliasi Kas');
        $response->assertSee('Konsol Verifikasi Pembayaran Manual &');
        $response->assertSee('Rekonsiliasi Kas');
        $response->assertSee('Pemeriksaan dan validasi bukti transfer bank manual serta QRIS statis oleh Admin/Sekre Keuangan sesuai');
        $response->assertSee('PRD App-GPA.md Section 15 & Rule 11 (Non-Gateway MVP Phase).');
        $response->assertSee('Live Ingestion: Bank');
        $response->assertSee('BCA / Mandiri Giro');

        $response->assertSee('Kebijakan Kontrol Finansial PRD Rule 11 & Section 15.2:');
        $response->assertSee('Mandatory Admin');
        $response->assertSee('BUKAN berarti otomatis lunas');
        $response->assertSee('menyetujui (Approve) bukti fisik rekening koran harian.');
    }

    public function test_console_renders_reconciliation_metric_cards(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Menunggu Verifikasi Admin');
        $response->assertSee('5');
        $response->assertSee('Bukti Bayar');
        $response->assertSee('Rp 38.450.000');
        $response->assertSee('Urgent (Antrean: 47 Menit)');
        $response->assertSee('Prioritas Tinggi');

        $response->assertSee('Terverifikasi Hari Ini');
        $response->assertSee('14');
        $response->assertSee('Transaksi');
        $response->assertSee('Rp 76.200.000');
        $response->assertSee('Otorisor: OP-4091');
        $response->assertSee('Buku Kas Sah');

        $response->assertSee('Ditolak / Selisih Bukti');
        $response->assertSee('Butuh konfirmasi ulang klien (Nominal beda)');
        $response->assertSee('ID PO: PO-202410-0089');
        $response->assertSee('Tertahan');

        $response->assertSee('Total Settlement Pekan Ini');
        $response->assertSee('Rp 260.631.000');
        $response->assertSee('Bank Transfer: 78% | QRIS: 14% | COD: 8%');
        $response->assertSee('100% Balanced');
        $response->assertSee('Recon-Pass');
    }

    public function test_console_renders_payment_queue_header_and_channel_filters(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Daftar Antrean Pembayaran Masuk');
        $response->assertSee('5 Transaksi Pending Review');
        $response->assertSee('Auto-Sync');
        $response->assertSee('(30s)');

        $response->assertSee('Semua Antrean');
        $response->assertSee('Transfer Bank BCA/Mandiri');
        $response->assertSee('QRIS Statis GPA');
        $response->assertSee('Giro / TOP B2B');

        $response->assertSee('Memori Antrean: 5 dari 5 Ditampilkan');
        $response->assertSee('FIFO Queue Priority: Strict');
    }

    public function test_queue_lists_five_pending_payments_with_evidence(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('PAY-0921');
        $response->assertSee('INV-202410-0091');
        $response->assertSee('PT Kuliner Makmur Sentosa');
        $response->assertSee('BCA Transfer Manual');
        $response->assertSee('Jam 10:20 WIB');
        $response->assertSee('Rp 18.550.000');
        $response->assertSee('Slip Transfer Unggahan');
        $response->assertSee('Inspeksi Aktif');

        $response->assertSee('PAY-0920');
        $response->assertSee('INV-202410-0090');
        $response->assertSee('UD Berkah Pangan Abadi');
        $response->assertSee('Mandiri Giro B2B');
        $response->assertSee('Rp 9.400.000');
        $response->assertSee('Warkat Kliring');

        $response->assertSee('PAY-0919');
        $response->assertSee('INV-202410-0088');
        $response->assertSee('Koperasi Tani Mitra Mandiri');
        $response->assertSee('QRIS Statis GPA');
        $response->assertSee('Rp 2.850.000');

        $response->assertSee('PAY-0918');
        $response->assertSee('INV-202410-0087');
        $response->assertSee('Dapur Katering Sejahtera');
        $response->assertSee('Rp 4.650.000');

        $response->assertSee('PAY-0917');
        $response->assertSee('INV-202410-0086');
        $response->assertSee('CV Sayur Segar Lestari');
        $response->assertSee('Rp 3.000.000');
        $response->assertSee('Pending');
    }

    public function test_console_renders_inspection_panel_for_selected_payment(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Inspeksi Bukti &amp; Aksi Otorisasi', false);
        $response->assertSee('Ref Kasir: PAY-0921');
        $response->assertSee('// Tiket Rekonsiliasi OP-4091');
        $response->assertSee('Siap Diaudit');

        $response->assertSee('Nama Klien / PT');
        $response->assertSee('PT Kuliner Makmur');
        $response->assertSee('Nomor Invoice');
        $response->assertSee('INV-202410-0091');
        $response->assertSee('Nilai Tagihan');
        $response->assertSee('Jatuh Tempo');
        $response->assertSee('28 Okt (TOP 7)');

        $response->assertSee('Slip Transfer Unggahan');
        $response->assertSee('JPG (640KB)');
        $response->assertSee('SLIP_TRANSFER_BCA_18550000.JPG');
        $response->assertSee('PT KULINER MAKMUR S.');
        $response->assertSee('BCA 5420-991-002');
        $response->assertSee('RP 18.550.000,-');
        $response->assertSee('24/10/2024 10:15:22 WIB');
        $response->assertSee('Status Metadata: Lengkap');
        $response->assertSee('Perbesar Dokumen');

        $response->assertSee('Mutasi Giro Bank GPA (Live)');
        $response->assertSee('Match 100%');
        $response->assertSee('BCA 8820-192-001');
        $response->assertSee('24/10/2024 10:15 WIB');
        $response->assertSee('[CR] Dana Masuk');
        $response->assertSee('+Rp 18.550.000');
        $response->assertSee('Nominal slip &amp; rekening koran identik.', false);
    }

    public function test_console_renders_rule_11_checklist_and_authorization_actions(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Checklist Protokol Audit (PRD Rule 11.2):');
        $response->assertSee('3/3 Terpenuhi');
        $response->assertSee('Nominal slip sama persis dengan mutasi bank (');
        $response->assertSee('Rekening tujuan sah sesuai Giro GPA Perusahaan (');
        $response->assertSee('Tanggal &amp; jam mutasi valid di perbankan (', false);

        $response->assertSee('Catatan Verifikasi Sekre / Rekap Audit:');
        $response->assertSee('Stamp Audit Log');
        $response->assertSee('Mutasi BCA jam 10:15 WIB valid dan klop. Disetujui untuk cetak kuitansi sah.');
        $response->assertSee('Simpan Catatan Audit');

        $response->assertSee('Approve &amp; Settled Lunas', false);
        $response->assertSee('Tolak Bukti Bayar');
    }

    public function test_console_renders_payment_audit_trail(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('Riwayat Log Verifikasi Pembayaran Terakhir (Audit Trail)');
        $response->assertSee('Ledger Buku Kas Realtime • Dilindungi Hash Kriptografi Immutable');
        $response->assertSee('SHA-256: 7f8a9e4d01b92a3c8e54c03b (Immutable)');

        $response->assertSee('Timestamp');
        $response->assertSee('ID Bayar');
        $response->assertSee('No. Faktur');
        $response->assertSee('Klien');
        $response->assertSee('Nominal');
        $response->assertSee('Aksi &amp; Hasil', false);
        $response->assertSee('Petugas Sekre');
        $response->assertSee('Catatan Audit');

        $response->assertSee('24/10 10:04:12');
        $response->assertSee('PAY-0916');
        $response->assertSee('PT Agro Boga Utama');
        $response->assertSee('Rp 22.400.000');
        $response->assertSee('Mutasi Mandiri Giro matched. Kuitansi K-089 terbit.');

        $response->assertSee('PAY-0915');
        $response->assertSee('CV Prima Buah Sejahtera');
        $response->assertSee('Rp 14.150.000');

        $response->assertSee('PAY-0914');
        $response->assertSee('Resto Nusantara Megah');
        $response->assertSee('Rp 8.200.000');
        $response->assertSee('Rejected');
        $response->assertSee('Nominal transfer kurang Rp 500rb. Eskalasi ke sales invoice.');

        $response->assertSee('PAY-0913');
        $response->assertSee('Katering Melati Harmoni');
        $response->assertSee('Rp 5.750.000');

        $response->assertSee('Menampilkan 4 dari 42 Transaksi Rekonsiliasi Hari Ini');
        $response->assertSee('Sebelumnya');
        $response->assertSee('Selanjutnya');
    }

    public function test_payment_data_reconciles_queue_totals_kpis_and_audit_ledger(): void
    {
        $queue = SecretaryPaymentData::queue();
        $rows = $queue['rows'];
        $metrics = collect(SecretaryPaymentData::metrics())->keyBy('key');
        $audit = SecretaryPaymentData::audit();

        $this->assertCount(5, $rows);
        $this->assertSame(38450000, array_sum(array_column($rows, 'amount')));
        $this->assertSame($queue['total'], array_sum(array_column($rows, 'amount')));
        $this->assertSame($queue['total_label'], $metrics['waiting']['detail']);
        $this->assertSame((string) count($rows), $metrics['waiting']['value']);

        $channels = array_count_values(array_column($rows, 'channel'));
        $this->assertSame(2, $channels['bank']);
        $this->assertSame(1, $channels['qris']);
        $this->assertSame(2, $channels['giro']);

        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'id'))));
        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'invoice'))));

        $selected = $rows[0];
        $this->assertSame('inspect', $selected['status']);
        $this->assertTrue($selected['match']);

        foreach (SecretaryPaymentData::inspection()['checklist'] as $item) {
            $this->assertArrayHasKey($item['key'], $selected);
        }

        $auditRows = $audit['rows'];
        $this->assertCount(4, $auditRows);
        $this->assertSame(50500000, array_sum(array_column($auditRows, 'amount')));
        $this->assertSame($metrics['rejected']['value'], (string) count(array_filter(
            $auditRows,
            fn ($row) => $row['status'] === 'rejected',
        )));
        $this->assertGreaterThan(count($auditRows), $audit['total_transactions']);
    }

    public function test_console_mounts_alpine_component_with_rule_11_authorization_flow(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('secretaryPayments(', false);
        $response->assertSee('setChannel(', false);
        $response->assertSee('filterCount(', false);
        $response->assertSee('visibleRows()', false);
        $response->assertSee('inspect(', false);
        $response->assertSee('rowClass(', false);
        $response->assertSee('isSettled(', false);
        $response->assertSee('isRejected(', false);
        $response->assertSee('toggleCheck(', false);
        $response->assertSee('isChecked(', false);
        $response->assertSee('checkComplete', false);
        $response->assertSee('progressLabel', false);
        $response->assertSee('openProof(', false);
        $response->assertSee('approve()', false);
        $response->assertSee('reject()', false);
        $response->assertSee('saveNote()', false);
        $response->assertSee('sync()', false);
        $response->assertSee('goToPage(', false);
        $response->assertSee('x-text', false);
    }

    public function test_navigation_marks_payment_verification_module_as_active(): void
    {
        $response = $this->get(route('secretary.payments'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.payments').'"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Verifikasi Pesanan');
        $response->assertSee('Faktur &amp; Tagihan', false);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GovernanceService
{
    protected string $settingsPath;
    protected string $auditLogPath;

    public function __construct()
    {
        $this->settingsPath = app()->environment('testing')
            ? storage_path('app/governance_settings_testing.json')
            : storage_path('app/governance_settings.json');
        $this->auditLogPath = app()->environment('testing')
            ? storage_path('app/audit_trail_testing.json')
            : storage_path('app/audit_trail.json');
        $this->ensureFilesExist();
    }

    protected function ensureFilesExist(): void
    {
        if (!File::exists(dirname($this->settingsPath))) {
            File::makeDirectory(dirname($this->settingsPath), 0755, true);
        }

        if (!File::exists($this->settingsPath)) {
            $defaultSettings = [
                'warehouse_loss_max_percent' => 2.0,
                'warehouse_loss_warning_percent' => 1.5,
                'default_top_credit_ceiling' => 100000000,
                'min_credit_score' => 75,
                'default_top_days' => 30,
                'min_margin_percent' => 15.0,
                'master_freeze_active' => false,
                'master_freeze_reason' => null,
                'master_freeze_engaged_at' => null,
                'frozen_clients' => [],
                'plt_active' => false,
                'plt_delegate_name' => null,
                'plt_ceiling_amount' => 100000000,
                'plt_duration_hours' => 72,
                'plt_activated_at' => null,
                'tax_ppn_enabled' => false,
                'tax_ppn_percent' => 11.0,
                'tax_ppn_note' => 'Fasilitas Bebas PPN Produk Pertanian DTP (PPN 0%)',
                'default_shipping_fee' => 75000.0,
                'free_shipping_min_order' => 1000000.0,
                'period_lock' => [
                    'is_locked' => false,
                    'period' => 'Kuartal ' . ceil(date('n') / 3) . ' (' . date('Y') . ')',
                    'locked_at' => null,
                    'locked_by' => null,
                    'sha256_hash' => null,
                ],
                'payment_channels' => $this->defaultPaymentChannels(),
            ];
            File::put($this->settingsPath, json_encode($defaultSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        if (!File::exists($this->auditLogPath)) {
            File::put($this->auditLogPath, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    public function getSettings(): array
    {
        $this->ensureFilesExist();
        $stored = json_decode(File::get($this->settingsPath), true) ?? [];
        $defaults = [
            'warehouse_loss_max_percent'     => 2.0,
            'warehouse_loss_warning_percent' => 1.5,
            'default_top_credit_ceiling'     => 100000000,
            'min_credit_score'               => 75,
            'default_top_days'               => 30,
            'min_margin_percent'             => 15.0,
            'master_freeze_active'           => false,
            'master_freeze_reason'           => null,
            'master_freeze_engaged_at'       => null,
            'frozen_clients'                 => [],
            'plt_active'                     => false,
            'plt_delegate_name'              => null,
            'plt_ceiling_amount'             => 100000000,
            'plt_duration_hours'             => 72,
            'plt_activated_at'               => null,
            'tax_ppn_enabled'                => false,
            'tax_ppn_percent'                => 11.0,
            'tax_ppn_note'                   => 'Fasilitas Bebas PPN Produk Pertanian DTP (PPN 0%)',
            'default_shipping_fee'           => 75000.0,
            'free_shipping_min_order'        => 1000000.0,
        ];

        return array_merge($defaults, $stored);
    }

    public function getTaxSettings(): array
    {
        $settings = $this->getSettings();
        return [
            'enabled' => (bool) ($settings['tax_ppn_enabled'] ?? false),
            'percent' => (float) ($settings['tax_ppn_percent'] ?? 11.0),
            'note'    => (string) ($settings['tax_ppn_note'] ?? 'Fasilitas Bebas PPN Produk Pertanian DTP (PPN 0%)'),
        ];
    }

    public function getShippingSettings(): array
    {
        $settings = $this->getSettings();
        return [
            'default_fee'    => (float) ($settings['default_shipping_fee'] ?? 75000.0),
            'free_min_order' => (float) ($settings['free_shipping_min_order'] ?? 1000000.0),
        ];
    }

    public function updateSettings(array $newSettings): bool
    {
        $current = $this->getSettings();
        $updated = array_merge($current, $newSettings);
        File::put($this->settingsPath, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return true;
    }

    public function getAuditLogs(): array
    {
        $this->ensureFilesExist();
        return json_decode(File::get($this->auditLogPath), true) ?? [];
    }

    // Mencatat riwayat aktivitas (audit log) sistem ke file penyimpanan
    public function addAuditLog(string $category, string $docRef, string $details, string $actorName, string $actorUid, string $status = 'SEALED'): array
    {
        $logs = $this->getAuditLogs();
        $nextNumber = str_pad(count($logs) + 1, 4, '0', STR_PAD_LEFT);
        $blockId = '#LOG-' . $nextNumber;
        $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB';

        // Hash unik untuk memverifikasi keaslian catatan aktivitas
        $hash = hash('sha256', $timestamp . $docRef . $details);

        $newEntry = [
            'block_id'        => $blockId,
            'timestamp'       => $timestamp,
            'actor'           => $actorName,
            'actor_uid'       => $actorUid,
            'action_category' => $category,
            'document_ref'    => $docRef,
            'details'         => $details,
            'status'          => $status,
            'hash'            => $hash,
        ];

        array_unshift($logs, $newEntry);
        File::put($this->auditLogPath, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $newEntry;
    }

    public function lockPeriod(string $periodName, string $lockedBy): array
    {
        $settings = $this->getSettings();
        $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB';
        $hash = '0x' . strtoupper(hash('sha256', $periodName . $timestamp . $lockedBy . 'GPA_IMMUTABLE_AUDIT_SEAL'));

        $settings['period_lock'] = [
            'is_locked' => true,
            'period' => $periodName,
            'locked_at' => $timestamp,
            'locked_by' => $lockedBy,
            'sha256_hash' => $hash,
        ];

        $this->updateSettings($settings);

        $this->addAuditLog(
            'STATUS_LOCK_ENGAGED',
            'PERIOD-' . preg_replace('/[^A-Za-z0-9]/', '', $periodName),
            "Periode {$periodName} telah dikunci permanen oleh {$lockedBy}. SHA-256 dibuat. Write-access dicabut.",
            $lockedBy,
            'DIR-01 [ROOT-ACCESS]',
            'LOCKED'
        );

        return $settings['period_lock'];
    }

    public function unlockPeriod(string $periodName, string $unlockedBy): bool
    {
        $settings = $this->getSettings();
        $settings['period_lock']['is_locked'] = false;
        $this->updateSettings($settings);

        $this->addAuditLog(
            'STATUS_LOCK_DISENGAGED',
            'PERIOD-' . preg_replace('/[^A-Za-z0-9]/', '', $periodName),
            "Kunci audit periode {$periodName} dibuka kembali oleh {$unlockedBy}.",
            $unlockedBy,
            'DIR-01 [ROOT-ACCESS]',
            'UNSEALED'
        );

        return true;
    }

    public function toggleMasterFreeze(bool $freeze, ?string $reason, string $actorName): bool
    {
        $settings = $this->getSettings();
        $settings['master_freeze_active'] = $freeze;
        $settings['master_freeze_reason'] = $freeze ? ($reason ?? 'Stock Opname Tahunan & Audit Forensik') : null;
        $settings['master_freeze_engaged_at'] = $freeze ? now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB' : null;
        $this->updateSettings($settings);

        $this->addAuditLog(
            $freeze ? 'MASTER_FREEZE_ENGAGED' : 'MASTER_FREEZE_DISENGAGED',
            'CRITICAL-SAFETY-01',
            $freeze ? "Master Freeze Switch diaktifkan oleh {$actorName}. Transaksi dihentikan instan. Alasan: {$reason}" : "Master Freeze Switch dinonaktifkan oleh {$actorName}. Transaksi dipulihkan.",
            $actorName,
            'DIR-01 [ROOT-ACCESS]',
            $freeze ? 'FREEZE_ACTIVE' : 'SYSTEM_NORMAL'
        );

        return $freeze;
    }

    /**
     * Memeriksa apakah sakelar darurat Master Freeze sedang aktif
     */
    public function isMasterFreezeActive(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['master_freeze_active']);
    }

    /**
     * Mengambil alasan pengaktifan Master Freeze
     */
    public function getMasterFreezeReason(): ?string
    {
        $settings = $this->getSettings();
        return $settings['master_freeze_reason'] ?? null;
    }

    /**
     * Mengambil daftar ID klien yang dibekukan plafon kreditnya
     */
    public function getFrozenClients(): array
    {
        $settings = $this->getSettings();
        return $settings['frozen_clients'] ?? [];
    }

    /**
     * Memeriksa apakah klien tertentu dibekukan plafon kreditnya
     */
    public function isClientFrozen(int $clientId): bool
    {
        $frozen = $this->getFrozenClients();
        return !empty($frozen[$clientId]) || !empty($frozen[(string) $clientId]);
    }

    /**
     * Mengubah status pembekuan klien (toggle / set langsung) dan menyimpannya secara persisten
     */
    public function toggleClientFreeze(int $clientId, ?bool $targetState = null): bool
    {
        $settings = $this->getSettings();
        $frozen = $settings['frozen_clients'] ?? [];

        $currentlyLocked = !empty($frozen[$clientId]) || !empty($frozen[(string) $clientId]);

        if ($targetState !== null) {
            $isLocked = $targetState;
        } else {
            $isLocked = !$currentlyLocked;
        }

        if ($isLocked) {
            $frozen[(string) $clientId] = true;
            $frozen[$clientId] = true;
        } else {
            unset($frozen[$clientId], $frozen[(string) $clientId]);
        }

        $settings['frozen_clients'] = $frozen;
        $this->updateSettings($settings);

        return $isLocked;
    }

    /**
     * Membuka blokir pembekuan klien (misal saat restrukturisasi disahkan)
     */
    public function unfreezeClient(int $clientId): bool
    {
        $settings = $this->getSettings();
        $frozen = $settings['frozen_clients'] ?? [];

        unset($frozen[$clientId], $frozen[(string) $clientId]);

        $settings['frozen_clients'] = $frozen;
        $this->updateSettings($settings);

        return true;
    }

    public function configurePlt(bool $active, ?string $delegateName, ?float $ceiling, ?int $durationHours, string $actorName): bool
    {
        $settings = $this->getSettings();
        $settings['plt_active'] = $active;
        if ($active) {
            $settings['plt_delegate_name'] = $delegateName ?? 'Nurhayati, S.Ak (VP Finance & Controller)';
            $settings['plt_ceiling_amount'] = $ceiling ?? 350000000;
            $settings['plt_duration_hours'] = $durationHours ?? 72;
            $settings['plt_activated_at'] = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB';
        }
        $this->updateSettings($settings);

        $this->addAuditLog(
            $active ? 'PLT_MANDATE_ACTIVATED' : 'PLT_MANDATE_DEACTIVATED',
            'GOVERNANCE-SUCCESSION-02',
            $active ? "Mandat Plt Direksi diserahkan kepada {$settings['plt_delegate_name']} dengan limit Rp " . number_format($settings['plt_ceiling_amount'], 0, ',', '.') . " durasi {$settings['plt_duration_hours']} jam." : "Mandat Plt Direksi dicabut kembali oleh {$actorName}.",
            $actorName,
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        return true;
    }

    /**
     * Kanal pembayaran default resmi Koperasi/Green Pasundan Agriculture (GPA)
     */
    public function defaultPaymentChannels(): array
    {
        return [
            [
                'id'             => 'bca',
                'type'           => 'BANK',
                'name'           => 'Bank BCA',
                'bank_code'      => 'BCA',
                'account_number' => '8210-992-3841',
                'account_name'   => 'Green Pasundan Agriculture (Divisi Keuangan B2B)',
                'description'    => 'Rekening Virtual & Giro Korporat Utama',
                'instructions'   => 'Sertakan Nomor Faktur di berita transfer untuk rekonsiliasi otomatis perbankan.',
                'image_url'      => null,
                'logo_url'       => '/images/payments/bca.svg',
                'is_active'      => true,
            ],
            [
                'id'             => 'mandiri',
                'type'           => 'BANK',
                'name'           => 'Bank Mandiri',
                'bank_code'      => 'MANDIRI',
                'account_number' => '137-00-1928374-1',
                'account_name'   => 'Green Pasundan Agriculture',
                'description'    => 'Giro Korporat & Mandiri Cash Management (MCM)',
                'instructions'   => 'Transfer via Mandiri Livin, Kopra by Mandiri, atau Kliring SKN/BI-FAST.',
                'image_url'      => null,
                'logo_url'       => '/images/payments/mandiri.svg',
                'is_active'      => true,
            ],
            [
                'id'             => 'bri',
                'type'           => 'BANK',
                'name'           => 'Bank BRI',
                'bank_code'      => 'BRI',
                'account_number' => '0341-01-001928-30-2',
                'account_name'   => 'Green Pasundan Agriculture',
                'description'    => 'Rekening Operasional Agribisnis & BritAma Bisnis',
                'instructions'   => 'Transfer via BRImo, Cash Management BRI, atau Teller BRI seluruh Indonesia.',
                'image_url'      => null,
                'logo_url'       => '/images/payments/bri.svg',
                'is_active'      => true,
            ],
            [
                'id'             => 'bni',
                'type'           => 'BANK',
                'name'           => 'Bank BNI',
                'bank_code'      => 'BNI',
                'account_number' => '0819-283-746',
                'account_name'   => 'Green Pasundan Agriculture',
                'description'    => 'Rekening Giro Bisnis & BNI Direct',
                'instructions'   => 'Transfer via BNI Direct, BNI Mobile Banking, atau ATM BNI.',
                'image_url'      => null,
                'logo_url'       => '/images/payments/bni.svg',
                'is_active'      => true,
            ],
            [
                'id'             => 'bsi',
                'type'           => 'BANK',
                'name'           => 'Bank Syariah Indonesia (BSI)',
                'bank_code'      => 'BSI',
                'account_number' => '719-283-9481',
                'account_name'   => 'Green Pasundan Agriculture',
                'description'    => 'Rekening Giro Wadiah Syariah Bebas Riba',
                'instructions'   => 'Transfer via BSI Mobile, BSI Net Banking, atau Kliring BI-FAST.',
                'image_url'      => null,
                'logo_url'       => '/images/payments/bsi.svg',
                'is_active'      => true,
            ],
            [
                'id'             => 'qris',
                'type'           => 'QRIS',
                'name'           => 'QRIS GPA (Semua E-Wallet & Mobile Banking)',
                'bank_code'      => 'QRIS',
                'account_number' => 'ID1020039182948',
                'account_name'   => 'GREEN PASUNDAN AGRICULTURE',
                'description'    => 'Pindai QRIS via BCA Mobile, Livin, BRImo, GoPay, OVO, ShopeePay, DANA',
                'instructions'   => 'Scan barcode QRIS resmi GPA di bawah dan simpan bukti tangkapan layar transaksi.',
                'image_url'      => '/storage/payment-channels/qris-gpa-default.png',
                'logo_url'       => '/images/payments/qris.svg',
                'is_active'      => true,
            ],
            [
                'id'             => 'ewallet',
                'type'           => 'EWALLET',
                'name'           => 'Dompet Digital Bisnis (GoPay & DANA GPA)',
                'bank_code'      => 'EWALLET',
                'account_number' => '0812-8888-9281',
                'account_name'   => 'GREEN PASUNDAN AGRICULTURE',
                'description'    => 'Merchant Dompet Digital Resmi Terverifikasi',
                'instructions'   => 'Transfer saldo dompet digital ke nomor merchant resmi Green Pasundan Agriculture.',
                'image_url'      => null,
                'logo_url'       => '/images/payments/ewallet.svg',
                'is_active'      => true,
            ],
        ];
    }

    /**
     * Mengambil daftar seluruh kanal pembayaran (aktif & non-aktif)
     */
    public function getPaymentChannels(): array
    {
        $settings = $this->getSettings();
        $defaults = $this->defaultPaymentChannels();

        if (empty($settings['payment_channels']) || !is_array($settings['payment_channels'])) {
            $settings['payment_channels'] = $defaults;
            $this->updateSettings($settings);
            return $defaults;
        }

        $existing = $settings['payment_channels'];

        // Pastikan seluruh 7 metode pembayaran resmi selalu langsung ada sejak awal
        if (!app()->environment('testing')) {
            $existingIds = array_map('strtolower', array_column($existing, 'id'));
            $hasMissing = false;
            foreach ($defaults as $def) {
                if (!in_array(strtolower($def['id']), $existingIds)) {
                    $existing[] = $def;
                    $hasMissing = true;
                }
            }
            if ($hasMissing) {
                $settings['payment_channels'] = $existing;
                $this->updateSettings($settings);
            }
        }

        $lookup = [
            'bca'     => '/images/payments/bca.svg',
            'mandiri' => '/images/payments/mandiri.svg',
            'bri'     => '/images/payments/bri.svg',
            'bni'     => '/images/payments/bni.svg',
            'bsi'     => '/images/payments/bsi.svg',
            'qris'    => '/images/payments/qris.svg',
            'ewallet' => '/images/payments/ewallet.svg',
        ];

        // Pastikan setiap kanal memiliki key 'image_url' dan 'logo_url' resmi
        $channels = array_map(function ($ch) use ($lookup) {
            if ($ch['type'] === 'QRIS' && empty($ch['image_url'])) {
                $ch['image_url'] = '/storage/payment-channels/qris-gpa-default.png';
            } elseif (!array_key_exists('image_url', $ch)) {
                $ch['image_url'] = null;
            }

            $idKey = strtolower($ch['id'] ?? '');
            $codeKey = strtolower($ch['bank_code'] ?? '');
            if (empty($ch['logo_url'])) {
                $ch['logo_url'] = $lookup[$idKey] ?? ($lookup[$codeKey] ?? null);
            }

            return $ch;
        }, $existing);

        return $channels;
    }

    /**
     * Mengambil hanya kanal pembayaran yang diaktifkan oleh Direktur
     */
    public function getActivePaymentChannels(): array
    {
        $channels = $this->getPaymentChannels();
        return array_values(array_filter($channels, fn($c) => !empty($c['is_active'])));
    }

    /**
     * Memperbarui konfigurasi kanal pembayaran oleh Direktur Utama
     */
    public function updatePaymentChannels(array $channels): bool
    {
        $settings = $this->getSettings();
        $settings['payment_channels'] = $channels;
        return $this->updateSettings($settings);
    }
}

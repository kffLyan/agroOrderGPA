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
        $this->settingsPath = storage_path('app/governance_settings.json');
        $this->auditLogPath = storage_path('app/audit_trail.json');
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
                'plt_active' => false,
                'plt_delegate_name' => 'Nurhayati, S.Ak (VP Finance & Controller)',
                'plt_ceiling_amount' => 350000000,
                'plt_duration_hours' => 72,
                'plt_activated_at' => null,
                'period_lock' => [
                    'is_locked' => false,
                    'period' => 'Kuartal IV (Okt - Des 2026)',
                    'locked_at' => null,
                    'locked_by' => null,
                    'sha256_hash' => null,
                ],
            ];
            File::put($this->settingsPath, json_encode($defaultSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        if (!File::exists($this->auditLogPath)) {
            $initialLogs = [
                [
                    'block_id' => '#4412',
                    'timestamp' => '2026-10-24 15:18:02 WIB',
                    'actor' => 'Ahmad Sanusi, S.P.',
                    'actor_uid' => 'DIR-01 [CLEARANCE L4]',
                    'action_category' => 'APPROVAL KONTRAK',
                    'document_ref' => 'CTR-B2B-2025-089',
                    'details' => 'Persetujuan Khusus Kontrak B2B Hotel Grand Pangrango (Margin 18.5%, Plafon TOP Rp 200M, Tenor 30 Hari).',
                    'status' => 'SEALED',
                    'hash' => hash('sha256', 'CTR-B2B-2025-089-SEALED-4412'),
                ],
                [
                    'block_id' => '#4411',
                    'timestamp' => '2026-10-24 14:02:44 WIB',
                    'actor' => 'Nurhayati, S.Ak',
                    'actor_uid' => 'FIN-LEAD-03 [L3]',
                    'action_category' => 'REKONSILIASI KAS',
                    'document_ref' => 'INV-GPA-202610-0088',
                    'details' => 'Kliring Pembayaran Invoice Termin 2 Rp 114.200.000 via VA Mandiri Korporat, Selisih Kliring Rp 0.',
                    'status' => 'SEALED',
                    'hash' => hash('sha256', 'INV-GPA-202610-0088-SEALED-4411'),
                ],
                [
                    'block_id' => '#4410',
                    'timestamp' => '2026-10-24 11:15:00 WIB',
                    'actor' => 'Ahmad Sanusi, S.P.',
                    'actor_uid' => 'DIR-01 [CLEARANCE L4]',
                    'action_category' => 'SYSTEM GOVERNANCE',
                    'document_ref' => 'Q3-2026-LEDGER',
                    'details' => 'Eksekusi Audit Freeze Q3 2026: Penutupan Mutasi Jurnal Buku Besar Agribisnis Kuartal 3. Status Finalized.',
                    'status' => 'SEALED',
                    'hash' => hash('sha256', 'Q3-2026-LEDGER-SEALED-4410'),
                ],
                [
                    'block_id' => '#4409',
                    'timestamp' => '2026-10-24 09:40:12 WIB',
                    'actor' => 'Agung Wicaksono',
                    'actor_uid' => 'WMS-SUPER-01 [L2]',
                    'action_category' => 'BUFFER OVERRIDE',
                    'document_ref' => 'STK-TMT-LEMBANG-04',
                    'details' => 'Penyesuaian Kalibrasi Buffer Tomat Beef Pasca Sortir Gudang Lembang (+240 KG dari panen kemitraan Gapoktan).',
                    'status' => 'SEALED',
                    'hash' => hash('sha256', 'STK-TMT-LEMBANG-04-SEALED-4409'),
                ],
                [
                    'block_id' => '#4408',
                    'timestamp' => '2026-10-24 08:12:55 WIB',
                    'actor' => 'System Auto-Guard',
                    'actor_uid' => 'DAEMON CORE RULE 02',
                    'action_category' => 'SECURITY BLOCK',
                    'document_ref' => 'ORD-GPA-202610-0112',
                    'details' => 'Pencegahan Otomatis Overselling: Permintaan PO Melebihi Buffer Fisik Kentang Granola Gudang Ciwidey (Defisit 1.8 MT).',
                    'status' => 'INTERCEPTED',
                    'hash' => hash('sha256', 'ORD-GPA-202610-0112-INTERCEPTED-4408'),
                ],
            ];
            File::put($this->auditLogPath, json_encode($initialLogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    public function getSettings(): array
    {
        $this->ensureFilesExist();
        return json_decode(File::get($this->settingsPath), true) ?? [];
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

    public function addAuditLog(string $category, string $docRef, string $details, string $actorName, string $actorUid, string $status = 'SEALED'): array
    {
        $logs = $this->getAuditLogs();
        $nextNumber = 4413 + count($logs) - 5;
        $blockId = '#block-' . $nextNumber;
        $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') . ' WIB';
        
        $prevHash = !empty($logs) ? ($logs[0]['hash'] ?? '') : '';
        $rawContent = $prevHash . $blockId . $timestamp . $docRef . $details;
        $hash = hash('sha256', $rawContent);

        $newEntry = [
            'block_id' => $blockId,
            'timestamp' => $timestamp,
            'actor' => $actorName,
            'actor_uid' => $actorUid,
            'action_category' => $category,
            'document_ref' => $docRef,
            'details' => $details,
            'status' => $status,
            'hash' => $hash,
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
}

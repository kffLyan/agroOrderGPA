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
                'plt_delegate_name' => null,
                'plt_ceiling_amount' => 100000000,
                'plt_duration_hours' => 72,
                'plt_activated_at' => null,
                'period_lock' => [
                    'is_locked' => false,
                    'period' => 'Kuartal ' . ceil(date('n') / 3) . ' (' . date('Y') . ')',
                    'locked_at' => null,
                    'locked_by' => null,
                    'sha256_hash' => null,
                ],
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

const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(',', '.'));
    return Number.isFinite(parsed) ? parsed : 0;
};

const kgLabel = (value) => {
    const rounded = Math.round(numberOf(value) * 10) / 10;

    return Number.isInteger(rounded) ? String(rounded) : rounded.toFixed(1);
};

const statusToneClasses = {
    accent: 'bg-accent text-ink outline-success-deep',
    success: 'bg-success-soft text-success-deep outline-success/40',
    danger: 'bg-danger-soft text-danger-ink outline-danger/30',
};

export default function coordinatorMonitoring(rows = [], dossier = {}, meta = {}) {
    return {
        dossier: dossier && typeof dossier === 'object' ? dossier : {},
        meta: meta && typeof meta === 'object' ? meta : {},
        selected: '',
        decision: 'approved',
        checks: {},
        notes: '',
        locked: [],
        rejected: [],
        rows: (Array.isArray(rows) ? rows : []).map((row) => ({
            ...row,
            cargo_kg: numberOf(row.cargo_kg),
            manifest_kg: row.manifest_kg === null || row.manifest_kg === undefined ? null : numberOf(row.manifest_kg),
            dossier_ref: row.dossier_ref ?? null,
        })),

        init() {
            this.selected = String(this.meta.defaultSj ?? this.rows[0]?.sj ?? '');
            this.loadDossier();
        },

        /* ---------- dispatch log ---------- */

        rowBySj(sj) {
            return this.rows.find((row) => row.sj === sj) ?? null;
        },

        selectedRow() {
            return this.rowBySj(this.selected) ?? this.rows[0] ?? null;
        },

        isActive(sj) {
            return sj === this.selected;
        },

        isLocked(sj) {
            return this.locked.includes(sj);
        },

        isRejected(sj) {
            return this.rejected.includes(sj);
        },

        entry() {
            return this.dossier.entries?.[this.selected] ?? null;
        },

        hasDossier() {
            return Boolean(this.entry());
        },

        statusLabel(sj) {
            const row = this.rowBySj(sj);

            if (!row) {
                return '-';
            }

            if (this.isLocked(sj)) {
                return 'PoD TERVERIFIKASI \u{2022} PESANAN DITUTUP';
            }

            if (this.isRejected(sj)) {
                return 'PoD DITOLAK \u{2022} MENUNGGU TIM AUDIT';
            }

            return row.status ?? '-';
        },

        statusTone(sj) {
            const row = this.rowBySj(sj);

            if (this.isLocked(sj)) {
                return statusToneClasses.success;
            }

            if (this.isRejected(sj)) {
                return statusToneClasses.danger;
            }

            return statusToneClasses[row?.status_tone ?? 'accent'] ?? statusToneClasses.accent;
        },

        cargoLabel(sj) {
            const row = this.rowBySj(sj);

            return row ? `${this.kg(row.cargo_kg)} kg` : '-';
        },

        cargoNote(sj) {
            const row = this.rowBySj(sj);

            if (!row) {
                return '';
            }

            if (row.manifest_kg !== null) {
                return `Manifest: ${this.kg(row.manifest_kg)} kg`;
            }

            return row.commodity ?? '';
        },

        cargoNoteTone(sj) {
            const row = this.rowBySj(sj);

            if (row?.manifest_kg !== null && row?.manifest_kg !== undefined) {
                return 'text-danger';
            }

            return 'text-ink-quiet';
        },

        manifestStrike(sj) {
            const row = this.rowBySj(sj);

            return row?.manifest_kg !== null && row?.manifest_kg !== undefined ? 'line-through' : '';
        },

        gapKg(sj) {
            const row = this.rowBySj(sj);

            if (!row || row.manifest_kg === null) {
                return 0;
            }

            return Math.max(0, Math.round((row.manifest_kg - row.cargo_kg) * 10) / 10);
        },

        select(sj) {
            const row = this.rowBySj(sj);

            if (!row) {
                this.run('Perjalanan Tidak Ditemukan', `Surat jalan ${sj} tidak ada pada log pengiriman hari ini.`, 'danger');
                return;
            }

            this.selected = sj;
            this.loadDossier();
        },

        /* ---------- dossier ---------- */

        loadDossier() {
            const entry = this.entry();
            const base = {};

            (this.dossier.checks ?? []).forEach((check) => {
                base[check.key] = Boolean(entry?.checks?.[check.key]);
            });

            this.checks = base;
            this.notes = entry?.notes ?? '';
            this.decision = 'approved';
        },

        reference() {
            return this.entry()?.ref ?? this.selectedRow()?.dossier_ref ?? 'BERKAS BELUM ADA';
        },

        associatedSj() {
            return this.entry()?.sj ?? this.selectedRow()?.sj ?? '-';
        },

        exhibits() {
            return this.entry()?.exhibits ?? [];
        },

        exhibitField(index, key) {
            return this.exhibits()[index]?.[key] ?? '';
        },

        checkKeys() {
            return (this.dossier.checks ?? []).map((check) => check.key);
        },

        checkDefinition(key) {
            return (this.dossier.checks ?? []).find((check) => check.key === key) ?? null;
        },

        checkComputed(key) {
            return key === 'weight' || key === 'gps';
        },

        weightMatch() {
            const entry = this.entry();
            const row = this.selectedRow();

            if (!entry || !row) {
                return false;
            }

            return Math.abs(numberOf(entry.net_kg) - numberOf(row.cargo_kg)) < 0.001;
        },

        gpsWithinTolerance() {
            const entry = this.entry();

            if (!entry) {
                return false;
            }

            return numberOf(entry.gps_radius_m) <= numberOf(entry.gps_tolerance_m);
        },

        checkPassed(key) {
            if (key === 'weight') {
                return this.weightMatch();
            }

            if (key === 'gps') {
                return this.gpsWithinTolerance();
            }

            return Boolean(this.checks[key]);
        },

        checkLabel(key) {
            const definition = this.checkDefinition(key);
            const entry = this.entry();

            if (!definition) {
                return '';
            }

            if (key === 'weight') {
                return `${definition.label} (${this.kg(entry?.net_kg)} kg)`;
            }

            if (key === 'gps') {
                return `${definition.label} < ${this.kg(entry?.gps_tolerance_m)}m Dari Dock`;
            }

            return definition.label;
        },

        checkTone(key) {
            if (this.checkPassed(key)) {
                return 'bg-brand text-accent';
            }

            if (this.checkComputed(key)) {
                return 'bg-surface text-danger outline outline-2 outline-danger';
            }

            return 'bg-surface text-transparent outline outline-2 outline-line-board';
        },

        toggleCheck(key) {
            if (this.checkComputed(key)) {
                this.run(
                    'Parameter Terukur Otomatis',
                    `${this.checkLabel(key)} dihitung dari sensor timbangan dan GPS dock, tidak dapat dicentang manual.`,
                    'warning',
                );
                return;
            }

            this.checks[key] = !this.checks[key];
        },

        missingChecks() {
            return this.checkKeys().filter((key) => !this.checkPassed(key));
        },

        /* ---------- KPI ---------- */

        dropoffCount() {
            return numberOf(this.meta.dropoffBase) + this.locked.length;
        },

        dropoffValue() {
            return `${this.dropoffCount()} / ${numberOf(this.meta.dropoffTotal)}`;
        },

        dropoffPercent() {
            const total = Math.max(1, numberOf(this.meta.dropoffTotal));

            return Math.round((this.dropoffCount() / total) * 100);
        },

        dropoffUnit() {
            return `Titik (${this.dropoffPercent()}%)`;
        },

        dropoffNote() {
            return `PoD VALID: ${this.dropoffCount()} BERKAS`;
        },

        /* ---------- decision ---------- */

        setDecision(key) {
            this.decision = key;
        },

        isDecision(key) {
            return this.decision === key;
        },

        canValidate() {
            const row = this.selectedRow();

            return Boolean(row) && this.hasDossier() && !this.isLocked(row.sj) && this.missingChecks().length === 0 && this.notes.trim() !== '';
        },

        validate() {
            const row = this.selectedRow();

            if (!row) {
                return;
            }

            if (!this.hasDossier()) {
                this.run(
                    'Hard Gate PoD Ditutup',
                    `${row.sj} belum memiliki berkas PoD. Foto dokumen bertanda tangan dan foto dock wajib masuk sebelum pesanan ditutup.`,
                    'danger',
                );
                return;
            }

            if (this.isLocked(row.sj)) {
                this.run('PoD Sudah Tervalidasi', `${row.sj} \u{2022} ${row.customer} \u{2022} ledger sudah terkunci atas nama penerima.`, 'warning');
                return;
            }

            const missing = this.missingChecks();

            if (missing.length > 0) {
                this.run(
                    'Validasi PoD Ditolak \u{2022} Parameter Belum Lengkap',
                    `Wajib dilengkapi lebih dulu: ${missing.map((key) => this.checkLabel(key)).join(' \u{2022} ')}`,
                    'danger',
                );
                return;
            }

            if (this.notes.trim() === '') {
                this.run('Catatan Audit Wajib', 'Catatan koordinator gudang wajib diisi sebelum keputusan dicatat pada audit trail.', 'danger');
                return;
            }

            if (this.decision === 'reject') {
                this.rejected.push(row.sj);
                this.run(
                    'PoD Ditolak \u{2022} Eskalasi Tim Audit',
                    `${row.sj} ditolak dan diteruskan ke Tim Audit. Status pesanan tetap terbuka, ledger belum boleh dikunci.`,
                    'warning',
                );
                return;
            }

            this.locked.push(row.sj);
            this.run(
                'PoD Tervalidasi & Pesanan Ditutup',
                `${row.sj} \u{2022} ${row.customer} \u{2022} ${this.kg(row.cargo_kg)} KG \u{2022} ${this.reference()} disahkan. \u{2022} ${this.dropoffValue()} titik dropoff selesai.`,
                'success',
            );
        },

        /* ---------- actions ---------- */

        rowAction(key, sj) {
            const row = this.rowBySj(sj);

            if (!row) {
                this.run('Aksi Tidak Tersedia', `Surat jalan ${sj} tidak ditemukan pada log hari ini.`, 'danger');
                return;
            }

            if (key === 'call-driver') {
                this.run('Kanal Driver Dibuka', `${row.driver} \u{2022} ${row.vehicle} \u{2022} hotline operasional 021-5050-0888 \u{2022} status ${row.arrival}.`);
                return;
            }

            if (key === 'view-sj') {
                this.run('Pratinjau Surat Jalan', `${row.sj} \u{2022} ${row.customer} \u{2022} ${this.cargoLabel(sj)} \u{2022} ${row.destination}.`);
                return;
            }

            if (key === 'inspect-pod') {
                this.select(sj);

                if (!this.hasDossier()) {
                    this.run('Berkas PoD Belum Masuk', `${sj} tidak punya berkas PoD di antrean verifikasi hari ini.`, 'danger');
                    return;
                }

                this.run('Berkas PoD Dibuka', `${this.reference()} \u{2022} ${this.exhibits().length} eksibit foto \u{2022} pemeriksaan dokumen fisik dibuka.`);
                return;
            }

            if (key === 'download-proof') {
                this.run('Unduhan Bukti Dimulai', `${row.dossier_ref ?? row.sj} \u{2022} 2 eksibit (dokumen SJ & dock chiller) \u{2022} 300 DPI.`);
                return;
            }

            if (key === 'open-retur') {
                this.run('Laporan Retur Dibuka', `Selisih ${this.kg(this.gapKg(sj))} KG pada ${sj} wajib disahkan lewat BA Retur Seketika (PRD Rule 12).`, 'warning');
                return;
            }

            if (key === 'view-ba') {
                this.run('Detail BA Retur', `BA-RETUR-${sj} \u{2022} afkir basah ${this.kg(this.gapKg(sj))} KG \u{2022} menunggu tanda tangan penerima.`, 'warning');
            }
        },

        dossierAction(key) {
            const row = this.selectedRow();

            if (!row) {
                return;
            }

            if (key === 'validate-pod') {
                this.validate();
                return;
            }

            if (key === 'save-draft') {
                this.run(
                    'Draft Verifikasi Disimpan',
                    `${row.sj} \u{2022} ${this.missingChecks().length} parameter belum terverifikasi \u{2022} draft aman untuk dilanjutkan setelah armada tiba.`,
                );
                return;
            }

            if (key === 'escalate-audit') {
                this.run(
                    'Eskalasi ke Tim Audit',
                    `${row.sj} \u{2022} ${this.reference()} \u{2022} berkas diteruskan ke Tim Audit untuk pemeriksaan ulang.`,
                    'danger',
                );
            }
        },

        refreshTelemetry() {
            this.run(
                'Telemetry Pengiriman Diperbarui',
                `${this.rows.length} perjalanan terpantau \u{2022} 2 armada on-route \u{2022} 1 dock chiller dalam toleransi \u{2022} ${this.dropoffValue()} titik dropoff selesai.`,
            );
        },

        kg(value) {
            return kgLabel(value);
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

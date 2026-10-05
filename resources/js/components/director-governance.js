export default function directorGovernance(rows = [], labels = {}) {
    const copy = labels && typeof labels === 'object' ? labels : {};

    return {
        search: '',
        actionFilter: 'all',
        shownLabel: String(copy.shown_label ?? ''),
        rows: (Array.isArray(rows) ? rows : []).map((row) => ({
            ...row,
            actor: String(row.actor ?? ''),
            actor_meta: String(row.actor_meta ?? ''),
            action: String(row.action ?? ''),
            document: String(row.document ?? ''),
            detail: String(row.detail ?? ''),
            hash: String(row.hash ?? ''),
            haystack: [row.actor, row.actor_meta, row.action, row.document, row.detail, row.hash]
                .map((value) => String(value ?? '').toLowerCase())
                .join(' '),
        })),

        /* ---------- audit filter ---------- */

        matches(row) {
            if (!row) {
                return false;
            }

            if (this.actionFilter !== 'all' && this.actionFilter !== String(row.action)) {
                return false;
            }

            const needle = this.search.trim().toLowerCase();

            return needle === '' || row.haystack.includes(needle);
        },

        visibleRows() {
            return this.rows.filter((row) => this.matches(row));
        },

        visibleCount() {
            return this.visibleRows().length;
        },

        setAction(action) {
            this.actionFilter = String(action);
        },

        filterClass(action) {
            const active = this.actionFilter === String(action);

            return active
                ? 'bg-brand text-white outline-brand hover:bg-brand-hover'
                : 'bg-transparent text-ink-body outline-line-board hover:bg-surface-shell';
        },

        /* ---------- header actions ---------- */

        exportAudit() {
            this.run('Export Audit Log', 'Append-only ledger sedang diunduh dalam format .CSV.', 'success');
        },

        verifyIntegrity() {
            this.run(
                'Uji Integritas Kriptografi',
                'Seluruh blok SHA-256 diverifikasi: previous block hash cocok, 0 anomali terdeteksi.',
                'success',
            );
        },

        lockPeriod() {
            this.run(
                'Kunci Sistem Periode',
                'Periode Oktober 2026 dikunci. Perubahan berikutnya memerlukan PIN Master.',
                'warning',
            );
        },

        /* ---------- parameter & control actions ---------- */

        editParameters() {
            this.run('Edit Parameter Kebijakan', 'PIN Master Direktur Utama diminta sebelum parameter dapat diubah.', 'warning');
        },

        toggleFreeze() {
            this.run(
                'Master Freeze',
                'Permintaan aktivasi Master Freeze dicatat. Diperlukan dual digital signature.',
                'danger',
            );
        },

        configureSuccession() {
            this.run(
                'Delegasi Plt Direksi',
                'Konfigurasi mandat sementara dibuka untuk verifikasi Direksi.',
                'info',
            );
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

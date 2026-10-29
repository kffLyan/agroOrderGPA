export default function directorGovernance(rows = [], labels = {}) {
    const copy = labels && typeof labels === 'object' ? labels : {};

    return {
        search: '',
        actionFilter: 'all',
        page: 1,
        perPage: 10,
        shownLabel: String(copy.shown_label ?? ''),
        rows: (Array.isArray(rows) ? rows : []).map((row) => ({
            ...row,
            date: String(row.date ?? ''),
            time: String(row.time ?? ''),
            actor: String(row.actor ?? ''),
            actor_meta: String(row.actor_meta ?? ''),
            action: String(row.action ?? ''),
            action_tone: String(row.action_tone ?? 'neutral'),
            document: String(row.document ?? ''),
            detail: String(row.detail ?? ''),
            hash: String(row.hash ?? ''),
            block: String(row.block ?? ''),
            blocked: Boolean(row.blocked),
            haystack: [
                row.date,
                row.time,
                row.actor,
                row.actor_meta,
                row.action,
                row.document,
                row.detail,
                row.hash,
                row.block,
            ]
                .map((value) => String(value ?? '').toLowerCase())
                .join(' '),
        })),

        /* ---------- audit filter & search ---------- */

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

        /* ---------- pagination (10 per page) ---------- */

        totalPages() {
            return Math.max(1, Math.ceil(this.visibleCount() / this.perPage));
        },

        paginatedRows() {
            const start = (this.page - 1) * this.perPage;
            return this.visibleRows().slice(start, start + this.perPage);
        },

        startIndex() {
            if (this.visibleCount() === 0) return 0;
            return (this.page - 1) * this.perPage + 1;
        },

        endIndex() {
            return Math.min(this.page * this.perPage, this.visibleCount());
        },

        setPage(p) {
            const num = parseInt(p, 10);
            if (!isNaN(num) && num >= 1 && num <= this.totalPages()) {
                this.page = num;
            }
        },

        prevPage() {
            if (this.page > 1) {
                this.page--;
            }
        },

        nextPage() {
            if (this.page < this.totalPages()) {
                this.page++;
            }
        },

        pageNumbers() {
            const total = this.totalPages();
            const cur = this.page;

            if (total <= 7) {
                return Array.from({ length: total }, (_, i) => i + 1);
            }

            if (cur <= 4) {
                return [1, 2, 3, 4, 5, '...', total];
            }

            if (cur >= total - 3) {
                return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
            }

            return [1, '...', cur - 1, cur, cur + 1, '...', total];
        },

        setAction(action) {
            this.actionFilter = String(action);
            this.page = 1;
        },

        resetFilters() {
            this.search = '';
            this.actionFilter = 'all';
            this.page = 1;
        },

        actionBadgeClass(tone) {
            switch (tone) {
                case 'danger':
                    return 'bg-red-50 text-red-700 border-red-200';
                case 'success':
                    return 'bg-emerald-50 text-emerald-800 border-emerald-200';
                case 'dark':
                    return 'bg-brand-soft text-brand-deep border-brand/20';
                default:
                    return 'bg-surface-muted text-ink border-line';
            }
        },

        /* ---------- header actions ---------- */

        exportAudit() {
            this.run(
                'Export Audit Log',
                'Append-only ledger sedang diunduh dalam format .CSV.',
                'success'
            );
        },

        verifyIntegrity() {
            this.run(
                'Uji Integritas Kriptografi',
                'Seluruh blok SHA-256 diverifikasi: previous block hash cocok, 0 anomali terdeteksi.',
                'success'
            );
        },

        lockPeriod() {
            this.run(
                'Kunci Sistem Periode',
                'Periode Oktober 2026 dikunci. Perubahan berikutnya memerlukan PIN Master.',
                'warning'
            );
        },

        /* ---------- parameter & control actions ---------- */

        editParameters() {
            this.run(
                'Edit Parameter Kebijakan',
                'PIN Master Direktur Utama diminta sebelum parameter dapat diubah.',
                'warning'
            );
        },

        toggleFreeze() {
            this.run(
                'Master Freeze',
                'Permintaan aktivasi Master Freeze dicatat. Diperlukan dual digital signature.',
                'danger'
            );
        },

        configureSuccession() {
            this.run(
                'Delegasi Plt Direksi',
                'Konfigurasi mandat sementara dibuka untuk verifikasi Direksi.',
                'info'
            );
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

const normalize = (value) => String(value ?? '').toLowerCase();

const toNumber = (value) => {
    const parsed = parseInt(String(value ?? '').replace(/[^0-9]/g, ''), 10);

    return Number.isNaN(parsed) ? 0 : parsed;
};

const formatKg = (value) => toNumber(value).toLocaleString('id-ID');

export default function secretaryInventory(rows = [], totals = {}) {
    const list = Array.isArray(rows) ? rows : [];

    return {
        rows: list,
        atpBaseline: toNumber(totals.atp),
        commodity: 'Semua Komoditas (5 Standar)',
        source: 'Semua Sumber (Multi-Source)',
        status: 'Semua Status Operasional',
        criticalOnly: false,
        criticalThreshold: Number(totals.criticalThreshold) || 20,

        isCommodityMatch(row) {
            return this.commodity === 'Semua Komoditas (5 Standar)' || row.name === this.commodity;
        },

        isSourceMatch(row) {
            return this.source === 'Semua Sumber (Multi-Source)' || row.sources.includes(this.source);
        },

        isStatusMatch(row) {
            return this.status === 'Semua Status Operasional' || row.status.label === this.status;
        },

        atpRatio(row) {
            const total = toNumber(row.total_value);

            if (total === 0) {
                return 0;
            }

            return (toNumber(row.atp_value) / total) * 100;
        },

        isCritical(row) {
            return this.atpRatio(row) < this.criticalThreshold;
        },

        matchesSearch(row) {
            const needle = normalize(this.query).trim();

            if (! needle) {
                return true;
            }

            return [row.name, row.sku, row.grade, row.buffer_partner, ...row.keywords]
                .map(normalize)
                .some((value) => value.includes(needle));
        },

        matchesFilter(row) {
            return (
                this.isCommodityMatch(row) &&
                this.isSourceMatch(row) &&
                this.isStatusMatch(row) &&
                (! this.criticalOnly || this.isCritical(row)) &&
                this.matchesSearch(row)
            );
        },

        visibleRows() {
            return this.rows.filter((row) => this.matchesFilter(row));
        },

        visibleCount() {
            return this.visibleRows().length;
        },

        criticalCount() {
            return this.rows.filter((row) => this.isCritical(row)).length;
        },

        visibleAtpTotal() {
            const total = this.visibleRows().reduce((sum, row) => sum + toNumber(row.atp_value), 0);

            return `${formatKg(total)} kg`;
        },

        atpFooter() {
            return this.visibleCount() === this.rows.length
                ? `${formatKg(this.atpBaseline)} kg`
                : this.visibleAtpTotal();
        },

        handleAction(action) {
            this.run(action.label, action.message);
        },

        handleFilterChange() {
            this.run(
                'Filter Neraca Pasokan Diperbarui',
                `${this.visibleCount()} dari ${this.rows.length} komoditas lolos filter aktif.`
            );
        },

        toggleCritical() {
            const label = this.criticalOnly ? 'Semua Kuota' : `Kuota < ${this.criticalThreshold}%`;

            this.run(
                this.criticalOnly ? 'Filter ATP Kritis Diaktifkan' : 'Filter ATP Kritis Dinonaktifkan',
                label
            );
        },

        bulkLock() {
            const visible = this.visibleRows();

            this.run(
                'Kunci Kuota Massal Dijalankan',
                `Rule 02 mengunci ${visible.length} komoditas (${this.visibleAtpTotal()} ATP tersisa).`
            );
        },

        detail(row) {
            this.run(
                `Neraca ${row.name}`,
                `${row.sku} • Total ${row.total_value} • Terkunci ${row.locked_value} • ATP ${row.atp_value}`
            );
        },

        rowAction(row) {
            this.run(
                `${row.action.label}: ${row.name}`,
                `${row.sku} • ${row.atp_note} (${this.atpRatio(row).toFixed(1)}% dari total pasokan).`
            );
        },

        syncLog() {
            this.run('Audit Trail Lengkap', 'Riwayat mutasi stok 30 hari terakhir sedang dimuat.');
        },

        releaseDo() {
            this.run(
                'Rilis Surat Jalan Massal',
                '18 DO batch siap cetak dan Multimodal dikunci ke armada eksternal.'
            );
        },

        readinessCheck(check) {
            this.run(check.title, `${check.note} • Status: ${check.badge.label}`);
        },
    };
}
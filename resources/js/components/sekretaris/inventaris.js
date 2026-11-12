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
        query: '',
        atpBaseline: toNumber(totals.atp),
        commodity: 'Semua Komoditas (5 Standar)',
        source: 'Semua Sumber (Multi-Source)',
        status: 'Semua Status Operasional',
        criticalOnly: false,
        criticalThreshold: Number(totals.criticalThreshold) || 20,
        page: 1,
        perPage: 10,

        run(title, message, tone = 'info') {
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('gpa:toast', { title, message, tone });
            } else {
                window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
            }
        },

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

        filteredRows() {
            return this.rows.filter((row) => this.matchesFilter(row));
        },

        get lastPage() {
            return Math.max(1, Math.ceil(this.filteredRows().length / this.perPage));
        },

        pagedRows() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredRows().slice(start, start + this.perPage);
        },

        isVisible(row) {
            if (! this.matchesFilter(row)) {
                return false;
            }
            return this.pagedRows().some((item) => item.sku === row.sku);
        },

        visibleRows() {
            return this.pagedRows();
        },

        visibleCount() {
            return this.filteredRows().length;
        },

        criticalCount() {
            return this.rows.filter((row) => this.isCritical(row)).length;
        },

        visibleAtpTotal() {
            const total = this.filteredRows().reduce((sum, row) => sum + toNumber(row.atp_value), 0);

            return `${formatKg(total)} kg`;
        },

        atpFooter() {
            return this.visibleCount() === this.rows.length
                ? `${formatKg(this.atpBaseline)} kg`
                : this.visibleAtpTotal();
        },

        goToPage(page) {
            const target = Math.min(Math.max(1, page), this.lastPage);
            if (this.page !== target) {
                this.page = target;
            }
        },

        pageNumbers() {
            const pages = [];
            const total = this.lastPage;
            if (total <= 5) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                let start = Math.max(1, this.page - 2);
                let end = Math.min(total, this.page + 2);
                if (this.page <= 3) {
                    start = 1;
                    end = 5;
                } else if (this.page >= total - 2) {
                    start = total - 4;
                    end = total;
                }
                for (let i = start; i <= end; i++) pages.push(i);
            }
            return pages;
        },

        summaryLabel() {
            const total = this.filteredRows().length;
            if (total === 0) return '0 komoditas';
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, total);
            return `Menampilkan ${start}–${end} dari ${total} komoditas inventaris`;
        },

        handleAction(action) {
            const label = String(action?.label || '');
            if (label.includes('Sinkron') || label.includes('Segarkan')) {
                window.location.reload();
                return;
            }
            if (label.includes('Unduh') || label.includes('CSV')) {
                window.location.href = '/sekretaris/laporan/export';
                return;
            }
            if (label.includes('Kritis')) {
                this.criticalOnly = !this.criticalOnly;
                this.toggleCritical();
                return;
            }
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

        detail(row) {
            this.run(
                `Pemantauan ${row.name}`,
                `Total: ${row.total_value} • Terkunci di Pesanan: ${row.locked_value} • Siap Dipesan (ATP): ${row.atp_value}`,
                'info'
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
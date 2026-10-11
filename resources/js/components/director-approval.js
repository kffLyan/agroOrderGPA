const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(/[^0-9.-]/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

const rupiah = (value) =>
    'Rp ' + Math.round(numberOf(value)).toLocaleString('id-ID', { maximumFractionDigits: 0 });

const filterToneClasses = {
    all: 'bg-brand text-white outline-brand hover:bg-brand-hover',
    discount: 'bg-accent text-ink outline-success-deep hover:opacity-90',
    top: 'bg-accent text-ink outline-success-deep hover:opacity-90',
    annual: 'bg-accent text-ink outline-success-deep hover:opacity-90',
};

export default function directorApproval(rows = [], labels = {}) {
    const copy = labels && typeof labels === 'object' ? labels : {};

    return {
        filter: 'all',
        search: '',
        selected: [],
        totalRows: (Array.isArray(rows) ? rows : []).length,
        totalLabel: String(copy.shown_label ?? ''),
        rows: (Array.isArray(rows) ? rows : []).map((row) => ({
            ...row,
            contract: String(row.contract ?? ''),
            filter_key: String(row.filter_key ?? ''),
            client: String(row.client ?? ''),
            commodity: String(row.commodity ?? ''),
            submitted_by: String(row.submitted_by ?? ''),
            value: numberOf(row.value),
            haystack: [row.contract, row.client, row.commodity, row.submitted_by]
                .map((value) => String(value ?? '').toLowerCase())
                .join(' '),
        })),

        /* ---------- filter & search ---------- */

        setFilter(key) {
            this.filter = String(key);
            this.selected = [];
        },

        matches(key, index) {
            const row = this.rows[index];

            if (!row) {
                return false;
            }

            if (this.filter !== 'all' && this.filter !== String(key)) {
                return false;
            }

            const needle = this.search.trim().toLowerCase();

            return needle === '' || row.haystack.includes(needle);
        },

        visibleRows() {
            return this.rows.filter((row) => this.matches(row.filter_key));
        },

        visibleCount() {
            return this.visibleRows().length;
        },

        visibleLabel() {
            return `Menampilkan ${this.visibleCount()} dari ${this.totalRows} Pengajuan Pending`;
        },

        filterClass(key) {
            const base = 'transition-colors';
            const tone =
                this.filter === String(key)
                    ? filterToneClasses[String(key)] ?? filterToneClasses.all
                    : null;

            return tone ? `${base} ${tone}` : `${base} bg-transparent text-ink-body outline-line-board hover:bg-surface-shell`;
        },

        /* ---------- batch selection ---------- */

        selectedCount() {
            return this.selectedRows().length;
        },

        selectedRows() {
            return this.visibleRows().filter((row) => this.selected.includes(row.contract));
        },

        selectedValue() {
            return this.selectedRows().reduce((total, row) => total + row.value, 0);
        },

        selectedLabel() {
            return `${this.selectedCount()} / ${this.visibleRows().length} Terpilih`;
        },

        selectedValueLabel() {
            return rupiah(this.selectedValue());
        },

        selectAll() {
            this.selected = this.visibleRows().map((row) => row.contract);
        },

        /* ---------- row actions ---------- */

        detail(contract) {
            this.run('Detail Pengajuan', `Simpan draft telah dibuka untuk ${contract}.`, 'info');
        },

        approve(contract) {
            const row = this.rows.find((item) => item.contract === contract);

            this.selected = this.selected.filter((item) => item !== contract);
            this.run(
                'Kontrak Disetujui',
                row
                    ? `${row.client} (${row.contract}) disetujui senilai ${rupiah(row.value)}.`
                    : `${contract} disetujui.`,
                'success',
            );
        },

        approveSelected() {
            const rows = this.selectedRows();

            if (rows.length === 0) {
                return;
            }

            const value = rows.reduce((total, row) => total + row.value, 0);
            this.selected = [];
            this.run(
                'Batch Otorisasi Diproses',
                `${rows.length} kontrak senilai ${rupiah(value)} disetujui asparagus.`,
                'success',
            );
        },

        revise(contract) {
            const row = this.rows.find((item) => item.contract === contract);

            this.selected = this.selected.filter((item) => item !== contract);
            this.run(
                'Revisi Diminta',
                row
                    ? `${row.client} dikembalikan ke ${row.submitted_by} untuk revisi margin.`
                    : `${contract} dikembalikan untuk revisi.`,
                'warning',
            );
        },

        reject(contract) {
            const row = this.rows.find((item) => item.contract === contract);

            this.selected = this.selected.filter((item) => item !== contract);
            this.run(
                'Kontrak Ditolak',
                row ? `${row.client} (${row.contract}) ditolak.` : `${contract} ditolak.`,
                'danger',
            );
        },

        /* ---------- page actions ---------- */

        printAuthorization() {
            window.print();
            this.run('Cetak Disiapkan', 'Dialog cetak browser dibuka untuk dokumen otorisasi.', 'info');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

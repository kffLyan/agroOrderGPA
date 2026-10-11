const normalize = (value) => String(value ?? '').toLowerCase();

const paymentKeys = {
    'Semua Status Tagihan': 'all',
    Lunas: 'lunas',
    'Tempo TOP 14': 'top14',
    'Tempo TOP 30': 'top30',
    'Pending Verif': 'pending',
};

export default function secretaryReports(rows = [], filters = [], tabs = [], totalTransactions = 64, hash = '') {
    const journalRows = Array.isArray(rows) ? rows : [];
    const filterList = Array.isArray(filters) ? filters : [];
    const tabList = Array.isArray(tabs) ? tabs : [];

    const initial = {};

    filterList.forEach((filter) => {
        initial[filter.key] = filter.value;
    });

    return {
        journal: journalRows,
        filters: filterList,
        tabs: tabList,
        totalTransactions: Number(totalTransactions) || 0,
        hash: String(hash || ''),
        selected: { ...initial },
        defaults: { ...initial },
        tab: 'sales',

        get activeTab() {
            return this.tab === 'sales';
        },

        get activeTabLabel() {
            return this.tabs.find((item) => item.key === this.tab)?.label ?? 'Tab 1';
        },

        get activePaymentKey() {
            return paymentKeys[this.selected.payment] ?? 'all';
        },

        visibleRows() {
            return this.journal.filter((row) => this.matches(row));
        },

        matches(row) {
            if (this.activePaymentKey !== 'all' && row.status !== this.activePaymentKey) {
                return false;
            }

            const needle = normalize(this.query).trim();

            if (! needle) {
                return true;
            }

            return [row.invoice, row.sj, row.client, row.location, row.commodity]
                .map(normalize)
                .some((value) => value !== '' && value.includes(needle));
        },

        isVisible(row) {
            return this.matches(row);
        },

        get totalEstimate() {
            return this.visibleRows().reduce((total, row) => total + Number(row.estimate ?? 0), 0);
        },

        get totalNet() {
            return Math.round(this.visibleRows().reduce((total, row) => total + Number(row.net ?? 0), 0) * 10) / 10;
        },

        get totalValue() {
            return this.visibleRows().reduce((total, row) => total + Number(row.value ?? 0), 0);
        },

        get totalDeviation() {
            return this.deviation(this.totalEstimate, this.totalNet);
        },

        number(value, fraction = 0) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: fraction,
                maximumFractionDigits: fraction,
            }).format(Number(value) || 0);
        },

        kg(value) {
            return this.number(value, 1);
        },

        kgInt(value) {
            return this.number(value, 0);
        },

        rupiah(value) {
            return `Rp ${this.number(value)}`;
        },

        deviation(estimate, net) {
            const base = Number(estimate) || 0;

            if (base <= 0) {
                return '0.00';
            }

            return (((Number(net) - base) / base) * 100).toFixed(2);
        },

        deviationClass(estimate, net) {
            return Number(this.deviation(estimate, net)) <= -0.5 ? 'text-warning-caution' : 'text-success-deep';
        },

        isBlocked(row) {
            return Number(this.deviation(row.estimate, row.net)) <= -0.5;
        },

        setTab(key) {
            if (this.tab === key) {
                return;
            }

            this.tab = key;

            this.run('Tab Rekapitulasi Diubah', `${this.activeTabLabel} dimuat pada konsol rekapitulasi.`);
        },

        applyFilters() {
            this.run(
                'Filter Rekapitulasi Diterapkan',
                `${this.selected.period} • ${this.activePaymentKey === 'all' ? 'Semua Status Tagihan' : this.selected.payment} — ${this.visibleRows().length} transaksi dihitung ulang.`
            );
        },

        resetFilters() {
            this.selected = { ...this.defaults };

            this.run('Filter Direset', 'Seluruh parameter kembali ke default buku besar periode berjalan.');
        },

        openAudit(row, file) {
            this.run(
                'Berkas Audit Dibuka',
                `${row.invoice} / ${row.sj} — ${file} (${row.audit_files}/3 berkas trifecta) ditampilkan pada viewer dokumen audit.`
            );
        },

        act(key) {
            const messages = {
                sync: 'Sinkronisasi Audit Selesai',
                export: 'Ekspor Rekapitulasi Diproses',
                print: 'Cetak PDF Resmi Diproses',
                download: 'Unduh Excel Lengkap Diproses',
                pdf: 'Cetak Laporan Resmi Diproses',
                send: 'Rekapitulasi Terkirim ke Direktur',
            };

            const details = {
                sync: 'Audit 64 transaksi terakhir tersinkron pada stasiun timbangan pusat pukul 04:00 WIB.',
                export: 'Rekapitulasi .csv / .xlsx untuk periode berjalan sedang disusun dari buku besar aktual.',
                print: 'Dokumen Sah laporan PDF resmi disiapkan dengan stempel hash SHA-256 untuk tanda tangan Direktur Utama.',
                download: 'File rekapitulasi lengkap (.xlsx) dengan sheet jurnal, deviasi, dan aging piutang disiapkan.',
                pdf: 'Dokumen Sah PT Guna Panen Agro dicetak pada kertas kop resmi.',
                send: 'Rekapitulasi terkirim ke Konsol Direktur Utama dan tercatat pada audit log sekretariat.',
            };

            this.run(messages[key] ?? 'Aksi Rekapitulasi', details[key] ?? 'Permintaan dicatat pada audit trail.');
        },
    };
}
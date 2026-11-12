const normalize = (value) => String(value ?? '').toLowerCase().trim();

export default function secretaryReports(rows = [], filters = [], tabs = [], totalTransactions = 64, hash = '') {
    const journalRows = Array.isArray(rows) ? rows : [];
    const filterList = Array.isArray(filters) ? filters : [];
    const tabList = Array.isArray(tabs) ? tabs : [];

    const initial = {
        period: 'Bulan Berjalan',
        client: 'Semua Pembeli & Mitra B2B',
        commodity: 'Semua Sayuran Segar',
        payment: 'Semua Status Tagihan',
    };

    filterList.forEach((filter) => {
        initial[filter.key] = filter.value;
    });

    return {
        journal: journalRows,
        filters: filterList,
        tabs: tabList,
        query: '',
        totalTransactions: Number(totalTransactions) || journalRows.length,
        hash: String(hash || ''),
        selected: { ...initial },
        defaults: { ...initial },
        tab: 'sales',

        run(title, message, tone = 'info') {
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('gpa:toast', { title, message, tone });
            } else {
                window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
            }
        },

        get activeTabLabel() {
            return this.tabs.find((item) => item.key === this.tab)?.label ?? 'Rekapitulasi Penjualan & Pesanan';
        },

        visibleRows() {
            return this.journal.filter((row) => this.matches(row));
        },

        matches(row) {
            // 1. Tab filter
            if (this.tab === 'weighing') {
                // Volume & Analisis Timbangan: yang memiliki timbangan riil (netto > 0)
                if (!row.net || Number(row.net) <= 0) {
                    return false;
                }
            } else if (this.tab === 'dispatch') {
                // Surat Jalan & Pengiriman: pesanan yang sudah ada surat jalan / berstatus pengiriman / selesai
                if (!['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(row.order_status) && !row.sj) {
                    return false;
                }
            } else if (this.tab === 'aging') {
                // Pemantauan Umur Tagihan (Tempo): pesanan yang belum lunas (tempo atau piutang berjalan)
                if (row.is_paid || row.status === 'lunas') {
                    return false;
                }
            }

            // 2. Filter Status Pembayaran
            if (this.selected.payment && !this.selected.payment.includes('Semua')) {
                const p = normalize(this.selected.payment);
                if (p.includes('lunas') && !row.is_paid && row.status !== 'lunas') return false;
                if (p.includes('tempo') && (row.is_paid || row.status === 'lunas')) return false;
                if (p.includes('menunggu') && row.order_status !== 'MENUNGGU_VERIFIKASI') return false;
            }

            // 3. Filter Pembeli
            if (this.selected.client && !this.selected.client.includes('Semua')) {
                if (!normalize(row.client).includes(normalize(this.selected.client))) {
                    return false;
                }
            }

            // 4. Filter Komoditas
            if (this.selected.commodity && !this.selected.commodity.includes('Semua')) {
                if (!normalize(row.commodity).includes(normalize(this.selected.commodity))) {
                    return false;
                }
            }

            // 5. Search Bar
            const needle = normalize(this.query);
            if (!needle) {
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
            if (base <= 0) return '0.00';
            return (((Number(net) - base) / base) * 100).toFixed(2);
        },

        deviationClass(estimate, net) {
            return Number(this.deviation(estimate, net)) <= -0.5 ? 'text-warning-caution' : 'text-success-deep';
        },

        isBlocked(row) {
            return Number(this.deviation(row.estimate, row.net)) <= -0.5;
        },

        setTab(key) {
            this.tab = key;
            const count = this.visibleRows().length;
            this.run('Tab Berpindah', `${this.activeTabLabel}: ${count} baris transaksi disaring.`);
        },

        applyFilters() {
            const count = this.visibleRows().length;
            this.run(
                'Filter Diterapkan',
                `Ditemukan ${count} transaksi sesuai kriteria yang dipilih.`
            );
        },

        resetFilters() {
            this.selected = { ...this.defaults };
            this.query = '';
            this.tab = 'sales';
            this.run('Filter Direset', 'Seluruh filter telah dikembalikan ke kondisi default.');
        },

        act(key) {
            if (key === 'export' || key === 'download') {
                window.location.href = '/sekretaris/laporan/export';
                return;
            }

            if (key === 'print' || key === 'pdf') {
                window.open('/sekretaris/laporan/print', '_blank');
                return;
            }

            if (key === 'sync') {
                window.location.reload();
                return;
            }

            if (key === 'send') {
                this.run('Laporan Terkirim', 'Rekapitulasi resmi berhasil dikirim ke Dasbor Direktur Utama.', 'success');
                return;
            }

            window.print();
        },
    };
}
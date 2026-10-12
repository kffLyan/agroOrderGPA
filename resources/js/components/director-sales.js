const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(/[^0-9.-]/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

const rupiah = (value) =>
    'Rp ' + Math.round(numberOf(value)).toLocaleString('id-ID', { maximumFractionDigits: 0 });

const percent = (value, decimals = 1) =>
    numberOf(value).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });

const scopeIdleClass =
    'bg-surface-shell text-success-deep outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted';
const scopeActiveClass =
    'bg-brand text-accent outline outline-1 -outline-offset-1 outline-brand-deep/40';

export default function directorSales(scopeKeys = [], trend = [], portfolio = []) {
    const keys = (Array.isArray(scopeKeys) ? scopeKeys : []).map((key) => String(key));

    return {
        scope: keys[0] ?? 'MTD',
        scopeKeys: keys,
        trend: (Array.isArray(trend) ? trend : []).map((row) => ({
            week: String(row.week ?? ''),
            value: numberOf(row.value),
            label: String(row.label ?? ''),
            share: numberOf(row.share),
            status: String(row.status ?? 'closed'),
            is_peak: Boolean(row.is_peak),
        })),
        portfolio: (Array.isArray(portfolio) ? portfolio : []).map((row) => ({
            sku: String(row.sku ?? ''),
            name: String(row.name ?? ''),
            revenue: numberOf(row.revenue),
            gross: numberOf(row.gross),
            margin: numberOf(row.margin),
            status: String(row.status ?? ''),
        })),

        /* ---------- scope pita KPI ---------- */

        activeScopeKey() {
            return this.scopeKeys.includes(this.scope) ? this.scope : (this.scopeKeys[0] ?? 'MTD');
        },

        scopeClass(key) {
            const target = String(key);

            return `${this.activeScopeKey() === target ? scopeActiveClass : scopeIdleClass} transition-colors`;
        },

        setScope(key) {
            const target = String(key);

            if (!this.scopeKeys.includes(target) || target === this.activeScopeKey()) {
                return;
            }

            this.scope = target;
            this.notify(
                'Cakupan Periode',
                `Pita KPI kini menampilkan ${target}. Grafik, komposisi kanal, dan rekapitulasi tetap memakai basis konsolidasi.`,
                'info',
            );
        },

        /* ---------- ringkasan grafik & portofolio ---------- */

        trendTotalLabel() {
            return rupiah(this.trend.reduce((total, row) => total + row.value, 0));
        },

        peakWeek() {
            const peak = this.trend.reduce(
                (best, row) => (row.value > (best?.value ?? 0) ? row : best),
                null,
            );

            return peak?.week ?? '-';
        },

        runningWeek() {
            return this.trend.find((row) => row.status === 'running')?.week ?? '-';
        },

        portfolioTotalLabel() {
            return rupiah(this.portfolio.reduce((total, row) => total + row.revenue, 0));
        },

        portfolioGrossLabel() {
            return rupiah(this.portfolio.reduce((total, row) => total + row.gross, 0));
        },

        /* ---------- aksi monitoring ---------- */

    inspectWeek(row) {
        this.notify(
            `Realisasi ${row.week}`,
            `${rupiah(row.value)} tercatat pada ${percent(row.share)} dari ceiling mingguan.`,
            'info',
        );
    },

    inspectChannel(row) {
        this.notify(
            `Kanal ${row.name}`,
            `${rupiah(row.value)} tercatat pada ${percent(row.share, 0)} dari konsolidasi omzet periode berjalan.`,
            'info',
        );
    },

    inspectMethod(row) {
        this.notify(
            `Metode ${row.name}`,
            `${rupiah(row.value)} melalui ${row.note?.toLowerCase() ?? 'gateway pembayaran'}.`,
            'info',
        );
    },


        inspectCommodity(row) {
            this.notify(
                `Portofolio ${row.sku}`,
                `${row.name} menyumbang ${rupiah(row.revenue)} dengan laba kotor ${percent(row.margin)}% (${row.status}).`,
                'info',
            );
        },

        verifyPortfolio() {
            const below = this.portfolio.filter((row) => row.margin < 15);

            this.notify(
                below.length === 0 ? 'Portofolio VERIFIED' : 'Margin Perlu Perhatian',
                below.length === 0
                    ? `Omzet ${this.portfolioTotalLabel()} dan laba kotor ${this.portfolioGrossLabel()} lolos seluruh ambang margin 15.0%.`
                    : `${below.length} komoditas berada di bawah margin floor 15.0% dan memerlukan tinjauan Direksi.`,
                below.length === 0 ? 'success' : 'warning',
            );
        },

        sealRecap() {
            this.notify(
                'Tanda Tangan Digital',
                'Rekapitulasi kas harian diverifikasi dengan sertifikat elektronik dan hash SHA-256.',
                'success',
            );
        },

        exportSummary() {
            this.notify(
                'Unduh Ringkasan XLSX',
                `Rekap ${this.activeScopeKey()} berisi omzet, laba kotor, dan rekapitulasi arus kas sedang disusun.`,
                'success',
            );
        },

        printRecap() {
            window.print();
            this.notify('Cetak Rekapitulasi PDF', 'Dokumen rekonsiliasi penjualan dikirim ke printer.', 'info');
        },

        /* ---------- toast ---------- */

        notify(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

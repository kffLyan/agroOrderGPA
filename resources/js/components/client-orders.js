const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

/**
 * Halaman "Daftar Pesanan Saya (Orders Management)".
 *
 * Seluruh data pesanan di-render server-side; Alpine hanya menangani filter
 * status/tanggal/gudang/pembayaran, pencarian nomor PO & surat jalan, sakelar
 * deviasi timbangan, serta navigasi halaman.
 */
export default function clientOrders(defaults = {}) {
    return {
        orders: defaults.orders ?? [],
        tabs: defaults.tabs ?? [],
        page: 1,
        lastPage: defaults.pagination?.last_page ?? 1,
        total: defaults.pagination?.total ?? 0,
        perPage: defaults.pagination?.per_page ?? 5,
        status: 'all',
        range: defaults.filters?.range?.value ?? '30 Hari Terakhir',
        hub: 'all',
        payment: 'all',
        deviationOnly: false,
        search: '',

        init() {
            this.lastPage = defaults.pagination?.last_page ?? 1;
            this.total = defaults.pagination?.total ?? 0;
            this.perPage = defaults.pagination?.per_page ?? 5;
            this.range = defaults.filters?.range?.value ?? '30 Hari Terakhir';
        },

        money(value) {
            return rupiah.format(value ?? 0);
        },

        matchesTab(order) {
            return this.status === 'all' || order.stage === this.status;
        },

        matchesHub(order) {
            return this.hub === 'all' || order.hub === this.hub;
        },

        matchesPayment(order) {
            if (! this.payment || this.payment === 'all') {
                return true;
            }

            return order.payment === this.payment;
        },

        matchesDeviation(order) {
            return ! this.deviationOnly || (order.deviation_percent ?? 0) > 1;
        },

        matchesSearch(order) {
            const keyword = this.search.trim().toLowerCase();

            if (! keyword) {
                return true;
            }

            return [order.po, order.sj, order.vehicle]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(keyword));
        },

        isVisible(order) {
            return this.matchesTab(order)
                && this.matchesHub(order)
                && this.matchesPayment(order)
                && this.matchesDeviation(order)
                && this.matchesSearch(order);
        },

        visibleOrders() {
            return this.orders.filter((order) => this.isVisible(order));
        },

        visibleCount() {
            return this.visibleOrders().length;
        },

        hasFilters() {
            return this.status !== 'all'
                || this.hub !== 'all'
                || (this.payment !== 'all' && Boolean(this.payment))
                || this.deviationOnly
                || this.search.trim() !== '';
        },

        resetFilters() {
            this.status = 'all';
            this.hub = 'all';
            this.payment = 'all';
            this.range = defaults.filters?.range?.value ?? '30 Hari Terakhir';
            this.deviationOnly = false;
            this.search = '';
            this.page = 1;
        },

        selectTab(value) {
            this.status = value;
            this.page = 1;
        },

        tabCount(value) {
            return this.tabs.find((tab) => tab.value === value)?.count ?? 0;
        },

        /**
         * Pratinjau hanya memuat halaman 1 (lima baris). Halaman berikutnya
         * disimulasikan seperti tombol aksi lain di dashboard.
         */
        goToPage(page) {
            this.page = Math.min(Math.max(1, page), this.lastPage);

            this.$dispatch('gpa:toast', {
                title: `Halaman ${this.page} dari ${this.lastPage}`,
                message: 'Data halaman berikutnya dimuat dari API Orders GPA saat modul terhubung.',
                tone: 'info',
            });
        },

        pageNumbers() {
            const numbers = [1, 2, 3];

            return [...new Set([...numbers, this.lastPage])].sort((a, b) => a - b);
        },

        summaryLabel() {
            return `Menampilkan ${this.visibleCount()} dari ${this.total} pesanan aktif`;
        },

        actionTone(variant) {
            if (variant === 'solid') {
                return 'bg-brand text-accent hover:bg-brand-hover';
            }

            if (variant === 'warning') {
                return 'border border-warning bg-canvas text-warning-deep hover:bg-warning-soft/40';
            }

            if (variant === 'danger') {
                return 'border border-danger/40 bg-canvas text-danger hover:bg-danger-soft';
            }

            return 'border border-line-board bg-canvas text-ink hover:border-brand hover:bg-surface-muted';
        },

        notifyAction(order, action) {
            if (order?.id) {
                window.location.href = `/klien/orders/${order.id}`;
                return;
            }

            this.$dispatch('gpa:toast', {
                title: action,
                message: `Memproses informasi pesanan ${order?.po ?? ''}...`,
                tone: action === 'Batal PO' ? 'danger' : 'info',
            });
        },

        exportRecap() {
            const statusParam = encodeURIComponent(this.status || 'all');
            const printUrl = `/klien/orders/print-rekap?status=${statusParam}&auto_print=1`;

            window.open(printUrl, '_blank');

            this.$dispatch('gpa:toast', {
                title: 'Dokumen Rekap PDF Disiapkan',
                message: 'Membuka dokumen resmi rekapitulasi pesanan PO siap cetak & simpan PDF.',
                tone: 'success',
            });
        },
    };
}
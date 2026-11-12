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
        total: defaults.pagination?.total ?? 0,
        perPage: defaults.pagination?.per_page ?? 10,
        status: 'all',
        range: defaults.filters?.range?.value ?? 'all',
        hub: 'all',
        payment: 'all',
        deviationOnly: false,
        search: '',

        init() {
            this.total = defaults.pagination?.total ?? 0;
            this.perPage = defaults.pagination?.per_page ?? 10;
            this.range = defaults.filters?.range?.value ?? 'all';
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

            return String(order.payment ?? '').toLowerCase() === String(this.payment).toLowerCase();
        },

        matchesRange(order) {
            if (! this.range || this.range === 'all') {
                return true;
            }

            const rawDate = order.created_at || order.target_delivery_date;
            if (! rawDate) {
                return true;
            }

            const orderDate = new Date(rawDate);
            if (isNaN(orderDate.getTime())) {
                return true;
            }

            const now = new Date();

            if (this.range === '30d') {
                const diffTime = now.getTime() - orderDate.getTime();
                const diffDays = diffTime / (1000 * 60 * 60 * 24);
                return diffDays >= -1 && diffDays <= 30;
            }

            if (this.range === 'mtd') {
                return orderDate.getFullYear() === now.getFullYear() && orderDate.getMonth() === now.getMonth();
            }

            return true;
        },

        matchesDeviation(order) {
            return ! this.deviationOnly || (order.deviation_percent ?? 0) > 1;
        },

        matchesSearch(order) {
            const keyword = this.search.trim().toLowerCase();

            if (! keyword) {
                return true;
            }

            const searchTargets = [
                order.po,
                order.order_number,
                order.sj,
                order.surat_jalan_number,
                order.vehicle,
                order.vehicle_plate_number,
                order.driver?.name,
                order.driver_name,
                order.driver_note,
                order.commodities_summary,
                order.delivery_address,
                order.status_label,
            ];

            return searchTargets
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(keyword));
        },

        matchesFilter(order) {
            return this.matchesTab(order)
                && this.matchesHub(order)
                && this.matchesPayment(order)
                && this.matchesRange(order)
                && this.matchesDeviation(order)
                && this.matchesSearch(order);
        },

        filteredOrders() {
            return this.orders.filter((order) => this.matchesFilter(order));
        },

        get lastPage() {
            return Math.max(1, Math.ceil(this.filteredOrders().length / this.perPage));
        },

        pagedOrders() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredOrders().slice(start, start + this.perPage);
        },

        isVisible(order) {
            if (! this.matchesFilter(order)) {
                return false;
            }
            return this.pagedOrders().some((item) => (item.id === order.id) || (item.po === order.po));
        },

        visibleOrders() {
            return this.pagedOrders();
        },

        visibleCount() {
            return this.filteredOrders().length;
        },

        hasFilters() {
            return this.status !== 'all'
                || this.hub !== 'all'
                || (this.payment !== 'all' && Boolean(this.payment))
                || (this.range !== 'all' && Boolean(this.range))
                || this.deviationOnly
                || this.search.trim() !== '';
        },

        resetFilters() {
            this.status = 'all';
            this.hub = 'all';
            this.payment = 'all';
            this.range = 'all';
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
            const total = this.filteredOrders().length;
            if (total === 0) {
                return '0 pesanan aktif';
            }
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, total);
            return `Menampilkan ${start}–${end} dari ${total} pesanan aktif`;
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
            const printUrl = `/klien/pesanan/print-rekap?status=${statusParam}&auto_print=1`;

            window.open(printUrl, '_blank');

            this.$dispatch('gpa:toast', {
                title: 'Dokumen Rekap PDF Disiapkan',
                message: 'Membuka dokumen resmi rekapitulasi pesanan PO siap cetak & simpan PDF.',
                tone: 'success',
            });
        },
    };
}
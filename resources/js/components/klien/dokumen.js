/**
 * Halaman "Pusat Dokumen & Faktur Konsolidasi Klien B2B".
 *
 * Faktur, lampiran surat jalan, rincian PPN, dan rekening pembayaran dirender
 * server-side. Alpine menangani perpindahan tab arsip, filter periode, pencarian
 * nomor faktur/surat jalan, pemilihan lampiran aktif, dan simulasi aksi dokumen.
 */
export default function clientDocuments(defaults = {}) {
    return {
        invoices: defaults.invoices ?? [],
        tabs: defaults.tabs ?? [],
        counts: defaults.counts ?? {},
        page: 1,
        perPage: 10,
        total: defaults.pagination?.total ?? 0,
        defaultPeriod: defaults.periods?.[0]?.value ?? 'all',
        tab: defaults.tabs?.[0]?.value ?? 'all',
        period: defaults.periods?.[0]?.value ?? 'all',
        search: '',
        selected: defaults.invoices?.[0]?.po ?? null,
        proofMenuOpen: false,

        init() {
            this.perPage = 10;
            this.page = 1;
            this.selected = defaults.invoices?.[0]?.po ?? null;
            this.keepSelectionVisible();
        },

        selectTab(value) {
            this.tab = value;
            this.page = 1;
        },

        tabCount(value) {
            return this.counts[value] ?? 0;
        },

        /**
         * Pratinjau selalu memakai master faktur konsolidasi; tab arsip hanya
         * mengganti judul, jumlah dokumen, dan catatan arsip.
         */
        tabNotice() {
            const tab = this.tabs.find((item) => item.value === this.tab);

            if (! tab || tab.value === 'faktur') {
                return '';
            }

            return `${this.tabCount(tab.value)} ${tab.archive} tersedia di vault GPA; pratinjau memakai master faktur konsolidasi.`;
        },

        archiveLabel() {
            if (! this.tab || this.tab === 'all') {
                return 'Faktur Konsolidasi TOP (Faktur Bulanan)';
            }

            return this.tabs.find((item) => item.value === this.tab)?.label
                ?? 'Faktur Konsolidasi TOP (Faktur Bulanan)';
        },

        matchesTab(invoice) {
            if (! this.tab || this.tab === 'all') {
                return true;
            }

            if (this.tab === 'unpaid') {
                return ['UNPAID', 'PARTIAL', 'OVERDUE'].includes(invoice.status_raw);
            }

            if (this.tab === 'settled') {
                return invoice.status_raw === 'PAID';
            }

            if (this.tab === 'tax') {
                return true;
            }

            return true;
        },

        matchesPeriod(invoice) {
            return this.period === 'all' || invoice.period === this.period;
        },

        matchesSearch(invoice) {
            const keyword = this.search.trim().toLowerCase();

            if (! keyword) {
                return true;
            }

            return [invoice.po, ...(invoice.attachments ?? []).map((item) => item.sj)]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(keyword));
        },

        matchesFilter(invoice) {
            return this.matchesTab(invoice) && this.matchesPeriod(invoice) && this.matchesSearch(invoice);
        },

        filteredInvoices() {
            return this.invoices.filter((invoice) => this.matchesFilter(invoice));
        },

        get lastPage() {
            return Math.max(1, Math.ceil(this.filteredInvoices().length / this.perPage));
        },

        pagedInvoices() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredInvoices().slice(start, start + this.perPage);
        },

        isVisible(invoice) {
            if (! this.matchesFilter(invoice)) {
                return false;
            }
            return this.pagedInvoices().some((item) => item.po === invoice.po);
        },

        visibleInvoices() {
            return this.pagedInvoices();
        },

        visibleCount() {
            return this.filteredInvoices().length;
        },

        hasFilters() {
            return (this.tab !== 'all' && this.tab !== 'faktur') || this.period !== this.defaultPeriod || this.search.trim() !== '';
        },

        resetFilters() {
            this.tab = 'all';
            this.period = this.defaultPeriod;
            this.search = '';
            this.page = 1;
            this.keepSelectionVisible();
        },

        selectInvoice(po) {
            this.selected = po;
        },

        isSelected(po) {
            return this.selected === po;
        },

        /**
         * Panel lampiran aktif selalu mengikuti baris yang sedang terlihat
         * supaya detail tidak pernah menampilkan faktur tersaring.
         */
        keepSelectionVisible() {
            const visible = this.pagedInvoices();

            if (visible.length === 0) {
                return;
            }

            if (! visible.some((invoice) => invoice.po === this.selected)) {
                this.selected = visible[0].po;
            }
        },

        summaryLabel() {
            const total = this.filteredInvoices().length;
            if (total === 0) {
                return '0 faktur ditemukan';
            }
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, total);
            return `Menampilkan ${start}–${end} dari ${total} total faktur konsolidasi`;
        },

        goToPage(page) {
            const target = Math.min(Math.max(1, page), this.lastPage);
            if (this.page !== target) {
                this.page = target;
                this.keepSelectionVisible();
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

        runAction(action, invoice = null) {
            if (action?.href) {
                window.location.href = action.href;
                return;
            }

            if (invoice?.po) {
                window.location.href = `/klien/payment-proof?tagihan=${encodeURIComponent(invoice.po)}`;
                return;
            }

            const label = action?.label ?? '';

            if (label.includes('CSV') || label.includes('Rekap')) {
                this.exportInvoicesCsv();
                return;
            }

            if (label.includes('Cetak') || label.includes('Print')) {
                window.open('/klien/dokumen/print-rekap?auto_print=1', '_blank');
                return;
            }

            this.$dispatch('gpa:toast', {
                title: action?.label ?? 'Aksi Dokumen',
                message: 'Memproses aksi dokumen...',
                tone: 'info',
            });
        },

        exportInvoicesCsv() {
            const rows = [
                ['No', 'No Faktur', 'Periode', 'Bobot Netto (kg)', 'Total Tagihan (Rp)', 'Jatuh Tempo', 'Status Tagihan'],
            ];

            this.visibleInvoices().forEach((inv, idx) => {
                rows.push([
                    idx + 1,
                    inv.po,
                    inv.period_label || '-',
                    inv.netto_value || '0',
                    inv.total_label ? inv.total_label.replace(/[^\d]/g, '') : '',
                    inv.due || '-',
                    inv.status || inv.status_raw,
                ]);
            });

            const csvContent = rows.map((r) => r.map((val) => `"${String(val).replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', `rekap-faktur-gpa-${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            this.$dispatch('gpa:toast', {
                title: 'Rekap Tagihan Diunduh',
                message: `${this.visibleCount()} faktur konsolidasi berhasil diekspor ke file CSV.`,
                tone: 'success',
            });
        },

        toggleProofMenu() {
            this.proofMenuOpen = ! this.proofMenuOpen;
        },

        /**
         * Item dropdown tanpa tautan tetap berupa simulasi aksi dokumen GPA.
         */
        runProofMenuAction(item) {
            this.proofMenuOpen = false;

            this.$dispatch('gpa:toast', {
                title: item.label,
                message: item.note,
                tone: 'info',
            });
        },

        /**
         * Rekening pembayaran resmi GPA bisa disalin langsung dari dropdown kop.
         */
        async copySettlementAccount() {
            this.proofMenuOpen = false;

            try {
                await navigator.clipboard.writeText('840-552-1920');

                this.$dispatch('gpa:toast', {
                    title: 'Rekening Pembayaran GPA',
                    message: 'BCA: 840-552-1920 • a.n. Green Pasundan Agriculture tersalin ke papan klip.',
                    tone: 'success',
                });
            } catch {
                this.$dispatch('gpa:toast', {
                    title: 'Rekening Pembayaran GPA',
                    message: 'BCA: 840-552-1920 • a.n. Green Pasundan Agriculture — salin manual bila papan klip dibatasi browser.',
                    tone: 'info',
                });
            }
        },

        contactFinance() {
            this.$dispatch('gpa:toast', {
                title: 'Kontak Tim Keuangan',
                message: 'Layanan Keuangan GPA siap membantu rekonsiliasi tagihan Anda.',
                tone: 'info',
            });
        },
    };
}
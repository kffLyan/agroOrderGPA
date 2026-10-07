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
        lastPage: defaults.pagination?.last_page ?? 1,
        total: defaults.pagination?.total ?? 0,
        defaultPeriod: defaults.periods?.[0]?.value ?? '2026-q4',
        tab: 'faktur',
        period: defaults.periods?.[0]?.value ?? '2026-q4',
        search: '',
        selected: defaults.invoices?.[0]?.po ?? null,
        proofMenuOpen: false,

        init() {
            this.lastPage = defaults.pagination?.last_page ?? 1;
            this.total = defaults.pagination?.total ?? 0;
            this.selected = defaults.invoices?.[0]?.po ?? null;
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

        isVisible(invoice) {
            return this.matchesTab(invoice) && this.matchesPeriod(invoice) && this.matchesSearch(invoice);
        },

        visibleInvoices() {
            return this.invoices.filter((invoice) => this.isVisible(invoice));
        },

        visibleCount() {
            return this.visibleInvoices().length;
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
            const visible = this.visibleInvoices();

            if (visible.length === 0) {
                return;
            }

            if (! visible.some((invoice) => invoice.po === this.selected)) {
                this.selected = visible[0].po;
            }
        },

        summaryLabel() {
            return `Menampilkan ${this.visibleCount()} dari ${this.total} total faktur konsolidasi (2026)`;
        },

        goToPage(page) {
            this.page = Math.min(Math.max(1, page), this.lastPage);

            this.$dispatch('gpa:toast', {
                title: `Halaman ${this.page} dari ${this.lastPage}`,
                message: 'Halaman berikutnya dimuat dari API Dokumen & Faktur GPA saat modul terhubung.',
                tone: 'info',
            });
        },

        pageNumbers() {
            return [...new Set([1, 2, this.lastPage])].sort((a, b) => a - b);
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
                window.open('/klien/documents/print-rekap?auto_print=1', '_blank');
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
         * Rekening settlement GPA bisa disalin langsung dari dropdown kop.
         */
        async copySettlementAccount() {
            this.proofMenuOpen = false;

            try {
                await navigator.clipboard.writeText('840-552-1920');

                this.$dispatch('gpa:toast', {
                    title: 'Rekening Settlement GPA',
                    message: 'BCA: 840-552-1920 • a.n. PT Green Pasundan Agriculture tersalin ke papan klip.',
                    tone: 'success',
                });
            } catch {
                this.$dispatch('gpa:toast', {
                    title: 'Rekening Settlement GPA',
                    message: 'BCA: 840-552-1920 • a.n. PT Green Pasundan Agriculture — salin manual bila papan klip dibatasi browser.',
                    tone: 'info',
                });
            }
        },

        contactFinance() {
            this.$dispatch('gpa:toast', {
                title: 'Kontak Tim Finance',
                message: 'SLA Hotline 24/7 GPA Finance siap membantu rekonsiliasi tagihan.',
                tone: 'info',
            });
        },
    };
}
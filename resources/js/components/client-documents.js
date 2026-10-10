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
            return this.matchesPeriod(invoice) && this.matchesSearch(invoice);
        },

        visibleInvoices() {
            return this.invoices.filter((invoice) => this.isVisible(invoice));
        },

        visibleCount() {
            return this.visibleInvoices().length;
        },

        hasFilters() {
            return this.period !== this.defaultPeriod || this.search.trim() !== '';
        },

        resetFilters() {
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
            this.$dispatch('gpa:toast', {
                title: action.label,
                message: invoice
                    ? `${invoice.po} - ${action.label} akan connected ke modul Finance & Audit GPA.`
                    : `${action.label} akan connected ke modul Finance & Audit GPA.`,
                tone: action.variant === 'solid' ? 'success' : 'info',
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
                await navigator.clipboard.writeText('128-094-8891');

                this.$dispatch('gpa:toast', {
                    title: 'Rekening Settlement GPA',
                    message: '128-094-8891 • a/n PT Agro Pasti Ada (CV Gema Perkasa) tersalin ke papan klip.',
                    tone: 'success',
                });
            } catch {
                this.$dispatch('gpa:toast', {
                    title: 'Rekening Settlement GPA',
                    message: '128-094-8891 • a/n PT Agro Pasti Ada (CV Gema Perkasa) — salin manual bila papan klip dibatasi browser.',
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
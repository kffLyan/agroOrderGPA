const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryInvoicing(clients = [], ledger = [], statusFilters = {}, totalInvoices = 42, printUrl = '') {
    const clientList = Array.isArray(clients) ? clients : [];
    const ledgerRows = Array.isArray(ledger) ? ledger : [];

    return {
        clients: clientList,
        ledger: ledgerRows,
        printUrl: printUrl || '/cetak/faktur/__invoice__',
        query: '',
        statusFilters: {
            all: 'Filter Status',
            outstanding: 'Piutang Berjalan',
            paid: 'Sudah Lunas',
            ...(statusFilters ?? {}),
        },
        totalInvoices: Number(totalInvoices) || 0,
        clientId: clientList[0]?.id ?? null,
        selected: clientList[0]?.documents?.map((document) => document.sj) ?? [],
        termsFilter: 'all',
        statusMode: 'all',
        generated: [],
        page: 1,
        perPage: 10,

        run(title, message, tone = 'info') {
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('gpa:toast', { title, message, tone });
            } else {
                window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
            }
        },

        get client() {
            return this.clients.find((item) => String(item.id) === String(this.clientId)) ?? this.clients[0] ?? null;
        },

        get documents() {
            return this.client?.documents ?? [];
        },

        get selectedDocuments() {
            return this.documents.filter((document) => this.selected.includes(document.sj));
        },

        number(value, fraction = 0) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: fraction }).format(Number(value) || 0);
        },

        rupiah(value) {
            return `Rp ${this.number(value)}`;
        },

        weight(value) {
            return `${this.number(value)} kg`;
        },

        dueDate() {
            const client = this.client;

            if (! client) {
                return '—';
            }

            const issueStr = client.issue_date || new Date().toISOString().slice(0, 10);
            const issued = new Date(`${issueStr}T00:00:00`);
            if (isNaN(issued.getTime())) {
                const now = new Date();
                now.setDate(now.getDate() + Number(client.terms_days || 30));
                const day = String(now.getDate()).padStart(2, '0');
                return `${day} ${MONTHS[now.getMonth()]} ${now.getFullYear()}`;
            }

            issued.setDate(issued.getDate() + Number(client.terms_days || 0));

            const day = String(issued.getDate()).padStart(2, '0');

            return `${day} ${MONTHS[issued.getMonth()]} ${issued.getFullYear()}`;
        },

        creditLimit() {
            return Number(this.client?.plafon ?? 0);
        },

        creditUsed() {
            return Number(this.client?.used ?? 0);
        },

        creditSisa() {
            return Math.max(this.creditLimit() - this.creditUsed(), 0);
        },

        creditPercent() {
            if (! this.creditLimit()) {
                return 0;
            }

            return Math.min(Math.round((this.creditUsed() / this.creditLimit()) * 1000) / 10, 100);
        },

        documentsLabel() {
            const count = this.selectedDocuments.length;

            return `${this.number(count)} Surat Jalan (${this.number(this.totalWeight())} KG)`;
        },

        totalWeight() {
            return this.selectedDocuments.reduce((total, document) => total + Number(document.weight ?? 0), 0);
        },

        totalValue() {
            return this.selectedDocuments.reduce((total, document) => total + Number(document.value ?? 0), 0);
        },

        dueLabel() {
            return `${this.number(this.client?.terms_days ?? 0)} Hari Kalender (Jatuh Tempo: ${this.dueDate()})`;
        },

        isSelected(sj) {
            return this.selected.includes(sj);
        },

        belongsToClient(sj) {
            return this.documents.some((document) => document.sj === sj);
        },

        isGenerated(id) {
            return this.generated.includes(id);
        },

        switchClient(id) {
            if (! id || String(id) === String(this.clientId)) {
                return;
            }

            const next = this.clients.find((item) => String(item.id) === String(id));

            if (! next) {
                return;
            }

            this.clientId = next.id;
            this.selected = next.documents.map((document) => document.sj);

            this.run(
                'Entitas Klien B2B Dipilih',
                `${next.name} [${next.terms_label}] • sisa plafon ${this.rupiah(this.creditSisa())} • ${next.documents.length} SJ sah siap difakturkan.`
            );
        },

        toggleDocument(sj) {
            if (this.isSelected(sj)) {
                this.selected = this.selected.filter((item) => item !== sj);

                return;
            }

            this.selected.push(sj);
        },

        toggleAll() {
            this.selected = this.selectedDocuments.length === this.documents.length ? [] : this.documents.map((document) => document.sj);
        },

        allSelected() {
            return this.documents.length > 0 && this.selectedDocuments.length === this.documents.length;
        },

        generate() {
            if (! this.selectedDocuments.length) {
                this.run(
                    'Faktur Ditolak',
                    'Tidak ada Surat Jalan sah yang dipilih. Faktur hanya dapat diterbitkan dari SJ "Selesai" berserta POD & bobot netto sah.',
                    'danger'
                );

                return;
            }

            if (this.totalValue() > this.creditSisa()) {
                this.run(
                    'Plafon Kredit Melebihi Sisa',
                    `${this.client.name}: total tagihan ${this.rupiah(this.totalValue())} melebihi sisa plafon ${this.rupiah(this.creditSisa())}.`,
                    'danger'
                );

                return;
            }

            this.run(
                'Faktur Konsolidasi Terbit',
                `${this.documentsLabel()} atas nama ${this.client.name} — total tagihan ${this.rupiah(this.totalValue())}, jatuh tempo ${this.dueDate()}. Dokumen PDF & QR penagihan dikirim ke portal klien B2B.`
            );

            if (this.client?.name) {
                this.query = this.client.name;
                const ledgerSection = document.getElementById('buku-besar-faktur');
                if (ledgerSection) {
                    ledgerSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
        },

        termCount(days) {
            return this.ledger.filter((row) => Number(row.terms_days) === Number(days)).length;
        },

        statusLabel() {
            return this.statusFilters[this.statusMode] ?? this.statusFilters.all;
        },

        cycleStatus() {
            const order = ['all', 'outstanding', 'paid'];
            const index = order.indexOf(this.statusMode);

            this.statusMode = order[(index + 1) % order.length];
            this.page = 1;

            this.run('Filter Status Diperbarui', `${this.statusLabel()} • ${this.visibleRows().length} faktur ditampilkan.`);
        },

        setTerms(filter) {
            this.termsFilter = String(filter);
            this.page = 1;
        },

        matchesFilter(row) {
            if (this.termsFilter !== 'all' && String(row.terms_days) !== String(this.termsFilter)) {
                return false;
            }

            if (this.statusMode === 'paid' && ! row.paid) {
                return false;
            }

            if (this.statusMode === 'outstanding' && row.paid) {
                return false;
            }

            const needle = normalize(this.query).trim();

            if (! needle) {
                return true;
            }

            return [row.id, row.client, row.contract, row.status_label]
                .map(normalize)
                .some((value) => value !== '' && value.includes(needle));
        },

        filteredRows() {
            return this.ledger.filter((row) => this.matchesFilter(row));
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
            return this.pagedRows().some((item) => item.id === row.id);
        },

        visibleRows() {
            return this.pagedRows();
        },

        visibleCount() {
            return this.filteredRows().length;
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
            if (total === 0) return '0 faktur ditemukan';
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, total);
            return `Menampilkan ${start}–${end} dari ${total} baris ledger piutang berjalan`;
        },

        exportLedger() {
            window.location.href = '/sekretaris/faktur/export';
        },

        manualInvoice() {
            window.location.href = '/sekretaris/pesanan-manual';
        },

        viewInvoice(row) {
            if (row?.id) {
                const url = this.printUrl ? this.printUrl.replace('__invoice__', row.id) : `/cetak/faktur/${row.id}`;
                window.open(url, '_blank');
            } else {
                this.run('Detail Faktur', `${row.id} — ${row.client} • ${row.total_label} • ${row.terms_label} • jatuh tempo ${row.due}.`, 'info');
            }
        },

        printInvoice(row) {
            if (row?.id) {
                const baseUrl = this.printUrl ? this.printUrl.replace('__invoice__', row.id) : `/cetak/faktur/${row.id}`;
                const url = baseUrl.includes('?') ? `${baseUrl}&print=1` : `${baseUrl}?print=1`;
                window.open(url, '_blank');
            } else {
                window.print();
            }
        },

        followUp(row) {
            const tone = row.status_key === 'blocked' ? 'danger' : 'warning';

            this.run(
                row.status_key === 'blocked' ? 'Auto-Freeze Dikonfirmasi' : 'Follow-up Terjadwal',
                row.status_key === 'blocked'
                    ? `${row.id}: auto-freeze klien ${row.client} diperpanjang. PO baru ditolak sampai pelunasan.`
                    : `${row.id}: pengingat tagihan diteruskan ke PIC ${row.client} (jatuh tempo ${row.due}).`,
                tone
            );
        },
    };
}
const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryInvoicing(clients = [], ledger = [], statusFilters = {}, totalInvoices = 42, invoiceUrlTemplate = '') {
    const clientList = Array.isArray(clients) ? clients : [];
    const ledgerRows = Array.isArray(ledger) ? ledger : [];

    return {
        clients: clientList,
        ledger: ledgerRows,
        statusFilters: {
            all: 'Filter Status',
            outstanding: 'Piutang Berjalan',
            paid: 'Sudah Lunas',
            ...(statusFilters ?? {}),
        },
        totalInvoices: Number(totalInvoices) || 0,
        invoiceUrlTemplate,
        clientId: clientList[0]?.id ?? null,
        selected: clientList[0]?.documents?.map((document) => document.sj) ?? [],
        termsFilter: 'all',
        statusMode: 'all',
        generated: [],

        get client() {
            return this.clients.find((item) => item.id === this.clientId) ?? this.clients[0] ?? null;
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

            const issued = new Date(`${client.issue_date}T00:00:00Z`);
            issued.setUTCDate(issued.getUTCDate() + Number(client.terms_days || 0));

            const day = String(issued.getUTCDate()).padStart(2, '0');

            return `${day} ${MONTHS[issued.getUTCMonth()]} ${issued.getUTCFullYear()}`;
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
            if (! id || id === this.clientId) {
                return;
            }

            const next = this.clients.find((item) => item.id === id);

            if (! next) {
                return;
            }

            this.clientId = id;
            this.selected = next.documents.map((document) => document.sj);

            this.run(
                'Entitas Klien B2B Dipilih',
                `${next.name} [${next.terms_label} Hari] • sisa plafon ${this.rupiah(this.creditSisa())} • ${next.documents.length} SJ sah siap difakturkan.`
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
                    'Faktur Ditolak (Rule 05)',
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

            this.run('Filter Status Diperbarui', `${this.statusLabel()} • ${this.visibleRows().length} faktur ditampilkan.`);
        },

        setTerms(filter) {
            this.termsFilter = filter;
        },

        matchesFilter(row) {
            if (this.termsFilter !== 'all' && Number(row.terms_days) !== Number(this.termsFilter)) {
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

        visibleRows() {
            return this.ledger.filter((row) => this.matchesFilter(row));
        },

        exportLedger() {
            this.run(
                'Ekspor Ledger CSV',
                `${this.visibleRows().length} baris ledger diekspor dengan filter TOP aktif (${this.statusLabel()}). Arsip lampiran: Rule 14 & 05.`
            );
        },

        manualInvoice() {
            this.run(
                'Faktur Manual Baru',
                'Formulir faktur manual dibuka. Nomor faktur, NPWP klien, dan bukti POD wajib dilampirkan sebelum penerbitan.'
            );
        },

        viewInvoice(row) {
            window.location.assign(this.invoiceUrl(row));
        },

        printInvoice(row) {
            window.open(this.invoiceUrl(row), '_blank', 'noopener');
        },

        invoiceUrl(row) {
            return this.invoiceUrlTemplate
                .replace('__invoice__', encodeURIComponent(row.id));
        },

        followUp(row) {
            const tone = row.status_key === 'blocked' ? 'danger' : 'warning';

            this.run(
                row.status_key === 'blocked' ? 'Auto-Freeze Dikonfirmasi' : 'Follow-up Terjadwal',
                row.status_key === 'blocked'
                    ? `${row.id}: auto-freeze klien ${row.client} diperpanjang. PO baru ditolak sampai pelunasan.`
                    : `${row.id}: pengingat tagihan dikirim ke PIC ${row.client} (H-3 jatuh tempo ${row.due}).`,
                tone
            );
        },
    };
}
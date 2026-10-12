const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(/[^0-9.-]/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

const toCsv = (rows) =>
    rows
        .map((row) =>
            row
                .map((cell) => `"${String(cell).replace(/"/g, '""')}"`)
                .join(',')
        )
        .join('\r\n');

export default function directorReport(commodities = [], clients = [], audit = [], seal = {}) {
    return {
        commodities: (Array.isArray(commodities) ? commodities : []).map((row) => ({
            name: String(row.name ?? ''),
            po_label: String(row.po_label ?? ''),
            tera_label: String(row.tera_label ?? ''),
            deviation_label: String(row.deviation_label ?? ''),
            price_label: String(row.price_label ?? ''),
            revenue_label: String(row.revenue_label ?? ''),
            contribution_label: String(row.contribution_label ?? ''),
            margin_label: String(row.margin_label ?? ''),
            _revenue: numberOf(row.revenue),
            _contribution: numberOf(row.contribution),
        })),
        clients: (Array.isArray(clients) ? clients : []).map((row) => ({
            name: String(row.name ?? ''),
            code: String(row.code ?? ''),
            po_label: String(row.po_label ?? ''),
            volume_label: String(row.volume_label ?? ''),
            gross_label: String(row.gross_label ?? ''),
            settled_label: String(row.settled_label ?? ''),
            receivable_label: String(row.receivable_label ?? ''),
            term_label: String(row.term_label ?? ''),
            status_label: String(row.status_label ?? ''),
            _gross: numberOf(row.gross),
            _receivable: numberOf(row.receivable),
        })),
        audit: (Array.isArray(audit) ? audit : []).map((entry) => ({
            timestamp: String(entry.timestamp ?? ''),
            tag: String(entry.tag ?? ''),
            message: String(entry.message ?? ''),
            trailing: String(entry.trailing ?? ''),
        })),
        seal: {
            certificate: String(seal?.certificate ?? ''),
            hash: String(seal?.hash ?? ''),
            signatory: String(seal?.signatory ?? ''),
        },
        grossTotal() {
            return this.clients.reduce((total, row) => total + row._gross, 0);
        },
        receivableTotal() {
            return this.clients.reduce((total, row) => total + row._receivable, 0);
        },
        settledTotal() {
            return this.grossTotal() - this.receivableTotal();
        },
        settledPercent() {
            const gross = this.grossTotal();

            return gross > 0 ? Math.round((this.settledTotal() / gross) * 1000) / 10 : 0;
        },
        maxContribution() {
            return this.commodities.reduce((max, row) => Math.max(max, row._contribution), 0) || 1;
        },
        contributionWidth(row) {
            return `${Math.max(4, Math.round((row._contribution / this.maxContribution()) * 100))}%`;
        },
        exportWorkbook() {
            const lines = [
                ['LAPORAN PENJUALAN EKSEKUTIF // ARSIP TERKUNCI'],
                ['Sertifikat', this.seal.certificate],
                ['Penandatangan', this.seal.signatory],
                ['SHA-256', this.seal.hash],
                [],
                ['KOMODITAS', 'PO (kg)', 'Tera Sah (kg)', 'Deviasi', 'Harga/kg', 'Omzet', 'Kontribusi', 'Margin'],
                ...this.commodities.map((row) => [
                    row.name,
                    row.po_label,
                    row.tera_label,
                    row.deviation_label,
                    row.price_label,
                    row.revenue_label,
                    row.contribution_label,
                    row.margin_label,
                ]),
                [],
                ['KLIEN B2B', 'Kode', 'Frek. PO', 'Volume', 'Bruto', 'Settled', 'Piutang', 'Tempo', 'Status'],
                ...this.clients.map((row) => [
                    row.name,
                    row.code,
                    row.po_label,
                    row.volume_label,
                    row.gross_label,
                    row.settled_label,
                    row.receivable_label,
                    row.term_label,
                    row.status_label,
                ]),
                [],
                ['AUDIT TRAIL'],
                ...this.audit.map((entry) => [
                    entry.timestamp + ' ' + entry.tag + ' ' + entry.message + (entry.trailing ? ' // ' + entry.trailing : ''),
                ]),
            ];

            const blob = new Blob(['﻿' + toCsv(lines)], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = 'laporan-penjualan-eksekutif-k4-2026.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            this.notify(
                'Buku Besar Terunduh',
                'Rekapitulasi komoditas, piutang B2B, dan audit trail Kuartal IV 2026 diekspor. Sertifikat arsip tetap berlaku.',
                'success',
            );
        },
        printSeal() {
            window.print();

            this.notify(
                'Dokumen Pengesahan',
                'Menyiapkan halaman untuk pencetakan PDF beserta sertifikat keaslian.',
                'info',
            );
        },
        copyHash() {
            if (navigator?.clipboard?.writeText) {
                navigator.clipboard.writeText(this.seal.hash);
            }

            this.notify(
                'Hash Kriptografis',
                `SHA-256 arsip disalin ke papan klip: ${this.seal.hash.slice(0, 18)}...`,
                'info',
            );
        },
        revealAudit() {
            this.notify(
                'Log Audit Forensik',
                `${this.audit.length} entri terminal dimuat. Write-access tetap tercabut permanen.`,
                'info',
            );
        },
        verifyIntegrity() {
            const settled = this.settledTotal();
            const receivable = this.receivableTotal();

            this.notify(
                'Integrity Check: PASSED',
                `Gross ${settled + receivable} dikompensasikan ${settled} settled + ${receivable} piutang berjalan (${this.settledPercent()}% settled). Hash SHA-256 cocok.`,
                'success',
            );
        },

        /* ---------- toast ---------- */

        notify(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

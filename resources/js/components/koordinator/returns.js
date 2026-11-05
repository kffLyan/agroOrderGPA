const numberFormat = (value, fractionDigits = 0) => new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
}).format(Number(value) || 0);

const csvCell = (value) => `"${String(value ?? '').replaceAll('"', '""')}"`;

export default function coordinatorReturns(batchRows = [], mutationRows = [], filters = {}) {
    return {
        batchRows: Array.isArray(batchRows) ? batchRows : [],
        mutationRows: Array.isArray(mutationRows) ? mutationRows : [],
        periods: filters.periods ?? [],
        hubs: filters.hubs ?? [],
        draftPeriod: filters.default_period ?? '',
        draftHub: filters.default_hub ?? 'all',
        appliedPeriod: filters.default_period ?? '',
        appliedHub: filters.default_hub ?? 'all',

        numberFormat(value, fractionDigits = 0) {
            return numberFormat(value, fractionDigits);
        },

        matchesPeriod(row) {
            if (this.appliedPeriod.startsWith('month-')) {
                return row.date.startsWith(this.appliedPeriod.slice(6));
            }

            if (this.appliedPeriod.startsWith('week-')) {
                return Number(row.week) === Number(this.appliedPeriod.slice(5));
            }

            if (this.appliedPeriod.startsWith('day-')) {
                return row.date === this.appliedPeriod.slice(4);
            }

            return true;
        },

        matchesHub(row) {
            return this.appliedHub === 'all' || row.hub === this.appliedHub;
        },

        matchesRow(row) {
            return this.matchesPeriod(row) && this.matchesHub(row);
        },

        visibleBatchRows() {
            return this.batchRows.filter((row) => this.matchesRow(row));
        },

        visibleMutationRows() {
            return this.mutationRows.filter((row) => this.matchesRow(row));
        },

        summary() {
            const rows = this.visibleBatchRows();
            const gross = rows.reduce((total, row) => total + Number(row.gross), 0);
            const net = rows.reduce((total, row) => total + Number(row.net), 0);
            const loss = rows.reduce((total, row) => total + Number(row.loss), 0);

            return {
                gross,
                net,
                loss,
                lossPercent: gross > 0 ? (loss / gross) * 100 : 0,
                mutationCount: this.visibleMutationRows().length,
                movement: this.visibleMutationRows().reduce((total, row) => total + Number(row.movement_kg), 0),
                returned: this.visibleMutationRows().reduce((total, row) => total + Number(row.return), 0),
            };
        },

        applyFilters() {
            this.appliedPeriod = this.draftPeriod;
            this.appliedHub = this.draftHub;
        },

        kg(value, fractionDigits = 0) {
            return `${numberFormat(value, fractionDigits)} kg`;
        },

        exportCsv() {
            const rows = [
                ['REKAP PASOKAN PANEN & TIMBANGAN'],
                ['Kode Batch', 'Tanggal / Jam', 'Sentra', 'Poktan / Petani', 'Komoditas', 'Bruto (kg)', 'Netto (kg)', 'Susut (kg)', 'Susut (%)', 'Status Tera', 'Petugas QC'],
                ...this.visibleBatchRows().map((row) => [
                    row.code, row.datetime, row.hub, row.farmer, row.commodity,
                    row.gross, row.net, row.loss, row.loss_percent, row.status, row.officer,
                ]),
                [],
                ['MUTASI BUFFER STOCK & RETUR'],
                ['ID Referensi', 'Sentra', 'Sumber / Klien', 'Komoditas', 'Masuk / Keluar', 'Retur / Selisih (kg)', 'Keterangan', 'Status'],
                ...this.visibleMutationRows().map((row) => [
                    row.id, row.hub, row.source, row.commodity, row.movement,
                    row.return, row.description, row.status,
                ]),
            ];
            const csv = `\uFEFF${rows.map((row) => row.map(csvCell).join(',')).join('\r\n')}`;
            const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
            const link = document.createElement('a');
            link.href = url;
            link.download = 'laporan-retur-koordinator.csv';
            link.click();
            URL.revokeObjectURL(url);
        },
    };
}

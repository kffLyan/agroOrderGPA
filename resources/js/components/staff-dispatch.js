const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryDispatch(rows = []) {
    const list = Array.isArray(rows) ? rows : [];

    return {
        rows: list,
        warehouse: 'Semua Gudang (SUB-04)',
        selectedId: list[0]?.id ?? null,
        released: [],

        get selected() {
            return this.rows.find((row) => row.id === this.selectedId) ?? this.rows[0] ?? null;
        },

        isSelected(id) {
            return this.selectedId === id;
        },

        isReady(row) {
            return row.state === 'ready';
        },

        isReleased(id) {
            return this.released.includes(id);
        },

        matchesFilter(row) {
            if (this.warehouse !== 'Semua Gudang (SUB-04)' && row.warehouse !== this.warehouse) {
                return false;
            }

            const needle = normalize(this.query).trim();

            if (! needle) {
                return true;
            }

            return [row.id, row.client, row.dock, row.sj, row.armada, row.driver]
                .map(normalize)
                .some((value) => value !== '' && value.includes(needle));
        },

        visibleRows() {
            return this.rows.filter((row) => this.matchesFilter(row));
        },

        visibleCount() {
            return this.visibleRows().length;
        },

        readyCount() {
            return this.visibleRows().filter((row) => this.isReady(row)).length;
        },

        pendingCount() {
            return this.visibleRows().filter((row) => ! this.isReady(row)).length;
        },

        issuedCount() {
            return this.released.length;
        },

        rowClass(row) {
            if (this.isReleased(row.id)) {
                return 'bg-accent/20 opacity-70';
            }

            if (this.isSelected(row.id)) {
                return 'bg-surface-muted';
            }

            return row.state === 'ready' ? '' : 'bg-surface-shell/60';
        },

        select(id) {
            if (this.isSelected(id)) {
                return;
            }

            this.selectedId = id;
        },

        handleWarehouse() {
            this.run(
                'Filter Gudang Diperbarui',
                `${this.visibleCount()} dokumen dalam antrean • ${this.readyCount()} siap rilis • ${this.pendingCount()} tertunda.`
            );
        },

        issue(row) {
            if (! this.isReady(row)) {
                this.run(
                    'Rilis Ditolak (Rule 05)',
                    `${row.id}: status timbangan belum SAH TERA GUDANG. Kunci estimasi tidak dapat diproses.`,
                    'danger'
                );

                return;
            }

            if (this.isReleased(row.id)) {
                this.run('Sudah Terbit', `${row.sj} sudah prostagland dan terkunci ke arsip penagihan.`, 'danger');

                return;
            }

            this.selectedId = row.id;
            this.released.push(row.id);

            this.run(
                'Surat Jalan Terbit',
                `${row.sj} untuk ${row.client} — netto sah ${row.netto} dikunci, QR armada terbit.`
            );
        },

        confirmRelease() {
            const row = this.selected;

            if (! row) {
                return;
            }

            this.issue(row);
        },

        saveDraft() {
            const row = this.selected;

            this.run(
                'Draft Disimpan',
                row ? `${row.sj} disimpan sebagai draf dan tidak mengubah status PO.` : 'Tidak ada dokumen aktif.'
            );
        },

        printProof() {
            const row = this.selected;

            this.run(
                'Cetak Bukti Fisik',
                row ? `Bukti timbangan ${row.sj} dikirim ke printer gudang ${row.warehouse}.` : 'Pilih dokumen terlebih dahulu.'
            );
        },
    };
}
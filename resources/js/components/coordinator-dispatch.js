const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(',', '.'));
    return Number.isFinite(parsed) ? parsed : 0;
};

const kgFormat = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

export default function coordinatorDispatch(queue = [], manifestRows = [], filters = [], baseDocs = 4) {
    return {
        filter: (Array.isArray(filters) ? filters : [])[0]?.key ?? 'all',
        filterOptions: Array.isArray(filters) ? filters : [],
        filterOpen: false,
        thermal: 4,
        baseDocs: numberOf(baseDocs),
        issued: {},
        queue: (Array.isArray(queue) ? queue : []).map((item) => ({
            ...item,
            net: numberOf(item.net),
            tara: numberOf(item.tara),
            gross: numberOf(item.gross),
        })),
        rows: (Array.isArray(manifestRows) ? manifestRows : []).map((row) => ({
            ...row,
            net: numberOf(row.net),
        })),

        get issuedCount() {
            return this.queue.filter((item) => this.issued[item.sj]).length;
        },

        get pendingCount() {
            return this.queue.length - this.issuedCount;
        },

        queueMeta() {
            return `${this.pendingCount()} ORDER MENUNGGU DOKUMEN CETAK`;
        },

        docCount() {
            return this.baseDocs + this.issuedCount;
        },

        manifestTonase() {
            return this.rows.reduce((total, row) => total + numberOf(row.net), 0);
        },

        tonase() {
            return this.manifestTonase() + this.queue
                .filter((item) => this.issued[item.sj])
                .reduce((total, item) => total + numberOf(item.net), 0);
        },

        kg(value) {
            return kgFormat.format(numberOf(value));
        },

        queueBySj(sj) {
            return this.queue.find((item) => item.sj === sj);
        },

        rowById(id) {
            return this.rows.find((row) => row.id === id);
        },

        isIssued(sj) {
            return Boolean(this.issued[sj]);
        },

        canIssue(sj) {
            const item = this.queueBySj(sj);

            if (!item) {
                return false;
            }

            const sealed = String(item.lock_stamp ?? '').trim().length > 0;
            const consistent = Math.abs(numberOf(item.net) - (numberOf(item.gross) - numberOf(item.tara))) < 0.001;

            return sealed && consistent && numberOf(item.net) > 0;
        },

        issue(sj) {
            const item = this.queueBySj(sj);

            if (!item) {
                this.run('Dokumen Tidak Ditemukan', 'Surat Jalan tidak ditemukan dalam antrean penerbitan hari ini.', 'danger');

                return;
            }

            if (this.issued[sj]) {
                this.run('Surat Jalan Sudah Terbit', `${sj} telah terbit dan masuk antrean cetak thermal.`, 'warning');

                return;
            }

            if (!this.canIssue(sj)) {
                this.run('Penerbitan Ditolak · Lockout 904', `Segel timbangan ${sj} tidak sah. Netto harus sama dengan gross dikurangi tera sebelum gate dibuka.`, 'danger');

                return;
            }

            this.issued[sj] = true;
            this.thermal += 1;

            this.run(
                'Surat Jalan Terbit & Dicetak',
                `${sj} terbit untuk ${item.po} · ${this.kg(item.net)} KG netto sah · 3 rangkap + QR valid masuk antrean thermal.`,
                'success',
            );
        },

        issueAll() {
            const pending = this.queue.filter((item) => !this.issued[item.sj]);
            const allowed = pending.filter((item) => this.canIssue(item.sj));
            const blocked = pending.filter((item) => !this.canIssue(item.sj));

            allowed.forEach((item) => {
                this.issued[item.sj] = true;
                this.thermal += 1;
            });

            if (!allowed.length) {
                this.run('Batch Ditolak · Lockout 904', 'Tidak ada dokumen dengan segel timbangan sah. Gate keluar gudang tetap terkunci.', 'danger');

                return;
            }

            this.run(
                'Batch Surat Jalan Tercetak',
                `${allowed.length} dokumen terbit, ${this.kg(allowed.reduce((total, item) => total + numberOf(item.net), 0))} KG netto sah, ${this.thermal} antrean thermal.`,
                'success',
            );

            if (blocked.length) {
                this.run('Dokumen Ditahan', `${blocked.length} dokumen ditahan karena segel tera tidak sah.`, 'danger');
            }
        },

        setFilter(key) {
            this.filter = key;
            this.filterOpen = false;

            this.run('Filter Manifest Diperbarui', `${this.visibleRows().length} dari ${this.rows.length} dokumen tampil pada filter ${this.filterLabel(key)}.`);
        },

        visibleRows() {
            if (this.filter === 'all') {
                return this.rows;
            }

            return this.rows.filter((row) => row.dock_state === this.filter);
        },

        filterCounts() {
            return {
                all: this.rows.length,
                standby: this.rows.filter((row) => row.dock_state === 'standby').length,
                moving: this.rows.filter((row) => row.dock_state === 'moving').length,
            };
        },

        filterLabel(key = this.filter) {
            const option = this.filterOptions.find((item) => item.key === key) ?? this.filterOptions[0];
            const label = String(option?.label ?? key);

            return label.replace(/\(\d+\)$/, `(${this.filterCounts()[key] ?? 0})`);
        },

        isVisible(id) {
            return this.visibleRows().some((row) => row.id === id);
        },

        state(id) {
            return this.rowById(id)?.dock_state ?? 'standby';
        },

        dockText(id) {
            return this.rowById(id)?.dock ?? '';
        },

        actionOf(id) {
            return this.rowById(id)?.action ?? 'track';
        },

        actionLabel(id) {
            return this.rowById(id)?.action_label ?? '';
        },

        actionVariant(id) {
            const action = this.actionOf(id);

            if (action === 'handover') {
                return 'bg-ink text-white outline outline-1 outline-brand-line shadow-sub hover:bg-ink-muted';
            }

            if (action === 'reprint') {
                return 'bg-surface-shell text-ink outline outline-1 outline-line-board/60 shadow-sub hover:bg-surface-muted';
            }

            return 'bg-surface text-ink outline outline-1 outline-line-board/60 shadow-sub hover:bg-surface-muted';
        },

        actionIconTone(id) {
            const action = this.actionOf(id);

            if (action === 'handover') {
                return 'text-accent';
            }

            return action === 'track' ? 'text-success-deep' : 'text-ink';
        },

        actRow(id) {
            const row = this.rowById(id);

            if (!row) {
                return;
            }

            if (row.action === 'handover') {
                const dockBefore = row.dock;

                row.action = 'track';
                row.action_label = 'Lacak GPS Supir';
                row.dock_state = 'moving';
                row.dock = 'MENUJU LOKASI';

                this.run(
                    'Tugas Diteruskan ke Supir',
                    `${row.driver} (${row.plate}) menerima ${this.kg(row.net)} KG. Gate ${dockBefore} dilepas pukul ${row.time}.`,
                    'success',
                );

                return;
            }

            if (row.action === 'reprint') {
                this.run('Rangkap Dicetak Ulang', `3 rangkap ${row.id} dicetak ulang dan barcode diverifikasi ulang oleh driver ${row.driver}.`);

                return;
            }

            this.run('Pelacakan GPS Aktif', `${row.driver} · ${row.plate} · ${row.vehicle} — ${row.slot}, ${this.kg(row.net)} KG menuju lokasi penerima.`);
        },

        act(key) {
            if (key === 'log') {
                this.run('Log Dispatch Terbit', `${this.docCount()} dokumen sah · ${this.kg(this.tonase())} KG netto sah · ${this.pendingCount()} antrean cetak menunggu.`);

                return;
            }

            if (key === 'thermal') {
                this.run('Antrean Cetak Thermal', `${this.thermal} dokumen menunggu cetak thermal · Zebra Driver Link online 203 DPI.`);
            }
        },

        batch(key) {
            if (key === 'export') {
                this.run('Manifest Harian Diekspor', `${this.rows.length} baris manifest · ${this.kg(this.tonase())} KG netto sah · 3 rangkap + QR per dokumen (.CSV).`);

                return;
            }

            if (key === 'audit-gate') {
                this.run('Audit Gate Keluar Gudang', `${this.docCount()} dokumen lolos barcode verification. Gate dikunci dengan audit token SEC-GATE-${this.thermal}.`, 'warning');

                return;
            }

            if (key === 'print-all') {
                this.issueAll();
            }
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

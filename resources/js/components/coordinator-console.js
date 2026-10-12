const normalize = (value) => String(value ?? '').toLowerCase();

const formatNumber = (value) => Number(value ?? 0).toLocaleString('id-ID');

export default function coordinatorConsole(rows = [], orders = [], logs = []) {
    return {
        sentra: null,
        syncing: false,
        locked: [],
        rows,
        orders,
        logs,

        init() {
            this.$root.addEventListener('gpa:sync-scale', () => this.syncScale());
        },

        act(key) {
            if (key === 'export') {
                this.run(
                    'Export Rekap (CSV)',
                    'Rekap pasokan 5 komoditas inti sedang disiapkan untuk diunduh.',
                    'success',
                );

                return;
            }

            if (key === 'filter') {
                this.cycleSentra();

                return;
            }

            if (key === 'ticket') {
                this.run(
                    'Input Tiket Baru',
                    'Formulir tiket timbangan Gate-01 dibuka pada modul intake.',
                    'info',
                );

                return;
            }

            this.run('Aksi Belum Tersedia', `Aksi "${key}" sedang disiapkan.`, 'info');
        },

        syncScale() {
            if (this.syncing) {
                return;
            }

            this.syncing = true;

            window.setTimeout(() => {
                this.syncing = false;
                this.run(
                    'Sync Timbangan Selesai',
                    '3 tiket Gate-01 tersinkron. Deviasi susut -1.18% masih dalam toleransi SOP.',
                    'success',
                );
            }, 900);
        },

        kg(value) {
            return formatNumber(value);
        },

        rowTotal(row) {
            return Number(row.supply ?? 0) + Number(row.buffer ?? 0);
        },

        allocation(row) {
            const order = Number(row.order ?? 0);

            if (order <= 0) {
                return 0;
            }

            return Math.min(100, Math.round((Number(row.supply ?? 0) / order) * 100));
        },

        gapLabel(row) {
            const gap = this.rowTotal(row) - Number(row.order ?? 0);

            return `${gap >= 0 ? '+' : '-'}${formatNumber(Math.abs(gap))} KG`;
        },

        isLocked(key) {
            return this.locked.includes(key);
        },

        toggleLock(key) {
            const row = this.rows.find((entry) => entry.key === key);
            const name = row?.name ?? 'Komoditas';

            if (this.isLocked(key)) {
                this.locked = this.locked.filter((entry) => entry !== key);
                this.run('Stok Dilepas', `${name} kembali ke alokasi bebas.`);

                return;
            }

            this.locked.push(key);
            this.run('Stok Terkunci', `${name} dikunci untuk Immediate Order.`, 'success');
        },

        matches(needle, values) {
            if (!needle) {
                return true;
            }

            return values.map(normalize).some((value) => value.includes(needle));
        },

        needle() {
            return normalize(this.query).trim();
        },

        sentraOptions() {
            const options = [];

            this.rows.forEach((row) => {
                [row.group, row.partner].forEach((value) => {
                    if (value && !options.includes(value)) {
                        options.push(value);
                    }
                });
            });

            return options;
        },

        isSentraVisible(row) {
            if (!this.sentra) {
                return true;
            }

            return [row.group, row.partner].map(normalize).includes(normalize(this.sentra));
        },

        visibleRows() {
            const needle = this.needle();

            return this.rows.filter(
                (row) =>
                    this.isSentraVisible(row) &&
                    this.matches(needle, [row.name, row.batch, row.grade, row.group, row.partner, row.zone]),
            );
        },

        visibleOrders() {
            const needle = this.needle();

            return this.orders.filter((order) =>
                this.matches(needle, [
                    order.po,
                    order.client,
                    order.allocation,
                    order.bay,
                    order.status,
                    order.dispatch,
                ]),
            );
        },

        visibleLogs() {
            const needle = this.needle();

            return this.logs.filter((log) =>
                this.matches(needle, [log.ticket, log.name, log.commodity, log.qc, log.note]),
            );
        },

        cycleSentra() {
            const options = this.sentraOptions();
            const current = options.indexOf(this.sentra);
            const next = current + 1 >= options.length ? -1 : current + 1;

            this.sentra = next === -1 ? null : options[next];

            this.run(
                'Filter Sentra',
                this.sentra
                    ? `Tabel dibatasi pada sentra "${this.sentra}".`
                    : 'Filter sentra dihapus, seluruh kolom ditampilkan kembali.',
            );
        },

        printSlip(log) {
            this.run(
                `Slip ${log.ticket}`,
                `Slip timbangan ${log.name} (${formatNumber(log.net)} KG) dicetak ke printer Gate-01.`,
                'success',
            );
        },

        openColdHub() {
            this.run(
                'Cold-Storage Hub',
                'Pembukaan kontrol cold-storage hub dijadwalkan setelah staging 3 batch selesai.',
                'info',
            );
        },
    };
}
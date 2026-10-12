const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(',', '.'));
    return Number.isFinite(parsed) ? parsed : 0;
};

const idNumber = new Intl.NumberFormat('id-ID');

export default function coordinatorStock(commodities = [], filterOptions = [], intakeFields = [], logItems = [], logTotal = 0) {
    return {
        filter: filterOptions[0]?.key ?? 'all',
        filterOptions: Array.isArray(filterOptions) ? filterOptions : [],
        lockedAll: false,
        allLogs: false,
        logTotal: numberOf(logTotal),
        baseLogCount: Array.isArray(logItems) ? logItems.length : 0,
        pendingLogs: [],
        commodities: (Array.isArray(commodities) ? commodities : []).map((commodity) => {
            const tiles = Array.isArray(commodity.tiles) ? commodity.tiles : [];

            return {
                ...commodity,
                builtian: numberOf(tiles[0]?.value),
                buffer: numberOf(tiles[1]?.value),
                po: numberOf(tiles[2]?.value),
                bufferRequested: false,
                locked: false,
            };
        }),
        form: (Array.isArray(intakeFields) ? intakeFields : []).reduce((accumulator, field) => {
            accumulator[field.key] = field.type === 'number' ? numberOf(field.value) : field.value;

            return accumulator;
        }, {}),

        get visible() {
            if (this.filter === 'all') {
                return this.commodities.map((commodity) => commodity.key);
            }

            return this.commodities.filter((commodity) => commodity.category === this.filter).map((commodity) => commodity.key);
        },

        get visibleCount() {
            return this.visible.length;
        },

        kg(value) {
            return idNumber.format(Math.round(numberOf(value)));
        },

        percent(value, digits = 1) {
            return `${numberOf(value).toFixed(digits)}%`;
        },

        pct(value) {
            const amount = numberOf(value);
            return `${Number.isInteger(amount) ? amount : amount.toFixed(1)}%`;
        },

        signed(value) {
            return `+${this.kg(value)}`;
        },

        rtp(commodity) {
            return Math.max(0, numberOf(commodity.total) - numberOf(commodity.po));
        },

        utilization(commodity) {
            const total = numberOf(commodity.total);

            return total > 0 ? (numberOf(commodity.po) / total) * 100 : 0;
        },

        totalStock() {
            return this.commodities.reduce((total, commodity) => total + numberOf(commodity.total), 0);
        },

        builtianStock() {
            return this.commodities.reduce((total, commodity) => total + numberOf(commodity.builtian), 0);
        },

        bufferStock() {
            return this.commodities.reduce((total, commodity) => total + numberOf(commodity.buffer), 0);
        },

        reserved() {
            return this.commodities.reduce((total, commodity) => total + numberOf(commodity.po), 0);
        },

        freeStock() {
            return Math.max(0, this.totalStock() - this.reserved());
        },

        builtianPercent() {
            const total = this.totalStock();

            return total > 0 ? (this.builtianStock() / total) * 100 : 0;
        },

        shortName(name) {
            return String(name ?? '').split(' ').slice(0, 2).join(' ');
        },

        lotPrefix(commodity) {
            return String(commodity?.code ?? 'CMD-XXX-000')
                .split('-')[1] ?? 'XXX';
        },

        lotNumber(commodity, date, isBuffer) {
            const compact = String(date ?? '').replaceAll('-', '').slice(0, 8) || '00000000';
            const prefix = isBuffer ? 'BUF' : 'LOT';
            const sequence = this.pendingLogs.length + (isBuffer ? 5 : 10);

            return `${prefix}-${this.lotPrefix(commodity)}-${compact}-${String(sequence).padStart(2, '0')}`;
        },

        clockStamp() {
            const now = new Date();

            return `${[now.getHours(), now.getMinutes(), now.getSeconds()].map((part) => String(part).padStart(2, '0')).join(':')} WIB`;
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },

        act(key) {
            if (key === 'export') {
                this.run(
                    'Ledger Stok Diekspor',
                    `${this.visibleCount} komoditas, ${this.kg(this.totalStock())} KG, alokasi PO ${this.kg(this.reserved())} KG diunduh sebagai .CSV.`,
                );

                return;
            }

            if (key === 'lock-all') {
                this.lockAllStock();
            }
        },

        lockAllStock() {
            if (this.lockedAll) {
                this.run('Alokasi Sudah Terkunci', 'Seluruh alokasi pasokan sedang dalam status terkunci.', 'warning');

                return;
            }

            this.lockedAll = true;
            this.commodities.forEach((commodity) => {
                commodity.locked = true;
            });

            this.run(
                'Alokasi Pasokan Terkunci',
                `${this.commodities.length} komoditas terkunci. Request buffer hanya via approval DQ.`,
                'success',
            );
        },

        requestBuffer(commodity) {
            if (this.lockedAll || commodity.locked) {
                this.run('Stok Terkunci', 'Buka kunci alokasi sebelum mengajukan buffer.', 'warning');

                return;
            }

            if (commodity.buffer_need > 0) {
                commodity.bufferRequested = true;

                this.run(
                    'Injeksi Buffer Diproses',
                    `Mitra ${commodity.hub} diminta injeksi ${this.kg(commodity.buffer_need)} KG ${commodity.name}.`,
                    'success',
                );

                return;
            }

            this.run(
                'Permintaan Buffer Terkirim',
                `Permintaan buffer ${commodity.name} dikirim ke mitra penyangga. RTP ${this.signed(this.rtp(commodity))} KG masih aman.`,
            );
        },

        lockStock(commodity) {
            if (commodity.locked || this.lockedAll) {
                this.run('Stok Sudah Terkunci', `Alokasi ${commodity.name} tidak dapat dikunci ulang.`, 'warning');

                return;
            }

            commodity.locked = true;

            this.run(
                'Stok Terkunci',
                `${commodity.name} dikunci untuk mencegah overselling di luar alokasi PO.`,
                'success',
            );
        },

        submitIntake() {
            const quantity = Math.round(numberOf(this.form.quantity));

            if (quantity <= 0) {
                this.run('Kuantitas Tidak Valid', 'Isi kuantitas netto minimal 1 KG sebelum menyimpan.', 'error');

                return;
            }

            const commodity = this.commodities.find((item) => item.key === this.form.commodity);

            if (!commodity) {
                this.run('Komoditas Tidak Ditemukan', 'Pilih komoditas inti yang tersedia pada filter.', 'error');

                return;
            }

            const isBuffer = this.form.category === 'Injeksi Buffer Mitra';

            if (isBuffer) {
                commodity.buffer += quantity;
            } else {
                commodity.builtian += quantity;
            }

            commodity.total += quantity;

            const channel = isBuffer ? 'Buffer Mitra' : 'Binaan';
            const vendor = String(this.form.vendor ?? '').replace(/^(Pak|Bu)\s+/, '');

            this.pendingLogs.unshift({
                lot: this.lotNumber(commodity, this.form.date, isBuffer),
                time: this.clockStamp(),
                entry: `+${this.kg(quantity)} KG ${this.shortName(commodity.name)} (${channel} - ${vendor})`,
                note: String(this.form.notes ?? '').trim() || 'QC Pass: Mutu Terverifikasi',
                audit: 'AUDIT: SEC-OP-882',
            });

            this.logTotal += 1;
            this.form.quantity = 0;

            this.run(
                'Data Pasokan Tersimpan',
                `${this.kg(quantity)} KG ${commodity.name} masuk ke stok. RTP kini ${this.signed(this.rtp(commodity))} KG.`,
                'success',
            );
        },

        showAllLogs() {
            this.allLogs = true;

            this.run(
                'Log Intake Terbaru',
                `${this.baseLogCount + this.pendingLogs.length} baris log ditampilkan pada feed hari ini.`,
            );
        },
    };
}
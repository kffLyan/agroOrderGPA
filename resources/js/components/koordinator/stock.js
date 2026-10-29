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
        bufferModal: false,
        activeBufferCommodity: null,
        bufferAmount: 250,
        bufferSupplier: 'Mitra Sentra Lembang',
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

        init() {
            try {
                const storedLockedAll = localStorage.getItem('gpa_stock_locked_all');
                if (storedLockedAll === '1') {
                    this.lockedAll = true;
                    this.commodities.forEach(c => { c.locked = true; });
                }

                const storedLockedItems = localStorage.getItem('gpa_stock_locked_items');
                if (storedLockedItems) {
                    const lockedKeys = JSON.parse(storedLockedItems);
                    this.commodities.forEach(c => {
                        if (lockedKeys.includes(c.key)) c.locked = true;
                    });
                }
            } catch (e) {}
        },

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
                this.exportCsv();
                return;
            }

            if (key === 'lock-all') {
                this.toggleLockAll();
            }
        },

        exportCsv() {
            const visibleCommodities = this.commodities.filter((c) => this.visible.includes(c.key));
            const headers = ['Kode Komoditas', 'Nama Komoditas', 'Kategori', 'Total Stok (KG)', 'Mitra Binaan (KG)', 'Buffer Cadangan (KG)', 'Alokasi PO (KG)', 'RTP Bebas (KG)', 'Status Alokasi'];
            const rows = visibleCommodities.map((c) => [
                c.code || c.key,
                `"${c.name}"`,
                c.category || '-',
                c.total || 0,
                c.builtian || 0,
                c.buffer || 0,
                c.po || 0,
                this.rtp(c),
                c.locked ? 'TERKUNCI' : 'AKTIF',
            ]);

            const csvContent = '\uFEFF' + [headers.join(';'), ...rows.map((e) => e.join(';'))].join('\r\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', `Ledger_Stok_GPA_${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            this.run('Ledger Stok Berhasil Diunduh', `${visibleCommodities.length} komoditas diekspor ke file CSV resmi.`, 'success');
        },

        toggleLockAll() {
            this.lockedAll = !this.lockedAll;
            this.commodities.forEach((commodity) => {
                commodity.locked = this.lockedAll;
            });

            try {
                localStorage.setItem('gpa_stock_locked_all', this.lockedAll ? '1' : '0');
                if (this.lockedAll) {
                    localStorage.setItem('gpa_stock_locked_items', JSON.stringify(this.commodities.map(c => c.key)));
                } else {
                    localStorage.removeItem('gpa_stock_locked_items');
                }
            } catch (e) {}

            this.run(
                this.lockedAll ? 'Alokasi Pasokan Terkunci' : 'Kunci Alokasi Dibuka',
                this.lockedAll
                    ? `${this.commodities.length} komoditas dikunci untuk mencegah perubahan alokasi mendadak.`
                    : 'Seluruh komoditas kembali ke status alokasi bebas.',
                this.lockedAll ? 'warning' : 'info',
            );
        },

        requestBuffer(commodity) {
            this.activeBufferCommodity = commodity;
            this.bufferAmount = commodity.buffer_need > 0 ? commodity.buffer_need : 200;
            this.bufferSupplier = commodity.hub || 'Sentra Mitra Penyangga';
            this.bufferModal = true;
        },

        closeBufferModal() {
            this.bufferModal = false;
            this.activeBufferCommodity = null;
        },

        confirmBufferInjection() {
            const commodity = this.activeBufferCommodity;
            if (!commodity) return;

            const amount = numberOf(this.bufferAmount);
            if (amount <= 0) {
                this.run('Kuantitas Tidak Valid', 'Masukkan kuantitas buffer minimal 1 KG.', 'danger');
                return;
            }

            commodity.buffer += amount;
            commodity.total += amount;
            commodity.bufferRequested = true;

            const lot = this.lotNumber(commodity, new Date().toISOString().slice(0, 10), true);
            this.pendingLogs.unshift({
                lot,
                time: this.clockStamp(),
                entry: `+${this.kg(amount)} KG ${this.shortName(commodity.name)} (Buffer - ${this.bufferSupplier})`,
                note: 'Injeksi Buffer Disetujui Koordinator',
                audit: 'AUDIT: INJ-BUF-' + Math.floor(100 + Math.random() * 900),
            });

            this.logTotal += 1;

            // Simpan ke database via AJAX
            fetch('/koordinator/stok/batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    commodity: commodity.key,
                    vendor: this.bufferSupplier,
                    grade: 'Grade A Standar',
                    category: 'Injeksi Buffer Mitra',
                    quantity: amount,
                    date: new Date().toISOString().slice(0, 10),
                    notes: 'Injeksi Buffer Penyangga Packhouse',
                }),
            }).catch(e => console.error('Gagal mencatat buffer:', e));

            this.run(
                'Injeksi Buffer Berhasil',
                `${this.kg(amount)} KG ${commodity.name} dari ${this.bufferSupplier} ditambahkan ke buffer cadangan.`,
                'success',
            );

            this.closeBufferModal();
        },

        lockStock(commodity) {
            commodity.locked = !commodity.locked;

            try {
                const lockedKeys = this.commodities.filter(c => c.locked).map(c => c.key);
                localStorage.setItem('gpa_stock_locked_items', JSON.stringify(lockedKeys));
            } catch (e) {}

            this.run(
                commodity.locked ? 'Stok Terkunci' : 'Stok Dibuka',
                commodity.locked
                    ? `${commodity.name} dikunci untuk mencegah overselling di luar alokasi PO.`
                    : `${commodity.name} kembali ke alokasi bebas.`,
                commodity.locked ? 'success' : 'info',
            );
        },

        submitIntake() {
            const quantity = Math.round(numberOf(this.form.quantity));

            if (quantity <= 0) {
                this.run('Kuantitas Tidak Valid', 'Isi kuantitas netto minimal 1 KG sebelum menyimpan.', 'danger');

                return;
            }

            const commodity = this.commodities.find((item) => item.key === this.form.commodity);

            if (!commodity) {
                this.run('Komoditas Tidak Ditemukan', 'Pilih komoditas inti yang tersedia pada filter.', 'danger');

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
            const savedQuantity = quantity;
            this.form.quantity = 0;

            // Simpan ke database via AJAX
            fetch('/koordinator/stok/batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    commodity: this.form.commodity,
                    vendor: this.form.vendor,
                    grade: this.form.grade,
                    category: this.form.category,
                    quantity: savedQuantity,
                    date: this.form.date,
                    notes: this.form.notes,
                }),
            }).catch(e => console.error('Gagal mencatat batch panen:', e));

            this.run(
                'Data Pasokan Tersimpan',
                `${this.kg(savedQuantity)} KG ${commodity.name} masuk ke stok. RTP kini ${this.signed(this.rtp(commodity))} KG.`,
                'success',
            );
        },

        showAllLogs() {
            this.allLogs = true;

            this.run(
                'Seluruh Log Intake Tampil',
                `${this.baseLogCount + this.pendingLogs.length} baris log pasokan dimuat secara lengkap.`,
                'info'
            );
        },
    };
}
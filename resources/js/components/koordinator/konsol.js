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
        slipModal: false,
        activeSlip: null,

        init() {
            this.$root.addEventListener('gpa:sync-scale', () => this.syncScale());

            try {
                const storedLocked = localStorage.getItem('gpa_coordinator_locked_stocks');
                if (storedLocked) {
                    this.locked = JSON.parse(storedLocked);
                }
            } catch (e) {
                this.locked = [];
            }
        },

        act(key) {
            if (key === 'export') {
                window.location.href = '/koordinator/stok';
                return;
            }

            if (key === 'filter') {
                this.cycleSentra();
                return;
            }

            if (key === 'ticket') {
                window.location.href = '/koordinator/penimbangan';
                return;
            }

            if (key === 'refresh') {
                this.refreshData();
                return;
            }

            window.location.reload();
        },

        refreshData() {
            this.syncing = true;
            this.run('Menyegarkan Data', 'Memperbarui data pasokan dan antrean penerimaan terbaru...', 'info');
            setTimeout(() => {
                this.syncing = false;
                this.run('Data Diperbarui', 'Data kesiapan pasokan dan penerimaan timbangan berhasil disinkronkan.', 'success');
            }, 600);
        },

        syncScale() {
            this.syncing = true;
            this.run('Sinkronisasi Timbangan', 'Mengkalibrasi sensor timbangan penerimaan panen...', 'info');
            setTimeout(() => {
                this.syncing = false;
                const now = new Date();
                const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} WIB`;
                if (this.logs && this.logs.length > 0) {
                    this.logs[0].time = timeStr;
                }
                this.run('Sensor Timbangan Terkalibrasi', `Timbangan utama sinkron pada ${timeStr}. Penimbangan akurat dan siap beroperasi.`, 'success');
            }, 700);
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
                this.saveLocked();
                this.run('Stok Dilepas', `${name} kembali ke alokasi bebas operasional.`, 'info');
                return;
            }

            this.locked.push(key);
            this.saveLocked();
            this.run('Stok Terkunci', `${name} resmi dikunci untuk alokasi prioritas cold storage.`, 'success');
        },

        saveLocked() {
            try {
                localStorage.setItem('gpa_coordinator_locked_stocks', JSON.stringify(this.locked));
            } catch (e) {}
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
                'info'
            );
        },

        resetSentra() {
            this.sentra = null;
            this.run('Filter Sentra Direset', 'Seluruh sentra mitra binaan kembali ditampilkan.', 'info');
        },

        printSlip(log) {
            this.activeSlip = log;
            this.slipModal = true;
        },

        closeSlip() {
            this.slipModal = false;
            this.activeSlip = null;
        },

        executeSlipPrint() {
            const printContent = document.getElementById('gpa-slip-printable');
            if (!printContent) {
                window.print();
                return;
            }

            const win = window.open('', '_blank', 'width=650,height=800');
            win.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Slip Timbang Pos Utama - ${this.activeSlip?.ticket || 'Tiket'}</title>
                    <style>
                        body { font-family: monospace, sans-serif; padding: 24px; color: #111; margin: 0; }
                        .ticket-box { border: 2px dashed #333; padding: 20px; max-width: 450px; margin: auto; }
                        .header { text-align: center; border-bottom: 1px solid #333; padding-bottom: 12px; margin-bottom: 12px; }
                        .title { font-weight: bold; font-size: 16px; margin: 0; }
                        .subtitle { font-size: 11px; margin-top: 4px; color: #555; }
                        .row { display: flex; justify-content: space-between; margin: 6px 0; font-size: 13px; }
                        .bold { font-weight: bold; }
                        .divider { border-top: 1px dashed #777; margin: 12px 0; }
                        .netto-row { background: #eee; padding: 8px; border-radius: 4px; margin-top: 10px; font-size: 15px; font-weight: bold; }
                        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class="ticket-box">
                        <div class="header">
                            <h2 class="title">KOPERASI TANI GREEN PASUNDAN AGRICULTURE</h2>
                            <div class="subtitle">Stasiun Penimbangan Resmi - Pos Timbangan Utama GPA</div>
                            <div class="subtitle">Ciwidey, Kab. Bandung - Jawa Barat</div>
                        </div>
                        <div class="row"><span>No. Tiket:</span><span class="bold">${this.activeSlip?.ticket || '-'}</span></div>
                        <div class="row"><span>Waktu Masuk:</span><span>${this.activeSlip?.time || 'Hari ini'}</span></div>
                        <div class="row"><span>Mitra / Pemasok:</span><span class="bold">${this.activeSlip?.name || '-'}</span></div>
                        <div class="row"><span>Komoditas:</span><span>${this.activeSlip?.commodity || '-'}</span></div>
                        <div class="row"><span>Kualifikasi QC:</span><span class="bold">${this.activeSlip?.qc || '-'}</span></div>
                        <div class="divider"></div>
                        <div class="row"><span>Berat Kotor (Bruto):</span><span>${this.kg(this.activeSlip?.gross)} KG</span></div>
                        <div class="row"><span>Berat Wadah (Tara):</span><span>${this.kg(this.activeSlip?.tare)} KG</span></div>
                        <div class="row netto-row"><span>BERAT BERSIH (NETTO):</span><span>${this.kg(this.activeSlip?.net)} KG</span></div>
                        <div class="divider"></div>
                        <div class="row"><span>Catatan:</span><span>${this.activeSlip?.note || 'Lolos Verifikasi Timbangan Resmi'}</span></div>
                        <div class="row" style="margin-top: 20px;">
                            <div style="text-align: center; width: 50%;">
                                <div style="font-size: 11px;">Pemasok / Driver</div>
                                <div style="margin-top: 35px; font-size: 12px;">( .................... )</div>
                            </div>
                            <div style="text-align: center; width: 50%;">
                                <div style="font-size: 11px;">Petugas Timbang</div>
                                <div style="margin-top: 35px; font-size: 12px;">( Koordinator )</div>
                            </div>
                        </div>
                        <div class="footer">
                            Bukti Penimbangan Resmi Koperasi Tani Green Pasundan Agriculture.<br>
                            Dicetak otomatis melalui Konsol Koordinator.
                        </div>
                    </div>
                    <script>
                        window.onload = function() { window.print(); };
                    </script>
                </body>
                </html>
            `);
            win.document.close();
            this.run('Slip Dicetak', `Slip timbang ${this.activeSlip?.ticket} dikirim ke pencetakan.`, 'success');
        },

        openColdHub() {
            window.location.href = '/koordinator/stok';
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}
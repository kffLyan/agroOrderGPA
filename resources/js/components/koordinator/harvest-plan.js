const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(',', '.'));
    return Number.isFinite(parsed) ? parsed : 0;
};

const kgFormat = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const stageToneClasses = {
    pill: 'bg-surface-pill text-ink-body outline-line-board/40',
    accent: 'bg-accent text-ink outline-success-deep',
    'accent-soft': 'bg-accent/50 text-success-deep outline-success/30',
};

const stageDotClasses = {
    pill: 'bg-ink-quiet',
    accent: 'bg-success-deep',
    'accent-soft': 'bg-success-deep',
};

export default function coordinatorHarvestPlan(rows = [], stages = {}, station = {}, qc = {}, meta = {}) {
    return {
        stages: stages && typeof stages === 'object' ? stages : {},
        station: station && typeof station === 'object' ? station : {},
        qc: qc && typeof qc === 'object' ? qc : {},
        meta: meta && typeof meta === 'object' ? meta : {},
        selected: '',
        labelModal: false,
        afkirModal: false,
        afkirReason: 'Daun luar layu / kelebihan kadar air',
        afkirPercent: 2.4,
        sampleModal: false,
        selectedSample: null,
        transferModal: false,
        rows: (Array.isArray(rows) ? rows : []).map((row) => ({
            ...row,
            stage: Math.min(4, Math.max(1, Math.round(numberOf(row.stage) || 1))),
            krat_done: numberOf(row.krat_done),
            krat_total: numberOf(row.krat_total),
            est_kg: numberOf(row.est_kg),
            afkir: row.afkir ?? null,
            afkir_flagged: false,
        })),

        init() {
            this.selected = this.rows[0]?.po ?? '';

            try {
                const storedTransfers = localStorage.getItem('gpa_harvest_transfers');
                if (storedTransfers) {
                    const transferredPos = JSON.parse(storedTransfers);
                    this.rows.forEach(r => {
                        if (transferredPos.includes(r.po)) r.stage = 4;
                    });
                }

                const storedAfkir = localStorage.getItem('gpa_harvest_afkir');
                if (storedAfkir) {
                    const afkirMap = JSON.parse(storedAfkir);
                    this.rows.forEach(r => {
                        if (afkirMap[r.po]) {
                            r.afkir_flagged = true;
                            r.afkir = afkirMap[r.po];
                        }
                    });
                }
            } catch (e) {}
        },

        /* ---------- queue ---------- */

        rowByPo(po) {
            return this.rows.find((row) => row.po === po) ?? null;
        },

        selectedRow() {
            return this.rowByPo(this.selected) ?? this.rows[0] ?? null;
        },

        isActive(po) {
            return po === this.selected;
        },

        stageOf(po) {
            return this.rowByPo(po)?.stage ?? 1;
        },

        stageLabel(po) {
            return this.stages[this.stageOf(po)]?.chip ?? 'TAHAP 1: TRIMMING';
        },

        stageTone(po) {
            return stageToneClasses[this.stages[this.stageOf(po)]?.tone ?? 'pill'] ?? stageToneClasses.pill;
        },

        stageDot(po) {
            return stageDotClasses[this.stages[this.stageOf(po)]?.tone ?? 'pill'] ?? stageDotClasses.pill;
        },

        commodityLabel(po) {
            const row = this.rowByPo(po);
            if (!row) {
                return '';
            }

            return [row.commodity, row.commodity_note].filter(Boolean).join(' ');
        },

        afkirOf(po) {
            return this.rowByPo(po)?.afkir ?? null;
        },

        afkirTone(po) {
            const row = this.rowByPo(po);
            if (!row?.afkir) {
                return 'text-warning';
            }

            return row.afkir_flagged ? 'text-danger' : 'text-warning';
        },

        actionLabel(po) {
            return this.isActive(po) ? 'Inspeksi' : 'Pilih';
        },

        actionVariant(po) {
            return this.isActive(po)
                ? 'bg-brand text-accent shadow-sub outline outline-1 outline-brand-line hover:bg-brand-hover'
                : 'bg-surface text-ink-body outline outline-1 outline-line-board/60 hover:bg-surface-muted hover:text-ink';
        },

        actionIconTone(po) {
            return this.isActive(po) ? 'text-accent' : 'text-ink-body';
        },

        select(po) {
            const row = this.rowByPo(po);

            if (!row) {
                this.run('Batch Tidak Ditemukan', 'Batch packing tidak ditemukan dalam antrean hari ini.', 'danger');
                return;
            }

            this.selected = po;
            this.run(
                'Workstation Diprioritaskan',
                `${row.buyer} • ${row.bay} • ${this.stageLabel(po)} • ${this.kg(row.est_kg)} KG estimasi packing.`,
                'info'
            );
        },

        refreshQueue() {
            this.run('Antrean Packing Disegarkan', `${this.rows.length} dari ${this.meta.totalBatches} batch operasional tampil pada staging zone aktif.`, 'success');
        },

        /* ---------- active workstation ---------- */

        buyer() {
            return this.selectedRow()?.buyer ?? '';
        },

        poLabel() {
            const row = this.selectedRow();

            return row ? `#${row.po}` : '-';
        },

        commodityGrade() {
            const row = this.selectedRow();

            return row ? `Komoditas: ${this.commodityLabel(row.po)}` : 'Komoditas: -';
        },

        targetLabel() {
            const row = this.selectedRow();

            return row ? `Target: ${row.krat_total} Krat / ${this.kg(row.est_kg)} kg Gross Target` : 'Target: -';
        },

        tabLabel() {
            const row = this.selectedRow();
            const code = String(row?.station ?? '').split(' ').pop() ?? '';

            return `${this.station.tab_prefix ?? 'WORKSTATION'} ${row ? `MEJA #${code}` : '-'}`;
        },

        barcodePrefix() {
            const row = this.selectedRow();
            const suffix = String(row?.po ?? '').slice(-4);

            return `${this.station.barcode_prefix ?? 'BAR-LOT'}-${suffix}-[01..${row?.krat_total ?? 0}]`;
        },

        palletCount() {
            const row = this.selectedRow();
            const total = numberOf(row?.krat_total);

            return total > 0 ? Math.ceil(total / 20) : 0;
        },

        palletLabel(position) {
            const row = this.selectedRow();
            const total = numberOf(row?.krat_total);
            const filled = Math.min(20, Math.max(0, total - 20 * (position - 1)));
            const tier = Math.max(1, Math.ceil(filled / 4));

            return `${filled} Krat (Tier 4x${tier})`;
        },

        /* ---------- KPI ---------- */

        doneCount() {
            return numberOf(this.meta.baseDone) + this.rows.filter((row) => row.stage === 4).length;
        },

        donePercent() {
            const total = Math.max(1, numberOf(this.meta.totalBatches));

            return Math.round((this.doneCount() / total) * 100);
        },

        doneLabel() {
            return `Selesai (${this.donePercent()}%)`;
        },

        doneProgress() {
            const total = Math.max(1, numberOf(this.meta.totalBatches));

            return Math.min(100, Math.round((this.doneCount() / total) * 1000) / 10);
        },

        weighNote() {
            const shown = this.rows
                .filter((row) => row.stage >= 3)
                .reduce((total, row) => total + numberOf(row.est_kg), 0);

            return `${this.kg(numberOf(this.meta.baseWeighKg) + shown)} kg menuju antrean timbang`;
        },

        progressFor(key, fallback) {
            if (key === 'sorted') {
                return this.doneProgress();
            }

            return numberOf(fallback);
        },

        /* ---------- actions ---------- */

        stationAction(key) {
            if (key === 'print-label') {
                this.printLabel();
                return;
            }

            if (key === 'flag-afkir') {
                this.flagAfkir();
                return;
            }

            if (key === 'transfer') {
                this.transfer();
            }
        },

        printLabel() {
            const row = this.selectedRow();
            if (!row) return;
            this.labelModal = true;
        },

        closeLabelModal() {
            this.labelModal = false;
        },

        executeThermalLabelPrint() {
            const row = this.selectedRow();
            if (!row) return;

            const win = window.open('', '_blank', 'width=550,height=600');
            win.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Thermal Label - ${row.po}</title>
                    <style>
                        body { font-family: monospace, sans-serif; padding: 16px; margin: 0; }
                        .label { border: 2px solid #000; padding: 14px; width: 340px; margin: 10px auto; border-radius: 6px; }
                        .h1 { font-size: 14px; font-weight: bold; margin: 0; }
                        .bar { font-size: 22px; font-weight: 900; letter-spacing: 3px; margin: 10px 0; border: 1px solid #000; padding: 6px; text-align: center; background: #eee; }
                        .row { display: flex; justify-content: space-between; font-size: 11px; margin: 4px 0; }
                    </style>
                </head>
                <body>
                    <div class="label">
                        <div class="h1">GREEN PASUNDAN AGRICULTURE</div>
                        <div style="font-size: 10px; margin-bottom: 8px;">Cold-Chain Staging • Ciwidey Packhouse</div>
                        <div class="bar">||| ${row.po} |||</div>
                        <div class="row"><span>Komoditas:</span><b>${this.commodityLabel(row.po)}</b></div>
                        <div class="row"><span>Staging:</span><b>${row.bay} (${row.station})</b></div>
                        <div class="row"><span>Buyer:</span><b>${row.buyer}</b></div>
                        <div class="row"><span>Total Krat:</span><b>${row.krat_total} Krat (${this.kg(row.est_kg)} KG)</b></div>
                        <div class="row"><span>Standard:</span><b>Food-Grade +4°C Cold Chain</b></div>
                    </div>
                    <script>window.onload = function() { window.print(); };</script>
                </body>
                </html>
            `);
            win.document.close();
            this.run('Label Termal Dicetak', `${row.krat_total} label barcode termal siap ditempel pada krat.`, 'success');
            this.closeLabelModal();
        },

        flagAfkir() {
            const row = this.selectedRow();
            if (!row) return;
            this.afkirModal = true;
        },

        closeAfkirModal() {
            this.afkirModal = false;
        },

        submitAfkir() {
            const row = this.selectedRow();
            if (!row) return;

            row.afkir_flagged = true;
            row.afkir = `${this.afkirPercent}% (${this.afkirReason})`;

            try {
                const storedAfkir = JSON.parse(localStorage.getItem('gpa_harvest_afkir') || '{}');
                storedAfkir[row.po] = row.afkir;
                localStorage.setItem('gpa_harvest_afkir', JSON.stringify(storedAfkir));
            } catch (e) {}

            this.run(
                'Afkir Tinggi Ditandai',
                `${row.po} afkir ${this.afkirPercent}% tercatat: "${this.afkirReason}". Diwajibkan sortasi ulang.`,
                'danger',
            );
            this.closeAfkirModal();
        },

        canTransfer() {
            return this.stageOf(this.selected) >= 3;
        },

        ctaLabel() {
            return String(this.station.cta?.label ?? 'TRANSFER KE MEJA TIMBANGAN NETTO >>');
        },

        transfer() {
            const row = this.selectedRow();
            if (!row) return;

            if (!this.canTransfer()) {
                this.run(
                    'Transfer Ditolak',
                    `${row.po} masih pada tahap ${this.stageLabel(row.po)}. Selesaikan tahap pra-pendinginan sebelum masuk antrean timbangan.`,
                    'danger',
                );
                return;
            }

            this.transferModal = true;
        },

        closeTransferModal() {
            this.transferModal = false;
        },

        confirmTransfer() {
            const row = this.selectedRow();
            if (!row) return;

            row.stage = 4;

            try {
                const storedTransfers = JSON.parse(localStorage.getItem('gpa_harvest_transfers') || '[]');
                if (!storedTransfers.includes(row.po)) {
                    storedTransfers.push(row.po);
                    localStorage.setItem('gpa_harvest_transfers', JSON.stringify(storedTransfers));
                }
            } catch (e) {}

            this.run(
                'Batch Diteruskan ke Timbangan',
                `${row.po} (${this.kg(row.est_kg)} KG, ${row.krat_total} krat) sukses dialihkan ke Meja Timbangan Netto.`,
                'success',
            );
            this.closeTransferModal();
        },

        goToWeighing() {
            const row = this.selectedRow();
            this.confirmTransfer();
            window.location.href = `/koordinator/penimbangan?po=${row ? row.po : ''}`;
        },

        /* ---------- QC log ---------- */

        qcSubtitle() {
            const row = this.selectedRow();

            return row
                ? `Sampel acak 5 krat (12.5% sampling) dari total ${row.krat_total} krat PO #${row.po}`
                : String(this.qc.subtitle ?? '');
        },

        avgTrim() {
            const samples = Array.isArray(this.qc.samples) ? this.qc.samples : [];

            if (samples.length === 0) {
                return String(this.qc.avg_value ?? '0.00%');
            }

            const total = samples.reduce((sum, sample) => sum + numberOf(sample.trim_percent), 0);
            const average = Math.round((total / samples.length) * 100) / 100;

            return `${average.toFixed(2)}%`;
        },

        inspectSample(krat) {
            const samples = Array.isArray(this.qc.samples) ? this.qc.samples : [];
            const found = samples.find(s => s.krat === krat) || {
                krat,
                trim_percent: '1.20%',
                gross_kg: '25.40',
                tare_kg: '2.00',
                net_kg: '23.40'
            };
            this.selectedSample = found;
            this.sampleModal = true;
        },

        closeSampleModal() {
            this.sampleModal = false;
            this.selectedSample = null;
        },

        confirmSampleQc() {
            this.run('Uji Petik Terverifikasi', `${this.selectedSample?.krat} lolos inspeksi QC trimming dan standar kesegaran cold-chain.`, 'success');
            this.closeSampleModal();
        },

        kg(value) {
            return kgFormat.format(numberOf(value));
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

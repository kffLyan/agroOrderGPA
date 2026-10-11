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
                `${row.buyer} \u{2022} ${row.bay} \u{2022} ${this.stageLabel(po)} \u{2022} ${this.kg(row.est_kg)} KG estimasi packing.`,
            );
        },

        refreshQueue() {
            this.run('Antrean Packing Disegarkan', `${this.rows.length} dari ${this.meta.totalBatches} batch operasional tampil pada staging zone aktif.`);
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

            return `${this.station.barcode_prefix ?? ''}-${suffix}-[01..${row?.krat_total ?? 0}]`;
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

            if (!row) {
                return;
            }

            this.run(
                'Label Krat Dicetak',
                `${this.barcodePrefix()} \u{2022} ${row.krat_total} label termal food-grade \u{2022} printer Zebra di ${row.station}.`,
            );
        },

        flagAfkir() {
            const row = this.selectedRow();

            if (!row) {
                return;
            }

            row.afkir_flagged = true;
            row.afkir = '2.4% (Review)';
            this.run(
                'Afkir Tinggi Ditandai',
                `${row.po} afkir 2.4% melampaui SOP 2.0% \u{2022} sortasi ulang wajib sebelum transfer ke timbangan (PRD Rule 04).`,
                'danger',
            );
        },

        canTransfer() {
            return this.stageOf(this.selected) >= 3;
        },

        ctaLabel() {
            return String(this.station.cta?.label ?? 'TRANSFER KE MEJA TIMBANGAN NETTO >>');
        },

        transfer() {
            const row = this.selectedRow();

            if (!row) {
                return;
            }

            if (!this.canTransfer()) {
                this.run(
                    'Transfer Ditolak \u{2022} Hard Gate Packing',
                    `${row.po} masih pada tahap ${this.stageLabel(row.po)}. Wadah wajib steril dan pra-isi +4\u{00B0}C sebelum masuk antrean timbangan.`,
                    'danger',
                );
                return;
            }

            row.stage = 4;
            this.run(
                'Batch Diteruskan ke Timbangan',
                `${row.po} \u{2022} ${this.kg(row.est_kg)} KG gross ${row.krat_total} krat \u{2022} ke ${row.station} timbangan netto. \u{2022} ${this.doneLabel()}.`,
                'success',
            );
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
            this.run('Uji Petik Terverifikasi', `${krat} \u{2022} berdasar sensor calibrated \u{2022} trimming ${this.avgTrim()} di bawah toleransi SOP 2.00%.`);
        },

        kg(value) {
            return kgFormat.format(numberOf(value));
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

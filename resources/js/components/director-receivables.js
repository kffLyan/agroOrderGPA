const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(/[^0-9.-]/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

const rupiah = (value) => `Rp ${Math.round(numberOf(value)).toLocaleString('id-ID')}`;

const dayOf = (value) => Math.round(numberOf(value));

export default function directorReceivables(rows = [], policy = {}, verification = {}, activeContracts = 0) {
    return {
        query: '',
        topFilter: 'all',
        activeContracts: numberOf(activeContracts),
        rows: (Array.isArray(rows) ? rows : []).map((row) => ({
            name: String(row.name ?? ''),
            client_code: String(row.client_code ?? ''),
            sj_label: String(row.sj_label ?? ''),
            outstanding: numberOf(row.outstanding),
            outstanding_label: String(row.outstanding_label ?? ''),
            verification: String(row.verification ?? ''),
            verification_tone: String(row.verification_tone ?? 'success'),
            top_days: numberOf(row.top_days),
            top_label: String(row.top_label ?? ''),
            due_label: String(row.due_label ?? ''),
            days_remaining: dayOf(row.days_remaining),
            days_label: String(row.days_label ?? ''),
            status_label: String(row.status_label ?? ''),
            status_tone: String(row.status_tone ?? 'success'),
            limit_label: String(row.limit_label ?? ''),
            utilisation_label: String(row.utilisation_label ?? ''),
            utilisation_note: String(row.utilisation_note ?? ''),
            utilisation_width: Math.min(numberOf(row.utilisation_width), 100),
            over_limit: Boolean(row.over_limit),
            locked: Boolean(row.locked),
            actions: (Array.isArray(row.actions) ? row.actions : []).map(String),
        })),
        policy: {
            bypass_action: String(policy?.bypass_action ?? 'Log Otorisasi Bypass'),
        },
        verification: {
            action: String(verification?.action ?? 'Buka Antrean Verifikasi Sekre'),
            queue_value: String(verification?.queue_value ?? '0 Faktur'),
            clearing_value: String(verification?.clearing_value ?? 'Rp 0'),
            sla: String(verification?.sla ?? ''),
        },
        topFilters: [
            { key: 'all', label: 'Semua' },
            { key: '14', label: 'TOP 14' },
            { key: '30', label: 'TOP 30' },
            { key: '45', label: 'TOP 45' },
        ],

        /* ---------- filter buku besar ---------- */

        matchesQuery(row) {
            const needle = this.query.trim().toLowerCase();

            if (needle === '') {
                return true;
            }

            return [row.name, row.client_code, row.sj_label]
                .some((field) => field.toLowerCase().includes(needle));
        },
        matchesTop(row) {
            return this.topFilter === 'all' || String(row.top_days) === this.topFilter;
        },
        rowVisible(index) {
            const row = this.rows[index];

            return row ? this.matchesQuery(row) && this.matchesTop(row) : true;
        },
        visibleCount() {
            return this.rows.filter((row) => this.matchesQuery(row) && this.matchesTop(row)).length;
        },
        shownLabel() {
            return `Menampilkan ${this.visibleCount()} dari ${this.activeContracts} Klien Kontrak Aktif B2B`;
        },
        outstandingTotal() {
            return rupiah(
                this.rows.filter((row) => this.matchesQuery(row) && this.matchesTop(row))
                    .reduce((total, row) => total + row.outstanding, 0),
            );
        },
        frozenRows() {
            return this.rows.filter((row) => row.locked);
        },
        overdueRows() {
            return this.rows.filter((row) => row.days_remaining < 0);
        },
        breachingRows() {
            return this.rows.filter((row) => row.over_limit);
        },
        setTopFilter(key) {
            this.topFilter = key;
        },

        /* ---------- tindakan direksi ---------- */

        inspectInvoice(row) {
            const tempo =
                row.days_remaining < 0
                    ? `menunggak ${Math.abs(row.days_remaining)} hari lewat batas TOP.`
                    : `masih ${row.days_remaining} hari sebelum jatuh tempo.`;

            this.notify(
                `Faktur Tempo ${row.client_code}`,
                `Sisa tagihan ${row.outstanding_label} ${tempo} Status AR ${row.status_label}.`,
                row.status_tone === 'danger' ? 'danger' : 'info',
            );
        },
        toggleFreeze(row) {
            if (!row.locked) {
                this.notify(
                    'Kunci Plafon',
                    `Plafon kredit ${row.client_code} akan dikunci setelah otorisasi biometrik Direktur.`,
                    'info',
                );

                return;
            }

            this.notify(
                'Lepas Freeze',
                `PO ${row.client_code} hanya dapat dilepas setelah restrukturisasi tagihan disetujui Direksi.`,
                'danger',
            );
        },
        sendReminder(row) {
            this.notify(
                'Reminder WA Terkirim',
                `Secretary akan menerima pengingat konfirmasi kliring untuk ${row.client_code} senilai ${row.outstanding_label}.`,
                'info',
            );
        },
        restructure(row) {
            this.notify(
                'Restrukturisasi Tagihan',
                `Jadwal baru untuk ${row.client_code} harus disusun dan disahkan Direksi sebelum PO berikutnya dilepas.`,
                'danger',
            );
        },
        openVerification() {
            this.notify(
                'Antrean Verifikasi Finance',
                `${this.verification.queue_value} menunggu pencocokan dua arah dengan total kliring ${this.verification.clearing_value}.`,
                'success',
            );
        },
        openBypassLog() {
            this.notify(
                'Log Otorisasi Bypass',
                'Semua bypass pembekuan memerlukan biometrik atau PIN Direktur Operasional.',
                'info',
            );
        },
        filterTopOnly() {
            this.topFilter = 'all';
            this.notify(
                'Filter TOP',
                'Menampilkan seluruh termin pembayaran dalam buku besar tempo.',
                'info',
            );
        },

        /* ---------- toast ---------- */

        notify(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

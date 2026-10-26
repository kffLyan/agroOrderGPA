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
            id: row.id,
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
        async toggleFreeze(row) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/direktur/receivables/${row.id}/toggle-freeze`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });

                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    row.locked = Boolean(data.locked);
                    this.notify(
                        row.locked ? 'Plafon Dikunci' : 'Lockdown Dicabut',
                        data.message || `Status plafon ${row.client_code} berhasil diperbarui.`,
                        row.locked ? 'danger' : 'success'
                    );
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    this.notify('Gagal Memperbarui', 'Gagal mengubah status pembekuan kredit klien.', 'danger');
                }
            } catch (err) {
                this.notify('Kesalahan Jaringan', err.message, 'danger');
            }
        },
        async sendReminder(row) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/direktur/receivables/${row.id}/reminder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });

                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    this.notify(
                        'Reminder Terkirim',
                        data.message || `Surat penagihan tempo berhasil diteruskan ke PIC ${row.name}.`,
                        'success'
                    );
                } else {
                    this.notify('Gagal Mengirim', 'Gagal mengirimkan pengingat tagihan.', 'danger');
                }
            } catch (err) {
                this.notify('Kesalahan Jaringan', err.message, 'danger');
            }
        },
        async restructure(row) {
            const days = await window.$prompt({
                title: 'Restrukturisasi Tenor TOP',
                message: `Masukkan jumlah hari perpanjangan tenor TOP untuk ${row.name}:`,
                defaultValue: '14',
                placeholder: 'Contoh: 14',
                inputType: 'number',
                icon: 'calendar',
                confirmText: 'Sahkan Tenor Baru',
                cancelText: 'Batal',
                tone: 'brand',
            });
            if (!days) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/direktur/receivables/${row.id}/restructure`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ extended_days: parseInt(days, 10) }),
                });

                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    this.notify(
                        'Restrukturisasi Disahkan',
                        data.message || `Jadwal baru disahkan. Tenor TOP diperpanjang +${days} hari.`,
                        'success'
                    );
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    this.notify('Gagal Restrukturisasi', 'Gagal memproses restrukturisasi kredit.', 'danger');
                }
            } catch (err) {
                this.notify('Kesalahan Jaringan', err.message, 'danger');
            }
        },
        openVerification() {
            window.location.href = '/sekretaris/verifikasi';
        },
        openBypassLog() {
            window.location.href = '/direktur/governance';
        },
        filterTopOnly() {
            const cycle = ['all', '14', '30', '45'];
            const currentIndex = cycle.indexOf(this.topFilter);
            this.topFilter = cycle[(currentIndex + 1) % cycle.length];
            this.notify(
                'Filter TOP Diperbarui',
                this.topFilter === 'all' ? 'Menampilkan seluruh termin pembayaran.' : `Menampilkan faktur dengan tenor TOP ${this.topFilter} hari.`,
                'info',
            );
        },

        /* ---------- toast ---------- */

        notify(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

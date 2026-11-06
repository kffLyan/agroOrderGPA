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
        selectedRow: null,
        auditOpen: false,
        openDetail(row) {
            this.selectedRow = row;
        },
        closeDetail() {
            this.selectedRow = null;
        },
        openAudit() {
            this.auditOpen = true;
        },
        closeAudit() {
            this.auditOpen = false;
        },
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
        page: 1,
        perPage: 10,

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
        filteredRows() {
            return this.rows.filter((row) => this.matchesQuery(row) && this.matchesTop(row));
        },
        get lastPage() {
            return Math.max(1, Math.ceil(this.filteredRows().length / this.perPage));
        },
        pagedRows() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredRows().slice(start, start + this.perPage);
        },
        rowVisible(index) {
            const row = this.rows[index];
            if (! row || ! (this.matchesQuery(row) && this.matchesTop(row))) {
                return false;
            }
            return this.pagedRows().some((item) => item.client_id === row.client_id);
        },
        visibleCount() {
            return this.filteredRows().length;
        },
        goToPage(page) {
            const target = Math.min(Math.max(1, page), this.lastPage);
            if (this.page !== target) {
                this.page = target;
            }
        },
        pageNumbers() {
            const pages = [];
            const total = this.lastPage;
            if (total <= 5) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                let start = Math.max(1, this.page - 2);
                let end = Math.min(total, this.page + 2);
                if (this.page <= 3) {
                    start = 1;
                    end = 5;
                } else if (this.page >= total - 2) {
                    start = total - 4;
                    end = total;
                }
                for (let i = start; i <= end; i++) pages.push(i);
            }
            return pages;
        },
        shownLabel() {
            const total = this.filteredRows().length;
            if (total === 0) return '0 Klien Kontrak Aktif B2B';
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, total);
            return `Menampilkan ${start}–${end} dari ${total} Klien Kontrak Aktif B2B`;
        },
        outstandingTotal() {
            return rupiah(
                this.filteredRows().reduce((total, row) => total + row.outstanding, 0),
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
            this.page = 1;
        },

        /* ---------- tindakan direksi ---------- */

        inspectInvoice(row) {
            this.openDetail(row);
        },
        async toggleFreeze(row) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/direktur/piutang/${row.id}/toggle-freeze`, {
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
                const response = await fetch(`/direktur/piutang/${row.id}/reminder`, {
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
                const response = await fetch(`/direktur/piutang/${row.id}/restructure`, {
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
            window.location.href = '/direktur/tata-kelola';
        },
        filterTopOnly() {
            const cycle = ['all', '14', '30', '45'];
            const currentIndex = cycle.indexOf(this.topFilter);
            this.topFilter = cycle[(currentIndex + 1) % cycle.length];
            this.page = 1;
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

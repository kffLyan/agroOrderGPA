const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(/[^0-9.-]/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

const rupiah = (value) =>
    'Rp ' + Math.round(numberOf(value)).toLocaleString('id-ID', { maximumFractionDigits: 0 });

const filterToneClasses = {
    all: 'bg-brand text-white outline-brand hover:bg-brand-hover',
    discount: 'bg-accent text-ink outline-success-deep hover:opacity-90',
    top: 'bg-accent text-ink outline-success-deep hover:opacity-90',
    annual: 'bg-accent text-ink outline-success-deep hover:opacity-90',
};

export default function directorApproval(rows = [], labels = {}) {
    const copy = labels && typeof labels === 'object' ? labels : {};

    return {
        filter: 'all',
        search: '',
        selected: [],
        totalRows: (Array.isArray(rows) ? rows : []).length,
        totalLabel: String(copy.shown_label ?? ''),
        rows: (Array.isArray(rows) ? rows : []).map((row) => ({
            ...row,
            id: row.id,
            contract: String(row.contract ?? ''),
            filter_key: String(row.filter_key ?? ''),
            client: String(row.client ?? ''),
            commodity: String(row.commodity ?? ''),
            submitted_by: String(row.submitted_by ?? ''),
            value: numberOf(row.value),
            haystack: [row.contract, row.client, row.commodity, row.submitted_by]
                .map((value) => String(value ?? '').toLowerCase())
                .join(' '),
        })),

        /* ---------- filter & search ---------- */

        setFilter(key) {
            this.filter = String(key);
            this.selected = [];
        },

        matches(key, index) {
            const row = this.rows[index];

            if (!row) {
                return false;
            }

            if (this.filter !== 'all' && this.filter !== String(key)) {
                return false;
            }

            const needle = this.search.trim().toLowerCase();

            return needle === '' || row.haystack.includes(needle);
        },

        visibleRows() {
            return this.rows.filter((row) => this.matches(row.filter_key));
        },

        visibleCount() {
            return this.visibleRows().length;
        },

        visibleLabel() {
            return `Menampilkan ${this.visibleCount()} dari ${this.totalRows} Pengajuan Pending`;
        },

        filterClass(key) {
            const base = 'transition-colors';
            const tone =
                this.filter === String(key)
                    ? filterToneClasses[String(key)] ?? filterToneClasses.all
                    : null;

            return tone ? `${base} ${tone}` : `${base} bg-transparent text-ink-body outline-line-board hover:bg-surface-shell`;
        },

        /* ---------- batch selection ---------- */

        selectedCount() {
            return this.selectedRows().length;
        },

        selectedRows() {
            return this.visibleRows().filter((row) => this.selected.includes(row.id));
        },

        selectedValue() {
            return this.selectedRows().reduce((total, row) => total + row.value, 0);
        },

        selectedLabel() {
            return `${this.selectedCount()} / ${this.visibleRows().length} Terpilih`;
        },

        selectedValueLabel() {
            return rupiah(this.selectedValue());
        },

        selectAll() {
            if (this.selected.length === this.visibleRows().length) {
                this.selected = [];
            } else {
                this.selected = this.visibleRows().map((row) => row.id);
            }
        },

        /* ---------- row actions ---------- */

        detail(id) {
            window.location.href = `/direktur/contracts/${id}`;
        },

        async approve(id) {
            const row = this.rows.find((item) => item.id === id || item.contract === id);
            const targetId = row ? row.id : id;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/direktur/contracts/${targetId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });

                if (response.ok) {
                    this.rows = this.rows.filter((item) => item.id !== targetId);
                    this.selected = this.selected.filter((item) => item !== targetId);
                    this.totalRows = this.rows.length;
                    this.run('Kontrak Disetujui', `Kontrak ${row?.contract || targetId} berhasil disetujui & disahkan oleh Direktur.`, 'success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    const data = await response.json().catch(() => ({}));
                    this.run('Gagal Otorisasi', data.message || 'Terjadi kesalahan saat menyetujui kontrak.', 'danger');
                }
            } catch (err) {
                this.run('Kesalahan Jaringan', 'Tidak dapat menghubungi server: ' + err.message, 'danger');
            }
        },

        async approveSelected() {
            if (this.selected.length === 0) {
                this.run('Pilih Kontrak', 'Centang minimal satu kontrak untuk batch approval.', 'warning');
                return;
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch('/direktur/contracts/batch-approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ selected_ids: this.selected }),
                });

                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    this.run('Batch Otorisasi Berhasil', data.message || 'Batch kontrak berhasil disetujui.', 'success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    const data = await response.json().catch(() => ({}));
                    this.run('Gagal Batch Otorisasi', data.message || 'Gagal memproses batch approval.', 'danger');
                }
            } catch (err) {
                this.run('Kesalahan Jaringan', 'Tidak dapat menghubungi server: ' + err.message, 'danger');
            }
        },

        async revise(id) {
            const row = this.rows.find((item) => item.id === id || item.contract === id);
            const targetId = row ? row.id : id;
            const notes = await window.$prompt({
                title: 'Revisi Klausul Kontrak',
                message: 'Masukkan catatan instruksi revisi klausul untuk Tim Legal & Sales:',
                defaultValue: 'Harap sesuaikan tenor TOP menjadi maksimal 30 hari.',
                placeholder: 'Catatan revisi...',
                icon: 'edit',
                tone: 'warning',
                confirmText: 'Kirim Revisi',
                cancelText: 'Batal',
            });

            if (!notes) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/direktur/contracts/${targetId}/revision`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ revision_notes: notes }),
                });

                if (response.ok) {
                    this.run('Revisi Terkirim', `Catatan revisi untuk kontrak ${row?.contract || targetId} telah dikirim ke Tim Sales.`, 'warning');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    const data = await response.json().catch(() => ({}));
                    this.run('Gagal Minta Revisi', data.message || 'Gagal mengirim permintaan revisi.', 'danger');
                }
            } catch (err) {
                this.run('Kesalahan Jaringan', 'Tidak dapat menghubungi server: ' + err.message, 'danger');
            }
        },

        async reject(id) {
            const row = this.rows.find((item) => item.id === id || item.contract === id);
            const targetId = row ? row.id : id;
            const reason = await window.$prompt({
                title: 'Tolak Pengesahan Kontrak',
                message: 'Masukkan alasan penolakan kontrak untuk catatan arsip audit:',
                defaultValue: 'Margin di bawah threshold atau plafon risiko kredit terlampaui.',
                placeholder: 'Alasan penolakan...',
                icon: 'danger',
                tone: 'danger',
                confirmText: 'Tolak Kontrak',
                cancelText: 'Batal',
            });

            if (!reason) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/direktur/contracts/${targetId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ rejection_reason: reason }),
                });

                if (response.ok) {
                    this.rows = this.rows.filter((item) => item.id !== targetId);
                    this.run('Kontrak Ditolak', `Kontrak ${row?.contract || targetId} resmi ditolak. Alasan tercatat di audit log.`, 'danger');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    const data = await response.json().catch(() => ({}));
                    this.run('Gagal Menolak', data.message || 'Gagal menolak kontrak.', 'danger');
                }
            } catch (err) {
                this.run('Kesalahan Jaringan', 'Tidak dapat menghubungi server: ' + err.message, 'danger');
            }
        },

        /* ---------- page actions ---------- */

        printAuthorization() {
            window.print();
            this.run('Cetak Disiapkan', 'Dialog cetak browser dibuka untuk dokumen otorisasi.', 'info');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

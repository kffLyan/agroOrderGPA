const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryPayments(queue = [], filters = [], operatorCode = 'OP-4091', auditTotal = 42) {
    const rows = Array.isArray(queue) ? queue : [];
    const filterList = Array.isArray(filters) ? filters : [];

    return {
        queue: rows,
        filters: filterList,
        query: '',
        operatorCode: String(operatorCode || 'OP-4091').toUpperCase(),
        auditTotal: Number(auditTotal) || 0,
        channel: 'all',
        paymentId: rows[0]?.id ?? null,
        settled: [],
        rejected: [],
        checks: [true, true, true],
        note: rows[0]?.note ?? '',
        auditPage: 1,

        proofModalOpen: false,
        proofModalUrl: null,
        proofModalTitle: '',
        proofModalSubtitle: '',
        proofModalSender: '',
        proofModalAccount: '',
        proofModalNominal: '',
        proofModalStamp: '',
        proofModalNote: '',
        proofModalFileName: '',
        proofModalIsImage: true,

        run(title, message, tone = 'info') {
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('gpa:toast', { title, message, tone });
            } else {
                window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
            }
        },

        get selected() {
            return this.queue.find((row) => row.id === this.paymentId) ?? this.queue[0] ?? null;
        },

        get checkDone() {
            return this.checks.filter(Boolean).length;
        },

        get checkComplete() {
            return this.checkDone === this.checks.length;
        },

        get inspectionRef() {
            return this.selected ? `Ref Kasir: ${this.selected.id}` : 'Ref Kasir: —';
        },

        get mutationChip() {
            return this.selected?.match ? 'Match 100%' : 'Perlu Cek';
        },

        get mutationNote() {
            return this.selected?.match
                ? 'Nominal slip & rekening koran identik.'
                : 'Nominal slip berbeda dengan mutasi rekening koran.';
        },

        get progressLabel() {
            return `${this.checkDone}/${this.checks.length} Terpenuhi`;
        },

        get queueTotal() {
            return this.queue.reduce((total, row) => total + Number(row.amount ?? 0), 0);
        },

        number(value, fraction = 0) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: fraction }).format(Number(value) || 0);
        },

        rupiah(value) {
            return `Rp ${this.number(value)}`;
        },

        field(row, key) {
            return row?.[key] ?? '—';
        },

        filterCount(key) {
            return this.queue.length === 0 && key === 'all'
                ? 0
                : this.queue.filter((row) => key === 'all' || row.channel === key).length;
        },

        filterLabel(key) {
            return this.filters.find((item) => item.key === key)?.label ?? 'Semua Antrean';
        },

        setChannel(key) {
            this.channel = key;

            this.run('Filter Antrean Diperbarui', `${this.filterLabel(key)} • ${this.visibleRows().length} transaksi ditampilkan.`);
        },

        visibleRows() {
            return this.queue.filter((row) => {
                if (this.channel !== 'all' && row.channel !== this.channel) {
                    return false;
                }

                const needle = normalize(this.query).trim();

                if (! needle) {
                    return true;
                }

                return [row.id, row.invoice, row.client, row.channel_label, row.evidence]
                    .map(normalize)
                    .some((value) => value !== '' && value.includes(needle));
            });
        },

        isSettled(row) {
            return this.settled.includes(row.id);
        },

        isRejected(row) {
            return this.rejected.includes(row.id);
        },

        rowClass(row) {
            if (this.paymentId === row.id) {
                return 'bg-success-soft/50 outline outline-2 outline-success';
            }

            if (this.isSettled(row)) {
                return 'bg-accent/30 outline outline-1 outline-success-deep/40';
            }

            if (this.isRejected(row)) {
                return 'bg-danger-soft/40 outline outline-1 outline-danger/40';
            }

            return 'bg-surface outline outline-1 outline-line-hair';
        },

        inspect(row) {
            if (this.paymentId === row.id) {
                return;
            }

            this.paymentId = row.id;
            this.note = row.note ?? '';
            this.checks = row.match ? [true, true, true] : [true, false, false];
        },

        toggleCheck(index) {
            this.checks[index] = ! this.checks[index];
        },

        isChecked(index) {
            return Boolean(this.checks[index]);
        },

        openProof(row) {
            const target = row || this.selected;
            if (!target) return;

            let fileUrl = target.evidence_url || target.proof_url || null;
            if (!fileUrl && target.evidence_file) {
                const fname = String(target.evidence_file);
                if (fname.startsWith('http://') || fname.startsWith('https://')) {
                    fileUrl = fname;
                } else if (fname.startsWith('payments/') || fname.startsWith('uploads/')) {
                    fileUrl = '/storage/' + fname;
                }
            }

            this.proofModalUrl = fileUrl;
            this.proofModalTitle = `Bukti Transfer • ${target.id || ''}`;
            this.proofModalSubtitle = `${target.client || 'Mitra Klien'} — ${target.invoice || ''}`;
            this.proofModalSender = target.slip_sender || target.client || '—';
            this.proofModalAccount = target.slip_account || '—';
            this.proofModalNominal = target.amount_label || target.slip_nominal || '—';
            this.proofModalStamp = target.slip_stamp || target.time || '—';
            this.proofModalNote = target.evidence_note || 'Status Bukti: Terlampir';
            this.proofModalFileName = target.evidence_file || 'bukti_transfer.jpg';

            const urlLower = String(this.proofModalUrl || '').toLowerCase();
            this.proofModalIsImage = urlLower.endsWith('.pdf') ? false : true;

            this.proofModalOpen = true;
        },

        closeProof() {
            this.proofModalOpen = false;
        },

        async approve() {
            const row = this.selected;

            if (! row) {
                return;
            }

            if (this.isSettled(row)) {
                this.run('Bukti Sudah Lunas', `${row.id} telah disetujui dan tercatat pada buku kas.`, 'danger');

                return;
            }

            if (! this.checkComplete) {
                this.run(
                    'Persetujuan Ditolak',
                    `Checklist audit ${this.progressLabel}. Bukti ${row.id} belum boleh disetujui sampai seluruh poin verifikasi perbankan terpenuhi.`,
                    'danger'
                );

                return;
            }

            const targetId = row.payment_id || parseInt(String(row.id).replace(/\D/g, ''), 10);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/sekretaris/pembayaran/${targetId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ note: this.note || 'Mutasi bank terverifikasi cocok.' }),
                });

                if (response.ok) {
                    this.settled.push(row.id);
                    this.note = '';

                    this.run(
                        'Pembayaran Disetujui & Lunas',
                        `${row.id} / ${row.invoice} • ${row.client} — ${row.amount_label} tercatat lunas pada buku kas (otorisasi ${this.operatorCode}).`,
                        'success'
                    );
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const data = await response.json().catch(() => ({}));
                    this.run('Gagal Verifikasi', data.message || 'Gagal menyetujui pembayaran.', 'danger');
                }
            } catch (err) {
                this.run('Kesalahan Jaringan', err.message, 'danger');
            }
        },

        async reject() {
            const row = this.selected;

            if (! row || this.isRejected(row)) {
                return;
            }

            const targetId = row.payment_id || parseInt(String(row.id).replace(/\D/g, ''), 10);
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/sekretaris/pembayaran/${targetId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ note: this.note || 'Bukti transfer tidak valid atau dana belum masuk rekening koran.' }),
                });

                if (response.ok) {
                    this.rejected.push(row.id);
                    this.note = '';

                    this.run(
                        'Bukti Bayar Ditolak',
                        `${row.id} / ${row.invoice} • ${row.client} ditolak. Klien diminta konfirmasi ulang nominal; faktur tetap berstatus belum lunas.`,
                        'danger'
                    );
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const data = await response.json().catch(() => ({}));
                    this.run('Gagal Menolak', data.message || 'Gagal menolak pembayaran.', 'danger');
                }
            } catch (err) {
                this.run('Kesalahan Jaringan', err.message, 'danger');
            }
        },

        saveNote() {
            const row = this.selected;

            if (! row || ! this.note.trim()) {
                this.run('Catatan Kosong', 'Isi catatan verifikasi sekre sebelum disimpan ke audit log.', 'warning');

                return;
            }

            this.run('Catatan Audit Disimpan', `${row.id}: "${this.note.trim()}" dicantumkan pada audit log ${this.operatorCode}.`, 'success');
        },

        sync() {
            window.location.reload();
        },

        goToPage(page) {
            this.auditPage = Number(page) || 1;

            this.run('Halaman Audit Diubah', `Halaman ${this.auditPage} dari ${this.number(this.auditTotal)} transaksi rekonsiliasi kas dimuat.`);
        },
    };
}
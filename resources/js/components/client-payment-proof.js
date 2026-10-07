/**
 * Halaman "Unggah Bukti Pembayaran & Pelacakan Status Verifikasi Tagihan".
 *
 * Formulir ini adalah turunan dari Pusat Dokumen & Faktur: pilihan tagihan,
 * nominal wajib transfer, dan rekening settlement dikirim server-side, lalu
 * Alpine menangani pemilihan tagihan, pratinjau slip transfer, validasi nominal
 * terhadap Total Wajib Transfer, pernyataan kepatuhan, dan simulasi pengiriman
 * ke Staf Sekretariat GPA.
 */
export default function clientPaymentProof(defaults = {}) {
    return {
        invoices: defaults.invoices ?? [],
        account: defaults.account ?? {},
        fileRules: defaults.fileRules ?? {},
        compliance: defaults.compliance ?? {},
        selected: defaults.selected ?? null,
        form: {
            senderBank: defaults.form?.sender_bank ?? '',
            senderAccount: defaults.form?.sender_account ?? '',
            amount: '',
            transferAt: defaults.form?.transfer_at ?? '',
            reference: defaults.form?.reference ?? '',
            note: defaults.form?.note ?? '',
            compliance: false,
        },
        file: defaults.file ?? null,
        rawFile: null,
        dragActive: false,
        sending: false,
        submitted: false,
        draftSaved: false,
        error: null,

        init() {
            this.selected = defaults.selected ?? this.invoices[0]?.po ?? null;
            this.form.amount = this.payableDigits;
        },

        get current() {
            return this.invoices.find((invoice) => invoice.po === this.selected) ?? this.invoices[0] ?? null;
        },

        get payableDigits() {
            return this.current?.payable_digits ?? '';
        },

        get creditValueLabel() {
            return this.current?.credit_value_label ?? '- Rp 0';
        },

        get hasCredit() {
            return Boolean(this.current?.has_credit);
        },

        digitsOnly(value) {
            return String(value ?? '').replace(/\D/g, '');
        },

        get amountValue() {
            return Number.parseInt(this.digitsOnly(this.form.amount) || '0', 10);
        },

        /**
         * Nominal transfer wajib identik dengan Total Wajib Transfer; selisih
         * sekecil apa pun akan ditolak Staf Sekretariat saat rekonsiliasi.
         */
        get amountMatches() {
            if (! this.current) {
                return false;
            }

            const target = Number(this.current.payable || this.current.payable_digits || 0);

            return this.amountValue === target;
        },

        get hasFile() {
            return this.file !== null;
        },

        get canSubmit() {
            return this.hasFile && this.amountMatches && this.form.compliance === true && ! this.sending;
        },

        onInvoiceChange() {
            this.form.amount = this.payableDigits;
            this.submitted = false;
            this.draftSaved = false;
            this.error = null;

            this.$dispatch('gpa:toast', {
                title: 'Tagihan Dipilih',
                message: `${this.current?.po ?? '-'} — Total Wajib Transfer ${this.current?.payable_label ?? '-'}.`,
                tone: 'info',
            });
        },

        formatBytes(bytes) {
            if (! Number.isFinite(bytes) || bytes <= 0) {
                return '0 KB';
            }

            const kilobytes = bytes / 1024;

            return kilobytes >= 1024
                ? `${(kilobytes / 1024).toFixed(1)} MB`
                : `${Math.round(kilobytes)} KB`;
        },

        onFileInput(event) {
            this.acceptFile(event.target.files?.[0] ?? null);
        },

        onDragOver() {
            this.dragActive = true;
        },

        onDrop(event) {
            this.dragActive = false;
            this.acceptFile(event.dataTransfer?.files?.[0] ?? null);
        },

        acceptFile(file) {
            if (! file) {
                return;
            }

            const extension = (file.name.split('.').pop() ?? '').toLowerCase();
            const allowed = this.fileRules.accept ?? [];
            const maxBytes = (this.fileRules.max_kb ?? 5120) * 1024;

            if (allowed.length > 0 && ! allowed.includes(extension)) {
                this.reject(`Berkas .${extension} tidak didukung. Gunakan JPG, PNG, atau PDF.`);

                return;
            }

            if (file.size > maxBytes) {
                this.reject(`Ukuran berkas ${this.formatBytes(file.size)} melebihi ${this.fileRules.max_label}.`);

                return;
            }

            this.rawFile = file;
            this.file = {
                name: file.name,
                size_label: this.formatBytes(file.size),
                time_label: 'Baru saja diunggah',
                hash_label: 'SHA-256 Dihitung',
            };
            this.error = null;

            this.$dispatch('gpa:toast', {
                title: 'Slip Transfer Terlampirkan',
                message: `${file.name} (${this.formatBytes(file.size)}) siap dikirim ke Sekretariat GPA.`,
                tone: 'success',
            });
        },

        removeFile() {
            this.file = null;
            this.rawFile = null;
            this.submitted = false;
        },

        async copy(text, label) {
            try {
                await navigator.clipboard.writeText(text);

                this.$dispatch('gpa:toast', {
                    title: label,
                    message: `"${text}" tersalin ke papan klip.`,
                    tone: 'success',
                });
            } catch {
                this.$dispatch('gpa:toast', {
                    title: label,
                    message: `Salin manual: ${text}`,
                    tone: 'info',
                });
            }
        },

        copyAccount() {
            this.copy(this.account.number ?? '', 'Nominal Rekening GPA');
        },

        copyAmount() {
            this.copy(this.payableDigits, 'Nominal Transfer Presisi');
        },

        contactPic() {
            this.$dispatch('gpa:toast', {
                title: 'Hotline Sekretariat GPA',
                message: 'SLA 2 jam kerja aktif. Ext 4 akan terhubung ke PIC Finance.',
                tone: 'info',
            });
        },

        exportReport() {
            const rows = [
                ['ID Bayar', 'Faktur Terkait', 'Tanggal Transfer', 'Nominal (Rp)', 'Metode', 'Pemeriksa', 'Status', 'No Kuitansi'],
            ];

            const historyTableRows = document.querySelectorAll('table tbody tr');
            if (historyTableRows.length > 0) {
                historyTableRows.forEach((tr) => {
                    const cols = Array.from(tr.querySelectorAll('td')).map((td) => td.innerText.trim().replace(/\n+/g, ' '));
                    if (cols.length >= 6) {
                        rows.push(cols);
                    }
                });
            }

            const csvContent = rows.map((r) => r.map((val) => `"${String(val).replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', `rekap-pembayaran-gpa-${new Date().toISOString().slice(0, 10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            this.$dispatch('gpa:toast', {
                title: 'Laporan Rekonsiliasi Diunduh',
                message: 'File CSV riwayat rekonsiliasi pembayaran berhasil diunduh.',
                tone: 'success',
            });
        },

        saveDraft() {
            this.draftSaved = true;
            this.submitted = false;

            this.$dispatch('gpa:toast', {
                title: 'Draf Tersimpan',
                message: `Draft ${this.current?.po ?? '-'} disimpan di vault klien dan dapat dilanjutkan kapan saja.`,
                tone: 'info',
            });
        },

        async submit() {
            if (! this.hasFile) {
                this.reject('Lampirkan slip transfer atau screenshot m-banking sebelum mengirim bukti.');

                return;
            }

            if (! this.amountMatches) {
                this.reject(`Nominal transfer harus sama dengan Total Wajib Transfer ${this.current?.payable_label ?? ''}.`);

                return;
            }

            if (this.form.compliance !== true) {
                this.reject('Centang pernyataan kepatuhan sebelum mengirim bukti ke Sekretariat.');

                return;
            }

            this.sending = true;
            this.error = null;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '';

                const formData = new FormData();
                formData.append('invoice_po', this.current?.po ?? '');
                formData.append('amount', this.amountValue);
                formData.append('sender_bank', this.form.senderBank || 'BCA');
                formData.append('sender_account', this.form.senderAccount || '-');
                formData.append('reference', this.form.reference || '');
                formData.append('transfer_at', this.form.transferAt || '');
                formData.append('note', this.form.note || '');
                if (this.rawFile) {
                    formData.append('proof_file', this.rawFile);
                }

                const response = await fetch('/klien/payment-proof', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json().catch(() => ({}));

                if (! response.ok || result.success === false) {
                    throw new Error(result.message || 'Gagal mengirim bukti pembayaran ke server.');
                }

                this.sending = false;
                this.submitted = true;
                this.draftSaved = false;

                this.$dispatch('gpa:toast', {
                    title: 'Bukti Pembayaran Terkirim',
                    message: `${this.current?.po ?? '-'} berhasil diajukan dan masuk antrean verifikasi Sekretariat GPA.`,
                    tone: 'success',
                });

                window.setTimeout(() => {
                    window.location.href = '/klien/documents';
                }, 1200);
            } catch (err) {
                this.sending = false;
                this.reject(err.message || 'Terjadi gangguan koneksi saat mengirim bukti pembayaran.');
            }
        },

        reject(message) {
            this.error = message;

            this.$dispatch('gpa:toast', {
                title: 'Bukti Belum Dapat Dikirim',
                message,
                tone: 'danger',
            });
        },
    };
}
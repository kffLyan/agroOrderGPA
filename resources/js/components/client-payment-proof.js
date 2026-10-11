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
            return this.current !== null && this.amountValue === this.current.payable;
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
            this.$dispatch('gpa:toast', {
                title: 'Laporan Rekonsiliasi',
                message: 'Ekspor .XLSX 60 hari terakhir akan dibuat dari vault pembayaran GPA.',
                tone: 'info',
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

        submit() {
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

            window.setTimeout(() => {
                this.sending = false;
                this.submitted = true;
                this.draftSaved = false;

                this.$dispatch('gpa:toast', {
                    title: 'Bukti Dikirim ke Sekretariat',
                    message: `${this.current?.po ?? '-'} masuk antrean verifikasi manual. Estimasi maks 2 jam kerja.`,
                    tone: 'success',
                });
            }, 700);
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
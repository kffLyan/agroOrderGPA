    /**
 * Formulir "Input Order Manual" (Sub-01 // Verifikasi Pesanan).
 *
 * Alpine ini menghitung ulang subtotal, berat, dan tagihan secara reaktif,
 * lalu mengunci evaluasi batas kredit B2B serta ketersediaan stok panen
 * sebelum operator boleh meneruskan pesanan ke tahap faktur.
 */

const toNumber = (value) => {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : 0;
};

const clone = (value) => JSON.parse(JSON.stringify(value ?? null));

export default function secretaryManualOrder(
    commodities = [],
    catalog = [],
    logistics = {},
    payment = {},
    source = {},
    client = {},
    clientList = [],
    paymentChannels = [],
) {
    const rows = Array.isArray(commodities) ? commodities.map(clone) : [];
    const catalogList = Array.isArray(catalog) ? catalog.map(clone) : [];
    const logisticsForm = clone(logistics) ?? {};
    const paymentForm = clone(payment) ?? {};
    const sourceForm = clone(source) ?? {};
    const clientForm = clone(client) ?? {};
    const clients = Array.isArray(clientList) ? clientList.map(clone) : [];
    const channels = Array.isArray(paymentChannels) ? paymentChannels.map(clone) : [];

    const defaultChannelId = channels.length > 0 ? channels[0].id : 'bca';
    const defaultChannelName = channels.length > 0 ? channels[0].name : 'Bank BCA';

    return {
        rows,
        catalog: catalogList,
        source: sourceForm,
        client: clientForm,
        clients,
        selectedClientId: clientForm.id ?? (clients[0]?.id ?? ''),
        logistics: logisticsForm,
        payment: paymentForm,
        channels,
        paymentCategory: 'official', // 'official' (Kanal Resmi GPA) atau 'b2b' (Termin Khusus B2B)
        selectedChannelId: defaultChannelId,
        senderBank: defaultChannelName,
        senderAccount: '',
        paymentReference: '',
        query: '',
        isSubmitting: false,
        rawFile: null,
        dragActive: false,
        fullQrisModal: null,
        evidenceModal: false,
        evidence: {
            file: paymentForm.file ?? '',
            size: paymentForm.size ?? '',
            hash: paymentForm.hash ?? '',
            hash_note: paymentForm.hash_note ?? '',
            previewUrl: null,
            isImage: false,
        },
        nextKey: 0,
        selectedCatalog: '',
        channel: sourceForm.active ?? 'whatsapp',
        clientMode: clientForm.active_mode ?? 'registered',
        paymentMethod: paymentForm.active ?? 'transfer',
        paymentCustomNote: '',
        cashAmount: 0,

        get selectedChannel() {
            return this.channels.find((c) => c.id === this.selectedChannelId) ?? this.channels[0] ?? {
                id: 'bca',
                type: 'BANK',
                name: 'Bank BCA',
                account_number: '8210-992-3841',
                account_name: 'PT Green Pasundan Agriculture (Divisi Keuangan B2B)',
                instructions: 'Sertakan nomor referensi pesanan di berita transfer.',
                logo_url: '/images/payments/bca.svg',
            };
        },

        selectChannel(id) {
            this.selectedChannelId = id;
            this.paymentCategory = 'official';
            const ch = this.selectedChannel;
            if (ch) {
                this.paymentMethod = ch.type === 'QRIS' ? 'qris' : (ch.type === 'EWALLET' ? 'ewallet' : 'transfer');
                this.senderBank = ch.name;
                this.run('Kanal Pembayaran Dipilih', `${ch.name}: ${ch.account_number} (a.n. ${ch.account_name})`, 'info');
            }
        },

        selectCashMethod() {
            this.paymentCategory = 'cash';
            this.paymentMethod = 'cash';
            this.senderBank = 'Kasir Tunai / Kas Sekretariat';
            if (!this.cashAmount || this.cashAmount < this.total) {
                this.cashAmount = this.total;
            }
            this.run('Pembayaran Tunai (Cash)', 'Sistem menghitung kembalian otomatis di kasir.', 'info');
        },

        setCashExact() {
            this.cashAmount = this.total;
            this.run('Uang Pas Dipilih', `Nominal tunai diatur: ${this.rupiah(this.total)}`, 'info');
        },

        addCashPreset(amount) {
            this.cashAmount = (toNumber(this.cashAmount) || 0) + amount;
        },

        roundCashUp(step = 50000) {
            if (this.total <= 0) return;
            const rounded = Math.ceil(this.total / step) * step;
            this.cashAmount = Math.max(rounded, this.total);
        },

        selectB2bMethod(method) {
            this.paymentCategory = 'b2b';
            this.paymentMethod = method;
            const labels = {
                cod: 'Bayar di Tempat (COD)',
                top14: 'Tempo 14 Hari (Mitra Kontrak)',
                custom: 'Metode Khusus / SK Direktur',
            };
            this.run('Skema Pembayaran Dipilih', labels[method] || method, 'info');
        },

        async copy(text, label) {
            try {
                await navigator.clipboard.writeText(text);
                this.run(label, `"${text}" berhasil disalin ke papan klip.`, 'success');
            } catch {
                this.run(label, `Salin manual: ${text}`, 'info');
            }
        },

        copyAccount() {
            const acc = this.selectedChannel?.account_number ?? '';
            const name = this.selectedChannel?.name ?? 'GPA';
            this.copy(acc, `Nomor Rekening / NMID ${name}`);
        },

        run(title, message, tone = 'info') {
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('gpa:toast', { title, message, tone });
            } else {
                window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
            }
        },

        /* ------------------------------------------------------------------
         | Pemilihan Klien Terdaftar
         * ----------------------------------------------------------------*/
        selectClient(id) {
            const found = this.clients.find((c) => String(c.id) === String(id));
            if (!found) return;

            this.selectedClientId = found.id;
            this.client.id = found.id;
            this.client.code = found.code;
            this.client.name = found.name;
            this.client.contact = found.contact;
            this.client.phone = found.phone;
            this.client.address = found.address;
            this.client.credit = {
                limit: found.credit_limit,
                used: found.credit_used,
            };

            this.run('Pembeli Dipilih', `${found.name} (${found.code}) siap diproses.`, 'info');
        },

        /* ------------------------------------------------------------------
         | Formatter
         * ----------------------------------------------------------------*/

        number(value, fraction = 0) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: fraction,
                maximumFractionDigits: fraction,
            }).format(toNumber(value));
        },

        kg(value) {
            return `${this.number(value, 1)} kg`;
        },

        rupiah(value) {
            return `Rp ${this.number(value)}`;
        },

        compactRupiah(value) {
            const amount = toNumber(value);

            if (amount >= 1000000) {
                return `Rp ${this.number(amount / 1000000, 2)}M`;
            }

            if (amount >= 1000) {
                return `Rp ${this.number(amount / 1000, 0)}K`;
            }

            return this.rupiah(amount);
        },

        /* ------------------------------------------------------------------
         | Kalkulasi reaktif
         * ----------------------------------------------------------------*/

        quantity(row) {
            return toNumber(row?.quantity);
        },

        price(row) {
            return toNumber(row?.price);
        },

        rowSubtotal(row) {
            return this.quantity(row) * this.price(row);
        },

        get totalWeight() {
            return this.rows.reduce((total, row) => total + this.quantity(row), 0);
        },

        get subtotal() {
            return this.rows.reduce((total, row) => total + this.rowSubtotal(row), 0);
        },

        get fee() {
            return toNumber(this.logistics?.fee);
        },

        get total() {
            return this.subtotal + this.fee;
        },

        get cashReceived() {
            return toNumber(this.cashAmount);
        },

        get cashChange() {
            return Math.max(0, this.cashReceived - this.total);
        },

        get cashShortage() {
            return Math.max(0, this.total - this.cashReceived);
        },

        get isCashSufficient() {
            return this.cashReceived >= this.total && this.total > 0;
        },

        /* ------------------------------------------------------------------
         | Batas kredit B2B
         * ----------------------------------------------------------------*/

        get creditLimit() {
            return toNumber(this.client?.credit?.limit);
        },

        get creditUsed() {
            return toNumber(this.client?.credit?.used);
        },

        get creditRemaining() {
            return this.creditLimit - this.creditUsed;
        },

        get creditPercent() {
            if (this.creditLimit <= 0) {
                return 0;
            }

            return Math.round((this.creditUsed / this.creditLimit) * 1000) / 10;
        },

        get creditAfter() {
            return this.creditRemaining - this.total;
        },

        get creditOk() {
            return this.creditAfter >= 0;
        },

        /* ------------------------------------------------------------------
         | Ketersediaan stok panen & minimum order
         * ----------------------------------------------------------------*/

        stockOk(row) {
            return this.quantity(row) <= toNumber(row?.stock);
        },

        minOk(row) {
            return this.quantity(row) >= toNumber(row?.min_order);
        },

        rowOk(row) {
            return this.stockOk(row) && this.minOk(row);
        },

        stockLabel(row) {
            if (!this.stockOk(row)) {
                return 'Stok Sistem Tidak Cukup';
            }

            return 'Cukup / Aman';
        },

        stockClass(row) {
            return this.stockOk(row)
                ? 'bg-success-soft text-success-ink'
                : 'bg-danger-soft text-danger-ink';
        },

        minLabel(row) {
            const quantity = this.number(this.quantity(row));
            const minimum = this.number(row?.min_order);
            const mark = this.minOk(row) ? 'PASS' : 'FAIL';

            return `${quantity} kg >= ${minimum} kg [${mark}]`;
        },

        minClass(row) {
            return this.minOk(row) ? 'text-success-deep' : 'text-danger-ink';
        },

        /** Alasan pemblokiran sebelum PO boleh diteruskan. */
        blockers() {
            const issues = [];

            this.rows.forEach((row) => {
                if (! this.stockOk(row)) {
                    issues.push(
                        `${row.name}: ${this.number(this.quantity(row))} kg melebihi stok sistem ${this.number(row.stock)} kg.`
                    );
                }

                if (! this.minOk(row)) {
                    issues.push(
                        `${row.name}: ${this.number(this.quantity(row))} kg di bawah minimum order ${this.number(row.min_order)} kg.`
                    );
                }
            });

            if (! this.creditOk && this.clientMode === 'registered') {
                issues.push(
                    `Total estimasi ${this.rupiah(this.total)} melampaui sisa plafon ${this.rupiah(this.creditRemaining)}.`
                );
            }

            return issues;
        },

        get ready() {
            return this.rows.length > 0 && this.blockers().length === 0;
        },

        /* ------------------------------------------------------------------
         | Aksi formulir
         * ----------------------------------------------------------------*/

        addRow(key) {
            const item = this.catalog.find((entry) => entry.key === key);

            if (! item) {
                return;
            }

            if (this.rows.some((row) => row.sku === item.sku)) {
                this.run('Baris Sudah Terdaftar', `${item.name} sudah ada di tabel komoditas.`, 'warning');

                return;
            }

            this.rows.push({ ...clone(item), quantity: Math.max(1, item.min_order || 1) });
            this.nextKey += 1;
            this.selectedCatalog = '';

            this.run(
                'Baris Komoditas Ditambahkan',
                `${item.name} (${item.sku}) • ${this.kg(item.min_order)} — stok siap dialokasikan.`
            );
        },

        removeRow(index) {
            const row = this.rows[index];

            if (! row) {
                return;
            }

            this.rows.splice(index, 1);

            this.run('Baris Komoditas Dihapus', `${row.name} (${row.sku}) dikeluarkan dari draft PO ini.`, 'warning');
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
            if (!file) return;

            const extension = (file.name.split('.').pop() ?? '').toLowerCase();
            const allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            const maxBytes = 5120 * 1024; // 5 MB

            if (!allowed.includes(extension)) {
                this.run('Format Tidak Didukung', `Berkas .${extension} tidak didukung. Gunakan JPG, PNG, atau PDF.`, 'danger');
                return;
            }

            if (file.size > maxBytes) {
                const mb = (file.size / (1024 * 1024)).toFixed(1);
                this.run('Ukuran Melebihi Batas', `Ukuran berkas ${mb} MB melebihi batas maksimal 5 MB.`, 'danger');
                return;
            }

            this.rawFile = file;
            const sizeKb = Math.round(file.size / 1024);
            const sizeText = sizeKb > 1024 ? `${(sizeKb / 1024).toFixed(1)} MB` : `${sizeKb} KB`;
            const previewUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;

            this.evidence = {
                file: file.name,
                size: sizeText,
                hash: 'GPA-' + Math.random().toString(36).substring(2, 10).toUpperCase(),
                hash_note: 'Berkas Siap Diunggah',
                previewUrl: previewUrl,
                isImage: file.type.startsWith('image/'),
            };

            this.run('Bukti Dilampirkan', `${file.name} (${sizeText}) siap disimpan bersama pesanan.`, 'success');
        },

        handleFileUpload(event) {
            this.onFileInput(event);
        },

        viewEvidence() {
            if (!this.evidence.file) {
                this.run('Bukti Belum Diunggah', 'Belum ada berkas bukti pembayaran yang dilampirkan.', 'warning');
                return;
            }

            if (this.evidence.previewUrl) {
                this.evidenceModal = true;
            } else {
                this.run('Bukti Pembayaran', `${this.evidence.file} (${this.evidence.size}) • Berkas PDF/Dokumen terlampir.`, 'info');
            }
        },

        removeEvidence() {
            if (this.evidence.previewUrl) {
                URL.revokeObjectURL(this.evidence.previewUrl);
            }
            this.rawFile = null;
            this.evidence = { file: '', size: '', hash: '', hash_note: '', previewUrl: null, isImage: false };
            this.run('Bukti Dihapus', 'Lampiran bukti transfer telah dilepas.', 'warning');
        },

        /* ------------------------------------------------------------------
         | Pengiriman Nyata ke Server (Backend Integration)
         * ----------------------------------------------------------------*/
        async submitOrder(targetStatus = 'MENUNGGU_VERIFIKASI') {
            if (this.isSubmitting) return;

            if (this.rows.length === 0) {
                this.run('Formulir Kosong', 'Tambahkan minimal satu baris komoditas sebelum menyimpan.', 'danger');
                return;
            }

            // Validasi kuantitas
            for (const row of this.rows) {
                if (this.quantity(row) <= 0) {
                    this.run('Kuantitas Tidak Valid', `Kuantitas untuk ${row.name} harus lebih dari 0 kg.`, 'danger');
                    return;
                }
            }

            // Jika status verifikasi, pastikan tidak ada blocker
            if (targetStatus === 'MENUNGGU_VERIFIKASI') {
                const issues = this.blockers();
                if (issues.length > 0) {
                    this.run('Verifikasi Diblokir', issues[0], 'danger');
                    return;
                }

                // Validasi uang tunai jika memilih metode cash
                if (this.paymentMethod === 'cash') {
                    if (this.cashReceived < this.total) {
                        this.run(
                            'Uang Tunai Kurang',
                            `Nominal tunai yang diterima kurang Rp ${this.number(this.cashShortage)}. Total tagihan: Rp ${this.number(this.total)}, Diterima: Rp ${this.number(this.cashReceived)}.`,
                            'danger'
                        );
                        return;
                    }
                }
            }

            this.isSubmitting = true;
            if (window.showGpaLoader) {
                window.showGpaLoader(
                    targetStatus === 'DRAFT' ? 'Menyimpan Draf PO...' : 'Memproses Pesanan Manual...',
                    'Menyimpan data pesanan dan mengalokasikan stok komoditas...'
                );
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const formData = new FormData();
            formData.append('client_id', this.clientMode === 'registered' ? (this.client.id || '') : '');
            formData.append('client_name', this.client.name || 'Pelanggan Walk-In');
            formData.append('client_phone', this.client.phone || '');
            formData.append('delivery_address', this.client.address || 'Gudang Utama Klien, Jawa Barat');
            if (this.logistics.date) formData.append('target_delivery_date', this.logistics.date);
            formData.append('order_source', (this.channel || 'WHATSAPP').toUpperCase());
            formData.append('source_reference', this.source.reference || '');
            formData.append('payment_method', this.paymentMethod || 'transfer');
            formData.append('payment_channel_id', this.paymentCategory === 'cash' ? 'cash' : (this.selectedChannelId || ''));
            formData.append('sender_bank', this.paymentCategory === 'cash' ? 'Kasir Tunai / Kas Sekretariat' : (this.senderBank || this.selectedChannel?.name || ''));
            formData.append('sender_account', this.senderAccount || '');
            formData.append('payment_reference', this.paymentReference || '');
            formData.append('payment_custom_note', this.paymentCustomNote || '');
            formData.append('cash_amount', this.cashAmount || 0);
            formData.append('cash_change', this.cashChange || 0);
            formData.append('fleet_type', this.logistics?.fleet === 'Armada Khusus / Custom'
                ? (this.logistics?.customFleet || 'Armada Khusus')
                : (this.logistics?.fleet || 'CDD Reefer Chilled'));
            formData.append('status', targetStatus);
            formData.append('items', JSON.stringify(this.rows.map(row => ({
                product_id: row.product_id,
                quantity: this.quantity(row),
                price: this.price(row),
            }))));

            if (this.rawFile) {
                formData.append('proof_file', this.rawFile);
            }

            try {
                const storeUrl = window.gpaManualOrderStoreUrl || '/sekretaris/pesanan-manual';
                const res = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json().catch(() => ({
                    success: false,
                    message: 'Server mengembalikan respons non-JSON (' + res.status + ').'
                }));

                if (res.ok && data.success) {
                    this.run(
                        targetStatus === 'DRAFT' ? 'Draf Disimpan' : 'Pesanan Berhasil Disimpan',
                        data.message,
                        'success'
                    );

                    setTimeout(() => {
                        window.location.href = data.redirect_url || '/sekretaris/verifikasi';
                    }, 500);
                } else {
                    if (window.hideGpaLoader) window.hideGpaLoader();
                    this.isSubmitting = false;
                    this.run('Gagal Menyimpan', data.message || 'Terjadi kendala saat menyimpan pesanan.', 'danger');
                }
            } catch (err) {
                if (window.hideGpaLoader) window.hideGpaLoader();
                this.isSubmitting = false;
                this.run('Kesalahan Jaringan', 'Gagal menghubungi server. Pastikan koneksi aktif dan coba lagi.', 'danger');
            }
        },

        saveDraft() {
            this.submitOrder('DRAFT');
        },

        verify() {
            this.submitOrder('MENUNGGU_VERIFIKASI');
        },

        async cancel() {
            const ok = await window.$confirm({
                title: 'Batalkan Order Manual',
                message: 'Apakah Anda yakin ingin membatalkan pengisian order manual dan kembali ke antrean verifikasi?',
                icon: 'warning',
                tone: 'warning',
                confirmText: 'Ya, Batalkan',
                cancelText: 'Tetap Lanjutkan',
            });
            if (ok) {
                window.location.href = '/sekretaris/verifikasi';
            }
        },
    };
}
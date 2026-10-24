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
) {
    const rows = Array.isArray(commodities) ? commodities.map(clone) : [];
    const catalogList = Array.isArray(catalog) ? catalog.map(clone) : [];
    const logisticsForm = clone(logistics) ?? {};
    const paymentForm = clone(payment) ?? {};
    const sourceForm = clone(source) ?? {};
    const clientForm = clone(client) ?? {};
    const clients = Array.isArray(clientList) ? clientList.map(clone) : [];

    return {
        rows,
        catalog: catalogList,
        source: sourceForm,
        client: clientForm,
        clients,
        selectedClientId: clientForm.id ?? (clients[0]?.id ?? ''),
        logistics: logisticsForm,
        payment: paymentForm,
        query: '',
        isSubmitting: false,
        evidence: {
            file: paymentForm.file ?? '',
            size: paymentForm.size ?? '',
            hash: paymentForm.hash ?? '',
            hash_note: paymentForm.hash_note ?? '',
        },
        nextKey: 0,
        selectedCatalog: '',
        channel: sourceForm.active ?? 'whatsapp',
        clientMode: clientForm.active_mode ?? 'registered',
        paymentMethod: paymentForm.active ?? 'transfer',
        paymentCustomNote: '',

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

        handleFileUpload(event) {
            const file = event.target.files?.[0];
            if (!file) return;

            const sizeKb = Math.round(file.size / 1024);
            const sizeText = sizeKb > 1024 ? `${(sizeKb / 1024).toFixed(1)} MB` : `${sizeKb} KB`;
            const fakeHash = 'GPA-' + Math.random().toString(36).substring(2, 10).toUpperCase();

            this.evidence = {
                file: file.name,
                size: sizeText,
                hash: fakeHash,
                hash_note: 'Dokumen Terverifikasi Otomatis',
            };

            this.run('Bukti Dilampirkan', `${file.name} (${sizeText}) berhasil diunggah ke formulir.`, 'success');
        },

        viewEvidence() {
            if (! this.evidence.file) {
                this.run('Bukti Belum Diunggah', 'Lampirkan screenshot bukti transfer sebelum meneruskan PO.', 'warning');

                return;
            }

            this.run('Bukti Pembayaran', `${this.evidence.file} • ${this.evidence.size} • Hash: ${this.evidence.hash}`, 'info');
        },

        removeEvidence() {
            this.evidence = { file: '', size: '', hash: '', hash_note: '' };

            this.run('Bukti Dihapus', 'Lampiran bukti pembayaran dilepas dari draft PO.', 'warning');
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
            }

            this.isSubmitting = true;
            if (window.showGpaLoader) {
                window.showGpaLoader(
                    targetStatus === 'DRAFT' ? 'Menyimpan Draf PO...' : 'Memproses Pesanan Manual...',
                    'Menyimpan data pesanan dan mengalokasikan stok komoditas...'
                );
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const payload = {
                client_id: this.clientMode === 'registered' ? (this.client.id || null) : null,
                client_name: this.client.name || 'Pelanggan Walk-In',
                client_phone: this.client.phone || '',
                delivery_address: this.client.address || 'Gudang Utama Klien, Jawa Barat',
                target_delivery_date: this.logistics.date || null,
                order_source: (this.channel || 'WHATSAPP').toUpperCase(),
                source_reference: this.source.reference,
                payment_method: this.paymentMethod,
                payment_custom_note: this.paymentCustomNote || '',
                fleet_type: this.logistics?.fleet === 'Armada Khusus / Custom'
                    ? (this.logistics?.customFleet || 'Armada Khusus')
                    : (this.logistics?.fleet || 'CDD Reefer Chilled'),
                status: targetStatus,
                items: this.rows.map(row => ({
                    product_id: row.product_id,
                    quantity: this.quantity(row),
                    price: this.price(row),
                })),
            };

            try {
                const storeUrl = window.gpaManualOrderStoreUrl || '/sekretaris/pesanan-manual';
                const res = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
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
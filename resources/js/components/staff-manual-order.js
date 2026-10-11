/**
 * Formulir "Input Order Manual" (Sub-01 // Verifikasi Pesanan).
 *
 * Alpine ini menghitung ulang subtotal, berat, dan tagihan secara reaktif,
 * lalu mengunci evaluation Rule 03 (anti overselling) serta Rule 02 (credit
 * limit B2B) sebelum operator boleh meneruskan pesanan ke tahap faktur.
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
) {
    const rows = Array.isArray(commodities) ? commodities.map(clone) : [];
    const catalogList = Array.isArray(catalog) ? catalog.map(clone) : [];
    const logisticsForm = clone(logistics) ?? {};
    const paymentForm = clone(payment) ?? {};
    const sourceForm = clone(source) ?? {};
    const clientForm = clone(client) ?? {};

    return {
        rows,
        catalog: catalogList,
        source: sourceForm,
        client: clientForm,
        logistics: logisticsForm,
        payment: paymentForm,
        evidence: {
            file: paymentForm.file ?? '',
            size: paymentForm.size ?? '',
            hash: paymentForm.hash ?? '',
            hash_note: paymentForm.hash_note ?? '',
        },
        nextKey: 0,
        selectedCatalog: '',
        channel: sourceForm.active ?? '',
        clientMode: clientForm.active_mode ?? 'registered',
        paymentMethod: paymentForm.active ?? '',

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
         | Rule 02 — credit limit B2B
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
         | Rule 03 — anti overselling & minimum order
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
                        `${row.name}: ${this.number(this.quantity(row))} kg melebihi stok sistem ${this.number(row.stock)} kg (Rule 03).`
                    );
                }

                if (! this.minOk(row)) {
                    issues.push(
                        `${row.name}: ${this.number(this.quantity(row))} kg di bawah minimum order ${this.number(row.min_order)} kg.`
                    );
                }
            });

            if (! this.creditOk) {
                issues.push(
                    `Total estimasi ${this.rupiah(this.total)} melampaui sisa plafon ${this.rupiah(this.creditRemaining)} (Rule 02).`
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

            this.rows.push({ ...clone(item), quantity: item.min_order });
            this.nextKey += 1;

            this.run(
                'Baris Komoditas Ditambahkan',
                `${item.name} (${item.sku}) • ${this.kg(item.min_order)} — minimum order sistem diterapkan.`
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

        viewEvidence() {
            if (! this.evidence.file) {
                this.run('Bukti Belum Diunggah', 'Lampirkan screenshot bukti transfer sebelum meneruskan PO.', 'warning');

                return;
            }

            this.run('Bukti Pembayaran Dibuka', `${this.evidence.file} • ${this.evidence.size} • SHA-256 ${this.evidence.hash}`);
        },

        removeEvidence() {
            this.evidence = { file: '', size: '', hash: '', hash_note: '' };

            this.run('Bukti Dihapus', 'Lampiran bukti pembayaran dilepas dari draft PO.', 'warning');
        },

        saveDraft() {
            this.run(
                'Draf PO Disimpan',
                `${this.source.reference} • ${this.number(this.totalWeight, 1)} kg • estimasi ${this.rupiah(this.total)} — disimpan sebagai In Draft Entry.`
            );
        },

        verify() {
            const issues = this.blockers();

            if (issues.length > 0) {
                this.run('Verifikasi Diblokir', issues[0], 'danger');

                return;
            }

            this.run(
                'Verifikasi Order Manual Berhasil',
                `${this.source.reference} diteruskan ke Antrean Verifikasi Pesanan • ${this.number(this.totalWeight, 1)} kg • ${this.rupiah(this.total)} belum termasuk PPN.`
            );
        },

        cancel() {
            this.run('Draft Dibatalkan', `${this.source.reference} dibuang dan tidak masuk ke buku besar operasional.`, 'warning');
        },
    };
}
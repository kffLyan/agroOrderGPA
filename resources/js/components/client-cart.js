import { isCartEmptied, isCartSubmitted, onDraftSynced, readDraft, setCartEmptied, setCartSubmitted, writeDraft } from './draft-store';

const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

export default function clientCart(commodities = [], demoLines = [], defaults = {}) {
    return {
        commodities,
        demoLines,
        lines: [],
        deliveryDate: defaults.date ?? '25 Oktober 2024',
        dockWindow: defaults.window ?? '03:30 - 05:30 WIB',
        dock: defaults.dock ?? 'ciracas',
        driverNote: defaults.driver_note ?? '',
        payment: defaults.payment ?? 'top30',
        pic: defaults.pic ?? 0,
        picOpen: false,
        submitted: false,
        statusLabel: defaults.status ?? 'Drafting',

        init() {
            this.deliveryDate = defaults.date ?? '25 Oktober 2024';
            this.dockWindow = defaults.window ?? '03:30 - 05:30 WIB';
            this.driverNote = defaults.driver_note ?? '';
            this.submitted = isCartSubmitted();
            this.syncStatus();
            this.loadLines();

            this.stopDraftSync = onDraftSynced(() => {
                if (this.lines.length > 0) {
                    return;
                }

                this.loadLines();
            });
        },

        destroy() {
            this.stopDraftSync?.();
        },

        syncStatus() {
            this.statusLabel = this.submitted
                ? (defaults.submitted_status ?? 'Menunggu Verifikasi Admin')
                : (defaults.status ?? 'Drafting');
        },

        money(value) {
            return rupiah.format(value);
        },

        /**
         * Draft PO disimpan browser adalah sumber utama. Baris contoh dari PHP
         * hanya dipakai saat keranjang benar-benar masih kosong.
         */
        loadLines() {
            const stored = readDraft().map((entry) => this.hydrate(entry));

            if (stored.length > 0) {
                this.lines = stored;

                return;
            }

            if (isCartEmptied()) {
                this.lines = [];

                return;
            }

            this.lines = this.demoLines;

            if (this.lines.length > 0) {
                setCartEmptied(false);
                this.persist();
            }
        },

        hydrate(entry) {
            const item = this.commodities.find((candidate) => candidate.key === entry.key);

            if (!item) {
                return { ...entry };
            }

            return {
                ...item,
                ...entry,
                sku: entry.sku ?? item.sku,
                grade: entry.grade ?? item.grade,
                packaging: entry.packaging ?? `Kemasan: ${item.packaging} • Origin: ${item.origin}`,
                cold_chain: entry.cold_chain ?? item.cold_chain,
                stock: entry.stock ?? item.stock,
                moq: entry.moq ?? item.moq,
                step: entry.step ?? item.step,
                crate_kg: entry.crate_kg ?? item.crate_kg,
                pack_label: entry.pack_label ?? item.pack_label,
                price: entry.price ?? item.price,
                qty: entry.qty ?? 0,
                total: (entry.qty ?? 0) * (entry.price ?? item.price),
            };
        },

        persist() {
            writeDraft(this.lines.map((line) => ({
                key: line.key,
                sku: line.sku,
                grade: line.grade,
                name: line.name,
                packaging: line.packaging,
                cold_chain: line.cold_chain,
                price: line.price,
                qty: line.qty,
                total: line.total,
                stock: line.stock,
                moq: line.moq,
                step: line.step,
                crate_kg: line.crate_kg,
                pack_label: line.pack_label,
            })));
        },

        itemCount() {
            return this.lines.length;
        },

        empty() {
            return this.lines.length === 0;
        },

        weight(line) {
            return line.qty;
        },

        crates(line) {
            const size = line.crate_kg || 1;

            return Math.round(line.qty / size);
        },

        packText(line) {
            return `(${this.crates(line)} ${line.pack_label || 'Krat'} @ ${line.crate_kg}kg)`;
        },

        bufferTone(line) {
            return line.qty > (line.stock ?? 0) ? 'caution' : 'success';
        },

        bufferState(line) {
            return line.qty > (line.stock ?? 0) ? 'MELEBIHI' : 'TERPENUHI';
        },

        totalWeight() {
            return this.lines.reduce((total, line) => total + line.qty, 0);
        },

        totalTons() {
            return (this.totalWeight() / 1000).toFixed(2).replace('.', ',');
        },

        subtotal() {
            return this.lines.reduce((total, line) => total + line.qty * line.price, 0);
        },

        shipping() {
            return 0;
        },

        grandTotal() {
            return this.subtotal() + this.shipping();
        },

        setQty(key, value) {
            const line = this.lines.find((entry) => entry.key === key);

            if (!line) {
                return;
            }

            const step = line.step || 1;
            const limit = line.stock ?? Number.MAX_SAFE_INTEGER;
            const next = Math.max(0, Math.min(limit, Number(value) || 0));

            line.qty = Math.max(0, Math.min(limit, Math.round(next / step) * step));
            line.total = line.qty * line.price;

            this.persist();
        },

        stepBy(key, direction) {
            const line = this.lines.find((entry) => entry.key === key);

            if (line) {
                this.setQty(key, line.qty + (line.step || 1) * direction);
            }
        },

        removeLine(key) {
            const line = this.lines.find((entry) => entry.key === key);

            this.lines = this.lines.filter((entry) => entry.key !== key);

            if (this.lines.length === 0) {
                setCartEmptied(true);
            }

            this.persist();

            this.$dispatch('gpa:toast', {
                title: 'Item dihapus',
                message: `${line?.name ?? 'Komoditas'} dikeluarkan dari draft PO.`,
                tone: 'danger',
            });
        },

        clearCart() {
            if (this.lines.length === 0) {
                return;
            }

            this.lines = [];
            setCartEmptied(true);
            setCartSubmitted(false);
            this.submitted = false;
            this.syncStatus();
            this.persist();

            this.$dispatch('gpa:toast', {
                title: 'Keranjang dikosongkan',
                message: 'Seluruh item dihapus dari draft PO.',
                tone: 'danger',
            });
        },

        addFromCatalog() {
            window.location.href = '/katalog';
        },

        changePic(index) {
            this.pic = index;
            this.picOpen = false;
        },

        togglePic() {
            this.picOpen = !this.picOpen;
        },

        submitPo() {
            if (this.empty()) {
                return;
            }

            if (this.overBuffer()) {
                this.$dispatch('gpa:toast', {
                    title: 'Volume melebihi alokasi',
                    message: 'Satu atau lebih item melewati sisa stok buffer. Turunkan volume sebelum submit.',
                    tone: 'danger',
                });

                return;
            }

            setCartSubmitted(true);
            this.submitted = true;
            this.syncStatus();
            this.persist();

            this.$dispatch('gpa:toast', {
                title: 'PO diajukan ke admin',
                message: `${this.totalWeight()} kg menunggu verifikasi SLA < 2 jam kerja.`,
                tone: 'success',
            });
        },

        saveDraft() {
            if (this.empty()) {
                return;
            }

            setCartSubmitted(false);
            this.submitted = false;
            this.syncStatus();
            this.persist();

            this.$dispatch('gpa:toast', {
                title: 'Draf PO disimpan',
                message: 'Ringkasan estimasi tersimpan dan dapat dilanjutkan kapan saja.',
                tone: 'success',
            });
        },

        overBuffer() {
            return this.lines.some((line) => line.qty > (line.stock ?? 0));
        },
    };
}
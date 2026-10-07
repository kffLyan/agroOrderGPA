import { onDraftSynced, readDraft, setCartEmptied, writeDraft } from './draft-store';

const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

const clock = new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false,
    timeZone: 'Asia/Jakarta',
});

export default function clientDashboard(catalogue = []) {
    return {
        sidebarOpen: false,
        syncedAt: '',
        statusFilter: 'all',
        draft: [],
        quantities: Object.fromEntries(
            catalogue.map((item) => [item.key, item.initial_qty]),
        ),

        init() {
            this.tickClock();
            this.timer = window.setInterval(() => this.tickClock(), 1000);
            this.draft = readDraft();
            this.stopDraftSync = onDraftSynced(() => {
                this.draft = readDraft();
            });
        },

        destroy() {
            window.clearInterval(this.timer);
            this.stopDraftSync?.();
        },

        tickClock() {
            this.syncedAt = `${clock.format(new Date())} WIB`;
        },

        money(value) {
            return rupiah.format(value);
        },

        filteredOrders(orders) {
            if (this.statusFilter === 'all') {
                return orders;
            }

            return orders.filter((order) => order.status === this.statusFilter);
        },

        statusOptions(orders) {
            const labels = [...new Set(orders.map((order) => order.status))];

            return [
                { value: 'all', label: `Semua Pesanan Aktif (${orders.length})` },
                ...labels.map((label) => ({
                    value: label,
                    label: `${label} (${orders.filter((order) => order.status === label).length})`,
                })),
            ];
        },

        setQuantity(item, value) {
            const step = item.step || 1;
            const next = Math.max(0, Math.min(item.remaining, Number(value) || 0));
            const rounded = Math.round(next / step) * step;

            this.quantities[item.key] = Math.max(0, Math.min(item.remaining, rounded));
        },

        step(item, direction) {
            const step = item.step || 1;

            this.setQuantity(item, (this.quantities[item.key] ?? 0) + step * direction);
        },

        addToDraft(item) {
            const qty = this.quantities[item.key] ?? 0;

            if (qty <= 0) {
                this.$dispatch('gpa:toast', {
                    title: 'Jumlah belum valid',
                    message: `Isi minimal 1 kg untuk ${item.name}.`,
                    tone: 'danger',
                });

                return;
            }

            const line = this.draft.find((entry) => entry.key === item.key);

            if (line) {
                line.qty = Math.min(item.remaining, line.qty + qty);
                line.total = line.qty * item.price;
            } else {
                this.draft.push({
                    key: String(item.key),
                    id: item.id || (parseInt(String(item.key).replace(/\D/g, '')) || 1),
                    sku: item.sku || `SKU-${item.key}`,
                    grade: item.grade || 'Grade A',
                    name: item.name,
                    packaging: `Krat Plastik Higienis 10 kg • Origin: Lembang`,
                    cold_chain: '2-6°C',
                    price: item.price,
                    qty,
                    total: qty * item.price,
                    stock: item.remaining || 2000,
                });
            }

            setCartEmptied(false);

            this.writeDraft();

            this.$dispatch('gpa:toast', {
                title: 'Masuk ke draft PO',
                message: `${qty} kg ${item.name} ditambahkan ke draft PO.`,
                tone: 'success',
            });
        },

        writeDraft() {
            writeDraft(this.draft);
        },

        removeFromDraft(key) {
            this.draft = this.draft.filter((entry) => entry.key !== key);

            this.writeDraft();
        },

        draftCount() {
            return this.draft.length;
        },

        draftWeight() {
            return this.draft.reduce((total, entry) => total + entry.qty, 0);
        },

        draftTotal() {
            return this.draft.reduce((total, entry) => total + entry.total, 0);
        },

        submitDraft() {
            if (this.draft.length === 0) {
                return;
            }

            this.$dispatch('gpa:toast', {
                title: 'Draft PO tersimpan',
                message: `${this.draftWeight()} kg dikirim ke admin GPA untuk verifikasi.`,
                tone: 'success',
            });

            this.draft = [];

            setCartEmptied(true);

            this.writeDraft();
        },
    };
}
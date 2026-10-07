import { onDraftSynced, readDraft, setCartEmptied, writeDraft } from './draft-store';

const rupiah = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
});

export default function clientCatalog(items = []) {
    return {
        items,
        category: 'all',
        origin: 'all',
        sort: 'stock',
        query: '',
        availableOnly: false,
        quantities: Object.fromEntries(
            items.map((item) => [item.key, item.initial_qty]),
        ),
        draft: [],

        init() {
            this.draft = readDraft();
            this.stopDraftSync = onDraftSynced(() => {
                this.draft = readDraft();
            });
        },

        destroy() {
            this.stopDraftSync?.();
        },

        money(value) {
            return rupiah.format(value);
        },

        find(key) {
            return this.items.find((item) => item.key === key);
        },

        haystack(item) {
            return [
                item.name,
                item.subtitle,
                item.code,
                item.hub_code,
                item.origin,
                item.category_label,
                item.cold_chain,
            ]
                .join(' ')
                .toLowerCase();
        },

        matches(key) {
            const item = this.find(key);

            if (!item) {
                return false;
            }

            if (this.availableOnly && !item.available) {
                return false;
            }

            if (this.category !== 'all' && item.category !== this.category) {
                return false;
            }

            if (this.origin !== 'all' && item.origin !== this.origin) {
                return false;
            }

            const query = this.query.trim().toLowerCase();

            return query === '' || this.haystack(item).includes(query);
        },

        ordered() {
            const visible = this.items.filter((item) => this.matches(item.key));

            switch (this.sort) {
                case 'price_asc':
                    return visible.sort((a, b) => a.price - b.price);
                case 'price_desc':
                    return visible.sort((a, b) => b.price - a.price);
                case 'moq':
                    return visible.sort((a, b) => a.moq - b.moq);
                case 'name':
                    return visible.sort((a, b) => a.name.localeCompare(b.name, 'id'));
                default:
                    return visible.sort((a, b) => b.stock - a.stock);
            }
        },

        visibleCount() {
            return this.items.filter((item) => this.matches(item.key)).length;
        },

        chipClass(tone) {
            switch (tone) {
                case 'safe':
                    return 'bg-accent text-success-ink ring-accent-edge';
                case 'caution':
                    return 'bg-warning-cream text-warning-caution ring-warning-caution/30';
                default:
                    return 'bg-surface-pill text-ink-body ring-line-board';
            }
        },

        toneClass(tone) {
            switch (tone) {
                case 'success':
                    return 'text-success-deep';
                case 'caution':
                    return 'text-warning-caution';
                default:
                    return 'text-ink';
            }
        },

        pillClass(tone) {
            switch (tone) {
                case 'safe':
                    return 'bg-accent text-success-ink ring-accent-edge';
                case 'caution':
                    return 'bg-warning-cream text-warning-caution ring-warning-caution/30';
                default:
                    return 'bg-surface-pill text-ink-body ring-line-board';
            }
        },

        filtered() {
            return this.category !== 'all'
                || this.origin !== 'all'
                || this.query.trim() !== ''
                || this.availableOnly;
        },

        reset() {
            this.category = 'all';
            this.origin = 'all';
            this.sort = 'stock';
            this.query = '';
            this.availableOnly = false;
        },

        quantity(key) {
            return this.quantities[key] ?? 0;
        },

        setQuantity(key, value) {
            const item = this.find(key);

            if (!item) {
                return;
            }

            const step = item.step || 1;
            const next = Math.max(0, Math.min(item.stock, Number(value) || 0));
            const rounded = Math.round(next / step) * step;

            this.quantities[key] = Math.max(0, Math.min(item.stock, rounded));
        },

        stepBy(key, direction) {
            const item = this.find(key);

            if (!item) {
                return;
            }

            this.setQuantity(key, this.quantity(key) + (item.step || 1) * direction);
        },

        estimatedTotal(key) {
            const item = this.find(key);

            return item ? item.price * this.quantity(key) : 0;
        },

        addToDraft(key) {
            const item = this.find(key);
            const qty = this.quantity(key);

            if (!item) {
                return;
            }

            if (qty < item.moq) {
                this.$dispatch('gpa:toast', {
                    title: 'Belum memenuhi MOQ',
                    message: `Minimum order ${item.name} adalah ${item.moq} kg.`,
                    tone: 'danger',
                });

                return;
            }

            const line = this.draft.find((entry) => entry.key === key);

            if (line) {
                line.qty = Math.min(item.stock, line.qty + qty);
                line.total = line.qty * item.price;
            } else {
                const pkg = item.packaging || 'Krat Plastik Higienis 10 kg';
                const orig = item.origin || 'Lembang';
                this.draft.push({
                    key: String(item.key),
                    id: item.id,
                    sku: item.sku || item.code || `SKU-${item.id}`,
                    grade: item.grade || 'Grade A',
                    name: item.name,
                    packaging: `${pkg} • Origin: ${orig}`,
                    cold_chain: item.cold_chain || '2-6°C',
                    price: item.price,
                    qty,
                    total: qty * item.price,
                    stock: item.stock,
                    moq: item.moq,
                    step: item.step,
                    crate_kg: item.crate_kg,
                    pack_label: item.pack_label,
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

            const weight = this.draftWeight();

            setCartEmptied(true);

            this.draft = [];
            this.writeDraft();

            this.$dispatch('gpa:toast', {
                title: 'Draft PO tersimpan',
                message: `${weight} kg dikirim ke admin GPA untuk verifikasi.`,
                tone: 'success',
            });
        },

        exportSpec() {
            this.$dispatch('gpa:toast', {
                title: 'Export spesifikasi disiapkan',
                message: 'Spesifikasi kontrak B2B dikirim ke admin GPA sebagai PDF.',
                tone: 'success',
            });
        },
    };
}
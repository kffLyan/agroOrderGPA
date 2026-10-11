const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryVerification(orders = [], checklistTotal = 0) {
    const list = Array.isArray(orders) ? orders : [];

    return {
        orders: list,
        checklistTotal: Number(checklistTotal) || 0,
        filter: 'all',
        selectedId: list[0]?.id ?? null,
        resolved: [],
        checked: [],

        get selected() {
            return this.orders.find((order) => order.id === this.selectedId) ?? this.orders[0] ?? null;
        },

        isSelected(id) {
            return this.selectedId === id;
        },

        isResolved(id) {
            return this.resolved.includes(id);
        },

        matchesFilter(order) {
            if (this.isResolved(order.id)) {
                return false;
            }

            if (this.filter !== 'all' && order.channel !== this.filter) {
                return false;
            }

            const needle = normalize(this.query).trim();

            if (!needle) {
                return true;
            }

            return [
                order.id,
                order.client,
                order.meta,
                ...order.queue_commodities.map((item) => item.name),
            ]
                .map(normalize)
                .some((value) => value.includes(needle));
        },

        visibleOrders() {
            return this.orders.filter((order) => this.matchesFilter(order));
        },

        pendingCount() {
            return this.orders.filter((order) => ! this.isResolved(order.id)).length;
        },

        visibleCount() {
            return this.visibleOrders().length;
        },

        cardClass(order) {
            if (this.isSelected(order.id)) {
                return 'bg-surface shadow-card outline outline-2 outline-success-deep';
            }

            return 'bg-surface-shell outline outline-1 outline-line-soft hover:outline-line-board';
        },

        filterClass(key) {
            return this.filter === key
                ? 'bg-surface text-ink shadow-sub'
                : 'text-ink-body hover:text-ink';
        },

        select(id) {
            if (this.isSelected(id)) {
                return;
            }

            this.selectedId = id;
            this.checked = [];

            this.$nextTick(() => {
                document.getElementById('order-inspection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },

        nextPending(excludeId) {
            const next = this.visibleOrders().find((order) => order.id !== excludeId) ?? null;

            if (next) {
                this.select(next.id);

                return;
            }

            this.selectedId = this.visibleOrders()[0]?.id ?? null;
            this.checked = [];
        },

        handleStep(step) {
            this.run(step.label, step.message);
        },

        reject() {
            const order = this.selected;

            if (! order) {
                return;
            }

            this.run(
                `${order.id}: Tolak Pesanan`,
                'PO ditolak dan diteruskan ke PIC klien untuk revisi.',
                'danger'
            );

            this.resolved.push(order.id);
            this.nextPending(order.id);
        },

        revise() {
            const order = this.selected;

            if (! order) {
                return;
            }

            this.run(
                `${order.id}: Minta Revisi Klien`,
                `Permintaan revisi ${order.id} dikirim ke PIC ${order.client} melalui kanal resmi.`,
                'warning'
            );
        },

        verify() {
            const order = this.selected;

            if (! order) {
                return;
            }

            if (this.checked.length < this.checklistTotal) {
                this.run(
                    'Hard-Gate Belum Lengkap',
                    'Centang seluruh poin Hard-Gate Checklist Sekretariat sebelum meneruskan PO.',
                    'danger'
                );

                return;
            }

            this.run(
                `${order.id}: Diverifikasi`,
                `PO ${order.id} diteruskan ke Koordinator Lapangan. Surat Jalan tetap terkunci hingga timbangan riil disubmit.`,
                'success'
            );

            this.resolved.push(order.id);
            this.nextPending(order.id);
        },

        draftInvoice() {
            const order = this.selected;

            if (! order) {
                return;
            }

            this.run(
                'Cetak Draft Tagihan',
                `Draft tagihan ${order.client} (${order.deliveries.length} dokumen SJ) disiapkan untuk ditinjau.`
            );
        },

        issueInvoice() {
            const order = this.selected;

            if (! order) {
                return;
            }

            this.run(
                'Terbitkan Invoice Tempo (TOP 30)',
                `Invoice tempo ${order.deliveries_total} untuk ${order.client} masuk antrean penerbitan Finance.`,
                'success'
            );
        },

        restore() {
            this.resolved = [];
            this.selectedId = this.visibleOrders()[0]?.id ?? this.orders[0]?.id ?? null;
            this.checked = [];
        },
    };
}
const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryVerification(orders = [], checklistTotal = 0) {
    const list = Array.isArray(orders) ? orders : [];

    return {
        orders: list,
        checklistTotal: Number(checklistTotal) || 0,
        filter: 'all',
        query: '',
        selectedId: list[0]?.id ?? null,
        resolved: [],
        checked: [],
        manualShipping: 0,

        run(title, message, tone = 'info') {
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('gpa:toast', { title, message, tone });
            } else {
                window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
            }
        },

        get selected() {
            return this.orders.find((order) => order.id === this.selectedId) ?? this.orders[0] ?? null;
        },

        syncManualShipping() {
            const current = this.selected;
            this.manualShipping = Number(current?.shipping_raw ?? 0);
        },

        setShippingPreset(val) {
            this.manualShipping = Math.max(0, Number(val || 0));
        },

        getSubtotal() {
            return Number(this.selected?.subtotal_raw || 0);
        },

        getShipping() {
            return Math.max(0, Number(this.manualShipping || 0));
        },

        getTaxRate() {
            return Number(this.selected?.tax_rate_raw || 0);
        },

        getTaxAmount() {
            const rate = this.getTaxRate();
            if (rate > 0) {
                return Math.round(this.getSubtotal() * (rate / 100));
            }
            return 0;
        },

        getGrandTotal() {
            return this.getSubtotal() + this.getShipping() + this.getTaxAmount();
        },

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
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

        init() {
            // Cek parameter URL atau hash jika diarahkan ke PO tertentu
            const urlParams = new URLSearchParams(window.location.search);
            const targetPo = urlParams.get('po') || urlParams.get('order') || window.location.hash.replace('#', '');
            if (targetPo) {
                const found = this.orders.find((o) => o.id === targetPo || String(o.order_id) === String(targetPo));
                if (found) {
                    this.selectedId = found.id;
                    this.$nextTick(() => {
                        this.scrollToInspection();
                    });
                }
            }
            this.syncManualShipping();
        },

        select(id) {
            this.selectedId = id;
            this.checked = [];
            this.syncManualShipping();

            this.$nextTick(() => {
                this.scrollToInspection();
            });
        },

        scrollToInspection() {
            const el = document.getElementById('order-inspection');
            if (el) {
                const header = document.querySelector('header');
                const headerHeight = header ? header.offsetHeight : 70;
                const elementPosition = el.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - (headerHeight + 20);

                window.scrollTo({
                    top: Math.max(0, offsetPosition),
                    behavior: 'smooth'
                });

                // Efek visual highlight border halus agar perhatian langsung tertuju ke card ini
                el.classList.add('ring-2', 'ring-accent', 'transition-all');
                setTimeout(() => {
                    el.classList.remove('ring-2', 'ring-accent');
                }, 1400);
            }
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

        async reject() {
            const order = this.selected;

            if (! order) {
                return;
            }

            if (order.order_id) {
                const reason = await window.$prompt({
                    title: 'Tolak Verifikasi Pesanan',
                    message: 'Masukkan alasan penolakan pesanan ini untuk diteruskan ke Klien:',
                    defaultValue: 'Kapasitas armada / kuota stok panen tidak mencukupi',
                    placeholder: 'Alasan penolakan...',
                    icon: 'danger',
                    tone: 'danger',
                    confirmText: 'Tolak Pesanan',
                    cancelText: 'Batal',
                });
                if (!reason) {
                    return;
                }

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '';

                try {
                    const response = await fetch(`/sekretaris/orders/${order.order_id}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ rejection_reason: reason }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok || data.success === false) {
                        throw new Error(data.message || 'Gagal menolak pesanan.');
                    }

                    this.run(
                        `Pesanan ${order.id}: Ditolak`,
                        data.message || `Pesanan ${order.id} telah ditolak dan dihapus dari antrean verifikasi.`,
                        'danger'
                    );

                    this.resolved.push(order.id);
                    this.orders = this.orders.filter((o) => o.id !== order.id);
                    this.nextPending(order.id);
                } catch (err) {
                    this.run('Penolakan Gagal', err.message || 'Terjadi kesalahan sistem saat menolak pesanan.', 'danger');
                }
                return;
            }

            this.run(
                `${order.id}: Tolak Pesanan`,
                'Pesanan ditolak dan status pesanan dibatalkan (BATAL).',
                'danger'
            );

            this.resolved.push(order.id);
            this.orders = this.orders.filter((o) => o.id !== order.id);
            this.nextPending(order.id);
        },

        async verify() {
            const order = this.selected;

            if (! order) {
                return;
            }

            if (this.checklistTotal > 0 && this.checked.length < this.checklistTotal) {
                this.run(
                    'Hard-Gate Belum Lengkap',
                    'Centang seluruh poin Hard-Gate Checklist Sekretariat sebelum meneruskan PO.',
                    'danger'
                );

                return;
            }

            if (order.order_id) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '';

                try {
                    const response = await fetch(`/sekretaris/orders/${order.order_id}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            shipping_cost: this.getShipping(),
                        }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok || data.success === false) {
                        throw new Error(data.message || 'Gagal menyetujui pesanan.');
                    }

                    this.run(
                        `Pesanan ${order.id}: Disetujui!`,
                        data.message || `Pesanan ${order.id} berhasil disetujui. Kuota panen dikunci & Invoice tagihan diterbitkan.`,
                        'success'
                    );

                    // Hilangkan pesanan yang sudah diverifikasi dari antrean seketika
                    this.resolved.push(order.id);
                    this.orders = this.orders.filter((o) => o.id !== order.id);
                    this.nextPending(order.id);
                } catch (err) {
                    this.run('Verifikasi Gagal', err.message || 'Terjadi kesalahan sistem saat menyetujui pesanan.', 'danger');
                }
                return;
            }

            this.run(
                `${order.id}: Disetujui`,
                `Pesanan ${order.id} berhasil disetujui. Invoice Tagihan diterbitkan dan menunggu pembayaran lunas dari klien.`,
                'success'
            );

            this.resolved.push(order.id);
            this.orders = this.orders.filter((o) => o.id !== order.id);
            this.nextPending(order.id);
        },

        restore() {
            this.resolved = [];
            this.selectedId = this.visibleOrders()[0]?.id ?? this.orders[0]?.id ?? null;
            this.checked = [];
        },
    };
}
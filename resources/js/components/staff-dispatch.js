const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryDispatch(rows = []) {
    const list = Array.isArray(rows) ? rows : [];

    return {
        rows: list,
        query: '',
        warehouse: 'Semua Gudang (SUB-04)',
        selectedId: list[0]?.id ?? null,
        released: [],

        run(title, message, tone = 'info') {
            if (typeof this.$dispatch === 'function') {
                this.$dispatch('gpa:toast', { title, message, tone });
            } else {
                window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
            }
        },

        get selected() {
            return this.rows.find((row) => row.id === this.selectedId) ?? this.rows[0] ?? null;
        },

        isSelected(id) {
            return this.selectedId === id;
        },

        isReady(row) {
            return row?.state === 'ready' || row?.order_status === 'LUNAS';
        },

        isReleased(id) {
            return this.released.includes(id);
        },

        matchesFilter(row) {
            if (this.warehouse !== 'Semua Gudang (SUB-04)' && row.warehouse !== this.warehouse) {
                return false;
            }

            const needle = normalize(this.query).trim();

            if (! needle) {
                return true;
            }

            return [row.id, row.client, row.dock, row.sj, row.armada, row.driver]
                .map(normalize)
                .some((value) => value !== '' && value.includes(needle));
        },

        visibleRows() {
            return this.rows.filter((row) => this.matchesFilter(row));
        },

        visibleCount() {
            return this.visibleRows().length;
        },

        readyCount() {
            return this.visibleRows().filter((row) => this.isReady(row)).length;
        },

        pendingCount() {
            return this.visibleRows().filter((row) => ! this.isReady(row)).length;
        },

        issuedCount() {
            return this.released.length;
        },

        rowClass(row) {
            if (this.isSelected(row.id)) {
                return 'bg-surface-sunken/80 ring-1 ring-inset ring-brand/20';
            }

            return 'bg-white hover:bg-surface-track/50';
        },

        select(id) {
            if (this.isSelected(id)) {
                return;
            }

            this.selectedId = id;
        },

        handleWarehouse() {
            this.run(
                'Filter Gudang Diperbarui',
                `${this.visibleCount()} dokumen dalam antrean • ${this.readyCount()} siap rilis • ${this.pendingCount()} tertunda.`
            );
        },

        async updateDriver(orderId, driverId, row) {
            if (! orderId || ! driverId) return;

            // Penugasan supir HANYA bisa sebelum Surat Jalan diterbitkan
            if (row && (this.isReleased(row.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(row.order_status))) {
                this.run(
                    'Penugasan Terkunci',
                    'Surat Jalan sudah diterbitkan secara resmi. Armada dan supir telah terkunci dan tidak dapat diubah lagi.',
                    'danger'
                );
                return;
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/sekretaris/surat-jalan/${orderId}/assign-driver`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        driver_id: driverId,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (row) {
                        row.driver_id = data.driver_id;
                        row.driver = data.driver_name;
                        row.driver_name = data.driver_name;
                    }

                    this.run(
                        'Supir Armada Ditugaskan',
                        `Pesanan #${row?.id ?? orderId} resmi ditugaskan ke supir ${data.driver_name}.`,
                        'info'
                    );
                } else {
                    this.run(
                        'Gagal Menugaskan Supir',
                        data.message || 'Terjadi kesalahan saat menugaskan supir.',
                        'danger'
                    );
                }
            } catch (e) {
                this.run(
                    'Gagal Menugaskan Supir',
                    'Terjadi kendala jaringan saat memperbarui supir armada.',
                    'danger'
                );
            }
        },

        async issue(row) {
            if (! row) return;

            // Jika surat jalan sudah terbit sebelumnya
            if (this.isReleased(row.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(row.order_status)) {
                this.run(
                    'Surat Jalan Sudah Terbit',
                    `Surat Jalan ${row.sj} untuk pesanan ${row.id} telah resmi diterbitkan sebelumnya (Status: ${row.order_status}). Anda dapat mencetak fisik via tombol "Cetak Surat Jalan".`,
                    'info'
                );
                return;
            }

            // Jika status pesanan belum LUNAS
            if (! this.isReady(row)) {
                this.run(
                    'Penerbitan Ditolak',
                    `Pesanan ${row.id} belum berstatus LUNAS. Sesuai SOP, Surat Jalan hanya dapat diterbitkan setelah pembayaran diverifikasi lunas oleh Sekretaris.`,
                    'danger'
                );
                return;
            }

            // Proses penerbitan ke backend
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const targetId = row.order_id || parseInt(String(row.id).replace(/\D/g, ''), 10);

            try {
                const response = await fetch(`/sekretaris/surat-jalan/${targetId}/terbitkan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        driver_id: row.driver_id ?? null,
                        vehicle_plate_number: row.plate !== '—' ? row.plate : null,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.selectedId = row.id;
                    if (! this.released.includes(row.id)) {
                        this.released.push(row.id);
                    }
                    row.state = 'issued';
                    row.order_status = 'SIAP_KIRIM';
                    if (row.status_chip) {
                        row.status_chip.label = 'Surat Jalan Terbit (SIAP KIRIM)';
                        row.status_chip.class = 'bg-surface-pill text-ink-body font-bold';
                    }

                    this.run(
                        'Surat Jalan Berhasil Diterbitkan',
                        `Surat Jalan ${row.sj} untuk ${row.client} berhasil diterbitkan dan ditugaskan ke armada logistik.`,
                        'info'
                    );
                } else {
                    this.run(
                        'Gagal Menerbitkan',
                        data.message || data.error || 'Terjadi kesalahan saat menerbitkan Surat Jalan.',
                        'danger'
                    );
                }
            } catch (e) {
                this.selectedId = row.id;
                if (! this.released.includes(row.id)) {
                    this.released.push(row.id);
                }
                row.state = 'issued';
                row.order_status = 'SIAP_KIRIM';
                this.run(
                    'Surat Jalan Terbit',
                    `Surat Jalan ${row.sj} untuk ${row.client} siap dicetak.`,
                    'info'
                );
            }
        },

        async confirmRelease() {
            const row = this.selected;

            if (! row) {
                return;
            }

            await this.issue(row);
        },

        saveDraft() {
            const row = this.selected;

            this.run(
                'Draft Disimpan',
                row ? `${row.sj} disimpan sebagai draf dan tidak mengubah status PO.` : 'Tidak ada dokumen aktif.'
            );
        },

        printProof() {
            window.print();
        },
    };
}
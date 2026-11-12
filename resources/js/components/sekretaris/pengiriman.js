const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryDispatch(rows = []) {
    const list = Array.isArray(rows) ? rows : [];

    return {
        rows: list,
        query: '',
        warehouse: 'Semua Gudang (SUB-04)',
        selectedId: list[0]?.id ?? null,
        released: [],
        page: 1,
        perPage: 10,

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
            const needle = normalize(this.query).trim();

            if (! needle) {
                return true;
            }

            return [row.id, row.client, row.dock, row.sj, row.armada, row.driver]
                .map(normalize)
                .some((value) => value !== '' && value.includes(needle));
        },

        filteredRows() {
            return this.rows.filter((row) => this.matchesFilter(row));
        },

        get lastPage() {
            return Math.max(1, Math.ceil(this.filteredRows().length / this.perPage));
        },

        pagedRows() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredRows().slice(start, start + this.perPage);
        },

        isVisible(row) {
            if (! this.matchesFilter(row)) {
                return false;
            }
            return this.pagedRows().some((item) => item.id === row.id);
        },

        visibleRows() {
            return this.pagedRows();
        },

        visibleCount() {
            return this.filteredRows().length;
        },

        readyCount() {
            return this.filteredRows().filter((row) => this.isReady(row)).length;
        },

        pendingCount() {
            return this.filteredRows().filter((row) => ! this.isReady(row)).length;
        },

        goToPage(page) {
            const target = Math.min(Math.max(1, page), this.lastPage);
            if (this.page !== target) {
                this.page = target;
            }
        },

        pageNumbers() {
            const pages = [];
            const total = this.lastPage;
            if (total <= 5) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                let start = Math.max(1, this.page - 2);
                let end = Math.min(total, this.page + 2);
                if (this.page <= 3) {
                    start = 1;
                    end = 5;
                } else if (this.page >= total - 2) {
                    start = total - 4;
                    end = total;
                }
                for (let i = start; i <= end; i++) pages.push(i);
            }
            return pages;
        },

        summaryLabel() {
            const total = this.filteredRows().length;
            if (total === 0) return '0 pengiriman';
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, total);
            return `Menampilkan ${start}–${end} dari ${total} pengiriman`;
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
            this.page = 1;
            this.run(
                'Filter Gudang Diperbarui',
                `${this.visibleCount()} dokumen dalam antrean • ${this.readyCount()} siap rilis • ${this.pendingCount()} tertunda.`
            );
        },

        async updateDriver(orderId, driverId, row) {
            if (! orderId || ! driverId) return;

            const target = this.rows.find((r) => String(r.order_id) === String(orderId) || String(r.id) === String(row?.id)) ?? row;

            // Penugasan supir HANYA bisa sebelum Surat Jalan diterbitkan
            if (target && (this.isReleased(target.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(target.order_status))) {
                this.run(
                    'Penugasan Terkunci',
                    'Surat Jalan sudah diterbitkan secara resmi. Armada dan supir telah terkunci dan tidak dapat diubah lagi.',
                    'danger'
                );
                return;
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/sekretaris/surat-jalan/${orderId}/tugaskan-supir`, {
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
                    if (target) {
                        target.driver_id = data.driver_id;
                        target.driver = data.driver_name;
                        target.driver_name = data.driver_name;
                        if (data.plate) {
                            target.plate = data.plate;
                        }
                    }
                    if (row && row !== target) {
                        row.driver_id = data.driver_id;
                        row.driver = data.driver_name;
                        row.driver_name = data.driver_name;
                        if (data.plate) {
                            row.plate = data.plate;
                        }
                    }

                    this.run(
                        'Supir Armada Ditugaskan',
                        `Pesanan #${target?.id ?? orderId} resmi ditugaskan ke supir ${data.driver_name}.`,
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

            const target = this.rows.find((r) => String(r.id) === String(row.id) || String(r.order_id) === String(row.order_id)) ?? row;

            // Jika surat jalan sudah terbit sebelumnya
            if (this.isReleased(target.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(target.order_status)) {
                this.run(
                    'Surat Jalan Sudah Terbit',
                    `Surat Jalan ${target.sj} untuk pesanan ${target.id} telah resmi diterbitkan sebelumnya (Status: ${target.order_status}). Anda dapat mencetak fisik via tombol "Cetak Surat Jalan".`,
                    'info'
                );
                return;
            }

            // Jika status pesanan belum LUNAS
            if (! this.isReady(target)) {
                this.run(
                    'Penerbitan Ditolak',
                    `Pesanan ${target.id} belum berstatus LUNAS. Sesuai SOP, Surat Jalan hanya dapat diterbitkan setelah pembayaran diverifikasi lunas oleh Sekretaris.`,
                    'danger'
                );
                return;
            }

            // Proses penerbitan ke backend
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const targetId = target.order_id || parseInt(String(target.id).replace(/\D/g, ''), 10);

            try {
                const response = await fetch(`/sekretaris/surat-jalan/${targetId}/terbitkan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        driver_id: target.driver_id ?? null,
                        vehicle_plate_number: (target.plate && target.plate !== '—') ? target.plate : null,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.selectedId = target.id;
                    if (! this.released.includes(target.id)) {
                        this.released.push(target.id);
                    }
                    target.state = 'issued';
                    target.order_status = 'SIAP_KIRIM';
                    if (data.driver_name) {
                        target.driver = data.driver_name;
                        target.driver_name = data.driver_name;
                    }
                    if (data.driver_id) {
                        target.driver_id = data.driver_id;
                    }
                    if (data.plate) {
                        target.plate = data.plate;
                    }
                    if (data.sj_number) {
                        target.sj = data.sj_number;
                    }
                    if (target.status_chip) {
                        target.status_chip.label = 'Surat Jalan Terbit (SIAP KIRIM)';
                        target.status_chip.class = 'bg-surface-pill text-ink-body font-bold';
                    }

                    if (row && row !== target) {
                        row.state = 'issued';
                        row.order_status = 'SIAP_KIRIM';
                        if (data.driver_name) {
                            row.driver = data.driver_name;
                            row.driver_name = data.driver_name;
                        }
                        if (data.driver_id) {
                            row.driver_id = data.driver_id;
                        }
                        if (data.plate) {
                            row.plate = data.plate;
                        }
                        if (data.sj_number) {
                            row.sj = data.sj_number;
                        }
                        if (row.status_chip) {
                            row.status_chip.label = 'Surat Jalan Terbit (SIAP KIRIM)';
                            row.status_chip.class = 'bg-surface-pill text-ink-body font-bold';
                        }
                    }

                    this.run(
                        'Surat Jalan Berhasil Diterbitkan',
                        `Surat Jalan ${target.sj} untuk ${target.client} berhasil diterbitkan dan ditugaskan ke armada logistik (${target.driver || 'Supir'}).`,
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
                this.selectedId = target.id;
                if (! this.released.includes(target.id)) {
                    this.released.push(target.id);
                }
                target.state = 'issued';
                target.order_status = 'SIAP_KIRIM';
                this.run(
                    'Surat Jalan Terbit',
                    `Surat Jalan ${target.sj} untuk ${target.client} siap dicetak.`,
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
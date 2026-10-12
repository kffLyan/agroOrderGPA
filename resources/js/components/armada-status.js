export default function armadaStatus() {
    return {
        syncCount: 15,
        closed: false,

        /* ---------- derived state ---------- */

        syncLabel() {
            return this.syncCount + 'S';
        },

        /* ---------- actions ---------- */

        refreshTelemetry() {
            this.syncCount = 15;
            this.run(
                'Sinkronisasi Sensor',
                'Status sensor chiller box, kapasitas muat, dan posisi kendaraan diperbarui.',
                'success',
            );
        },

        openReport() {
            this.run(
                'Lapor Kendala',
                'Jika ada kendala kendaraan atau jalan, segera hubungi kontak dispatch operasional.',
                'info',
            );
        },

        callDispatch(phone = '021-884-9021') {
            window.location.href = 'tel:' + phone.replace(/[^0-9+]/g, '');
        },

        openSop() {
            this.run(
                'Panduan SOP Mutu',
                'SOP Cold-Chain: Pastikan suhu chiller selalu di rentang +2°C hingga +6°C selama pengantaran sayur segar.',
                'info',
            );
        },

        closeShift() {
            if (this.closed) {
                return;
            }

            this.closed = true;
            this.run(
                'Shift Ditutup',
                'Tugas harian armada telah diselesaikan. Terima kasih atas kerja keras hari ini!',
                'success',
            );
        },

        openMenu(label) {
            this.run(String(label), 'Modul armada siap digunakan untuk awak armada.', 'info');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

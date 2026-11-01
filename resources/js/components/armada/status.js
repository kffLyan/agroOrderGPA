export default function armadaStatus() {
    return {
        syncCount: 15,
        closed: false,

        /* ---------- derived state ---------- */

        syncLabel() {
            return this.syncCount + 'S';
        },

        /* ---------- actions ---------- */

        refreshStatus() {
            this.run(
                'Status Diperbarui',
                'Status operasional armada dan kapasitas muatan berhasil diperbarui.',
                'success',
            );
        },

        refreshTelemetry() {
            this.refreshStatus();
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
                'Pastikan barang pesanan disusun rapi, terlindung dari terik matahari dan hujan, serta sesuai Surat Jalan.',
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

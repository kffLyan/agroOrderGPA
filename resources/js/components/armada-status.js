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
                'Sinkronisasi Telemetri',
                'Sensor IoT disegarkan ulang. Chiller, tonase, bahan bakar, dan GPS terbaca ulang.',
                'success',
            );
        },

        openReport() {
            this.run(
                'Lapor Kendala',
                'Form laporan kendala kendaraan dibuka untuk awak armada saat ini.',
                'info',
            );
        },

        callDispatch() {
            this.run('Dispatch Hub Bogor-04', 'Menyalakan panggilan Ext-402 / 021-884-9021.', 'info');
        },

        openSop() {
            this.run('SOP DOC_V3', 'Panduan penanganan mutu dan cold-chain dibuka.', 'info');
        },

        closeShift() {
            if (this.closed) {
                return;
            }

            this.closed = true;

            this.run(
                'Shift Ditutup',
                'Rekap shift R-01 tersimpan. Pastikan seluruh PoD terunggah dan suhu akhir reefer tercatat.',
                'success',
            );
        },

        openMenu(label) {
            this.run(String(label), 'Modul armada ini sedang disiapkan untuk awak armada.', 'info');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}
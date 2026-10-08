export default function armadaDispatch(gatePass = {}) {
    return {
        secondsLeft: (Number(gatePass.expiry_minutes) || 45) * 60,
        started: false,
        timer: null,

        /* ---------- lifecycle ---------- */

        init() {
            this.timer = window.setInterval(() => {
                if (this.secondsLeft > 0) {
                    this.secondsLeft -= 1;
                }
            }, 1000);
        },

        destroy() {
            if (this.timer) {
                window.clearInterval(this.timer);
                this.timer = null;
            }
        },

        /* ---------- derived state ---------- */

        expiryLabel() {
            const minutes = Math.floor(this.secondsLeft / 60);
            const seconds = this.secondsLeft % 60;

            return minutes + ':' + String(seconds).padStart(2, '0');
        },

        expired() {
            return this.secondsLeft <= 0;
        },

        /* ---------- actions ---------- */

        callPic() {
            this.run('Hubungi PIC', 'Menyalakan panggilan ke ' + (gatePass.pic_phone ?? '') + '.', 'info');
        },

        openMap() {
            this.run(
                'Navigasi Peta',
                'Membuka rute Dock-03 ke Central Kitchen Ciracas Hub, Jl. Raya Bogor KM 28.',
                'info',
            );
        },

        verifySeal() {
            this.run(
                'Segel Terverifikasi',
                'Segel digital container terkunci otomatis. Gate pass siap dipindai di pos security dock.',
                'success',
            );
        },

        startRoute() {
            if (this.started) {
                return;
            }

            this.started = true;

            this.run(
                'Pengiriman Dimulai',
                'Status GPS logbook dan reefer tracker aktif terkoneksi. Rute menuju STOP #1 dibuka.',
                'success',
            );
        },

        reportIssue() {
            this.run(
                'Lapor Kendala',
                'Form laporan kendala atau selisih muatan dibuka untuk ' + (gatePass.sj_number ?? 'surat jalan') + '.',
                'info',
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
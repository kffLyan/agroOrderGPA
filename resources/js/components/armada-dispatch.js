export default function armadaDispatch(gatePass = {}) {
    return {
        secondsLeft: (Number(gatePass.expiry_minutes) || 45) * 60,
        started: Boolean(gatePass.is_started),
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

        callPic(phone) {
            const targetPhone = phone || gatePass.pic_phone;
            if (targetPhone) {
                window.location.href = 'tel:' + targetPhone.replace(/[^0-9+]/g, '');
            } else {
                this.run('Hubungi PIC', 'Nomor telepon PIC tidak tersedia.', 'warning');
            }
        },

        openMap(address) {
            const dest = address || gatePass.destination_address;
            if (dest) {
                window.open('https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(dest), '_blank');
            } else {
                this.run(
                    'Navigasi Peta',
                    'Membuka panduan navigasi ke alamat tujuan klien.',
                    'info',
                );
            }
        },

        verifySeal() {
            this.run(
                'Segel Terverifikasi',
                'Segel digital boks kendaraan terkunci otomatis. Gate pass siap dipindai di pos keluar.',
                'success',
            );
        },

        startRoute() {
            if (this.started) {
                this.run('Pengiriman Berjalan', 'Perjalanan sudah dimulai.', 'info');
                return;
            }

            const form = document.getElementById('start-route-form');
            if (form) {
                this.started = true;
                form.submit();
                return;
            }

            this.started = true;
            this.run(
                'Pengiriman Dimulai',
                'Status pengiriman diubah menjadi Dalam Pengiriman.',
                'success',
            );
        },

        reportIssue() {
            this.run(
                'Lapor Kendala',
                'Silakan hubungi koordinator dispatch atau catat kendala pada nomor darurat hub.',
                'info',
            );
        },

        openMenu(label) {
            this.run(String(label), 'Modul armada siap digunakan untuk awak pengiriman.', 'info');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

export default function armadaTasks(filters = []) {
    return {
        filter: 'all',

        /* ---------- derived state ---------- */

        counts() {
            return filters.reduce((acc, item) => {
                acc[item.key] = Number(item.count) || 0;
                return acc;
            }, {});
        },

        labelFor(key) {
            return filters.find((item) => item.key === key)?.label ?? String(key);
        },

        /* ---------- actions ---------- */

        selectFilter(key) {
            this.filter = String(key);
            this.run(
                'Filter Antrean',
                key === 'all'
                    ? 'Menampilkan seluruh titik pengantaran pada tugas berjalan.'
                    : 'Menampilkan antrean berstatus ' + this.labelFor(key) + '.',
                'info',
            );
        },

        checkRoute(drop) {
            if (drop?.address) {
                const url = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(drop.address);
                window.open(url, '_blank');
            } else {
                this.run(
                    'Cek Rute',
                    'Rute pengantaran ke ' + (drop?.client ?? 'tujuan') + ' sedang disiapkan untuk navigasi.',
                    'info',
                );
            }
        },

        processPod(drop) {
            if (drop?.primary?.locked) {
                this.run(
                    'PoD Belum Tersedia',
                    'Pesanan belum dalam status siap penyerahan.',
                    'warning',
                );
                return;
            }

            if (drop?.order_id) {
                window.location.href = '/armada/scan-pod?order_id=' + drop.order_id;
                return;
            }

            window.location.href = '/armada/scan-pod';
        },

        callSupport(phone = '021-890-1234') {
            window.location.href = 'tel:' + phone.replace(/[^0-9+]/g, '');
        },

        openMenu(label) {
            this.run(String(label), 'Modul armada siap digunakan untuk operasional lapangan.', 'info');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

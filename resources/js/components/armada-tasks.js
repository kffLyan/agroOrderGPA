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
                    ? 'Menampilkan seluruh titik bongkar pada manifest berjalan.'
                    : 'Menampilkan antrean berstatus ' + this.labelFor(key) + '.',
                'info',
            );
        },

        checkRoute(drop) {
            this.run(
                'Cek Rute',
                'Rute alternatif ke ' + (drop?.client ?? 'tujuan') + ' sedang disiapkan untuk navigasi.',
                'info',
            );
        },

        processPod(drop) {
            if (drop?.primary?.locked) {
                this.run(
                    'PoD Terkunci',
                    'Dokumen masih terkunci sampai Drop #1 selesai. Selesaikan drop pertama lebih dulu.',
                    'warning',
                );

                return;
            }

            this.run(
                'Proses PoD Drop',
                'Membuka alur bukti terima untuk ' + (drop?.sj ?? 'surat jalan') + '.',
                'success',
            );
        },

        callSupport() {
            this.run('Hubungi Dispatch', 'Menyalakan panggilan ke (021) 884-9021.', 'info');
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
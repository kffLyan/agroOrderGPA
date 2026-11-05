let counter = 0;
let lastPushed = { title: '', message: '', time: 0 };

export default function gpaToast() {
    return {
        items: [],

        push(detail = {}) {
            const now = Date.now();
            const title = detail.title ?? 'Pemberitahuan';
            const message = detail.message ?? detail.description ?? '';
            const isMobile = typeof window !== 'undefined' && window.innerWidth < 640;

            // Cegah duplikasi notifikasi identik yang terpanggil dalam jeda < 800ms
            if (lastPushed.title === title && lastPushed.message === message && (now - lastPushed.time) < 800) {
                return;
            }
            lastPushed = { title, message, time: now };

            // Pada perangkat mobile, batasi maksimal 1 notifikasi agar tidak menumpuk menutupi layar.
            // Jika ada notifikasi sebelumnya, langsung digantikan oleh notifikasi yang baru.
            const maxItems = isMobile ? 1 : 3;

            if (isMobile) {
                this.items = [];
            } else {
                // Pada desktop, hapus notifikasi lama dengan judul yang sama (misal ganti filter)
                this.items = this.items.filter((i) => i.title !== title);
                while (this.items.length >= maxItems) {
                    this.items.shift();
                }
            }

            const id = ++counter;

            const item = {
                id,
                title,
                message,
                tone: detail.tone ?? detail.type ?? 'info',
                time: detail.time ?? 'Baru saja',
                exiting: false,
                manualClosed: false,
            };

            this.items.push(item);

            // Durasi otomatis: lebih cepat pada mobile (2600ms) agar tidak menghalangi navigasi layar
            const duration = detail.duration ?? (isMobile ? 2600 : 4200);

            // Animasi OUT otomatis saat timeout
            window.setTimeout(() => {
                const target = this.items.find((i) => i.id === id);
                if (target && !target.manualClosed) {
                    target.exiting = true;
                    // Setelah durasi animasi out selesai (280ms), hapus dari array
                    window.setTimeout(() => {
                        this.items = this.items.filter((i) => i.id !== id);
                    }, 280);
                }
            }, duration);
        },

        // Tutup manual oleh pengguna: langsung ditutup seketika tanpa animasi out lambat
        closeManual(id) {
            const target = this.items.find((i) => i.id === id);
            if (target) {
                target.manualClosed = true;
            }
            this.items = this.items.filter((item) => item.id !== id);
        },

        dismiss(id) {
            this.closeManual(id);
        },
    };
}

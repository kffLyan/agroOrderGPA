let counter = 0;
let lastPushed = { title: '', message: '', time: 0 };

export default function gpaToast() {
    return {
        items: [],

        push(detail = {}) {
            const now = Date.now();
            const title = detail.title ?? 'Pemberitahuan';
            const message = detail.message ?? detail.description ?? '';

            // Cegah duplikasi notifikasi identik yang terpanggil dalam jeda < 800ms
            if (lastPushed.title === title && lastPushed.message === message && (now - lastPushed.time) < 800) {
                return;
            }
            lastPushed = { title, message, time: now };

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

            if (this.items.length > 3) {
                this.items.shift();
            }

            const duration = detail.duration ?? 4500;

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

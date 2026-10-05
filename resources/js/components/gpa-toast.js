let counter = 0;

export default function gpaToast() {
    return {
        items: [],

        push(detail = {}) {
            const id = ++counter;

            this.items.push({
                id,
                title: detail.title ?? 'Pemberitahuan',
                message: detail.message ?? '',
                tone: detail.tone ?? 'info',
            });

            if (this.items.length > 3) {
                this.items.shift();
            }

            window.setTimeout(() => this.dismiss(id), detail.duration ?? 5200);
        },

        dismiss(id) {
            this.items = this.items.filter((item) => item.id !== id);
        },
    };
}

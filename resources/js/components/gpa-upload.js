export default (config = {}) => ({
    files: [],
    dragging: false,
    error: '',
    accept: config.accept ?? '',
    multiple: Boolean(config.multiple),
    maxSizeMb: Number(config.maxSizeMb) || 8,

    init() {
        this.$refs.input?.addEventListener('change', (event) => this.absorb(event.target.files));
    },

    get hasFiles() {
        return this.files.length > 0;
    },

    browse() {
        this.$refs.input?.click();
    },

    absorb(list) {
        const incoming = Array.from(list ?? []);

        this.error = '';

        if (! this.multiple) {
            this.files = [];
        }

        for (const file of incoming) {
            if (! this.allowed(file)) {
                this.error = `${file.name} tidak memenuhi jenis atau batas ukuran berkas.`;
                continue;
            }

            this.files.push(file);
        }

        this.sync();
    },

    handleDrop(event) {
        this.dragging = false;
        this.absorb(event.dataTransfer?.files);
    },

    remove(index) {
        this.files.splice(index, 1);
        this.error = '';
        this.sync();
    },

    clear() {
        this.files = [];
        this.error = '';
        this.sync();
    },

    allowed(file) {
        if (file.size > this.maxSizeMb * 1024 * 1024) {
            return false;
        }

        if (! this.accept) {
            return true;
        }

        return this.accept.split(',').some((rule) => {
            const token = rule.trim().toLowerCase();

            if (!token) {
                return false;
            }

            if (token.startsWith('.')) {
                return file.name.toLowerCase().endsWith(token);
            }

            if (token.endsWith('/*')) {
                return file.type.toLowerCase().startsWith(token.slice(0, -1));
            }

            return file.type.toLowerCase() === token;
        });
    },

    sync() {
        const input = this.$refs.input;

        if (!input) {
            return;
        }

        input.value = '';

        if (typeof DataTransfer === 'undefined') {
            return;
        }

        const transfer = new DataTransfer();

        this.files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    },

    humanSize(bytes) {
        if (bytes < 1024) {
            return `${bytes} B`;
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    },
});
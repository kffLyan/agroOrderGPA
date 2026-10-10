export default function armadaPod(steps = []) {
    return {
        activeStep: 1,
        condition: 'clear',
        signature: '',
        captured: {},

        /* ---------- derived state ---------- */

        manifestCaptured() {
            return Boolean(this.captured.manifest);
        },

        dockCaptured() {
            return Boolean(this.captured.dock);
        },

        signed() {
            return this.signature.trim().length > 0;
        },

        checklistDone(index) {
            return this.manifestCaptured() || index === 0;
        },

        canLock() {
            return this.manifestCaptured() && this.dockCaptured() && this.signed();
        },

        blockers() {
            const missing = [];

            if (! this.manifestCaptured()) {
                missing.push('Foto Surat Jalan');
            }

            if (! this.dockCaptured()) {
                missing.push('Foto Dock Muatan');
            }

            if (! this.signed()) {
                missing.push('Tanda Tangan Penerima');
            }

            return missing;
        },

        /* ---------- actions ---------- */

        capture(key) {
            this.captured = { ...this.captured, [String(key)]: true };

            const label = key === 'dock' ? 'Foto Dock Muatan' : 'Foto Surat Jalan';

            this.run(
                label,
                'Frame terkunci ke ledger PoD. Foto tidak dapat dihapus setelah PoD divalidasi.',
                'success',
            );

            this.scrollTo(key);
        },

        selectCondition(key) {
            this.condition = String(key);

            this.run(
                'Status Kondisi Muatan',
                this.condition === 'clear'
                    ? 'Penerima menyatakan barang diterima utuh tanpa retur.'
                    : 'Selisih tercatat. Form input kuantitas ditolak dan wajib verifikasi alasan fisik.',
                this.condition === 'clear' ? 'success' : 'warning',
            );
        },

        sign(name) {
            this.signature = String(name ?? '');

            this.run(
                'Tanda Tangan Digital',
                this.signed()
                    ? 'Tanda tangan penerima tersimpan pada PoD electronics.'
                    : 'Tanda tangan dikosongkan. Penerima harus menandatangani di atas kanvas.',
                this.signed() ? 'success' : 'warning',
            );
        },

        clearSignature() {
            this.signature = '';
            this.run('Hapus Tanda Tangan', 'Kanvas tanda tangan dikosongkan.', 'info');
        },

        lockPod() {
            if (! this.canLock()) {
                this.run(
                    'PoD Belum Lengkap',
                    'Langkah wajib yang belum terpenuhi: ' + this.blockers().join(', ') + '.',
                    'danger',
                );

                return;
            }

            this.run(
                'Lock PoD',
                'Pengiriman SJ-GPA-202610-0001 di-lock dan ditandai selesai. Invoice B2B terbit di Pusat.',
                'success',
            );
        },

        callHub() {
            this.run('Hub Bogor', 'Menyalakan panggilan ke 0811-9988-77.', 'info');
        },

        openMenu(label) {
            this.run(String(label), 'Modul armada ini sedang disiapkan untuk awak armada.', 'info');
        },

        scrollTo(key) {
            const node = document.getElementById('pod-step-' + String(key));

            if (node && typeof node.scrollIntoView === 'function') {
                node.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}
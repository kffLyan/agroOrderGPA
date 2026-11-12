export default function armadaPod(steps = []) {
    return {
        activeStep: 1,
        condition: 'clear',
        receiverName: '',
        captured: {
            manifest: false,
            dock: false,
        },
        previews: {
            manifest: null,
            dock: null,
        },

        /* ---------- derived state ---------- */

        manifestCaptured() {
            return Boolean(this.captured.manifest);
        },

        dockCaptured() {
            return Boolean(this.captured.dock);
        },

        checklistDone(index) {
            return this.manifestCaptured() || index === 0;
        },

        canLock() {
            return this.manifestCaptured() && this.dockCaptured();
        },

        blockers() {
            const missing = [];

            if (!this.manifestCaptured()) {
                missing.push('Foto Fisik Surat Jalan (Cap & Tanda Tangan)');
            }

            if (!this.dockCaptured()) {
                missing.push('Foto Serah Terima / Muatan Dock');
            }

            return missing;
        },

        /* ---------- actions ---------- */

        handlePhotoUpload(event, key) {
            const file = event.target.files?.[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previews[key] = e.target.result;
                    this.captured[key] = true;
                    this.run(
                        key === 'dock' ? 'Foto Muatan Diunggah' : 'Foto Surat Jalan Diunggah',
                        'Berkas foto berhasil ditangkap.',
                        'success'
                    );
                };
                reader.readAsDataURL(file);
            }
        },

        capture(key) {
            const fileInput = document.getElementById('photo-input-' + key);
            if (fileInput) {
                fileInput.click();
                return;
            }

            this.captured = { ...this.captured, [String(key)]: true };
            const label = key === 'dock' ? 'Foto Dock Muatan' : 'Foto Surat Jalan';
            this.run(
                label,
                'Foto berhasil ditangkap dan siap diverifikasi.',
                'success',
            );
            this.scrollTo(key);
        },

        selectCondition(key) {
            this.condition = String(key);
            const hiddenCond = document.getElementById('hidden-condition');
            if (hiddenCond) {
                hiddenCond.value = this.condition;
            }

            this.run(
                'Status Kondisi Muatan',
                this.condition === 'clear'
                    ? 'Penerima menyatakan barang diterima utuh tanpa retur.'
                    : 'Catatan selisih/retur dipilih. Keterangan fisik akan dicatat pada berita acara.',
                this.condition === 'clear' ? 'success' : 'warning',
            );
        },

        lockPod() {
            if (!this.canLock()) {
                this.run(
                    'PoD Belum Lengkap',
                    'Langkah wajib yang belum terpenuhi: ' + this.blockers().join(', ') + '.',
                    'danger',
                );
                return;
            }

            const form = document.getElementById('pod-submit-form');
            if (form) {
                form.submit();
                return;
            }

            this.run(
                'PoD Berhasil Divalidasi',
                'Pengiriman ditandai selesai. Dokumen bukti terima resmi tersimpan.',
                'success',
            );
        },

        callHub(phone = '0811-9988-77') {
            window.location.href = 'tel:' + phone.replace(/[^0-9+]/g, '');
        },

        openMenu(label) {
            this.run(String(label), 'Modul armada siap digunakan untuk awak armada.', 'info');
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

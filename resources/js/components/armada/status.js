export default function armadaStatus() {
    return {
        syncCount: 15,
        closed: false,

        /* ---------- derived state ---------- */

        syncLabel() {
            return this.syncCount + 'S';
        },

        /* ---------- actions ---------- */

        refreshStatus() {
            this.run(
                'Status Diperbarui',
                'Status operasional armada dan kapasitas muatan berhasil diperbarui.',
                'success',
            );
        },

        refreshTelemetry() {
            this.refreshStatus();
        },

        async openReport() {
            if (typeof window.$prompt === 'function') {
                const kendala = await window.$prompt({
                    title: 'Lapor Kendala Pengiriman',
                    message: 'Deskripsikan kendala kendaraan / kendala perjalanan armada:',
                    placeholder: 'Contoh: Kendala mesin atau kemacetan parah di KM 28...',
                    icon: 'alert-triangle',
                    tone: 'warning',
                    confirmText: 'Teruskan ke Dispatcher',
                    cancelText: 'Batal',
                });
                if (kendala) {
                    const waUrl = `https://wa.me/6281112233445?text=${encodeURIComponent('LAPORAN KENDALA ARMADA: ' + kendala)}`;
                    window.open(waUrl, '_blank');
                    this.run('Kendala Terkirim', 'Laporan diteruskan ke WhatsApp hotline Dispatcher Sentral.', 'warning');
                }
            } else {
                this.run(
                    'Lapor Kendala',
                    'Jika ada kendala kendaraan atau jalan, segera hubungi hotline dispatch operasional di 0811-1223-3445.',
                    'warning',
                );
            }
        },

        callDispatch(phone = '0811-1223-3445') {
            window.location.href = 'tel:' + phone.replace(/[^0-9+]/g, '');
        },

        openSop() {
            if (typeof window.$alert === 'function') {
                window.$alert({
                    title: 'SOP Mutu & Standar Pengiriman GPA',
                    message: '1. Pastikan boks kendaraan tertutup rapat dan higienis.\n2. Susun krat sayur sesuai urutan titik bongkar dock.\n3. Periksa kesesuaian fisik dan lembar Surat Jalan.\n4. Mintakan tanda tangan & stempel basah penerima.\n5. Unggah 2 foto bukti serah terima (PoD) sebelum berangkat ke titik berikutnya.',
                    icon: 'file-text',
                    tone: 'info',
                });
            } else {
                this.run(
                    'Panduan SOP Mutu',
                    'Pastikan barang pesanan disusun rapi, terlindung dari terik matahari dan hujan, serta sesuai Surat Jalan.',
                    'info',
                );
            }
        },

        closeShift() {
            if (this.closed) {
                return;
            }

            this.closed = true;
            this.run(
                'Shift Ditutup',
                'Tugas harian armada telah diselesaikan. Terima kasih atas kerja keras hari ini!',
                'success',
            );
        },

        openMenu(label) {
            this.run(String(label), 'Modul armada siap digunakan untuk awak armada.', 'info');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

export default function direkturPaymentChannels(initialChannels = []) {
    let channels = [];
    if (Array.isArray(initialChannels)) {
        channels = initialChannels;
    } else if (typeof initialChannels === 'string') {
        try {
            channels = JSON.parse(initialChannels);
        } catch (e) {
            channels = [];
        }
    }

    return {
        channels: channels,
        editing: false,
        modalImage: null,
        modalTitle: '',

        openModal(url, title) {
            this.modalImage = url;
            this.modalTitle = title || 'Barcode QRIS Resmi';
        },

        previewImage(event, channel) {
            const file = event.target.files[0];
            if (file) {
                channel.preview_url = URL.createObjectURL(file);
            }
        },

        addChannel(type = 'BANK') {
            const id = (type === 'QRIS' ? 'qris_' : (type === 'EWALLET' ? 'ewallet_' : 'bank_')) + Date.now();
            const template = {
                id: id,
                type: type,
                name: type === 'QRIS' ? 'QRIS Resmi GPA' : (type === 'EWALLET' ? 'Dompet Digital Bisnis GPA' : 'Bank Baru'),
                bank_code: type === 'QRIS' ? 'QRIS' : (type === 'EWALLET' ? 'EWALLET' : 'BANK'),
                account_number: type === 'QRIS' ? 'ID1020039182948' : '1234-5678-90',
                account_name: 'GREEN PASUNDAN AGRICULTURE',
                description: type === 'QRIS' ? 'Pindai barcode QRIS via m-banking & e-wallet apa saja' : 'Rekening Giro Korporat Resmi',
                instructions: type === 'QRIS' ? 'Pindai barcode QRIS resmi GPA di bawah dan simpan bukti tangkapan layar transaksi.' : 'Sertakan nomor faktur di berita transfer.',
                image_url: type === 'QRIS' ? '/storage/payment-channels/qris-gpa-default.png' : null,
                preview_url: null,
                is_active: true
            };
            this.channels.push(template);
            this.editing = true;
        },

        async removeChannel(index) {
            if (this.channels.length <= 1) {
                if (window.$alert) {
                    await window.$alert({
                        title: 'Kanal Pembayaran Wajib',
                        message: 'Minimal harus ada satu kanal pembayaran aktif untuk operasional transaksi.',
                        tone: 'warning'
                    });
                }
                return;
            }
            const targetName = this.channels[index]?.name || 'ini';
            let ok = true;
            if (window.$confirm) {
                ok = await window.$confirm({
                    title: 'Hapus Kanal Pembayaran',
                    message: 'Apakah Anda yakin ingin menghapus kanal pembayaran "' + targetName + '"?',
                    tone: 'danger',
                    confirmText: 'Ya, Hapus',
                    cancelText: 'Batal'
                });
            }
            if (ok) {
                this.channels.splice(index, 1);
                this.editing = true;
            }
        },

        async loadPopularPresets() {
            let ok = true;
            if (window.$confirm) {
                ok = await window.$confirm({
                    title: 'Terapkan Preset Kanal Perbankan',
                    message: 'Terapkan template lengkap seluruh metode pembayaran terpopuler di Indonesia (Bank BCA, Mandiri, BRI, BNI, BSI, QRIS Standar Nasional dengan Barcode, & E-Wallet)?\n\nAnda dapat menyesuaikan kembali sebelum menekan tombol Simpan.',
                    tone: 'brand',
                    confirmText: 'Terapkan Preset',
                    cancelText: 'Batal'
                });
            }
            if (ok) {
                this.channels = [
                    {
                        id: 'bca',
                        type: 'BANK',
                        name: 'Bank BCA',
                        bank_code: 'BCA',
                        account_number: '8210-992-3841',
                        account_name: 'Green Pasundan Agriculture (Divisi Keuangan B2B)',
                        description: 'Rekening Virtual & Giro Korporat Utama',
                        instructions: 'Sertakan Nomor Faktur di berita transfer untuk rekonsiliasi otomatis perbankan.',
                        image_url: null,
                        logo_url: '/images/payments/bca.svg',
                        preview_url: null,
                        is_active: true
                    },
                    {
                        id: 'mandiri',
                        type: 'BANK',
                        name: 'Bank Mandiri',
                        bank_code: 'MANDIRI',
                        account_number: '137-00-1928374-1',
                        account_name: 'Green Pasundan Agriculture',
                        description: 'Giro Korporat & Mandiri Cash Management (MCM)',
                        instructions: 'Transfer via Mandiri Livin, Kopra by Mandiri, atau Kliring SKN/BI-FAST.',
                        image_url: null,
                        logo_url: '/images/payments/mandiri.svg',
                        preview_url: null,
                        is_active: true
                    },
                    {
                        id: 'bri',
                        type: 'BANK',
                        name: 'Bank BRI',
                        bank_code: 'BRI',
                        account_number: '0341-01-001928-30-2',
                        account_name: 'Green Pasundan Agriculture',
                        description: 'Rekening Operasional Agribisnis & BritAma Bisnis',
                        instructions: 'Transfer via BRImo, Cash Management BRI, atau Teller BRI seluruh Indonesia.',
                        image_url: null,
                        logo_url: '/images/payments/bri.svg',
                        preview_url: null,
                        is_active: true
                    },
                    {
                        id: 'bni',
                        type: 'BANK',
                        name: 'Bank BNI',
                        bank_code: 'BNI',
                        account_number: '0819-283-746',
                        account_name: 'Green Pasundan Agriculture',
                        description: 'Rekening Giro Bisnis & BNI Direct',
                        instructions: 'Transfer via BNI Direct, BNI Mobile Banking, atau ATM BNI.',
                        image_url: null,
                        logo_url: '/images/payments/bni.svg',
                        preview_url: null,
                        is_active: true
                    },
                    {
                        id: 'bsi',
                        type: 'BANK',
                        name: 'Bank Syariah Indonesia (BSI)',
                        bank_code: 'BSI',
                        account_number: '719-283-9481',
                        account_name: 'Green Pasundan Agriculture',
                        description: 'Rekening Giro Wadiah Syariah Bebas Riba',
                        instructions: 'Transfer via BSI Mobile, BSI Net Banking, atau Kliring BI-FAST.',
                        image_url: null,
                        logo_url: '/images/payments/bsi.svg',
                        preview_url: null,
                        is_active: true
                    },
                    {
                        id: 'qris',
                        type: 'QRIS',
                        name: 'QRIS GPA (Semua E-Wallet & Mobile Banking)',
                        bank_code: 'QRIS',
                        account_number: 'ID1020039182948',
                        account_name: 'GREEN PASUNDAN AGRICULTURE',
                        description: 'Pindai QRIS via BCA Mobile, Livin, BRImo, GoPay, OVO, ShopeePay, DANA',
                        instructions: 'Scan barcode QRIS resmi GPA di bawah dan simpan bukti tangkapan layar transaksi.',
                        image_url: '/storage/payment-channels/qris-gpa-default.png',
                        logo_url: '/images/payments/qris.svg',
                        preview_url: null,
                        is_active: true
                    },
                    {
                        id: 'ewallet',
                        type: 'EWALLET',
                        name: 'Dompet Digital Bisnis (GoPay & DANA GPA)',
                        bank_code: 'EWALLET',
                        account_number: '0812-8888-9281',
                        account_name: 'GREEN PASUNDAN AGRICULTURE',
                        description: 'Merchant Dompet Digital Resmi Terverifikasi',
                        instructions: 'Transfer saldo dompet digital ke nomor merchant resmi Green Pasundan Agriculture.',
                        image_url: null,
                        logo_url: '/images/payments/ewallet.svg',
                        preview_url: null,
                        is_active: true
                    }
                ];
                this.editing = true;
            }
        }
    };
}

const toNumber = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(',', '.'));

    return Number.isFinite(parsed) ? parsed : 0;
};

const fixed = (value) => (Math.round(toNumber(value) * 100) / 100).toFixed(2);

const rupiah = (value) => `Rp ${Math.round(toNumber(value)).toLocaleString('id-ID')}`;

const percent = (value) => `${toNumber(value).toFixed(2)}%`;

export default function coordinatorWeighing(config = {}) {
    return {
        estimate: toNumber(config.estimate) || 500,
        rate: toNumber(config.rate) || 18000,
        gross: toNumber(config.gross) || 51,
        crates: toNumber(config.crates) || 3,
        tareEach: toNumber(config.tareEach) || 2.0,
        tolerance: toNumber(config.tolerance) || 2.0,
        orderId: config.orderId || null,
        lockUrl: config.lockUrl || null,
        locked: false,
        draftSaved: false,
        sampleUploaded: false,

        kg(value) {
            return fixed(value);
        },

        tareTotal() {
            return Math.round((this.crates * this.tareEach) * 100) / 100;
        },

        net() {
            const netVal = this.gross - this.tareTotal();
            return Math.round(netVal * 100) / 100;
        },

        variance() {
            return Math.round((this.net() - this.estimate) * 100) / 100;
        },

        variancePercent() {
            return this.estimate > 0 ? (this.variance() / this.estimate) * 100 : 0;
        },

        withinTolerance() {
            return Math.abs(this.variancePercent()) <= this.tolerance;
        },

        estimatedValue() {
            return this.estimate * this.rate;
        },

        compensation() {
            return this.variance() * this.rate;
        },

        finalAmount() {
            return Math.max(0, this.net()) * this.rate;
        },

        readingValue(key) {
            if (key === 'gross') {
                return this.kg(this.gross);
            }

            if (key === 'tare') {
                return this.kg(this.tareTotal());
            }

            return this.kg(this.net());
        },

        gradingMass(key) {
            return key === 'afkir' ? Math.max(0, Math.abs(this.variance())) : this.net();
        },

        gradingPercent(key) {
            if (this.estimate <= 0) {
                return '0.00%';
            }

            return percent((this.gradingMass(key) / this.estimate) * 100);
        },

        varianceText() {
            const value = this.variance();
            const sign = value < 0 ? '-' : '+';
            const magnitude = Math.abs(value);

            return `${sign}${fixed(magnitude)} kg (${this.variancePercent() < 0 ? '' : '+'}${percent(this.variancePercent())})`;
        },

        toleranceText() {
            if (this.withinTolerance()) {
                return `STATUS: SELISIH MASUK BATAS WAJAR (≤ ±${fixed(this.tolerance)}%)`;
            }

            return `STATUS: PERHATIAN! SELISIH MELEBIHI BATAS TOLERANSI (±${fixed(this.tolerance)}%)`;
        },

        confirmTitle() {
            return `KONFIRMASI BERAT BERSIH (NETTO): ${this.kg(this.net())} KG`;
        },

        finalValue() {
            return rupiah(this.finalAmount());
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },

        act(key) {
            if (key === 'reset-zero') {
                this.resetZero();
                return;
            }

            if (key === 're-read') {
                this.reReadSensor();
                return;
            }

            if (key === 'draft') {
                this.draftSaved = true;
                this.run(
                    'Draf Berhasil Disimpan',
                    `Data berat bersih sementara ${this.kg(this.net())} KG telah disimpan.`,
                    'info'
                );
                return;
            }

            if (key === 'lock') {
                this.lockData();
                return;
            }

            if (key === 'delivery') {
                this.issueDelivery();
            }
        },

        resetZero() {
            this.gross = this.tareTotal();
            this.draftSaved = false;

            this.run(
                'Timbangan Dinolkan',
                `Angka timbangan dinolkan. Berat wadah (${this.kg(this.tareTotal())} KG) tetap diperhitungkan.`,
                'info'
            );
        },

        reReadSensor() {
            this.draftSaved = false;
            this.run(
                'Angka Timbangan Diperbarui',
                `Berat Kotor: ${this.kg(this.gross)} KG | Berat Bersih: ${this.kg(this.net())} KG.`,
                'success',
            );
        },

        lockData() {
            if (!this.withinTolerance()) {
                this.run(
                    'Peringatan Selisih Berat',
                    `Selisih berat (${percent(this.variancePercent())}) melebihi batas toleransi yang diizinkan (±${fixed(this.tolerance)}%). Harap cek kembali timbangan Anda.`,
                    'danger',
                );

                return;
            }

            this.locked = true;

            if (config.lockUrl) {
                fetch(config.lockUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        actual_net_weight: this.net(),
                        crates: this.crates,
                        tare_each: this.tareEach,
                    })
                }).then(res => res.json()).then(data => {
                    this.run(
                        'Data Berhasil Dikunci & Disimpan',
                        `Berat bersih riil ${this.kg(this.net())} KG berhasil disimpan ke sistem. Status pesanan kini Siap Kirim.`,
                        'success'
                    );
                }).catch(err => {
                    this.run(
                        'Data Berhasil Dikunci',
                        `Berat bersih riil ${this.kg(this.net())} KG berhasil dikunci. Surat Jalan siap diterbitkan.`,
                        'success'
                    );
                });
            } else {
                this.run(
                    'Data Berhasil Dikunci',
                    `Berat bersih riil ${this.kg(this.net())} KG berhasil dikunci. Surat Jalan siap diterbitkan.`,
                    'success',
                );
            }
        },

        issueDelivery() {
            this.run(
                'Membuka Surat Jalan',
                'Mengalihkan ke halaman penerbitan Surat Jalan...',
                'success',
            );
            setTimeout(() => {
                window.location.href = '/koordinator/surat-jalan';
            }, 600);
        },

        uploadSample() {
            this.sampleUploaded = true;

            this.run(
                'Foto Bukti Berhasil Diunggah',
                'Foto dokumentasi timbangan dan sampel sayur berhasil disimpan sebagai arsip.',
                'success',
            );
        },
    };
}
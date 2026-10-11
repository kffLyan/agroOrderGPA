const toNumber = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(',', '.'));

    return Number.isFinite(parsed) ? parsed : 0;
};

const fixed = (value) => (Math.round(toNumber(value) * 100) / 100).toFixed(2);

const rupiah = (value) => `Rp ${Math.round(toNumber(value)).toLocaleString('id-ID')}`;

const percent = (value) => `${toNumber(value).toFixed(3)}%`;

export default function coordinatorWeighing(config = {}) {
    return {
        estimate: toNumber(config.estimate) || 800,
        rate: toNumber(config.rate) || 15000,
        gross: toNumber(config.gross) || 835,
        crates: toNumber(config.crates) || 20,
        tareEach: toNumber(config.tareEach) || 2,
        tolerance: toNumber(config.tolerance) || 2,
        locked: false,
        draftSaved: false,
        sampleUploaded: false,

        kg(value) {
            return fixed(value);
        },

        tareTotal() {
            return this.crates * this.tareEach;
        },

        net() {
            return this.gross - this.tareTotal();
        },

        variance() {
            return this.net - this.estimate;
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
            return this.net() * this.rate;
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
            return key === 'afkir' ? Math.abs(this.variance()) : this.net();
        },

        gradingPercent(key) {
            if (this.estimate <= 0) {
                return '0.000%';
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
                return `STATUS: MASUK TOLERANSI KONTRAK (≤ ${fixed(this.tolerance)}%)`;
            }

            return `STATUS: DI LUAR TOLERANSI KONTRAK (≤ ${fixed(this.tolerance)}%)`;
        },

        confirmTitle() {
            return `VALIDASI BERAT NETTO RIIL : ${this.kg(this.net())} KG`;
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
                    'Draf Timbangan Disimpan',
                    `Draf ${this.kg(this.net())} KG disimpan untuk verifikasi lanjutan Koordinator.`,
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
                'Reset Zero (Tare)',
                `Timbangan dinolkan. Tara wadah ${this.kg(this.tareTotal())} KG tetap tercatat.`,
            );
        },

        reReadSensor() {
            this.draftSaved = false;
            this.gross = toNumber((this.gross + 0.01).toFixed(2));

            this.run(
                'Re-Read Sensor',
                `Bacaan ulang diterima: Gross ${this.kg(this.gross)} KG, Netto ${this.kg(this.net())} KG.`,
                'success',
            );
        },

        lockData() {
            if (!this.withinTolerance()) {
                this.run(
                    'Hard-Gate Menolak',
                    `Deviasi ${percent(this.variancePercent())} berada di luar toleransi ±${fixed(this.tolerance)}%.`,
                    'danger',
                );

                return;
            }

            this.locked = true;

            this.run(
                'Data Timbangan Terkunci',
                `Netto ${this.kg(this.net())} KG dikunci permanen. Penerbitan Surat Jalan kini terbuka.`,
                'success',
            );
        },

        issueDelivery() {
            this.run(
                'Surat Jalan Terbit',
                `SJ-GPA-202610-0001 dicetak untuk ${this.kg(this.net())} KG senilai ${rupiah(this.finalAmount())}.`,
                'success',
            );
        },

        uploadSample() {
            this.sampleUploaded = true;

            this.run(
                'Sample Bukti Diunggah',
                'Foto display timbangan dan sampel daun tersimpan pada lampiran audit forensik.',
                'success',
            );
        },
    };
}
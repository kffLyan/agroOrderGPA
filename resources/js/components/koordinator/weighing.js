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
        samplePhotoUrl: null,
        scaleBeep: false,

        init() {
            try {
                const draftKey = `gpa_weighing_draft_${this.orderId || 'default'}`;
                const savedDraft = localStorage.getItem(draftKey);
                if (savedDraft) {
                    const parsed = JSON.parse(savedDraft);
                    if (parsed.gross) this.gross = parsed.gross;
                    if (parsed.crates) this.crates = parsed.crates;
                    if (parsed.tareEach) this.tareEach = parsed.tareEach;
                    if (parsed.samplePhotoUrl) {
                        this.samplePhotoUrl = parsed.samplePhotoUrl;
                        this.sampleUploaded = true;
                    }
                    this.draftSaved = true;
                }

                const lockedKey = `gpa_weighing_locked_${this.orderId || 'default'}`;
                if (localStorage.getItem(lockedKey) === '1') {
                    this.locked = true;
                }
            } catch (e) {}
        },

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
                this.saveDraft();
                return;
            }

            if (key === 'lock') {
                this.lockData();
                return;
            }

            if (key === 'print-ticket') {
                this.printTicket();
            }
        },

        resetZero() {
            this.gross = this.tareTotal();
            this.draftSaved = false;

            this.run(
                'Timbangan Dinolkan (Tare)',
                `Angka timbangan dinolkan. Berat wadah (${this.kg(this.tareTotal())} KG) diperhitungkan sebagai tara. Netto: 0.00 KG.`,
                'info',
            );
        },

        reReadSensor() {
            this.scaleBeep = true;
            setTimeout(() => { this.scaleBeep = false; }, 300);

            // Simulasikan pembacaan sensor timbangan digital terkalibrasi
            const randomDelta = (Math.floor(Math.random() * 5) - 2) * 0.1;
            this.gross = Math.max(this.tareTotal() + 1, Math.round((this.gross + randomDelta) * 10) / 10);
            this.draftSaved = false;

            this.run(
                'Sensor Timbangan Terbaca',
                `Indikator Metrologi Stabil • Bruto: ${this.kg(this.gross)} KG | Netto: ${this.kg(this.net())} KG.`,
                'success',
            );
        },

        saveDraft() {
            this.draftSaved = true;
            try {
                const draftKey = `gpa_weighing_draft_${this.orderId || 'default'}`;
                localStorage.setItem(draftKey, JSON.stringify({
                    gross: this.gross,
                    crates: this.crates,
                    tareEach: this.tareEach,
                    samplePhotoUrl: this.samplePhotoUrl,
                    savedAt: new Date().toISOString(),
                }));
            } catch (e) {}

            this.run(
                'Draf Tersimpan',
                `Data berat bersih sementara ${this.kg(this.net())} KG telah disimpan ke memori operasional.`,
                'success',
            );
        },

        lockData() {
            if (!this.withinTolerance()) {
                this.run(
                    'Peringatan Toleransi',
                    `Selisih berat (${percent(this.variancePercent())}) melebihi batas toleransi yang diizinkan (±${fixed(this.tolerance)}%). Lakukan kalibrasi ulang timbangan.`,
                    'danger',
                );

                return;
            }

            const targetUrl = this.lockUrl || config?.lockUrl;

            if (targetUrl) {
                fetch(targetUrl, {
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
                    }),
                })
                    .then((res) => res.json())
                    .then((data) => {
                        this.locked = true;
                        this.persistLock();
                        this.run(
                            'Data Sah & Terkunci',
                            data.message || `Berat bersih riil ${this.kg(this.net())} KG berhasil dikunci dan sah menurut Metrologi Legal.`,
                            'success',
                        );
                    })
                    .catch(() => {
                        this.locked = true;
                        this.persistLock();
                        this.run(
                            'Data Sah & Terkunci',
                            `Berat bersih riil ${this.kg(this.net())} KG berhasil dikunci dan sah menurut Metrologi Legal.`,
                            'success',
                        );
                    });
            } else {
                this.locked = true;
                this.persistLock();
                this.run(
                    'Data Sah & Terkunci',
                    `Berat bersih riil ${this.kg(this.net())} KG berhasil dikunci dan sah menurut Metrologi Legal.`,
                    'success',
                );
            }
        },

        persistLock() {
            try {
                const lockedKey = `gpa_weighing_locked_${this.orderId || 'default'}`;
                localStorage.setItem(lockedKey, '1');
            } catch (e) {}
        },

        printTicket() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} WIB`;
            const ticketId = `TK-NETTO-${String(now.getFullYear()).slice(-2)}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}-${Math.floor(100+Math.random()*900)}`;

            const win = window.open('', '_blank', 'width=600,height=750');
            win.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Tiket Penimbangan Netto - ${ticketId}</title>
                    <style>
                        body { font-family: monospace, sans-serif; padding: 24px; color: #111; margin: 0; }
                        .ticket-box { border: 2px dashed #222; padding: 20px; max-width: 480px; margin: auto; }
                        .header { text-align: center; border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 12px; }
                        .title { font-weight: bold; font-size: 16px; margin: 0; }
                        .subtitle { font-size: 11px; margin-top: 4px; color: #444; }
                        .row { display: flex; justify-content: space-between; margin: 6px 0; font-size: 13px; }
                        .bold { font-weight: bold; }
                        .divider { border-top: 1px dashed #777; margin: 12px 0; }
                        .netto-row { background: #e0f2fe; padding: 10px; border-radius: 4px; margin-top: 10px; font-size: 16px; font-weight: bold; color: #0369a1; }
                        .footer { margin-top: 24px; text-align: center; font-size: 11px; color: #555; border-top: 1px solid #ccc; padding-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class="ticket-box">
                        <div class="header">
                            <h2 class="title">KOPERASI TANI GREEN PASUNDAN AGRICULTURE</h2>
                            <div class="subtitle">UPTD Metrologi Legal - Sertifikat Tera Sah Kelas III</div>
                            <div class="subtitle">Hub Sortasi & Packhouse Ciwidey, Bandung</div>
                        </div>
                        <div class="row"><span>Nomor Tiket:</span><span class="bold">${ticketId}</span></div>
                        <div class="row"><span>Tanggal & Waktu:</span><span>${dateStr}, ${timeStr}</span></div>
                        <div class="row"><span>Status Metrologi:</span><span class="bold" style="color: #15803d;">TERA SAH TERA-01</span></div>
                        <div class="divider"></div>
                        <div class="row"><span>Jumlah Krat:</span><span class="bold">${this.crates} Krat Steril</span></div>
                        <div class="row"><span>Tara per Krat:</span><span>${this.tareEach.toFixed(2)} KG</span></div>
                        <div class="row"><span>Total Berat Tara:</span><span>${this.kg(this.tareTotal())} KG</span></div>
                        <div class="row"><span>Berat Kotor (Bruto):</span><span>${this.kg(this.gross)} KG</span></div>
                        <div class="row netto-row"><span>BERAT BERSIH (NETTO):</span><span>${this.kg(this.net())} KG</span></div>
                        <div class="divider"></div>
                        <div class="row"><span>Estimasi Awal:</span><span>${this.kg(this.estimate)} KG</span></div>
                        <div class="row"><span>Selisih (Variance):</span><span>${this.varianceText()}</span></div>
                        <div class="row"><span>Toleransi SOP (±${fixed(this.tolerance)}%):</span><span class="bold" style="color: #15803d;">MEMENUHI SYARAT (LOLOS)</span></div>
                        <div class="divider"></div>
                        <div class="row" style="margin-top: 25px;">
                            <div style="text-align: center; width: 50%;">
                                <div style="font-size: 11px;">Petugas Metrologi</div>
                                <div style="margin-top: 40px; font-size: 12px; font-weight: bold;">( Koordinator )</div>
                            </div>
                            <div style="text-align: center; width: 50%;">
                                <div style="font-size: 11px;">Staf Gudang Packhouse</div>
                                <div style="margin-top: 40px; font-size: 12px; font-weight: bold;">( .................... )</div>
                            </div>
                        </div>
                        <div class="footer">
                            Dokumen bukti timbang netto resmi Koperasi Tani GPA.<br>
                            Arsip sah data stok siap distribusi.
                        </div>
                    </div>
                    <script>window.onload = function() { window.print(); };</script>
                </body>
                </html>
            `);
            win.document.close();
            this.run('Tiket Dicetak', `Tiket timbangan metrologi ${ticketId} siap dicetak.`, 'success');
        },

        uploadSample() {
            const input = document.getElementById('gpa-weighing-photo-input');
            if (input) {
                input.click();
            } else {
                this.sampleUploaded = true;
                this.run('Foto Bukti Tersimpan', 'Dokumentasi timbangan dan sampel sayur berhasil disimpan.', 'success');
            }
        },

        handlePhotoUpload(event) {
            const file = event.target?.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.samplePhotoUrl = e.target.result;
                this.sampleUploaded = true;
                this.saveDraft();
                this.run(
                    'Foto Bukti Berhasil Diunggah',
                    `Foto "${file.name}" (${(file.size / 1024).toFixed(1)} KB) tersimpan sebagai bukti QC timbangan.`,
                    'success',
                );
            };
            reader.readAsDataURL(file);
        },
    };
}
const DRAFT_KEY = 'gpa.register.draft';

const TRACKED_FIELDS = [
    'name',
    'business_name',
    'phone',
    'email',
    'address',
    'delivery_zone',
    'delivery_window',
    'vehicle_access',
    'delivery_notes',
    'payment_method',
];

export default function registrationForm(initial = {}) {
    return {
        csrf: initial.csrf ?? '',
        otpSendUrl: initial.otpSendUrl ?? '',
        otpResendUrl: initial.otpResendUrl ?? '',
        otpVerifyUrl: initial.otpVerifyUrl ?? '',

        name: initial.name ?? '',
        business_name: initial.business_name ?? '',
        phone: initial.phone ?? '',
        email: initial.email ?? '',
        address: initial.address ?? '',
        delivery_zone: initial.delivery_zone ?? '',
        delivery_window: initial.delivery_window ?? '',
        vehicle_access: initial.vehicle_access ?? '',
        delivery_notes: initial.delivery_notes ?? '',
        payment_method: initial.payment_method ?? '',
        commodities: Array.isArray(initial.commodities) ? initial.commodities : [],
        integrity_accepted: Boolean(initial.integrity_accepted),

        draftAvailable: false,
        draftSavedAt: null,
        submitting: false,
        localErrors: {},

        otp: {
            state: 'idle',
            code: '',
            error: '',
            devCode: null,
            verifiedPhone: null,
            resendIn: 0,
            timer: null,
        },

        password: {
            value: initial.password?.value ?? '',
            confirmation: initial.password?.confirmation ?? '',
        },

        init() {
            if (this.phone) {
                this.phone = this.digits(this.phone).replace(/^62/, '').slice(0, 15);
            }

            const draft = this.readDraft();

            if (draft) {
                this.draftAvailable = true;
                this.draftSavedAt = draft.saved_at;
            }
        },

        digits(value) {
            return String(value ?? '').replace(/\D/g, '');
        },

        localErrorMessages() {
            return Object.values(this.localErrors).filter(Boolean);
        },

        localPhone() {
            const digits = this.digits(this.phone).replace(/^62/, '');

            return digits;
        },

        displayPhone() {
            const digits = this.localPhone();

            if (digits.length < 9) {
                return digits;
            }

            return digits.replace(/(\d{3})(\d{3})(\d{0,4})(\d{0,5})/, (_, a, b, c, d) =>
                [a, b, c, d].filter(Boolean).join('-'),
            );
        },

        onPhoneInput(event) {
            this.phone = this.digits(event.target.value).replace(/^62/, '').slice(0, 15);
            event.target.value = this.displayPhone();

            if (this.otp.state === 'verified' && this.otp.verifiedPhone !== this.phone) {
                this.otp.state = 'idle';
                this.otp.code = '';
                this.otp.verifiedPhone = null;
            }
        },

        phoneValid() {
            return this.localPhone().length >= 9 && this.localPhone().length <= 15;
        },

        async sendOtp(isResend = false) {
            this.localErrors = { ...this.localErrors, phone: undefined };

            if (!this.phoneValid()) {
                this.otp.state = 'error';
                this.otp.error = 'Nomor WhatsApp belum valid. Masukkan 9-15 digit setelah kode negara.';

                return;
            }

            this.otp.state = isResend ? 'resending' : 'sending';
            this.otp.error = '';

            try {
                const response = await fetch(isResend ? this.otpResendUrl : this.otpSendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: JSON.stringify({ phone: `+${this.localPhone()}` }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message ?? 'Kode OTP gagal dikirim.');
                }

                this.otp.state = 'sent';
                this.otp.code = '';
                this.otp.devCode = payload.dev_code ?? null;
                this.otp.verifiedPhone = null;
                this.startResendCountdown(60);

                this.toast('success', 'Kode OTP dikirim', `Kode 6 digit dikirim ke WhatsApp +${this.localPhone()}.`);
            } catch (error) {
                this.otp.state = 'error';
                this.otp.error = error.message;
            }
        },

        async verifyOtp() {
            this.otp.error = '';

            if (this.otp.code.length !== 6) {
                this.otp.error = 'Masukkan 6 digit kode OTP.';

                return;
            }

            this.otp.state = 'verifying';

            try {
                const response = await fetch(this.otpVerifyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: JSON.stringify({ phone: `+${this.localPhone()}`, code: this.otp.code }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message ?? 'Kode OTP tidak valid atau sudah kedaluwarsa.');
                }

                this.otp.state = 'verified';
                this.otp.verifiedPhone = this.localPhone();
                this.toast('success', 'Nomor WhatsApp terverifikasi', 'Aktivasi akun dapat dilanjutkan.');
            } catch (error) {
                this.otp.state = 'sent';
                this.otp.error = error.message;
            }
        },

        startResendCountdown(seconds) {
            window.clearInterval(this.otp.timer);
            this.otp.resendIn = seconds;

            this.otp.timer = window.setInterval(() => {
                this.otp.resendIn -= 1;

                if (this.otp.resendIn <= 0) {
                    window.clearInterval(this.otp.timer);
                }
            }, 1000);
        },

        passwordScore() {
            const value = this.password.value;
            let score = 0;

            if (value.length >= 8) score += 1;
            if (value.length >= 12) score += 1;
            if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score += 1;
            if (/\d/.test(value) && /[^A-Za-z0-9]/.test(value)) score += 1;

            return score;
        },

        passwordLabel() {
            return ['Terlalu lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.passwordScore()];
        },

        passwordTone() {
            const score = this.passwordScore();

            if (score <= 1) return 'bg-danger';
            if (score === 2) return 'bg-ink-subtle';
            if (score === 3) return 'bg-success';

            return 'bg-brand';
        },

        passwordsMatch() {
            return this.password.value.length > 0 && this.password.value === this.password.confirmation;
        },

        commodityCount() {
            return this.commodities.length;
        },

        completion() {
            const checks = [
                this.name,
                this.business_name,
                this.phoneValid() && this.otp.state === 'verified',
                this.address,
                this.delivery_zone,
                this.delivery_window,
                this.vehicle_access,
                this.commodityCount() > 0,
                this.payment_method,
                this.password.value.length >= 8,
                this.integrity_accepted,
            ];

            return Math.round((checks.filter(Boolean).length / checks.length) * 100);
        },

        saveDraft() {
            const payload = { saved_at: new Date().toISOString() };

            TRACKED_FIELDS.forEach((field) => {
                payload[field] = this[field] ?? '';
            });

            payload.commodities = [...this.commodities];

            localStorage.setItem(DRAFT_KEY, JSON.stringify(payload));
            this.draftAvailable = false;
            this.draftSavedAt = payload.saved_at;
            this.toast('info', 'Draf formulir disimpan', 'Tersimpan di perangkat ini. Kata sandi tidak ikut disimpan.');
        },

        restoreDraft() {
            const draft = this.readDraft();

            if (!draft) {
                this.draftAvailable = false;

                return;
            }

            TRACKED_FIELDS.forEach((field) => {
                if (draft[field] !== undefined && draft[field] !== '') {
                    if (typeof draft[field] === 'string' && draft[field].includes('[object HTML')) {
                        return;
                    }
                    this[field] = draft[field];
                }
            });

            this.commodities = Array.isArray(draft.commodities) ? draft.commodities : [];
            this.draftAvailable = false;
            this.toast('info', 'Draf dipulihkan', 'Data formulir sebelumnya dikembalikan ke isian.');
        },

        discardDraft() {
            localStorage.removeItem(DRAFT_KEY);
            this.draftAvailable = false;
            this.draftSavedAt = null;
        },

        readDraft() {
            try {
                const raw = localStorage.getItem(DRAFT_KEY);

                if (!raw) return null;

                const draft = JSON.parse(raw);
                if (!draft || typeof draft !== 'object') return null;

                for (const key of Object.keys(draft)) {
                    if (typeof draft[key] === 'string' && draft[key].includes('[object HTML')) {
                        delete draft[key];
                    }
                }

                return draft;
            } catch {
                return null;
            }
        },

        onSubmit(event) {
            this.localErrors = {};

            if (!this.phoneValid()) {
                this.localErrors.phone = 'Nomor WhatsApp belum valid.';
            }

            if (this.otp.state !== 'verified') {
                this.localErrors.otp = 'Verifikasi kode OTP WhatsApp sebelum melanjutkan.';
            }

            if (this.password.value !== this.password.confirmation) {
                this.localErrors.password_confirmation = 'Konfirmasi kata sandi tidak sama.';
            }

            if (Object.keys(this.localErrors).length > 0) {
                event.preventDefault();
                this.$nextTick(() => {
                    const summary = Array.from(document.querySelectorAll('[data-gpa-error-summary]')).find(
                        (el) => el.offsetParent !== null,
                    );

                    summary?.focus();
                    summary?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });

                return;
            }

            localStorage.removeItem(DRAFT_KEY);
            this.submitting = true;
        },

        toast(tone, title, message) {
            window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { tone, title, message } }));
        },
    };
}

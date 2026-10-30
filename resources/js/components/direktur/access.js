export default function directorAccess(users = []) {
    return {
        userSearch: '',
        roleFilter: 'all',
        users: (Array.isArray(users) ? users : []).map((user) => ({
            ...user,
            role_key: String(user.role_key ?? ''),
            name: String(user.name ?? ''),
            code: String(user.code ?? ''),
            role: String(user.role ?? ''),
            division: String(user.division ?? ''),
            nik: String(user.nik ?? ''),
            hub: String(user.hub ?? ''),
            haystack: [user.name, user.code, user.role, user.division, user.nik, user.hub]
                .map((value) => String(value ?? '').toLowerCase())
                .join(' '),
        })),

        /* ---------- directory filter ---------- */

        matchesUser(user) {
            if (!user) {
                return false;
            }

            if (this.roleFilter !== 'all' && this.roleFilter !== String(user.role_key)) {
                return false;
            }

            const needle = this.userSearch.trim().toLowerCase();

            return needle === '' || user.haystack.includes(needle);
        },

        visibleUsers() {
            return this.users.filter((user) => this.matchesUser(user));
        },

        setRole(role) {
            this.roleFilter = String(role);
        },

        roleClass(role) {
            return this.roleFilter === String(role)
                ? 'bg-brand text-white outline-brand hover:bg-brand-hover'
                : 'bg-transparent text-ink-body outline-line-board hover:bg-surface-shell';
        },

        /* ---------- header actions ---------- */

        exportMatrix() {
            this.run(
                'Unduh Matriks Akses',
                'Matriks hak akses 5 peran pengguna sedang diunduh dalam format .CSV.',
                'success',
            );
        },

        regenerateToken() {
            this.run(
                'Regenerasi Kunci Sesi',
                'Kunci keamanan sesi pengguna berhasil diperbarui. Sesi aktif tetap berjalan lancar.',
                'warning',
            );
        },

        createUser() {
            this.run(
                'Tambah Pengguna Baru',
                'Formulir registrasi akun dibuka. Pastikan data identitas pengguna telah diverifikasi.',
                'info',
            );
        },

        syncRbacPolicy() {
            this.run(
                'Sinkronisasi Hak Akses',
                'Matriks wewenang hak akses berhasil disinkronkan ke seluruh sistem.',
                'success',
            );
        },

        runUserAction(action) {
            this.run(
                String(action),
                'Aksi otorisasi tercatat rapi pada riwayat aktivitas sistem.',
                'info',
            );
        },

        pageDirectory(step) {
            this.run(
                'Navigasi Direktori',
                step > 0
                    ? 'Halaman berikutnya memuat 6 entri otorisasi berikutnya.'
                    : 'Halaman sebelumnya tidak tersedia pada entri terkini.',
                'info',
            );
        },

        revokeAllSessions() {
            this.run(
                'Revoke All Sessions',
                'Seluruh 14 sesi aktif akan diputus. Diperlukan PIN Master Direktur Utama.',
                'danger',
            );
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}
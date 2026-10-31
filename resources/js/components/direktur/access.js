export default function directorAccess(users = []) {
    return {
        userSearch: '',
        roleFilter: 'all',
        perPage: 10,
        currentPage: 1,
        users: (Array.isArray(users) ? users : []).map((user) => ({
            ...user,
            id: Number(user.id),
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

        init() {
            this.$watch('userSearch', () => {
                this.currentPage = 1;
            });
            this.$watch('roleFilter', () => {
                this.currentPage = 1;
            });
        },

        /* ---------- directory filter & pagination ---------- */

        matchesUser(user) {
            if (!user) {
                return false;
            }

            if (this.roleFilter !== 'all') {
                const filter = this.roleFilter.toLowerCase();
                const userKey = String(user.role_key ?? '').toLowerCase();
                if (filter !== userKey) {
                    return false;
                }
            }

            const needle = this.userSearch.trim().toLowerCase();

            return needle === '' || user.haystack.includes(needle);
        },

        visibleUsers() {
            return this.users.filter((user) => this.matchesUser(user));
        },

        totalPages() {
            return Math.max(1, Math.ceil(this.visibleUsers().length / this.perPage));
        },

        paginatedUsers() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.visibleUsers().slice(start, start + this.perPage);
        },

        isUserVisible(user) {
            if (!this.matchesUser(user)) {
                return false;
            }
            return this.paginatedUsers().some((u) => u.id === Number(user.id));
        },

        setPerPage(limit) {
            this.perPage = Number(limit);
            this.currentPage = 1;
        },

        setRole(role) {
            this.roleFilter = String(role);
            this.currentPage = 1;
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages()) {
                this.currentPage++;
            }
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages()) {
                this.currentPage = page;
            }
        },

        startEntry() {
            if (this.visibleUsers().length === 0) return 0;
            return (this.currentPage - 1) * this.perPage + 1;
        },

        endEntry() {
            return Math.min(this.currentPage * this.perPage, this.visibleUsers().length);
        },

        roleClass(role) {
            return this.roleFilter === String(role)
                ? 'bg-brand text-white outline-brand hover:bg-brand-hover font-bold shadow-sub'
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
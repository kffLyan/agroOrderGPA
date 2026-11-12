const accessRoles = [
    { key: 'KLIEN', label: 'KLIEN' },
    { key: 'SEKRETARIS', label: 'SEKRETARIS' },
    { key: 'KOORDINATOR', label: 'KOORDINATOR' },
    { key: 'ARMADA', label: 'ARMADA' },
    { key: 'DIREKTUR', label: 'DIREKTUR' },
];

const accessFeatures = [
    {
        key: 'orders',
        label: 'Katalog & Buat Order B2B',
        description: 'Order PO-distribusi klien korporat dan price list kontrak',
    },
    {
        key: 'weighing',
        label: 'Timbangan Aktual & Netto Panen',
        description: 'Certifikasi bobot bersih timbangan elektronik',
    },
    {
        key: 'dispatch',
        label: 'Terbitkan Surat Jalan',
        description: 'Dokumen Predicate legalitas pengiriman kargo',
    },
    {
        key: 'billing',
        label: 'Faktur, Bayar & Rekonsiliasi Kas',
        description: 'Settlement termin dan kliring rekening korporat',
    },
    {
        key: 'pod',
        label: 'PoD Digital, Foto & Catat Retur',
        description: 'Bukti penerimaan barang dan catatan sengketa',
    },
    {
        key: 'contracts',
        label: 'Approval Kontrak & Kunci Audit',
        description: 'Otorisasi monograf final dan sealing ledger',
    },
];

const initialAccessMatrix = {
    orders: {
        KLIEN: 'Read / Create',
        SEKRETARIS: 'Form WA / Manual (Create)',
        KOORDINATOR: 'No Access',
        ARMADA: 'No Access',
        DIREKTUR: 'Full Monitoring (Read)',
    },
    weighing: {
        KLIEN: 'Read-Only',
        SEKRETARIS: 'Read Riil',
        KOORDINATOR: 'Lock & Certify',
        ARMADA: 'No Access',
        DIREKTUR: 'Audit Calibration (Read)',
    },
    dispatch: {
        KLIEN: 'No Access',
        SEKRETARIS: 'Create & Sign',
        KOORDINATOR: 'Read (Manifest)',
        ARMADA: 'Read Digital',
        DIREKTUR: 'Read & Invalidation',
    },
    billing: {
        KLIEN: 'Read / Upload Bukti',
        SEKRETARIS: 'Approve & Reconcile',
        KOORDINATOR: 'No Access',
        ARMADA: 'No Access',
        DIREKTUR: 'Executive Ledger Read',
    },
    pod: {
        KLIEN: 'Digital Sign PoD',
        SEKRETARIS: 'Verify Dispute',
        KOORDINATOR: 'Verify & Stock Sync',
        ARMADA: 'Upload & Submit',
        DIREKTUR: 'Dispute Final Arbiter',
    },
    contracts: {
        KLIEN: 'NONE (BLOCKED)',
        SEKRETARIS: 'Draft / Review',
        KOORDINATOR: 'NONE (BLOCKED)',
        ARMADA: 'NONE (BLOCKED)',
        DIREKTUR: 'SOLE APPROVER / LOCK',
    },
};

const accessOptions = [
    'Tidak ada akses',
    'Lihat',
    'Buat',
    'Ubah',
    'Setujui',
    'Kelola penuh',
];

const copyAccessMatrix = (matrix) => Object.fromEntries(
    Object.entries(matrix).map(([feature, rolePermissions]) => [
        feature,
        { ...rolePermissions },
    ]),
);

export default function directorAccess(users = []) {
    return {
        userSearch: '',
        roleFilter: 'all',
        page: 1,
        perPage: 10,
        accessRoles,
        accessFeatures,
        accessOptions,
        accessMatrix: copyAccessMatrix(initialAccessMatrix),
        draftAccessMatrix: null,
        selectedAccessRole: 'KLIEN',
        roleManagementOpen: false,
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

            if (this.roleFilter !== 'all') {
                if (this.roleFilter === 'client_kontrak') {
                    if (user.role_key !== 'client' || user.client_type !== 'B2B_KONTRAK') {
                        return false;
                    }
                } else if (this.roleFilter === 'client_reguler') {
                    if (user.role_key !== 'client' || user.client_type === 'B2B_KONTRAK') {
                        return false;
                    }
                } else if (this.roleFilter !== String(user.role_key)) {
                    return false;
                }
            }

            const needle = this.userSearch.trim().toLowerCase();

            return needle === '' || user.haystack.includes(needle);
        },

        filteredUsers() {
            return this.users.filter((user) => this.matchesUser(user));
        },

        get lastPage() {
            return Math.max(1, Math.ceil(this.filteredUsers().length / this.perPage));
        },

        pagedUsers() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredUsers().slice(start, start + this.perPage);
        },

        isUserVisible(user) {
            if (! this.matchesUser(user)) {
                return false;
            }
            return this.pagedUsers().some((item) => item.id === user.id);
        },

        visibleUsers() {
            return this.pagedUsers();
        },

        visibleCount() {
            return this.filteredUsers().length;
        },

        goToPage(page) {
            const target = Math.min(Math.max(1, page), this.lastPage);
            if (this.page !== target) {
                this.page = target;
            }
        },

        pageDirectory(step) {
            this.goToPage(this.page + step);
        },

        pageNumbers() {
            const pages = [];
            const total = this.lastPage;
            if (total <= 5) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                let start = Math.max(1, this.page - 2);
                let end = Math.min(total, this.page + 2);
                if (this.page <= 3) {
                    start = 1;
                    end = 5;
                } else if (this.page >= total - 2) {
                    start = total - 4;
                    end = total;
                }
                for (let i = start; i <= end; i++) pages.push(i);
            }
            return pages;
        },

        summaryLabel() {
            const total = this.filteredUsers().length;
            if (total === 0) return '0 entri otorisasi terdaftar';
            const start = (this.page - 1) * this.perPage + 1;
            const end = Math.min(this.page * this.perPage, total);
            return `Menampilkan ${start}–${end} dari ${total} entri otorisasi terdaftar`;
        },

        setRole(role) {
            this.roleFilter = String(role);
            this.page = 1;
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
                'Matriks RBAC 5 peran sedang diunduh dalam format .CSV.',
                'success',
            );
        },

        regenerateToken() {
            this.run(
                'Regenerasi Token Kripto',
                'Token sesi RSA-4096 diregenerasi untuk seluruh akun. Sesi aktif tidak diputus.',
                'warning',
            );
        },

        createUser() {
            this.run(
                'Tambah Pengguna Baru',
                'Formulir registrasi akun dibuka. NIK wajib diverifikasi terhadap Vault Keamanan.',
                'info',
            );
        },

        syncRbacPolicy() {
            this.run(
                'Sync RBAC Policy',
                'Matriks wewenang disinkronkan ke semua node. 5 role terisolasi tanpa pencampuran.',
                'success',
            );
        },

        openRoleManagement() {
            this.draftAccessMatrix = copyAccessMatrix(this.accessMatrix);
            this.selectedAccessRole = 'KLIEN';
            this.roleManagementOpen = true;
        },

        closeRoleManagement() {
            this.roleManagementOpen = false;
            this.draftAccessMatrix = null;
        },

        resetAccessRole() {
            this.accessFeatures.forEach((feature) => {
                this.draftAccessMatrix[feature.key][this.selectedAccessRole]
                    = initialAccessMatrix[feature.key][this.selectedAccessRole];
            });
        },

        saveRoleManagement() {
            this.accessMatrix = copyAccessMatrix(this.draftAccessMatrix);
            this.closeRoleManagement();
            this.run(
                'Pratinjau Hak Akses Diperbarui',
                'Matriks diperbarui di tampilan ini. Perubahan belum tersimpan ke server.',
                'info',
            );
        },

        permissionBadgeClass(roleKey, permission) {
            if (permission === 'No Access' || permission === 'NONE (BLOCKED)' || permission === 'Tidak ada akses') {
                return 'text-ink-body';
            }

            if (roleKey === 'DIREKTUR') {
                return 'bg-ink text-accent';
            }

            if (permission === 'Read / Create' || permission.includes('Approve') || permission.includes('Certify')) {
                return 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep';
            }

            return 'bg-surface-track text-ink';
        },

        runUserAction(action) {
            this.run(
                String(action),
                'Aksi otorisasi dicatat pada audit trail SHA-256 dan memerlukan PIN Master.',
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
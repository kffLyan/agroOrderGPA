export default function directorDashboard(operator = {}) {
    return {
        sidebarOpen: false,
        operator: operator && typeof operator === 'object' ? operator : {},

        /* ---------- dashboard actions ---------- */

        exportExcel() {
            window.location.href = '/direktur/reports/export';
        },

        printPdf() {
            window.open('/direktur/reports/download', '_blank');
        },

        auditLog() {
            this.run('Audit Log', 'Jejak audit kepatuhan dibuka.', 'info');
        },

        openAuditNote() {
            this.run('Jejak Audit', 'Catatan kepatuhan piutang dibuka di modul audit.', 'info');
        },

        dispensation() {
            const name = this.operator?.name ? ` oleh ${this.operator.name}` : '';

            this.run('Dispensi Diajukan', `Pengajuan dispensi tenor 7 hari${name} dikirim ke Kantor Finance.`, 'warning');
        },

        /* ---------- toast ---------- */

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}

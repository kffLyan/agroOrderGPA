const normalize = (value) => String(value ?? '').toLowerCase();

export default function secretaryDashboard() {
    return {
        sidebarOpen: false,
        query: '',
        resolved: [],
        dismissed: [],

        init() {
            this.onShortcut = (event) => {
                const target = event.target;
                const typing =
                    target instanceof HTMLElement &&
                    (target.isContentEditable ||
                        ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    this.focusSearch();

                    return;
                }

                if (event.key === '/' && !typing) {
                    event.preventDefault();
                    this.focusSearch();
                }
            };

            window.addEventListener('keydown', this.onShortcut);
        },

        destroy() {
            window.removeEventListener('keydown', this.onShortcut);
        },

        focusSearch() {
            this.$refs.globalSearch?.focus();
        },

        run(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },

        isResolved(po) {
            return this.resolved.includes(po);
        },

        isRowVisible(row) {
            if (this.isResolved(row.po)) {
                return false;
            }

            const needle = normalize(this.query).trim();

            if (!needle) {
                return true;
            }

            return [row.po, row.client, row.terms.label, ...row.items.map((item) => item.name)]
                .map(normalize)
                .some((value) => value.includes(needle));
        },

        visibleRows(rows) {
            return rows.filter((row) => this.isRowVisible(row));
        },

        pendingCount(rows) {
            return rows.filter((row) => !this.isResolved(row.po)).length;
        },

        visibleAlerts(items) {
            return items.filter((_, index) => !this.dismissed.includes(index));
        },

        handle(po, action) {
            const toneByIntent = {
                approve: 'success',
                escalate: 'danger',
                split: 'success',
                detail: 'info',
                proof: 'info',
                hub: 'info',
            };

            this.run(`${po}: ${action.label}`, action.message, toneByIntent[action.intent] ?? 'info');

            if (!['approve', 'escalate', 'split'].includes(action.intent)) {
                return;
            }

            this.resolved.push(po);
        },

        restore(po) {
            this.resolved = this.resolved.filter((entry) => entry !== po);
        },

        dismiss(index) {
            this.dismissed.push(index);
        },
    };
}
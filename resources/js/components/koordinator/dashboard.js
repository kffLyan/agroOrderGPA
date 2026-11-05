export default function coordinatorDashboard() {
    return {
        sidebarOpen: false,
        query: '',

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

        syncScale() {
            this.$dispatch('gpa:sync-scale');
        },
    };
}
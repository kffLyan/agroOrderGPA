(() => {
    // Jika gpaProgress sudah aktif dari blade layout, gunakan instance tersebut agar sinkron dan efisien
    if (window.gpaProgress && document.getElementById('gpa-top-progress-bar')) {
        return;
    }

    const transition = 'transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease';
    const bar = document.createElement('div');

    bar.id = 'gpa-top-progress-bar';
    bar.setAttribute('role', 'progressbar');
    bar.setAttribute('aria-hidden', 'true');
    Object.assign(bar.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        right: '0',
        height: '2.5px',
        zIndex: '99999999',
        pointerEvents: 'none',
        opacity: '0',
        transformOrigin: 'left center',
        transform: 'scaleX(0)',
        background: 'linear-gradient(90deg, #2d6003 0%, #4a8a00 35%, #5b9e04 70%, #bef377 100%)',
        boxShadow: '0 0 8px rgba(190, 243, 119, 0.75), 0 0 3px rgba(91, 158, 4, 0.6)',
        transition,
    });

    const peg = document.createElement('div');
    peg.className = 'gpa-progress-peg';
    Object.assign(peg.style, {
        position: 'absolute',
        right: '0',
        top: '0',
        height: '100%',
        width: '70px',
        boxShadow: '0 0 10px #bef377, 0 0 4px #bef377',
        opacity: '0.9',
        transform: 'rotate(3deg) translate(0px, -1.5px)',
    });
    bar.appendChild(peg);

    function mount() {
        if (document.body && !bar.parentNode) {
            document.body.appendChild(bar);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mount, { once: true });
    } else {
        mount();
    }

    let value = 0;
    let pending = 0;
    let timer = null;
    let guard = null;
    let debounceTimer = null;

    const set = (n) => {
        value = n;
        bar.style.transform = `scaleX(${n})`;
    };

    const trickle = () => {
        const step = value < 0.35 ? 0.08 : value < 0.65 ? 0.03 : 0.006;
        set(Math.min(value + step, 0.90));
    };

    const executeStart = () => {
        pending += 1;
        if (timer) return;

        mount();
        bar.style.opacity = '1';
        set(0.15);
        timer = setInterval(trickle, 280);
        guard = setTimeout(() => done(true), 10000);
    };

    const start = (delayMs) => {
        clearTimeout(debounceTimer);
        const wait = typeof delayMs === 'number' ? delayMs : 120;
        if (wait <= 0) {
            executeStart();
        } else {
            debounceTimer = setTimeout(executeStart, wait);
        }

        if (typeof window.showGpaNavSkeleton === 'function') {
            window.showGpaNavSkeleton(280);
        }
    };

    const done = (force = false) => {
        clearTimeout(debounceTimer);
        pending = force ? 0 : Math.max(0, pending - 1);

        if (typeof window.hideGpaNavSkeleton === 'function') {
            window.hideGpaNavSkeleton();
        }

        if (pending > 0 || !timer) {
            bar.style.opacity = '0';
            set(0);
            return;
        }

        clearInterval(timer);
        clearTimeout(guard);
        timer = null;
        set(1);

        setTimeout(() => {
            bar.style.opacity = '0';

            setTimeout(() => {
                if (timer) return;
                bar.style.transition = 'none';
                set(0);
                void bar.offsetWidth;
                bar.style.transition = transition;
            }, 220);
        }, 140);
    };

    document.addEventListener('click', (e) => {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
            return;
        }

        const a = e.target.closest('a[href]');

        if (
            !a ||
            a.target === '_blank' ||
            a.hasAttribute('download') ||
            a.hasAttribute('data-no-progress') ||
            a.hasAttribute('data-no-loader') ||
            a.hasAttribute('data-instant')
        ) {
            return;
        }

        if (!/^https?:$/.test(a.protocol) || a.origin !== location.origin) {
            return;
        }

        if (a.pathname === location.pathname && a.search === location.search && a.hash) {
            return;
        }

        if (a.hasAttribute('data-show-loader') || a.hasAttribute('data-gpa-loader')) {
            if (typeof window.showGpaLoader === 'function') {
                window.showGpaLoader(a.getAttribute('data-loader-text') || 'Memuat');
            }
            return;
        }

        // Terapkan threshold 120ms
        start(120);
    });

    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented || e.target.target === '_blank' || e.target.hasAttribute('data-no-progress')) {
            return;
        }

        if (e.target.hasAttribute('data-show-loader') || e.target.hasAttribute('data-gpa-loader')) {
            if (typeof window.showGpaLoader === 'function') {
                window.showGpaLoader(e.target.getAttribute('data-loader-text') || 'Memproses...');
            }
            return;
        }

        start(60);
    });

    window.addEventListener('pageshow', () => {
        done(true);
        if (typeof window.hideGpaNavSkeleton === 'function') {
            window.hideGpaNavSkeleton();
        }
        if (typeof window.hideGpaLoader === 'function') {
            window.hideGpaLoader();
        }
    });

    window.gpaProgress = { start, done: () => done() };
})();

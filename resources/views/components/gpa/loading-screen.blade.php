<div id="gpa-loader-overlay" aria-live="polite" aria-busy="true">
<style>
  #gpa-loader-overlay {
    --bg: #f8faf3;
    --fg: #2f4a12;
    color-scheme: light;
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 20px;
    font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
    background: var(--bg);
    color: var(--fg);
    box-sizing: border-box;
    padding-top: env(safe-area-inset-top, 0px);
    padding-bottom: env(safe-area-inset-bottom, 0px);
    opacity: 1;
    pointer-events: auto;
    transition: opacity 0.3s ease-out;
  }
  #gpa-loader-overlay svg {
    width: min(38vw, 180px);
    height: auto;
    overflow: visible;
    animation: fade 4.2s ease-in-out infinite;
  }
  #gpa-loader-overlay .ghost {
    opacity: .09;
  }
  #gpa-loader-overlay .leaf {
    clip-path: inset(0 100% 0 0);
  }
  #gpa-loader-overlay .l1 {
    animation: w1 4.2s cubic-bezier(.45, 0, .25, 1) infinite;
  }
  #gpa-loader-overlay .l2 {
    animation: w2 4.2s cubic-bezier(.45, 0, .25, 1) infinite;
  }
  #gpa-loader-overlay .l3 {
    animation: w3 4.2s cubic-bezier(.45, 0, .25, 1) infinite;
  }
  @keyframes w1 {
    0% { clip-path: inset(0 100% 0 0); }
    22%, 100% { clip-path: inset(0 0 0 0); }
  }
  @keyframes w2 {
    0%, 22% { clip-path: inset(0 100% 0 0); }
    44%, 100% { clip-path: inset(0 0 0 0); }
  }
  @keyframes w3 {
    0%, 44% { clip-path: inset(0 100% 0 0); }
    66%, 100% { clip-path: inset(0 0 0 0); }
  }
  @keyframes fade {
    0%, 84% { opacity: 1; }
    96%, 100% { opacity: 0; }
  }
  #gpa-loader-overlay p {
    margin: 0;
    font-size: 14px;
    letter-spacing: .12em;
    text-transform: uppercase;
    opacity: .7;
  }
  #gpa-loader-overlay.fade-out {
    opacity: 0;
    pointer-events: none;
  }
  @media (prefers-reduced-motion: reduce) {
    #gpa-loader-overlay .leaf,
    #gpa-loader-overlay svg {
      animation: none;
      clip-path: none;
    }
  }
</style>

<svg viewBox="330 90 1280 1710" role="img" aria-label="Memuat">
  <defs>
    <linearGradient id="gL" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#143a00"/><stop offset=".5" stop-color="#2f6600"/><stop offset="1" stop-color="#5b9e04"/></linearGradient>
    <linearGradient id="gR" x1="0" y1="1" x2="1" y2="0"><stop offset="0" stop-color="#5b9e04"/><stop offset=".6" stop-color="#4a8a00"/><stop offset="1" stop-color="#bddc00"/></linearGradient>
    <linearGradient id="gT" x1="0" y1="1" x2="1" y2="0"><stop offset="0" stop-color="#163f00"/><stop offset=".4" stop-color="#5f9500"/><stop offset="1" stop-color="#c0dc00"/></linearGradient>
    <path id="pL" d="M365 918 C620 960 900 1180 962 1540 L965 1775 C650 1700 365 1400 365 918 Z"/>
    <path id="pT" d="M965 118 C800 340 655 640 650 975 C800 1070 900 1190 965 1325 C1030 1190 1140 1060 1285 975 C1270 640 1130 340 965 118 Z"/>
    <path id="pR" d="M1568 918 C1312 960 1032 1180 970 1540 L967 1775 C1282 1700 1568 1400 1568 918 Z"/>
  </defs>
  <g class="ghost" fill="currentColor"><use href="#pL"/><use href="#pT"/><use href="#pR"/></g>
  <use class="leaf l1" href="#pL" fill="url(#gL)"/>
  <use class="leaf l2" href="#pT" fill="url(#gT)"/>
  <use class="leaf l3" href="#pR" fill="url(#gR)"/>
</svg>
<p>Memuat</p>
</div>

<script>
  (function () {
    const overlay = document.getElementById('gpa-loader-overlay');
    if (!overlay) return;

    window.hideGpaLoader = function () {
      if (!overlay) return;
      overlay.classList.add('fade-out');
      setTimeout(function () {
        overlay.style.display = 'none';
        overlay.classList.remove('fade-out');
      }, 320);
    };

    window.showGpaLoader = function () {
      if (!overlay) return;
      overlay.style.display = 'flex';
      overlay.classList.remove('fade-out');
    };

    function dismissLoader() {
      setTimeout(window.hideGpaLoader, 50);
    }

    if (document.readyState === 'interactive' || document.readyState === 'complete') {
      dismissLoader();
    } else {
      document.addEventListener('DOMContentLoaded', dismissLoader);
      window.addEventListener('load', dismissLoader);
    }

    // Safety timeout maksimal 3 detik agar tidak pernah menggantung
    setTimeout(window.hideGpaLoader, 3000);

    let navTimer = null;
    document.addEventListener('click', function (e) {
      const anchor = e.target.closest('a');
      if (!anchor) return;
      const href = anchor.getAttribute('href');
      const target = anchor.getAttribute('target');
      const download = anchor.getAttribute('download');

      if (
        !href ||
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        href.startsWith('tel:') ||
        href.startsWith('mailto:') ||
        target === '_blank' ||
        download !== null ||
        anchor.hasAttribute('data-no-loader') ||
        e.ctrlKey || e.metaKey || e.shiftKey
      ) {
        return;
      }

      try {
        const url = new URL(href, window.location.origin);
        if (url.origin === window.location.origin && url.pathname !== window.location.pathname) {
          clearTimeout(navTimer);
          navTimer = setTimeout(function () {
            window.showGpaLoader();
          }, 150);
          setTimeout(window.hideGpaLoader, 4000);
        }
      } catch (err) {}
    });

    window.addEventListener('pageshow', function (event) {
      if (event.persisted) {
        window.hideGpaLoader();
      }
    });
  })();
</script>

<script>
  (function () {
    try {
      if (sessionStorage.getItem('gpa_page_transition') === '1') {
        sessionStorage.removeItem('gpa_page_transition');
        document.documentElement.classList.add('gpa-trans-in');
      }
    } catch (e) {}
  })();
</script>

<div id="gpa-loader-overlay" aria-live="polite" aria-busy="false" onclick="window.hideGpaLoader()">
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
    gap: 16px;
    font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
    background: var(--bg);
    color: var(--fg);
    box-sizing: border-box;
    padding-top: env(safe-area-inset-top, 0px);
    padding-bottom: env(safe-area-inset-bottom, 0px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.22s ease-out, visibility 0.22s ease-out;
    cursor: pointer;
  }
  #gpa-loader-overlay.is-active {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  /* Transisi Halus (Dissolve Out) Saat Baru Tiba di Halaman Baru */
  html.gpa-trans-in #gpa-loader-overlay {
    opacity: 1;
    visibility: visible;
    pointer-events: none;
    animation: gpaDissolveOut 0.26s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }
  @keyframes gpaDissolveOut {
    0% {
      opacity: 1;
      visibility: visible;
    }
    100% {
      opacity: 0;
      visibility: hidden;
    }
  }
  html.gpa-trans-in #gpa-loader-overlay .leaf {
    clip-path: inset(0 0 0 0) !important;
    animation: none !important;
  }
  html.gpa-trans-in #gpa-loader-overlay svg {
    animation: none !important;
  }

  #gpa-loader-overlay svg {
    width: min(38vw, 180px);
    height: auto;
    overflow: visible;
  }
  #gpa-loader-overlay.is-active svg {
    animation: fade 4.2s ease-in-out infinite;
  }
  #gpa-loader-overlay .ghost {
    opacity: .09;
  }
  #gpa-loader-overlay .leaf {
    clip-path: inset(0 100% 0 0);
  }
  #gpa-loader-overlay.is-active .l1 {
    animation: w1 4.2s cubic-bezier(.45, 0, .25, 1) infinite;
  }
  #gpa-loader-overlay.is-active .l2 {
    animation: w2 4.2s cubic-bezier(.45, 0, .25, 1) infinite;
  }
  #gpa-loader-overlay.is-active .l3 {
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
    font-weight: 600;
  }
  #gpa-loader-overlay .subtext {
    margin: 0;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: normal;
    text-transform: none;
    opacity: .55;
    text-align: center;
    max-width: 320px;
    line-height: 1.4;
  }
  @media (prefers-reduced-motion: reduce) {
    #gpa-loader-overlay .leaf,
    #gpa-loader-overlay svg,
    html.gpa-trans-in #gpa-loader-overlay {
      animation: none !important;
      clip-path: none !important;
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
<p id="gpa-loader-text">Memuat</p>
<p id="gpa-loader-subtext" class="subtext" style="display: none;"></p>
</div>

<script>
  (function () {
    const overlay = document.getElementById('gpa-loader-overlay');
    const textEl = document.getElementById('gpa-loader-text');
    const subtextEl = document.getElementById('gpa-loader-subtext');
    let navTimer = null;
    let safetyTimer = null;

    if (!overlay) return;

    // Bersihkan penanda transisi masuk setelah animasi selesai
    setTimeout(function () {
      document.documentElement.classList.remove('gpa-trans-in');
    }, 300);

    // Sembunyikan loading screen
    window.hideGpaLoader = function () {
      clearTimeout(navTimer);
      clearTimeout(safetyTimer);
      try {
        sessionStorage.removeItem('gpa_page_transition');
        document.documentElement.classList.remove('gpa-trans-in');
      } catch (e) {}
      if (!overlay) return;
      overlay.classList.remove('is-active');
      overlay.setAttribute('aria-busy', 'false');
    };

    // Tampilkan loading screen di akhir saat mau berpindah halaman / proses aksi
    window.showGpaLoader = function (customText, customSubtext) {
      clearTimeout(navTimer);
      clearTimeout(safetyTimer);
      if (!overlay) return;

      if (textEl) {
        textEl.textContent = customText || 'Memuat';
      }
      if (subtextEl) {
        if (customSubtext) {
          subtextEl.textContent = customSubtext;
          subtextEl.style.display = 'block';
        } else {
          subtextEl.textContent = '';
          subtextEl.style.display = 'none';
        }
      }

      overlay.classList.add('is-active');
      overlay.setAttribute('aria-busy', 'true');

      // Failsafe 4 detik: jika navigasi batal atau server lambat, overlay tidak membeku
      safetyTimer = setTimeout(window.hideGpaLoader, 4000);
    };

    // 1. Tangani Klik Navigasi Link Antar Halaman (Aktif hanya saat mau berpindah)
    document.addEventListener('click', function (e) {
      const anchor = e.target.closest('a');
      if (!anchor) return;

      const href = anchor.getAttribute('href');
      const target = anchor.getAttribute('target');
      const download = anchor.getAttribute('download');

      // Abaikan jika bukan navigasi biasa (link hash internal, javascript, download, tab baru, tel, mailto, dll)
      if (
        !href ||
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        href.startsWith('tel:') ||
        href.startsWith('mailto:') ||
        (target && target !== '_self') ||
        download !== null ||
        anchor.hasAttribute('data-no-loader') ||
        e.defaultPrevented ||
        e.ctrlKey || e.metaKey || e.shiftKey || e.altKey
      ) {
        return;
      }

      try {
        const url = new URL(href, window.location.origin);
        // Hanya untuk navigasi internal ke URL / halaman lain
        if (url.origin === window.location.origin && (url.pathname !== window.location.pathname || url.search !== window.location.search)) {
          clearTimeout(navTimer);
          try {
            sessionStorage.setItem('gpa_page_transition', '1');
          } catch (err) {}
          // Berikan jeda sangat singkat (80ms): responsif dan tidak berkedip jika navigasi instan
          navTimer = setTimeout(function () {
            window.showGpaLoader('Memuat');
          }, 80);
        }
      } catch (err) {}
    });

    // 2. Tangani Form Submission (Aktif saat form dikirim untuk berpindah / memproses data)
    document.addEventListener('submit', function (e) {
      const form = e.target;
      if (!form || form.hasAttribute('data-no-loader') || e.defaultPrevented) return;

      if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        return;
      }

      const target = form.getAttribute('target');
      if (target && target !== '_self') return;

      clearTimeout(navTimer);
      try {
        sessionStorage.setItem('gpa_page_transition', '1');
      } catch (err) {}
      navTimer = setTimeout(function () {
        window.showGpaLoader('Memproses...');
      }, 50);
    });

    // 3. Pastikan loader selalu nonaktif saat halaman ditampilkan (termasuk BFCache browser / Back / Forward)
    window.addEventListener('pageshow', function () {
      try {
        sessionStorage.removeItem('gpa_page_transition');
        document.documentElement.classList.remove('gpa-trans-in');
      } catch (e) {}
      window.hideGpaLoader();
    });
    window.addEventListener('popstate', function () {
      try {
        sessionStorage.removeItem('gpa_page_transition');
        document.documentElement.classList.remove('gpa-trans-in');
      } catch (e) {}
      window.hideGpaLoader();
    });

    // 4. Tekan tombol ESC atau klik overlay untuk membatalkan jika diinginkan
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        window.hideGpaLoader();
      }
    });
  })();
</script>

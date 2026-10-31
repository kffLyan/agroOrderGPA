{{--
  GPA Unified Navigation & Loading Component (UX-Optimized)
  - UX Threshold: Perpindahan cepat (<120ms) tidak memunculkan indikator berkedip agar tetap tenang & nyaman.
  - YouTube Progress Bar: Hanya meluncur jika navigasi memakan waktu >120ms.
  - Skeleton Loading: Muncul jika proses memuat data melebihi 280ms (mencegah kedipan layout).
  - Dedicated Leaf Loader: Tetap dikhususkan untuk aksi berat / penanda eksplisit (data-show-loader).
--}}

{{-- 1. Garis Loading Atas ala YouTube (Sleek 2.5px, Halus & Ergonomis) --}}
<div id="gpa-top-progress-bar" role="progressbar" aria-hidden="true" style="
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 2.5px;
  z-index: 99999999;
  pointer-events: none;
  opacity: 0;
  transform-origin: left center;
  transform: scaleX(0);
  background: linear-gradient(90deg, #2d6003 0%, #4a8a00 35%, #5b9e04 70%, #bef377 100%);
  box-shadow: 0 0 8px rgba(190, 243, 119, 0.75), 0 0 3px rgba(91, 158, 4, 0.6);
  transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
">
  <div class="gpa-progress-peg" style="
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 70px;
    box-shadow: 0 0 10px #bef377, 0 0 4px #bef377;
    opacity: 0.9;
    transform: rotate(3deg) translate(0px, -1.5px);
  "></div>
</div>

{{-- 2. Tampilan Skeleton Loading (Aktif jika navigasi membutuhkan waktu lebih lama) --}}
<div id="gpa-nav-skeleton" aria-hidden="true">
  <style>
    #gpa-nav-skeleton,
    #gpa-nav-skeleton.is-active {
      display: none !important;
      opacity: 0 !important;
      visibility: hidden !important;
      pointer-events: none !important;
    }
    #gpa-nav-skeleton .gpa-skel-wrap {
      max-width: 1280px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 20px;
      width: 100%;
    }
    #gpa-nav-skeleton .gpa-skel-pill {
      border-radius: 6px;
      background-color: #dbe2d0;
    }
    #gpa-nav-skeleton .gpa-skel-box {
      background-color: #dbe2d0;
    }
    #gpa-nav-skeleton .gpa-skel-grid {
      display: grid;
      grid-template-columns: repeat(1, minmax(0, 1fr));
      gap: 16px;
    }
    @media (min-width: 640px) {
      #gpa-nav-skeleton .gpa-skel-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }
    @media (min-width: 1024px) {
      #gpa-nav-skeleton .gpa-skel-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
    }
    #gpa-nav-skeleton .gpa-skel-card {
      border-radius: 12px;
      border: 1px solid #d7e0c9;
      background: #ffffff;
      padding: 16px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    #gpa-nav-skeleton .gpa-skel-panel {
      border-radius: 12px;
      border: 1px solid #d7e0c9;
      background: #ffffff;
      padding: 20px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    #gpa-nav-skeleton .gpa-skel-row {
      border-radius: 6px;
      background-color: #e5ecd8;
      height: 38px;
    }
    .gpa-shimmer {
      background: linear-gradient(
        90deg,
        rgba(215, 224, 201, 0.45) 0%,
        rgba(255, 255, 255, 0.85) 50%,
        rgba(215, 224, 201, 0.45) 100%
      ) !important;
      background-size: 200% 100% !important;
      animation: gpaShimmer 1.4s infinite linear !important;
    }
    @keyframes gpaShimmer {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
    @media (max-width: 639px) {
      .gpa-skel-hide-sm { display: none !important; }
    }
    @media (max-width: 1023px) {
      .gpa-skel-hide-md { display: none !important; }
    }
    @media (prefers-reduced-motion: reduce) {
      .gpa-shimmer { animation: none !important; }
    }
  </style>

  <div class="gpa-skel-wrap">
    <!-- Header / Judul Halaman Skeleton -->
    <div>
      <div class="gpa-skel-pill gpa-shimmer" style="width: 130px; height: 13px;"></div>
      <div class="gpa-skel-pill gpa-shimmer" style="width: 260px; height: 28px; margin-top: 10px;"></div>
    </div>

    <!-- Metric / Stats Cards Skeleton (4 cards responsif) -->
    <div class="gpa-skel-grid">
      <div class="gpa-skel-card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="gpa-skel-pill gpa-shimmer" style="width: 80px; height: 12px;"></div>
          <div class="gpa-skel-box gpa-shimmer" style="width: 28px; height: 28px; border-radius: 6px;"></div>
        </div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 120px; height: 24px; margin-top: 12px;"></div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 85px; height: 10px; margin-top: 8px;"></div>
      </div>
      <div class="gpa-skel-card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="gpa-skel-pill gpa-shimmer" style="width: 90px; height: 12px;"></div>
          <div class="gpa-skel-box gpa-shimmer" style="width: 28px; height: 28px; border-radius: 6px;"></div>
        </div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 105px; height: 24px; margin-top: 12px;"></div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 75px; height: 10px; margin-top: 8px;"></div>
      </div>
      <div class="gpa-skel-card gpa-skel-hide-sm">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="gpa-skel-pill gpa-shimmer" style="width: 100px; height: 12px;"></div>
          <div class="gpa-skel-box gpa-shimmer" style="width: 28px; height: 28px; border-radius: 6px;"></div>
        </div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 135px; height: 24px; margin-top: 12px;"></div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 95px; height: 10px; margin-top: 8px;"></div>
      </div>
      <div class="gpa-skel-card gpa-skel-hide-md">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="gpa-skel-pill gpa-shimmer" style="width: 85px; height: 12px;"></div>
          <div class="gpa-skel-box gpa-shimmer" style="width: 28px; height: 28px; border-radius: 6px;"></div>
        </div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 110px; height: 24px; margin-top: 12px;"></div>
        <div class="gpa-skel-pill gpa-shimmer" style="width: 100px; height: 10px; margin-top: 8px;"></div>
      </div>
    </div>

    <!-- Content Panel / Table Skeleton -->
    <div class="gpa-skel-panel">
      <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #d7e0c9; padding-bottom:14px; margin-bottom:16px;">
        <div class="gpa-skel-pill gpa-shimmer" style="width: 180px; height: 16px;"></div>
        <div style="display:flex; gap:8px;">
          <div class="gpa-skel-pill gpa-shimmer" style="width: 80px; height: 32px; border-radius: 8px;"></div>
          <div class="gpa-skel-pill gpa-shimmer" style="width: 110px; height: 32px; border-radius: 8px;"></div>
        </div>
      </div>
      <div style="display:flex; flex-direction:column; gap:10px;">
        <div class="gpa-skel-row gpa-shimmer"></div>
        <div class="gpa-skel-row gpa-shimmer"></div>
        <div class="gpa-skel-row gpa-shimmer"></div>
        <div class="gpa-skel-row gpa-shimmer"></div>
        <div class="gpa-skel-row gpa-shimmer"></div>
      </div>
    </div>
  </div>
</div>

{{-- 3. Dedicated Fullscreen Leaf Loader (Khusus Aksi Tertentu / Eksplisit Saja) --}}
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
      #gpa-loader-overlay svg {
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
    const progressBar = document.getElementById('gpa-top-progress-bar');
    const skeletonOverlay = document.getElementById('gpa-nav-skeleton');
    const leafOverlay = document.getElementById('gpa-loader-overlay');
    const leafTextEl = document.getElementById('gpa-loader-text');
    const leafSubtextEl = document.getElementById('gpa-loader-subtext');

    let progressValue = 0;
    let progressTimer = null;
    let progressGuard = null;
    let navDebounceTimer = null;
    let skeletonDebounceTimer = null;
    let leafSafetyTimer = null;

    // --- 1. Top Progress Bar Engine (Debounced untuk UX yang Nyaman) ---
    function setProgress(n) {
      if (!progressBar) return;
      progressValue = n;
      progressBar.style.transform = 'scaleX(' + n + ')';
    }

    function trickleProgress() {
      const step = progressValue < 0.35 ? 0.08 : progressValue < 0.65 ? 0.03 : 0.006;
      setProgress(Math.min(progressValue + step, 0.90));
    }

    function executeProgressBarStart() {
      if (!progressBar || progressTimer) return;
      progressBar.style.transition = 'transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease';
      progressBar.style.opacity = '1';
      setProgress(0.15);

      progressTimer = setInterval(trickleProgress, 280);
      progressGuard = setTimeout(function () {
        window.gpaProgress.done(true);
      }, 2500);
    }

    window.gpaProgress = {
      // delayMs: 120ms default. Jika halaman berpindah lebih cepat dari 120ms, progress bar tidak muncul (bebas kedip)
      start: function (delayMs) {
        clearTimeout(navDebounceTimer);
        const wait = typeof delayMs === 'number' ? delayMs : 120;
        if (wait <= 0) {
          executeProgressBarStart();
        } else {
          navDebounceTimer = setTimeout(executeProgressBarStart, wait);
        }
      },

      done: function (force) {
        clearTimeout(navDebounceTimer);
        clearTimeout(skeletonDebounceTimer);

        if (!progressBar) return;

        clearInterval(progressTimer);
        clearTimeout(progressGuard);
        progressTimer = null;

        if (progressValue > 0) {
          setProgress(1);
          setTimeout(function () {
            if (!progressBar) return;
            progressBar.style.opacity = '0';
            setTimeout(function () {
              if (!progressBar) return;
              progressBar.style.transition = 'none';
              setProgress(0);
              void progressBar.offsetWidth;
              progressBar.style.transition = 'transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease';
            }, 220);
          }, 140);
        } else {
          progressBar.style.opacity = '0';
          setProgress(0);
        }
      }
    };

    // --- 2. Skeleton Loading Engine (Dinonaktifkan agar tidak menutupi tampilan pengguna) ---
    function executeSkeletonShow() {
      // Nonaktif: Halaman tetap tampil bersih tanpa kedip overlay abu-abu
      return;
    }

    window.showGpaNavSkeleton = function (delayMs) {
      // Safe no-op
      return;
    };

    window.hideGpaNavSkeleton = function () {
      clearTimeout(skeletonDebounceTimer);
      if (skeletonOverlay) {
        skeletonOverlay.classList.remove('is-active');
        skeletonOverlay.style.display = 'none';
      }
      document.body.classList.remove('gpa-is-navigating');
    };

    // --- 3. Dedicated Fullscreen Leaf Loader (Khusus Tempat / Aksi Tertentu Saja) ---
    window.showGpaLoader = function (customText, customSubtext) {
      clearTimeout(leafSafetyTimer);
      if (!leafOverlay) return;

      if (leafTextEl) {
        leafTextEl.textContent = customText || 'Memuat';
      }
      if (leafSubtextEl) {
        if (customSubtext) {
          leafSubtextEl.textContent = customSubtext;
          leafSubtextEl.style.display = 'block';
        } else {
          leafSubtextEl.textContent = '';
          leafSubtextEl.style.display = 'none';
        }
      }

      leafOverlay.classList.add('is-active');
      leafOverlay.setAttribute('aria-busy', 'true');

      leafSafetyTimer = setTimeout(window.hideGpaLoader, 5000);
    };

    window.hideGpaLoader = function () {
      clearTimeout(leafSafetyTimer);
      if (!leafOverlay) return;
      leafOverlay.classList.remove('is-active');
      leafOverlay.setAttribute('aria-busy', 'false');
    };

    // Pastikan status bersih saat halaman baru mendarat
    window.hideGpaNavSkeleton();
    window.hideGpaLoader();

    // --- 4. Interceptor Navigasi yang Nyaman & Responsif (UX Friendly) ---
    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
        return;
      }

      const anchor = e.target.closest('a[href]');
      if (!anchor) return;

      const href = anchor.getAttribute('href');
      const target = anchor.getAttribute('target');
      const download = anchor.getAttribute('download');

      // Abaikan link instan, tab internal, anchor jump, hash, javascript, tel, mailto, tab baru
      const hrefLower = String(href || '').toLowerCase();
      if (
        !href ||
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        href.startsWith('tel:') ||
        href.startsWith('mailto:') ||
        (target && target !== '_self') ||
        download !== null ||
        anchor.hasAttribute('data-no-progress') ||
        anchor.hasAttribute('data-no-loader') ||
        anchor.hasAttribute('data-instant') ||
        hrefLower.includes('export') ||
        hrefLower.includes('.csv') ||
        hrefLower.includes('.pdf') ||
        hrefLower.includes('.xlsx') ||
        hrefLower.includes('/cetak') ||
        hrefLower.includes('download')
      ) {
        return;
      }

      try {
        const url = new URL(href, window.location.origin);
        if (url.origin !== window.location.origin) return;

        // Aksi khusus (misal download laporan berat atau sinkronisasi) yang ditandai data-show-loader
        if (anchor.hasAttribute('data-show-loader') || anchor.hasAttribute('data-gpa-loader')) {
          const customText = anchor.getAttribute('data-loader-text') || 'Memuat';
          const customSub = anchor.getAttribute('data-loader-subtext') || '';
          window.showGpaLoader(customText, customSub);
          return;
        }

        // Navigasi halaman biasa: Gunakan threshold (120ms untuk bar)
        // Jika halaman cepat terbuka (<120ms), tidak ada kedip bar sama sekali!
        if (url.pathname !== window.location.pathname || url.search !== window.location.search) {
          window.gpaProgress.start(120);
        }
      } catch (err) {}
    });

    // --- 5. Interceptor Form Submit ---
    document.addEventListener('submit', function (e) {
      const form = e.target;
      if (!form || e.defaultPrevented || form.hasAttribute('data-no-progress')) return;

      const target = form.getAttribute('target');
      if (target && target !== '_self') return;

      if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        return;
      }

      if (form.hasAttribute('data-show-loader') || form.hasAttribute('data-gpa-loader')) {
        const customText = form.getAttribute('data-loader-text') || 'Memproses...';
        const customSub = form.getAttribute('data-loader-subtext') || 'Menyimpan data transaksi...';
        window.showGpaLoader(customText, customSub);
        return;
      }

      // Pada submit form, respon server membutuhkan waktu pemrosesan: mulai bar di 60ms
      window.gpaProgress.start(60);
    });

    // --- 6. Event Reset Saat Halaman Selesai Dimuat / Navigasi Batal ---
    document.addEventListener('DOMContentLoaded', function () {
      window.gpaProgress.done(true);
      window.hideGpaNavSkeleton();
      window.hideGpaLoader();
    });

    window.addEventListener('load', function () {
      window.gpaProgress.done(true);
      window.hideGpaNavSkeleton();
      window.hideGpaLoader();
    });

    window.addEventListener('focus', function () {
      window.gpaProgress.done(true);
      window.hideGpaNavSkeleton();
      window.hideGpaLoader();
    });

    window.addEventListener('pageshow', function () {
      window.gpaProgress.done(true);
      window.hideGpaNavSkeleton();
      window.hideGpaLoader();
    });

    window.addEventListener('popstate', function () {
      window.gpaProgress.done(true);
      window.hideGpaNavSkeleton();
      window.hideGpaLoader();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        window.gpaProgress.done(true);
        window.hideGpaNavSkeleton();
        window.hideGpaLoader();
      }
    });

    window.addEventListener('popstate', function () {
      window.gpaProgress.done(true);
      window.hideGpaNavSkeleton();
      window.hideGpaLoader();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        window.gpaProgress.done(true);
        window.hideGpaNavSkeleton();
        window.hideGpaLoader();
      }
    });
  })();
</script>

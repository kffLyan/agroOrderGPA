<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light">
<title>Loading GPA</title>
<style>
  :root { --bg:#f8faf3; --fg:#2f4a12; color-scheme:light; box-sizing:border-box; padding-top:env(safe-area-inset-top,0px); padding-bottom:env(safe-area-inset-bottom,0px); }
  html, body { height:100%; margin:0; }
  body { background:var(--bg); color:var(--fg); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:20px; font-family:system-ui,-apple-system,"Segoe UI",sans-serif; }
  svg { width:min(38vw,180px); height:auto; overflow:visible; }
  .ghost { opacity:.09; }
  .leaf { clip-path:inset(0 100% 0 0); }
  .l1 { animation:w1 1.2s cubic-bezier(.45,0,.25,1) forwards; }
  .l2 { animation:w2 1.2s cubic-bezier(.45,0,.25,1) forwards; }
  .l3 { animation:w3 1.2s cubic-bezier(.45,0,.25,1) forwards; }
  @keyframes w1 { 0%{clip-path:inset(0 100% 0 0)} 33%,100%{clip-path:inset(0 0 0 0)} }
  @keyframes w2 { 0%,25%{clip-path:inset(0 100% 0 0)} 66%,100%{clip-path:inset(0 0 0 0)} }
  @keyframes w3 { 0%,55%{clip-path:inset(0 100% 0 0)} 100%{clip-path:inset(0 0 0 0)} }
  p { margin:0; font-size:14px; letter-spacing:.12em; text-transform:uppercase; opacity:.7; }
  @media (prefers-reduced-motion: reduce) { .leaf, svg { animation:none; clip-path:none; } }
</style>
</head>
<body>
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
</body>
</html>

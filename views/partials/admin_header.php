<?php
$adminUser = $_SESSION["aizudemy_admin"] ?? "admin";
$curAdmin = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
$isDash = ($curAdmin==="admin" || $curAdmin==="admin/");
$isPosts = str_starts_with($curAdmin, "admin/posts");
$isAi = str_starts_with($curAdmin, "admin/ai-settings");
$isAds = str_starts_with($curAdmin, "admin/ads-settings");
$isSec = str_starts_with($curAdmin, "admin/security-settings");
$av = '1787221202';
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,viewport-fit=cover">
<title><?= e($title ?? "Admin — AizuDemy") ?></title>
<link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon.png">
<script>(function(){try{var m=document.cookie.match(/(?:^|; )aizudemy_theme=([^;]*)/);var t=m?decodeURIComponent(m[1]):null;if(!t)try{t=localStorage.getItem("aizudemy_theme")}catch(e){} if(!t) t="dark";document.documentElement.setAttribute("data-theme",t)}catch(e){document.documentElement.setAttribute("data-theme","dark")}})();</script>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>">
<style>
/* ===================================================
   AIZUDEMY SAAS ADMIN 2026 — FULL RESPONSIVE SYSTEM
   =================================================== */
:root {
  --safe-top: env(safe-area-inset-top, 0px);
  --safe-bottom: env(safe-area-inset-bottom, 0px);
  --safe-left: env(safe-area-inset-left, 0px);
  --safe-right: env(safe-area-inset-right, 0px);
}

.admin-shell{min-height:100vh;min-height:100dvh;display:flex;flex-direction:column;background:var(--bg);position:relative;overflow-x:hidden;box-sizing:border-box}

/* Top Navbar */
.admin-top{height:58px;display:flex;align-items:center;gap:12px;padding:0 20px;padding-left:max(20px,var(--safe-left));padding-right:max(20px,var(--safe-right));background:var(--header);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:50;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-sizing:border-box}
.admin-top .brand{font-family:Poppins,sans-serif;font-weight:700;color:var(--main);display:flex;align-items:center;gap:8px;text-decoration:none;flex-shrink:0;font-size:16px}
.admin-top .brand span{color:var(--primary)}
.admin-top-nav{display:flex;gap:4px;margin-left:12px;align-items:center}
.admin-top-nav a{padding:6px 12px;border-radius:8px;font-size:12.5px;font-weight:600;color:var(--muted);text-decoration:none;transition:.15s;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.admin-top-nav a.active,.admin-top-nav a:hover{background:rgba(0,90,240,.08);color:var(--primary)}
html[data-theme="dark"] .admin-top-nav a.active{background:rgba(0,90,240,.18);color:#5b8def}
.admin-top .spacer{margin-left:auto}
.admin-top .user{font-size:12px;color:var(--muted);background:var(--bg);border:1px solid var(--border);padding:5px 12px;border-radius:20px;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.admin-top .user b{color:var(--main)}

/* Header Action Buttons */
.admin-header-actions{display:flex;align-items:center;gap:8px}

/* Mobile Menu Toggle Button */
.admin-menu-btn{display:none;align-items:center;justify-content:center;width:38px;height:38px;border-radius:10px;background:var(--bg);border:1px solid var(--border);color:var(--main);font-size:18px;cursor:pointer;transition:.15s;flex-shrink:0}
.admin-menu-btn:hover{background:rgba(0,90,240,.08);color:var(--primary);border-color:var(--primary)}

/* Mobile Off-Canvas Drawer */
.admin-drawer-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);z-index:90;opacity:0;visibility:hidden;transition:opacity .25s ease,visibility .25s ease}
.admin-drawer-overlay.open{opacity:1;visibility:visible}
.admin-drawer{position:fixed;top:0;right:-320px;width:300px;max-width:86vw;height:100%;height:100dvh;background:var(--card);border-left:1px solid var(--border);z-index:95;box-shadow:-10px 0 35px rgba(0,0,0,.35);transition:right .28s cubic-bezier(.16,1,.3,1);display:flex;flex-direction:column;padding:0;overflow-y:auto;-webkit-overflow-scrolling:touch;box-sizing:border-box}
.admin-drawer.open{right:0}
.admin-drawer-header{padding:16px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--header)}
.admin-drawer-close{width:34px;height:34px;border-radius:8px;background:var(--bg);border:1px solid var(--border);color:var(--muted);display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;transition:.15s}
.admin-drawer-close:hover{color:#ef4444;border-color:#ef4444;background:rgba(239,68,68,.08)}
.admin-drawer-user{padding:14px 18px;background:var(--bg);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
.admin-drawer-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#005af0,#00dcc0);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px;flex-shrink:0;box-shadow:0 3px 10px rgba(0,90,240,.3)}
.admin-drawer-menu{padding:12px;display:flex;flex-direction:column;gap:4px;flex:1;overflow-y:auto}
.admin-drawer-link{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;font-size:13.5px;font-weight:600;color:var(--main);text-decoration:none;transition:.15s}
.admin-drawer-link:hover,.admin-drawer-link.active{background:rgba(0,90,240,.08);color:var(--primary)}
html[data-theme="dark"] .admin-drawer-link.active{background:rgba(0,90,240,.18);color:#5b8def}
.admin-drawer-footer{padding:14px 18px;padding-bottom:max(14px,var(--safe-bottom));border-top:1px solid var(--border);display:flex;flex-direction:column;gap:10px;background:var(--header)}

/* Mobile Bottom Navigation Bar */
.admin-bottom-nav{display:none;position:fixed;bottom:0;left:0;right:0;height:calc(58px + var(--safe-bottom));padding-bottom:var(--safe-bottom);background:var(--header);border-top:1px solid var(--border);z-index:40;box-shadow:0 -4px 20px rgba(0,0,0,.08);padding-left:6px;padding-right:6px;justify-content:space-around;align-items:center;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);box-sizing:border-box}
html[data-theme="dark"] .admin-bottom-nav{box-shadow:0 -4px 25px rgba(0,0,0,.45)}
.admin-bottom-item{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;flex:1;height:100%;color:var(--muted);text-decoration:none;font-size:10.5px;font-weight:700;transition:.15s;padding:4px 0;box-sizing:border-box}
.admin-bottom-item .b-icon{font-size:19px;line-height:1}
.admin-bottom-item.active,.admin-bottom-item:hover{color:var(--primary)}
html[data-theme="dark"] .admin-bottom-item.active{color:#5b8def}

/* Main Container & Universal Mobile Responsiveness */
.admin-main{flex:1;padding:24px 24px;width:100%;box-sizing:border-box}
.admin-container{max-width:1280px;margin:0 auto;width:100%;box-sizing:border-box}
.admin-wrap{width:100%;margin:0 auto;box-sizing:border-box}
.admin-card{background:var(--card);border-radius:14px;border:1px solid var(--border);box-shadow:0 4px 16px rgba(0,0,0,.04);padding:20px;box-sizing:border-box}
html[data-theme="dark"] .admin-card{box-shadow:0 4px 20px rgba(0,0,0,.25)}

/* Table Responsive Horizontal Container */
.table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;margin:0;border-radius:10px}
.table-responsive::-webkit-scrollbar{height:5px}
.table-responsive::-webkit-scrollbar-thumb{background:rgba(0,90,240,.25);border-radius:6px}
.table{width:100%;border-collapse:collapse;font-size:13px}
.table th,.table td{padding:10px 12px;border-bottom:1px solid var(--border);text-align:left}
.table th{font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);background:rgba(0,0,0,.02);font-weight:700}
html[data-theme="dark"] .table th{background:rgba(255,255,255,.02)}

/* Form Controls Mobile Friendly */
.input, select.input, textarea.input{width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:10px;outline:0;font-size:13.5px;background:var(--card);color:var(--main);box-sizing:border-box;transition:border-color .15s,box-shadow .15s}
.input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,90,240,.12)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 16px;border-radius:9px;border:0;cursor:pointer;font-size:13px;font-weight:600;text-decoration:none;transition:all .15s;white-space:nowrap;box-sizing:border-box;min-height:38px}
.btn-primary{background:var(--primary);color:#fff}
.btn-primary:hover{background:var(--primary-hover);color:#fff}
.btn-ghost{background:var(--card);border:1px solid var(--border);color:var(--main)}
.btn-ghost:hover{border-color:var(--primary);color:var(--primary)}

/* ===================================================
   RESPONSIVE BREAKPOINTS (TABLETS & SMARTPHONES)
   =================================================== */
@media(max-width: 900px){
  .admin-top-nav{display:none}
  .admin-top .user{display:none}
  .admin-menu-btn{display:flex}
  .admin-bottom-nav{display:flex}
  .admin-main{padding:16px 14px calc(76px + var(--safe-bottom)) 14px}
  .admin-card{padding:16px;border-radius:12px}
  .admin-top .btn-logout-top{display:none}
}

@media(max-width: 640px){
  .admin-top{padding:0 12px;height:54px;gap:8px}
  .admin-top .brand{font-size:15px;gap:6px}
  .admin-top .brand-logo{width:26px;height:26px}
  .admin-main{padding:12px 10px calc(76px + var(--safe-bottom)) 10px}
  .admin-card{padding:14px 12px;border-radius:12px}
}

@media(max-width: 380px){
  .admin-top .brand-text small{display:none}
}
</style>
</head><body class="admin-shell">

<!-- TOP NAVBAR -->
<header class="admin-top">
  <a class="brand" href="<?= BASE_URL ?>/admin">
    <img src="<?= BASE_URL ?>/assets/images/watermark_badge.png" alt="AizuDemy" class="brand-logo" width="28" height="28" style="border-radius:50%;object-fit:cover;box-shadow:0 2px 6px rgba(0,0,0,.15)">
    <span class="brand-text">Aizu<b style="color:var(--primary)">Demy</b> <small style="font-weight:700;color:var(--primary);font-size:10.5px;margin-left:4px;background:rgba(0,90,240,.08);padding:2px 6px;border-radius:5px">Admin</small></span>
  </a>

  <!-- Desktop Nav Links -->
  <nav class="admin-top-nav">
    <a href="<?= BASE_URL ?>/admin" class="<?= $isDash?"active":"" ?>">📊 Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/posts" class="<?= $isPosts?"active":"" ?>">📝 Artikel</a>
    <a href="<?= BASE_URL ?>/admin/ai-settings" class="<?= $isAi?"active":"" ?>">🤖 AI Engine</a>
    <a href="<?= BASE_URL ?>/admin/ads-settings" class="<?= $isAds?"active":"" ?>">📢 Iklan</a>
    <a href="<?= BASE_URL ?>/admin/security-settings" class="<?= $isSec?"active":"" ?>">🔒 2FA Sentinel</a>
    <a href="<?= BASE_URL ?>/" target="_blank">🌐 Lihat Blog ↗</a>
  </nav>

  <div class="spacer"></div>

  <div class="admin-header-actions">
    <span class="user">👤 <b><?= e($adminUser) ?></b></span>
    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Ganti tema"><span class="knob"><span class="icon">🌙</span></span></button>
    <a class="btn btn-ghost btn-logout-top" href="<?= BASE_URL ?>/admin/logout" style="padding:6px 12px;font-size:12px">Logout</a>
    <button type="button" class="admin-menu-btn" id="openDrawerBtn" aria-label="Buka Menu">☰</button>
  </div>
</header>

<!-- MOBILE OFF-CANVAS DRAWER -->
<div class="admin-drawer-overlay" id="drawerOverlay"></div>
<aside class="admin-drawer" id="adminDrawer">
  <div class="admin-drawer-header">
    <div style="display:flex;align-items:center;gap:8px">
      <img src="<?= BASE_URL ?>/assets/images/watermark_badge.png" alt="AizuDemy" width="26" height="26" style="border-radius:50%">
      <span style="font-weight:700;font-size:15px;color:var(--main)">Menu Admin</span>
    </div>
    <button type="button" class="admin-drawer-close" id="closeDrawerBtn" aria-label="Tutup">&times;</button>
  </div>
  <div class="admin-drawer-user">
    <div class="admin-drawer-avatar"><?= strtoupper(substr($adminUser, 0, 1)) ?></div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:700;font-size:13.5px;color:var(--main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($adminUser) ?></div>
      <div style="font-size:11px;color:var(--primary);font-weight:700">God Administrator</div>
    </div>
  </div>
  <div class="admin-drawer-menu">
    <a href="<?= BASE_URL ?>/admin" class="admin-drawer-link <?= $isDash?"active":"" ?>"><span>📊</span> Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/posts" class="admin-drawer-link <?= $isPosts?"active":"" ?>"><span>📝</span> Kelola Artikel</a>
    <a href="<?= BASE_URL ?>/admin/ai-settings" class="admin-drawer-link <?= $isAi?"active":"" ?>"><span>🤖</span> AI Engine Settings</a>
    <a href="<?= BASE_URL ?>/admin/ads-settings" class="admin-drawer-link <?= $isAds?"active":"" ?>"><span>📢</span> Pengaturan Iklan & AdSense</a>
    <a href="<?= BASE_URL ?>/admin/security-settings" class="admin-drawer-link <?= $isSec?"active":"" ?>"><span>🔒</span> WhatsApp 2FA Sentinel</a>
    <a href="<?= BASE_URL ?>/" target="_blank" class="admin-drawer-link"><span>🌐</span> Kunjungi Blog Live ↗</a>
  </div>
  <div class="admin-drawer-footer">
    <button type="button" id="drawerThemeToggle" class="btn btn-ghost" style="width:100%;justify-content:center;gap:8px;font-size:12.5px">
      <span id="drawerThemeIcon">🌙</span> <span id="drawerThemeText">Mode Gelap</span>
    </button>
    <a href="<?= BASE_URL ?>/admin/logout" class="btn btn-ghost" style="width:100%;color:#dc2626;border-color:rgba(220,38,38,.3);justify-content:center">🚪 Logout Akun</a>
  </div>
</aside>

<!-- MOBILE BOTTOM NAVIGATION (Quick thumb reach for smartphones) -->
<nav class="admin-bottom-nav">
  <a href="<?= BASE_URL ?>/admin" class="admin-bottom-item <?= $isDash?"active":"" ?>">
    <span class="b-icon">📊</span>
    <span>Dash</span>
  </a>
  <a href="<?= BASE_URL ?>/admin/posts" class="admin-bottom-item <?= $isPosts?"active":"" ?>">
    <span class="b-icon">📝</span>
    <span>Artikel</span>
  </a>
  <a href="<?= BASE_URL ?>/admin/ai-settings" class="admin-bottom-item <?= $isAi?"active":"" ?>">
    <span class="b-icon">🤖</span>
    <span>AI</span>
  </a>
  <a href="<?= BASE_URL ?>/admin/security-settings" class="admin-bottom-item <?= $isSec?"active":"" ?>">
    <span class="b-icon">🔒</span>
    <span>2FA</span>
  </a>
  <a href="javascript:void(0)" class="admin-bottom-item" id="bottomMoreBtn">
    <span class="b-icon">☰</span>
    <span>Menu</span>
  </a>
</nav>

<main class="admin-main"><div class="admin-container">

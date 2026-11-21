<?php
$adminUser = $_SESSION["aizudemy_admin"] ?? "admin";
$curAdmin = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
$isDash = ($curAdmin==="admin" || $curAdmin==="admin/");
$isPosts = str_starts_with($curAdmin, "admin/posts");
$isCats = str_starts_with($curAdmin, "admin/categories");
$isAi = str_starts_with($curAdmin, "admin/ai-settings");
$isAds = str_starts_with($curAdmin, "admin/ads-settings");
$isSeo = str_starts_with($curAdmin, "admin/seo-settings");
$isAccount = str_starts_with($curAdmin, "admin/account");
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? "Admin — AizuDemy") ?></title>
<script>(function(){try{var m=document.cookie.match(/(?:^|; )aizudemy_theme=([^;]*)/);var t=m?decodeURIComponent(m[1]):null;if(!t)try{t=localStorage.getItem("aizudemy_theme")}catch(e){} if(!t) t="dark";document.documentElement.setAttribute("data-theme",t)}catch(e){document.documentElement.setAttribute("data-theme","dark")}})();</script>
<link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
<link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon.png">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>">
<style>
.admin-shell{min-height:100vh;display:flex;flex-direction:column;background:var(--bg)}
.admin-top{height:56px;display:flex;align-items:center;gap:14px;padding:0 20px;background:var(--header);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:30}
.admin-top .brand{font-family:Poppins,sans-serif;font-weight:700;color:var(--main);display:flex;align-items:center;gap:6px;flex-shrink:0;text-decoration:none}
.admin-top .brand span{color:var(--primary)}
.admin-top nav{display:flex;gap:6px;margin-left:8px;overflow-x:auto;white-space:nowrap;scrollbar-width:none;-ms-overflow-style:none}
.admin-top nav::-webkit-scrollbar{display:none}
.admin-top nav a{padding:7px 11px;border-radius:8px;font-size:13px;font-weight:600;color:var(--muted);white-space:nowrap;flex-shrink:0;text-decoration:none}
.admin-top nav a.active,.admin-top nav a:hover{background:rgba(0,90,240,.12);color:var(--primary)}
.admin-top .spacer{margin-left:auto;flex-shrink:0}
.admin-top .user{font-size:12px;color:var(--muted);white-space:nowrap;flex-shrink:0}
@media(max-width:768px){
  .admin-top{padding:0 10px;gap:6px}
  .admin-top .brand small{display:none}
  .admin-top nav{margin-left:2px;gap:3px}
  .admin-top nav a{padding:6px 8px;font-size:12px}
  .admin-top .user{display:none}
}
</style>
</head><body class="admin-shell">
<header class="admin-top">
  <a class="brand" href="<?= BASE_URL ?>/admin"><span>✦</span> AizuDemy <small style="font-weight:400;color:var(--muted)">Admin</small></a>
  <nav>
    <a href="<?= BASE_URL ?>/admin" class="<?= $isDash?"active":"" ?>">📊 Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/posts" class="<?= $isPosts?"active":"" ?>">📝 Artikel</a>
    <a href="<?= BASE_URL ?>/admin/categories" class="<?= $isCats?"active":"" ?>">📂 Kategori</a>
    <a href="<?= BASE_URL ?>/admin/ai-settings" class="<?= $isAi?"active":"" ?>">🤖 AI</a>
    <a href="<?= BASE_URL ?>/admin/ads-settings" class="<?= $isAds?"active":"" ?>">📢 AdSense</a>
    <a href="<?= BASE_URL ?>/admin/seo-settings" class="<?= $isSeo?"active":"" ?>">🔍 SEO</a>
    <a href="<?= BASE_URL ?>/admin/account" class="<?= $isAccount?"active":"" ?>">⚙️ Akun</a>
    <a href="<?= BASE_URL ?>/" target="_blank">🌐 Lihat Blog</a>
  </nav>
  <div class="spacer"></div>
  <button type="button" class="btn btn-ghost" onclick="toggleShortcutModal()" style="padding:5px 10px;font-size:12px;display:flex;align-items:center;gap:4px" title="Lihat Pintasan Keyboard (Shift + ?)">⌨️ Hotkeys</button>
  <a class="user" href="<?= BASE_URL ?>/admin/account" style="text-decoration:none">👤 <?= e($adminUser) ?></a>
  <button type="button" id="themeToggle" class="theme-toggle" aria-label="Ganti tema"><span class="knob"><span class="icon">🌙</span></span></button>
  <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/logout" style="padding:6px 12px;font-size:12px">Logout</a>
</header>
<main class="admin-main" style="flex:1;padding:16px 20px"><div style="max-width:1280px;margin:0 auto">

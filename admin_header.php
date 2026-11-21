<?php
$adminUser = $_SESSION["aizudemy_admin"] ?? "admin";
$curAdmin = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
$isDash = ($curAdmin==="admin" || $curAdmin==="admin/");
$isPosts = str_starts_with($curAdmin, "admin/posts");
$isAi = str_starts_with($curAdmin, "admin/ai-settings");
$av = '1787221202';
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? "Admin — AizuDemy") ?></title>
<script>(function(){try{var m=document.cookie.match(/(?:^|; )aizudemy_theme=([^;]*)/);var t=m?decodeURIComponent(m[1]):null;if(!t)try{t=localStorage.getItem("aizudemy_theme")}catch(e){} if(!t) t="dark";document.documentElement.setAttribute("data-theme",t)}catch(e){document.documentElement.setAttribute("data-theme","dark")}})();</script>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=1787226957">
<style>
.admin-shell{min-height:100vh;display:flex;flex-direction:column;background:var(--bg)}
.admin-top{height:56px;display:flex;align-items:center;gap:16px;padding:0 20px;background:var(--header);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:30}
.admin-top .brand{font-family:Poppins,sans-serif;font-weight:700;color:var(--main);display:flex;align-items:center;gap:8px}
.admin-top .brand span{color:var(--primary)}
.admin-top nav{display:flex;gap:6px;margin-left:14px}
.admin-top nav a{padding:7px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--muted)}
.admin-top nav a.active,.admin-top nav a:hover{background:rgba(0,90,240,.08);color:var(--primary)}
.admin-top .spacer{margin-left:auto}
.admin-top .user{font-size:12px;color:var(--muted)}
@media(max-width:640px){.admin-top{padding:0 14px;gap:10px}.admin-top nav{gap:2px;margin-left:6px}.admin-top nav a{padding:6px 8px;font-size:12px}.admin-top .user{display:none}}
</style>
</head><body class="admin-shell">
<header class="admin-top">
  <a class="brand" href="<?= BASE_URL ?>/admin" style="display:flex;align-items:center;gap:9px;text-decoration:none">
    <img src="<?= BASE_URL ?>/assets/images/watermark_badge.png" alt="AizuDemy" class="brand-logo" width="30" height="30" style="width:30px;height:30px;border-radius:50%;object-fit:cover;box-shadow:0 2px 6px rgba(0,0,0,.15)">
    <span class="brand-text" style="display:inline-flex;align-items:center">Aizu<b style="color:var(--primary)">Demy</b> <small style="font-weight:600;color:var(--primary);font-size:11px;margin-left:6px;background:rgba(0,90,240,.08);padding:2px 7px;border-radius:6px">Admin</small></span>
  </a>
  <nav>
    <a href="<?= BASE_URL ?>/admin" class="<?= $isDash?"active":"" ?>">📊 Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/posts" class="<?= $isPosts?"active":"" ?>">📝 Artikel</a>
    <a href="<?= BASE_URL ?>/admin/ai-settings" class="<?= $isAi?"active":"" ?>">🤖 AI</a>
    <a href="<?= BASE_URL ?>/" target="_blank">🌐 Lihat Blog</a>
  </nav>
  <div class="spacer"></div>
  <span class="user">👤 <?= e($adminUser) ?></span>
  <button type="button" id="themeToggle" class="theme-toggle" aria-label="Ganti tema"><span class="knob"><span class="icon">🌙</span></span></button>
  <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/logout" style="padding:6px 12px;font-size:12px">Logout</a>
</header>
<main style="flex:1;padding:20px 24px"><div style="max-width:1280px;margin:0 auto">

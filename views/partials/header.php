<?php
$cats = all_categories();
$cur  = $_GET['cat'] ?? '';
$q    = $_GET['q'] ?? '';
$uri  = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$isHome = ($uri==='' || $uri==='aizudemy' || $uri==='aizudemy/');
$navUri = $uri;
$__base = trim(dirname($_SERVER["SCRIPT_NAME"]), "/");
if ($__base !== "" && $__base !== "/" && strpos($navUri, $__base) === 0) $navUri = trim(substr($navUri, strlen($__base)), "/");
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? SITE_NAME) ?> — <?= e(SITE_DESC) ?></title>
<meta name="description" content="<?= e($desc ?? SITE_DESC) ?>">
<?php
// OG / Twitter Card — preview saat dibagikan (WA/FB/Twitter)
$__ogTitle = $title ?? SITE_NAME;
$__ogDesc  = trim(strip_tags($desc ?? SITE_DESC));
if(mb_strlen($__ogDesc) > 160) $__ogDesc = mb_substr($__ogDesc, 0, 157)."...";
$__scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$__host   = $_SERVER["HTTP_HOST"] ?? "localhost";
$__path   = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
$__ogUrl  = $__scheme."://".$__host.$__path;
$__ogImage = "";
$__ogType  = isset($post) && !empty($post) ? "article" : "website";
if(!empty($post["thumbnail"] ?? null)){
  $__t = $post["thumbnail"];
  if(str_starts_with($__t, "http")) $__ogImage = $__t;
  elseif(str_starts_with($__t, "/")) $__ogImage = $__scheme."://".$__host.$__t;
  else $__ogImage = rtrim(BASE_URL,"/")."/".ltrim($__t,"/");
} elseif(!empty($post["content"] ?? null) && preg_match('/src="([^"]+)"/i', $post["content"], $__m)){
  $__src = $__m[1];
  if(str_starts_with($__src, "http")) $__ogImage = $__src;
  elseif(str_starts_with($__src, "/")) $__ogImage = $__scheme."://".$__host.$__src;
  else $__ogImage = rtrim(BASE_URL,"/")."/".ltrim($__src,"/");
}
// fallback: pakai thumbnail post terbaru atau watermark badge
if($__ogImage === ""){
  try{ 
    $__fb = db()->query("SELECT thumbnail FROM posts WHERE status='publish' AND thumbnail IS NOT NULL AND thumbnail != '' ORDER BY id DESC LIMIT 1")->fetchColumn(); 
    if($__fb){ 
      if(str_starts_with($__fb,"http")) $__ogImage=$__fb; 
      elseif(str_starts_with($__fb,"/")) $__ogImage=$__scheme."://".$__host.$__fb; 
      else $__ogImage=rtrim(BASE_URL,"/")."/".ltrim($__fb,"/"); 
    } else {
      $__ogImage = rtrim(BASE_URL,"/")."/assets/images/watermark_badge.png";
    }
  } catch (Throwable $e){
    $__ogImage = rtrim(BASE_URL,"/")."/assets/images/watermark_badge.png";
  }
}
$__ogMime = "image/jpeg";
if (str_ends_with(strtolower($__ogImage), ".png")) $__ogMime = "image/png";
elseif (str_ends_with(strtolower($__ogImage), ".webp")) $__ogMime = "image/webp";
?>
<link rel="alternate" type="application/rss+xml" title="AizuDemy RSS Feed" href="<?= BASE_URL ?>/feed">
    <link rel="canonical" href="<?= e($__ogUrl) ?>">

<!-- Open Graph / WhatsApp / Facebook -->
<meta property="og:type" content="<?= e($__ogType) ?>">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="<?= e($__ogTitle) ?>">
<meta property="og:description" content="<?= e($__ogDesc) ?>">
<meta property="og:url" content="<?= e($__ogUrl) ?>">
<?php if($__ogImage !== ""): ?>
<meta property="og:image" content="<?= e($__ogImage) ?>">
<meta property="og:image:secure_url" content="<?= e($__ogImage) ?>">
<meta property="og:image:type" content="<?= $__ogMime ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($__ogTitle) ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($__ogTitle) ?>">
<meta name="twitter:description" content="<?= e($__ogDesc) ?>">
<?php if($__ogImage !== ""): ?>
<meta name="twitter:image" content="<?= e($__ogImage) ?>">
<meta name="twitter:image:alt" content="<?= e($__ogTitle) ?>">
<?php endif; ?>
<?php
$__adsClientId = setting('adsense_client_id', 'ca-pub-1354033982953234');
$__adsAuto = setting('adsense_auto_ads', '1');
if (!empty($__adsClientId)): ?>
<meta name='impact-site-verification' value='d7ed82de-0bb5-415d-b7af-ec8fc2369483'>
    <meta name="google-adsense-account content="<?= e($__adsClientId) ?>">
<?php if ($__adsAuto === '1'): ?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e($__adsClientId) ?>" crossorigin="anonymous"></script>
<?php endif; ?>
<?php endif; ?>
<script>(function(){try{var m=document.cookie.match(/(?:^|; )aizudemy_theme=([^;]*)/);var t=m?decodeURIComponent(m[1]):null;if(!t)try{t=localStorage.getItem("aizudemy_theme")}catch(e){} if(!t) t="dark";document.documentElement.setAttribute("data-theme",t)}catch(e){document.documentElement.setAttribute("data-theme","dark")}})();</script>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= filemtime(__DIR__.'/../../public/css/style.css') ?: 1787228800 ?>">
</head><body>
<input type="checkbox" id="navToggle" class="nav-toggle" hidden>
<header class="header">
  <label for="navToggle" class="hamburger" aria-label="Menu"><i></i><i></i><i></i></label>
  <a class="brand" href="<?= BASE_URL ?>/">
    <img src="<?= BASE_URL ?>/assets/images/watermark_badge.png" alt="AizuDemy" class="brand-logo" width="32" height="32">
    <span class="brand-text">Aizu<b>Demy</b></span>
  </a>
  <div class="head-right">
    <form id="searchForm" class="search" action="<?= BASE_URL ?>/search" method="get">
      <input type="text" name="q" placeholder="Cari tutorial, topik, materi..." value="<?= e($q) ?>">
      <button type="submit" aria-label="Cari">⌕</button>
    </form>
    <button type="button" class="search-icon-btn" aria-label="Cari" onclick="document.querySelector('.header .search input').focus()"><span>⌕</span></button>
    <button type="button" id="themeToggle" class="theme-toggle" aria-label="Ganti tema"><span class="knob"><span class="icon">🌙</span></span></button>
    <button type="button" class="search-toggle-btn" aria-label="Search">⌕</button>
  </div>
</header>
<script>
(function(){
  function getTheme(){ return document.documentElement.getAttribute("data-theme")||"dark"; }
  function applyTheme(t){
    document.documentElement.setAttribute("data-theme", t);
    try{ localStorage.setItem("aizudemy_theme", t); }catch(e){}
    document.cookie = "aizudemy_theme="+encodeURIComponent(t)+"; path=/; max-age=31536000; SameSite=Lax";
    var icon = document.querySelector("#themeToggle .icon");
    if(icon) icon.textContent = t==="dark" ? "🌙" : "☀️";
  }
  // init icon
  applyTheme(getTheme());
  document.addEventListener("DOMContentLoaded", function(){
    applyTheme(getTheme());
    var btn=document.getElementById("themeToggle");
    if(btn) btn.addEventListener("click", function(){
      var cur=getTheme();
      applyTheme(cur==="dark" ? "light" : "dark");
    });
  });
})();
</script>

<label for="navToggle" class="nav-overlay"></label>
<nav class="nav">
  <h4>Navigasi</h4>
  <a href="<?= BASE_URL ?>/" class="<?= $isHome?'active':'' ?>">🏠 Beranda</a>
  <?php
  // === Filter kategori yang hanya memiliki artikel aktif (>0) ===
  $activeCats = [];
  foreach($cats as $c) {
    $cCnt = count_posts($c['id']);
    if ($cCnt > 0) {
      $c['post_count'] = $cCnt;
      $activeCats[] = $c;
    }
  }

  // Grup kategori dinamis & rapi
  $navGroups = [
    '🤖 AI & Teknologi'    => ['kecerdasan-buatan', 'teknologi'],
    '🛡️ Cyber Security'   => ['cyber-security', 'belajar-hacking'],
    '💻 Web Development'   => ['web-development', 'html', 'php', 'mysqli'],
    '☁️ Server & Cloud'    => ['tutorial', 'cyberpanel'],
    '📱 Android & Mobile'  => ['android'],
  ];

  $catMap = []; 
  foreach($activeCats as $c) $catMap[$c['slug']] = $c;
  $handled = [];

  foreach($navGroups as $gName => $slugs):
    $items = array_filter(array_map(fn($s)=>$catMap[$s]??null, $slugs));
    if(empty($items)) continue;
    $isOpen = in_array($cur, array_column($items,'slug'));
  ?>
    <details class="nav-group" <?= $isOpen?'open':'' ?>>
      <summary><?= e($gName) ?> <span class="nav-count"><?= array_sum(array_column($items, 'post_count')) ?></span></summary>
      <div class="nav-group-items">
        <?php foreach($items as $c): $handled[]=$c['slug']; ?>
          <a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>" class="<?= $cur===$c['slug']?'active':'' ?>">
            — <?= e($c['name']) ?> <span style="font-size:11px;color:var(--muted);float:right">(<?= $c['post_count'] ?>)</span>
          </a>
        <?php endforeach; ?>
      </div>
    </details>
  <?php endforeach;

  // Sisa kategori aktif yang belum masuk grup di atas
  $rest = array_filter($activeCats, fn($c)=>!in_array($c['slug'],$handled));
  if(!empty($rest)):
  ?>
    <details class="nav-group" <?= in_array($cur,array_column($rest,'slug'))?'open':'' ?>>
      <summary>📦 Topik Lainnya <span class="nav-count"><?= array_sum(array_column($rest, 'post_count')) ?></span></summary>
      <div class="nav-group-items">
        <?php foreach($rest as $c): ?>
          <a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>" class="<?= $cur===$c['slug']?'active':'' ?>">
            — <?= e($c['name']) ?> <span style="font-size:11px;color:var(--muted);float:right">(<?= $c['post_count'] ?>)</span>
          </a>
        <?php endforeach; ?>
      </div>
    </details>
  <?php endif; ?>

  <div class="sep"></div>
  <h4>Informasi & Kebijakan</h4>
  <a href="<?= BASE_URL ?>/about" class="<?= $navUri==="about"?"active":"" ?>">ℹ️ Tentang Kami</a>
  <a href="<?= BASE_URL ?>/contact" class="<?= $navUri==="contact"?"active":"" ?>">✉️ Hubungi Kami</a>
  <a href="<?= BASE_URL ?>/privacy" class="<?= $navUri==="privacy"?"active":"" ?>">🔒 Kebijakan Privasi</a>
  <a href="<?= BASE_URL ?>/syarat-ketentuan" class="<?= in_array($navUri,["syarat-ketentuan","terms"])?"active":"" ?>">📜 Syarat & Ketentuan</a>
  <a href="<?= BASE_URL ?>/disclaimer" class="<?= in_array($navUri,["disclaimer","disclimer"])?"active":"" ?>">⚠️ Disclaimer</a>
  <div class="sep"></div>
  <div class="nav-foot">Media & Portal Edukasi Teknologi Informasi.<br>Dikelola dengan ❤️ oleh Aizu Dev.</div>
</nav>
<nav class="nav-mobile" aria-label="Mobile nav">
  <a href="<?= BASE_URL ?>/" class="<?= $isHome?'active':'' ?>">
    <span class="icon">🏠</span>
    <span>Beranda</span>
  </a>
  <a href="<?= BASE_URL ?>/category/kecerdasan-buatan" class="<?= ($cur??'')==='kecerdasan-buatan'?'active':'' ?>">
    <span class="icon">🤖</span>
    <span>AI Tekno</span>
  </a>
  <a href="<?= BASE_URL ?>/category/cyber-security" class="<?= ($cur??'')==='cyber-security'?'active':'' ?>">
    <span class="icon">🛡️</span>
    <span>Security</span>
  </a>
  <a href="<?= BASE_URL ?>/search" class="<?= ($navUri??'')==='search'?'active':'' ?>">
    <span class="icon">🔍</span>
    <span>Cari</span>
  </a>
  <a href="javascript:void(0)" onclick="var t=document.getElementById('navToggle'); if(t) t.checked=!t.checked;">
    <span class="icon">📂</span>
    <span>Kategori</span>
  </a>
</nav>
<main class="main"><div class="container">

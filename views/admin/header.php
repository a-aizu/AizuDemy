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
<?php
$seoSiteName = setting('seo_site_name', SITE_NAME);
$seoSiteTagline = setting('seo_site_tagline', SITE_DESC);
$seoSiteDesc = setting('seo_site_desc', SITE_DESC);
$seoKeywords = setting('seo_site_keywords', '');
$seoAuthor = setting('seo_site_author', 'Aizu Dev');
$seoRobots = setting('seo_robots_directive', 'index, follow');
$googleVerif = setting('seo_google_verification', '');
$bingVerif = setting('seo_bing_verification', '');
$gaId = setting('seo_ga_id', '');

$pageTitle = ($title ?? $seoSiteName) . ' — ' . $seoSiteTagline;
$pageDesc  = !empty($desc) ? $desc : $seoSiteDesc;
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<?php if($seoKeywords !== ''): ?><meta name="keywords" content="<?= e($seoKeywords) ?>"><?php endif; ?>
<meta name="author" content="<?= e($seoAuthor) ?>">
<meta name="robots" content="<?= e($seoRobots) ?>">
<?php if($googleVerif !== ''): ?>
<meta name="google-site-verification" content="<?= e(str_replace('google-site-verification=', '', $googleVerif)) ?>">
<?php endif; ?>
<?php if($bingVerif !== ''): ?>
<meta name="msvalidate.01" content="<?= e(str_replace('msvalidate.01=', '', $bingVerif)) ?>">
<?php endif; ?>

<?php
// OG / Twitter Card — biar pas dishare keluar preview (WA/FB/Twitter)
$__ogTitle = $title ?? $seoSiteName;
$__ogDesc  = trim(strip_tags($pageDesc));
if(mb_strlen($__ogDesc) > 160) $__ogDesc = mb_substr($__ogDesc, 0, 157)."...";
$__scheme = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$__host   = $_SERVER["HTTP_HOST"] ?? "localhost";
$__path   = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
$__ogUrl  = $__scheme."://".$__host.$__path;
$__ogImage = setting('seo_site_og_image', '');
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
// fallback: pakai thumbnail post terbaru biar home/category tetap ada preview
if($__ogImage === ""){
  try{ $__fb = db()->query("SELECT thumbnail FROM posts WHERE status='publish' AND thumbnail IS NOT NULL AND thumbnail != '' ORDER BY updated_at DESC LIMIT 1")->fetchColumn(); if($__fb){ if(str_starts_with($__fb,"http")) $__ogImage=$__fb; elseif(str_starts_with($__fb,"/")) $__ogImage=$__scheme."://".$__host.$__fb; else $__ogImage=rtrim(BASE_URL,"/")."/".ltrim($__fb,"/"); } } catch (Exception $e){}
}
?>
<link rel="canonical" href="<?= e($__ogUrl) ?>">
<meta property="og:type" content="<?= e($__ogType) ?>">
<meta property="og:site_name" content="<?= e($seoSiteName) ?>">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="<?= e($__ogTitle) ?>">
<meta property="og:description" content="<?= e($__ogDesc) ?>">
<meta property="og:url" content="<?= e($__ogUrl) ?>">
<?php if($__ogImage !== ""): ?><meta property="og:image" content="<?= e($__ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($__ogTitle) ?>">
<meta name="twitter:description" content="<?= e($__ogDesc) ?>">
<?php if($__ogImage !== ""): ?><meta name="twitter:image" content="<?= e($__ogImage) ?>"><?php endif; ?>

<?php if($gaId !== ''): ?>
<!-- Google Analytics 4 (GA4) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= e($gaId) ?>');
</script>
<?php endif; ?>

<?php if(isset($post) && !empty($post)): ?>
<!-- Schema.org JSON-LD Article Rich Snippet -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": <?= json_encode($post['title']) ?>,
  "description": <?= json_encode(mb_substr(strip_tags($post['excerpt'] ?? $post['content']), 0, 160)) ?>,
  "image": [<?= json_encode($__ogImage ?: (rtrim(BASE_URL,'/').'/assets/images/favicon.png')) ?>],
  "datePublished": <?= json_encode(date('c', strtotime($post['created_at']))) ?>,
  "dateModified": <?= json_encode(date('c', strtotime($post['updated_at'] ?? $post['created_at']))) ?>,
  "author": {
    "@type": "Organization",
    "name": "AizuDemy",
    "url": "https://aizudemy.net"
  },
  "publisher": {
    "@type": "Organization",
    "name": "AizuDemy",
    "logo": {
      "@type": "ImageObject",
      "url": "https://aizudemy.net/assets/images/favicon.png"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": <?= json_encode($__ogUrl) ?>
  }
}
</script>
<?php else: ?>
<!-- Schema.org JSON-LD WebSite -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "AizuDemy",
  "url": "https://aizudemy.net",
  "description": "Platform Belajar Digital — Tutorial Vibe Coding, Cyber Security & Teknologi",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://aizudemy.net/search?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
<?php endif; ?>

<?php
$adsenseClient = setting('adsense_client_id', '');
$adsenseAuto = setting('adsense_auto_ads', '0');
$adsenseHead = setting('adsense_head_code', '');
$hasHeadScript = (strpos($adsenseHead, 'pagead2.googlesyndication.com') !== false);
?>
<?php if($adsenseClient !== ''): ?>
<!-- Google AdSense Account Verification Meta Tag -->
<meta name="google-adsense-account" content="<?= e($adsenseClient) ?>">
<?php endif; ?>
<?php if($adsenseClient !== '' && !$hasHeadScript): ?>
<!-- Google AdSense Auto-Ads Script -->
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e($adsenseClient) ?>" crossorigin="anonymous"></script>
<?php endif; ?>
<?php if($adsenseHead !== ''): ?>
<!-- Custom AdSense Head Code -->
<?= $adsenseHead ?>
<?php endif; ?>

<script>(function(){try{var m=document.cookie.match(/(?:^|; )aizudemy_theme=([^;]*)/);var t=m?decodeURIComponent(m[1]):null;if(!t)try{t=localStorage.getItem("aizudemy_theme")}catch(e){} if(!t) t="dark";document.documentElement.setAttribute("data-theme",t)}catch(e){document.documentElement.setAttribute("data-theme","dark")}})();</script>
<link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon.png">
<link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/favicon.png">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>">
</head><body>
<input type="checkbox" id="navToggle" class="nav-toggle" hidden>
<header class="header">
  <label for="navToggle" class="hamburger" aria-label="Menu"><i></i><i></i><i></i></label>
  <a class="brand" href="<?= BASE_URL ?>/"><span>✦</span> AizuDemy</a>
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
  // === Grup kategori dropdown ===
  $navGroups = [
    '💻 Web App'        => ['html','php','mysqli','php-script','java'],
    '🛡️ Cyber Security' => ['belajar-hacking','cyber-security','cyberpanel','trick'],
    '🤖 Teknologi & AI' => ['teknologi','kecerdasan-buatan','tutorial','videos','android'],
    '📚 Edukasi & Umum' => ['pendidikan','artikel','umum'],
  ];
  $catMap = []; foreach($cats as $c) $catMap[$c['slug']] = $c;
  $handled = [];
  foreach($navGroups as $gName => $slugs):
    // filter only existing slugs
    $items = array_filter(array_map(fn($s)=>$catMap[$s]??null, $slugs));
    if(empty($items)) continue;
    // open if current cat inside this group
    $isOpen = in_array($cur, array_column($items,'slug'));
  ?>
    <details class="nav-group" <?= $isOpen?'open':'' ?>>
      <summary><?= e($gName) ?> <span class="nav-count"><?= count($items) ?></span></summary>
      <div class="nav-group-items">
        <?php foreach($items as $c): $handled[]=$c['slug']; ?>
          <a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>" class="<?= $cur===$c['slug']?'active':'' ?>">— <?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </details>
  <?php endforeach;
  // sisa kategori yang belum ke-group (otomatis masuk Lainnya)
  $rest = array_filter($cats, fn($c)=>!in_array($c['slug'],$handled));
  if(!empty($rest)):
  ?>
    <details class="nav-group" <?= in_array($cur,array_column($rest,'slug'))?'open':'' ?>>
      <summary>📦 Lainnya <span class="nav-count"><?= count($rest) ?></span></summary>
      <div class="nav-group-items">
        <?php foreach($rest as $c): ?>
          <a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>" class="<?= $cur===$c['slug']?'active':'' ?>">— <?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </details>
  <?php endif; ?>
  <div class="sep"></div>
  <h4>Bantuan</h4>
  <a href="<?= BASE_URL ?>/search" class="<?= $navUri==="search"?"active":"" ?>">🔍 Pencarian</a>
  <a href="<?= BASE_URL ?>/sitemap" class="<?= $navUri==="sitemap"?"active":"" ?>">🗺️ Sitemap</a>
  <a href="<?= BASE_URL ?>/sitemap.xml">📄 Sitemap XML</a>
  <div class="sep"></div>
  <h4>Informasi</h4>
  <a href="<?= BASE_URL ?>/tentang-kami" class="<?= $navUri==="tentang-kami"?"active":"" ?>">ℹ️ Tentang Kami</a>
  <a href="<?= BASE_URL ?>/hubungi-kami" class="<?= $navUri==="hubungi-kami"?"active":"" ?>">✉️ Hubungi Kami</a>
  <a href="<?= BASE_URL ?>/privacy-policy" class="<?= $navUri==="privacy-policy"?"active":"" ?>">🔒 Kebijakan Privasi</a>
  <a href="<?= BASE_URL ?>/disclaimer" class="<?= in_array($navUri,["disclaimer","disclimer"])?"active":"" ?>">⚠️ Disclaimer</a>
  <div class="sep"></div>
  <div class="nav-foot">Artikel baru setiap hari.<br>Dibuat dengan ❤️ Aizu Dev.</div>
</nav>
<nav class="nav-mobile" aria-label="Mobile nav">
  <a href="<?= BASE_URL ?>/" class="<?= $isHome?'active':'' ?>"><span class="icon">🏠</span><span>Home</span></a>
  <a href="<?= BASE_URL ?>/category/teknologi"><span class="icon">💻</span><span>Tekno</span></a>
  <a href="<?= BASE_URL ?>/search"><span class="icon">🔍</span><span>Cari</span></a>
  <a href="<?= BASE_URL ?>/admin"><span class="icon">⚙️</span><span>Admin</span></a>
  <a href="#" onclick="document.getElementById('navToggle').checked=false; window.scrollTo({top:0,behavior:'smooth'}); return false;"><span class="icon">⬆️</span><span>Atas</span></a>
</nav>
<main class="main"><div class="container">

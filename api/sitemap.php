<?php
require_once __DIR__."/../app/config/config.php";
if (isset($_SERVER["REQUEST_URI"]) && str_contains($_SERVER["REQUEST_URI"], "/api/sitemap.php")) {
    header("Location: ".BASE_URL."/sitemap.xml", true, 301);
    exit;
}
header("Content-Type: application/xml; charset=utf-8");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
$base = rtrim(BASE_URL, "/");
$today = date("Y-m-d");
echo "  <url><loc>$base/</loc><lastmod>$today</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>\n";

// Halaman statis & legalitas
$statics = ["about", "contact", "privacy", "syarat-ketentuan", "disclaimer", "sitemap"];
foreach($statics as $s){
    echo "  <url><loc>$base/$s</loc><lastmod>$today</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>\n";
}

// Hanya tampilkan kategori yang memiliki artikel aktif
foreach(all_categories() as $c){
    $cnt = (int)db()->prepare("SELECT COUNT(*) FROM posts WHERE category_id=? AND status='publish'")->execute([$c['id']]) ? db()->prepare("SELECT COUNT(*) FROM posts WHERE category_id=? AND status='publish'") : null;
    $stmt = db()->prepare("SELECT COUNT(*) FROM posts WHERE category_id=? AND status='publish'");
    $stmt->execute([$c['id']]);
    if ((int)$stmt->fetchColumn() === 0) continue;
    echo "  <url><loc>$base/category/".htmlspecialchars($c["slug"], ENT_QUOTES, 'UTF-8')."</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>\n";
}

// Seluruh artikel berstatus publish
$posts = db()->query("SELECT slug, updated_at FROM posts WHERE status='publish' ORDER BY updated_at DESC")->fetchAll();
foreach($posts as $p){
    $loc = $base."/post/".$p["slug"];
    $date = date("Y-m-d", strtotime($p["updated_at"]));
    echo "  <url><loc>".htmlspecialchars($loc, ENT_QUOTES, 'UTF-8')."</loc><lastmod>$date</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>\n";
}
echo "</urlset>";

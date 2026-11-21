<?php
require_once __DIR__."/../app/config/config.php";
if (isset($_SERVER["REQUEST_URI"]) && str_contains($_SERVER["REQUEST_URI"], "/api/sitemap.php")) {
    header("Location: ".BASE_URL."/sitemap.xml", true, 301);
    exit;
}
header("Content-Type: application/xml; charset=utf-8");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
$base = BASE_URL;
$today = date("Y-m-d");
echo "  <url><loc>$base/</loc><lastmod>$today</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>\n";
// halaman statis
$statics = ["tentang-kami","hubungi-kami","privacy-policy","disclaimer","sitemap"];
foreach($statics as $s){
    echo "  <url><loc>$base/$s</loc><lastmod>$today</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>\n";
}
foreach(all_categories() as $c){
    echo "  <url><loc>$base/category/".e($c["slug"])."</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>\n";
}
$posts = db()->query("SELECT slug, updated_at FROM posts WHERE status=\"publish\" ORDER BY updated_at DESC")->fetchAll();
foreach($posts as $p){
    $loc = $base."/post/".$p["slug"];
    $date = date("Y-m-d", strtotime($p["updated_at"]));
    echo "  <url><loc>".e($loc)."</loc><lastmod>$date</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>\n";
}
echo "</urlset>";

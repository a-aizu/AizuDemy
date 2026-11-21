<?php
require_once __DIR__."/app/config/config.php";
$uri = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH) ?? "";
$basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? "", "/");
$uri = trim($uri, "/");
if ($basePath !== "" && strpos($uri, $basePath) === 0) {
    $uri = trim(substr($uri, strlen($basePath)), "/");
}
if ($uri === "" || $uri === "index.php") $uri = "";

if ($uri === "ads.txt") {
    header("Content-Type: text/plain; charset=utf-8");
    $pub = setting('adsense_client_id', 'ca-pub-1354033982953234');
    $pubClean = str_replace('ca-pub-', 'pub-', $pub);
    echo "google.com, " . $pubClean . ", DIRECT, f08c47fec0942fa0\n";
    exit;
}

// Auth & SSO routes
if ($uri === "api/trigger_publisher.php" || $uri === "api/trigger_publisher") {
    require_once __DIR__ . "/api/trigger_publisher.php"; exit;
}
if ($uri === "sso-callback" || $uri === "admin/sso-callback") {
    $ssoToken = trim($_GET['sso_token'] ?? '');
    if (!$ssoToken) { header("Location: ".BASE_URL."/admin/login?err=token_missing"); exit; }
    $verifyUrl = 'https://aaizu.net/api/?action=verify_sso&token=' . urlencode($ssoToken);
    $json = @file_get_contents($verifyUrl);
    $data = json_decode($json, true);
    if (!$data || ($data['status'] ?? '') !== 'success') {
        header("Location: ".BASE_URL."/admin/login?err=sso_failed"); exit;
    }
    $user = $data['user'] ?? $data;
    $role = strtolower(trim($user['role'] ?? ''));
    if ($role !== 'god') {
        header("Location: ".BASE_URL."/admin/login?err=role_unauthorized"); exit;
    }
    $username = $user['username'] ?? $user['nama'] ?? 'admin';
    $email = $user['email'] ?? '';
    $_SESSION['aizudemy_admin'] = $username;
    $_SESSION['aizudemy_email'] = $email;
    $_SESSION['aizudemy_role'] = $role;
    setting_save('admin_username', $username);
    header("Location: ".BASE_URL."/admin"); exit;
}
if ($uri === "admin/login") {
    $title="Login Admin"; include __DIR__."/views/admin/login.php"; exit;
}
if ($uri === "admin/logout") { session_destroy(); header("Location: ".BASE_URL."/admin/login"); exit; }
if ($uri === "admin" || $uri === "admin/") {
    require_admin();
    $range = (isset($_GET["range"]) && $_GET["range"]==="30") ? 30 : 7;
    $stats = [
        "posts"      => (int)db()->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
        "publish"    => (int)db()->query("SELECT COUNT(*) FROM posts WHERE status='publish'")->fetchColumn(),
        "draft"      => (int)db()->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn(),
        "cats"       => (int)db()->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
        "views"      => (int)db()->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn(),
        "this_month" => (int)db()->query("SELECT COUNT(*) FROM posts WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")->fetchColumn(),
        "no_thumb"   => (int)db()->query("SELECT COUNT(*) FROM posts WHERE thumbnail IS NULL OR thumbnail=''")->fetchColumn(),
        "no_cat"     => (int)db()->query("SELECT COUNT(*) FROM posts WHERE category_id IS NULL")->fetchColumn(),
    ];
    $recent  = db()->query("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC LIMIT 8")->fetchAll();
    $popular = popular_posts(5);
    $catStats = db()->query("SELECT c.id,c.name,c.slug,COUNT(p.id) as cnt FROM categories c LEFT JOIN posts p ON p.category_id=c.id GROUP BY c.id ORDER BY cnt DESC, c.name ASC LIMIT 5")->fetchAll();
    $days = [];
    for($i=$range-1;$i>=0;$i--) $days[date("Y-m-d", strtotime("-".$i." days"))]=0;
    $chartQ = db()->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM posts WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ".(int)$range." DAY) GROUP BY DATE(created_at)")->fetchAll();
    foreach($chartQ as $row) if(isset($days[$row["d"]])) $days[$row["d"]]=(int)$row["c"];
    $chart = $days;
    $chartMax = max(1, max($days));
    $chartTotal = array_sum($days);
    $drafts = db()->query("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='draft' ORDER BY p.updated_at DESC LIMIT 5")->fetchAll();
    $needs  = db()->query("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE (p.thumbnail IS NULL OR p.thumbnail='') OR p.category_id IS NULL ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
    try { $sysMysql = db()->query("SELECT VERSION()")->fetchColumn(); } catch(Exception $e){ $sysMysql="-"; }
    $sysInfo = [
        "php"       => PHP_VERSION,
        "mysql"     => $sysMysql,
        "uploads"   => count(glob(UPLOAD_PATH."*")),
        "last_post" => db()->query("SELECT MAX(updated_at) FROM posts")->fetchColumn(),
    ];
    $pipelineStatus=[];
    $pipelineFile=__DIR__.'/scripts/pipeline_status.json';
    if(is_readable($pipelineFile)) $pipelineStatus=json_decode(file_get_contents($pipelineFile),true) ?: [];
    $title="Dashboard — AizuDemy"; include __DIR__."/views/partials/admin_header.php"; include __DIR__."/views/admin/dashboard.php"; include __DIR__."/views/partials/admin_footer.php"; exit;
}
if ($uri === "admin/ai-settings") {
    require_admin();
    $msg=''; $ok=false;
    $aiConfig=ai_config(false);
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if(!csrf_check($_POST['csrf'] ?? '')){ $msg='Sesi formulir tidak valid. Muat ulang halaman.'; }
        else {
            foreach(ai_roles() as $role){
                $base=trim($_POST[$role.'_base_url'] ?? '');
                $model=trim($_POST[$role.'_model'] ?? '');
                $timeout=max(30,min(1800,(int)($_POST[$role.'_timeout'] ?? 360)));
                if($base!=='' && (!filter_var($base,FILTER_VALIDATE_URL) || !preg_match('#^https?://#i',$base))){ $msg="Base URL {$role} tidak valid."; break; }
                $aiConfig[$role]['base_url']=rtrim($base,'/'); $aiConfig[$role]['model']=$model; $aiConfig[$role]['timeout']=$timeout;
            }
            if($msg===''){
                $testRole=$_POST['test_role'] ?? '';
                if(isset($_POST['save_ai']) || in_array($testRole,ai_roles(),true)){
                    foreach(ai_roles() as $role){
                        setting_save("ai_{$role}_base_url",$aiConfig[$role]['base_url']);
                        setting_save("ai_{$role}_model",$aiConfig[$role]['model']);
                        setting_save("ai_{$role}_timeout",(string)$aiConfig[$role]['timeout']);
                        if(!empty($_POST[$role.'_clear_key'])) setting_save("ai_{$role}_api_key",'');
                        elseif(trim($_POST[$role.'_api_key'] ?? '')!=='') setting_save("ai_{$role}_api_key",ai_encrypt(trim($_POST[$role.'_api_key'])));
                    }
                    $imageMode=($_POST['image_endpoint_mode'] ?? 'images')==='chat'?'chat':'images';
                    setting_save('ai_image_endpoint_mode',$imageMode);
                    $aiConfig=ai_config(false);
                    $ok=true; $msg='Konfigurasi AI berhasil disimpan.';
                }
                if($testRole && in_array($testRole,ai_roles(),true)){
                    try{
                        $live=ai_config(true)[$testRole];
                        if(!$live['base_url'] || !$live['api_key']) throw new RuntimeException('Base URL dan API key wajib diisi.');
                        $ch=curl_init(rtrim($live['base_url'],'/').'/models');
                        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$live['api_key'],'Accept: application/json'],CURLOPT_USERAGENT=>'AizuDemy/1.0']);
                        $body=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
                        if($body===false || $code<200 || $code>=300) throw new RuntimeException($err ?: "Provider merespons HTTP {$code}.");
                        $decoded=json_decode($body,true); $count=is_array($decoded['data'] ?? null)?count($decoded['data']):null;
                        $ok=true; $msg=$aiConfig[$testRole]['label'].' terhubung'.($count!==null?" — {$count} model tersedia.":'.');
                    }catch(Throwable $e){ $ok=false; $msg='Tes gagal: '.$e->getMessage(); }
                }
            }
        }
    }
    $aiConfig=ai_config(false);
    $title='AI Settings — AizuDemy'; include __DIR__.'/views/partials/admin_header.php'; include __DIR__.'/views/admin/ai_settings.php'; include __DIR__.'/views/partials/admin_footer.php'; exit;
}
if ($uri === "admin/categories" || strpos($uri,"admin/categories")===0) {
    require_admin();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        $desc = trim($_POST['description'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        if($action === 'save' && $name !== ''){
            db()->prepare("INSERT INTO categories (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), updated_at=NOW()")->execute([$name, $slug, $desc]);
            header("Location: ".BASE_URL."/admin/categories?success=1"); exit;
        } elseif($action === 'update' && $id > 0 && $name !== ''){
            db()->prepare("UPDATE categories SET name=?, slug=?, description=?, updated_at=NOW() WHERE id=?")->execute([$name, $slug, $desc, $id]);
            header("Location: ".BASE_URL."/admin/categories?success=1"); exit;
        }
    } elseif($action === 'delete'){
        $id = (int)($_GET['id'] ?? 0);
        if($id > 0){
            db()->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
        }
        header("Location: ".BASE_URL."/admin/categories?success=1"); exit;
    }
    $categories = db()->query("SELECT c.*, COUNT(p.id) as post_count FROM categories c LEFT JOIN posts p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
    $title='Kelola Kategori — AizuDemy'; include __DIR__.'/views/partials/admin_header.php'; include __DIR__.'/views/admin/categories.php'; include __DIR__.'/views/partials/admin_footer.php'; exit;
}
if ($uri === "admin/ads-settings" || strpos($uri,"admin/ads-settings")===0) {
    require_admin();
    $msg = '';
    if($_SERVER['REQUEST_METHOD']==='POST'){
        setting_save('adsense_client_id', trim($_POST['adsense_client_id'] ?? ''));
        setting_save('adsense_auto_ads', $_POST['adsense_auto_ads'] ?? '0');
        setting_save('adsense_head_code', trim($_POST['adsense_head_code'] ?? ''));
        setting_save('adsense_post_top', trim($_POST['adsense_post_top'] ?? ''));
        setting_save('adsense_post_bottom', trim($_POST['adsense_post_bottom'] ?? ''));
        setting_save('adsense_sidebar', trim($_POST['adsense_sidebar'] ?? ''));
        $msg = 'Pengaturan Google AdSense berhasil disimpan.';
    }
    $adsenseClientId = setting('adsense_client_id', '');
    $adsenseAutoAds = setting('adsense_auto_ads', '0');
    $adsenseHeadCode = setting('adsense_head_code', '');
    $adsensePostTop = setting('adsense_post_top', '');
    $adsensePostBottom = setting('adsense_post_bottom', '');
    $adsenseSidebar = setting('adsense_sidebar', '');

    $title='Pengaturan AdSense — AizuDemy'; include __DIR__.'/views/partials/admin_header.php'; include __DIR__.'/views/admin/ads_settings.php'; include __DIR__.'/views/partials/admin_footer.php'; exit;
}
if ($uri === "admin/seo-settings" || strpos($uri,"admin/seo-settings")===0) {
    require_admin();
    $msg = '';
    if($_SERVER['REQUEST_METHOD']==='POST'){
        setting_save('seo_site_name', trim($_POST['seo_site_name'] ?? ''));
        setting_save('seo_site_tagline', trim($_POST['seo_site_tagline'] ?? ''));
        setting_save('seo_site_desc', trim($_POST['seo_site_desc'] ?? ''));
        setting_save('seo_site_keywords', trim($_POST['seo_site_keywords'] ?? ''));
        setting_save('seo_site_author', trim($_POST['seo_site_author'] ?? ''));
        setting_save('seo_site_og_image', trim($_POST['seo_site_og_image'] ?? ''));
        setting_save('seo_google_verification', trim($_POST['seo_google_verification'] ?? ''));
        setting_save('seo_bing_verification', trim($_POST['seo_bing_verification'] ?? ''));
        setting_save('seo_ga_id', trim($_POST['seo_ga_id'] ?? ''));
        setting_save('seo_robots_directive', $_POST['seo_robots_directive'] ?? 'index, follow');
        $msg = 'Pengaturan SEO & Meta Tags berhasil disimpan.';
    }
    $seoSiteName = setting('seo_site_name', SITE_NAME);
    $seoSiteTagline = setting('seo_site_tagline', SITE_DESC);
    $seoSiteDesc = setting('seo_site_desc', SITE_DESC);
    $seoSiteKeywords = setting('seo_site_keywords', 'vibe coding, cyber security, tutorial php, belajar hacking, aizudemy');
    $seoSiteAuthor = setting('seo_site_author', 'Aizu Dev');
    $seoSiteOgImage = setting('seo_site_og_image', '');
    $seoGoogleVerification = setting('seo_google_verification', '');
    $seoBingVerification = setting('seo_bing_verification', '');
    $seoGaId = setting('seo_ga_id', '');
    $seoRobotsDirective = setting('seo_robots_directive', 'index, follow');

    $title='Pengaturan SEO — AizuDemy'; include __DIR__.'/views/partials/admin_header.php'; include __DIR__.'/views/admin/seo_settings.php'; include __DIR__.'/views/partials/admin_footer.php'; exit;
}
if ($uri === "admin/account" || strpos($uri,"admin/account")===0) {
    require_admin();
    $accountError = '';
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $username = trim($_POST['username'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');

        if($username === ''){
            $accountError = 'Username tidak boleh kosong.';
        } elseif($newPass !== '' && $newPass !== $confirmPass){
            $accountError = 'Konfirmasi password baru tidak cocok.';
        } else {
            $_SESSION["aizudemy_admin"] = $username;
            setting_save('admin_username', $username);
            if($newPass !== ''){
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                setting_save('admin_password', $hash);
                db()->prepare("UPDATE users SET username=?, password=? WHERE role='admin' OR username='admin'")->execute([$username, $hash]);
            }
            header("Location: ".BASE_URL."/admin/account?success=1"); exit;
        }
    }
    $title='Pengaturan Akun — AizuDemy'; include __DIR__.'/views/partials/admin_header.php'; include __DIR__.'/views/admin/account.php'; include __DIR__.'/views/partials/admin_footer.php'; exit;
}
if (strpos($uri,"admin/posts")===0) {
    require_admin();
    $msg=""; $action=$_GET["action"] ?? "";
    if($_SERVER["REQUEST_METHOD"]==="POST" && ($_POST["form"]??"")==="save"){
        $title_=trim($_POST["title"]); $slug_=trim($_POST["slug"]) ?: slugify($title_); $catId=$_POST["category_id"] ?: null;
        $excerpt=trim($_POST["excerpt"]); $content=trim($_POST["content"]); $status=$_POST["status"] ?? "publish";
        $id = $_POST["id"] ?? null;
        $thumbnail=trim($_POST['thumbnail'] ?? '');
        if(!empty($_FILES['thumbnail_file']['tmp_name'])){
            if(($_FILES['thumbnail_file']['error'] ?? UPLOAD_ERR_OK)!==UPLOAD_ERR_OK || ($_FILES['thumbnail_file']['size'] ?? 0)>5*1024*1024){ $msg='Upload thumbnail gagal atau melebihi 5 MB.'; }
            else {
                $mime=(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['thumbnail_file']['tmp_name']);
                $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime] ?? '';
                if(!$ext){ $msg='Thumbnail harus JPG, PNG, atau WebP.'; }
                else {
                    $name=slugify($title_).'-'.bin2hex(random_bytes(5)).'.'.$ext;
                    if(!move_uploaded_file($_FILES['thumbnail_file']['tmp_name'],UPLOAD_PATH.$name)) $msg='Tidak dapat menyimpan thumbnail.';
                    else {
                        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
                        if ($basePath === '/' || $basePath === '\\') $basePath = '';
                        $thumbnail = rtrim($basePath, '/').'/assets/uploads/'.$name;
                    }
                }
            }
        }
        if($msg!==''){ $action=$id?'edit':'new'; }
        else {
        $baseSlug=$slug_; $i=1; $now = date('Y-m-d H:i:s');
        if($id){
            while(true){ $chk=db()->prepare("SELECT id FROM posts WHERE slug=? AND id!=?"); $chk->execute([$slug_,$id]); if(!$chk->fetch()) break; $slug_=$baseSlug."-".$i; $i++; }
            db()->prepare("UPDATE posts SET category_id=?,title=?,slug=?,excerpt=?,content=?,thumbnail=?,status=?,updated_at=? WHERE id=?")->execute([$catId,$title_,$slug_,$excerpt,$content,$thumbnail?:null,$status,$now,$id]);
            $msg="Artikel diperbarui.";
        } else {
            while(true){ $chk=db()->prepare("SELECT id FROM posts WHERE slug=?"); $chk->execute([$slug_]); if(!$chk->fetch()) break; $slug_=$baseSlug."-".$i; $i++; }
            if(!$excerpt) $excerpt=mb_substr(strip_tags($content),0,160)."…";
            db()->prepare("INSERT INTO posts (category_id,title,slug,excerpt,content,thumbnail,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$catId,$title_,$slug_,$excerpt,$content,$thumbnail?:null,$status,$now,$now]);
            $msg="Artikel ditambahkan.";
        }
          $action="";
        }
    }
    if($action==="delete" && isset($_GET["id"])){
        db()->prepare("DELETE FROM posts WHERE id=?")->execute([(int)$_GET["id"]]);
        $msg="Artikel dihapus."; $action="";
    }
    $edit=null;
    if($action==="edit" && isset($_GET["id"])){
        $s=db()->prepare("SELECT * FROM posts WHERE id=?"); $s->execute([(int)$_GET["id"]]); $edit=$s->fetch();
    }
    $where = []; $params = [];
    if(!empty($_GET['q'])){
        $where[] = "(p.title LIKE ? OR p.slug LIKE ?)";
        $params[] = "%".trim($_GET['q'])."%";
        $params[] = "%".trim($_GET['q'])."%";
    }
    if(!empty($_GET['cat'])){
        $where[] = "p.category_id = ?";
        $params[] = (int)$_GET['cat'];
    }
    if(!empty($_GET['status'])){
        $where[] = "p.status = ?";
        $params[] = trim($_GET['status']);
    }
    $sqlWhere = $where ? "WHERE ".implode(" AND ", $where) : "";
    $stmtAll = db()->prepare("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id {$sqlWhere} ORDER BY p.created_at DESC");
    $stmtAll->execute($params);
    $all = $stmtAll->fetchAll();

    $title="Kelola Artikel — AizuDemy"; include __DIR__."/views/partials/admin_header.php"; include __DIR__."/views/admin/posts.php"; include __DIR__."/views/partials/admin_footer.php"; exit;
}

// Legacy Blogger /p/*.html → redirect 301 ke URL bersih
if (preg_match("#^p/([^/]+)\.html$#", $uri, $m)) {
    $slug = $m[1];
    $legacy = ["tentang-kami"=>"tentang-kami","hubungi-kami"=>"hubungi-kami","privacy-policy"=>"privacy-policy","disclimer"=>"disclaimer","disclaimer"=>"disclaimer","sitemap"=>"sitemap"];
    if (isset($legacy[$slug])) { header("Location: ".BASE_URL."/".$legacy[$slug], true, 301); exit; }
    // fallback: coba tanpa .html
    header("Location: ".BASE_URL."/".$slug, true, 301); exit;
}

// Halaman statis (migrasi dari Blogger)
$staticMap = [
    "tentang-kami"   => ["file"=>"tentang-kami.php",   "title"=>"Tentang Kami — AizuDemy",    "desc"=>"Tentang AizuDemy — platform edukasi & teknologi"],
    "hubungi-kami"   => ["file"=>"hubungi-kami.php",   "title"=>"Hubungi Kami — AizuDemy",    "desc"=>"Hubungi tim AizuDemy"],
    "privacy-policy" => ["file"=>"privacy-policy.php", "title"=>"Privacy Policy — AizuDemy",  "desc"=>"Kebijakan privasi AizuDemy"],
    "disclaimer"     => ["file"=>"disclaimer.php",     "title"=>"Disclaimer — AizuDemy",      "desc"=>"Disclaimer AizuDemy"],
    "disclimer"      => ["file"=>"disclaimer.php",     "title"=>"Disclaimer — AizuDemy",      "desc"=>"Disclaimer AizuDemy"],
    "sitemap"        => ["file"=>"sitemap-html.php",   "title"=>"Sitemap — AizuDemy",         "desc"=>"Daftar isi AizuDemy"],
];
if (isset($staticMap[$uri])) {
    $cfg = $staticMap[$uri];
    $title = $cfg["title"]; $desc = $cfg["desc"];
    include __DIR__."/views/partials/header.php";
    include __DIR__."/views/pages/".$cfg["file"];
    include __DIR__."/views/partials/footer.php";
    exit;
}

// Public routes
if ($uri === "search") {
    $title = "Pencarian — AizuDemy"; $desc="Cari artikel di AizuDemy";
    include __DIR__."/views/partials/header.php"; include __DIR__."/views/pages/search.php"; include __DIR__."/views/partials/footer.php"; exit;
}
if (preg_match("#^category/([^/]+)$#",$uri,$m)) {
    $cat = cat_by_slug($m[1]);
    $title = $cat ? e($cat["name"])." — AizuDemy" : "Kategori — AizuDemy";
    $desc = $cat["description"] ?? SITE_DESC;
    include __DIR__."/views/partials/header.php"; include __DIR__."/views/pages/category.php"; include __DIR__."/views/partials/footer.php"; exit;
}
if (preg_match("#^post/([^/]+)$#",$uri,$m)) {
    $post = post_by_slug($m[1]);
    $title = $post ? $post["title"] : "Artikel — AizuDemy";
    $desc = $post["excerpt"] ?? SITE_DESC;
    include __DIR__."/views/partials/header.php"; include __DIR__."/views/pages/post.php"; include __DIR__."/views/partials/footer.php"; exit;
}
if ($uri === "sitemap.xml") { include __DIR__."/api/sitemap.php"; exit; }
// Home
$title = SITE_NAME." — ".SITE_DESC; $desc=SITE_DESC;
include __DIR__."/views/partials/header.php";
include __DIR__."/views/pages/home.php";
include __DIR__."/views/partials/footer.php";

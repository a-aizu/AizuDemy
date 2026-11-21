<?php
require_once __DIR__."/app/config/config.php";
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$base = rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/");
if ($base !== "" && $base !== "/" && strpos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}
$uri = trim($uri, "/");
if ($uri === "" || $uri === "index.php") $uri = "";

// Auth routes
if ($uri === "sso-callback") {
    $token = trim($_GET["sso_token"] ?? $_GET["token"] ?? "");
    if (!$token) {
        header("Location: ".BASE_URL."/admin/login?err=token_missing");
        exit;
    }

    // Verify token with Aizu Sphere API
    $verifyUrl = "https://aaizu.net/api/?action=verify_sso&token=" . urlencode($token);
    $ch = curl_init($verifyUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'AizuDemy/1.0'
    ]);
    $resp = curl_exec($ch);
    $errCode = curl_errno($ch);
    curl_close($ch);

    if ($errCode !== 0 || !$resp) {
        header("Location: ".BASE_URL."/admin/login?err=sso_failed");
        exit;
    }

    $ssoData = json_decode($resp, true);
    if (!$ssoData || ($ssoData["status"] ?? "") !== "success") {
        header("Location: ".BASE_URL."/admin/login?err=sso_failed");
        exit;
    }

    // Check if role is god (or admin)
    $role = strtolower(trim($ssoData["role"] ?? ""));
    if ($role !== "god" && $role !== "admin") {
        header("Location: ".BASE_URL."/admin/login?err=role_unauthorized");
        exit;
    }

    $userData = [
        "id" => $ssoData["user_id"] ?? 1,
        "username" => $ssoData["username"] ?? "admin",
        "name" => $ssoData["nama"] ?? $ssoData["username"] ?? "Admin",
        "email" => $ssoData["email"] ?? "",
        "role" => $role,
        "avatar" => !empty($ssoData["foto_profil"]) ? "https://aaizu.net/uploads/avatar/" . $ssoData["foto_profil"] : ""
    ];

    // Enforce WhatsApp 2FA (STB Sentinel Security)
    $wa2fa = setting('wa_2fa_enabled', '1');
    if ($wa2fa === '1') {
        $authReq = create_wa_auth_request($userData);
        $_SESSION['pending_2fa_id'] = $authReq['request_id'];
        header("Location: " . BASE_URL . "/admin/wa-2fa-verify?req_id=" . urlencode($authReq['request_id']));
        exit;
    }

    // Direct Login Fallback
    $_SESSION["aizudemy_admin"] = $userData["username"];
    $_SESSION["aizudemy_user"] = $userData;
    header("Location: ".BASE_URL."/admin");
    exit;
}

if ($uri === "admin/wa-2fa-verify") {
    $reqId = $_GET['req_id'] ?? $_SESSION['pending_2fa_id'] ?? '';
    $req = $reqId ? get_wa_auth_request($reqId) : null;
    if (!$req) {
        header("Location: " . BASE_URL . "/admin/login?err=token_missing");
        exit;
    }
    include __DIR__ . "/views/admin/wa_2fa_verify.php";
    exit;
}

if ($uri === "api/wa-auth-status") {
    header('Content-Type: application/json; charset=utf-8');
    $reqId = $_GET['req_id'] ?? $_GET['ticket_id'] ?? $_SESSION['pending_2fa_id'] ?? '';
    $req = $reqId ? get_wa_auth_request($reqId) : null;
    if (!$req) {
        echo json_encode(['status' => 'NOT_FOUND']);
        exit;
    }
    
    // If pending locally, check STB Sentinel Gateway status as fallback
    if ($req['status'] === 'PENDING') {
        try {
            $ch = curl_init("http://127.0.0.1:20129/api/sentinel/auth-status?ticket_id=" . urlencode($reqId) . "&app_id=aizudemy");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 2,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_HTTPHEADER => ['User-Agent: AizuDemy-SentinelClient/1.0']
            ]);
            $raw = curl_exec($ch);
            $remote = json_decode((string)$raw, true);
            curl_close($ch);
            if (!empty($remote['status']) && in_array($remote['status'], ['APPROVED', 'REJECTED'])) {
                db()->prepare("UPDATE wa_auth_requests SET status = ? WHERE request_id = ?")->execute([$remote['status'], $reqId]);
                $req['status'] = $remote['status'];
            }
        } catch (Throwable $e) {}
    }

    if ($req['status'] === 'APPROVED') {
        $u = $req['user_data'];
        $_SESSION["aizudemy_admin"] = $u["username"] ?? "admin";
        $_SESSION["aizudemy_user"] = $u;
        unset($_SESSION['pending_2fa_id']);
        echo json_encode(['status' => 'APPROVED', 'redirect' => BASE_URL . '/admin']);
        exit;
    }
    if ($req['status'] === 'REJECTED') {
        unset($_SESSION['pending_2fa_id']);
        echo json_encode(['status' => 'REJECTED', 'redirect' => BASE_URL . '/admin/login?err=access_denied']);
        exit;
    }
    $timeLeft = max(0, (int)$req['expires_at'] - time());
    echo json_encode(['status' => $req['status'], 'time_left' => $timeLeft]);
    exit;
}

if ($uri === "api/wa-webhook") {
    header('Content-Type: application/json; charset=utf-8');
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true) ?: $_POST;
    
    $ticketId = trim($payload['ticket_id'] ?? $payload['req_id'] ?? '');
    $action = strtoupper(trim($payload['action'] ?? $payload['status'] ?? ''));
    if ($ticketId && in_array($action, ['APPROVED', 'REJECTED'])) {
        db()->prepare("UPDATE wa_auth_requests SET status = ? WHERE request_id = ?")->execute([$action, $ticketId]);
        echo json_encode(['success' => true, 'action' => $action, 'message' => 'Sentinel webhook processed']);
        exit;
    }

    $message = trim($payload['message'] ?? $payload['text'] ?? '');
    $cleanMsg = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $message));
    
    $allowWords = ['y', 'ya', 'yes', 'izinkan', 'ijinkan', 'acc', 'accept', 'ok', 'setuju', 'allow', 'buka', 'masuk'];
    $denyWords = ['t', 'tidak', 'no', 'tolak', 'reject', 'deny', 'batal', 'cancel', 'blokir'];
    
    $stmt = db()->query("SELECT * FROM wa_auth_requests WHERE status = 'PENDING' AND expires_at >= UNIX_TIMESTAMP() ORDER BY id DESC LIMIT 1");
    $pending = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pending) {
        if (in_array($cleanMsg, $allowWords)) {
            db()->prepare("UPDATE wa_auth_requests SET status = 'APPROVED' WHERE id = ?")->execute([$pending['id']]);
            echo json_encode(['success' => true, 'action' => 'APPROVED', 'reply' => "✅ *[AIZU SENTINEL]* Izin Akses Masuk *DISETUJUI* untuk *AizuDemy*."]);
            exit;
        } elseif (in_array($cleanMsg, $denyWords)) {
            db()->prepare("UPDATE wa_auth_requests SET status = 'REJECTED' WHERE id = ?")->execute([$pending['id']]);
            echo json_encode(['success' => true, 'action' => 'REJECTED', 'reply' => "🚫 *[AIZU SENTINEL]* Permintaan Masuk *DITOLAK* untuk *AizuDemy*."]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'No active pending request or command not recognized.']);
    exit;
}

if ($uri === "admin/login") {
    $err = $_GET["err"] ?? "";
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $u = $_POST["username"] ?? "";
        $p = $_POST["password"] ?? "";
        $row = db()->prepare("SELECT * FROM users WHERE username=?"); $row->execute([$u]); $user=$row->fetch();
        $ok = false;
        if ($user) $ok = password_verify($p, $user["password"]);
        else if ($u === "admin" && password_verify($p, "__SET_ADMIN_PASSWORD_HASH__")) $ok = true;
        if ($ok) {
            $userData = [
                "id" => $user["id"] ?? 1,
                "username" => $u,
                "name" => $user["name"] ?? $u,
                "email" => $user["email"] ?? "",
                "role" => "god",
                "avatar" => ""
            ];
            $wa2fa = setting('wa_2fa_enabled', '1');
            if ($wa2fa === '1') {
                $authReq = create_wa_auth_request($userData);
                $_SESSION['pending_2fa_id'] = $authReq['request_id'];
                header("Location: " . BASE_URL . "/admin/wa-2fa-verify?req_id=" . urlencode($authReq['request_id']));
                exit;
            }
            $_SESSION["aizudemy_admin"] = $u;
            $_SESSION["aizudemy_user"] = $userData;
            header("Location: ".BASE_URL."/admin");
            exit;
        }
        else $err = "Username / password salah.";
    }
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
        "today_views"=> (int)(db()->query("SELECT COALESCE(SUM(views),0) FROM post_views_daily WHERE view_date=CURDATE()")->fetchColumn() ?: 0),
        "week_views" => (int)(db()->query("SELECT COALESCE(SUM(views),0) FROM post_views_daily WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn() ?: 0),
        "month_views"=> (int)(db()->query("SELECT COALESCE(SUM(views),0) FROM post_views_daily WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn() ?: 0),
        "this_month" => (int)db()->query("SELECT COUNT(*) FROM posts WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")->fetchColumn(),
        "no_thumb"   => (int)db()->query("SELECT COUNT(*) FROM posts WHERE thumbnail IS NULL OR thumbnail=''")->fetchColumn(),
        "no_cat"     => (int)db()->query("SELECT COUNT(*) FROM posts WHERE category_id IS NULL")->fetchColumn(),
    ];
    $recent           = db()->query("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC LIMIT 8")->fetchAll();
    $popularDay       = get_popular_posts_by_period('day', 8);
    $popularWeek      = get_popular_posts_by_period('week', 8);
    $popularMonth     = get_popular_posts_by_period('month', 8);
    $popularAll       = get_popular_posts_by_period('all', 8);
    $popular          = $popularAll;
    $analyticsSummary = get_views_analytics_summary(30);
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
    $title="Dashboard "; include __DIR__."/views/partials/admin_header.php"; include __DIR__."/views/admin/dashboard.php"; include __DIR__."/views/partials/admin_footer.php"; exit;
}
if ($uri === "admin/ai-fix-post") {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Metode request tidak diizinkan.']);
        exit;
    }
    if (!csrf_check($_POST['csrf'] ?? '')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Sesi CSRF tidak valid. Silakan muat ulang halaman.']);
        exit;
    }
    $postId = (int)($_POST['id'] ?? 0);
    if ($postId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID artikel tidak valid.']);
        exit;
    }
    try {
        $result = ai_auto_repair_post($postId);
        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($uri === "admin/trigger-publisher") {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Metode request tidak diizinkan.']);
        exit;
    }
    if (!csrf_check($_POST['csrf'] ?? '')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Sesi CSRF tidak valid. Silakan muat ulang halaman.']);
        exit;
    }
    try {
        $script = __DIR__ . '/scripts/daily_publisher.py';
        if (!file_exists($script)) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Script publisher tidak ditemukan.']);
            exit;
        }

        $cmd = "nohup /usr/bin/python3 " . escapeshellarg($script) . " > /tmp/admin_trigger.log 2>&1 &";
        exec($cmd);

        echo json_encode([
            'ok' => true,
            'message' => '⚡ AI Auto-Publisher berhasil dipicu! Artikel baru sedang diproses di background.'
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
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
if ($uri === "admin/security-settings") {
    require_admin();
    $title='Keamanan & WhatsApp 2FA — AizuDemy'; include __DIR__.'/views/partials/admin_header.php'; include __DIR__.'/views/admin/security_settings.php'; include __DIR__.'/views/partials/admin_footer.php'; exit;
}
if ($uri === "admin/ads-settings") {
    require_admin();
    $msg = '';
    $ok = false;
    $fields = ['adsense_client_id', 'adsense_auto_ads', 'adsense_post_top', 'adsense_in_article', 'adsense_post_bottom', 'adsense_sidebar', 'adsense_home_feed'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_check($_POST['csrf'] ?? '')) {
            $msg = 'Sesi formulir tidak valid (CSRF). Silakan muat ulang halaman.';
        } else {
            foreach ($fields as $field) {
                $val = trim($_POST[$field] ?? '');
                setting_save($field, $val);
            }
            $ok = true;
            $msg = 'Pengaturan iklan & Google AdSense berhasil disimpan!';
        }
    }

    $adSettings = [];
    foreach ($fields as $field) {
        $adSettings[$field] = setting($field, $field === 'adsense_client_id' ? 'ca-pub-1354033982953234' : ($field === 'adsense_auto_ads' ? '1' : ''));
    }

    $title = 'Pengaturan Iklan — AizuDemy';
    include __DIR__ . '/views/partials/admin_header.php';
    include __DIR__ . '/views/admin/ads_settings.php';
    include __DIR__ . '/views/partials/admin_footer.php';
    exit;
}
if (strpos($uri,"admin/posts")===0) {
    require_admin();
    $msg=""; $action=$_GET["action"] ?? "";
    if($_SERVER["REQUEST_METHOD"]==="POST" && ($_POST["form"]??"")==="save"){
        if(!csrf_check($_POST['csrf'] ?? '')){
            $msg = 'Sesi formulir tidak valid (CSRF). Silakan muat ulang halaman.';
        } else {
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
                    else $thumbnail=rtrim(parse_url(BASE_URL,PHP_URL_PATH) ?: '/aizudemy','/').'/public/uploads/'.$name;
                }
            }
        }
        if($msg!==''){ $action=$id?'edit':'new'; }
        else {
        $baseSlug=$slug_; $i=1;
        if($id){
            while(true){ $chk=db()->prepare("SELECT id FROM posts WHERE slug=? AND id!=?"); $chk->execute([$slug_,$id]); if(!$chk->fetch()) break; $slug_=$baseSlug."-".$i; $i++; }
            db()->prepare("UPDATE posts SET category_id=?,title=?,slug=?,excerpt=?,content=?,thumbnail=?,status=? WHERE id=?")->execute([$catId,$title_,$slug_,$excerpt,$content,$thumbnail?:null,$status,$id]);
            $msg="Artikel diperbarui.";
        } else {
            while(true){ $chk=db()->prepare("SELECT id FROM posts WHERE slug=?"); $chk->execute([$slug_]); if(!$chk->fetch()) break; $slug_=$baseSlug."-".$i; $i++; }
            if(!$excerpt) $excerpt=mb_substr(strip_tags($content),0,160)."…";
            db()->prepare("INSERT INTO posts (category_id,title,slug,excerpt,content,thumbnail,status) VALUES (?,?,?,?,?,?,?)")->execute([$catId,$title_,$slug_,$excerpt,$content,$thumbnail?:null,$status]);
            $msg="Artikel ditambahkan.";
        }
          $action="";
        }
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
    
    // Filters: q (search), cat (category), status (publish/draft)
    $qFilter = trim($_GET["q"] ?? "");
    $catFilter = trim($_GET["cat"] ?? "");
    $statusFilter = trim($_GET["status"] ?? "");

    $where = [];
    $params = [];

    if ($qFilter !== "") {
        $where[] = "(p.title LIKE ? OR p.slug LIKE ? OR p.content LIKE ?)";
        $params[] = "%{$qFilter}%";
        $params[] = "%{$qFilter}%";
        $params[] = "%{$qFilter}%";
    }

    if ($catFilter !== "") {
        if ($catFilter === "0" || $catFilter === "none") {
            $where[] = "p.category_id IS NULL";
        } else {
            $where[] = "p.category_id = ?";
            $params[] = (int)$catFilter;
        }
    }

    if ($statusFilter !== "") {
        $where[] = "p.status = ?";
        $params[] = $statusFilter;
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    $stmt = db()->prepare("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id {$whereSql} ORDER BY p.created_at DESC");
    $stmt->execute($params);
    $all = $stmt->fetchAll();

    $title="Kelola Artikel "; include __DIR__."/views/partials/admin_header.php"; include __DIR__."/views/admin/posts.php"; include __DIR__."/views/partials/admin_footer.php"; exit;
}

// Legacy Blogger /p/*.html → redirect 301 ke URL bersih
if (preg_match("#^p/([^/]+)\.html$#", $uri, $m)) {
    $slug = $m[1];
    $legacy = [
        "tentang-kami"     => "tentang-kami",
        "hubungi-kami"     => "hubungi-kami",
        "privacy-policy"   => "privacy-policy",
        "syarat-ketentuan" => "syarat-ketentuan",
        "terms"            => "syarat-ketentuan",
        "disclimer"        => "disclaimer",
        "disclaimer"       => "disclaimer",
        "sitemap"          => "sitemap"
    ];
    if (isset($legacy[$slug])) { header("Location: ".BASE_URL."/".$legacy[$slug], true, 301); exit; }
    // fallback: coba tanpa .html
    header("Location: ".BASE_URL."/".$slug, true, 301); exit;
}

// Halaman statis & legalitas
$staticMap = [
    "tentang-kami"     => ["file"=>"tentang-kami.php",     "title"=>"Tentang Kami ",                "desc"=>"Tentang AizuDemy — platform edukasi & teknologi"],
    "hubungi-kami"     => ["file"=>"hubungi-kami.php",     "title"=>"Hubungi Kami ",                "desc"=>"Hubungi tim redaksi & pengelola AizuDemy"],
    "privacy-policy"   => ["file"=>"privacy-policy.php",   "title"=>"Kebijakan Privasi ",          "desc"=>"Kebijakan privasi & perlindungan data AizuDemy"],
    "syarat-ketentuan" => ["file"=>"syarat-ketentuan.php", "title"=>"Syarat & Ketentuan Layanan ", "desc"=>"Ketentuan dan syarat penggunaan platform AizuDemy"],
    "about"            => ["file"=>"tentang-kami.php",   "title"=>"Tentang Kami ",           "desc"=>"Tentang platform belajar digital AizuDemy"],
    "tentang"          => ["file"=>"tentang-kami.php",   "title"=>"Tentang Kami ",           "desc"=>"Tentang platform belajar digital AizuDemy"],
    "contact"          => ["file"=>"hubungi-kami.php",  "title"=>"Hubungi Kami ",          "desc"=>"Hubungi tim AizuDemy untuk pertanyaan dan kerja sama"],
    "hubungi"          => ["file"=>"hubungi-kami.php",  "title"=>"Hubungi Kami ",          "desc"=>"Hubungi tim AizuDemy untuk pertanyaan dan kerja sama"],
    "privacy"          => ["file"=>"privacy-policy.php", "title"=>"Kebijakan Privasi ",     "desc"=>"Kebijakan privasi dan penggunaan data AizuDemy"],
    "privacy-policy"   => ["file"=>"privacy-policy.php", "title"=>"Kebijakan Privasi ",     "desc"=>"Kebijakan privasi dan penggunaan data AizuDemy"],
    "terms"            => ["file"=>"syarat-ketentuan.php", "title"=>"Syarat & Ketentuan Layanan ", "desc"=>"Ketentuan dan syarat penggunaan platform AizuDemy"],
    "disclaimer"       => ["file"=>"disclaimer.php",       "title"=>"Disclaimer ",                  "desc"=>"Penafian & batasan tanggung jawab materi AizuDemy"],
    "disclimer"        => ["file"=>"disclaimer.php",       "title"=>"Disclaimer ",                  "desc"=>"Penafian & batasan tanggung jawab materi AizuDemy"],
    "sitemap"          => ["file"=>"sitemap-html.php",     "title"=>"Sitemap ",                     "desc"=>"Daftar isi dan indeks artikel AizuDemy"],
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
    $title = "Pencarian "; $desc="Cari artikel di AizuDemy";
    include __DIR__."/views/partials/header.php"; include __DIR__."/views/pages/search.php"; include __DIR__."/views/partials/footer.php"; exit;
}
if (preg_match("#^category/([^/]+)$#",$uri,$m)) {
    $cat = cat_by_slug($m[1]);
    if (!$cat) {
        http_response_code(404);
        $title = "404 Kategori Tidak Ditemukan ";
        $desc = "Kategori yang Anda cari tidak ditemukan.";
        include __DIR__."/views/partials/header.php";
        echo "<div class='widget' style='text-align:center;padding:50px 20px;'><h2>404 — Kategori Tidak Ditemukan</h2><p style='color:var(--muted);margin:12px 0 24px;'>Kategori yang Anda tuju tidak tersedia atau telah digabungkan.</p><a href='".BASE_URL."/' class='btn btn-primary'>Kembali ke Beranda</a></div>";
        include __DIR__."/views/partials/footer.php";
        exit;
    }
    $title = e($cat["name"])." ";
    $desc = $cat["description"] ?? SITE_DESC;
    include __DIR__."/views/partials/header.php"; include __DIR__."/views/pages/category.php"; include __DIR__."/views/partials/footer.php"; exit;
}
if (preg_match("#^post/([^/]+)$#",$uri,$m)) {
    $post = post_by_slug($m[1]);
    if (!$post) {
        http_response_code(404);
        $title = "404 Artikel Tidak Ditemukan ";
        $desc = "Artikel yang Anda cari tidak ditemukan atau telah diperbarui.";
        include __DIR__."/views/partials/header.php";
        echo "<div class='widget' style='text-align:center;padding:50px 20px;'><h2>404 — Artikel Tidak Ditemukan</h2><p style='color:var(--muted);margin:12px 0 24px;'>Artikel yang Anda cari tidak ditemukan atau telah dipindahkan.</p><a href='".BASE_URL."/' class='btn btn-primary'>Kembali ke Beranda</a></div>";
        include __DIR__."/views/partials/footer.php";
        exit;
    }
    $title = $post["title"];
    $desc = $post["excerpt"] ?? SITE_DESC;
    include __DIR__."/views/partials/header.php"; include __DIR__."/views/pages/post.php"; include __DIR__."/views/partials/footer.php"; exit;
}
if ($uri === "sitemap.xml") { include __DIR__."/api/sitemap.php"; exit; }
if ($uri === "feed" || $uri === "feed.xml" || $uri === "rss") { include __DIR__."/feed.php"; exit; }

// Home vs 404 untuk rute tak dikenal
if ($uri === "") {
    $title = SITE_NAME." — ".SITE_DESC; $desc=SITE_DESC;
    include __DIR__."/views/partials/header.php";
    include __DIR__."/views/pages/home.php";
    include __DIR__."/views/partials/footer.php";
    exit;
}

// 404 Fallback untuk rute acak yang tidak terdaftar
http_response_code(404);
$title = "404 Halaman Tidak Ditemukan ";
$desc = "Halaman yang Anda cari tidak ditemukan.";
include __DIR__."/views/partials/header.php";
echo "<div class='widget' style='text-align:center;padding:50px 20px;'><h2>404 — Halaman Tidak Ditemukan</h2><p style='color:var(--muted);margin:12px 0 24px;'>Maaf, alamat URL yang Anda tuju tidak tersedia di AizuDemy.</p><a href='".BASE_URL."/' class='btn btn-primary'>Kembali ke Beranda</a></div>";
include __DIR__."/views/partials/footer.php";
exit;

<?php
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function slugify($t){
    $t = strtolower(trim($t));
    $t = preg_replace('/[^a-z0-9]+/','-',$t);
    return trim($t,'-');
}
function setting($k, $def=''){
    try{
        $r = db()->prepare("SELECT svalue FROM settings WHERE skey=?");
        $r->execute([$k]);
        $v = $r->fetchColumn();
        return $v !== false ? $v : $def;
    }catch(Exception $e){ return $def; }
}
function setting_save($k, $v){
    $s=db()->prepare("INSERT INTO settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)");
    return $s->execute([$k,$v]);
}
function csrf_token(){
    if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_check($token){ return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token); }
function ai_secret_key(){
    $path='/opt/lampp/etc/aizudemy-ai.key';
    if(!is_readable($path)) throw new RuntimeException('Kunci enkripsi AI belum tersedia.');
    $raw=trim(file_get_contents($path));
    if(strlen($raw)<32) throw new RuntimeException('Kunci enkripsi AI tidak valid.');
    return hash('sha256',$raw,true);
}
function ai_encrypt($plain){
    if($plain==='') return '';
    $iv=random_bytes(12); $tag='';
    $cipher=openssl_encrypt($plain,'aes-256-gcm',ai_secret_key(),OPENSSL_RAW_DATA,$iv,$tag);
    if($cipher===false) throw new RuntimeException('Gagal mengenkripsi API key.');
    return 'enc:v1:'.base64_encode($iv.$tag.$cipher);
}
function ai_decrypt($stored){
    if(!$stored) return '';
    if(!str_starts_with($stored,'enc:v1:')) return $stored; // migrasi nilai lama
    $raw=base64_decode(substr($stored,7),true);
    if($raw===false || strlen($raw)<29) return '';
    $plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',ai_secret_key(),OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16));
    return $plain===false ? '' : $plain;
}
function ai_roles(){ return ['writer','editor','visual','image']; }
function ai_config($withSecrets=false){
    $defaults=[
      'writer'=>['label'=>'Writer','type'=>'chat'], 'editor'=>['label'=>'Editor','type'=>'chat'],
      'visual'=>['label'=>'Visual Director','type'=>'chat'], 'image'=>['label'=>'Image Generator','type'=>'image']
    ];
    foreach($defaults as $role=>&$cfg){
        $cfg['base_url']=setting("ai_{$role}_base_url",'');
        $cfg['model']=setting("ai_{$role}_model",'');
        $timeoutDefaults=['writer'=>900,'editor'=>900,'visual'=>240,'image'=>480];
        $cfg['timeout']=(int)setting("ai_{$role}_timeout",(string)$timeoutDefaults[$role]);
        if($role==='image') $cfg['endpoint_mode']=setting('ai_image_endpoint_mode','images');
        $secret=setting("ai_{$role}_api_key",'');
        $cfg['has_key']=$secret!=='';
        if($withSecrets) $cfg['api_key']=ai_decrypt($secret);
    }
    return $defaults;
}
function all_categories(){
    return db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
}
function cat_by_slug($slug){
    $s=db()->prepare("SELECT * FROM categories WHERE slug=?");
    $s->execute([$slug]); return $s->fetch();
}
function post_by_slug($slug){
    $s=db()->prepare("SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.slug=? AND p.status='publish' LIMIT 1");
    $s->execute([$slug]); return $s->fetch();
}
function latest_posts($limit=9, $offset=0, $cat_id=null, $q=null){
    $sql="SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='publish'";
    $params=[];
    if($cat_id){ $sql.=" AND p.category_id=?"; $params[]=$cat_id; }
    if($q){ $sql.=" AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)"; $like="%$q%"; array_push($params,$like,$like,$like); }
    $sql.=" ORDER BY p.created_at DESC LIMIT ".(int)$limit." OFFSET ".(int)$offset;
    $s=db()->prepare($sql); $s->execute($params); return $s->fetchAll();
}
function count_posts($cat_id=null,$q=null){
    $sql="SELECT COUNT(*) FROM posts p WHERE p.status='publish'";
    $params=[];
    if($cat_id){ $sql.=" AND p.category_id=?"; $params[]=$cat_id; }
    if($q){ $sql.=" AND (p.title LIKE ? OR p.content LIKE ?)" ; $like="%$q%"; array_push($params,$like,$like); }
    $s=db()->prepare($sql); $s->execute($params); return (int)$s->fetchColumn();
}
function popular_posts($limit=5){
    return db()->query("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='publish' ORDER BY p.views DESC, p.created_at DESC LIMIT ".(int)$limit)->fetchAll();
}
function get_popular_posts_by_period($period = 'all', $limit = 8){
    $period = strtolower((string)$period);
    if ($period === 'day' || $period === 'daily' || $period === 'today') {
        $sql = "SELECT p.*, c.name as cat_name, c.slug as cat_slug, 
                       COALESCE(v.views, 0) as period_views, 
                       p.views as total_views 
                FROM posts p 
                LEFT JOIN categories c ON c.id = p.category_id 
                INNER JOIN post_views_daily v ON v.post_id = p.id AND v.view_date = CURDATE() 
                WHERE p.status = 'publish' 
                ORDER BY period_views DESC, p.views DESC, p.created_at DESC 
                LIMIT " . (int)$limit;
    } elseif ($period === 'week' || $period === 'weekly' || $period === '7days') {
        $sql = "SELECT p.*, c.name as cat_name, c.slug as cat_slug, 
                       COALESCE(SUM(v.views), 0) as period_views, 
                       p.views as total_views 
                FROM posts p 
                LEFT JOIN categories c ON c.id = p.category_id 
                INNER JOIN post_views_daily v ON v.post_id = p.id AND v.view_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                WHERE p.status = 'publish' 
                GROUP BY p.id 
                ORDER BY period_views DESC, p.views DESC, p.created_at DESC 
                LIMIT " . (int)$limit;
    } elseif ($period === 'month' || $period === 'monthly' || $period === '30days') {
        $sql = "SELECT p.*, c.name as cat_name, c.slug as cat_slug, 
                       COALESCE(SUM(v.views), 0) as period_views, 
                       p.views as total_views 
                FROM posts p 
                LEFT JOIN categories c ON c.id = p.category_id 
                INNER JOIN post_views_daily v ON v.post_id = p.id AND v.view_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                WHERE p.status = 'publish' 
                GROUP BY p.id 
                ORDER BY period_views DESC, p.views DESC, p.created_at DESC 
                LIMIT " . (int)$limit;
    } else {
        // Total Keseluruhan (All-Time)
        $sql = "SELECT p.*, c.name as cat_name, c.slug as cat_slug, 
                       p.views as period_views, 
                       p.views as total_views 
                FROM posts p 
                LEFT JOIN categories c ON c.id = p.category_id 
                WHERE p.status = 'publish' 
                ORDER BY p.views DESC, p.created_at DESC 
                LIMIT " . (int)$limit;
    }

    try {
        $res = db()->query($sql)->fetchAll();
        if (empty($res) && in_array($period, ['day', 'daily', 'today', 'week', 'weekly', 'month', 'monthly'])) {
            $res = db()->query("SELECT p.*, c.name as cat_name, c.slug as cat_slug, 0 as period_views, p.views as total_views FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.status = 'publish' ORDER BY p.views DESC, p.created_at DESC LIMIT " . (int)$limit)->fetchAll();
        }
        return $res;
    } catch (Throwable $e) {
        return popular_posts($limit);
    }
}

/**
 * Parsing User-Agent untuk mengekstrak Tipe Perangkat, OS, dan Browser
 */
function parse_user_agent(?string $ua = null): array {
    $ua = $ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
    $deviceType = 'desktop';
    $os = 'Unknown OS';
    $browser = 'Unknown Browser';

    // 1. Deteksi Bot / Crawler
    if (preg_match('/(bot|crawl|spider|slurp|facebookexternalhit|whatsapp|googlebot|bingbot|yahoo|duckduckbot|yandexbot|curl|wget)/i', $ua)) {
        return ['device_type' => 'bot', 'os' => 'Bot/Crawler', 'browser' => 'Bot Crawler'];
    }

    // 2. Deteksi Device Type & OS
    if (preg_match('/ipad/i', $ua)) {
        $deviceType = 'tablet';
        $os = 'iPadOS';
    } elseif (preg_match('/(tablet|playbook|silk)|(android(?!.*mobile))/i', $ua)) {
        $deviceType = 'tablet';
        $os = 'Android Tablet';
    } elseif (preg_match('/(iphone|ipod)/i', $ua)) {
        $deviceType = 'mobile';
        $os = 'iOS';
    } elseif (preg_match('/android/i', $ua)) {
        $deviceType = 'mobile';
        $os = 'Android';
    } elseif (preg_match('/windows phone/i', $ua)) {
        $deviceType = 'mobile';
        $os = 'Windows Phone';
    } elseif (preg_match('/windows nt 10/i', $ua) || preg_match('/windows nt 11/i', $ua)) {
        $os = 'Windows 10/11';
    } elseif (preg_match('/windows/i', $ua)) {
        $os = 'Windows';
    } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
        $os = 'macOS';
    } elseif (preg_match('/linux/i', $ua)) {
        $os = 'Linux';
    }

    // 3. Deteksi Browser
    if (preg_match('/edg/i', $ua)) {
        $browser = 'Edge';
    } elseif (preg_match('/samsungbrowser/i', $ua)) {
        $browser = 'Samsung Browser';
    } elseif (preg_match('/opr|opera/i', $ua)) {
        $browser = 'Opera';
    } elseif (preg_match('/chrome|crios/i', $ua)) {
        $browser = 'Chrome';
    } elseif (preg_match('/firefox|fxios/i', $ua)) {
        $browser = 'Firefox';
    } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) {
        $browser = 'Safari';
    } elseif (preg_match('/msie|trident/i', $ua)) {
        $browser = 'Internet Explorer';
    }

    return [
        'device_type' => $deviceType,
        'os' => $os,
        'browser' => $browser
    ];
}

/**
 * Parsing HTTP Referrer untuk mengidentifikasi Kategori Sumber Trafik
 */
function parse_traffic_source(?string $ref = null, ?string $host = null): array {
    $ref = trim($ref ?? ($_SERVER['HTTP_REFERER'] ?? ''));
    if (empty($ref)) {
        return ['source' => 'direct', 'clean_ref' => 'Direct / Bookmark'];
    }

    $refHost = parse_url($ref, PHP_URL_HOST) ?: '';
    $currentHost = $host ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');

    // Jika dari website sendiri -> Direct / Internal
    if ($refHost === $currentHost || str_ends_with($refHost, 'aizudemy.net') || str_ends_with($refHost, 'localhost')) {
        return ['source' => 'direct', 'clean_ref' => 'Internal Navigation'];
    }

    // Mesin Pencari
    if (preg_match('/(google|bing|yahoo|duckduckgo|yandex|baidu|ecosia|ask\.com)/i', $refHost)) {
        return ['source' => 'search', 'clean_ref' => $refHost];
    }

    // Media Sosial / Chat
    if (preg_match('/(whatsapp|telegram|t\.co|twitter|x\.com|facebook|instagram|tiktok|linkedin|reddit|youtube|pinterest|threads)/i', $refHost)) {
        return ['source' => 'social', 'clean_ref' => $refHost];
    }

    return ['source' => 'referral', 'clean_ref' => $refHost];
}

/**
 * Resolusi Geografis IP (Negara, Kode ISO, Provinsi, Kota) dengan Cache Database
 */
function resolve_ip_geo(string $ip): array {
    $default = [
        'country' => 'Indonesia',
        'country_code' => 'ID',
        'region' => 'Sumatera Utara',
        'city' => 'Medan',
        'isp' => 'Local Network'
    ];

    if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.16.')) {
        return $default;
    }

    // 1. Cek Header Cloudflare
    $cfCountry = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    $cfCity    = $_SERVER['HTTP_CF_IPCITY'] ?? '';
    if (!empty($cfCountry)) {
        return [
            'country' => ($cfCountry === 'ID' ? 'Indonesia' : $cfCountry),
            'country_code' => strtoupper($cfCountry),
            'region' => $_SERVER['HTTP_CF_REGION'] ?? 'Indonesia Region',
            'city' => !empty($cfCity) ? $cfCity : ($cfCountry === 'ID' ? 'Jakarta' : 'City'),
            'isp' => 'Cloudflare Network'
        ];
    }

    // 2. Cek Cache Database lokal aizu_ip_geo
    try {
        $stmt = db()->prepare("SELECT country, country_code, region, city, isp FROM aizu_ip_geo WHERE ip = ? LIMIT 1");
        $stmt->execute([$ip]);
        $cached = $stmt->fetch();
        if ($cached) {
            return $cached;
        }
    } catch (Throwable $e) {}

    // 3. Query IP Geolocation API eksternal (timeout singkat 1.5 detik)
    try {
        $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
        $apiUrl = "http://ip-api.com/json/" . urlencode($ip) . "?fields=status,country,countryCode,regionName,city,isp";
        $raw = @file_get_contents($apiUrl, false, $ctx);
        if ($raw) {
            $data = json_decode($raw, true);
            if (!empty($data) && ($data['status'] ?? '') === 'success') {
                $geo = [
                    'country'      => $data['country'] ?? 'Indonesia',
                    'country_code' => $data['countryCode'] ?? 'ID',
                    'region'       => $data['regionName'] ?? 'Sumatera Utara',
                    'city'         => $data['city'] ?? 'Medan',
                    'isp'          => $data['isp'] ?? 'Unknown ISP'
                ];
                // Simpan ke cache aizu_ip_geo
                try {
                    db()->prepare("INSERT INTO aizu_ip_geo (ip, country, country_code, region, city, isp) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE country=VALUES(country), city=VALUES(city)")
                        ->execute([$ip, $geo['country'], $geo['country_code'], $geo['region'], $geo['city'], $geo['isp']]);
                } catch (Throwable $e) {}
                return $geo;
            }
        }
    } catch (Throwable $e) {}

    return $default;
}

/**
 * Pencatat View & Analitik Pengunjung Lengkap dengan 4-Lapis Proteksi Anti-Spam & Anti-Refresh
 */
function track_post_view(int $postId): void {
    if ($postId <= 0) return;

    // Lapis 1: Abaikan jika diakses oleh Admin yang sedang login (agar admin cek/edit artikel tidak menambah view)
    if (!empty($_SESSION['aizudemy_admin'])) {
        return;
    }

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $uaData = parse_user_agent($ua);

    // Lapis 2: Abaikan Bot / Crawler Mesin Pencari & Scraper (Googlebot, Bingbot, curl, spider)
    if ($uaData['device_type'] === 'bot') {
        return;
    }

    // Lapis 3: Fast Session Cooldown (Anti-Refresh Instan di level browser)
    if (session_status() === PHP_SESSION_ACTIVE || !headers_sent()) {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        $sessionKey = 'viewed_post_' . $postId;
        if (!empty($_SESSION[$sessionKey]) && (time() - (int)$_SESSION[$sessionKey]) < 900) { // Jeda 15 Menit
            return;
        }
    }

    $ip = get_client_ip();
    $today = date('Y-m-d');
    
    // Visitor Hash unik (Anonim & Aman Privasi)
    $visitorHash = hash('sha256', $ip . '|' . $ua . '|' . $today);

    // Lapis 4: Database Cooldown 15 Menit (Cegah spam view dari IP/perangkat yang sama meski ganti tab/incognito)
    try {
        $check = db()->prepare("SELECT id FROM post_views_analytics WHERE post_id = ? AND visitor_hash = ? AND created_at >= NOW() - INTERVAL 15 MINUTE LIMIT 1");
        $check->execute([$postId, $visitorHash]);
        if ($check->fetchColumn()) {
            return;
        }
    } catch (Throwable $e) {}

    // Simpan timestamp di session
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['viewed_post_' . $postId] = time();
    }

    // Resolusi Geografis IP & Saluran Trafik
    $geo = resolve_ip_geo($ip);
    $refData = parse_traffic_source();

    // Eksekusi Pencatatan Database (Atomic & Safe)
    try {
        // Update Total Views di tabel posts
        db()->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$postId]);

        // Update Agregasi Harian di tabel post_views_daily
        db()->prepare("INSERT INTO post_views_daily (post_id, view_date, views) VALUES (?, CURDATE(), 1) ON DUPLICATE KEY UPDATE views = views + 1")->execute([$postId]);

        // Simpan Log Analitik Lengkap di tabel post_views_analytics
        db()->prepare("INSERT INTO post_views_analytics 
            (post_id, visitor_hash, ip_address, country, country_code, region, city, device_type, os, browser, referrer, traffic_source, view_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())")
            ->execute([
                $postId,
                $visitorHash,
                $ip,
                $geo['country'],
                $geo['country_code'],
                $geo['region'],
                $geo['city'],
                $uaData['device_type'],
                $uaData['os'],
                $uaData['browser'],
                $refData['clean_ref'],
                $refData['source']
            ]);
    } catch (Throwable $e) {}
}

/**
 * Ringkasan Agregat Analitik Demografi untuk Dashboard Admin
 */
function get_views_analytics_summary(int $days = 30): array {
    try {
        $intervalSql = "view_date >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)";

        // 1. Top Kota
        $cities = db()->query("SELECT city, country, COUNT(*) as views FROM post_views_analytics WHERE $intervalSql GROUP BY city, country ORDER BY views DESC LIMIT 6")->fetchAll();

        // 2. Top Negara
        $countries = db()->query("SELECT country, country_code, COUNT(*) as views FROM post_views_analytics WHERE $intervalSql GROUP BY country, country_code ORDER BY views DESC LIMIT 6")->fetchAll();

        // 3. Distribusi Perangkat (Mobile vs Desktop vs Tablet)
        $devices = db()->query("SELECT device_type, COUNT(*) as count FROM post_views_analytics WHERE $intervalSql GROUP BY device_type ORDER BY count DESC")->fetchAll();

        // 4. Distribusi Sumber Trafik
        $sources = db()->query("SELECT traffic_source, COUNT(*) as count FROM post_views_analytics WHERE $intervalSql GROUP BY traffic_source ORDER BY count DESC")->fetchAll();

        // 5. Top Browser & OS
        $browsers = db()->query("SELECT browser, COUNT(*) as count FROM post_views_analytics WHERE $intervalSql GROUP BY browser ORDER BY count DESC LIMIT 5")->fetchAll();
        $osList   = db()->query("SELECT os, COUNT(*) as count FROM post_views_analytics WHERE $intervalSql GROUP BY os ORDER BY count DESC LIMIT 5")->fetchAll();

        // 6. Total Unique Visitors (UV)
        $uv = (int)db()->query("SELECT COUNT(DISTINCT visitor_hash) FROM post_views_analytics WHERE $intervalSql")->fetchColumn();

        return [
            'cities'    => $cities,
            'countries' => $countries,
            'devices'   => $devices,
            'sources'   => $sources,
            'browsers'  => $browsers,
            'os'        => $osList,
            'uv'        => $uv,
        ];
    } catch (Throwable $e) {
        return [
            'cities' => [], 'countries' => [], 'devices' => [], 'sources' => [], 'browsers' => [], 'os' => [], 'uv' => 0
        ];
    }
}
function related_posts($cat_id,$exclude_id,$limit=3){
    $s=db()->prepare("SELECT * FROM posts WHERE category_id=? AND id!=? AND status='publish' ORDER BY created_at DESC LIMIT ".(int)$limit);
    $s->execute([$cat_id,$exclude_id]);
    $r=$s->fetchAll();
    if(count($r)<$limit){
        $s2=db()->prepare("SELECT * FROM posts WHERE id!=? AND status='publish' ORDER BY created_at DESC LIMIT ".(int)$limit);
        $s2->execute([$exclude_id]); $r=$s2->fetchAll();
    }
    return array_slice($r,0,$limit);
}
function is_admin(){ return !empty($_SESSION['aizudemy_admin']); }
function require_admin(){ if(!is_admin()){ header("Location: ".BASE_URL."/admin/login"); exit; } }
function time_ago($dt){
    $t=strtotime($dt); $diff=time()-$t;
    if($diff<60) return 'Baru saja';
    if($diff<3600) return floor($diff/60).' menit lalu';
    if($diff<86400) return floor($diff/3600).' jam lalu';
    if($diff<2592000) return floor($diff/86400).' hari lalu';
    return date('d M Y', $t);
}

function ai_parse_chat_response(string $raw): string {
    $raw = trim($raw);
    if (str_starts_with($raw, 'data:')) {
        $content = '';
        $lines = explode("\n", $raw);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'data:')) {
                $jsonStr = trim(substr($line, 5));
                if ($jsonStr === '[DONE]') continue;
                $decoded = json_decode($jsonStr, true);
                if (is_array($decoded)) {
                    $delta = $decoded['choices'][0]['delta']['content'] ?? '';
                    $content .= $delta;
                }
            }
        }
        if (!empty($content)) return $content;
    }
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $decoded['choices'][0]['message']['content'] ?? '';
    }
    return $raw;
}

function ai_chat_call(array $cfg, array $messages, int $timeout = 180): string {
    if (empty($cfg['base_url']) || empty($cfg['api_key']) || empty($cfg['model'])) {
        throw new RuntimeException("Konfigurasi role AI belum lengkap.");
    }
    $payload = [
        'model' => $cfg['model'],
        'messages' => $messages
    ];
    $ch = curl_init(rtrim($cfg['base_url'], '/') . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $cfg['api_key'],
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE)
    ]);
    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($res === false || $code < 200 || $code >= 300) {
        throw new RuntimeException($err ?: "AI API merespons HTTP {$code}: " . substr((string)$res, 0, 300));
    }
    return ai_parse_chat_response((string)$res);
}

function ai_parse_json(string $text): array {
    $text = trim($text);
    if (empty($text)) return [];

    // 1. Clean markdown code fences (```json ... ``` or ``` ... ```)
    if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/', $text, $m)) {
        $text = trim($m[1]);
    } else {
        $lines = explode("\n", $text);
        $cleaned = [];
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '```')) continue;
            $cleaned[] = $line;
        }
        $text = trim(implode("\n", $cleaned));
    }

    // 2. Direct JSON decode
    $decoded = json_decode($text, true);
    if (is_array($decoded) && (!empty($decoded['content']) || !empty($decoded['title']))) {
        return $decoded;
    }

    // 3. Locate outer JSON bounds
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $blob = substr($text, $start, $end - $start + 1);
        $decoded = json_decode($blob, true);
        if (is_array($decoded) && (!empty($decoded['content']) || !empty($decoded['title']))) {
            return $decoded;
        }

        // 4. Repair unescaped newlines, tabs, and control characters inside JSON strings
        $len = strlen($blob);
        $repaired = '';
        $in_string = false;
        $escaped = false;
        for ($i = 0; $i < $len; $i++) {
            $char = $blob[$i];
            if ($escaped) {
                $repaired .= $char;
                $escaped = false;
            } elseif ($char === '\\') {
                $repaired .= $char;
                $escaped = true;
            } elseif ($char === '"') {
                $repaired .= $char;
                $in_string = !$in_string;
            } elseif ($in_string && $char === "\n") {
                $repaired .= "\\n";
            } elseif ($in_string && $char === "\r") {
                $repaired .= "\\r";
            } elseif ($in_string && $char === "\t") {
                $repaired .= "\\t";
            } elseif ($in_string && ord($char) < 0x20) {
                $repaired .= " ";
            } else {
                $repaired .= $char;
            }
        }

        $decoded = json_decode($repaired, true);
        if (is_array($decoded) && (!empty($decoded['content']) || !empty($decoded['title']))) {
            return $decoded;
        }

        // 5. Clean trailing commas before closing braces/brackets
        $cleaned_commas = preg_replace('/,\s*([\}\]])/', '$1', $repaired);
        $decoded = json_decode($cleaned_commas, true);
        if (is_array($decoded) && (!empty($decoded['content']) || !empty($decoded['title']))) {
            return $decoded;
        }
    }

    // 6. Advanced Fallback: Regex & token-based field extraction
    $result = [];
    if (preg_match('/"title"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/', $text, $m)) {
        $result['title'] = stripcslashes($m[1]);
    }
    if (preg_match('/"category_id"\s*:\s*(\d+)/', $text, $m)) {
        $result['category_id'] = (int)$m[1];
    }
    if (preg_match('/"excerpt"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/', $text, $m)) {
        $result['excerpt'] = stripcslashes($m[1]);
    }
    
    // Extract changelog array
    if (preg_match('/"changelog"\s*:\s*\[([\s\S]*?)\]/', $text, $m)) {
        $items = [];
        if (preg_match_all('/"((?:\\\\.|[^"\\\\])*)"/', $m[1], $itemMatches)) {
            foreach ($itemMatches[1] as $item) {
                $items[] = stripcslashes($item);
            }
        }
        $result['changelog'] = $items;
    }

    // Extract content
    $contentPatterns = [
        '/"content"\s*:\s*"([\s\S]*?)"\s*,\s*"(?:title|excerpt|category_id|changelog)"/',
        '/"(?:title|excerpt|category_id|changelog)"\s*:\s*[\s\S]*?,\s*"content"\s*:\s*"([\s\S]*?)"\s*\}/',
        '/"content"\s*:\s*"([\s\S]*?)"\s*\}?$/',
    ];
    foreach ($contentPatterns as $pat) {
        if (preg_match($pat, $text, $m)) {
            $result['content'] = stripcslashes($m[1]);
            break;
        }
    }

    if (empty($result['content'])) {
        $idx = strpos($text, '"content"');
        if ($idx !== false) {
            $afterKey = ltrim(substr($text, $idx + strlen('"content"')));
            if (str_starts_with($afterKey, ':')) {
                $afterColon = ltrim(substr($afterKey, 1));
                if (str_starts_with($afterColon, '"')) {
                    $rawC = substr($afterColon, 1);
                    $rawC = preg_replace('/"\s*\}?\s*$/', '', $rawC);
                    $result['content'] = stripcslashes($rawC);
                }
            }
        }
    }

    if (!empty($result['content']) || !empty($result['title'])) {
        return $result;
    }

    return [];
}

function ai_generate_thumbnail_file(string $title, string $slug, array $ai): string {
    $visual = $ai['visual'] ?? [];
    $image  = $ai['image'] ?? [];
    
    // 1. Visual Prompt with ag/gemini-3.6-flash-high
    $promptReq = "Bertindaklah sebagai Visual Art Director kelas dunia. Buat 1 prompt gambar bahasa Inggris (16:9 widescreen) " .
        "yang SANGAT REALISTIS (photorealistic, real-life documentary photography, 8k, cinematic lighting) " .
        "dan 100% RELEVAN dengan judul artikel: \"{$title}\".\n\n" .
        "PANDUAN GAYA VISUAL (WAJIB REALISTIS & RELEVAN):\n" .
        "1. GAYA FOTOGRAFI NYATA: Tampilkan foto asli berkualitas tinggi (shot on 35mm / 50mm f/1.8 lens, authentic shallow depth of field, real tactile textures, subtle natural film grain, realistic studio/ambient lighting).\n" .
        "2. RELEVANSI OBJEK NYATA DENGAN TOPIK: Workstation developer nyata, rack server enterprise rapi di datacenter modern, hardware security key fisik asli, atau meja kerja software engineer minimalis.\n" .
        "3. DILARANG KERAS GAYA KARTUN / 3D FANTASI: JANGAN gunakan 3D isometric cartoon, jangan gunakan octane render fiksi. Utamakan fotografi realistis lingkungan nyata.\n" .
        "4. DILARANG ADA TEKS: ABSOLUTELY NO text, NO letters, NO words, NO typography, NO watermark, NO fake logo in the image.\n\n" .
        "Balas HANYA prompt gambar bahasa Inggris murni tanpa teks pengantar atau markdown.";
    
    $visPrompt = "";
    if (!empty($visual['base_url']) && !empty($visual['api_key'])) {
        try {
            $visPrompt = ai_chat_call($visual, [['role' => 'user', 'content' => $promptReq]], 60);
        } catch (Throwable $e) {}
    }
    if (!$visPrompt) {
        $visPrompt = "Ultra-photorealistic 8k photograph of an authentic modern tech workstation and real enterprise hardware related to {$title}, shot on 35mm f/1.8 lens, natural depth of field, tactile real-world textures, warm cinematic ambient lighting, clean minimal workspace, high-end editorial photography, no text, no letters, no words, no watermark";
    }

    $visPrompt = trim(preg_replace('/[\r\n\t]+/', ' ', $visPrompt));

    // 2. Try primary image model
    $rawImage = null;
    if (!empty($image['base_url']) && !empty($image['api_key'])) {
        try {
            $payload = ['model' => $image['model'], 'messages' => [['role' => 'user', 'content' => $visPrompt]]];
            $ch = curl_init(rtrim($image['base_url'], '/') . '/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $image['api_key'],
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($payload)
            ]);
            $res = json_decode(curl_exec($ch), true);
            curl_close($ch);
            
            $content = $res['choices'][0]['message']['content'] ?? '';
            if (preg_match('/data:image\/[^;]+;base64,([A-Za-z0-9+\/=]+)/', $content, $m)) {
                $rawImage = base64_decode($m[1]);
            }
        } catch (Throwable $e) {}
    }

    // 3. Fallback to Pollinations Flux if primary failed
    if (!$rawImage || strlen($rawImage) < 1024) {
        try {
            $url = "https://image.pollinations.ai/prompt/" . rawurlencode($visPrompt) . "?width=1280&height=720&model=flux&nologo=true";
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AizuDemy/1.0'
            ]);
            $rawImage = curl_exec($ch);
            curl_close($ch);
        } catch (Throwable $e) {}
    }

    if ($rawImage && strlen($rawImage) > 1024) {
        $filename = ($slug ?: 'post') . '-' . date('YmdHis') . '.png';
        $uploadDir = rtrim(UPLOAD_PATH, '/');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        @chmod($uploadDir, 0755);
        $targetFile = $uploadDir . '/' . $filename;
        file_put_contents($targetFile, $rawImage);
        @chmod($targetFile, 0644);
        return '/assets/uploads/' . $filename;
    }

    return '';
}

function ai_auto_repair_post(int $postId): array {
    $stmt = db()->prepare("SELECT p.*, c.name as cat_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id = ?");
    $stmt->execute([$postId]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) {
        throw new RuntimeException("Artikel dengan ID #{$postId} tidak ditemukan.");
    }

    $ai = ai_config(true);
    $writer = $ai['writer'];
    $editor = $ai['editor'] ?? $writer;

    if (empty($writer['base_url']) || empty($writer['api_key'])) {
        throw new RuntimeException("Kredensial AI Writer belum dikonfigurasi di /admin/ai-settings.");
    }

    $categories = all_categories();
    $catOptions = array_map(function($c) {
        return ['id' => (int)$c['id'], 'name' => $c['name'], 'slug' => $c['slug']];
    }, $categories);

    $wordCountBefore = str_word_count(strip_tags($p['content']));
    $hadNoThumbnail = empty($p['thumbnail']);
    $hadNoCategory = empty($p['category_id']);

    // Step 1: LLM Content, SEO, Formatting & Category Enhancement with Retry
    $systemPrompt = "Anda adalah Senior Tech Editor & Content Specialist AizuDemy. Tugas Anda adalah menganalisis, memperbaiki, menyempurnakan, dan melengkapi artikel blog teknologi.\n" .
        "Aturan Penulisan:\n" .
        "1. KONTEN: Jika konten terlalu pendek, usang, atau tidak terstruktur, perluas menjadi artikel yang mendalam, berbobot, mudah dipahami pemula namun teknis dan aplikatif.\n" .
        "2. FORMAT: Gunakan semantic HTML yang rapi (<h2>, <h3>, <p>, <ul>, <ol>, <pre><code class=\"language-...\">, <blockquote>, <strong>). Gunakan single quotes (') untuk atribut HTML agar tidak bentrok dengan JSON.\n" .
        "3. KATEGORI: Pilih ID kategori yang paling akurat dari daftar kategori yang tersedia.\n" .
        "4. EXCERPT: Buat ringkasan SEO (120-160 karakter) yang memikat.\n" .
        "5. CHANGELOG: Berikan array 'changelog' dalam bahasa Indonesia yang merinci poin-poin perbaikan spesifik yang telah Anda lakukan.\n" .
        "Wajib kembalikan HANYA JSON murni dengan format:\n" .
        "{\n" .
        "  \"title\": \"Judul artikel yang disempurnakan\",\n" .
        "  \"category_id\": 1,\n" .
        "  \"excerpt\": \"Ringkasan SEO artikel...\",\n" .
        "  \"content\": \"<p>Konten HTML lengkap...</p>\",\n" .
        "  \"changelog\": [\"Poin 1...\", \"Poin 2...\"]\n" .
        "}";

    $userPrompt = "Perbaiki dan sempurnakan artikel berikut:\n\n" .
        "JUDUL SAAT INI: " . $p['title'] . "\n" .
        "KATEGORI SAAT INI: " . ($p['cat_name'] ?: 'Belum ada kategori') . " (ID: " . ($p['category_id'] ?: 'null') . ")\n" .
        "EXCERPT SAAT INI: " . ($p['excerpt'] ?: 'Kosong') . "\n" .
        "KONTEN SAAT INI:\n" . $p['content'] . "\n\n" .
        "DAFTAR KATEGORI TERSEDIA:\n" . json_encode($catOptions, JSON_UNESCAPED_UNICODE) . "\n\n" .
        "Balas HANYA JSON valid sesuai spesifikasi.";

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt]
    ];

    $parsed = [];
    $lastError = '';
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        try {
            $responseRaw = ai_chat_call($writer, $messages, 300);
            $parsed = ai_parse_json($responseRaw);
            if (!empty($parsed['content']) || !empty($parsed['title'])) {
                break;
            }
            $lastError = 'Struktur JSON tidak memuat field content atau title';
        } catch (Throwable $e) {
            $lastError = $e->getMessage();
        }

        if ($attempt < 3) {
            $messages[] = ['role' => 'assistant', 'content' => $responseRaw ?? ''];
            $messages[] = ['role' => 'user', 'content' => "Error parsing JSON ({$lastError}). Tolong kembalikan HANYA JSON murni yang valid tanpa teks pembuka/penutup, dan pastikan tanda kutip HTML di-escape atau gunakan single quotes."];
            sleep(1);
        }
    }

    if (empty($parsed['content'])) {
        throw new RuntimeException("AI tidak mengembalikan struktur JSON yang valid setelah 3 percobaan: {$lastError}");
    }

    $newTitle = trim($parsed['title'] ?? $p['title']) ?: $p['title'];
    $newContent = trim($parsed['content']);
    $newExcerpt = trim($parsed['excerpt'] ?? '') ?: mb_substr(strip_tags($newContent), 0, 160) . '…';
    $newCatId = !empty($parsed['category_id']) ? (int)$parsed['category_id'] : $p['category_id'];
    
    // Verify valid category_id
    $validCatIds = array_column($categories, 'id');
    if (!in_array($newCatId, $validCatIds)) {
        $newCatId = $p['category_id'] ?: ($validCatIds[0] ?? null);
    }

    $changelog = is_array($parsed['changelog'] ?? null) ? $parsed['changelog'] : [];

    // Step 2: Thumbnail Generation if missing
    $newThumbnail = $p['thumbnail'];
    if (empty($newThumbnail)) {
        $generatedThumb = ai_generate_thumbnail_file($newTitle, $p['slug'], $ai);
        if ($generatedThumb) {
            $newThumbnail = $generatedThumb;
            $changelog[] = "Membuat dan memasang thumbnail 3D baru beresolusi tinggi dengan AI.";
        }
    }

    $wordCountAfter = str_word_count(strip_tags($newContent));
    if ($wordCountAfter > $wordCountBefore + 30) {
        $changelog[] = "Memperkaya konten dari {$wordCountBefore} kata menjadi {$wordCountAfter} kata (+".($wordCountAfter - $wordCountBefore)." kata).";
    }

    // Step 3: Save to Database
    $upStmt = db()->prepare("UPDATE posts SET title = ?, category_id = ?, excerpt = ?, content = ?, thumbnail = ?, status = 'publish', updated_at = NOW() WHERE id = ?");
    $upStmt->execute([$newTitle, $newCatId, $newExcerpt, $newContent, $newThumbnail ?: null, $postId]);

    // Fetch updated category name
    $catNameStmt = db()->prepare("SELECT name FROM categories WHERE id = ?");
    $catNameStmt->execute([$newCatId]);
    $catName = $catNameStmt->fetchColumn() ?: '-';

    return [
        'ok' => true,
        'post_id' => $postId,
        'title' => $newTitle,
        'slug' => $p['slug'],
        'thumbnail' => $newThumbnail,
        'cat_name' => $catName,
        'word_count_before' => $wordCountBefore,
        'word_count_after' => $wordCountAfter,
        'changelog' => $changelog
    ];
}

/**
 * Render an ad slot with container and micro-label.
 */
function render_ad(string $slot, string $extraClass = ''): string {
    $clientId = setting('adsense_client_id', 'ca-pub-1354033982953234');
    $adCode = trim(setting("adsense_{$slot}", ''));
    $affiliateCode = affiliate_for_slot($slot, $post['category_id'] ?? null);
    if ($adCode === '' && $affiliateCode === '') {
        return '';
    }
    $adCode = $adCode ?: $affiliateCode;

    // If only numeric slot ID is provided, build standard responsive AdSense unit
    if (is_numeric($adCode)) {
        $adCode = '<ins class="adsbygoogle" style="display:block" data-ad-client="'.e($clientId).'" data-ad-slot="'.e($adCode).'" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
    }

    $slotSlug = preg_replace('/[^a-z0-9_-]+/i', '-', $slot);
    $html = '<div class="ad-slot-wrap ad-slot-'.e($slotSlug).' '.e($extraClass).'">';
    $html .= '<div class="ad-label">IKLAN</div>';
    $html .= '<div class="ad-slot-body">'.$adCode.'</div>';
    $html .= '</div>';
    return $html;
}

function affiliate_for_slot(string $slot, ?int $categoryId = null): string {
    try {
        if ($categoryId) {
            $stmt = db()->prepare('SELECT html_code FROM affiliates WHERE slot = ? AND is_active = 1 AND (category_id = ? OR category_id IS NULL) ORDER BY sort_order ASC, id ASC LIMIT 1');
            $stmt->execute([$slot, $categoryId]);
            $row = $stmt->fetchColumn();
            if ($row) return (string)$row;
        }
        $stmt = db()->prepare('SELECT html_code FROM affiliates WHERE slot = ? AND is_active = 1 AND category_id IS NULL ORDER BY sort_order ASC, id ASC LIMIT 1');
        $stmt->execute([$slot]);
        $row = $stmt->fetchColumn();
        return $row ? (string)$row : '';
    } catch (Throwable $e) {
        return '';
    }
}

/**
 * Inject in-article ad slot after paragraph 2.
 */
function inject_in_article_ad(string $content, string $adHtml): string {
    if (empty($adHtml) || empty($content)) return $content;

    $paragraphs = explode('</p>', $content);
    if (count($paragraphs) < 3) {
        return $content . $adHtml;
    }

    $targetIdx = 1; // After paragraph 2 (index 1)
    $paragraphs[$targetIdx] .= '</p>' . $adHtml;
    return implode('</p>', array_slice($paragraphs, 0, $targetIdx)) . '</p>' . implode('</p>', array_slice($paragraphs, $targetIdx + 1));
}

/**
 * Detect whether client is on mobile / Android / iOS device.
 */
function is_mobile_device(): bool {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool)preg_match('/(android|iphone|ipad|ipod|mobile|touch|opera mini|blackberry)/i', $ua);
}

/**
 * Estimate reading time in minutes based on word count (~200 wpm).
 */
function estimated_reading_time(string $content): string {
    $words = str_word_count(strip_tags($content));
    $minutes = max(1, (int)ceil($words / 200));
    return "{$minutes} mnt baca";
}

/**
 * Generate interactive Table of Contents (TOC) and inject heading anchor IDs.
 */
function generate_toc_and_anchors(string $content): array {
    $tocItems = [];
    $index = 0;

    $pattern = '/<(h[23])([^>]*)>(.*?)<\/\1>/si';
    $modifiedContent = preg_replace_callback($pattern, function($matches) use (&$tocItems, &$index) {
        $tag = strtolower($matches[1]);
        $attrs = $matches[2];
        $text = strip_tags($matches[3]);
        $slug = 'section-' . ($index + 1) . '-' . slugify($text);
        
        $tocItems[] = [
            'level' => $tag === 'h2' ? 2 : 3,
            'title' => $text,
            'id' => $slug
        ];
        $index++;

        return "<{$tag}{$attrs} id=\"{$slug}\" class=\"post-heading-anchor\">{$matches[3]}</{$tag}>";
    }, $content);

    $tocHtml = '';
    if (count($tocItems) >= 2) {
        $tocHtml .= '<div class="post-toc">';
        $tocHtml .= '  <div class="post-toc-header" onclick="toggleToc(this)">';
        $tocHtml .= '    <span class="post-toc-title">📋 Daftar Isi Materi</span>';
        $tocHtml .= '    <span class="post-toc-toggle">Tutup ▴</span>';
        $tocHtml .= '  </div>';
        $tocHtml .= '  <nav class="post-toc-body">';
        $tocHtml .= '    <ol class="post-toc-list">';
        foreach ($tocItems as $item) {
            $class = $item['level'] === 3 ? 'toc-sub-item' : 'toc-main-item';
            $tocHtml .= "      <li class=\"{$class}\"><a href=\"#{$item['id']}\">" . e($item['title']) . "</a></li>";
        }
        $tocHtml .= '    </ol>';
        $tocHtml .= '  </nav>';
        $tocHtml .= '</div>';
    }

    return [
        'content' => $modifiedContent,
        'toc' => $tocHtml,
        'count' => count($tocItems)
    ];
}

/**
 * Get Client Real IP Address
 */
function get_client_ip(): string {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ipList = explode(',', $_SERVER[$k]);
            $ip = trim($ipList[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Get human-readable device name from User-Agent
 */
function get_device_name(): string {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $browser = 'Browser';
    $os = 'Device';

    if (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edg') === false) $browser = 'Chrome';
    elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) $browser = 'Safari';
    elseif (stripos($ua, 'Firefox') !== false) $browser = 'Firefox';
    elseif (stripos($ua, 'Edg') !== false) $browser = 'Edge';

    if (stripos($ua, 'Android') !== false) $os = 'Android';
    elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';
    elseif (stripos($ua, 'Windows') !== false) $os = 'Windows PC';
    elseif (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS') !== false) $os = 'Mac';
    elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';

    return "{$browser} on {$os}";
}

/**
 * Sentinel Gateway Configuration Constants
 */
if (!defined('SENTINEL_GATEWAY_URL')) define('SENTINEL_GATEWAY_URL', 'http://127.0.0.1:20129');
if (!defined('SENTINEL_APP_ID')) define('SENTINEL_APP_ID', 'aizudemy');
if (!defined('SENTINEL_API_KEY')) define('SENTINEL_API_KEY', 'aizu_sentinel_sec_aizudemy_2026');

/**
 * Create a new WhatsApp 2FA Authorization Request via STB Sentinel Gateway
 */
function create_wa_auth_request(array $userData): array {
    $requestId = 'aizudemy_' . bin2hex(random_bytes(10));
    $secretCode = sprintf("%06d", random_int(100000, 999999));
    $expiresAt = time() + 180; // 3 minutes validity
    $targetWa = setting('admin_wa_number', '62895626089159');
    $clientIp = get_client_ip();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User-Agent';

    $stmt = db()->prepare("INSERT INTO wa_auth_requests (request_id, user_data, secret_code, target_wa, client_ip, user_agent, status, expires_at) VALUES (?, ?, ?, ?, ?, ?, 'PENDING', ?)");
    $stmt->execute([
        $requestId,
        json_encode($userData, JSON_UNESCAPED_UNICODE),
        $secretCode,
        $targetWa,
        $clientIp,
        $userAgent,
        $expiresAt
    ]);

    $reqData = [
        'request_id' => $requestId,
        'user_data' => $userData,
        'secret_code' => $secretCode,
        'target_wa' => $targetWa,
        'client_ip' => $clientIp,
        'device_name' => get_device_name(),
        'expires_at' => $expiresAt
    ];

    // Trigger WhatsApp notification through STB Sentinel Master Gateway
    send_wa_2fa_notification($reqData);

    return $reqData;
}

/**
 * Send WhatsApp 2FA Authorization alert to Admin via STB Master Sentinel Gateway
 */
function send_wa_2fa_notification(array $req): bool {
    $user = $req['user_data'] ?? [];
    $targetWa = $req['target_wa'] ?? setting('admin_wa_number', '62895626089159');
    $baseUrl = defined('BASE_URL') ? BASE_URL : 'https://aizudemy.net';

    $payload = [
        'app_id' => SENTINEL_APP_ID,
        'api_key' => SENTINEL_API_KEY,
        'ticket_id' => $req['request_id'],
        'req_id' => $req['request_id'],
        'action_name' => 'Login Dashboard Administrator (God Role)',
        'purpose' => 'Akses Dashboard Admin AizuDemy',
        'service' => 'aizudemy',
        'service_name' => 'AizuDemy',
        'target_phone' => $targetWa,
        'user_data' => $user,
        'client_ip' => $req['client_ip'] ?? get_client_ip(),
        'device_name' => $req['device_name'] ?? get_device_name(),
        'callback_url' => rtrim($baseUrl, '/') . '/api/wa-webhook'
    ];

    $endpoints = [
        SENTINEL_GATEWAY_URL . '/api/sentinel/auth-request',
        'http://192.168.100.22:8000/api/sentinel/auth-request',
        SENTINEL_GATEWAY_URL . '/api/whatsapp/send-2fa'
    ];

    foreach ($endpoints as $url) {
        try {
            $ch = curl_init($url);
            $bodyJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
            
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $bodyJson,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'User-Agent: AizuDemy-SentinelClient/1.0'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2
            ]);
            $resp = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                return true;
            }
        } catch (Throwable $e) {}
    }

    return false;
}

/**
 * Fetch WhatsApp 2FA Authorization Request
 */
function get_wa_auth_request(string $requestId): ?array {
    $stmt = db()->prepare("SELECT * FROM wa_auth_requests WHERE request_id = ?");
    $stmt->execute([$requestId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;

    if ($row['status'] === 'PENDING' && time() > (int)$row['expires_at']) {
        db()->prepare("UPDATE wa_auth_requests SET status = 'EXPIRED' WHERE id = ?")->execute([$row['id']]);
        $row['status'] = 'EXPIRED';
    }

    $row['user_data'] = json_decode($row['user_data'], true) ?: [];
    return $row;
}

/**
 * Send concise WhatsApp notification when a new article is published
 */
function send_wa_publish_notification(string $title, string $url, string $category = ''): bool {
    $targetWa = setting('admin_wa_number', '62895626089159');
    $catLine = $category ? "\n• 📂 *Kategori*: " . ucfirst($category) : "";
    $waMsg = "📰 *[AIZUDEMY] Artikel Baru Terbit!* ✨\n\n"
           . "• 📝 *Judul*: {$title}{$catLine}\n"
           . "• 🔗 *Link*: {$url}\n\n"
           . "⏳ _Diterbitkan otomatis via AI Auto-Publisher_";

    $payload = [
        'target_phone' => $targetWa,
        'message' => $waMsg
    ];

    $endpoints = [
        SENTINEL_GATEWAY_URL . '/api/sentinel/notify',
        'http://192.168.100.22:8000/api/sentinel/notify'
    ];

    foreach ($endpoints as $url) {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'User-Agent: AizuDemy-Publisher/2.0'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2
            ]);
            $resp = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode >= 200 && $httpCode < 300) return true;
        } catch (Throwable $e) {}
    }
    return false;
}

/**
 * Extract 3-4 key takeaways / TL;DR bullets from article content
 */
function extract_tldr_takeaways(string $content, string $excerpt = ''): array {
    $bullets = [];
    // 1. Look for explicit bullet lists in early paragraphs
    if (preg_match_all('/(?:<li>|<p>[•\-*]\s*)([^<]+)(?:<\/li>|<\/p>)/i', $content, $m)) {
        foreach ($m[1] as $item) {
            $cleaned = trim(strip_tags($item));
            if (mb_strlen($cleaned) >= 20 && mb_strlen($cleaned) <= 180 && !in_array($cleaned, $bullets, true)) {
                $bullets[] = $cleaned;
                if (count($bullets) >= 3) break;
            }
        }
    }
    // 2. If not enough, extract meaningful sentences from headings and early paragraphs
    if (count($bullets) < 3) {
        $cleanText = strip_tags(preg_replace('/<pre[\s\S]*?<\/pre>|<code[\s\S]*?<\/code>/i', '', $content));
        $sentences = preg_split('/(?<=[.!?])\s+/u', $cleanText);
        foreach ($sentences as $s) {
            $s = trim($s);
            if (mb_strlen($s) >= 35 && mb_strlen($s) <= 160 && !str_starts_with($s, 'http') && !in_array($s, $bullets, true)) {
                $bullets[] = $s;
                if (count($bullets) >= 3) break;
            }
        }
    }
    // 3. Fallback to excerpt
    if (empty($bullets) && $excerpt) {
        $bullets[] = $excerpt;
    }
    return array_slice($bullets, 0, 3);
}

<?php
require_once __DIR__.'/../app/config/config.php';
header('Content-Type: application/json');
$headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!$data) $data = $_POST;

$token = $_SERVER['HTTP_X_TOKEN'] ?? $_SERVER['X_TOKEN'] ?? $headers['x-token'] ?? $_GET['token'] ?? $data['token'] ?? $data['X-Token'] ?? '';
$validTokens = ['YOUR_PUBLISH_TOKEN_HERE']; // ganti dengan token milikmu — jangan commit token asli
if(!in_array($token, $validTokens, true)){
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Unauthorized — token tidak valid']);
    exit;
}
$title = trim($data['title'] ?? '');
$content = trim($data['content'] ?? '');
$catSlug = trim($data['category_slug'] ?? '');
$excerpt = trim($data['excerpt'] ?? '');
$slug = trim($data['slug'] ?? '');
$thumbnail = trim($data['thumbnail'] ?? '');
$status = trim($data['status'] ?? 'publish');
if(!in_array($status, ['publish','draft'], true)) $status='publish';
if(!$title || !$content){ echo json_encode(['ok'=>false,'error'=>'title & content wajib']); exit; }
if(!$slug) $slug = slugify($title);
$baseSlug=$slug; $i=1;
while(true){
    $chk=db()->prepare("SELECT id FROM posts WHERE slug=?"); $chk->execute([$slug]);
    if(!$chk->fetch()) break;
    $slug=$baseSlug.'-'.$i; $i++;
}
$catId=null;
if($catSlug){
    $c=db()->prepare("SELECT id FROM categories WHERE slug=?"); $c->execute([$catSlug]);
    $catId=$c->fetchColumn() ?: null;
    if(!$catId){
        db()->prepare("INSERT INTO categories (name,slug) VALUES (?,?)")->execute([ucfirst($catSlug),$catSlug]);
        $catId=db()->lastInsertId();
    }
}
if(!$excerpt) $excerpt = mb_substr(strip_tags($content),0,160).'…';
// thumbnail: allow absolute http, site-absolute /..., or plain filename -> keep as-is; normalize to absolute if http
// if empty and content has first img, caller should have set it; fallback left null (OG will fallback)
$now = date('Y-m-d H:i:s');
db()->prepare("INSERT INTO posts (category_id,title,slug,excerpt,content,thumbnail,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$catId,$title,$slug,$excerpt,$content, $thumbnail ?: null, $status, $now, $now]);
echo json_encode(['ok'=>true,'slug'=>$slug,'url'=>BASE_URL.'/post/'.$slug,'id'=>db()->lastInsertId(),'thumbnail'=>$thumbnail,'status'=>$status]);

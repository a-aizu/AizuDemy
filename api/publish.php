<?php
require_once __DIR__.'/../app/config/config.php';
header('Content-Type: application/json');
$token = $_SERVER['HTTP_X_TOKEN'] ?? $_GET['token'] ?? '';
$tokenFile = '/opt/lampp/etc/aizudemy-publish.token';
$validToken = file_exists($tokenFile) ? trim(file_get_contents($tokenFile)) : (getenv('AIZUDEMY_PUBLISH_TOKEN') ?: 'YOUR_PUBLISH_TOKEN_HERE');
if(!$token || !hash_equals($validToken, $token)){
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Unauthorized — invalid token']);
    exit;
}
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!$data) $data = $_POST;
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
$createdAt = trim($data['created_at'] ?? '');
if($createdAt && strtotime($createdAt)){
    db()->prepare("INSERT INTO posts (category_id,title,slug,excerpt,content,thumbnail,status,created_at) VALUES (?,?,?,?,?,?,?,?)")->execute([$catId,$title,$slug,$excerpt,$content, $thumbnail ?: null, $status, $createdAt]);
} else {
    db()->prepare("INSERT INTO posts (category_id,title,slug,excerpt,content,thumbnail,status) VALUES (?,?,?,?,?,?,?)")->execute([$catId,$title,$slug,$excerpt,$content, $thumbnail ?: null, $status]);
}
echo json_encode(['ok'=>true,'slug'=>$slug,'url'=>BASE_URL.'/post/'.$slug,'id'=>db()->lastInsertId(),'thumbnail'=>$thumbnail,'status'=>$status]);

<?php
require_once __DIR__.'/../app/config/config.php';
header('Content-Type: application/json');

$headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(), CASE_LOWER) : [];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;
$token = $_SERVER['HTTP_X_TOKEN'] ?? $_SERVER['X_TOKEN'] ?? $headers['x-token'] ?? $_GET['token'] ?? $data['token'] ?? '';
$validTokens = ['YOUR_PUBLISH_TOKEN_HERE']; // ganti dengan token milikmu — jangan commit token asli
if(!in_array($token, $validTokens, true)){
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Unauthorized']);
    exit;
}

$key = trim($data['key'] ?? '');
if($key && preg_match('/^[a-f0-9]{64}$/i', $key)){
    setting_save('retry_key_'.$key, 'active');
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false, 'error'=>'Invalid key format']);
}

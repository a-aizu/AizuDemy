<?php
require_once __DIR__.'/../app/config/config.php';
header('Content-Type: application/json');

if(empty($_SESSION['aizudemy_admin'])){
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Sesi admin telah berakhir. Silakan login kembali.']);
    exit;
}

try {
    $script = __DIR__.'/../scripts/daily_publisher.py';
    if(!file_exists($script)){
        http_response_code(404);
        echo json_encode(['ok'=>false,'error'=>'Script publisher tidak ditemukan.']);
        exit;
    }

    $cmd = "nohup /usr/bin/python3 ".escapeshellarg($script)." > /tmp/admin_trigger.log 2>&1 &";
    exec($cmd);

    echo json_encode([
        'ok' => true,
        'message' => '⚡ AI Auto-Publisher berhasil dipicu! Artikel baru sedang diproses di background.'
    ]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}

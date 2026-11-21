<?php
require_once __DIR__.'/../app/config/config.php';
header('Content-Type: text/html; charset=UTF-8');

$key = trim($_GET['key'] ?? '');
if(!$key || !preg_match('/^[a-f0-9]{64}$/i', $key)){
    show_status_page(false, 'Token Tidak Valid', 'Format token pemicu ulang tidak valid.');
    exit;
}

try {
    $stmt = db()->prepare("SELECT svalue FROM settings WHERE skey = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute(['retry_key_'.$key]);
    $val = $stmt->fetchColumn();

    if(!$val){
        show_status_page(false, 'Link Tidak Valid / Sudah Kadaluarsa', 'Link pemicu sekali pakai ini sudah pernah digunakan atau telah kadaluarsa (> 24 jam).');
        exit;
    }

    // Hapus token agar menjadi sekali pakai (single-use)
    db()->prepare("DELETE FROM settings WHERE skey = ?")->execute(['retry_key_'.$key]);

    // Eksekusi pemicu di background
    $script = __DIR__.'/../scripts/daily_publisher.py';
    if(file_exists($script)){
        $cmd = "nohup /usr/bin/python3 ".escapeshellarg($script)." > /tmp/retry_publisher.log 2>&1 &";
        exec($cmd);
    }

    show_status_page(true, 'Pemicu Ulang Berhasil Diaktifkan!', 'Sistem AI Auto-Publisher sedang memproses ulang pembuatan dan publikasi artikel ke AizuDemy. Hasilnya akan segera tampil dalam 1-2 menit.');
} catch(Exception $e) {
    show_status_page(false, 'Gagal Memproses Pemicu', 'Terjadi kesalahan server: '.htmlspecialchars($e->getMessage()));
}

function show_status_page($ok, $title, $msg){
    $bg = $ok ? '#005af0' : '#d93025';
    $icon = $ok ? '🚀' : '⚠️';
    $homeUrl = BASE_URL.'/';
    echo "<!doctype html><html lang='id'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>{$title}</title><style>body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background:#0f1115;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}.card{background:#1c1f26;border-radius:16px;padding:32px;max-width:440px;width:100%;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.5);border:1px solid #262b36}.icon{font-size:48px;margin-bottom:16px}h1{font-size:20px;margin:0 0 12px;color:#fff}p{color:#a3adbb;font-size:14px;line-height:1.6;margin:0 0 24px}.btn{display:inline-block;padding:12px 24px;background:{$bg};color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:14px;transition:.2s}.btn:hover{opacity:0.9}</style></head><body><div class='card'><div class='icon'>{$icon}</div><h1>{$title}</h1><p>{$msg}</p><a class='btn' href='{$homeUrl}'>Buka AizuDemy →</a></div></body></html>";
}

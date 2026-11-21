<?php
// Runtime bridge untuk publisher. Tidak pernah mengeluarkan secret melalui HTTP.
if(PHP_SAPI !== 'cli'){ http_response_code(404); exit; }
require_once __DIR__.'/config.php';
try {
    // Ambil seluruh judul artikel aktif hingga 500 entri untuk pengecekan anti-duplikasi absolut
    $recentPosts = db()->query("SELECT id, title, slug FROM posts ORDER BY id DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
    $categories = db()->query("SELECT id, name, slug FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'ok' => true,
        'roles' => ai_config(true),
        'recent_posts' => $recentPosts,
        'categories' => $categories
    ], JSON_UNESCAPED_SLASHES);
} catch(Throwable $e){
    fwrite(STDERR, $e->getMessage()."\n"); exit(1);
}

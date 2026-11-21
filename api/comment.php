<?php
// API Komentar: submit & list
header('Content-Type: application/json');
require __DIR__ . '/../app/config/database.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'list') {
    $post_id = intval($_GET['post_id'] ?? 0);
    $pdo = db();
    $stmt = $pdo->prepare("SELECT nama, komentar, created_at FROM comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$post_id]);
    echo json_encode(['status' => 'ok', 'comments' => $stmt->fetchAll()]);
    exit;
}

if ($action === 'submit') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $komentar = trim($_POST['komentar'] ?? '');

    if ($post_id <= 0 || empty($nama) || empty($komentar)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan komentar wajib diisi.']);
        exit;
    }
    if (strlen($komentar) > 2000) {
        echo json_encode(['status' => 'error', 'message' => 'Komentar terlalu panjang (max 2000 karakter).']);
        exit;
    }
    // Anti-spam sederhana: tolak jika ada >3 link
    if (substr_count($komentar, 'http') > 3) {
        echo json_encode(['status' => 'error', 'message' => 'Komentar ditolak (spam terdeteksi).']);
        exit;
    }

    try {
        $pdo = db();
        // Cek post exists
        $chk = $pdo->prepare("SELECT id FROM posts WHERE id = ? LIMIT 1");
        $chk->execute([$post_id]);
        if (!$chk->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Artikel tidak ditemukan.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, nama, email, komentar, status) VALUES (?,?,?,?, 'pending')");
        $stmt->execute([$post_id, mb_substr($nama, 0, 100), mb_substr($email, 0, 255), $komentar]);
        echo json_encode(['status' => 'ok', 'message' => 'Komentar terkirim! Menunggu moderasi admin.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan komentar.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);

<?php
// API Newsletter subscribe
header('Content-Type: application/json');
require __DIR__ . '/../app/config/database.php';

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Email tidak valid.']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE status='active'");
    $stmt->execute([$email]);
    echo json_encode(['status' => 'ok', 'message' => 'Berhasil berlangganan! Cek email untuk konfirmasi.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan. Coba lagi.']);
}

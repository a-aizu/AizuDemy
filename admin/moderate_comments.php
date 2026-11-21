<?php
// Moderasi komentar AizuDemy - lindungi dengan login admin
session_start();
require __DIR__ . '/app/config/database.php';

// Simple auth: cek session admin yang sudah ada
// Sesuaikan dengan sistem login yang ada
if (empty($_SESSION['admin_logged_in'])) {
    // Coba cek via admin session yang umum
    die('Akses ditolak. Login sebagai admin dulu.');
}

$pdo = db();
$msg = '';

// Aksi approve/reject/delete
if (!empty($_POST['action']) && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    if ($_POST['action'] === 'approve') {
        $pdo->prepare("UPDATE comments SET status='approved' WHERE id=?")->execute([$id]);
        $msg = 'Komentar disetujui.';
    } elseif ($_POST['action'] === 'spam') {
        $pdo->prepare("UPDATE comments SET status='spam' WHERE id=?")->execute([$id]);
        $msg = 'Ditandai spam.';
    } elseif ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM comments WHERE id=?")->execute([$id]);
        $msg = 'Komentar dihapus.';
    }
}

$filter = $_GET['filter'] ?? 'pending';
$valid = ['pending', 'approved', 'spam'];
if (!in_array($filter, $valid)) $filter = 'pending';

$stmt = $pdo->prepare("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON p.id = c.post_id WHERE c.status = ? ORDER BY c.created_at DESC LIMIT 100");
$stmt->execute([$filter]);
$comments = $stmt->fetchAll();

// Hitung pending
$pending = $pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Moderasi Komentar</title>
<style>body{font-family:sans-serif;max-width:900px;margin:20px auto;padding:0 15px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:8px;font-size:13px}.tabs{margin-bottom:15px}.tabs a{padding:6px 12px;margin-right:5px;background:#eee;border-radius:4px;text-decoration:none;color:#333}.tabs a.active{background:#2563eb;color:#fff}button{padding:4px 10px;margin:2px;cursor:pointer;border:none;border-radius:4px}.ap{background:#22c55e;color:#fff}.sp{background:#f59e0b;color:#fff}.del{background:#ef4444;color:#fff}</style>
</head><body>
<h2>💬 Moderasi Komentar <?= $pending > 0 ? "($pending pending)" : "" ?></h2>
<?php if($msg): ?><p style="color:green"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<div class="tabs">
  <?php foreach($valid as $v): ?><a href="?filter=<?= $v ?>" class="<?= $filter===$v?'active':'' ?>"><?= ucfirst($v) ?></a><?php endforeach; ?>
</div>
<table><tr><th>Nama</th><th>Komentar</th><th>Artikel</th><th>Waktu</th><th>Aksi</th></tr>
<?php foreach($comments as $cm): ?>
<tr>
  <td><?= htmlspecialchars($cm['nama']) ?><br><small><?= htmlspecialchars($cm['email']) ?></small></td>
  <td><?= nl2br(htmlspecialchars(mb_substr($cm['komentar'],0,300))) ?></td>
  <td><small><?= htmlspecialchars(mb_substr($cm['post_title'] ?? '-',0,40)) ?></small></td>
  <td><small><?= $cm['created_at'] ?></small></td>
  <td><form method="post" style="display:inline">
    <input type="hidden" name="id" value="<?= $cm['id'] ?>">
    <?php if($filter !== 'approved'): ?><button name="action" value="approve" class="ap">✓</button><?php endif; ?>
    <?php if($filter !== 'spam'): ?><button name="action" value="spam" class="sp">Spam</button><?php endif; ?>
    <button name="action" value="delete" class="del" onclick="return confirm('Hapus?')">✕</button>
  </form></td>
</tr>
<?php endforeach; ?>
</table>
<?php if(!$comments): ?><p>Tidak ada komentar <?= $filter ?>.</p><?php endif; ?>
</body></html>

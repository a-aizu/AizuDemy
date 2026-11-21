<?php
$ssoCallback = BASE_URL . '/sso-callback';
$aizuSsoUrl = 'https://aaizu.net/sso/?api_token=aizudemy_sso_token_2026&return=' . urlencode($ssoCallback);
$err = $err ?? ($_GET['err'] ?? '');
if($err === 'role_unauthorized') $err = '🚫 Akses Ditolak: Hanya akun Aizu Sphere ber-role God yang diizinkan mengakses Admin AizuDemy.';
elseif($err === 'sso_failed') $err = '⚠️ Gagal verifikasi otentikasi SSO Aizu Sphere.';
elseif($err === 'token_missing') $err = '⚠️ Token otentikasi SSO tidak ditemukan.';
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login — AizuDemy Admin</title><link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico"><link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon.png"><link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>"></head><body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg)">
<div class="admin-card" style="width:100%;max-width:400px;padding:32px 28px;text-align:center">
  <h2 style="margin:0 0 4px">🔐 Admin AizuDemy</h2>
  <p style="color:var(--muted);font-size:13px;margin:0 0 24px">Portal Otentikasi Khusus Role <b>God</b></p>
  
  <?php if(!empty($err)): ?>
    <div style="background:rgba(220,38,38,0.1);border:1px solid #dc2626;color:#dc2626;padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:20px;text-align:left;line-height:1.5">
      <?= e($err) ?>
    </div>
  <?php endif; ?>

  <a href="<?= e($aizuSsoUrl) ?>" class="btn" style="width:100%;justify-content:center;gap:10px;padding:14px;background:linear-gradient(135deg,#005af0,#00dcc0);color:#fff;font-weight:700;border:0;box-shadow:0 6px 20px rgba(0,90,240,0.35);text-decoration:none;border-radius:12px;display:flex;align-items:center;font-size:14px">
    <span>✦</span> Login via Aizu Sphere (SSO)
  </a>

  <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)">
    🔒 Otentikasi Terpusat Aizu Sphere · Hanya Akses <b>God Role</b><br>
    <a href="<?= BASE_URL ?>/" style="color:var(--muted);text-decoration:underline;display:inline-block;margin-top:10px">← Kembali ke blog</a>
  </div>
</div>
</body></html>

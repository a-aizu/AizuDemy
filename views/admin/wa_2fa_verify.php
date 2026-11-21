<?php
$req = $req ?? null;
if (!$req) {
    header("Location: " . BASE_URL . "/admin/login?err=token_missing");
    exit;
}
$user = $req['user_data'] ?? [];
$name = $user['name'] ?? $user['nama'] ?? $user['username'] ?? 'Alex';
$targetWa = $req['target_wa'] ?? '0895626089159';
$botWa = '6285271752516'; // Bot WhatsApp Gateway
$waDeepLink = "https://wa.me/{$botWa}?text=Y";
$timeLeft = max(0, (int)$req['expires_at'] - time());
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title>Verifikasi 2FA WhatsApp — AizuDemy</title>
  <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon.png">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css?v=<?= time() ?>">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      min-height: 100dvh;
      background: var(--bg);
      margin: 0;
      padding: 16px;
      font-family: 'Nunito Sans', sans-serif;
      box-sizing: border-box;
    }
    .wa-gate-card {
      width: 100%;
      max-width: 440px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 32px 24px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.12);
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .service-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--primary);
      background: rgba(0, 90, 240, 0.08);
      border: 1px solid rgba(0, 90, 240, 0.15);
      padding: 4px 12px;
      border-radius: 20px;
      margin-bottom: 18px;
    }
    /* Radar Pulsing Animation */
    .radar-box {
      position: relative;
      width: 84px;
      height: 84px;
      margin: 0 auto 16px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .radar-circle {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      border: 2px solid rgba(0, 220, 192, 0.4);
      animation: radarPulse 2s cubic-bezier(0.25, 0.8, 0.25, 1) infinite;
    }
    .radar-circle:nth-child(2) {
      animation-delay: 0.6s;
    }
    .radar-icon {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, #005af0, #00dcc0);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 26px;
      box-shadow: 0 8px 24px rgba(0, 90, 240, 0.4);
      z-index: 2;
      transition: all .3s ease;
    }
    @keyframes radarPulse {
      0% { transform: scale(0.6); opacity: 1; }
      100% { transform: scale(1.6); opacity: 0; }
    }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 14px;
      border-radius: 20px;
      background: rgba(0, 220, 192, 0.12);
      color: #0d9488;
      font-size: 11.5px;
      font-weight: 800;
      margin-bottom: 12px;
      letter-spacing: 0.5px;
    }
    .status-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #0d9488;
      animation: blink 1.2s infinite;
    }
    .meta-box {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 14px 16px;
      text-align: left;
      margin: 16px 0;
      font-size: 12px;
      line-height: 1.6;
    }
    .meta-box .row {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px dashed var(--border);
      padding: 5px 0;
    }
    .meta-box .row:last-child {
      border: 0;
      padding-bottom: 0;
    }
    .meta-box .lbl {
      color: var(--muted);
    }
    .meta-box .val {
      font-weight: 700;
      color: var(--main);
    }
    .instruction-box {
      background: rgba(37, 211, 102, 0.08);
      border: 1px solid rgba(37, 211, 102, 0.25);
      border-radius: 12px;
      padding: 12px 14px;
      margin: 14px 0 18px;
      font-size: 12px;
      color: var(--main);
      line-height: 1.5;
      text-align: center;
    }
    .countdown-wrap {
      font-size: 12.5px;
      color: var(--muted);
      margin: 12px 0 16px;
    }
    .countdown-num {
      font-weight: 800;
      color: var(--primary);
      font-size: 14px;
    }
    .btn-wa-action {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 13px 18px;
      background: #25d366;
      color: #fff;
      font-weight: 800;
      font-size: 14px;
      border-radius: 14px;
      border: 0;
      text-decoration: none;
      box-shadow: 0 6px 20px rgba(37, 211, 102, 0.35);
      cursor: pointer;
      transition: transform .2s, box-shadow .2s;
    }
    .btn-wa-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(37, 211, 102, 0.45);
      color: #fff;
    }
    .cancel-link {
      display: inline-block;
      margin-top: 18px;
      font-size: 12px;
      color: var(--muted);
      text-decoration: none;
      transition: color .2s;
    }
    .cancel-link:hover {
      color: var(--main);
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="wa-gate-card">
  <!-- Brand Header -->
  <div class="service-badge">
    🎓 AIZUDEMY 2FA SECURITY GATE
  </div>

  <!-- Radar Animation -->
  <div class="radar-box">
    <div class="radar-circle"></div>
    <div class="radar-circle"></div>
    <div class="radar-icon" id="radarIcon">🔒</div>
  </div>

  <div class="status-badge" id="statusBadge">
    <span class="status-dot" id="statusDot"></span>
    <span id="statusText">MENUNGGU BALASAN WHATSAPP</span>
  </div>

  <h2 style="font-size:20px;margin:0 0 6px;color:var(--main);font-weight:800">Verifikasi WhatsApp</h2>
  <p style="font-size:12.5px;color:var(--muted);margin:0;line-height:1.4">
    Peringatan masuk telah dikirimkan ke WhatsApp resmi Master Admin.
  </p>

  <!-- Login Metadata -->
  <div class="meta-box">
    <div class="row">
      <span class="lbl">Layanan</span>
      <span class="val" style="color:var(--primary)">AizuDemy (aizudemy.net)</span>
    </div>
    <div class="row">
      <span class="lbl">Akun Admin</span>
      <span class="val"><?= e($name) ?> (God Role)</span>
    </div>
    <div class="row">
      <span class="lbl">IP Address</span>
      <span class="val"><?= e($req['client_ip']) ?></span>
    </div>
    <div class="row">
      <span class="lbl">Perangkat</span>
      <span class="val"><?= e($req['device_name']) ?></span>
    </div>
  </div>

  <!-- Instructions (Pure Y/T, No Code) -->
  <div class="instruction-box">
    👉 Buka WhatsApp Anda, lalu balas dengan <b>Y</b> (atau <b>IZINKAN</b>) untuk masuk, atau <b>T</b> (atau <b>TOLAK</b>) untuk membatalkan.
  </div>

  <!-- Countdown -->
  <div class="countdown-wrap">
    Batas waktu persetujuan: <span id="countdownTimer" class="countdown-num">03:00</span>
  </div>

  <!-- Direct WhatsApp Action -->
  <a href="<?= e($waDeepLink) ?>" target="_blank" class="btn-wa-action">
    <span>💬</span> Buka WhatsApp & Balas Y
  </a>

  <div>
    <a href="<?= BASE_URL ?>/admin/login?err=cancelled" class="cancel-link">← Batalkan dan Kembali ke Login</a>
  </div>
</div>

<script>
var reqId = '<?= e($req['request_id']) ?>';
var timeLeft = <?= (int)$timeLeft ?>;
var pollInterval = null;

function updateCountdown() {
  if (timeLeft <= 0) {
    document.getElementById('countdownTimer').textContent = '00:00 (Kedaluwarsa)';
    document.getElementById('statusText').textContent = 'PERMINTAAN KEDALUWARSA';
    document.getElementById('statusBadge').style.background = 'rgba(239, 68, 68, 0.12)';
    document.getElementById('statusBadge').style.color = '#dc2626';
    document.getElementById('statusDot').style.background = '#dc2626';
    clearInterval(pollInterval);
    return;
  }
  var m = Math.floor(timeLeft / 60);
  var s = timeLeft % 60;
  document.getElementById('countdownTimer').textContent = (m < 10 ? '0'+m : m) + ':' + (s < 10 ? '0'+s : s);
  timeLeft--;
}

function checkAuthStatus() {
  fetch('<?= BASE_URL ?>/api/wa-auth-status?req_id=' + encodeURIComponent(reqId))
    .then(function(res){ return res.json(); })
    .then(function(data) {
      if (data.status === 'APPROVED') {
        clearInterval(pollInterval);
        document.getElementById('statusText').textContent = 'LOGIN DISETUJUI!';
        document.getElementById('statusBadge').style.background = 'rgba(22, 163, 74, 0.15)';
        document.getElementById('statusBadge').style.color = '#16a34a';
        document.getElementById('statusDot').style.background = '#16a34a';
        var icon = document.getElementById('radarIcon');
        icon.textContent = '✓';
        icon.style.background = '#16a34a';
        setTimeout(function() {
          window.location.href = data.redirect || '<?= BASE_URL ?>/admin';
        }, 600);
      } else if (data.status === 'REJECTED') {
        clearInterval(pollInterval);
        document.getElementById('statusText').textContent = 'AKSES DITOLAK';
        document.getElementById('statusBadge').style.background = 'rgba(220, 38, 38, 0.15)';
        document.getElementById('statusBadge').style.color = '#dc2626';
        document.getElementById('statusDot').style.background = '#dc2626';
        var icon = document.getElementById('radarIcon');
        icon.textContent = '✕';
        icon.style.background = '#dc2626';
        setTimeout(function() {
          window.location.href = '<?= BASE_URL ?>/admin/login?err=access_denied';
        }, 1200);
      } else if (data.status === 'EXPIRED') {
        clearInterval(pollInterval);
        timeLeft = 0;
        updateCountdown();
      }
    })
    .catch(function(err){});
}

pollInterval = setInterval(function() {
  updateCountdown();
  checkAuthStatus();
}, 1500);
updateCountdown();
</script>

</body>
</html>

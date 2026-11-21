<?php
$saved = false;
$pingResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_security'])) {
        setting_save('wa_2fa_enabled', isset($_POST['wa_2fa_enabled']) ? '1' : '0');
        setting_save('admin_wa_number', trim($_POST['admin_wa_number'] ?? '0895626089159'));
        setting_save('baileys_gateway_url', trim($_POST['baileys_gateway_url'] ?? 'http://127.0.0.1:3000'));
        $saved = true;
    } elseif (isset($_POST['test_wa_ping'])) {
        $testWa = trim($_POST['admin_wa_number'] ?? setting('admin_wa_number', '0895626089159'));
        $testReq = [
            'user_data' => ['name' => $_SESSION['aizudemy_user']['name'] ?? 'Alex (God Admin)'],
            'target_wa' => $testWa,
            'client_ip' => get_client_ip(),
            'device_name' => get_device_name(),
            'secret_code' => sprintf("%06d", random_int(100000, 999999))
        ];
        $sent = send_wa_2fa_notification($testReq);
        $pingResult = [
            'success' => $sent,
            'code' => $testReq['secret_code'],
            'target' => $testWa
        ];
    }
}

$wa2faEnabled = setting('wa_2fa_enabled', '1');
$adminWa = setting('admin_wa_number', '0895626089159');
$gatewayUrl = setting('baileys_gateway_url', 'http://127.0.0.1:3000');

// Fetch recent 2FA audit logs
$recentLogs = db()->query("SELECT * FROM wa_auth_requests ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.sec-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.sec-actions{display:flex;gap:10px;flex-wrap:wrap}
@media(max-width:860px){
  .sec-grid{grid-template-columns:1fr;gap:14px}
  .sec-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .sec-actions .btn{width:100%;justify-content:center}
}
@media(max-width:480px){
  .sec-actions{grid-template-columns:1fr}
}
</style>

<div class="admin-wrap" style="max-width:1100px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
    <div>
      <h2 style="margin:0 0 4px;font-size:20px">🔒 Keamanan & WhatsApp 2FA Sentinel</h2>
      <p style="color:var(--muted);font-size:13px;margin:0">Konfigurasi otentikasi dua faktor interaktif WhatsApp (Gaya Node STB Akari)</p>
    </div>
    <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin">← Dashboard</a>
  </div>

  <?php if($saved): ?>
    <div style="background:rgba(22,163,74,0.12);border:1px solid #16a34a;color:#16a34a;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;font-weight:600">
      ✓ Konfigurasi keamanan WhatsApp 2FA berhasil disimpan!
    </div>
  <?php endif; ?>

  <?php if($pingResult): ?>
    <div style="background:rgba(0,90,240,0.1);border:1px solid var(--primary);color:var(--primary);padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px">
      <b>📲 Tes Notifikasi WhatsApp Terkirim!</b><br>
      Pesan peringatan telah dikirim ke nomor <b><?= e($pingResult['target']) ?></b> dengan kode dummy <b><?= e($pingResult['code']) ?></b>.
    </div>
  <?php endif; ?>

  <div class="sec-grid">
    <!-- Settings Form -->
    <div class="admin-card" style="padding:20px">
      <h3 style="margin:0 0 16px;font-size:15px">⚙️ Pengaturan WhatsApp 2FA</h3>
      <form method="post">
        <div style="margin-bottom:16px;background:var(--bg);padding:14px;border-radius:10px;border:1px solid var(--border)">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:700;font-size:13.5px;color:var(--main)">
            <input type="checkbox" name="wa_2fa_enabled" value="1" <?= $wa2faEnabled === '1' ? 'checked' : '' ?> style="width:18px;height:18px">
            Aktifkan Wajib WhatsApp 2FA Saat Login
          </label>
          <p style="font-size:12px;color:var(--muted);margin:6px 0 0 28px;line-height:1.4">
            Saat diaktifkan, setiap login admin / SSO akan menahan akses ke layar radar persetujuan hingga admin membalas <b>Y / IZINKAN</b> di WhatsApp.
          </p>
        </div>

        <div style="margin-bottom:16px">
          <label style="display:block;font-size:12.5px;font-weight:700;color:var(--main);margin-bottom:6px">📱 Nomor WhatsApp Master Admin:</label>
          <input class="input" type="text" name="admin_wa_number" value="<?= e($adminWa) ?>" placeholder="0895626089159" required>
          <span style="font-size:11.5px;color:var(--muted);margin-top:4px;display:block">Format: 08xxx atau 628xxx (Nomor yang akan menerima pesan izin login).</span>
        </div>

        <div style="margin-bottom:20px">
          <label style="display:block;font-size:12.5px;font-weight:700;color:var(--main);margin-bottom:6px">🌐 Endpoint Baileys Gateway / STB URL:</label>
          <input class="input" type="text" name="baileys_gateway_url" value="<?= e($gatewayUrl) ?>" placeholder="http://127.0.0.1:3000">
          <span style="font-size:11.5px;color:var(--muted);margin-top:4px;display:block">Default: <code>http://127.0.0.1:3000</code> atau endpoint relay gateway Anda.</span>
        </div>

        <div class="sec-actions" style="display:flex;gap:10px;flex-wrap:wrap">
          <button type="submit" name="save_security" class="btn btn-primary" style="padding:10px 20px">
            💾 Simpan Pengaturan
          </button>
          <button type="submit" name="test_wa_ping" class="btn btn-ghost" style="padding:10px 18px">
            📲 Tes Kirim WhatsApp
          </button>
        </div>
      </form>
    </div>

    <!-- STB Sentinel Info & Webhook Integration -->
    <div class="admin-card" style="padding:20px">
      <h3 style="margin:0 0 16px;font-size:15px">🛡️ Integrasi STB & Bot Webhook</h3>
      
      <div style="background:var(--bg);padding:14px;border-radius:10px;border:1px solid var(--border);font-size:12.5px;line-height:1.6;margin-bottom:16px">
        <div style="font-weight:700;color:var(--main);margin-bottom:6px">🔗 URL Webhook Masuk (Inbound Bot):</div>
        <code style="background:rgba(0,90,240,0.08);color:var(--primary);padding:4px 8px;border-radius:6px;font-size:12px;word-break:break-all;display:block;margin-bottom:8px">
          <?= BASE_URL ?>/api/wa-webhook
        </code>
        <span style="color:var(--muted);font-size:11.5px">
          Arahkan webhook bot Baileys / STB ke URL ini agar balasan <code>Y</code> / <code>IZINKAN</code> / <code>TOLAK</code> dari admin langsung mengotorisasi login browser secara instan.
        </span>
      </div>

      <div style="font-size:12.5px;color:var(--muted);line-height:1.5">
        <b>💡 Cara Kerja Otentikasi 2FA:</b>
        <ol style="margin:6px 0 0;padding-left:18px">
          <li>Admin login melalui SSO Aizu Sphere atau kredensial.</li>
          <li>Sistem membuat sesi 2FA (waktu 3 menit) & mengirim alert ke WhatsApp.</li>
          <li>Browser membuka layar radar interaktif.</li>
          <li>Admin cukup membalas <b>Y</b> atau klik tautan WhatsApp untuk masuk!</li>
        </ol>
      </div>
    </div>
  </div>

  <!-- Audit Log Requests -->
  <div class="admin-card" style="padding:20px;margin-top:20px">
    <h3 style="margin:0 0 14px;font-size:15px">📜 Riwayat Permintaan Login WhatsApp 2FA (Terbaru)</h3>
    <?php if(empty($recentLogs)): ?>
      <p style="color:var(--muted);font-size:13px;margin:0">Belum ada riwayat permintaan otentikasi.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table" style="width:100%;margin:0;min-width:540px">
          <thead>
            <tr>
              <th style="padding:8px 10px">Waktu</th>
              <th style="padding:8px 10px">IP & Perangkat</th>
              <th style="padding:8px 10px">Target WA</th>
              <th style="padding:8px 10px">Kode</th>
              <th style="padding:8px 10px">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($recentLogs as $log): 
              $u = json_decode($log['user_data'], true) ?: [];
              $statusColor = match($log['status']) {
                'APPROVED' => '#16a34a',
                'PENDING'  => '#0284c7',
                'REJECTED' => '#dc2626',
                default    => '#64748b'
              };
            ?>
              <tr>
                <td style="white-space:nowrap;color:var(--muted)"><?= e($log['created_at']) ?></td>
                <td>
                  <b><?= e($log['client_ip']) ?></b><br>
                  <span style="font-size:11px;color:var(--muted)"><?= e(mb_substr($log['user_agent'], 0, 35)) ?>...</span>
                </td>
                <td style="font-weight:600"><?= e($log['target_wa']) ?></td>
                <td><code style="font-weight:700"><?= e($log['secret_code']) ?></code></td>
                <td>
                  <span style="background:<?= $statusColor ?>15;color:<?= $statusColor ?>;padding:3px 8px;border-radius:6px;font-weight:700;font-size:11px">
                    <?= e($log['status']) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

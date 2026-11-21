<style>
.ai-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.ai-role{border-top:3px solid var(--primary)}
.ai-role-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:14px;flex-wrap:wrap}
.ai-role-head h3{margin:0;font-size:15px}
.ai-hint{font-size:12px;color:var(--muted);line-height:1.5}
.ai-status{font-size:11px;padding:3px 8px;border-radius:999px;background:var(--bg);border:1px solid var(--border);white-space:nowrap}
.ai-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.ai-message{padding:11px 14px;border-radius:9px;margin-bottom:14px;font-size:13px}
.ai-message.ok{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.ai-message.err{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
@media(max-width:860px){
  .ai-grid{grid-template-columns:1fr;gap:14px}
  .ai-actions button{width:100%;justify-content:center}
}
</style>
<div class="admin-wrap" style="max-width:1100px">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap">
    <div>
      <h2 style="margin:0;font-size:20px">🤖 AI Engine Settings</h2>
      <p class="ai-hint" style="margin:3px 0 0">Atur provider dan model untuk setiap tahap. API key disimpan terenkripsi.</p>
    </div>
    <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin">← Dashboard</a>
  </div>
  <?php if($msg): ?><div class="ai-message <?= $ok?'ok':'err' ?>"><?= e($msg) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="ai-grid">
    <?php foreach($aiConfig as $role=>$cfg):
      $descs=['writer'=>'Menyusun artikel utama dan SEO.','editor'=>'Merevisi alur, kualitas, dan gaya bahasa.','visual'=>'Membuat konsep serta prompt thumbnail.','image'=>'Menghasilkan file gambar melalui Images API.']; ?>
      <section class="admin-card ai-role">
        <div class="ai-role-head">
          <div>
            <h3><?= e($cfg['label']) ?></h3>
            <div class="ai-hint"><?= e($descs[$role]) ?></div>
          </div>
          <span class="ai-status"><?= $cfg['has_key']?'🔒 Key tersimpan':'Key kosong' ?></span>
        </div>
        <label class="label">Base URL</label>
        <input class="input" type="url" name="<?= $role ?>_base_url" value="<?= e($cfg['base_url']) ?>" placeholder="https://provider.example/v1">
        <label class="label" style="margin-top:10px">Model</label>
        <input class="input" name="<?= $role ?>_model" value="<?= e($cfg['model']) ?>" placeholder="Nama model dari provider">
        <label class="label" style="margin-top:10px">Timeout (detik)</label>
        <input class="input" type="number" min="30" max="1800" name="<?= $role ?>_timeout" value="<?= (int)$cfg['timeout'] ?>">
        <div class="ai-hint" style="margin-top:4px">Batas tunggu role ini, 30–1800 detik.</div>
        <?php if($role==='image'): ?>
          <label class="label" style="margin-top:10px">Protokol gambar</label>
          <select class="input" name="image_endpoint_mode">
            <option value="images" <?= ($cfg['endpoint_mode']??'images')==='images'?'selected':'' ?>>Images API — /images/generations</option>
            <option value="chat" <?= ($cfg['endpoint_mode']??'')==='chat'?'selected':'' ?>>Chat Completions — /chat/completions</option>
          </select>
          <div class="ai-hint" style="margin-top:4px">Gunakan Chat Completions untuk model multimodal yang mengembalikan gambar via respons chat.</div>
        <?php endif; ?>
        <label class="label" style="margin-top:10px">API Key</label>
        <input class="input" type="password" name="<?= $role ?>_api_key" value="" autocomplete="new-password" placeholder="<?= $cfg['has_key']?'Kosongkan untuk mempertahankan key':'Masukkan API key' ?>">
        <label style="display:flex;align-items:center;gap:7px;margin-top:8px;font-size:12px;color:var(--muted)">
          <input type="checkbox" name="<?= $role ?>_clear_key" value="1"> Hapus key tersimpan
        </label>
        <button class="btn btn-ghost" style="margin-top:12px;width:100%" type="submit" name="test_role" value="<?= $role ?>">⚡ Tes Koneksi Role Ini</button>
      </section>
    <?php endforeach; ?>
    </div>
    <div class="admin-card" style="margin-top:16px">
      <b style="font-size:14px">Pipeline Eksekusi AI</b>
      <p class="ai-hint" style="margin:4px 0 12px">Writer → Editor → Visual Director → Image Generator → simpan draft/publish. Role yang belum dikonfigurasi akan dilewati dengan aman.</p>
      <div class="ai-actions">
        <button class="btn btn-primary" type="submit" name="save_ai" value="1" style="padding:10px 22px;font-size:14px;font-weight:700">💾 Simpan Konfigurasi AI</button>
      </div>
    </div>
  </form>
</div>

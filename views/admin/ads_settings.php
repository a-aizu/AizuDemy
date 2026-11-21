<style>
.ads-core-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.ads-actions{display:flex;gap:10px;margin-top:8px;flex-wrap:wrap}
@media(max-width:640px){
  .ads-core-grid{grid-template-columns:1fr;gap:12px}
  .ads-actions{display:grid;grid-template-columns:1fr;gap:8px}
  .ads-actions .btn{width:100%;justify-content:center}
}
</style>

<div class="admin-wrap" style="max-width:960px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:10px;flex-wrap:wrap">
    <div>
      <h2 style="margin:0;font-size:20px">📢 Pengaturan Iklan & Google AdSense</h2>
      <div style="font-size:12.5px;color:var(--muted);margin-top:3px">Kelola Publisher ID, Google Auto Ads, dan unit slot iklan manual secara fleksibel.</div>
    </div>
    <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin">← Dashboard</a>
  </div>

  <?php if(!empty($msg)): ?>
    <div style="background:<?= $ok?'#dcfce7':'#fee' ?>;border:1px solid <?= $ok?'#bbf7d0':'#fecaca' ?>;color:<?= $ok?'#166534':'#dc2626' ?>;padding:12px 14px;border-radius:10px;font-size:13.5px;margin-bottom:16px;display:flex;align-items:center;gap:8px">
      <span><?= $ok?'✅':'⚠️' ?></span> <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="admin-card" style="display:flex;flex-direction:column;gap:20px;padding:20px">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <!-- SECTION 1: GOOGLE ADSENSE CORE -->
    <div>
      <h3 style="margin:0 0 4px;font-size:15px;color:var(--main);display:flex;align-items:center;gap:8px">
        <span>🌐</span> Akun Google AdSense
      </h3>
      <p style="font-size:12px;color:var(--muted);margin:0 0 14px">Identitas akun AdSense yang terdaftar di berkas <a href="<?= BASE_URL ?>/ads.txt" target="_blank" style="text-decoration:underline">ads.txt</a> dan header situs.</p>

      <div class="ads-core-grid">
        <div>
          <label class="label">Publisher ID (Client ID)</label>
          <input class="input" name="adsense_client_id" required value="<?= e($adSettings['adsense_client_id'] ?? 'ca-pub-1354033982953234') ?>" placeholder="ca-pub-1354033982953234">
          <small style="font-size:11px;color:var(--muted)">Contoh: <code>ca-pub-1354033982953234</code></small>
        </div>

        <div>
          <label class="label">Google Auto Ads (Otomatis)</label>
          <select class="input" name="adsense_auto_ads">
            <option value="1" <?= ($adSettings['adsense_auto_ads'] ?? '1')==='1'?'selected':'' ?>>Aktif (Rekomendasi Google)</option>
            <option value="0" <?= ($adSettings['adsense_auto_ads'] ?? '1')==='0'?'selected':'' ?>>Nonaktif (Hanya Slot Manual)</option>
          </select>
          <small style="font-size:11px;color:var(--muted)">Google menempatkan iklan otomatis dengan AI AdSense.</small>
        </div>
      </div>
    </div>

    <hr style="border:0;border-top:1px solid var(--border);margin:4px 0">

    <!-- SECTION 2: MANUAL AD SLOTS -->
    <div>
      <h3 style="margin:0 0 4px;font-size:15px;color:var(--main);display:flex;align-items:center;gap:8px">
        <span>📍</span> Slot Iklan Strategis (Manual Ad Slots)
      </h3>
      <p style="font-size:12px;color:var(--muted);margin:0 0 16px">Anda dapat memasukkan <b>Slot ID angka</b> (misal: <code>8492019384</code>) atau <b>Kode Script HTML Ad Unit Lengkap</b>.</p>

      <div style="display:flex;flex-direction:column;gap:16px">
        <!-- Slot 1: Post Top -->
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px">
          <label class="label" style="font-weight:700">1. Atas Artikel (Post Top Banner)</label>
          <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Tampil di bawah hero thumbnail dan di atas judul/paragraf awal artikel.</div>
          <textarea class="input" name="adsense_post_top" rows="3" style="font-family:monospace;font-size:12px" placeholder="Masukkan AdSense Slot ID angka atau Script HTML iklan..."><?= e($adSettings['adsense_post_top'] ?? '') ?></textarea>
        </div>

        <!-- Slot 2: In-Article -->
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px">
          <label class="label" style="font-weight:700">2. Tengah Artikel (In-Article Mid-Content)</label>
          <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Otomatis disisipkan di antara paragraf konten artikel (CTR & viewability tertinggi).</div>
          <textarea class="input" name="adsense_in_article" rows="3" style="font-family:monospace;font-size:12px" placeholder="Masukkan In-Article Slot ID atau script iklan..."><?= e($adSettings['adsense_in_article'] ?? '') ?></textarea>
        </div>

        <!-- Slot 3: Post Bottom -->
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px">
          <label class="label" style="font-weight:700">3. Bawah Artikel (Post Bottom Banner)</label>
          <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Tampil di akhir artikel sebelum tombol share dan artikel terkait.</div>
          <textarea class="input" name="adsense_post_bottom" rows="3" style="font-family:monospace;font-size:12px" placeholder="Masukkan Slot ID angka atau script iklan..."><?= e($adSettings['adsense_post_bottom'] ?? '') ?></textarea>
        </div>

        <!-- Slot 4: Sidebar Widget -->
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px">
          <label class="label" style="font-weight:700">4. Sidebar Kanan (Sidebar Widget Banner)</label>
          <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Tampil di sidebar desktop (posisi atas di atas widget kategori/populer).</div>
          <textarea class="input" name="adsense_sidebar" rows="3" style="font-family:monospace;font-size:12px" placeholder="Masukkan Display / Vertical Slot ID..."><?= e($adSettings['adsense_sidebar'] ?? '') ?></textarea>
        </div>

        <!-- Slot 5: Home Feed -->
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px">
          <label class="label" style="font-weight:700">5. Beranda (Home Feed Banner)</label>
          <div style="font-size:11.5px;color:var(--muted);margin-bottom:6px">Tampil di halaman beranda di bawah banner hero.</div>
          <textarea class="input" name="adsense_home_feed" rows="3" style="font-family:monospace;font-size:12px" placeholder="Masukkan Home Feed Slot ID atau script iklan..."><?= e($adSettings['adsense_home_feed'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="ads-actions" style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap">
      <button class="btn btn-primary" type="submit" style="padding:10px 22px;font-size:14px;font-weight:700">💾 Simpan Pengaturan Iklan</button>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/" target="_blank" style="padding:10px 18px">🌐 Cek Tampilan Live</a>
    </div>
  </form>
</div>

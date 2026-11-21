<div class="admin-wrap" style="max-width:1000px">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px">
    <div>
      <h2 style="margin:0">🔍 Pengaturan SEO & Meta Tags</h2>
      <div style="font-size:13px;color:var(--muted);margin-top:3px">Atur Judul Situs, Meta Deskripsi, Kata Kunci, Google Search Console, dan Google Analytics</div>
    </div>
  </div>

  <?php if(!empty($msg)): ?>
    <div style="padding:12px 16px;background:rgba(22,163,74,0.1);border:1px solid #16a34a;color:#16a34a;border-radius:8px;margin-bottom:18px;font-size:13px">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="admin-card" style="display:flex;flex-direction:column;gap:18px">
    <div style="border-bottom:1px solid var(--border);padding-bottom:14px">
      <h3 style="margin:0 0 4px">🌐 General SEO & Meta Tags Halaman Utama</h3>
      <div style="font-size:12px;color:var(--muted)">Pengaturan judul, deskripsi, dan keyword utama yang dibaca oleh Google & Bing</div>
    </div>

    <div class="form-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div>
        <label class="label" style="font-weight:700">Nama Situs (Site Name)</label>
        <input class="input form-input" name="seo_site_name" value="<?= e($seoSiteName) ?>" placeholder="AizuDemy">
      </div>
      <div>
        <label class="label" style="font-weight:700">Tagline / Slogan Situs</label>
        <input class="input form-input" name="seo_site_tagline" value="<?= e($seoSiteTagline) ?>" placeholder="Platform Belajar Digital — Tutorial, Teknologi & Pendidikan">
      </div>
    </div>

    <div>
      <label class="label" style="font-weight:700">Meta Description Halaman Utama (150-160 Karakter)</label>
      <textarea class="input form-input" name="seo_site_desc" rows="3" placeholder="Platform belajar digital gratis tentang vibe coding, cyber security, tutorial programming, dan teknologi..."><?= e($seoSiteDesc) ?></textarea>
      <div style="font-size:11px;color:var(--muted);margin-top:4px">Deskripsi ini akan muncul di hasil pencarian Google saat seseorang mencari nama blog Anda.</div>
    </div>

    <div>
      <label class="label" style="font-weight:700">Meta Keywords (Pisahkan dengan Koma)</label>
      <input class="input form-input" name="seo_site_keywords" value="<?= e($seoSiteKeywords) ?>" placeholder="vibe coding, cyber security, tutorial php, belajar hacking, aizudemy">
    </div>

    <div class="form-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div>
        <label class="label" style="font-weight:700">Author / Nama Penulis Default</label>
        <input class="input form-input" name="seo_site_author" value="<?= e($seoSiteAuthor) ?>" placeholder="Aizu Dev">
      </div>
      <div>
        <label class="label" style="font-weight:700">Default OpenGraph Image URL (Social Share)</label>
        <input class="input form-input" name="seo_site_og_image" value="<?= e($seoSiteOgImage) ?>" placeholder="https://aizudemy.net/assets/images/og-default.png">
      </div>
    </div>

    <div style="border-bottom:1px solid var(--border);padding-bottom:14px;margin-top:10px">
      <h3 style="margin:0 0 4px">🔑 Search Console & Verification Meta Tags</h3>
      <div style="font-size:12px;color:var(--muted)">Verifikasi kepemilikan situs untuk Google Search Console, Bing Webmaster, dan Google Analytics</div>
    </div>

    <div class="form-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div>
        <label class="label" style="font-weight:700">Google Site Verification Code (Search Console)</label>
        <input class="input form-input" name="seo_google_verification" value="<?= e($seoGoogleVerification) ?>" placeholder="google-site-verification=abc123xyz...">
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Isi dengan nilai konten verifikasi (misal: abc123xyz...) dari Google Search Console.</div>
      </div>
      <div>
        <label class="label" style="font-weight:700">Bing Site Verification Code (Webmaster)</label>
        <input class="input form-input" name="seo_bing_verification" value="<?= e($seoBingVerification) ?>" placeholder="msvalidate.01=...">
      </div>
    </div>

    <div class="form-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div>
        <label class="label" style="font-weight:700">Google Analytics 4 (GA4) Measurement ID</label>
        <input class="input form-input" name="seo_ga_id" value="<?= e($seoGaId) ?>" placeholder="G-XXXXXXXXXX">
        <div style="font-size:11px;color:var(--muted);margin-top:4px">Isi dengan Measurement ID Google Analytics 4 (misal: G-ABC123456).</div>
      </div>
      <div>
        <label class="label" style="font-weight:700">Robots Meta Directive Default</label>
        <select class="input form-input" name="seo_robots_directive">
          <option value="index, follow" <?= $seoRobotsDirective === 'index, follow' ? 'selected' : '' ?>>✅ index, follow (Diizinkan Diindeks Google & Bing)</option>
          <option value="noindex, nofollow" <?= $seoRobotsDirective === 'noindex, nofollow' ? 'selected' : '' ?>>🚫 noindex, nofollow (Sembunyikan dari Mesin Pencari)</option>
        </select>
      </div>
    </div>

    <div style="display:flex;gap:12px;margin-top:10px">
      <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:14px">💾 Simpan Pengaturan SEO</button>
    </div>
  </form>
</div>

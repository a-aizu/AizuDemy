<div class="post">
  <div class="post-head">
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/">Beranda</a> <span>›</span> <span>Sitemap</span></div>
    <h1 class="post-title">Sitemap</h1>
    <div class="post-meta"><span class="avatar">🗺️</span><span>Daftar lengkap halaman AizuDemy · <a href="<?= BASE_URL ?>/sitemap.xml">XML Sitemap</a></span></div>
  </div>
  <div class="post-body">
    <h2>📄 Halaman Statis</h2>
    <ul>
      <li><a href="<?= BASE_URL ?>/tentang-kami">Tentang Kami</a></li>
      <li><a href="<?= BASE_URL ?>/hubungi-kami">Hubungi Kami</a></li>
      <li><a href="<?= BASE_URL ?>/privacy-policy">Privacy Policy</a></li>
      <li><a href="<?= BASE_URL ?>/disclaimer">Disclaimer</a></li>
      <li><a href="<?= BASE_URL ?>/sitemap">Sitemap (halaman ini)</a> · <a href="<?= BASE_URL ?>/sitemap.xml">sitemap.xml</a></li>
    </ul>
    <h2>📚 Kategori (<?= count(all_categories()) ?>)</h2>
    <ul>
      <?php foreach(all_categories() as $c): ?>
        <li><a href="<?= BASE_URL ?>/category/<?= e($c["slug"]) ?>"><?= e($c["name"]) ?></a> <span style="color:var(--muted)">— <?= e($c["description"] ?: "Artikel ".["name"]) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <h2>📝 Artikel Terbaru (<?= (int)db()->query("SELECT COUNT(*) FROM posts WHERE status=\"publish\"")->fetchColumn() ?>)</h2>
    <ul>
      <?php foreach(db()->query("SELECT title, slug, created_at FROM posts WHERE status=\"publish\" ORDER BY created_at DESC")->fetchAll() as $p): ?>
        <li><a href="<?= BASE_URL ?>/post/<?= e($p["slug"]) ?>"><?= e($p["title"]) ?></a> <span style="color:var(--muted);font-size:12px">— <?= date("d M Y", strtotime($p["created_at"])) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

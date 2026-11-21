<div class="post">
  <div class="post-head">
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/">Beranda</a> <span>›</span> <span>Sitemap</span></div>
    <h1 class="post-title">Daftar Isi Situs (HTML Sitemap)</h1>
    <div class="post-meta"><span class="avatar">🗺️</span><span>Navigasi lengkap halaman & artikel <b>aizudemy.net</b> · <a href="<?= BASE_URL ?>/sitemap.xml" target="_blank">XML Sitemap</a></span></div>
  </div>
  <div class="post-body">
    <h2>📄 Halaman Utama & Legalitas</h2>
    <ul>
      <li><a href="<?= BASE_URL ?>/">Beranda (Home)</a></li>
      <li><a href="<?= BASE_URL ?>/about">Tentang Kami (About Us)</a></li>
      <li><a href="<?= BASE_URL ?>/contact">Hubungi Kami (Contact Us)</a></li>
      <li><a href="<?= BASE_URL ?>/privacy">Kebijakan Privasi (Privacy Policy)</a></li>
      <li><a href="<?= BASE_URL ?>/syarat-ketentuan">Syarat & Ketentuan Layanan (Terms of Service)</a></li>
      <li><a href="<?= BASE_URL ?>/disclaimer">Penafian (Disclaimer)</a></li>
      <li><a href="<?= BASE_URL ?>/sitemap">Sitemap HTML (Halaman ini)</a> · <a href="<?= BASE_URL ?>/sitemap.xml" target="_blank">Sitemap XML Mesin Pencari</a></li>
    </ul>

    <h2>📚 Kategori Pembahasan</h2>
    <ul>
      <?php foreach(all_categories() as $c): 
        $cnt = (int)db()->prepare("SELECT COUNT(*) FROM posts WHERE category_id=? AND status='publish'")->execute([$c['id']]) ? db()->prepare("SELECT COUNT(*) FROM posts WHERE category_id=? AND status='publish'") : 0;
        $qCnt = db()->prepare("SELECT COUNT(*) FROM posts WHERE category_id=? AND status='publish'");
        $qCnt->execute([$c['id']]);
        $artCount = (int)$qCnt->fetchColumn();
        if ($artCount === 0) continue;
      ?>
        <li><a href="<?= BASE_URL ?>/category/<?= e($c["slug"]) ?>"><b><?= e($c["name"]) ?></b></a> <span style="color:var(--muted)">— <?= e($c["description"] ?: "Kumpulan artikel ".e($c["name"])) ?> (<?= $artCount ?> artikel)</span></li>
      <?php endforeach; ?>
    </ul>

    <h2>📝 Indeks Artikel Terbit (<?= (int)db()->query("SELECT COUNT(*) FROM posts WHERE status='publish'")->fetchColumn() ?>)</h2>
    <ul style="line-height:1.8">
      <?php foreach(db()->query("SELECT title, slug, created_at, category_id FROM posts WHERE status='publish' ORDER BY created_at DESC")->fetchAll() as $p): ?>
        <li><a href="<?= BASE_URL ?>/post/<?= e($p["slug"]) ?>"><?= e($p["title"]) ?></a> <span style="color:var(--muted);font-size:12px">— <?= date("d M Y", strtotime($p["created_at"])) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

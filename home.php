<?php
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 9;
$total = count_posts();
$posts = latest_posts($per, ($page-1)*$per);
$popular = popular_posts(5);
$totalPages = max(1, ceil($total/$per));
?>
<div class="hero">
  <h1>Belajar Tanpa Batas di AizuDemy</h1>
  <p>Tutorial praktis, insight teknologi, dan materi pendidikan — dirangkum ringkas, mudah dipahami, dan siap kamu praktikkan hari ini.</p>
  <a class="btn" href="<?= BASE_URL ?>/category/tutorial">Mulai Belajar →</a>
</div>

<div class="layout">
  <div class="content">
    <?php if(empty($posts)): ?>
      <div class="widget">Belum ada artikel. Silakan tambah dari Admin.</div>
    <?php else: ?>
      <div class="grid">
        <?php $idx=0; foreach($posts as $p): ?>
          <article class="card">
            <div class="thumb">
              <?php $thumb = !empty($p['thumbnail']) ? $p['thumbnail'] : "https://picsum.photos/seed/".e($p['slug'])."/600/340"; ?><img src="<?= e($thumb) ?>" alt="<?= e($p['title']) ?>" <?= $idx < 2 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
              <?php if($p['cat_name']): ?><span class="cat">#<?= e($p['cat_name']) ?></span><?php endif; ?>
            </div>
            <div class="body">
              <div class="title"><a href="<?= BASE_URL ?>/post/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></div>
              <div class="excerpt"><?= e($p['excerpt'] ?: mb_substr(strip_tags($p['content']),0,120).'…') ?></div>
              <div class="meta">
                <span class="author"><?= e($p['author']) ?></span>
                <span><?= e(time_ago($p['created_at'])) ?> <span class="dot"></span> <?= (int)$p['views'] ?> views</span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if($totalPages>1): ?>
        <div class="pager">
          <?php if($page>1): ?><a href="?page=<?= $page-1 ?>">‹ Prev</a><?php else: ?><span class="disabled">‹ Prev</span><?php endif; ?>
          <?php for($i=1;$i<=$totalPages;$i++): if($i==$page) echo '<span class="active">'.$i.'</span>'; else echo '<a href="?page='.$i.'">'.$i.'</a>'; endfor; ?>
          <?php if($page<$totalPages): ?><a href="?page=<?= $page+1 ?>">Next ›</a><?php else: ?><span class="disabled">Next ›</span><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <aside class="sidebar">
    <div class="widget">
      <h3>🔥 Populer</h3>
      <?php foreach($popular as $i=>$pop): ?>
        <div class="pop-item"><div class="n"><?= $i+1 ?></div><a href="<?= BASE_URL ?>/post/<?= e($pop['slug']) ?>"><?= e($pop['title']) ?></a></div>
      <?php endforeach; if(empty($popular)) echo '<div style="font-size:13px;color:var(--muted)">Belum ada data.</div>'; ?>
    </div>
    <div class="widget">
      <h3>📂 Kategori</h3>
      <ul>
        <?php foreach(all_categories() as $c): $cnt = count_posts($c['id']); ?>
          <li><a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a> <span class="count">(<?= $cnt ?>)</span></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </aside>
</div>

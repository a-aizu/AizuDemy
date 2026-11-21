<?php
$q = trim($_GET['q'] ?? ''); $page=max(1,(int)($_GET['page']??1)); $per=9;
$total = $q ? count_posts(null,$q) : 0;
$posts = $q ? latest_posts($per, ($page-1)*$per, null, $q) : [];
$totalPages = max(1, ceil($total/$per));
?>
<h2 style="margin-bottom:6px">🔍 Pencarian</h2>
<form method="get" action="<?= BASE_URL ?>/search" style="display:flex;gap:10px;margin-bottom:16px">
  <input class="input" type="text" name="q" value="<?= e($q) ?>" placeholder="Kata kunci...">
  <button class="btn btn-primary" type="submit">Cari</button>
</form>
<?php if($q===''): ?><div class="widget">Ketik kata kunci untuk mencari artikel.</div>
<?php elseif(empty($posts)): ?><div class="widget">Tidak ada hasil untuk “<?= e($q) ?>”.</div>
<?php else: ?>
<p style="color:var(--muted);margin:0 0 12px">Ditemukan <?= $total ?> hasil untuk “<?= e($q) ?>”</p>
<div class="grid">
<?php foreach($posts as $p): ?>
<article class="card"><div class="thumb"><?php $thumb = !empty($p['thumbnail']) ? $p['thumbnail'] : BASE_URL."/assets/images/default-thumb.svg"; ?><img src="<?= e($thumb) ?>"><span class="cat">#<?= e($p['cat_name'] ?? 'Umum') ?></span></div><div class="body"><div class="title"><a href="<?= BASE_URL ?>/post/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></div><div class="excerpt"><?= e($p['excerpt'] ?: mb_substr(strip_tags($p['content']),0,120).'…') ?></div><div class="meta"><span><?= e(time_ago($p['created_at'])) ?></span></div></div></article>
<?php endforeach; ?>
</div>
<?php if($totalPages>1): ?><div class="pager"><?php if($page>1): ?><a href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>">‹ Prev</a><?php else: ?><span class="disabled">‹ Prev</span><?php endif; ?><?php for($i=1;$i<=$totalPages;$i++): echo $i==$page ? '<span class="active">'.$i.'</span>' : '<a href="?page='.$i.'&q='.urlencode($q).'">'.$i.'</a>'; endfor; ?><?php if($page<$totalPages): ?><a href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>">Next ›</a><?php else: ?><span class="disabled">Next ›</span><?php endif; ?></div><?php endif; ?>
<?php endif; ?>

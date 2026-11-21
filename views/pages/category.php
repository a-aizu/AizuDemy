<?php
$cat = $cat ?? null;
if(!$cat){ echo "<div class='widget' style='text-align:center;padding:40px 20px;'><h3>Kategori tidak ditemukan</h3><p><a href='".BASE_URL."/'>Kembali ke beranda</a></p></div>"; return; }
$page = max(1,(int)($_GET['page']??1)); $per = is_mobile_device() ? 8 : 9;
$total = count_posts($cat['id']); $posts = latest_posts($per, ($page-1)*$per, $cat['id']);
$totalPages = max(1, ceil($total/$per));
?>
<h2 style="margin-bottom:6px">📚 <?= e($cat['name']) ?></h2>
<p style="color:var(--muted);margin:0 0 16px"><?= e($cat['description']) ?> — <?= $total ?> artikel</p>
<?php if(empty($posts)): ?>
  <div class="widget" style="text-align:center;padding:40px 20px;">
    <h3 style="margin-top:0">Belum ada artikel di kategori ini</h3>
    <p style="color:var(--muted);margin-bottom:16px">Silakan jelajahi kategori lainnya atau kembali ke beranda.</p>
    <a href="<?= BASE_URL ?>/" class="btn btn-primary">Kembali ke Beranda</a>
  </div>
<?php else: ?>
<div class="grid">
<?php foreach($posts as $p): ?>
  <article class="card">
    <?php $thumb = !empty($p['thumbnail']) ? $p['thumbnail'] : BASE_URL . "/public/images/default-thumb.svg"; ?><div class="thumb"><img src="<?= e($thumb) ?>" alt="<?= e($p['title']) ?>" loading="lazy"><span class="cat">#<?= e($p['cat_name']) ?></span></div>
    <div class="body"><div class="title"><a href="<?= BASE_URL ?>/post/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></div><div class="excerpt"><?= e($p['excerpt'] ?: mb_substr(strip_tags($p['content']),0,120).'…') ?></div><div class="meta"><span><?= e(time_ago($p['created_at'])) ?></span><span><?= (int)$p['views'] ?> views</span></div></div>
  </article>
<?php endforeach; ?>
</div>
<?php if($totalPages>1): ?><div class="pager"><?php if($page>1): ?><a href="?page=<?= $page-1 ?>">‹ Prev</a><?php else: ?><span class="disabled">‹ Prev</span><?php endif; ?><?php for($i=1;$i<=$totalPages;$i++): echo $i==$page ? '<span class="active">'.$i.'</span>' : '<a href="?page='.$i.'">'.$i.'</a>'; endfor; ?><?php if($page<$totalPages): ?><a href="?page=<?= $page+1 ?>">Next ›</a><?php else: ?><span class="disabled">Next ›</span><?php endif; ?></div><?php endif; ?>
<?php endif; ?>

<?php
$post = $post ?? null;
if(!$post){ http_response_code(404); echo "<div class='widget'><h3>404 — Artikel tidak ditemukan</h3><p><a href='".BASE_URL."/'>Kembali ke beranda</a></p></div>"; return; }
db()->prepare("UPDATE posts SET views=views+1 WHERE id=?")->execute([$post['id']]);
$related = related_posts($post['category_id'], $post['id'], 3);
?>
<div class="layout">
  <div class="content">
    <article class="post">
      <div class="post-head">
        <div class="breadcrumb"><a href="<?= BASE_URL ?>/">Beranda</a> › <?php if($post['cat_name']): ?><a href="<?= BASE_URL ?>/category/<?= e($post['cat_slug']) ?>"><?= e($post['cat_name']) ?></a> › <?php endif; ?><span><?= e($post['title']) ?></span></div>
        <h1 class="post-title"><?= e($post['title']) ?></h1>
        <div class="post-meta">
          <div class="avatar"><?= e(mb_substr($post['author'],0,1)) ?></div>
          <div><div style="font-weight:700;color:var(--main)"><?= e($post['author']) ?></div><div><?= e(time_ago($post['created_at'])) ?> · <?= (int)$post['views']+1 ?> views</div></div>
          <?php if($post['cat_name']): ?><span class="badge" style="margin-left:auto">#<?= e($post['cat_name']) ?></span><?php endif; ?>
        </div>
      </div>
      <div class="post-body">
        <?php if(!empty($post['thumbnail'])): ?>
          <figure class="post-hero">
            <img src="<?= e($post['thumbnail']) ?>" alt="<?= e($post['title']) ?>" width="1200" height="675" fetchpriority="high">
          </figure>
        <?php endif; ?>

        <?php $adTop = setting('adsense_post_top', ''); if($adTop !== ''): ?>
          <div class="adsense-slot adsense-post-top" style="margin:16px 0;text-align:center;overflow:hidden">
            <?= $adTop ?>
          </div>
        <?php endif; ?>

        <?= $post['content'] ?>

        <?php $adBottom = setting('adsense_post_bottom', ''); if($adBottom !== ''): ?>
          <div class="adsense-slot adsense-post-bottom" style="margin:20px 0;text-align:center;overflow:hidden">
            <?= $adBottom ?>
          </div>
        <?php endif; ?>

        <div class="share">
          <a class="fb" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL.'/post/'.$post['slug']) ?>">Facebook</a>
          <a class="tw" target="_blank" href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL.'/post/'.$post['slug']) ?>&text=<?= urlencode($post['title']) ?>">Twitter</a>
          <a class="wa" target="_blank" href="https://wa.me/?text=<?= urlencode($post['title'].' — '.BASE_URL.'/post/'.$post['slug']) ?>">WhatsApp</a>
        </div>
      </div>
    </article>

    <?php if($related): ?>
      <div style="margin-top:20px">
        <h3 style="margin-bottom:12px">📖 Artikel Terkait</h3>
        <div class="grid">
          <?php foreach($related as $r): ?>
            <article class="card">
              <?php $rthumb = !empty($r['thumbnail']) ? $r['thumbnail'] : BASE_URL."/assets/images/default-thumb.svg"; ?><div class="thumb"><img src="<?= e($rthumb) ?>" alt="<?= e($r['title']) ?>" loading="lazy"></div>
              <div class="body"><div class="title"><a href="<?= BASE_URL ?>/post/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></div><div class="meta"><span><?= e(time_ago($r['created_at'])) ?></span></div></div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <aside class="sidebar">
    <?php $adSidebar = setting('adsense_sidebar', ''); if($adSidebar !== ''): ?>
      <div class="widget adsense-widget" style="text-align:center;overflow:hidden">
        <h3>📢 Iklan</h3>
        <?= $adSidebar ?>
      </div>
    <?php endif; ?>
    <div class="widget"><h3>📂 Kategori</h3><ul><?php foreach(all_categories() as $c): ?><li><a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?></ul></div>
    <div class="widget"><h3>🔥 Populer</h3><?php foreach(popular_posts(5) as $i=>$pop): ?><div class="pop-item"><div class="n"><?= $i+1 ?></div><a href="<?= BASE_URL ?>/post/<?= e($pop['slug']) ?>"><?= e($pop['title']) ?></a></div><?php endforeach; ?></div>
  </aside>
</div>

<?php
// RSS Feed AizuDemy
header('Content-Type: application/rss+xml; charset=utf-8');
// db() sudah tersedia dari index.php

$pdo = db();
$stmt = $pdo->query("SELECT p.title, p.slug, p.content, p.thumbnail, p.created_at, p.author, c.name as cat_name
    FROM posts p LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status = 'publish' ORDER BY p.created_at DESC LIMIT 20");
$posts = $stmt->fetchAll();

$base = rtrim(BASE_URL, '/');
$now = date(DATE_RSS);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
  <title>AizuDemy — Platform belajar digital</title>
  <link><?= htmlspecialchars($base) ?></link>
  <description>Tutorial, Teknologi &amp; Pendidikan — artikel terbaru dari AizuDemy</description>
  <language>id</language>
  <lastBuildDate><?= $now ?></lastBuildDate>
  <atom:link href="<?= htmlspecialchars($base) ?>/feed" rel="self" type="application/rss+xml" />
  <?php foreach($posts as $p): ?>
  <item>
    <title><?= htmlspecialchars($p['title']) ?></title>
    <link><?= htmlspecialchars($base . '/post/' . $p['slug']) ?></link>
    <guid isPermaLink="true"><?= htmlspecialchars($base . '/post/' . $p['slug']) ?></guid>
    <description><![CDATA[<?= mb_substr(strip_tags($p['content']), 0, 500) ?>...]]></description>
    <?php if(!empty($p['thumbnail'])): ?><enclosure url="<?= htmlspecialchars($p['thumbnail']) ?>" type="image/jpeg" /><?php endif; ?>
    <?php if(!empty($p['cat_name'])): ?><category><?= htmlspecialchars($p['cat_name']) ?></category><?php endif; ?>
    <author><?= htmlspecialchars($p['author'] ?? 'AizuDemy') ?></author>
    <pubDate><?= date(DATE_RSS, strtotime($p['created_at'])) ?></pubDate>
  </item>
  <?php endforeach; ?>
</channel>
</rss>

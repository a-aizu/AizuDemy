<?php
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = is_mobile_device() ? 8 : 9;
$total = count_posts();
$posts = latest_posts($per, ($page-1)*$per);
$popular = popular_posts(5);
$totalPages = max(1, ceil($total/$per));
?>
<div class="hero">
  <h1>Belajar Tanpa Batas di AizuDemy</h1>
  <p>Tutorial praktis, insight teknologi, dan materi pendidikan — dirangkum ringkas, mudah dipahami, dan siap kamu praktikkan hari ini.</p>
  <button type="button" class="btn btn-hero-start" onclick="openLearningModal()">
    <span>🚀</span> Mulai Belajar →
  </button>
</div>

<!-- MODAL INTERAKTIF: PILIH TOPIK BELAJAR -->
<div id="learningModal" class="learn-modal-overlay" style="display:none" onclick="if(event.target===this) closeLearningModal()">
  <div class="learn-modal-dialog">
    <div class="learn-modal-handle"></div>
    <div class="learn-modal-header">
      <div>
        <h3 class="learn-modal-title">🎯 Mau Belajar Apa Hari Ini?</h3>
        <p class="learn-modal-subtitle">Pilih topik atau teknologi yang ingin kamu kuasai:</p>
      </div>
      <button type="button" class="learn-modal-close" onclick="closeLearningModal()" aria-label="Tutup">✕</button>
    </div>

    <!-- Quick Search in Modal -->
    <form class="learn-modal-search" action="<?= BASE_URL ?>/search" method="get">
      <span class="search-icon">🔍</span>
      <input type="text" name="q" placeholder="Cari topik spesifik... (Python, AI, HTML, VPS, Docker)">
      <button type="submit" class="btn btn-primary" style="padding:6px 14px;font-size:12px">Cari</button>
    </form>

    <!-- Grid of Learning Tracks -->
    <div class="learn-tracks-grid">
      <!-- Track 1: AI & Intelligent Agents -->
      <a href="<?= BASE_URL ?>/category/kecerdasan-buatan" class="learn-track-card">
        <div class="track-icon" style="background:rgba(0,90,240,0.1);color:#005af0">🤖</div>
        <div class="track-info">
          <h4>Kecerdasan Buatan & AI</h4>
          <p>AI Agent, LangGraph, Ollama, Prompt Engineering</p>
        </div>
        <div class="track-arrow">›</div>
      </a>

      <!-- Track 2: Python & Backend -->
      <a href="<?= BASE_URL ?>/search?q=Python" class="learn-track-card">
        <div class="track-icon" style="background:rgba(234,179,8,0.12);color:#eab308">🐍</div>
        <div class="track-info">
          <h4>Python & Backend</h4>
          <p>Python modern, FastAPI, REST API, automasi</p>
        </div>
        <div class="track-arrow">›</div>
      </a>

      <!-- Track 3: Web Development -->
      <a href="<?= BASE_URL ?>/category/web-development" class="learn-track-card">
        <div class="track-icon" style="background:rgba(239,68,68,0.1);color:#ef4444">🌐</div>
        <div class="track-info">
          <h4>Web Development</h4>
          <p>HTML5, CSS3, PHP, MySQLi, REST API modern</p>
        </div>
        <div class="track-arrow">›</div>
      </a>

      <!-- Track 4: Cyber Security & Linux -->
      <a href="<?= BASE_URL ?>/category/cyber-security" class="learn-track-card">
        <div class="track-icon" style="background:rgba(16,185,129,0.1);color:#10b981">🛡️</div>
        <div class="track-info">
          <h4>Cyber Security & Hardening</h4>
          <p>Hardening VPS Linux, audit API, ethical security</p>
        </div>
        <div class="track-arrow">›</div>
      </a>

      <!-- Track 5: Vibe Coding & AI Tools -->
      <a href="<?= BASE_URL ?>/search?q=Vibe+Coding" class="learn-track-card">
        <div class="track-icon" style="background:rgba(168,85,247,0.12);color:#a855f7">💡</div>
        <div class="track-info">
          <h4>Vibe Coding & AI Tools</h4>
          <p>AI coding, Cursor workflow, arsitektur masa kini</p>
        </div>
        <div class="track-arrow">›</div>
      </a>

      <!-- Track 6: Server & DevOps -->
      <a href="<?= BASE_URL ?>/category/tutorial" class="learn-track-card">
        <div class="track-icon" style="background:rgba(6,182,212,0.1);color:#06b6d4">☁️</div>
        <div class="track-info">
          <h4>Server, Cloud & DevOps</h4>
          <p>Panduan VPS, Docker, Podman, Cloudflare Tunnel</p>
        </div>
        <div class="track-arrow">›</div>
      </a>
    </div>

    <!-- Quick keyword chips -->
    <div class="learn-modal-footer">
      <div class="quick-title">⚡ Topik Populer:</div>
      <div class="quick-chips">
        <a href="<?= BASE_URL ?>/search?q=Python">Python</a>
        <a href="<?= BASE_URL ?>/search?q=FastAPI">FastAPI</a>
        <a href="<?= BASE_URL ?>/search?q=Docker">Docker</a>
        <a href="<?= BASE_URL ?>/category/kecerdasan-buatan">AI Agent</a>
        <a href="<?= BASE_URL ?>/category/cyber-security">Cyber Security</a>
        <a href="<?= BASE_URL ?>/category/web-development">Web Dev</a>
        <a href="<?= BASE_URL ?>/category/tutorial">Server & VPS</a>
        <a href="<?= BASE_URL ?>/search?q=Vibe+Coding">Vibe Coding</a>
      </div>
    </div>
  </div>
</div>

<script>
function openLearningModal() {
  var modal = document.getElementById('learningModal');
  if(modal) {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    var inp = modal.querySelector('input[name="q"]');
    if(inp) setTimeout(() => inp.focus(), 150);
  }
}
function closeLearningModal() {
  var modal = document.getElementById('learningModal');
  if(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeLearningModal();
});
</script>

<?= render_ad('home_feed', 'my-4') ?>

<div class="layout">
  <div class="content">
    <?php if(empty($posts)): ?>
      <div class="widget" style="text-align:center;padding:40px 20px;">Belum ada artikel. Silakan tambah dari Admin.</div>
    <?php else: ?>
      <div class="grid">
        <?php foreach($posts as $p): ?>
          <article class="card">
            <div class="thumb">
              <?php $thumb = !empty($p['thumbnail']) ? $p['thumbnail'] : BASE_URL . "/public/images/default-thumb.svg"; ?><img src="<?= e($thumb) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
              <?php if(!empty($p['cat_name'])): ?><span class="cat">#<?= e($p['cat_name']) ?></span><?php endif; ?>
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
    <?= render_ad('sidebar', 'mb-4') ?>
    <div class="widget">
      <h3>🔥 Populer</h3>
      <?php foreach($popular as $i=>$pop): ?>
        <div class="pop-item"><div class="n"><?= $i+1 ?></div><a href="<?= BASE_URL ?>/post/<?= e($pop['slug']) ?>"><?= e($pop['title']) ?></a></div>
      <?php endforeach; if(empty($popular)) echo '<div style="font-size:13px;color:var(--muted)">Belum ada data.</div>'; ?>
    </div>
    <div class="widget">
      <h3>📂 Kategori</h3>
      <ul>
        <?php foreach(all_categories() as $c): 
          $cnt = count_posts($c['id']); 
          if ($cnt === 0) continue;
        ?>
          <li><a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a> <span class="count">(<?= $cnt ?>)</span></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </aside>
</div>

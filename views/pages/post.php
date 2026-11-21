<?php
$post = $post ?? null;
if(!$post){ http_response_code(404); echo "<div class='widget'><h3>404 — Artikel tidak ditemukan</h3><p><a href='".BASE_URL."/'>Kembali ke beranda</a></p></div>"; return; }
track_post_view((int)$post['id']);
$related = related_posts($post['category_id'], $post['id'], 3);
$readingTime = estimated_reading_time($post['content']);
$tocData = generate_toc_and_anchors($post['content']);
$tldrList = extract_tldr_takeaways($post['content'], $post['excerpt'] ?? '');
?>
<!-- READING PROGRESS BAR -->
<div id="readingProgressBar" class="reading-progress-bar"></div>

<div class="layout">
  <div class="content">
    <article class="post">
      <div class="post-head">
        <div class="breadcrumb"><a href="<?= BASE_URL ?>/">Beranda</a> › <?php if($post['cat_name']): ?><a href="<?= BASE_URL ?>/category/<?= e($post['cat_slug']) ?>"><?= e($post['cat_name']) ?></a> › <?php endif; ?><span><?= e($post['title']) ?></span></div>
        <h1 class="post-title"><?= e($post['title']) ?></h1>
        <div class="post-meta">
          <div>
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
              <span><?= e(time_ago($post['created_at'])) ?></span>
            <?php if(!empty($post['updated_at']) && $post['updated_at'] !== $post['created_at']): ?>
              <span class="dot"></span>
              <span>diperbarui <?= e(time_ago($post['updated_at'])) ?></span>
            <?php endif; ?>
              <span class="dot"></span>
              <span><?= (int)$post['views']+1 ?> views</span>
              <span class="dot"></span>
              <span style="color:var(--primary);font-weight:600">⏱️ <?= $readingTime ?></span>
            </div>
          </div>
          <?php if($post['cat_name']): ?><a href="<?= BASE_URL ?>/category/<?= e($post['cat_slug']) ?>" class="badge" style="margin-left:auto;text-decoration:none">#<?= e($post['cat_name']) ?></a><?php endif; ?>
        </div>
      </div>
      <div class="post-body">
        <?php if(!empty($post['thumbnail'])): ?>
          <figure class="post-hero">
            <img src="<?= e($post['thumbnail']) ?>" alt="<?= e($post['title']) ?>" width="1200" height="675" fetchpriority="high">
          </figure>
        <?php endif; ?>
        
        <?= render_ad('post_top', 'my-3') ?>

        <!-- 🎧 AI AUDIO ARTICLE NARRATOR -->
        <div class="post-audio-card" id="audioNarratorCard">
          <div class="audio-info">
            <div class="audio-icon-wrap">🎧</div>
            <div class="audio-text">
              <div class="audio-title">
                <span>Dengarkan Artikel Ini</span>
                <span class="audio-waveform" id="audioWaveform">
                  <span></span><span></span><span></span><span></span>
                </span>
              </div>
              <div class="audio-subtitle" id="audioStatusText">Suara AI Otomatis • <?= $readingTime ?> baca</div>
            </div>
          </div>
          <div class="audio-controls">
            <button type="button" class="audio-speed-btn" id="audioSpeedBtn" title="Atur Kecepatan">1.0x</button>
            <button type="button" class="audio-play-btn" id="audioPlayBtn">
              <span id="audioPlayIcon">▶</span> <span id="audioPlayText">Putar</span>
            </button>
          </div>
        </div>

        <?php if(!empty($tldrList)): ?>
          <!-- ⚡ TL;DR KEY TAKEAWAYS -->
          <div class="post-tldr-card">
            <div class="post-tldr-head">
              <span class="post-tldr-badge">⚡ TL;DR</span>
              <h4 class="post-tldr-title">Poin Kunci Artikel Ini:</h4>
            </div>
            <ul class="post-tldr-list">
              <?php foreach($tldrList as $bullet): ?>
                <li><?= e($bullet) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if(!empty($tocData['toc'])): ?>
          <?= $tocData['toc'] ?>
        <?php endif; ?>

        <?= inject_in_article_ad($tocData['content'], render_ad('in_article', 'my-4')) ?>

        <?= render_ad('post_bottom', 'my-4') ?>

        <div class="share">
          <a class="fb" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL.'/post/'.$post['slug']) ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            Facebook</a>
          <a class="tw" target="_blank" href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL.'/post/'.$post['slug']) ?>&text=<?= urlencode($post['title']) ?>">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            X</a>
          <a class="wa" target="_blank" href="https://wa.me/?text=<?= urlencode($post['title'].' — '.BASE_URL.'/post/'.$post['slug']) ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            WhatsApp</a>
        </div>
      </div>

        <!-- AUTHOR BOX -->
        <div class="author-box" style="margin-top:28px;padding:20px;border:1px solid var(--border);border-radius:12px;background:var(--card);display:flex;gap:16px;align-items:flex-start">
          <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#FF8C00);display:flex;align-items:center;justify-content:center;color:#1a1a1a;font-weight:700;font-size:24px"><?= e(mb_substr($post['author'],0,1)) ?></div>
          <div style="flex:1">
            <div style="font-weight:700;color:var(--main);font-size:15px"><?= e($post['author']) ?></div>
            <p style="margin:6px 0 0;color:var(--muted);font-size:13px;line-height:1.6">Tim penulis AizuDemy yang menyajikan tutorial teknologi, cloud, dan pengembangan perangkat lunak dalam Bahasa Indonesia.</p>
          </div>
        </div>
        <!-- /AUTHOR BOX -->
        <!-- Schema.org BlogPosting -->
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BlogPosting",
          "headline": <?= json_encode($post['title'], JSON_UNESCAPED_UNICODE) ?>,
          "description": <?= json_encode(strip_tags($post['excerpt'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
          "image": <?= json_encode(!empty($post['thumbnail']) && str_starts_with($post['thumbnail'],'http') ? $post['thumbnail'] : rtrim(BASE_URL,"/") . "/assets/images/default-thumb.svg", JSON_UNESCAPED_UNICODE) ?>,
          "author": {
            "@type": "Organization",
            "name": "AizuDemy",
            "url": <?= json_encode(BASE_URL, JSON_UNESCAPED_UNICODE) ?>
          },
          "publisher": {
            "@type": "Organization",
            "name": "AizuDemy",
            "logo": {
              "@type": "ImageObject",
              "url": <?= json_encode(rtrim(BASE_URL,"/")."/assets/images/watermark_badge.png", JSON_UNESCAPED_UNICODE) ?>
            }
          },
          "datePublished": <?= json_encode(date('c', strtotime($post['created_at'])), JSON_UNESCAPED_UNICODE) ?>,
          "dateModified": <?= json_encode(date('c', strtotime($post['updated_at'] ?? $post['created_at'])), JSON_UNESCAPED_UNICODE) ?>,
          "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": <?= json_encode(BASE_URL."/post/".$post['slug'], JSON_UNESCAPED_UNICODE) ?>
          }
        }
        </script>
        <!-- Schema.org Breadcrumb -->
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Beranda", "item": <?= json_encode(BASE_URL, JSON_UNESCAPED_UNICODE) ?>},
            {"@type": "ListItem", "position": 2, "name": <?= json_encode($post['category_name'] ?? 'Artikel', JSON_UNESCAPED_UNICODE) ?>, "item": <?= json_encode(BASE_URL . '/kategori/' . ($post['category_slug'] ?? ''), JSON_UNESCAPED_UNICODE) ?>},
            {"@type": "ListItem", "position": 3, "name": <?= json_encode(mb_substr($post['title'], 0, 80), JSON_UNESCAPED_UNICODE) ?>}
          ]
        }
        </script>
        <!-- /Schema.org -->
    </article>

    <!-- ═══ KOMENTAR ═══ -->
    <div id="comments-section" style="margin-top:24px">
      <h3>💬 Komentar (<span id="comment-count">0</span>)</h3>
      <div id="comment-list" style="margin:12px 0"></div>
      <div class="widget" style="margin-top:12px">
        <h3>Tulis Komentar</h3>
        <form id="comment-form" onsubmit="return submitComment(event)">
          <input type="hidden" id="c-post-id" value="<?= (int)$post['id'] ?>">
          <div style="margin-bottom:8px"><input id="c-nama" type="text" placeholder="Nama kamu" required maxlength="100" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px"></div>
          <div style="margin-bottom:8px"><input id="c-email" type="email" placeholder="Email (opsional)" maxlength="255" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px"></div>
          <div style="margin-bottom:8px"><textarea id="c-komentar" placeholder="Tulis komentarmu..." required maxlength="2000" rows="3" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px"></textarea></div>
          <button type="submit" style="padding:8px 20px;background:var(--primary,#FFC400);color:#1a1a1a;font-weight:700;border:none;border-radius:6px;cursor:pointer">Kirim Komentar</button>
          <div id="comment-msg" style="margin-top:8px;font-size:13px"></div>
        </form>
      </div>
    </div>
    <script>
    async function loadComments(){
      const pid = document.getElementById('c-post-id').value;
      try {
        const r = await fetch('<?= BASE_URL ?>/api/comment.php?action=list&post_id='+pid);
        const d = await r.json();
        const list = document.getElementById('comment-list');
        document.getElementById('comment-count').textContent = d.comments ? d.comments.length : 0;
        if(!d.comments || !d.comments.length){ list.innerHTML = '<p style="color:#888;font-size:13px">Belum ada komentar. Jadilah yang pertama!</p>'; return; }
        list.innerHTML = d.comments.map(cm => {
          const esc = s => String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
          return '<div style="border:1px solid #eee;border-radius:8px;padding:10px;margin-bottom:8px"><div style="font-weight:700;font-size:13px">'+esc(cm.nama)+'</div><div style="font-size:13px;margin-top:4px;white-space:pre-wrap">'+esc(cm.komentar)+'</div><div style="font-size:11px;color:#999;margin-top:4px">'+esc(cm.created_at)+'</div></div>';
        }).join('');
      } catch(e){}
    }
    async function submitComment(e){
      e.preventDefault();
      const msg = document.getElementById('comment-msg');
      msg.textContent = 'Mengirim...';
      try {
        const fd = new FormData();
        fd.append('action','submit');
        fd.append('post_id', document.getElementById('c-post-id').value);
        fd.append('nama', document.getElementById('c-nama').value);
        fd.append('email', document.getElementById('c-email').value);
        fd.append('komentar', document.getElementById('c-komentar').value);
        const r = await fetch('<?= BASE_URL ?>/api/comment.php', {method:'POST', body:fd});
        const d = await r.json();
        msg.textContent = d.message;
        msg.style.color = d.status === 'ok' ? 'green' : 'red';
        if(d.status === 'ok'){ document.getElementById('comment-form').reset(); }
      } catch(e){ msg.textContent = 'Gagal mengirim.'; msg.style.color = 'red'; }
      return false;
    }
    loadComments();
    </script>

    <?php if($related): ?>
      <div style="margin-top:20px">
        <h3 style="margin-bottom:12px">📖 Artikel Terkait</h3>
        <div class="grid">
          <?php foreach($related as $r): ?>
            <article class="card">
              <?php $rthumb = !empty($r['thumbnail']) ? $r['thumbnail'] : "https://picsum.photos/seed/".e($r['slug'])."/600/340"; ?><div class="thumb"><img src="<?= e($rthumb) ?>" alt=""></div>
              <div class="body"><div class="title"><a href="<?= BASE_URL ?>/post/<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></div><div class="meta"><span><?= e(time_ago($r['created_at'])) ?></span></div></div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <aside class="sidebar">
    <?= render_ad('sidebar', 'mb-4') ?>
    <div class="widget"><h3>📂 Kategori</h3><ul><?php foreach(all_categories() as $c): ?><li><a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?></ul></div>
    <div class="widget"><h3>🔥 Populer</h3><?php foreach(popular_posts(5) as $i=>$pop): ?><div class="pop-item"><div class="n"><?= $i+1 ?></div><a href="<?= BASE_URL ?>/post/<?= e($pop['slug']) ?>"><?= e($pop['title']) ?></a></div><?php endforeach; ?></div>
    <div class="widget" style="background:linear-gradient(135deg,#FFC400,#FF8C00);color:#1a1a1a;border:none">
      <h3 style="color:#1a1a1a">📧 Newsletter</h3>
      <p style="font-size:13px;margin-bottom:10px">Dapatkan artikel terbaru langsung di email!</p>
      <form onsubmit="return subscribeNewsletter(event)">
        <input id="nl-email" type="email" placeholder="Email kamu" required style="width:100%;padding:8px;border:none;border-radius:6px;margin-bottom:8px">
        <button type="submit" style="width:100%;padding:8px;background:#1a1a1a;color:#FFC400;border:none;border-radius:6px;font-weight:700;cursor:pointer">Berlangganan</button>
        <div id="nl-msg" style="font-size:12px;margin-top:6px"></div>
      </form>
    </div>
    <script>
    async function subscribeNewsletter(e){
      e.preventDefault();
      const msg = document.getElementById('nl-msg');
      msg.textContent = 'Mendaftar...';
      try {
        const fd = new FormData();
        fd.append('email', document.getElementById('nl-email').value);
        const r = await fetch('<?= BASE_URL ?>/api/newsletter.php', {method:'POST', body:fd});
        const d = await r.json();
        msg.textContent = d.message;
        if(d.status === 'ok') document.getElementById('nl-email').value = '';
      } catch(e){ msg.textContent = 'Gagal. Coba lagi.'; }
      return false;
    }
    </script>
  </aside>
</div>

<!-- POST ENHANCEMENT SCRIPTS -->
<script>
// 1. Reading Progress Bar Listener
window.addEventListener('scroll', function() {
  var article = document.querySelector('.post-body');
  var bar = document.getElementById('readingProgressBar');
  if (!article || !bar) return;
  var rect = article.getBoundingClientRect();
  var totalHeight = rect.height - window.innerHeight;
  var progress = 0;
  if (totalHeight > 0) {
    var scrolled = -rect.top;
    progress = Math.min(100, Math.max(0, (scrolled / totalHeight) * 100));
  } else if (rect.top <= 0) {
    progress = 100;
  }
  bar.style.width = progress + '%';
});

// 2. Table of Contents Toggle
function toggleToc(headerEl) {
  var tocCard = headerEl.closest('.post-toc');
  if (!tocCard) return;
  var body = tocCard.querySelector('.post-toc-body');
  var toggleText = tocCard.querySelector('.post-toc-toggle');
  if (body.style.display === 'none') {
    body.style.display = 'block';
    if (toggleText) toggleText.textContent = 'Tutup ▴';
  } else {
    body.style.display = 'none';
    if (toggleText) toggleText.textContent = 'Buka ▾';
  }
}

// 3. 1-Click Copy Code Button on all code blocks
document.addEventListener('DOMContentLoaded', function() {
  var codeBlocks = document.querySelectorAll('.post-body pre');
  codeBlocks.forEach(function(pre) {
    // Check if already wrapped
    if (pre.parentNode.classList.contains('code-block-wrap')) return;

    var wrapper = document.createElement('div');
    wrapper.className = 'code-block-wrap';

    var header = document.createElement('div');
    header.className = 'code-block-header';

    // Detect language
    var code = pre.querySelector('code');
    var lang = 'CODE';
    if (code) {
      var match = code.className.match(/language-([a-zA-Z0-9_-]+)/);
      if (match) lang = match[1].toUpperCase();
    }
    header.innerHTML = '<span class="code-lang">' + lang + '</span><button type="button" class="code-copy-btn" title="Salin kode">📋 Salin</button>';

    pre.parentNode.insertBefore(wrapper, pre);
    wrapper.appendChild(header);
    wrapper.appendChild(pre);

    var copyBtn = header.querySelector('.code-copy-btn');
    copyBtn.addEventListener('click', function() {
      var textToCopy = (code ? code.innerText : pre.innerText).trim();
      navigator.clipboard.writeText(textToCopy).then(function() {
        copyBtn.innerHTML = '✓ Tersalin!';
        copyBtn.classList.add('copied');
        setTimeout(function() {
          copyBtn.innerHTML = '📋 Salin';
          copyBtn.classList.remove('copied');
        }, 2000);
      }).catch(function() {
        copyBtn.innerHTML = 'Gagal';
      });
    });
  });
});

// 4. AI Speech Synthesis Audio Narrator
(function() {
  var playBtn = document.getElementById('audioPlayBtn');
  var playIcon = document.getElementById('audioPlayIcon');
  var playText = document.getElementById('audioPlayText');
  var speedBtn = document.getElementById('audioSpeedBtn');
  var waveform = document.getElementById('audioWaveform');
  var statusText = document.getElementById('audioStatusText');

  if (!playBtn || !('speechSynthesis' in window)) {
    if (playBtn) playBtn.disabled = true;
    if (statusText) statusText.textContent = 'Audio tidak didukung di browser ini';
    return;
  }

  var isPlaying = false;
  var isPaused = false;
  var currentSpeed = 1.0;
  var speeds = [1.0, 1.25, 1.5, 2.0];
  var speedIdx = 0;
  var synth = window.speechSynthesis;
  var utterance = null;

  function extractCleanText() {
    var body = document.querySelector('.post-body');
    if (!body) return '';
    var clone = body.cloneNode(true);
    // Remove scripts, ads, toc, code blocks, cards, share buttons
    clone.querySelectorAll('script, style, pre, code, .post-toc, .post-audio-card, .post-tldr-card, .share, .ad-unit, iframe').forEach(function(el) {
      el.remove();
    });
    return (clone.innerText || clone.textContent || '').replace(/\s+/g, ' ').trim();
  }

  speedBtn.addEventListener('click', function() {
    speedIdx = (speedIdx + 1) % speeds.length;
    currentSpeed = speeds[speedIdx];
    speedBtn.textContent = currentSpeed + 'x';
    if (isPlaying) {
      synth.cancel();
      startSpeaking();
    }
  });

  function startSpeaking() {
    var text = extractCleanText();
    if (!text) return;

    utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = currentSpeed;
    utterance.lang = 'id-ID';

    var voices = synth.getVoices();
    for (var i = 0; i < voices.length; i++) {
      if (voices[i].lang && (voices[i].lang.startsWith('id') || voices[i].lang.startsWith('in'))) {
        utterance.voice = voices[i];
        break;
      }
    }

    utterance.onstart = function() {
      isPlaying = true;
      isPaused = false;
      playBtn.classList.add('playing');
      playIcon.textContent = '⏸';
      playText.textContent = 'Jeda';
      waveform.classList.add('active');
      statusText.textContent = 'Membacakan Narasi AI (' + currentSpeed + 'x)...';
    };

    utterance.onend = function() {
      isPlaying = false;
      isPaused = false;
      playBtn.classList.remove('playing');
      playIcon.textContent = '▶';
      playText.textContent = 'Putar';
      waveform.classList.remove('active');
      statusText.textContent = 'Selesai didengarkan ✨';
    };

    utterance.onerror = function() {
      isPlaying = false;
      isPaused = false;
      playBtn.classList.remove('playing');
      playIcon.textContent = '▶';
      playText.textContent = 'Putar';
      waveform.classList.remove('active');
      statusText.textContent = 'Gagal memuat audio';
    };

    synth.speak(utterance);
  }

  playBtn.addEventListener('click', function() {
    if (!isPlaying) {
      startSpeaking();
    } else {
      if (synth.speaking && !synth.paused) {
        synth.pause();
        isPaused = true;
        playIcon.textContent = '▶';
        playText.textContent = 'Lanjut';
        waveform.classList.remove('active');
        statusText.textContent = 'Dijeda';
      } else if (synth.paused) {
        synth.resume();
        isPaused = false;
        playIcon.textContent = '⏸';
        playText.textContent = 'Jeda';
        waveform.classList.add('active');
        statusText.textContent = 'Membacakan Narasi AI (' + currentSpeed + 'x)...';
      }
    }
  });

  window.addEventListener('beforeunload', function() {
    if (synth && synth.speaking) synth.cancel();
  });
})();
</script>

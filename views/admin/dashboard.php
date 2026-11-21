<style>
/* Dashboard polish — SaaS 2026 Mobile-Ready */
.dash-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
@media(max-width:768px){
  .dash-actions{display:grid;grid-template-columns:repeat(3,1fr);width:100%;gap:8px}
  .dash-actions #btnTriggerAi{grid-column:span 3;width:100%;padding:11px 14px}
  .dash-actions .btn{justify-content:center}
}
@media(max-width:480px){
  .dash-actions{grid-template-columns:1fr 1fr}
  .dash-actions #btnTriggerAi{grid-column:span 2}
  .dash-actions .btn-live-blog{grid-column:span 2}
}

.dash-stat-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:18px}
@media(max-width:1200px){.dash-stat-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:640px){.dash-stat-grid{grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:14px}}
@media(max-width:360px){.dash-stat-grid{grid-template-columns:repeat(2,1fr);gap:6px}}

.dash-stat{padding:12px 10px;border-radius:12px;background:var(--card);border:1px solid var(--border);box-shadow:0 4px 14px rgba(0,0,0,.04);display:flex;flex-direction:column;gap:3px;position:relative;overflow:hidden;box-sizing:border-box}
.dash-stat::after{content:"";position:absolute;right:-10px;top:-10px;width:60px;height:60px;border-radius:50%;background:rgba(0,90,240,.06)}
.dash-stat .k{font-size:10px;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);font-weight:700;display:flex;align-items:center;gap:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-stat .v{font-size:20px;font-weight:800;color:var(--main);line-height:1.15}
.dash-stat .s{font-size:10.5px;color:var(--muted);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-stat.warn{border-color:#fed7aa} html[data-theme="dark"] .dash-stat.warn{border-color:#7c4a10}
.dash-stat.good{border-color:#bbf7d0} html[data-theme="dark"] .dash-stat.good{border-color:#14532d}

.dash-analytics{display:grid;grid-template-columns:1.6fr 1fr;gap:14px;margin-bottom:18px}
@media(max-width:900px){.dash-analytics{grid-template-columns:1fr;gap:12px}}

.dash-chart-bars-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:6px;scrollbar-width:thin}
.dash-chart-bars-wrap::-webkit-scrollbar{height:4px}
.dash-chart-bars-wrap::-webkit-scrollbar-thumb{background:rgba(0,90,240,.2);border-radius:4px}
.dash-chart-bars{display:flex;align-items:end;gap:6px;height:110px;padding:8px 6px 0;border-top:1px solid var(--border);margin-top:10px;min-width:100%}
.dash-bar{flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;min-width:22px}
.dash-bar-i{width:100%;max-width:36px;background:linear-gradient(180deg,var(--primary),#00dcc0);border-radius:6px 6px 0 0;min-height:4px;transition:.2s;position:relative}
.dash-bar-i.zero{background:var(--border)}
.dash-bar small{font-size:9.5px;color:var(--muted);white-space:nowrap}
.dash-bar b{font-size:10.5px;color:var(--main);position:absolute;top:-16px;left:50%;transform:translateX(-50%);white-space:nowrap}

.cat-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);box-sizing:border-box}
.cat-row:last-child{border:0}
.cat-row .bar{flex:1;height:7px;background:var(--border);border-radius:999px;overflow:hidden}
.cat-row .fill{height:100%;background:var(--primary);border-radius:999px}

.dash-tabs-bar{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;margin:0 0 12px;padding:2px 0}
.dash-tabs-bar::-webkit-scrollbar{display:none}
.dash-tabs{display:flex;gap:6px;flex-wrap:nowrap}
.dash-tab{padding:7px 14px;border-radius:999px;border:1px solid var(--border);background:var(--card);font-size:12px;font-weight:700;cursor:pointer;color:var(--muted);transition:.15s;white-space:nowrap;flex-shrink:0}
.dash-tab:hover{border-color:var(--primary);color:var(--main)}
.dash-tab.active{background:var(--primary);color:#fff;border-color:var(--primary)}

.thumb-mini{width:40px;height:28px;border-radius:6px;object-fit:cover;background:var(--border);flex-shrink:0}
.dash-views-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px}
@media(max-width:860px){.dash-views-summary{grid-template-columns:repeat(2,1fr);gap:8px}}
@media(max-width:360px){.dash-views-summary{grid-template-columns:repeat(2,1fr);gap:6px}}

.dash-view-pill{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:10px 12px;display:flex;flex-direction:column;gap:3px;transition:.2s;box-sizing:border-box}
.dash-view-pill:hover{border-color:var(--primary)}
.dash-view-pill .k{font-size:10px;color:var(--muted);font-weight:700;letter-spacing:.04em;text-transform:uppercase;display:flex;align-items:center;gap:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-view-pill .v{font-size:19px;font-weight:800;color:var(--main);line-height:1.15}
.dash-view-pill.pill-day .v{color:#3b82f6}
.dash-view-pill.pill-week .v{color:#8b5cf6}
.dash-view-pill.pill-month .v{color:#ec4899}
.dash-view-pill.pill-total .v{color:#10b981}

.badge-rank{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:24px;border-radius:6px;font-size:11px;font-weight:800;color:#fff;padding:0 6px}
.badge-rank.rank-1{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 2px 6px rgba(245,158,11,0.35)}
.badge-rank.rank-2{background:linear-gradient(135deg,#94a3b8,#64748b)}
.badge-rank.rank-3{background:linear-gradient(135deg,#b45309,#78350f)}
.badge-rank.rank-other{background:var(--card);color:var(--muted);border:1px solid var(--border)}

.dash-demo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px}
@media(max-width:980px){.dash-demo-grid{grid-template-columns:1fr;gap:12px}}

.dash-bottom{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px}
@media(max-width:860px){.dash-bottom{grid-template-columns:1fr;gap:12px}}
.dash-kv{display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed var(--border);font-size:12.5px;gap:8px}
.dash-kv:last-child{border:0}
</style>

<div class="admin-wrap" style="max-width:1280px">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
    <div>
      <h2 style="margin:0;font-size:20px">📊 Dashboard</h2>
      <div style="font-size:12px;color:var(--muted);margin-top:3px">Ringkasan AizuDemy · <?= e(date('d M Y')) ?> · <a href="<?= BASE_URL ?>/sitemap.xml" target="_blank" style="color:var(--muted);text-decoration:underline">sitemap.xml</a></div>
    </div>
    <div class="dash-actions">
      <button type="button" class="btn" id="btnTriggerAi" style="background:linear-gradient(135deg,#005af0,#00dcc0);color:#fff;font-weight:700;border:0;box-shadow:0 4px 12px rgba(0,90,240,0.25);display:flex;align-items:center;justify-content:center;gap:6px;cursor:pointer" onclick="triggerAiPublisher()">
        <span>⚡</span> Tulis Artikel AI Otomatis
      </button>
      <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/posts?action=new" style="justify-content:center">+ Tulis Manual</a>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts" style="justify-content:center">Kelola Artikel</a>
      <a class="btn btn-ghost btn-live-blog" href="<?= BASE_URL ?>/" target="_blank" style="justify-content:center">🌐 Lihat Blog</a>
    </div>
  </div>

  <?php if(!empty($pipelineStatus)): $ps=$pipelineStatus['state'] ?? 'unknown'; ?>
  <div class="admin-card" style="margin-bottom:16px;border-left:4px solid <?= $ps==='success'||$ps==='completed'?'#16a34a':($ps==='failed'?'#dc2626':'var(--primary)') ?>">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
      <div><b>🤖 Auto Publisher: <?= e(strtoupper($ps)) ?></b><div style="font-size:12px;color:var(--muted);margin-top:4px">Tahap: <?= e($pipelineStatus['stage'] ?? '-') ?> · Seri: <?= e($pipelineStatus['series'] ?? '-') ?> · Part: <?= (int)($pipelineStatus['part'] ?? 0) ?></div></div>
      <small style="color:var(--muted)"><?= e($pipelineStatus['updated_at'] ?? '') ?></small>
    </div>
    <?php if(!empty($pipelineStatus['message'])): ?><div style="margin-top:8px;font-size:12px;color:#16a34a"><?= e($pipelineStatus['message']) ?></div><?php endif; ?>
    <?php if(!empty($pipelineStatus['error'])): ?><div style="margin-top:8px;font-size:12px;color:#dc2626"><?= e($pipelineStatus['error']) ?></div><?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- STAT GRID -->
  <div class="dash-stat-grid">
    <div class="dash-stat">
      <div class="k">📄 Total Artikel</div>
      <div class="v"><?= (int)$stats['posts'] ?></div>
      <div class="s"><?= (int)$stats['publish'] ?> publish · <?= (int)$stats['draft'] ?> draft</div>
    </div>
    <div class="dash-stat good">
      <div class="k">✅ Publish</div>
      <div class="v" style="color:#16a34a"><?= (int)$stats['publish'] ?></div>
      <div class="s"><?= $stats['draft']===0 ? 'Semua sudah publish 🎉' : $stats['draft'].' menunggu' ?></div>
    </div>
    <div class="dash-stat <?= $stats['draft']>0?'warn':'' ?>">
      <div class="k">📝 Draft</div>
      <div class="v"><?= (int)$stats['draft'] ?></div>
      <div class="s"><?= $stats['draft']>0 ? 'Perlu direview' : 'Tidak ada draft' ?></div>
    </div>
    <div class="dash-stat">
      <div class="k">📂 Kategori</div>
      <div class="v"><?= (int)$stats['cats'] ?></div>
      <div class="s"><?= count(array_filter($catStats, fn($c)=>(int)$c['cnt']>0)) ?> terisi · <?= (int)$stats['cats']-count(array_filter($catStats, fn($c)=>(int)$c['cnt']>0)) ?> kosong</div>
    </div>
    <div class="dash-stat">
      <div class="k">👁️ Total Views</div>
      <div class="v"><?= number_format((int)$stats['views']) ?></div>
      <div class="s">Akumulasi semua artikel</div>
    </div>
    <div class="dash-stat">
      <div class="k">📅 Bulan ini</div>
      <div class="v"><?= (int)$stats['this_month'] ?></div>
      <div class="s"><?= e(date('F Y')) ?> · <?= $stats['no_thumb']>0||$stats['no_cat']>0? $stats['no_thumb']+$stats['no_cat'].' butuh cek': 'Rapi ✓' ?></div>
    </div>
  </div>

  <!-- ANALYTICS -->
  <div class="dash-analytics">
    <div class="admin-card">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
        <h3 style="margin:0">📈 Artikel baru — <?= $range===30?'30 hari':'7 hari' ?> terakhir</h3>
        <div style="display:flex;gap:6px">
          <a href="<?= BASE_URL ?>/admin?range=7" class="btn <?= $range===7?'btn-primary':'btn-ghost' ?>" style="padding:5px 10px;font-size:11px">7 hari</a>
          <a href="<?= BASE_URL ?>/admin?range=30" class="btn <?= $range===30?'btn-primary':'btn-ghost' ?>" style="padding:5px 10px;font-size:11px">30 hari</a>
        </div>
      </div>
      <div style="font-size:12px;color:var(--muted);margin-top:4px">Total <?= (int)$chartTotal ?> artikel pada rentang ini · puncak <?= (int)$chartMax ?>/hari</div>
      <div class="dash-chart-bars-wrap">
        <div class="dash-chart-bars">
          <?php foreach($chart as $d=>$c):
            $h = $chartMax>0 ? max(4, round($c/$chartMax*92)) : 4;
            $label = date('d/m', strtotime($d));
            $isZero = $c===0;
          ?>
          <div class="dash-bar" title="<?= e($d) ?>: <?= (int)$c ?> artikel">
            <div class="dash-bar-i <?= $isZero?'zero':'' ?>" style="height:<?= $h ?>px"><?php if($c>0): ?><b><?= (int)$c ?></b><?php endif; ?></div>
            <small><?= e($label) ?></small>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div style="font-size:11px;color:var(--muted);margin-top:8px">* Grafik hitung <code>created_at</code> per hari (bukan views) — akurat dengan schema sekarang.</div>
    </div>
    <div class="admin-card">
      <h3 style="margin:0 0 6px">🏷️ Kategori teratas</h3>
      <div style="font-size:12px;color:var(--muted);margin-bottom:8px">Top 5 by jumlah artikel</div>
      <?php
        $maxCnt = max(1, max(array_column($catStats,'cnt') ?: [1]));
        foreach($catStats as $cc):
          $pct = $maxCnt>0 ? round((int)$cc['cnt']/$maxCnt*100) : 0;
      ?>
      <div class="cat-row">
        <div style="min-width:0;flex:1">
          <div style="font-size:13px;font-weight:700;color:var(--main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><a href="<?= BASE_URL ?>/category/<?= e($cc['slug']) ?>" target="_blank"><?= e($cc['name']) ?></a></div>
          <div style="font-size:11px;color:var(--muted)"><?= e($cc['slug']) ?></div>
        </div>
        <div class="bar" style="max-width:110px"><div class="fill" style="width:<?= $pct ?>%"></div></div>
        <span class="badge" style="min-width:28px;text-align:center"><?= (int)$cc['cnt'] ?></span>
      </div>
      <?php endforeach; if(empty($catStats)): ?><div style="font-size:12px;color:var(--muted)">Belum ada kategori</div><?php endif; ?>
      <div style="margin-top:10px"><a class="btn btn-ghost" href="<?= BASE_URL ?>/sitemap" target="_blank" style="width:100%;justify-content:center;display:flex">Lihat sitemap →</a></div>
    </div>
  </div>

  <!-- AUDIENCE DEMOGRAPHICS & TRAFFIC SOURCE INTELLIGENCE -->
  <div class="dash-demo-grid">
    <!-- Card 1: Top Kota & Wilayah -->
    <div class="admin-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <h3 style="margin:0;font-size:14px;display:flex;align-items:center;gap:6px">🏙️ Top Kota Pembaca</h3>
        <span style="font-size:11px;color:var(--muted)">30 Hari</span>
      </div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:10px">Sebaran wilayah pembaca aktif (IP Geo)</div>
      <?php 
        $maxCityViews = !empty($analyticsSummary['cities']) ? max(1, max(array_column($analyticsSummary['cities'], 'views'))) : 1;
        $totalCityViews = !empty($analyticsSummary['cities']) ? array_sum(array_column($analyticsSummary['cities'], 'views')) : 1;
        foreach(($analyticsSummary['cities'] ?? []) as $c):
          $pct = round(((int)$c['views'] / $totalCityViews) * 100);
      ?>
      <div class="cat-row" style="padding:6px 0">
        <div style="min-width:0;flex:1">
          <div style="font-size:12.5px;font-weight:700;color:var(--main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            📍 <?= e($c['city']) ?> <span style="font-size:10.5px;font-weight:normal;color:var(--muted)">(<?= e($c['country']) ?>)</span>
          </div>
        </div>
        <div class="bar" style="max-width:80px"><div class="fill" style="width:<?= round(((int)$c['views']/$maxCityViews)*100) ?>%"></div></div>
        <span class="badge" style="font-size:11px;padding:2px 6px"><?= (int)$c['views'] ?> (<?= $pct ?>%)</span>
      </div>
      <?php endforeach; if(empty($analyticsSummary['cities'])): ?><div style="font-size:12px;color:var(--muted);padding:10px 0">Belum ada data demografi kota</div><?php endif; ?>
    </div>

    <!-- Card 2: Perangkat & OS -->
    <div class="admin-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <h3 style="margin:0;font-size:14px;display:flex;align-items:center;gap:6px">📱 Perangkat & Gawai</h3>
        <span style="font-size:11px;color:var(--muted)">Mobile vs PC</span>
      </div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:10px">Tipe perangkat & sistem operasi pembaca</div>
      <?php 
        $totalDev = !empty($analyticsSummary['devices']) ? array_sum(array_column($analyticsSummary['devices'], 'count')) : 1;
        $maxDev = !empty($analyticsSummary['devices']) ? max(1, max(array_column($analyticsSummary['devices'], 'count'))) : 1;
        foreach(($analyticsSummary['devices'] ?? []) as $d):
          $pct = round(((int)$d['count'] / $totalDev) * 100);
          $icon = $d['device_type'] === 'mobile' ? '📱 Mobile (HP Android/iOS)' : ($d['device_type'] === 'desktop' ? '💻 Desktop (Laptop/PC)' : ($d['device_type'] === 'tablet' ? '📟 Tablet / iPad' : '🤖 Bot / Crawler'));
      ?>
      <div class="cat-row" style="padding:6px 0">
        <div style="min-width:0;flex:1">
          <div style="font-size:12px;font-weight:700;color:var(--main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= $icon ?></div>
        </div>
        <div class="bar" style="max-width:80px"><div class="fill" style="width:<?= round(((int)$d['count']/$maxDev)*100) ?>%;background:#8b5cf6"></div></div>
        <span class="badge" style="font-size:11px;padding:2px 6px"><?= $pct ?>%</span>
      </div>
      <?php endforeach; ?>
      <div style="border-top:1px dashed var(--border);margin-top:8px;padding-top:6px;font-size:11px;color:var(--muted);display:flex;justify-content:space-between">
        <span><b>Top OS:</b> <?= implode(', ', array_map(fn($o)=>$o['os'], array_slice($analyticsSummary['os'] ?? [], 0, 3))) ?></span>
      </div>
    </div>

    <!-- Card 3: Sumber Trafik -->
    <div class="admin-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <h3 style="margin:0;font-size:14px;display:flex;align-items:center;gap:6px">🔗 Saluran Trafik</h3>
        <span style="font-size:11px;color:var(--muted)">Channel Asal</span>
      </div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:10px">Asal rujukan kunjungan pembaca</div>
      <?php 
        $totalSrc = !empty($analyticsSummary['sources']) ? array_sum(array_column($analyticsSummary['sources'], 'count')) : 1;
        $maxSrc = !empty($analyticsSummary['sources']) ? max(1, max(array_column($analyticsSummary['sources'], 'count'))) : 1;
        foreach(($analyticsSummary['sources'] ?? []) as $s):
          $pct = round(((int)$s['count'] / $totalSrc) * 100);
          $srcLabel = match($s['traffic_source']){
            'search'   => '🔍 Mesin Pencari (Google/Bing)',
            'social'   => '💬 Media Sosial & WA Chat',
            'direct'   => '🌐 Direct / Bookmark',
            'referral' => '🔗 Website Eksternal',
            default    => '❓ Lainnya'
          };
          $fillColor = match($s['traffic_source']){
            'search'   => '#3b82f6',
            'social'   => '#10b981',
            'direct'   => '#f59e0b',
            'referral' => '#ec4899',
            default    => '#6b7280'
          };
      ?>
      <div class="cat-row" style="padding:6px 0">
        <div style="min-width:0;flex:1">
          <div style="font-size:12px;font-weight:700;color:var(--main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= $srcLabel ?></div>
        </div>
        <div class="bar" style="max-width:70px"><div class="fill" style="width:<?= round(((int)$s['count']/$maxSrc)*100) ?>%;background:<?= $fillColor ?>"></div></div>
        <span class="badge" style="font-size:11px;padding:2px 6px"><?= $pct ?>%</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- MULTI-PERIOD POPULARITY & RECENT POSTS HUB -->
  <div class="admin-card" id="dashTables">
    <!-- Quick Views Metrics Bar -->
    <div class="dash-views-summary">
      <div class="dash-view-pill pill-day">
        <div class="k">⚡ Views Hari Ini</div>
        <div class="v"><?= number_format((int)($stats['today_views'] ?? 0)) ?></div>
        <div style="font-size:10.5px;color:var(--muted)">Hari ini · <?= e(date('d M Y')) ?></div>
      </div>
      <div class="dash-view-pill pill-week">
        <div class="k">📅 Views 7 Hari</div>
        <div class="v"><?= number_format((int)($stats['week_views'] ?? 0)) ?></div>
        <div style="font-size:10.5px;color:var(--muted)">Minggu ini (7 hari terakhir)</div>
      </div>
      <div class="dash-view-pill pill-month">
        <div class="k">📆 Views 30 Hari</div>
        <div class="v"><?= number_format((int)($stats['month_views'] ?? 0)) ?></div>
        <div style="font-size:10.5px;color:var(--muted)">Bulan ini (30 hari terakhir)</div>
      </div>
      <div class="dash-view-pill pill-total">
        <div class="k">🏆 Total Keseluruhan</div>
        <div class="v"><?= number_format((int)($stats['views'] ?? 0)) ?></div>
        <div style="font-size:10.5px;color:var(--muted)">Akumulasi sepanjang waktu</div>
      </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:12px;padding-top:10px;border-top:1px solid var(--border)">
      <div>
        <h3 style="margin:0;display:flex;align-items:center;gap:6px">🔥 Peringkat Artikel Populer & Terbaru</h3>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">Pantau artikel yang paling banyak dibaca per hari, per minggu, per bulan, atau total keseluruhan</div>
      </div>
      <div class="dash-tabs-bar">
        <div class="dash-tabs" role="tablist">
          <button type="button" class="dash-tab active" data-tab="recent" onclick="switchDashTab('recent')">🕒 Terbaru</button>
          <button type="button" class="dash-tab" data-tab="pop-day" onclick="switchDashTab('pop-day')">⚡ Hari Ini</button>
          <button type="button" class="dash-tab" data-tab="pop-week" onclick="switchDashTab('pop-week')">📅 Minggu Ini</button>
          <button type="button" class="dash-tab" data-tab="pop-month" onclick="switchDashTab('pop-month')">📆 Bulan Ini</button>
          <button type="button" class="dash-tab" data-tab="pop-all" onclick="switchDashTab('pop-all')">🏆 Total All-Time</button>
        </div>
      </div>
    </div>

    <!-- TAB 1: TERBARU -->
    <div id="tab-recent" class="dash-tab-pane">
      <div class="table-responsive">
        <table class="table" style="width:100%;min-width:620px"><thead><tr><th>Artikel</th><th>Kategori</th><th>Status</th><th>Total Views</th><th>Tanggal</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach($recent as $r): ?>
            <tr>
              <td>
                <div style="display:flex;gap:10px;align-items:center;min-width:220px">
                  <?php if(!empty($r['thumbnail'])): ?><img class="thumb-mini" src="<?= e($r['thumbnail']) ?>" alt="" loading="lazy"><?php else: ?><div class="thumb-mini" style="display:flex;align-items:center;justify-content:center;font-size:14px">🖼️</div><?php endif; ?>
                  <div style="min-width:0">
                    <a href="<?= BASE_URL ?>/post/<?= e($r['slug']) ?>" target="_blank" style="font-weight:700;color:var(--main);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= e($r['title']) ?></a>
                    <div style="font-size:11px;color:var(--muted)"><?= e($r['slug']) ?> · <?= e(time_ago($r['created_at'])) ?></div>
                  </div>
                </div>
              </td>
              <td><?= e($r['cat_name'] ?? '-') ?></td>
              <td><span class="badge" style="background:<?= $r['status']=='publish'?'#16a34a':'#6b7280' ?>"><?= e($r['status']) ?></span></td>
              <td><b><?= number_format((int)$r['views']) ?></b></td>
              <td style="white-space:nowrap"><?= e(date('d/m/Y',strtotime($r['created_at']))) ?></td>
              <td style="white-space:nowrap"><a href="<?= BASE_URL ?>/admin/posts?action=edit&id=<?= (int)$r['id'] ?>">Edit</a> · <a href="<?= BASE_URL ?>/admin/posts?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Hapus?')">Hapus</a></td>
            </tr>
          <?php endforeach; if(empty($recent)): ?><tr><td colspan="6" style="text-align:center;color:var(--muted)">Belum ada artikel</td></tr><?php endif; ?>
        </tbody></table>
      </div>
      <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap"><a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts">Kelola Semua Artikel →</a><a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts?action=new">+ Tulis Baru</a></div>
    </div>

    <?php
    $popularSections = [
      'pop-day'   => ['title' => 'Top Artikel Populer Hari Ini', 'badge_color' => '#3b82f6', 'badge_icon' => '⚡', 'period_txt' => 'Views Hari Ini', 'data' => $popularDay],
      'pop-week'  => ['title' => 'Top Artikel Populer Minggu Ini (7 Hari Terakhir)', 'badge_color' => '#8b5cf6', 'badge_icon' => '📅', 'period_txt' => 'Views 7 Hari', 'data' => $popularWeek],
      'pop-month' => ['title' => 'Top Artikel Populer Bulan Ini (30 Hari Terakhir)', 'badge_color' => '#ec4899', 'badge_icon' => '📆', 'period_txt' => 'Views 30 Hari', 'data' => $popularMonth],
      'pop-all'   => ['title' => 'Top Artikel Sepanjang Waktu (Total Keseluruhan)', 'badge_color' => '#10b981', 'badge_icon' => '🏆', 'period_txt' => 'Total Views', 'data' => $popularAll],
    ];
    foreach($popularSections as $tabKey => $sec):
    ?>
    <!-- TAB POPULAR: <?= $tabKey ?> -->
    <div id="tab-<?= $tabKey ?>" class="dash-tab-pane" style="display:none">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;font-size:12px;color:var(--muted);flex-wrap:wrap;gap:6px">
        <span><b><?= $sec['badge_icon'] ?> <?= e($sec['title']) ?></b></span>
        <span>Diurutkan berdasarkan <code><?= e($sec['period_txt']) ?></code> tertinggi</span>
      </div>
      <div class="table-responsive">
        <table class="table" style="width:100%;min-width:620px">
          <thead>
            <tr>
              <th style="width:60px">Rank</th>
              <th>Artikel</th>
              <th>Kategori</th>
              <th style="text-align:right"><?= e($sec['period_txt']) ?></th>
              <th style="text-align:right">Total Views</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $rank = 1; foreach($sec['data'] as $r): 
              $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-other'));
              $rankIcon  = $rank === 1 ? '🥇 1' : ($rank === 2 ? '🥈 2' : ($rank === 3 ? '🥉 3' : '#'.$rank));
              $periodVal = isset($r['period_views']) ? (int)$r['period_views'] : (int)$r['views'];
              $totalVal  = isset($r['total_views']) ? (int)$r['total_views'] : (int)$r['views'];
            ?>
              <tr>
                <td><span class="badge-rank <?= $rankClass ?>"><?= $rankIcon ?></span></td>
                <td>
                  <div style="display:flex;gap:10px;align-items:center;min-width:220px">
                    <?php if(!empty($r['thumbnail'])): ?><img class="thumb-mini" src="<?= e($r['thumbnail']) ?>" alt="" loading="lazy"><?php else: ?><div class="thumb-mini" style="display:flex;align-items:center;justify-content:center;font-size:14px">🖼️</div><?php endif; ?>
                    <div style="min-width:0">
                      <a href="<?= BASE_URL ?>/post/<?= e($r['slug']) ?>" target="_blank" style="font-weight:700;color:var(--main);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= e($r['title']) ?></a>
                      <div style="font-size:11px;color:var(--muted)"><?= e($r['slug']) ?></div>
                    </div>
                  </div>
                </td>
                <td><span class="badge" style="background:var(--card);border:1px solid var(--border);color:var(--main)"><?= e($r['cat_name'] ?? 'Umum') ?></span></td>
                <td style="text-align:right">
                  <span class="badge" style="background:<?= $sec['badge_color'] ?>;color:#fff;font-weight:800;font-size:12px;padding:4px 8px">
                    <?= $sec['badge_icon'] ?> <?= number_format($periodVal) ?>
                  </span>
                </td>
                <td style="text-align:right;color:var(--muted);font-weight:600">
                  <?= number_format($totalVal) ?>
                </td>
                <td style="white-space:nowrap"><?= e(date('d/m/Y',strtotime($r['created_at']))) ?></td>
                <td style="white-space:nowrap">
                  <a href="<?= BASE_URL ?>/admin/posts?action=edit&id=<?= (int)$r['id'] ?>">Edit</a> · 
                  <a href="<?= BASE_URL ?>/post/<?= e($r['slug']) ?>" target="_blank">Lihat ↗</a>
                </td>
              </tr>
            <?php $rank++; endforeach; if(empty($sec['data'])): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Belum ada data views pada periode ini</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div style="font-size:11px;color:var(--muted);margin-top:8px">
        * Pelacakan views tercatat secara harian di database <code>post_views_daily</code> untuk histori akurat.
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <script>
  function switchDashTab(name){
    document.querySelectorAll('.dash-tab-pane').forEach(el => el.style.display = 'none');
    const target = document.getElementById('tab-' + name);
    if(target) target.style.display = '';
    document.querySelectorAll('.dash-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
    try{localStorage.setItem('aizudemy_dash_tab', name)}catch(e){}
  }
  try{
    var t = localStorage.getItem('aizudemy_dash_tab');
    if(t && document.getElementById('tab-' + t)) switchDashTab(t);
  }catch(e){}
  </script>

  <div class="dash-bottom">
    <div class="admin-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px">
        <h3 style="margin:0">⚠️ Butuh perhatian</h3>
        <?php if(!empty($drafts) || !empty($needs)): ?>
          <span style="font-size:11px;background:rgba(239,68,68,0.12);color:#ef4444;padding:3px 8px;border-radius:6px;font-weight:700">Perlu Penanganan</span>
        <?php endif; ?>
      </div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:12px"><?= (int)$stats['no_thumb'] ?> tanpa thumbnail · <?= (int)$stats['no_cat'] ?> tanpa kategori · <?= (int)$stats['draft'] ?> draft</div>
      <?php if(empty($drafts) && empty($needs)): ?>
        <div style="padding:24px;text-align:center;color:var(--muted);font-size:13px;background:var(--bg);border-radius:10px;border:1px dashed var(--border)">
          <div style="font-size:24px;margin-bottom:6px">🎉</div>
          <b>Semua artikel rapi & lengkap!</b><br>
          <span style="font-size:12px">Tidak ada draft kosong, artikel tanpa thumbnail, atau artikel tanpa kategori.</span>
        </div>
      <?php else: ?>
        <?php if(!empty($drafts)): ?>
          <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);font-weight:700;margin:8px 0 6px">Draft</div>
          <?php foreach($drafts as $d): ?>
            <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);flex-wrap:wrap">
              <div style="flex:1;min-width:180px">
                <a href="<?= BASE_URL ?>/admin/posts?action=edit&id=<?= (int)$d['id'] ?>" style="font-weight:700;color:var(--main);font-size:13px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($d['title']) ?></a>
                <div style="font-size:11px;color:var(--muted);display:flex;gap:6px;align-items:center;margin-top:2px">
                  <span class="badge" style="background:#6b7280;font-size:10px;padding:2px 6px">Draft</span>
                  <span><?= e($d['cat_name']??'Tanpa Kategori') ?></span> · <span><?= e(time_ago($d['updated_at'])) ?></span>
                </div>
              </div>
              <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
                <button class="btn btn-primary" onclick="openAiFixModal(<?= (int)$d['id'] ?>, '<?= e(addslashes($d['title'])) ?>')" style="padding:5px 10px;font-size:11px;display:inline-flex;align-items:center;gap:4px">✨ Perbaiki AI</button>
                <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts?action=edit&id=<?= (int)$d['id'] ?>" style="padding:5px 10px;font-size:11px">Edit</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <?php if(!empty($needs)): ?>
          <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);font-weight:700;margin:12px 0 6px">Tanpa thumbnail / kategori</div>
          <?php foreach($needs as $n): ?>
            <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);flex-wrap:wrap">
              <div style="flex:1;min-width:180px">
                <a href="<?= BASE_URL ?>/post/<?= e($n['slug']) ?>" target="_blank" style="font-weight:700;color:var(--main);font-size:13px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($n['title']) ?></a>
                <div style="font-size:11px;color:var(--muted);display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-top:2px">
                  <?php if(empty($n['thumbnail'])): ?><span class="badge" style="background:#f59e0b;font-size:10px;padding:2px 6px">⚠️ Tanpa Thumbnail</span><?php endif; ?>
                  <?php if(empty($n['category_id'])): ?><span class="badge" style="background:#ef4444;font-size:10px;padding:2px 6px">⚠️ Tanpa Kategori</span><?php endif; ?>
                  <span><?= e($n['cat_name']??'-') ?></span>
                </div>
              </div>
              <div style="display:flex;gap:6px;align-items:center;flex-shrink:0">
                <button class="btn btn-primary" onclick="openAiFixModal(<?= (int)$n['id'] ?>, '<?= e(addslashes($n['title'])) ?>')" style="padding:5px 10px;font-size:11px;display:inline-flex;align-items:center;gap:4px">✨ Perbaiki AI</button>
                <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts?action=edit&id=<?= (int)$n['id'] ?>" style="padding:5px 10px;font-size:11px">Edit</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="admin-card">
        <h3 style="margin:0 0 8px">⚡ Aksi cepat</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/posts?action=new" style="justify-content:center;display:flex">+ Tulis Artikel</a>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts" style="justify-content:center;display:flex">📝 Kelola Artikel</a>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/sitemap.xml" target="_blank" style="justify-content:center;display:flex">📄 Sitemap XML</a>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/sitemap" target="_blank" style="justify-content:center;display:flex">🗺️ Sitemap HTML</a>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/search" target="_blank" style="justify-content:center;display:flex">🔍 Cek Pencarian</a>
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/" target="_blank" style="justify-content:center;display:flex">🌐 Lihat Blog</a>
        </div>
        <div style="margin-top:10px;padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:8px;font-size:11.5px;color:var(--muted)">
          <b style="color:var(--main)">🔌 API Publikasi</b> — POST <code>/api/publish.php</code> header <code>X-Token: YOUR_PUBLISH_TOKEN_HERE</code><br>
          <a href="#api" onclick="document.getElementById('apiBox').scrollIntoView({behavior:'smooth'});return false" style="font-size:11px">Lihat contoh curl ↓</a>
        </div>
      </div>
      <div class="admin-card">
        <h3 style="margin:0 0 8px">🖥️ Info sistem</h3>
        <div class="dash-kv"><span style="color:var(--muted)">PHP</span><b><?= e($sysInfo['php']) ?></b></div>
        <div class="dash-kv"><span style="color:var(--muted)">MySQL</span><b style="font-size:11px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e(explode('-', $sysInfo['mysql'])[0]) ?></b></div>
        <div class="dash-kv"><span style="color:var(--muted)">Uploads</span><b><?= (int)$sysInfo['uploads'] ?> file</b></div>
        <div class="dash-kv"><span style="color:var(--muted)">Last update</span><b><?= $sysInfo['last_post'] ? e(date('d/m/Y H:i', strtotime($sysInfo['last_post']))) : '-' ?></b></div>
        <div class="dash-kv"><span style="color:var(--muted)">Base URL</span><b style="font-size:11px"><?= e(BASE_URL) ?></b></div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/logout" style="padding:6px 10px;font-size:11px">Logout</a>
          <span style="font-size:11px;color:var(--muted);align-self:center">Login sebagai <b style="color:var(--main)"><?= e($_SESSION['aizudemy_admin'] ?? 'admin') ?></b></span>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-card" id="apiBox" style="margin-top:14px">
    <h3>🔌 API Publikasi</h3>
    <p style="font-size:13px;color:var(--muted)">Kirim POST ke <code>/api/publish.php</code> dengan <code>title, content, category_slug, excerpt</code> + header <code>X-Token: YOUR_PUBLISH_TOKEN_HERE</code> untuk publikasi via integrasi eksternal / cron.</p>
    <pre style="background:#1e2430;color:#e6e8eb;padding:12px;border-radius:8px;font-size:12px;overflow:auto">curl -X POST <?= BASE_URL ?>/api/publish.php \
  -H "X-Token: YOUR_PUBLISH_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"title":"Judul Artikel","content":"&lt;p&gt;Isi HTML...&lt;/p&gt;","category_slug":"teknologi","excerpt":"Ringkasan"}'</pre>
  </div>
</div>

<!-- Modal AI Fix Post -->
<div id="aiFixModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);z-index:1000;align-items:center;justify-content:center;padding:16px;box-sizing:border-box">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;max-width:540px;width:100%;box-shadow:0 20px 40px rgba(0,0,0,0.4);overflow:hidden;display:flex;flex-direction:column;animation:modalPop .2s ease-out">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:6px">✨ AI Article Doctor & Auto-Repair</div>
      <button onclick="closeAiFixModal()" style="background:none;border:none;color:var(--muted);font-size:20px;cursor:pointer;line-height:1;padding:4px">&times;</button>
    </div>
    <div style="padding:20px" id="aiFixModalBody">
      <!-- Content populated dynamically -->
    </div>
  </div>
</div>

<style>
@keyframes modalPop { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
@keyframes pulseGlow { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }
.ai-step-pulse { animation: pulseGlow 1.5s infinite ease-in-out; }
</style>

<script>
var currentFixId = null;
var currentFixTitle = '';
var csrfToken = '<?= csrf_token() ?>';
var baseUrl = '<?= BASE_URL ?>';

function openAiFixModal(id, title) {
  currentFixId = id;
  currentFixTitle = title;
  var modal = document.getElementById('aiFixModal');
  var body = document.getElementById('aiFixModalBody');
  modal.style.display = 'flex';
  
  body.innerHTML = `
    <div style="margin-bottom:14px">
      <div style="font-size:12px;color:var(--muted)">Target Artikel:</div>
      <div style="font-weight:700;font-size:14px;color:var(--main);margin-top:2px">${escapeHtml(title)}</div>
    </div>
    <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px;font-size:12.5px;color:var(--muted);line-height:1.6;margin-bottom:16px">
      <div style="font-weight:700;color:var(--main);margin-bottom:6px">🤖 Apa yang akan dilakukan AI?</div>
      <div>✓ Menganalisis & memperluas konten dengan <b>ag/gemini-3.6-flash-high</b></div>
      <div>✓ Merapikan struktur HTML (Heading, Kode, Tips, Paragraf)</div>
      <div>✓ Menentukan kategori paling akurat & Excerpt SEO</div>
      <div>✓ Membuat thumbnail 3D resolusi tinggi jika artikel belum memiliki gambar</div>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="btn btn-ghost" onclick="closeAiFixModal()" style="padding:8px 14px;font-size:12px">Batal</button>
      <button class="btn btn-primary" id="btnExecuteFix" onclick="startAiFix()" style="padding:8px 16px;font-size:12px;font-weight:700">⚡ Mulai Perbaiki Sekarang</button>
    </div>
  `;
}

function closeAiFixModal() {
  document.getElementById('aiFixModal').style.display = 'none';
}

function startAiFix() {
  var body = document.getElementById('aiFixModalBody');
  body.innerHTML = `
    <div style="text-align:center;padding:24px 10px">
      <div style="font-size:36px;margin-bottom:12px" class="ai-step-pulse">🤖✨</div>
      <div style="font-weight:700;font-size:15px;color:var(--main);margin-bottom:6px" id="aiStepTitle">Sedang Memperbaiki Artikel...</div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:18px" id="aiStepDesc">AI sedang menganalisis isi artikel dan menyusun pembaruan konten...</div>
      <div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:10px;text-align:left;font-size:11.5px;color:var(--muted);line-height:1.8">
        <div id="step-1">⏳ 1. Menganalisis konten & struktur semantik...</div>
        <div id="step-2">⏳ 2. Menyempurnakan konten & SEO dengan ag/gemini-3.6-flash-high...</div>
        <div id="step-3">⏳ 3. Memeriksa & membuat thumbnail AI 3D jika diperlukan...</div>
        <div id="step-4">⏳ 4. Menyimpan data dan menyinkronkan database...</div>
      </div>
    </div>
  `;

  setTimeout(function(){
    var s1 = document.getElementById('step-1');
    if(s1) s1.innerHTML = '✅ 1. Menganalisis konten & struktur semantik (Selesai)';
    var s2 = document.getElementById('step-2');
    if(s2) s2.innerHTML = '<span style="color:var(--primary);font-weight:700">⏳ 2. Menyempurnakan konten & SEO dengan ag/gemini-3.6-flash-high...</span>';
  }, 2000);

  var fd = new FormData();
  fd.append('id', currentFixId);
  fd.append('csrf', csrfToken);

  fetch(baseUrl + '/admin/ai-fix-post', {
    method: 'POST',
    body: fd
  })
  .then(function(res){ return res.json(); })
  .then(function(data){
    if(!data.ok){
      showAiFixError(data.error || 'Terjadi kesalahan saat memproses perbaikan.');
      return;
    }
    showAiFixSuccess(data);
  })
  .catch(function(err){
    showAiFixError('Gagal menghubungi server: ' + err.message);
  });
}

function showAiFixSuccess(data) {
  var body = document.getElementById('aiFixModalBody');
  var changelogHtml = '';
  if(data.changelog && data.changelog.length > 0){
    changelogHtml = '<ul style="margin:6px 0 0 0;padding-left:18px;font-size:12px;line-height:1.6;color:var(--main)">';
    data.changelog.forEach(function(item){
      changelogHtml += '<li>' + escapeHtml(item) + '</li>';
    });
    changelogHtml += '</ul>';
  } else {
    changelogHtml = '<div style="font-size:12px;color:var(--muted)">Konten, format, dan kategori berhasil diselaraskan.</div>';
  }

  var thumbHtml = '';
  if(data.thumbnail){
    thumbHtml = `
      <div style="margin-top:10px">
        <div style="font-size:11px;font-weight:700;color:var(--muted);margin-bottom:4px">🖼️ Thumbnail:</div>
        <img src="${escapeHtml(data.thumbnail)}" alt="Thumbnail Preview" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
      </div>
    `;
  }

  body.innerHTML = `
    <div style="text-align:center;margin-bottom:14px">
      <div style="font-size:32px;margin-bottom:4px">🎉</div>
      <div style="font-weight:700;font-size:16px;color:#16a34a">Artikel Berhasil Diperbaiki!</div>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">Pembaruan telah disimpan ke database & live di website.</div>
    </div>

    <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:12px">
      <div style="font-weight:700;font-size:13px;color:var(--main)">${escapeHtml(data.title)}</div>
      <div style="font-size:11px;color:var(--muted);margin-top:4px;display:flex;gap:6px;flex-wrap:wrap">
        <span class="badge" style="background:#16a34a;font-size:10px">Published</span>
        <span>📁 Kategori: <b>${escapeHtml(data.cat_name || '-')}</b></span>
        ${data.word_count_before ? `<span>📊 ${data.word_count_before} kata ➔ <b>${data.word_count_after} kata</b></span>` : ''}
      </div>
      ${thumbHtml}
    </div>

    <div style="background:rgba(22,163,74,0.08);border:1px solid rgba(22,163,74,0.25);border-radius:8px;padding:12px;margin-bottom:16px">
      <div style="font-weight:700;font-size:12px;color:#16a34a">📋 Keterangan Perbaikan dari AI:</div>
      ${changelogHtml}
    </div>

    <div style="display:flex;gap:8px;justify-content:space-between;align-items:center">
      <a href="${baseUrl}/post/${escapeHtml(data.slug)}" target="_blank" class="btn btn-primary" style="padding:8px 14px;font-size:12px;font-weight:700">🌐 Lihat Artikel Live</a>
      <button class="btn btn-ghost" onclick="location.reload()" style="padding:8px 14px;font-size:12px">🔄 Selesai & Refresh</button>
    </div>
  `;
}

function showAiFixError(msg) {
  var body = document.getElementById('aiFixModalBody');
  body.innerHTML = `
    <div style="text-align:center;padding:14px 10px">
      <div style="font-size:32px;margin-bottom:8px">⚠️</div>
      <div style="font-weight:700;font-size:15px;color:#ef4444;margin-bottom:6px">Gagal Memperbaiki Artikel</div>
      <div style="font-size:12px;color:var(--muted);background:var(--bg);border:1px solid var(--border);padding:10px;border-radius:8px;margin-bottom:16px;text-align:left;word-break:break-word">
        ${escapeHtml(msg)}
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button class="btn btn-ghost" onclick="closeAiFixModal()" style="padding:8px 14px;font-size:12px">Tutup</button>
        <button class="btn btn-primary" onclick="openAiFixModal(currentFixId, currentFixTitle)" style="padding:8px 14px;font-size:12px">Coba Lagi</button>
      </div>
    </div>
  `;
}

function triggerAiPublisher() {
  const btn = document.getElementById('btnTriggerAi');
  if (!confirm('Apakah Anda ingin memicu pembuatan dan penerbitan 1 artikel baru secara otomatis dengan AI sekarang?')) return;
  
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.style.opacity = '0.7';
  btn.innerHTML = '<span>⏳</span> Memproses AI...';

  const formData = new FormData();
  formData.append('csrf', '<?= csrf_token() ?>');

  fetch('<?= BASE_URL ?>/admin/trigger-publisher', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.ok) {
      alert(data.message || 'AI Auto-Publisher berhasil dipicu!');
      setTimeout(() => window.location.reload(), 2000);
    } else {
      alert('Gagal: ' + (data.error || 'Terjadi kesalahan sistem.'));
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.innerHTML = originalHtml;
    }
  })
  .catch(err => {
    alert('Error koneksi: ' + err.message);
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.innerHTML = originalHtml;
  });
}

function escapeHtml(str) {
  if(!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
</script>


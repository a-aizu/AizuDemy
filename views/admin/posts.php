<style>
.post-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.post-filter-form{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.post-filter-input{flex:1;min-width:180px}
.post-filter-select{min-width:140px}
.post-filter-btns{display:flex;gap:6px}
.post-form-actions{display:flex;gap:10px;margin-top:8px;flex-wrap:wrap}

@media(max-width:768px){
  .post-filter-form{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .post-filter-input{grid-column:span 2;width:100%!important;min-width:100%!important}
  .post-filter-select{width:100%!important;min-width:100%!important}
  .post-filter-btns{grid-column:span 2;display:grid;grid-template-columns:1fr 1fr;gap:8px;width:100%}
  .post-filter-btns .btn{width:100%;justify-content:center}
  .post-form-grid{grid-template-columns:1fr;gap:10px}
  .post-form-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .post-form-actions .btn{width:100%;justify-content:center}
}
</style>

<div class="admin-wrap" style="max-width:1280px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap">
    <div>
      <h2 style="margin:0;font-size:20px">📝 Kelola Artikel</h2>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">Total <?= count($all) ?> artikel ditemukan</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn btn-primary" href="?action=new">+ Tulis Artikel</a>
      <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin">Dashboard</a>
    </div>
  </div>

  <?php if(!empty($msg)): ?>
    <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <?php if(($action ?? '')==='new' || ($action ?? '')==='edit'): $edit = $edit ?? null; ?>
    <form method="post" enctype="multipart/form-data" class="admin-card" style="display:flex;flex-direction:column;gap:14px;padding:20px">
      <input type="hidden" name="form" value="save">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
      
      <div>
        <label class="label" style="font-weight:700">Judul Artikel</label>
        <input class="input" name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="Judul artikel yang menarik...">
      </div>

      <div class="post-form-grid">
        <div>
          <label class="label">Slug (kosongkan = auto)</label>
          <input class="input" name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="cara-belajar-teknologi">
        </div>
        <div>
          <label class="label">Kategori</label>
          <select class="input" name="category_id">
            <option value="">— Tanpa kategori —</option>
            <?php foreach(all_categories() as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (!empty($edit['category_id']) && $edit['category_id']==$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="label">Ringkasan (Excerpt)</label>
        <textarea class="input" name="excerpt" rows="2" placeholder="Ringkasan singkat artikel..."><?= e($edit['excerpt'] ?? '') ?></textarea>
      </div>

      <div class="post-form-grid">
        <div>
          <label class="label">Thumbnail URL</label>
          <input class="input" name="thumbnail" value="<?= e($edit['thumbnail'] ?? '') ?>" placeholder="https://aizudemy.net/assets/uploads/gambar.png">
        </div>
        <div>
          <label class="label">Atau Upload Thumbnail Baru (Maks. 5 MB)</label>
          <input class="input" type="file" name="thumbnail_file" accept="image/jpeg,image/png,image/webp">
        </div>
      </div>

      <?php if(!empty($edit['thumbnail'])): ?>
        <div>
          <label class="label">Preview Thumbnail Saat Ini:</label>
          <img src="<?= e($edit['thumbnail']) ?>" alt="Preview" style="width:min(100%,360px);aspect-ratio:16/9;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
        </div>
      <?php endif; ?>

      <div>
        <label class="label" style="font-weight:700">Konten Artikel (HTML)</label>
        <textarea class="input" name="content" rows="14" required placeholder="Tulis isi artikel di sini..."><?= e($edit['content'] ?? '') ?></textarea>
      </div>

      <div style="max-width:300px;width:100%">
        <label class="label" style="font-weight:700">Status Publikasi</label>
        <select class="input" name="status">
          <option value="publish" <?= (($edit['status'] ?? 'publish')==='publish'?'selected':'') ?>>Publish</option>
          <option value="draft" <?= (($edit['status'] ?? '')==='draft'?'selected':'') ?>>Draft</option>
        </select>
      </div>

      <div class="post-form-actions">
        <button class="btn btn-primary" type="submit" style="padding:10px 22px;font-weight:700">💾 Simpan Artikel</button>
        <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts">Batal</a>
      </div>
    </form>

  <?php else: ?>

    <!-- FILTER BAR -->
    <div class="admin-card" style="margin-bottom:16px;padding:14px 18px">
      <form method="get" action="<?= BASE_URL ?>/admin/posts" class="post-filter-form">
        <div class="post-filter-input">
          <input class="input" type="text" name="q" value="<?= e($qFilter ?? '') ?>" placeholder="⌕ Cari judul, slug, atau kata kunci..." style="padding:8px 12px;font-size:13px">
        </div>

        <div class="post-filter-select">
          <select class="input" name="cat" style="padding:8px 12px;font-size:13px">
            <option value="">Semua Kategori</option>
            <option value="none" <?= ($catFilter ?? '')==='none'?'selected':'' ?>>— Tanpa Kategori —</option>
            <?php foreach(all_categories() as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ($catFilter ?? '')===(string)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="post-filter-select">
          <select class="input" name="status" style="padding:8px 12px;font-size:13px">
            <option value="">Semua Status</option>
            <option value="publish" <?= ($statusFilter ?? '')==='publish'?'selected':'' ?>>Publish</option>
            <option value="draft" <?= ($statusFilter ?? '')==='draft'?'selected':'' ?>>Draft</option>
          </select>
        </div>

        <div class="post-filter-btns">
          <button class="btn btn-primary" type="submit" style="padding:8px 16px;font-size:13px">🔍 Filter</button>
          <?php if(!empty($qFilter) || !empty($catFilter) || !empty($statusFilter)): ?>
            <a class="btn btn-ghost" href="<?= BASE_URL ?>/admin/posts" style="padding:8px 12px;font-size:13px">✕ Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- POSTS TABLE -->
    <div class="admin-card" style="padding:0;overflow:hidden">
      <div class="table-responsive">
        <table class="table" style="width:100%;margin:0;min-width:620px">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th style="width:70px">Thumb</th>
              <th>Judul Artikel</th>
              <th style="width:140px">Kategori</th>
              <th style="width:90px">Status</th>
              <th style="width:80px">Views</th>
              <th style="width:130px;text-align:right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($all as $r): ?>
              <tr>
                <td><small style="color:var(--muted)"><?= (int)$r['id'] ?></small></td>
                <td>
                  <?php if(!empty($r['thumbnail'])): ?>
                    <img src="<?= e($r['thumbnail']) ?>" alt="" style="width:48px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--border)" loading="lazy">
                  <?php else: ?>
                    <span style="font-size:10px;color:var(--muted);background:rgba(0,0,0,0.05);padding:4px 6px;border-radius:4px">No img</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>/post/<?= e($r['slug']) ?>" target="_blank" style="font-weight:600;color:var(--main);text-decoration:none;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                    <?= e($r['title']) ?>
                  </a>
                  <div style="font-size:11px;color:var(--muted);margin-top:2px">
                    <?= e($r['slug']) ?> · <?= e(date('d M Y', strtotime($r['created_at']))) ?>
                  </div>
                </td>
                <td>
                  <?php if(!empty($r['cat_name'])): ?>
                    <span style="font-size:11.5px;color:var(--main);background:rgba(0,90,240,0.06);padding:3px 8px;border-radius:6px;white-space:nowrap">📁 <?= e($r['cat_name']) ?></span>
                  <?php else: ?>
                    <span style="font-size:11px;color:#dc2626;white-space:nowrap">⚠️ Tanpa Kategori</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge" style="background:<?= $r['status']=='publish'?'#16a34a':'#6b7280' ?>;font-size:11px;padding:3px 8px">
                    <?= e(ucfirst($r['status'])) ?>
                  </span>
                </td>
                <td><small><b><?= number_format((int)$r['views']) ?></b></small></td>
                <td style="text-align:right;white-space:nowrap">
                  <a class="btn btn-ghost" href="?action=edit&id=<?= (int)$r['id'] ?>" style="padding:4px 8px;font-size:11.5px">Edit</a>
                  <a class="btn btn-ghost" href="?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('Hapus artikel ini?')" style="padding:4px 8px;font-size:11.5px;color:#dc2626">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if(empty($all)): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:32px 16px;color:var(--muted)">
                  <div style="font-size:24px;margin-bottom:6px">📭</div>
                  <div>Tidak ada artikel yang sesuai dengan filter pencarian.</div>
                  <?php if(!empty($qFilter) || !empty($catFilter) || !empty($statusFilter)): ?>
                    <a href="<?= BASE_URL ?>/admin/posts" style="display:inline-block;margin-top:8px;font-size:12px;color:var(--primary);text-decoration:underline">Reset Filter</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>

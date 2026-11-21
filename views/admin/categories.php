<div class="admin-wrap" style="max-width:1100px">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px">
    <div>
      <h2 style="margin:0">📂 Kelola Kategori</h2>
      <div style="font-size:13px;color:var(--muted);margin-top:3px">Tambah, edit, dan atur kategori artikel AizuDemy</div>
    </div>
    <button type="button" class="btn btn-primary" onclick="openCatModal('new')">+ Tambah Kategori</button>
  </div>

  <?php if(isset($_GET['success'])): ?>
    <div style="padding:12px 16px;background:rgba(22,163,74,0.1);border:1px solid #16a34a;color:#16a34a;border-radius:8px;margin-bottom:16px;font-size:13px">
      ✅ Data kategori berhasil diperbarui!
    </div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Slug</th>
            <th>Deskripsi</th>
            <th>Jumlah Artikel</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($categories as $c): ?>
            <tr>
              <td>#<?= (int)$c['id'] ?></td>
              <td>
                <a href="<?= BASE_URL ?>/category/<?= e($c['slug']) ?>" target="_blank" style="font-weight:700;color:var(--main)">
                  <?= e($c['name']) ?>
                </a>
              </td>
              <td><code><?= e($c['slug']) ?></code></td>
              <td style="color:var(--muted);font-size:13px"><?= e($c['description'] ?? '-') ?></td>
              <td>
                <span class="badge" style="background:var(--primary);color:#fff">
                  <?= (int)($c['post_count'] ?? 0) ?> Artikel
                </span>
              </td>
              <td style="white-space:nowrap">
                <button type="button" class="btn btn-ghost" style="padding:4px 8px;font-size:12px" 
                        onclick='openCatModal("edit", <?= json_encode($c) ?>)'>Edit</button>
                <a class="btn btn-ghost" style="padding:4px 8px;font-size:12px;color:#dc2626" 
                   href="<?= BASE_URL ?>/admin/categories?action=delete&id=<?= (int)$c['id'] ?>" 
                   onclick="return confirm('Hapus kategori <?= e($c['name']) ?>?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; if(empty($categories)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Belum ada kategori yang dibuat.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Dialog Add/Edit Category -->
<div id="catModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:99;align-items:center;justify-content:center;padding:20px">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;max-width:440px;width:100%;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,0.4)">
    <h3 id="catModalTitle" style="margin:0 0 16px">Tambah Kategori</h3>
    <form method="POST" action="<?= BASE_URL ?>/admin/categories">
      <input type="hidden" name="action" id="catFormAction" value="save">
      <input type="hidden" name="id" id="catFormId" value="">
      
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px">Nama Kategori</label>
        <input type="text" name="name" id="catFormName" class="form-input" required placeholder="Contoh: Cyber Security">
      </div>
      
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px">Slug (URL)</label>
        <input type="text" name="slug" id="catFormSlug" class="form-input" placeholder="Otomatis jika dikosongkan">
      </div>

      <div style="margin-bottom:20px">
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px">Deskripsi Singkat</label>
        <textarea name="description" id="catFormDesc" class="form-input" rows="3" placeholder="Deskripsi singkat seputar materi di kategori ini..."></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px">
        <button type="button" class="btn btn-ghost" onclick="closeCatModal()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Kategori</button>
      </div>
    </form>
  </div>
</div>

<script>
function openCatModal(mode, data = null){
  const modal = document.getElementById('catModal');
  modal.style.display = 'flex';
  if(mode === 'edit' && data){
    document.getElementById('catModalTitle').innerText = 'Edit Kategori';
    document.getElementById('catFormAction').value = 'update';
    document.getElementById('catFormId').value = data.id;
    document.getElementById('catFormName').value = data.name || '';
    document.getElementById('catFormSlug').value = data.slug || '';
    document.getElementById('catFormDesc').value = data.description || '';
  } else {
    document.getElementById('catModalTitle').innerText = 'Tambah Kategori Baru';
    document.getElementById('catFormAction').value = 'save';
    document.getElementById('catFormId').value = '';
    document.getElementById('catFormName').value = '';
    document.getElementById('catFormSlug').value = '';
    document.getElementById('catFormDesc').value = '';
  }
}
function closeCatModal(){
  document.getElementById('catModal').style.display = 'none';
}
</script>

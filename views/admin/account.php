<div class="admin-wrap" style="max-width:600px">
  <div style="margin-bottom:18px">
    <h2 style="margin:0">⚙️ Pengaturan Akun Admin</h2>
    <div style="font-size:13px;color:var(--muted);margin-top:3px">Ubah nama pengguna & kata sandi akses admin AizuDemy</div>
  </div>

  <?php if(isset($_GET['success'])): ?>
    <div style="padding:12px 16px;background:rgba(22,163,74,0.1);border:1px solid #16a34a;color:#16a34a;border-radius:8px;margin-bottom:16px;font-size:13px">
      ✅ Akun admin berhasil diperbarui! Silakan gunakan password baru pada login berikutnya.
    </div>
  <?php endif; ?>

  <?php if(!empty($accountError)): ?>
    <div style="padding:12px 16px;background:rgba(220,38,38,0.1);border:1px solid #dc2626;color:#dc2626;border-radius:8px;margin-bottom:16px;font-size:13px">
      ⚠️ <?= e($accountError) ?>
    </div>
  <?php endif; ?>

  <div class="admin-card">
    <form method="POST" action="<?= BASE_URL ?>/admin/account">
      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px">Username Admin</label>
        <input type="text" name="username" class="form-input" value="<?= e($_SESSION['aizudemy_admin'] ?? 'admin') ?>" required>
      </div>

      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px">Password Baru</label>
        <input type="password" name="new_password" class="form-input" placeholder="Biarkan kosong jika tidak ingin mengubah password">
      </div>

      <div style="margin-bottom:20px">
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px">Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" class="form-input" placeholder="Ketik ulang password baru">
      </div>

      <div style="display:flex;justify-content:flex-end">
        <button type="submit" class="btn btn-primary">Simpan Perubahan Akun</button>
      </div>
    </form>
  </div>
</div>

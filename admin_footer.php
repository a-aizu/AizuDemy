</div></main>

<footer style="padding:14px;text-align:center;font-size:11px;color:var(--muted);border-top:1px solid var(--border);display:flex;justify-content:center;gap:12px;align-items:center;flex-wrap:wrap">
  <span>© <?= date("Y") ?> AizuDemy Admin</span>
  <span>·</span>
  <a href="#" onclick="toggleShortcutModal(); return false;" style="color:var(--muted);text-decoration:underline">⌨️ Pintasan Keyboard (Shift + ?)</a>
  <span>·</span>
  <a href="<?= BASE_URL ?>/" style="color:var(--muted)">Kembali ke blog →</a>
</footer>

<!-- Modal Pintasan Keyboard / Hotkeys -->
<div id="shortcutModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.65);z-index:999;align-items:center;justify-content:center;padding:20px">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:16px;max-width:480px;width:100%;padding:24px;box-shadow:0 12px 36px rgba(0,0,0,0.5)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <h3 style="margin:0;font-size:18px;display:flex;align-items:center;gap:8px">⌨️ Pintasan Keyboard (Hotkeys)</h3>
      <button type="button" class="btn btn-ghost" onclick="toggleShortcutModal()" style="padding:4px 8px;font-size:16px">&times;</button>
    </div>
    
    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)">
        <span>⚡ Trigger AI Auto-Publisher</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">Shift + A</kbd>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)">
        <span>📝 Tulis Artikel Baru (Manual)</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">Shift + N</kbd>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)">
        <span>📊 Buka Dashboard Utama</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">Shift + D</kbd>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)">
        <span>📝 Buka Kelola Artikel</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">Shift + P</kbd>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)">
        <span>📂 Buka Kelola Kategori</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">Shift + C</kbd>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)">
        <span>🤖 Buka Pengaturan AI</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">Shift + S</kbd>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed var(--border)">
        <span>🔍 Fokuskan Pencarian</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">/</kbd>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0">
        <span>❓ Buka / Tutup Bantuan Hotkeys</span>
        <kbd style="background:var(--bg);border:1px solid var(--border);padding:2px 8px;border-radius:6px;font-family:monospace;font-weight:700">Shift + ?</kbd>
      </div>
    </div>

    <div style="margin-top:20px;text-align:right">
      <button type="button" class="btn btn-primary" onclick="toggleShortcutModal()">Tutup</button>
    </div>
  </div>
</div>

<script>
function toggleShortcutModal(){
  const modal = document.getElementById('shortcutModal');
  if(!modal) return;
  modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
}

(function(){
  function getTheme(){ return document.documentElement.getAttribute("data-theme")||"dark"; }
  function applyTheme(t){
    document.documentElement.setAttribute("data-theme", t);
    try{ localStorage.setItem("aizudemy_theme", t); }catch(e){}
    document.cookie = "aizudemy_theme="+encodeURIComponent(t)+"; path=/; max-age=31536000; SameSite=Lax";
    var icon = document.querySelector("#themeToggle .icon");
    if(icon) icon.textContent = t==="dark" ? "🌙" : "☀️";
  }
  applyTheme(getTheme());
  
  document.addEventListener("DOMContentLoaded", function(){
    applyTheme(getTheme());
    var btn=document.getElementById("themeToggle");
    if(btn) btn.addEventListener("click", function(){ applyTheme(getTheme()==="dark"?"light":"dark"); });

    // Keyboard Shortcuts Global Handler
    document.addEventListener("keydown", function(e){
      // Abaikan jika sedang mengetik di input, textarea, atau contenteditable
      const activeEl = document.activeElement;
      const isInput = activeEl && (activeEl.tagName === "INPUT" || activeEl.tagName === "TEXTAREA" || activeEl.isContentEditable);

      if(!isInput && e.key === "/"){
        e.preventDefault();
        const searchInput = document.querySelector("input[name='q']") || document.querySelector("input[type='search']");
        if(searchInput) searchInput.focus();
        return;
      }

      if(e.shiftKey){
        const key = e.key.toUpperCase();
        if(key === "?" || key === "/"){
          e.preventDefault();
          toggleShortcutModal();
        } else if(!isInput && key === "A"){
          e.preventDefault();
          if(typeof triggerAiPublisher === "function"){
            triggerAiPublisher();
          } else {
            window.location.href = "<?= BASE_URL ?>/admin?trigger_ai=1";
          }
        } else if(!isInput && key === "N"){
          e.preventDefault();
          window.location.href = "<?= BASE_URL ?>/admin/posts?action=new";
        } else if(!isInput && key === "D"){
          e.preventDefault();
          window.location.href = "<?= BASE_URL ?>/admin";
        } else if(!isInput && key === "P"){
          e.preventDefault();
          window.location.href = "<?= BASE_URL ?>/admin/posts";
        } else if(!isInput && key === "C"){
          e.preventDefault();
          window.location.href = "<?= BASE_URL ?>/admin/categories";
        } else if(!isInput && key === "S"){
          e.preventDefault();
          window.location.href = "<?= BASE_URL ?>/admin/ai-settings";
        }
      }
    });

    // Cek jika ada query ?trigger_ai=1 dari Web App Shortcut
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('trigger_ai') === '1'){
      if(typeof triggerAiPublisher === "function"){
        triggerAiPublisher();
      }
    }
  });
})();
</script>
</body></html>

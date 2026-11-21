</div></main>
<footer class="admin-footer" style="padding:18px 16px;text-align:center;font-size:12px;color:var(--muted);border-top:1px solid var(--border);box-sizing:border-box">
  <div style="max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <div>© <?= date("Y") ?> <b>AizuDemy</b> Admin Console · All Systems Operational</div>
    <div style="display:flex;gap:12px;align-items:center">
      <a href="<?= BASE_URL ?>/" target="_blank" style="color:var(--primary);text-decoration:none;font-weight:600">🌐 Kunjungi Blog</a>
      <span>·</span>
      <a href="<?= BASE_URL ?>/sitemap.xml" target="_blank" style="color:var(--muted);text-decoration:none">Sitemap</a>
      <span>·</span>
      <a href="<?= BASE_URL ?>/admin/logout" style="color:#dc2626;text-decoration:none">Logout</a>
    </div>
  </div>
</footer>
<style>
@media(max-width: 900px){
  .admin-footer{margin-bottom:calc(58px + var(--safe-bottom, 0px));padding-bottom:16px}
  .admin-footer > div{justify-content:center;text-align:center;flex-direction:column;gap:6px}
}
</style>
<script>
(function(){
  function getTheme(){ return document.documentElement.getAttribute("data-theme")||"dark"; }
  function applyTheme(t){
    document.documentElement.setAttribute("data-theme", t);
    try{ localStorage.setItem("aizudemy_theme", t); }catch(e){}
    document.cookie = "aizudemy_theme="+encodeURIComponent(t)+"; path=/; max-age=31536000; SameSite=Lax";
    var icon = document.querySelector("#themeToggle .icon");
    if(icon) icon.textContent = t==="dark" ? "🌙" : "☀️";
    
    // Sync drawer theme toggle
    var dIcon = document.getElementById("drawerThemeIcon");
    var dText = document.getElementById("drawerThemeText");
    if(dIcon) dIcon.textContent = t==="dark" ? "🌙" : "☀️";
    if(dText) dText.textContent = t==="dark" ? "Mode Gelap (Aktif)" : "Mode Terang (Aktif)";
  }
  applyTheme(getTheme());
  document.addEventListener("DOMContentLoaded", function(){
    applyTheme(getTheme());
    var btn = document.getElementById("themeToggle");
    if(btn) btn.addEventListener("click", function(){ applyTheme(getTheme()==="dark"?"light":"dark"); });

    var drawerThemeBtn = document.getElementById("drawerThemeToggle");
    if(drawerThemeBtn) drawerThemeBtn.addEventListener("click", function(){ applyTheme(getTheme()==="dark"?"light":"dark"); });

    // Drawer Mobile Toggle
    var drawer = document.getElementById("adminDrawer");
    var overlay = document.getElementById("drawerOverlay");
    var openBtn = document.getElementById("openDrawerBtn");
    var closeBtn = document.getElementById("closeDrawerBtn");
    var moreBtn = document.getElementById("bottomMoreBtn");

    function openDrawer(){
      if(drawer) drawer.classList.add("open");
      if(overlay) overlay.classList.add("open");
      document.body.style.overflow = "hidden";
    }
    function closeDrawer(){
      if(drawer) drawer.classList.remove("open");
      if(overlay) overlay.classList.remove("open");
      document.body.style.overflow = "";
    }

    if(openBtn) openBtn.addEventListener("click", openDrawer);
    if(moreBtn) moreBtn.addEventListener("click", openDrawer);
    if(closeBtn) closeBtn.addEventListener("click", closeDrawer);
    if(overlay) overlay.addEventListener("click", closeDrawer);
    document.addEventListener("keydown", function(e){ if(e.key === "Escape") closeDrawer(); });
  });
})();
</script>
</body></html>

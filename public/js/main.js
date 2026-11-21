document.addEventListener('DOMContentLoaded', function(){
  // search toggle on mobile (640px)
  var searchForm = document.getElementById('searchForm');
  var searchBtn  = document.querySelector('.search-toggle-btn');
  if(searchBtn && searchForm){
    searchBtn.addEventListener('click', function(e){
      e.preventDefault();
      searchForm.classList.toggle('open');
      if(searchForm.classList.contains('open')){
        var inp = searchForm.querySelector('input[name="q"]');
        if(inp) inp.focus();
      }
    });
  }
  // close drawer when clicking a nav link
  document.querySelectorAll('.nav a').forEach(function(a){
    a.addEventListener('click', function(){
      var cb = document.getElementById('navToggle');
      if(cb && window.innerWidth <= 640) cb.checked = false;
    });
  });
  // close search when pressing ESC
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      if(searchForm) searchForm.classList.remove('open');
      var cb = document.getElementById('navToggle');
      if(cb) cb.checked = false;
    }
  });
});

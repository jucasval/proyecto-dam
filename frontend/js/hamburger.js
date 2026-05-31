// js/hamburger.js — Menú hamburguesa para móvil

(function() {
  function init() {
    const sidebar  = document.querySelector('.sidebar');
    const topbar   = document.querySelector('.topbar');
    if (!sidebar || !topbar) return;

    // Crear overlay si no existe
    let overlay = document.getElementById('sidebar-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'sidebar-overlay';
      overlay.className = 'sidebar-overlay';
      document.body.appendChild(overlay);
    }

    // Crear botón hamburguesa si no existe
    let hamburger = document.getElementById('hamburger');
    if (!hamburger) {
      hamburger = document.createElement('button');
      hamburger.id = 'hamburger';
      hamburger.className = 'hamburger';
      hamburger.setAttribute('aria-label', 'Abrir menú');
      hamburger.innerHTML = '☰';
      topbar.insertBefore(hamburger, topbar.firstChild);
    }

    function abrirSidebar() {
      sidebar.classList.add('open');
      overlay.classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function cerrarSidebar() {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
      document.body.style.overflow = '';
    }

    hamburger.addEventListener('click', function(e) {
      e.stopPropagation();
      if (sidebar.classList.contains('open')) {
        cerrarSidebar();
      } else {
        abrirSidebar();
      }
    });

    overlay.addEventListener('click', cerrarSidebar);

    // Cerrar al pulsar un enlace del sidebar en móvil
    sidebar.querySelectorAll('.nav-item').forEach(function(item) {
      item.addEventListener('click', function() {
        if (window.innerWidth <= 768) cerrarSidebar();
      });
    });

    // Cerrar al girar a landscape
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) cerrarSidebar();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

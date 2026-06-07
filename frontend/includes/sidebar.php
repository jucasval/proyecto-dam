<?php
// frontend/includes/sidebar.php
// $paginaActiva debe estar definida antes de incluir
// $isRoot = true si se incluye desde frontend/index.php
$base = isset($isRoot) && $isRoot ? '' : '../';
?>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <img src="<?= $base ?>img/logo_ies.png" alt="IES Al-Andalus"
            style="width:100px;height:auto;padding:4px 0">
       <div>
          <div class="logo-title">Dpto. Informatica</div>
          <div class="logo-sub" id="curso-activo-label">Cargando...</div>
        </div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Gestion</div>
      <a href="<?= $base ?>index.php"              class="nav-item <?= ($paginaActiva==='dashboard')    ?'active':'' ?>"><span class="nav-icon">⊞</span> Dashboard</a>
      <a href="<?= $base ?>pages/profesores.php"   class="nav-item <?= ($paginaActiva==='profesores')   ?'active':'' ?>"><span class="nav-icon">◎</span> Profesores</a>
      <a href="<?= $base ?>pages/grupos.php"       class="nav-item <?= ($paginaActiva==='grupos')       ?'active':'' ?>"><span class="nav-icon">◧</span> Grupos</a>
      <a href="<?= $base ?>pages/modulos.php"      class="nav-item <?= ($paginaActiva==='modulos')      ?'active':'' ?>"><span class="nav-icon">◫</span> Modulos</a>
      <a href="<?= $base ?>pages/asignaciones.php" class="nav-item <?= ($paginaActiva==='asignaciones') ?'active':'' ?>"><span class="nav-icon">◈</span> Asignaciones</a>
      <div class="nav-section-label" style="margin-top:12px">Administracion</div>
      <a href="<?= $base ?>pages/cursos.php"       class="nav-item <?= ($paginaActiva==='cursos')       ?'active':'' ?>"><span class="nav-icon">◷</span> Cursos</a>
      <a href="<?= $base ?>pages/cargos.php"       class="nav-item <?= ($paginaActiva==='cargos')       ?'active':'' ?>"><span class="nav-icon">◑</span> Cargos</a>
      <a href="<?= $base ?>pages/usuarios.php"     class="nav-item <?= ($paginaActiva==='usuarios')     ?'active':'' ?>"><span class="nav-icon">◉</span> Usuarios</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-footer-text">TFC — DAM 2025</div>
      <div style="margin-top:6px;font-size:11px;color:#94a3b8">
        <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
      </div>
    </div>
  </aside>

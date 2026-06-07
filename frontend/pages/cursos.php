<?php
require_once __DIR__ . '/../../auth.php';
$paginaActiva = 'cursos';
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cursos — Dpto. Informatica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Cursos</h1>
        <span class="page-sub">Gestion de anos lectivos</span>
      </div>
      <a href="../../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
    </header>
    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">Cursos escolares</span>
          <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nuevo curso</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Curso</th>
                <th class="hide-mobile">Inicio</th>
                <th class="hide-mobile">Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-cursos">
              <tr><td colspan="5" class="table-loading">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="modal">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title" id="modal-titulo">Nuevo curso</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="curso-id">
        <div class="form-grid">
          <div class="form-group full">
            <label for="curso-nombre">Nombre del curso</label>
            <input type="text" id="curso-nombre" placeholder="Ej: 2026-2027">
          </div>
          <div class="form-group">
            <label for="curso-inicio">Fecha inicio</label>
            <input type="date" id="curso-inicio">
          </div>
          <div class="form-group">
            <label for="curso-fin">Fecha fin</label>
            <input type="date" id="curso-fin">
          </div>
        </div>
        <div id="seccion-profesores" style="margin-top:18px;display:none">
          <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase">
            Selecciona los profesores a copiar del curso anterior
          </div>
          <div style="border:1px solid var(--border);border-radius:var(--radius-sm);max-height:220px;overflow-y:auto;padding:4px 8px">
            <div id="check-profesores"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarCurso()">Guardar</button>
      </div>
    </div>
  </div>

  <script src="../js/api.js?v=<?= $v ?>"></script>
  <script src="../js/sync.js?v=<?= $v ?>"></script>
  <script src="../js/cursos.js?v=<?= $v ?>"></script>
  <script src="../js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el = document.getElementById('curso-activo-label');
      if (el) el.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

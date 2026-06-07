<?php
require_once __DIR__ . '/../../auth.php';
$paginaActiva = 'profesores';
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profesores — Dpto. Informática</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Profesores</h1>
        <span class="page-sub">Gestión del equipo docente</span>
      </div>
      <a href="../../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
    </header>
    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="card">
        <div class="card-header">
          <div class="toolbar">
            <input type="text" id="buscador" class="search-input" placeholder="Buscar profesor...">
          </div>
          <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nuevo profesor</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Apellidos, Nombre</th>
                <th>Puesto</th>
                <th class="hide-mobile">Horas asignadas</th>
                <th class="hide-mobile">Horas libres</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-profesores">
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
        <span class="modal-title" id="modal-titulo">Nuevo profesor</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="prof-id">
        <div class="form-grid">
          <div class="form-group">
            <label for="prof-nombre">Nombre</label>
            <input type="text" id="prof-nombre" placeholder="Ej: Maria Angeles">
          </div>
          <div class="form-group">
            <label for="prof-apellidos">Apellidos</label>
            <input type="text" id="prof-apellidos" placeholder="Ej: Garcia Lopez">
          </div>
          <div class="form-group">
            <label for="prof-puesto">Puesto</label>
            <select id="prof-puesto">
              <option value="PES">PES</option>
              <option value="PTFP">PTFP</option>
            </select>
          </div>
          <div class="form-group">
            <label for="prof-horas">Horas contrato</label>
            <input type="number" id="prof-horas" value="18" min="1" max="30">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarProfesor()">Guardar</button>
      </div>
    </div>
  </div>

  <script src="../js/api.js?v=<?= $v ?>"></script>
  <script src="../js/sync.js?v=<?= $v ?>"></script>
  <script src="../js/profesores.js?v=<?= $v ?>"></script>
  <script src="../js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el = document.getElementById('curso-activo-label');
      if (el) el.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

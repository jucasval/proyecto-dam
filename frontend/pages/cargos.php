<?php
require_once __DIR__ . '/../../auth.php';
$paginaActiva = 'cargos';
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cargos — Dpto. Informatica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Cargos</h1>
        <span class="page-sub">Gestion de cargos y dedicaciones</span>
      </div>
      <a href="../../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
    </header>
    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">Catalogo de cargos</span>
          <button class="btn btn-primary" onclick="abrirModalCargo()">+ Nuevo cargo</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Cargo</th>
                <th>Horas por defecto</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-cargos">
              <tr><td colspan="3" class="table-loading">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">Asignaciones del curso activo</span>
          <button class="btn btn-primary" onclick="abrirModalAsignacion()">+ Asignar cargo</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Profesor</th>
                <th class="hide-tablet">Puesto</th>
                <th>Cargo</th>
                <th>Horas</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-asignaciones-cargo">
              <tr><td colspan="5" class="table-loading">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="modal-cargo">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title" id="titulo-cargo">Nuevo cargo</span>
        <button class="modal-close" onclick="closeModal('modal-cargo')">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cargo-id">
        <div class="form-grid">
          <div class="form-group full">
            <label for="cargo-nombre">Nombre del cargo</label>
            <input type="text" id="cargo-nombre" placeholder="Ej: Jefatura de Estudios">
          </div>
          <div class="form-group">
            <label for="cargo-horas">Horas por defecto</label>
            <input type="number" id="cargo-horas" value="0" min="0" max="20" step="0.5">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal('modal-cargo')">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarCargo()">Guardar</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-asignacion">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title" id="titulo-asignacion">Asignar cargo</span>
        <button class="modal-close" onclick="closeModal('modal-asignacion')">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="asig-cargo-id">
        <div class="form-grid">
          <div class="form-group full">
            <label for="asig-cargo-profesor">Profesor</label>
            <select id="asig-cargo-profesor">
              <option value="">— Selecciona un profesor —</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="asig-cargo-cargo">Cargo</label>
            <select id="asig-cargo-cargo" onchange="autocompletarHoras()">
              <option value="">— Selecciona un cargo —</option>
            </select>
          </div>
          <div class="form-group">
            <label for="asig-cargo-horas">Horas</label>
            <input type="number" id="asig-cargo-horas" value="0" min="0" max="20" step="0.5">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal('modal-asignacion')">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarAsignacionCargo()">Guardar</button>
      </div>
    </div>
  </div>

  <script src="../js/api.js?v=<?= $v ?>"></script>
  <script src="../js/sync.js?v=<?= $v ?>"></script>
  <script src="../js/cargos.js?v=<?= $v ?>"></script>
  <script src="../js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el = document.getElementById('curso-activo-label');
      if (el) el.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

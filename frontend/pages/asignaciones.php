<?php
require_once __DIR__ . '/../../auth.php';
$paginaActiva = 'asignaciones';
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asignaciones — Dpto. Informatica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Asignaciones</h1>
        <span class="page-sub">Asignacion de modulos a profesores por grupo</span>
      </div>
      <a href="../../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
    </header>
    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="card">
        <div class="card-header">
          <div class="toolbar">
            <input type="text" id="buscador" class="search-input" placeholder="Buscar profesor o modulo...">
            <select id="filtro-grupo" class="search-input" style="width:180px">
              <option value="">Todos los grupos</option>
            </select>
            <select id="filtro-ciclo" class="search-input" style="width:140px">
              <option value="">Todos los ciclos</option>
              <option value="DAM">DAM</option>
              <option value="DAW">DAW</option>
              <option value="ASIR">ASIR</option>
              <option value="SMR">SMR</option>
              <option value="FPB">FPB</option>
              <option value="ESO">ESO</option>
              <option value="BTO">BTO</option>
            </select>
          </div>
          <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nueva asignacion</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Profesor</th>
                <th class="hide-tablet">Puesto</th>
                <th>Modulo</th>
                <th class="hide-tablet">Grupo</th>
                <th class="hide-mobile">Horas</th>
                <th class="hide-tablet">Desdoble</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-asignaciones">
              <tr><td colspan="7" class="table-loading">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
        <div style="padding:12px 20px;border-top:1px solid var(--border);font-size:13px;color:var(--text-secondary)">
          Total: <strong id="total-count">0</strong> asignaciones
        </div>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="modal">
    <div class="modal">
      <div class="modal-header">
        <span class="modal-title" id="modal-titulo">Nueva asignacion</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="asig-id">
        <div class="form-grid">
          <div class="form-group full">
            <label for="asig-grupo">1. Selecciona el grupo</label>
            <select id="asig-grupo" onchange="onGrupoChange()">
              <option value="">— Selecciona un grupo —</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="asig-modulo">2. Selecciona el modulo</label>
            <select id="asig-modulo" onchange="onModuloChange()" disabled>
              <option value="">— Primero elige un grupo —</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="asig-profesor">3. Selecciona el profesor</label>
            <select id="asig-profesor" onchange="onProfesorChange()" disabled>
              <option value="">— Primero elige un modulo —</option>
            </select>
          </div>
          <div class="form-group">
            <label for="asig-horas">Horas semanales</label>
            <input type="number" id="asig-horas" value="0" min="0" max="20" step="0.5" readonly
              style="background:#f8fafc;color:var(--text-secondary)">
          </div>
          <div class="form-group">
            <label for="asig-desdoble">Es desdoble?</label>
            <select id="asig-desdoble">
              <option value="0">No</option>
              <option value="1">Si</option>
            </select>
          </div>
          <div class="form-group full">
            <label for="asig-obs">Observaciones</label>
            <input type="text" id="asig-obs" placeholder="Opcional">
          </div>
        </div>
        <div id="aviso-horas" style="margin-top:14px;display:none"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarAsignacion()">Guardar</button>
      </div>
    </div>
  </div>

  <script src="../js/api.js?v=<?= $v ?>"></script>
  <script src="../js/sync.js?v=<?= $v ?>"></script>
  <script src="../js/asignaciones.js?v=<?= $v ?>"></script>
  <script src="../js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el = document.getElementById('curso-activo-label');
      if (el) el.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

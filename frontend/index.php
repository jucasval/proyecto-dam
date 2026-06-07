<?php
require_once __DIR__ . '/../auth.php';
$paginaActiva = 'dashboard';
$isRoot = true;
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Dpto. Informatica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Dashboard</h1>
        <span class="page-sub">Resumen del departamento</span>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <button class="btn btn-secondary" onclick="openModal('modal-informes')" style="display:flex;align-items:center;gap:6px">
          <span>🖨</span> Informes
        </button>
        <div class="topbar-badge">
          <span class="badge-dot"></span>
          <span id="curso-activo-badge">Curso activo</span>
        </div>
        <a href="../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
      </div>
    </header>

    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">Profesores</div>
          <div class="stat-value" id="stat-profesores">—</div>
          <div class="stat-hint">PES + PTFP</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Grupos</div>
          <div class="stat-value" id="stat-grupos">—</div>
          <div class="stat-hint">Todos los ciclos</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Modulos</div>
          <div class="stat-value" id="stat-modulos">—</div>
          <div class="stat-hint">Distintos modulos</div>
        </div>
        <div class="stat-card stat-card--accent">
          <div class="stat-label">Asignaciones</div>
          <div class="stat-value" id="stat-asignaciones">—</div>
          <div class="stat-hint">Total registradas</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Horas por profesor</span>
          <a href="pages/profesores.php" class="card-link">Ver todos →</a>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Profesor</th>
                <th class="hide-mobile">Puesto</th>
                <th>Asignadas</th>
                <th>Libres</th>
              </tr>
            </thead>
            <tbody id="tbody-horas">
              <tr><td colspan="4" class="table-loading">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal informes -->
  <div class="modal-overlay" id="modal-informes">
    <div class="modal" style="width:440px;max-width:95vw">
      <div class="modal-header">
        <span class="modal-title">Generar informe</span>
        <button class="modal-close" onclick="closeModal('modal-informes')">✕</button>
      </div>
      <div class="modal-body">
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:20px">
          Informe de horas asignadas por profesor — modulos y cargos del curso activo.
        </p>
        <div style="display:flex;flex-direction:column;gap:12px">
          <div style="border:1px solid var(--border);border-radius:var(--radius-md);padding:16px;display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-weight:600;font-size:13px">🖨 Imprimir</div>
              <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Abre la ventana de impresion del navegador</div>
            </div>
            <button class="btn btn-secondary" onclick="imprimirInforme();closeModal('modal-informes')">Imprimir</button>
          </div>
          <div style="border:1px solid var(--border);border-radius:var(--radius-md);padding:16px;display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-weight:600;font-size:13px">📄 PDF</div>
              <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Descarga el informe en formato PDF</div>
            </div>
            <button class="btn btn-secondary" onclick="exportarPDF();closeModal('modal-informes')">Descargar PDF</button>
          </div>
          <div style="border:1px solid var(--border);border-radius:var(--radius-md);padding:16px;display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-weight:600;font-size:13px">📊 Excel</div>
              <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Descarga en Excel con 3 hojas: Resumen, Modulos y Cargos</div>
            </div>
            <button class="btn btn-primary" onclick="exportarExcel();closeModal('modal-informes')">Descargar Excel</button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal('modal-informes')">Cancelar</button>
      </div>
    </div>
  </div>

  <script src="js/api.js?v=<?= $v ?>"></script>
  <script src="js/dashboard.js?v=<?= $v ?>"></script>
  <script src="js/informe.js?v=<?= $v ?>"></script>
  <script src="js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el  = document.getElementById('curso-activo-label');
      const el2 = document.getElementById('curso-activo-badge');
      if (el)  el.textContent  = c.nombre;
      if (el2) el2.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

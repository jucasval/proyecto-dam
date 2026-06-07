<?php
require_once __DIR__ . '/../../auth.php';
$paginaActiva = 'modulos';
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modulos — Dpto. Informatica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Modulos</h1>
        <span class="page-sub">Catalogo de modulos formativos</span>
      </div>
      <a href="../../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
    </header>
    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="card">
        <div class="card-header">
          <div class="toolbar">
            <input type="text" id="buscador" class="search-input" placeholder="Buscar modulo o codigo...">
          </div>
          <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nuevo modulo</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Nombre del modulo</th>
                <th class="hide-tablet">Codigo</th>
                <th class="hide-tablet">Horas PES</th>
                <th class="hide-tablet">Horas PTFP</th>
                <th class="hide-tablet">Total horas</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-modulos">
              <tr><td colspan="6" class="table-loading">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="modal">
    <div class="modal" style="width:600px;max-width:95vw">
      <div class="modal-header">
        <span class="modal-title" id="modal-titulo">Nuevo modulo</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="mod-id">
        <div class="form-grid">
          <div class="form-group full">
            <label for="mod-nombre">Nombre del modulo</label>
            <input type="text" id="mod-nombre" placeholder="Ej: Bases de datos">
          </div>
          <div class="form-group">
            <label for="mod-codigo">Codigo</label>
            <input type="text" id="mod-codigo" placeholder="Ej: BD" style="font-family:var(--font-mono)">
          </div>
          <div class="form-group"></div>
          <div class="form-group">
            <label for="mod-horas-pes">Horas PES</label>
            <input type="number" id="mod-horas-pes" value="0" min="0" max="30">
          </div>
          <div class="form-group">
            <label for="mod-horas-ptfp">Horas PTFP</label>
            <input type="number" id="mod-horas-ptfp" value="0" min="0" max="30">
          </div>
        </div>
        <div style="margin-top:18px">
          <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em">
            Grupos que imparten este modulo
          </div>
          <div style="border:1px solid var(--border);border-radius:var(--radius-sm);max-height:200px;overflow-y:auto;padding:4px 8px">
            <div id="check-grupos"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarModulo()">Guardar</button>
      </div>
    </div>
  </div>

  <script src="../js/api.js?v=<?= $v ?>"></script>
  <script src="../js/sync.js?v=<?= $v ?>"></script>
  <script src="../js/mod.js?v=<?= $v ?>"></script>
  <script src="../js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el = document.getElementById('curso-activo-label');
      if (el) el.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

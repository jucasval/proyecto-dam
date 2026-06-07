<?php
require_once __DIR__ . '/../../auth.php';
$paginaActiva = 'grupos';
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grupos — Dpto. Informática</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Grupos</h1>
        <span class="page-sub">Ciclos y grupos del departamento</span>
      </div>
      <a href="../../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
    </header>
    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="card">
        <div class="card-header">
          <div class="toolbar">
            <input type="text" id="buscador" class="search-input" placeholder="Buscar grupo...">
            <select id="filtro-ciclo" class="search-input" style="width:160px">
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
          <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nuevo grupo</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th class="hide-tablet">Ciclo</th>
                <th class="hide-tablet">Curso</th>
                <th class="hide-tablet">Modalidad</th>
                <th>Plan de estudios</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-grupos">
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
        <span class="modal-title" id="modal-titulo">Nuevo grupo</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="grupo-id">
        <div class="form-grid">
          <div class="form-group">
            <label for="grupo-nombre">Nombre del grupo</label>
            <input type="text" id="grupo-nombre" placeholder="Ej: 1 DAM">
          </div>
          <div class="form-group">
            <label for="grupo-ciclo">Ciclo</label>
            <select id="grupo-ciclo">
              <option value="DAM">DAM</option>
              <option value="DAW">DAW</option>
              <option value="ASIR">ASIR</option>
              <option value="SMR">SMR</option>
              <option value="FPB">FPB</option>
              <option value="ESO">ESO</option>
              <option value="BTO">BTO</option>
            </select>
          </div>
          <div class="form-group">
            <label for="grupo-curso">Curso</label>
            <select id="grupo-curso">
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
            </select>
          </div>
          <div class="form-group">
            <label for="grupo-modalidad">Modalidad</label>
            <select id="grupo-modalidad">
              <option value="Presencial">Presencial</option>
              <option value="Dual">Dual</option>
              <option value="Semipresencial">Semipresencial</option>
              <option value="Online">Online</option>
            </select>
          </div>
        </div>
        <div style="margin-top:18px">
          <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em">
            Modulos que se imparten en este grupo
          </div>
          <div style="border:1px solid var(--border);border-radius:var(--radius-sm);max-height:220px;overflow-y:auto;padding:4px 8px">
            <div id="check-modulos-grupo"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarGrupo()">Guardar</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-modulos">
    <div class="modal" style="width:700px;max-width:95vw">
      <div class="modal-header">
        <span class="modal-title" id="titulo-modulos">Modulos del grupo</span>
        <button class="modal-close" onclick="closeModal('modal-modulos')">✕</button>
      </div>
      <div class="modal-body">
        <div id="alert-modulos" class="alert" style="margin-bottom:12px;display:none"></div>
        
        <!-- Tabs -->
        <div style="display:flex;gap:8px;border-bottom:1px solid var(--border);margin-bottom:16px">
          <button class="tab-btn active" onclick="cambiarTab('asignados')" 
                  style="padding:10px 16px;border:none;background:none;cursor:pointer;font-weight:500;color:var(--text-secondary);border-bottom:3px solid transparent;transition:all 0.2s"
                  onmouseover="this.style.color='var(--text-primary)'" 
                  onmouseout="this.style.color='var(--text-secondary)'">
            ✓ Asignados (<span id="count-asignados">0</span>)
          </button>
          <button class="tab-btn" onclick="cambiarTab('disponibles')" 
                  style="padding:10px 16px;border:none;background:none;cursor:pointer;font-weight:500;color:var(--text-secondary);border-bottom:3px solid transparent;transition:all 0.2s"
                  onmouseover="this.style.color='var(--text-primary)'" 
                  onmouseout="this.style.color='var(--text-secondary)'">
            + Disponibles (<span id="count-disponibles">0</span>)
          </button>
        </div>

        <!-- Tab: Asignados -->
        <div id="tab-asignados" style="display:block">
          <div style="max-height:350px;overflow-y:auto">
            <div id="lista-modulos-asignados"></div>
          </div>
        </div>

        <!-- Tab: Disponibles -->
        <div id="tab-disponibles" style="display:none">
          <input type="text" id="buscar-disponibles" class="search-input" placeholder="Buscar módulos..." style="width:100%;margin-bottom:12px">
          <div style="max-height:350px;overflow-y:auto">
            <div id="lista-modulos-disponibles"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal('modal-modulos')">Cerrar</button>
      </div>
    </div>
  </div>

  <script src="../js/api.js?v=<?= $v ?>"></script>
  <script src="../js/sync.js?v=<?= $v ?>"></script>
  <script src="../js/grupos.js?v=<?= $v ?>"></script>
  <script src="../js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el = document.getElementById('curso-activo-label');
      if (el) el.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

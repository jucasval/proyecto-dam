<?php
require_once __DIR__ . '/../../auth.php';
$paginaActiva = 'usuarios';
$v = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios — Dpto. Informatica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/main.css?v=<?= $v ?>">
</head>
<body>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1 class="page-title">Usuarios</h1>
        <span class="page-sub">Gestion de acceso al sistema</span>
      </div>
      <a href="../../logout.php" class="btn btn-secondary" style="font-size:12px">Salir</a>
    </header>
    <div class="content-area">
      <div id="alert-box" class="alert"></div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">Usuarios autorizados</span>
          <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nuevo usuario</button>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th class="hide-mobile">Estado</th>
                <th class="hide-mobile">Creado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tbody-usuarios">
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
        <span class="modal-title" id="modal-titulo">Nuevo usuario</span>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="user-id">
        <div class="form-grid">
          <div class="form-group">
            <label for="user-nombre">Nombre completo</label>
            <input type="text" id="user-nombre" placeholder="Ej: Juan Francisco">
          </div>
          <div class="form-group">
            <label for="user-username">Nombre de usuario</label>
            <input type="text" id="user-username" placeholder="Ej: jucasval" style="font-family:var(--font-mono)">
          </div>
          <div class="form-group">
            <label for="user-password">
              Contrasena
              <span id="pass-hint" style="color:var(--text-muted);font-weight:400;display:none"> (dejar en blanco para no cambiar)</span>
            </label>
            <input type="password" id="user-password" placeholder="Minimo 6 caracteres">
          </div>
          <div class="form-group">
            <label for="user-activo">Estado</label>
            <select id="user-activo">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
        <button class="btn btn-primary"   onclick="guardarUsuario()">Guardar</button>
      </div>
    </div>
  </div>

  <script src="../js/api.js?v=<?= $v ?>"></script>
  <script src="../js/usuarios.js?v=<?= $v ?>"></script>
  <script src="../js/hamburger.js?v=<?= $v ?>"></script>
  <script>
    api.get('cursos/activo').then(c => {
      const el = document.getElementById('curso-activo-label');
      if (el) el.textContent = c.nombre;
    }).catch(() => {});
  </script>
</body>
</html>

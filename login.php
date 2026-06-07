<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: frontend/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        require_once __DIR__ . '/api/config/database.php';
        try {
            $db   = getConnection();
            $stmt = $db->prepare("SELECT * FROM usuario WHERE username = ? AND activo = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['username']   = $user['username'];
                $_SESSION['nombre']     = $user['nombre'];
                header('Location: frontend/index.php');
                exit;
            } else {
                $error = 'Usuario o contrasena incorrectos.';
            }
        } catch (Exception $e) {
            $error = 'Error de conexion: ' . $e->getMessage();
        }
    } else {
        $error = 'Introduce usuario y contrasena.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso — Dpto. Informatica</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #f0f2f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.10); padding: 40px 36px; width: 100%; max-width: 400px; }
    .login-header { text-align: center; margin-bottom: 32px; }
    .login-icon  { font-size: 32px; margin-bottom: 12px; }
    .login-title { font-size: 20px; font-weight: 600; color: #0f172a; }
    .login-sub   { font-size: 13px; color: #64748b; margin-top: 4px; }
    .form-group  { display: flex; flex-direction: column; gap: 5px; margin-bottom: 16px; }
    label        { font-size: 12px; font-weight: 500; color: #64748b; }
    input { font-family: 'DM Sans', sans-serif; font-size: 14px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; width: 100%; }
    input:focus  { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
    .btn { width: 100%; padding: 10px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; margin-top: 8px; }
    .btn:hover { background: #1d4ed8; }
    .error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; }
    .footer { text-align: center; margin-top: 24px; font-size: 11px; color: #94a3b8; }
  </style>
</head>
<body>
  <div class="login-card">
    <!-- Dentro de .login-card, antes de .login-header -->
    <div style="text-align:center;margin-bottom:20px">
        <img src="frontend/img/logo.png" alt="IES Al-Andalus"
        style="width:100%;height:auto">
    </div>
    <div class="login-header">
      <div class="login-title">Dpto. Informatica</div>
      <div class="login-sub">Gestion de horas del departamento</div>
    </div>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="username">Usuario</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Introduce tu usuario" autofocus>
      </div>
      <div class="form-group">
        <label for="password">Contrasena</label>
        <input type="password" id="password" name="password" placeholder="Introduce tu contrasena">
      </div>
      <button type="submit" class="btn">Acceder</button>
    </form>
    <div class="footer">TFC — DAM 2025</div>
  </div>
</body>
</html>

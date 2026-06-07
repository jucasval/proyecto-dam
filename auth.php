<?php
// auth.php — en la raiz del proyecto /proyecto/
// Incluir desde frontend/: require_once __DIR__ . '/../auth.php';
// Incluir desde frontend/pages/: require_once __DIR__ . '/../../auth.php';

// Prevenir caché para desarrollo
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Detectar profundidad para redirigir al login correctamente
    $script = $_SERVER['PHP_SELF'];
    if (strpos($script, '/pages/') !== false) {
        header('Location: ../../login.php');
    } elseif (strpos($script, '/frontend/') !== false) {
        header('Location: ../login.php');
    } else {
        header('Location: login.php');
    }
    exit;
}

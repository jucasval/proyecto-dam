<?php
// api/config/database.php

// Detectar si estamos en local o en producción
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = in_array($httpHost, [
    'localhost', 
    '127.0.0.1',
    '192.168.0.48',      // Tu IP local
    '192.168.0.48:80',   // Tu IP local con puerto
    'localhost:8000', 
    'localhost:3000'
]) || strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false;

if ($isLocal) {
    // CREDENCIALES LOCALES
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'asignacion_horas');
    define('DB_USER', 'jjuanf1');
    define('DB_PASS', 'elo02030');
} else {
    // CREDENCIALES INFINITYFREE
    define('DB_HOST', 'sql110.infinityfree.com');
    define('DB_NAME', 'ifo_42009562_asignacion_horas');
    define('DB_USER', 'ifo_42009562');
    define('DB_PASS', 'e1o82036');
}

define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

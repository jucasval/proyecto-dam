<?php
// api/index.php  — Punto de entrada de la API REST

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/database.php';

// Parsear la ruta: /api/profesores, /api/grupos, etc.
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$parts  = explode('/', trim($uri, '/'));

// Estructura esperada: /api/{recurso}/{id?}
$recurso = $parts[1] ?? '';
$id      = isset($parts[2]) && is_numeric($parts[2]) ? (int)$parts[2] : null;
$method  = $_SERVER['REQUEST_METHOD'];

$controllerMap = [
    'profesores'   => 'ProfesorController',
    'grupos'       => 'GrupoController',
    'modulos'      => 'ModuloController',
    'asignaciones' => 'AsignacionController',
];

if (!array_key_exists($recurso, $controllerMap)) {
    http_response_code(404);
    echo json_encode(['error' => "Recurso '$recurso' no encontrado"]);
    exit;
}

$controllerClass = $controllerMap[$recurso];
require_once __DIR__ . "/controllers/{$controllerClass}.php";

$controller = new $controllerClass(getConnection());

switch ($method) {
    case 'GET':
        $id ? $controller->show($id) : $controller->index();
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->store($data);
        break;
    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID requerido']); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->update($id, $data);
        break;
    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID requerido']); exit; }
        $controller->destroy($id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
}

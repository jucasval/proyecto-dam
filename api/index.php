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

// Parsear la ruta correctamente
$uri     = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$base    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
$uri     = substr($uri, strlen($base));
$uri     = trim($uri, '/');
$parts   = $uri !== '' ? explode('/', $uri) : [];

// Estructura esperada: {recurso}/{id?}/{accion?}
$recurso = $parts[0] ?? '';
$id      = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
$method  = $_SERVER['REQUEST_METHOD'];

$controllerMap = [
    'profesores'   => 'ProfesorController',
    'grupos'       => 'GrupoController',
    'modulos'      => 'ModuloController',
    'asignaciones' => 'AsignacionController',
    'cursos'       => 'CursoController',
    'cargos'       => 'CargoController',
];

if (!array_key_exists($recurso, $controllerMap)) {
    http_response_code(404);
    echo json_encode(['error' => "Recurso '$recurso' no encontrado"]);
    exit;
}

$controllerClass = $controllerMap[$recurso];
require_once __DIR__ . "/controllers/{$controllerClass}.php";
$controller = new $controllerClass(getConnection());

// Rutas especiales para profesores
if ($recurso === 'profesores') {
    $segmento = $parts[1] ?? null;
    if ($method === 'GET' && $segmento === 'horas') {
        $controller->horas();
        exit;
    }
}

// Rutas especiales para cursos
if ($recurso === 'cursos') {
    $segmento = $parts[1] ?? null;
    $accion   = $parts[2] ?? null;

    if ($method === 'GET' && $segmento === 'activo') {
        $controller->activo();
        exit;
    }
    if ($method === 'GET' && $id && $accion === 'profesores') {
        $controller->profesores($id);
        exit;
    }
    if ($method === 'PUT' && $id && $accion === 'activar') {
        $controller->activar($id);
        exit;
    }
}

// Rutas especiales para grupos
if ($recurso === 'grupos') {
    $accion = $parts[2] ?? null;
    if ($method === 'GET' && $id && $accion === 'modulos') {
        $controller->modulos($id);
        exit;
    }
}

// Rutas especiales para cargos
if ($recurso === 'cargos') {
    $segmento = $parts[1] ?? null;
    $asigId   = isset($parts[2]) && is_numeric($parts[2]) ? (int)$parts[2] : null;

    // GET /cargos/asignaciones
    if ($method === 'GET' && $segmento === 'asignaciones') {
        $controller->asignaciones();
        exit;
    }
    // POST /cargos/asignaciones
    if ($method === 'POST' && $segmento === 'asignaciones') {
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->asignar($data);
        exit;
    }
    // PUT /cargos/asignaciones/{id}
    if ($method === 'PUT' && $segmento === 'asignaciones' && $asigId) {
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->actualizarAsignacion($asigId, $data);
        exit;
    }
    // DELETE /cargos/asignaciones/{id}
    if ($method === 'DELETE' && $segmento === 'asignaciones' && $asigId) {
        $controller->eliminarAsignacion($asigId);
        exit;
    }
}

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

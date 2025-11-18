<?php
/**
 * API Router - Maneja las rutas de la API REST
 * Similar a FastAPI router structure
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace(dirname($script_name), '', $request_uri);
$path = trim($path, '/');

// Dividir la ruta
$path_parts = explode('/', $path);
$resource = $path_parts[0] ?? '';
$resource_id = $path_parts[1] ?? null;

// Routing
switch ($resource) {
    case 'vehicles':
        require_once 'vehicles.php';
        break;
        
    case 'favorites':
        require_once 'favoritos.php';
        break;
        
    case 'inquiries':
    case 'consultas':
        require_once 'consultas.php';
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint not found',
            'available_endpoints' => [
                '/api/vehicles',
                '/api/vehicles/{id}',
                '/api/favorites',
                '/api/inquiries'
            ]
        ]);
}


<?php
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

requireLogin();

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // GET /api/favorites - Obtener favoritos del usuario
            getFavorites($conn);
            break;
            
        case 'POST':
            // POST /api/favorites - Agregar a favoritos
            createFavorite($conn);
            break;
            
        case 'DELETE':
            // DELETE /api/favorites/{id} - Eliminar favorito
            $favorite_id = $_GET['id'] ?? null;
            if (!$favorite_id) {
                throw new Exception('Favorite ID required');
            }
            deleteFavorite($conn, $favorite_id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function getFavorites($conn) {
    $stmt = $conn->prepare("SELECT f.*, v.* 
        FROM favoritos f
        INNER JOIN vehiculos v ON f.vehiculo_id = v.id
        WHERE f.usuario_id = ?
        ORDER BY f.fecha_creacion DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll();
    
    $result = array_map(function($f) {
        return [
            'id' => (int)$f['id'],
            'user_id' => (int)$f['usuario_id'],
            'vehicle_id' => (int)$f['vehiculo_id'],
            'created_at' => $f['fecha_creacion']
        ];
    }, $favorites);
    
    echo json_encode($result);
}

function createFavorite($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $vehiculo_id = $data['vehicle_id'] ?? $data['vehiculo_id'] ?? 0;
    
    if (!$vehiculo_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vehicle ID required']);
        return;
    }
    
    // Verificar si ya existe (toggle behavior)
    $stmt = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND vehiculo_id = ?");
    $stmt->execute([$_SESSION['user_id'], $vehiculo_id]);
    $existe = $stmt->fetch();
    
    if ($existe) {
        // Si ya existe, eliminarlo (toggle off)
        $stmt = $conn->prepare("DELETE FROM favoritos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$existe['id'], $_SESSION['user_id']]);
        
        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'message' => 'Eliminado de favoritos',
            'id' => (int)$existe['id']
        ]);
        return;
    }
    
    // Agregar a favoritos (toggle on)
    $stmt = $conn->prepare("INSERT INTO favoritos (usuario_id, vehiculo_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $vehiculo_id]);
    
    $id = $conn->lastInsertId();
    echo json_encode([
        'success' => true,
        'action' => 'added',
        'message' => 'Agregado a favoritos',
        'id' => (int)$id,
        'user_id' => (int)$_SESSION['user_id'],
        'vehicle_id' => (int)$vehiculo_id,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

function deleteFavorite($conn, $favorite_id) {
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$favorite_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Favorite not found']);
        return;
    }
    
    echo json_encode(['message' => 'Favorite removed successfully']);
}


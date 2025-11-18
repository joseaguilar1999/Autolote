<?php
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '';

// Obtener ID de la URL si existe
$vehicle_id = null;
if (preg_match('/\/vehicles\/(\d+)/', $path, $matches)) {
    $vehicle_id = (int)$matches[1];
}

try {
    switch ($method) {
        case 'GET':
            if ($vehicle_id) {
                // GET /api/vehicles/{id}
                getVehicle($conn, $vehicle_id);
            } else {
                // GET /api/vehicles
                getVehicles($conn);
            }
            break;
            
        case 'POST':
            // POST /api/vehicles (requiere admin)
            requireAdmin();
            createVehicle($conn);
            break;
            
        case 'PUT':
            // PUT /api/vehicles/{id} (requiere admin)
            if (!$vehicle_id) {
                throw new Exception('Vehicle ID required');
            }
            requireAdmin();
            updateVehicle($conn, $vehicle_id);
            break;
            
        case 'DELETE':
            // DELETE /api/vehicles/{id} (requiere admin)
            if (!$vehicle_id) {
                throw new Exception('Vehicle ID required');
            }
            requireAdmin();
            deleteVehicle($conn, $vehicle_id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function getVehicles($conn) {
    // Obtener parámetros de filtro
    $marca = $_GET['marca'] ?? null;
    $año_min = $_GET['año_min'] ?? null;
    $año_max = $_GET['año_max'] ?? null;
    $precio_min = $_GET['precio_min'] ?? null;
    $precio_max = $_GET['precio_max'] ?? null;
    $transmision = $_GET['transmision'] ?? null;
    $status = $_GET['status'] ?? 'disponible';
    
    $sql = "SELECT v.*, 
            (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal,
            (SELECT GROUP_CONCAT(imagen_path) FROM vehiculos_imagenes WHERE vehiculo_id = v.id) as imagenes
            FROM vehiculos v 
            WHERE 1=1";
    
    $params = [];
    
    if ($marca) {
        $sql .= " AND v.marca LIKE ?";
        $params[] = "%$marca%";
    }
    
    if ($año_min) {
        $sql .= " AND v.año >= ?";
        $params[] = $año_min;
    }
    
    if ($año_max) {
        $sql .= " AND v.año <= ?";
        $params[] = $año_max;
    }
    
    if ($precio_min) {
        $sql .= " AND v.precio >= ?";
        $params[] = $precio_min;
    }
    
    if ($precio_max) {
        $sql .= " AND v.precio <= ?";
        $params[] = $precio_max;
    }
    
    if ($transmision) {
        $sql .= " AND v.transmision = ?";
        $params[] = $transmision;
    }
    
    if ($status) {
        $sql .= " AND v.estado = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY v.fecha_creacion DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
    
    // Formatear respuesta
    $result = array_map(function($v) {
        $images = [];
        if ($v['imagenes']) {
            $images = explode(',', $v['imagenes']);
            $images = array_map(function($img) {
                return UPLOAD_URL . trim($img);
            }, $images);
        } elseif ($v['imagen_principal']) {
            $images[] = UPLOAD_URL . $v['imagen_principal'];
        }
        
        return [
            'id' => (int)$v['id'],
            'marca' => $v['marca'],
            'modelo' => $v['modelo'],
            'año' => (int)$v['año'],
            'precio' => (float)$v['precio'],
            'kilometraje' => (int)$v['kilometraje'],
            'color' => $v['color'],
            'transmision' => ucfirst($v['transmision']),
            'descripcion' => $v['descripcion'] ?? '',
            'images' => $images,
            'featured' => isset($v['featured']) ? (bool)$v['featured'] : false,
            'status' => $v['estado'],
            'created_at' => $v['fecha_creacion'],
            'updated_at' => $v['fecha_actualizacion']
        ];
    }, $vehicles);
    
    echo json_encode($result);
}

function getVehicle($conn, $id) {
    $stmt = $conn->prepare("SELECT v.*, 
        (SELECT GROUP_CONCAT(imagen_path) FROM vehiculos_imagenes WHERE vehiculo_id = v.id) as imagenes
        FROM vehiculos v WHERE v.id = ?");
    $stmt->execute([$id]);
    $v = $stmt->fetch();
    
    if (!$v) {
        http_response_code(404);
        echo json_encode(['error' => 'Vehicle not found']);
        return;
    }
    
    $images = [];
    if ($v['imagenes']) {
        $images = explode(',', $v['imagenes']);
        $images = array_map(function($img) {
            return UPLOAD_URL . trim($img);
        }, $images);
    }
    
    $result = [
        'id' => (int)$v['id'],
        'marca' => $v['marca'],
        'modelo' => $v['modelo'],
        'año' => (int)$v['año'],
        'precio' => (float)$v['precio'],
        'kilometraje' => (int)$v['kilometraje'],
        'color' => $v['color'],
        'transmision' => ucfirst($v['transmision']),
        'descripcion' => $v['descripcion'] ?? '',
        'images' => $images,
        'featured' => isset($v['featured']) ? (bool)$v['featured'] : false,
        'status' => $v['estado'],
        'created_at' => $v['fecha_creacion'],
        'updated_at' => $v['fecha_actualizacion']
    ];
    
    echo json_encode($result);
}

function createVehicle($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $marca = $data['marca'] ?? '';
    $modelo = $data['modelo'] ?? '';
    $año = $data['año'] ?? null;
    $precio = $data['precio'] ?? null;
    $kilometraje = $data['kilometraje'] ?? null;
    $color = $data['color'] ?? '';
    $transmision = strtolower($data['transmision'] ?? '');
    $descripcion = $data['descripcion'] ?? '';
    $featured = $data['featured'] ?? false;
    $status = $data['status'] ?? 'disponible';
    
    if (!$marca || !$modelo || !$año || !$precio || !$kilometraje || !$color || !$transmision) {
        throw new Exception('Missing required fields');
    }
    
    // Verificar si existe campo featured
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'featured'");
        $hasFeatured = $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        $hasFeatured = false;
    }
    
    if ($hasFeatured) {
        $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion, estado, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $status, $featured ? 1 : 0]);
    } else {
        $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $status]);
    }
    
    $id = $conn->lastInsertId();
    getVehicle($conn, $id);
}

function updateVehicle($conn, $id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $updates = [];
    $params = [];
    
    $fields = ['marca', 'modelo', 'año', 'precio', 'kilometraje', 'color', 'transmision', 'descripcion', 'estado'];
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            if ($field === 'transmision') {
                $params[] = strtolower($data[$field]);
            } else {
                $params[] = $data[$field];
            }
        }
    }
    
    // Verificar si existe campo featured
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'featured'");
        $hasFeatured = $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        $hasFeatured = false;
    }
    
    if ($hasFeatured && isset($data['featured'])) {
        $updates[] = "featured = ?";
        $params[] = $data['featured'] ? 1 : 0;
    }
    
    if (empty($updates)) {
        throw new Exception('No fields to update');
    }
    
    $params[] = $id;
    $sql = "UPDATE vehiculos SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    getVehicle($conn, $id);
}

function deleteVehicle($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM vehiculos WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Vehicle not found']);
        return;
    }
    
    echo json_encode(['message' => 'Vehicle deleted successfully']);
}


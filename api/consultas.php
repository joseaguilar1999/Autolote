<?php
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // GET /api/inquiries - Obtener todas las consultas (requiere admin)
            requireAdmin();
            getInquiries($conn);
            break;
            
        case 'POST':
            // POST /api/inquiries - Crear consulta (público)
            createInquiry($conn);
            break;
            
        case 'PUT':
            // PUT /api/inquiries/{id} - Actualizar estado (requiere admin)
            requireAdmin();
            $inquiry_id = $_GET['id'] ?? null;
            if (!$inquiry_id) {
                throw new Exception('Inquiry ID required');
            }
            updateInquiryStatus($conn, $inquiry_id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function createInquiry($conn) {
    // Soporta tanto JSON como FormData
    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
    }
    
    $vehiculo_id = $data['vehicle_id'] ?? $data['vehiculo_id'] ?? null;
    $nombre = $data['name'] ?? $data['nombre'] ?? '';
    $email = $data['email'] ?? '';
    $telefono = $data['phone'] ?? $data['telefono'] ?? '';
    $mensaje = $data['message'] ?? $data['mensaje'] ?? '';
    
    if (!$nombre || !$email || !$mensaje) {
        throw new Exception('Name, email and message are required');
    }
    
    // Validar que el vehículo existe si se proporciona ID
    if ($vehiculo_id) {
        $stmt = $conn->prepare("SELECT id FROM vehiculos WHERE id = ?");
        $stmt->execute([$vehiculo_id]);
        if (!$stmt->fetch()) {
            $vehiculo_id = null;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO consultas (vehiculo_id, nombre, email, telefono, mensaje) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$vehiculo_id, $nombre, $email, $telefono, $mensaje])) {
        $id = $conn->lastInsertId();
        echo json_encode([
            'id' => (int)$id,
            'vehicle_id' => $vehiculo_id ? (int)$vehiculo_id : null,
            'name' => $nombre,
            'email' => $email,
            'phone' => $telefono,
            'message' => $mensaje,
            'status' => 'nueva',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception('Error creating inquiry');
    }
}

function getInquiries($conn) {
    $stmt = $conn->query("SELECT c.*, v.marca, v.modelo 
        FROM consultas c 
        LEFT JOIN vehiculos v ON c.vehiculo_id = v.id 
        ORDER BY c.fecha_creacion DESC");
    $inquiries = $stmt->fetchAll();
    
    $result = array_map(function($i) {
        return [
            'id' => (int)$i['id'],
            'vehicle_id' => $i['vehiculo_id'] ? (int)$i['vehiculo_id'] : null,
            'name' => $i['nombre'],
            'email' => $i['email'],
            'phone' => $i['telefono'] ?? '',
            'message' => $i['mensaje'],
            'status' => $i['estado'],
            'created_at' => $i['fecha_creacion']
        ];
    }, $inquiries);
    
    echo json_encode($result);
}

function updateInquiryStatus($conn, $inquiry_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $status = $data['status'] ?? null;
    
    if (!$status) {
        throw new Exception('Status is required');
    }
    
    $valid_statuses = ['nueva', 'leida', 'respondida'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Invalid status. Must be: nueva, leida, or respondida');
    }
    
    $stmt = $conn->prepare("UPDATE consultas SET estado = ? WHERE id = ?");
    $stmt->execute([$status, $inquiry_id]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Inquiry not found']);
        return;
    }
    
    // Devolver la consulta actualizada
    $stmt = $conn->prepare("SELECT * FROM consultas WHERE id = ?");
    $stmt->execute([$inquiry_id]);
    $inquiry = $stmt->fetch();
    
    echo json_encode([
        'id' => (int)$inquiry['id'],
        'vehicle_id' => $inquiry['vehiculo_id'] ? (int)$inquiry['vehiculo_id'] : null,
        'name' => $inquiry['nombre'],
        'email' => $inquiry['email'],
        'phone' => $inquiry['telefono'] ?? '',
        'message' => $inquiry['mensaje'],
        'status' => $inquiry['estado'],
        'created_at' => $inquiry['fecha_creacion']
    ]);
}


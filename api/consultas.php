<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiculo_id = $_POST['vehiculo_id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';
    
    if ($nombre && $email && $mensaje) {
        $conn = getDBConnection();
        
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
            echo json_encode(['success' => true, 'message' => 'Consulta enviada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al enviar consulta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Por favor completa todos los campos requeridos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}


<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $vehiculo_id = $data['vehiculo_id'] ?? 0;
    
    if ($vehiculo_id) {
        $conn = getDBConnection();
        
        // Verificar si ya existe
        $stmt = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND vehiculo_id = ?");
        $stmt->execute([$_SESSION['user_id'], $vehiculo_id]);
        $existe = $stmt->fetch();
        
        if ($existe) {
            // Eliminar de favoritos
            $stmt = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND vehiculo_id = ?");
            $stmt->execute([$_SESSION['user_id'], $vehiculo_id]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Agregar a favoritos
            $stmt = $conn->prepare("INSERT INTO favoritos (usuario_id, vehiculo_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $vehiculo_id]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID de vehículo inválido']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}


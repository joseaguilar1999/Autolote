<?php
// Configuración general del sitio
session_start();

// Configuración de rutas
define('BASE_URL', 'http://localhost/Autolote');
define('BASE_PATH', __DIR__ . '/..');

// Configuración de uploads
define('UPLOAD_DIR', BASE_PATH . '/uploads/vehiculos/');
define('UPLOAD_URL', BASE_URL . '/uploads/vehiculos/');

// Crear directorio de uploads si no existe
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Incluir conexión a base de datos
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/pagination.php';
require_once __DIR__ . '/security.php';

// Funciones de utilidad
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function formatPrice($price) {
    return 'Lps. ' . number_format($price, 2);
}

function formatKilometraje($km) {
    return number_format($km) . ' km';
}


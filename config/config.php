<?php
// Configuración general del sitio
session_start();

// Configuración de rutas
// Detectar automáticamente la URL base (funciona en desarrollo y producción)
// Render y otros servicios usan headers especiales para HTTPS detrás de proxy
$protocol = 'http';
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $protocol = 'https';
} elseif ($_SERVER['SERVER_PORT'] == 443) {
    $protocol = 'https';
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    // Render y otros servicios detrás de proxy
    $protocol = 'https';
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
    $protocol = 'https';
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar si estamos en desarrollo local (localhost) o en producción
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    // Desarrollo local
    $base_url = $protocol . '://' . $host . '/Autolote';
} else {
    // Producción (Render, etc.) - siempre HTTPS
    $base_url = 'https://' . $host;
}

define('BASE_URL', $base_url);
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


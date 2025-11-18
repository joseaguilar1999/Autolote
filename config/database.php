<?php
// Configuración de la base de datos
// Soporta variables de entorno para producción (Render, Railway, etc.)
// Si no están definidas, usa valores por defecto para desarrollo local

// Soporte para MYSQL_URL (formato: mysql://usuario:contraseña@host:puerto/nombre_base_datos)
$mysql_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($mysql_url) {
    // Parsear la URL de conexión MySQL
    $url_parts = parse_url($mysql_url);
    if ($url_parts && isset($url_parts['host'])) {
        define('DB_HOST', $url_parts['host'] . (isset($url_parts['port']) ? ':' . $url_parts['port'] : ''));
        define('DB_USER', isset($url_parts['user']) ? $url_parts['user'] : 'root');
        define('DB_PASS', isset($url_parts['pass']) ? $url_parts['pass'] : '');
        define('DB_NAME', isset($url_parts['path']) ? ltrim($url_parts['path'], '/') : 'autolote');
    } else {
        // Si el parseo falla, usar valores por defecto
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
        define('DB_USER', getenv('DB_USER') ?: 'root');
        define('DB_PASS', getenv('DB_PASS') ?: '');
        define('DB_NAME', getenv('DB_NAME') ?: 'autolote');
    }
} else {
    // Usar variables de entorno individuales o valores por defecto
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: 'autolote');
}

// Conexión a la base de datos
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}


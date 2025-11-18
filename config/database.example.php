<?php
// Configuración de la base de datos - ARCHIVO DE EJEMPLO
// Este archivo ya soporta variables de entorno automáticamente
// Para desarrollo local: No necesitas hacer nada, usa los valores por defecto
// Para producción: Define las variables de entorno en tu plataforma de hosting

// El archivo database.php ya está configurado para usar variables de entorno
// Si no están definidas, usa estos valores por defecto para desarrollo local:
// DB_HOST=localhost
// DB_USER=root
// DB_PASS=
// DB_NAME=autolote

// Para producción, define las variables de entorno según tu hosting:

// InfinityFree (en el archivo database.php directamente):
// define('DB_HOST', 'sqlXXX.infinityfree.com');
// define('DB_USER', 'tu_usuario_mysql');
// define('DB_PASS', 'tu_contraseña_mysql');
// define('DB_NAME', 'tu_nombre_base_datos');

// Render + Neon PostgreSQL (variables de entorno en Render Dashboard):
// DB_HOST=ep-xxxxx.us-east-2.aws.neon.tech
// DB_USER=tu_usuario_neon
// DB_PASS=tu_contraseña_neon
// DB_NAME=tu_base_datos
// NOTA: Necesitarás cambiar PDO de mysql: a pgsql: en database.php

// Render + Railway MySQL (variables de entorno en Render Dashboard):
// OPCIÓN 1: Usar MYSQL_URL (recomendado - se parsea automáticamente):
// MYSQL_URL=mysql://root:contraseña@containers-us-west-xxx.railway.app:3306/railway
//
// OPCIÓN 2: Usar variables individuales:
// DB_HOST=containers-us-west-xxx.railway.app
// DB_USER=root
// DB_PASS=tu_contraseña_railway
// DB_NAME=railway

// Render + PostgreSQL propio (variables de entorno en Render Dashboard):
// DB_HOST=dpg-xxxxx-a.oregon-postgres.render.com
// DB_USER=tu_usuario
// DB_PASS=tu_contraseña
// DB_NAME=tu_base_datos
// NOTA: Solo 90 días gratis, luego requiere pago

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


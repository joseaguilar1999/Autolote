<?php
/**
 * Script de inicio del proyecto
 * Verifica y ejecuta todo lo necesario para que el proyecto funcione
 */

require_once 'config/config.php';

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar conexión a la base de datos
try {
    $conn = getDBConnection();
    $success[] = "✓ Conexión a la base de datos exitosa";
    
    // Verificar que las tablas existan
    $tables = ['usuarios', 'vehiculos', 'vehiculos_imagenes', 'consultas', 'favoritos'];
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM {$table}");
            $success[] = "✓ Tabla '{$table}' existe";
        } catch (PDOException $e) {
            $errors[] = "✗ Tabla '{$table}' no existe. Ejecuta database.sql primero.";
        }
    }
    
    // Verificar columna featured
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'featured'");
        if ($stmt->rowCount() > 0) {
            $success[] = "✓ Columna 'featured' existe en vehiculos";
        } else {
            $warnings[] = "⚠ Columna 'featured' no existe. Ejecuta database_update_featured.sql";
        }
    } catch (PDOException $e) {
        $warnings[] = "⚠ No se pudo verificar la columna 'featured'";
    }
    
    // Verificar si hay vehículos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM vehiculos");
    $vehicle_count = $stmt->fetch()['total'];
    
    if ($vehicle_count == 0) {
        $warnings[] = "⚠ No hay vehículos en la base de datos. Ejecuta seed_vehicles.php para agregar datos de ejemplo.";
    } else {
        $success[] = "✓ Hay {$vehicle_count} vehículo(s) en la base de datos";
    }
    
    // Verificar si hay usuarios admin
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'admin'");
    $admin_count = $stmt->fetch()['total'];
    
    if ($admin_count == 0) {
        $warnings[] = "⚠ No hay usuarios administradores. Crea uno manualmente o ejecuta database.sql";
    } else {
        $success[] = "✓ Hay {$admin_count} usuario(s) administrador(es)";
    }
    
} catch (PDOException $e) {
    $errors[] = "✗ Error de conexión a la base de datos: " . $e->getMessage();
}

// 2. Verificar directorios necesarios
$directories = [
    'uploads' => 'Directorio para archivos subidos',
    'uploads/vehiculos' => 'Directorio para imágenes de vehículos',
    'config' => 'Directorio de configuración',
    'includes' => 'Directorio de includes',
    'admin' => 'Directorio del panel administrativo',
    'api' => 'Directorio de APIs'
];

foreach ($directories as $dir => $desc) {
    if (is_dir($dir)) {
        $success[] = "✓ Directorio '{$dir}' existe";
        
        // Verificar permisos de escritura para uploads
        if (strpos($dir, 'uploads') !== false) {
            if (is_writable($dir)) {
                $success[] = "✓ Directorio '{$dir}' tiene permisos de escritura";
            } else {
                $warnings[] = "⚠ Directorio '{$dir}' no tiene permisos de escritura";
            }
        }
    } else {
        $errors[] = "✗ Directorio '{$dir}' no existe";
    }
}

// 3. Verificar archivos importantes
$important_files = [
    'config/database.php' => 'Configuración de base de datos',
    'config/config.php' => 'Configuración general',
    'index.php' => 'Página principal',
    'login.php' => 'Página de login',
    'admin/index.php' => 'Panel administrativo'
];

foreach ($important_files as $file => $desc) {
    if (file_exists($file)) {
        $success[] = "✓ Archivo '{$file}' existe";
    } else {
        $errors[] = "✗ Archivo '{$file}' no existe";
    }
}

// 4. Verificar configuración
if (defined('BASE_URL') && !empty(BASE_URL)) {
    $success[] = "✓ BASE_URL configurado: " . BASE_URL;
} else {
    $warnings[] = "⚠ BASE_URL no está configurado";
}

// Mostrar resultados
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio del Proyecto - Autolote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #f8fafc 0%, #e0f2fe 50%, #f8fafc 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .status-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        .status-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .status-item:last-child {
            border-bottom: none;
        }
        .success-item {
            color: #10b981;
        }
        .warning-item {
            color: #f59e0b;
        }
        .error-item {
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card status-card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="bi bi-gear-fill me-2"></i>
                            Verificación del Sistema - Autolote
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle me-2"></i>Verificaciones Exitosas</h5>
                                <ul class="mb-0">
                                    <?php foreach ($success as $msg): ?>
                                        <li class="success-item"><?= htmlspecialchars($msg) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($warnings)): ?>
                            <div class="alert alert-warning">
                                <h5><i class="bi bi-exclamation-triangle me-2"></i>Advertencias</h5>
                                <ul class="mb-0">
                                    <?php foreach ($warnings as $msg): ?>
                                        <li class="warning-item"><?= htmlspecialchars($msg) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-x-circle me-2"></i>Errores</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $msg): ?>
                                        <li class="error-item"><?= htmlspecialchars($msg) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($errors)): ?>
                            <div class="alert alert-info">
                                <h5><i class="bi bi-info-circle me-2"></i>Estado del Sistema</h5>
                                <p class="mb-2">
                                    <?php if (empty($warnings)): ?>
                                        <strong class="text-success">✓ Sistema listo para usar</strong>
                                    <?php else: ?>
                                        <strong>El sistema está funcionando pero hay algunas advertencias.</strong>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex gap-2 flex-wrap mt-4">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>Ir al Inicio
                            </a>
                            <a href="admin/index.php" class="btn btn-outline-primary">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Administrativo
                            </a>
                            <?php if ($vehicle_count == 0): ?>
                                <a href="seed_vehicles.php" class="btn btn-success">
                                    <i class="bi bi-database-add me-2"></i>Agregar Vehículos de Ejemplo
                                </a>
                            <?php endif; ?>
                            <a href="start.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i>Actualizar Verificación
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Información del sistema -->
                <div class="card status-card mt-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                                <p><strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido' ?></p>
                                <p><strong>Base URL:</strong> <?= defined('BASE_URL') ? BASE_URL : 'No configurado' ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Directorio:</strong> <?= __DIR__ ?></p>
                                <p><strong>Fecha/Hora:</strong> <?= date('Y-m-d H:i:s') ?></p>
                                <p><strong>Zona Horaria:</strong> <?= date_default_timezone_get() ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


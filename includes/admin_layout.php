<?php
// Admin Layout Component
// Uso: include 'includes/admin_layout.php'; 
// Luego poner el contenido dentro de la variable $admin_content

if (!isset($admin_content)) {
    $admin_content = '';
}

// Obtener estadísticas para el sidebar
$conn = getDBConnection();
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas WHERE estado = 'nueva'");
$consultas_nuevas = $stmt->fetch()['total'];

// Determinar página activa
$current_page = basename($_SERVER['PHP_SELF']);
$nav_items = [
    ['path' => 'index.php', 'label' => 'Dashboard', 'icon' => 'speedometer2'],
    ['path' => 'vehiculos.php', 'label' => 'Vehículos', 'icon' => 'car-front'],
    ['path' => 'usuarios.php', 'label' => 'Usuarios', 'icon' => 'people'],
    ['path' => 'consultas.php', 'label' => 'Consultas', 'icon' => 'envelope'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Panel Administrativo - Autolote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php 
    // Asegurar que BASE_URL esté definido
    if (!defined('BASE_URL')) {
        // Render y otros servicios usan headers especiales para HTTPS detrás de proxy
        $protocol = 'http';
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } elseif ($_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https';
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = 'https';
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            $protocol = 'https';
        }
        
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            define('BASE_URL', $protocol . '://' . $host . '/Autolote');
        } else {
            // Producción - siempre HTTPS
            define('BASE_URL', 'https://' . $host);
        }
    }
    ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
    </style>
    <style>
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 256px;
            min-height: 100vh;
            flex-shrink: 0;
        }
        
        .admin-nav-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin: 0.25rem 0.75rem;
            border-radius: 0.5rem;
        }
        
        .admin-nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0 2px 2px 0;
            transition: height 0.3s ease;
        }
        
        .admin-nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            transform: translateX(4px);
        }
        
        .admin-nav-item.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            color: #fff;
            border-left: 3px solid #667eea;
        }
        
        .admin-nav-item.active::before {
            height: 60%;
        }
        
        .admin-sidebar .text-white {
            color: #fff !important;
        }
        
        .admin-sidebar .text-muted {
            color: #64748b !important;
        }
        
        .admin-nav-item i {
            width: 20px;
            text-align: center;
            margin-right: 0.75rem;
        }
        
        .admin-content-wrapper {
            flex: 1;
            padding: 2rem;
            min-width: 0;
        }
        
        .badge-notification {
            margin-left: auto;
            font-size: 0.65rem;
            padding: 0.125rem 0.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
                overflow-y: auto;
            }
            
            .admin-sidebar.show {
                left: 0;
            }
            
            .admin-content-wrapper {
                width: 100%;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="px-3 mb-4">
                <a href="index.php" class="d-flex align-items-center text-white text-decoration-none">
                    <i class="bi bi-car-front fs-3 me-2"></i>
                    <span class="fs-5 fw-bold">Autolote</span>
                </a>
                <small class="text-muted d-block mt-1">Panel Administrativo</small>
            </div>
            
            <nav class="nav flex-column">
                <?php foreach ($nav_items as $item): ?>
                    <?php 
                    $is_active = ($current_page === $item['path']);
                    $badge_count = ($item['path'] === 'consultas.php' && $consultas_nuevas > 0) ? $consultas_nuevas : 0;
                    ?>
                    <a href="<?= $item['path'] ?>" 
                       class="nav-link admin-nav-item <?= $is_active ? 'active' : '' ?>">
                        <i class="bi bi-<?= $item['icon'] ?> me-2"></i>
                        <?= $item['label'] ?>
                        <?php if ($badge_count > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" style="font-size: 0.65rem;">
                                <?= $badge_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
                
                <hr class="text-muted my-2">
                
                <a href="../index.php" class="nav-link admin-nav-item">
                    <i class="bi bi-house me-2"></i> Ver Sitio
                </a>
                
                <a href="../logout.php" class="nav-link admin-nav-item text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i> Salir
                </a>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="admin-content-wrapper">
            <?= $admin_content ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/admin-mobile.js"></script>
</body>
</html>


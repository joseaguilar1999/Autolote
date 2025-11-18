<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Estadísticas para el dashboard
$stats = [];

// Total de vehículos
$stmt = $conn->query("SELECT COUNT(*) as total FROM vehiculos");
$stats['total_vehiculos'] = $stmt->fetch()['total'];

// Vehículos disponibles
$stmt = $conn->query("SELECT COUNT(*) as total FROM vehiculos WHERE estado = 'disponible'");
$stats['vehiculos_disponibles'] = $stmt->fetch()['total'];

// Vehículos vendidos
$stmt = $conn->query("SELECT COUNT(*) as total FROM vehiculos WHERE estado = 'vendido'");
$stats['vehiculos_vendidos'] = $stmt->fetch()['total'];

// Total de usuarios
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'cliente'");
$stats['total_clientes'] = $stmt->fetch()['total'];

// Consultas nuevas/pendientes
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas WHERE estado = 'nueva'");
$stats['consultas_nuevas'] = $stmt->fetch()['total'];

// Total de consultas
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas");
$stats['total_consultas'] = $stmt->fetch()['total'];

// Ingresos totales (vehículos vendidos)
$stmt = $conn->query("SELECT SUM(precio) as total FROM vehiculos WHERE estado = 'vendido'");
$stats['ingresos_totales'] = $stmt->fetch()['total'] ?? 0;

// Vehículos recientes
$stmt = $conn->query("SELECT v.*, 
    (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
    FROM vehiculos v 
    ORDER BY v.fecha_creacion DESC 
    LIMIT 5");
$vehiculos_recientes = $stmt->fetchAll();

// Consultas recientes
$stmt = $conn->query("SELECT c.*, v.marca, v.modelo 
    FROM consultas c 
    LEFT JOIN vehiculos v ON c.vehiculo_id = v.id 
    ORDER BY c.fecha_creacion DESC 
    LIMIT 5");
$consultas_recientes = $stmt->fetchAll();

// Obtener estadísticas para el sidebar
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas WHERE estado = 'nueva'");
$consultas_nuevas = $stmt->fetch()['total'];
?>
<?php
$page_title = 'Dashboard Administrativo';
include '../includes/head.php';
?>
    <style>
        body {
            background-color: #f8fafc;
        }
        
        .admin-sidebar {
            min-height: 100vh;
            background-color: #0f172a;
            padding: 1.5rem 0;
        }
        
        .admin-sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: all 0.2s;
        }
        
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #1e293b;
            color: #fff;
        }
        
        .stat-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon.blue {
            background-color: #dbeafe;
            color: #2563eb;
        }
        
        .stat-icon.green {
            background-color: #d1fae5;
            color: #10b981;
        }
        
        .stat-icon.orange {
            background-color: #fed7aa;
            color: #f97316;
        }
        
        .stat-icon.purple {
            background-color: #e9d5ff;
            color: #9333ea;
        }
        
        .recent-table {
            font-size: 0.9rem;
        }
        
        .badge-status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
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
            
            .col-md-10 {
                width: 100%;
            }
            
            .col-md-2 {
                width: 100%;
            }
            
            .row.g-4 > [class*="col-"] {
                margin-bottom: 1rem;
            }
            
            .recent-table {
                font-size: 0.8rem;
            }
            
            .stat-card .card-body {
                padding: 1rem !important;
            }
            
            .stat-icon {
                width: 32px;
                height: 32px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar">
                <div class="px-3 mb-4">
                    <a href="index.php" class="d-flex align-items-center text-white text-decoration-none">
                        <i class="bi bi-car-front fs-3 me-2"></i>
                        <span class="fs-5 fw-bold">Autolote</span>
                    </a>
                    <small class="text-muted d-block mt-1">Panel Administrativo</small>
                </div>
                
                <nav class="nav flex-column">
                    <a href="index.php" class="nav-link active">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="vehiculos.php" class="nav-link">
                        <i class="bi bi-car-front me-2"></i> Vehículos
                    </a>
                    <a href="usuarios.php" class="nav-link">
                        <i class="bi bi-people me-2"></i> Usuarios
                    </a>
                    <a href="consultas.php" class="nav-link position-relative">
                        <i class="bi bi-envelope me-2"></i> Consultas
                        <?php if ($consultas_nuevas > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" style="font-size: 0.65rem;">
                                <?= $consultas_nuevas ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <hr class="text-muted my-2">
                    <a href="../index.php" class="nav-link">
                        <i class="bi bi-house me-2"></i> Ver Sitio
                    </a>
                    <a href="../logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Salir
                    </a>
                </nav>
            </div>

            <!-- Contenido Principal -->
            <div class="col-md-10">
                <div class="p-4">
                    <h1 class="display-5 fw-bold text-dark mb-5">Dashboard</h1>

                    <!-- Estadísticas -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-6 col-lg-3">
                            <div class="card stat-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="text-muted small mb-1 fw-semibold">Total Vehículos</p>
                                            <h2 class="h3 fw-bold text-dark mb-0"><?= $stats['total_vehiculos'] ?></h2>
                                        </div>
                                        <div class="stat-icon blue">
                                            <i class="bi bi-car-front fs-5"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Disponibles: <strong><?= $stats['vehiculos_disponibles'] ?></strong> | 
                                        Vendidos: <strong><?= $stats['vehiculos_vendidos'] ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card stat-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="text-muted small mb-1 fw-semibold">Usuarios Registrados</p>
                                            <h2 class="h3 fw-bold text-dark mb-0"><?= $stats['total_clientes'] ?></h2>
                                        </div>
                                        <div class="stat-icon green">
                                            <i class="bi bi-people fs-5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card stat-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="text-muted small mb-1 fw-semibold">Consultas</p>
                                            <h2 class="h3 fw-bold text-dark mb-0"><?= $stats['total_consultas'] ?></h2>
                                        </div>
                                        <div class="stat-icon orange">
                                            <i class="bi bi-envelope fs-5"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Pendientes: <strong class="text-danger"><?= $stats['consultas_nuevas'] ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="card stat-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="text-muted small mb-1 fw-semibold">Ingresos Totales</p>
                                            <h2 class="h3 fw-bold text-dark mb-0"><?= formatPrice($stats['ingresos_totales']) ?></h2>
                                        </div>
                                        <div class="stat-icon purple">
                                            <i class="bi bi-currency-dollar fs-5"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0">De vehículos vendidos</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tablas de Información Reciente -->
                    <div class="row g-4">
                        <!-- Vehículos Recientes -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="fw-bold mb-0">Vehículos Recientes</h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($vehiculos_recientes)): ?>
                                        <div class="p-4 text-center text-muted">
                                            <p class="mb-0">No hay vehículos registrados</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 recent-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Vehículo</th>
                                                        <th>Año</th>
                                                        <th>Precio</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($vehiculos_recientes as $v): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="vehiculo_form.php?id=<?= $v['id'] ?>" class="text-decoration-none text-dark fw-semibold">
                                                                    <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>
                                                                </a>
                                                            </td>
                                                            <td><?= $v['año'] ?></td>
                                                            <td><?= formatPrice($v['precio']) ?></td>
                                                            <td>
                                                                <span class="badge-status bg-<?= $v['estado'] === 'disponible' ? 'success' : ($v['estado'] === 'vendido' ? 'danger' : 'warning') ?>">
                                                                    <?= ucfirst($v['estado']) ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer bg-white border-0 py-3">
                                            <a href="vehiculos.php" class="btn btn-sm btn-outline-primary">
                                                Ver Todos los Vehículos <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Consultas Recientes -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="fw-bold mb-0">Consultas Recientes</h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php if (empty($consultas_recientes)): ?>
                                        <div class="p-4 text-center text-muted">
                                            <p class="mb-0">No hay consultas</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 recent-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Vehículo</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($consultas_recientes as $c): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="consultas.php" class="text-decoration-none text-dark fw-semibold">
                                                                    <?= htmlspecialchars($c['nombre']) ?>
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <?php if ($c['marca']): ?>
                                                                    <small class="text-muted"><?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?></small>
                                                                <?php else: ?>
                                                                    <small class="text-muted">General</small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge-status bg-<?= $c['estado'] === 'nueva' ? 'danger' : ($c['estado'] === 'leida' ? 'warning' : 'success') ?>">
                                                                    <?= ucfirst($c['estado']) ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="card-footer bg-white border-0 py-3">
                                            <a href="consultas.php" class="btn btn-sm btn-outline-primary">
                                                Ver Todas las Consultas <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= BASE_URL ?>/assets/js/admin-mobile.js"></script>
<?php include '../includes/footer.php'; ?>

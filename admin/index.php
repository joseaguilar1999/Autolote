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

$page_title = 'Dashboard Administrativo';

// Preparar contenido para el layout
ob_start();
?>
<style>
        
        .page-header {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .page-header h1 {
            background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        
        .stat-card {
            border: none;
            border-radius: 1rem;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.5), transparent);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(-4px);
        }
        
        .stat-card .card-body {
            padding: 1.75rem;
        }
        
        .stat-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-icon.blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }
        
        .stat-icon.green {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #10b981;
        }
        
        .stat-icon.orange {
            background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
            color: #f97316;
        }
        
        .stat-icon.purple {
            background: linear-gradient(135deg, #e9d5ff 0%, #ddd6fe 100%);
            color: #9333ea;
        }
        
        .stat-footer {
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .stat-footer strong {
            color: #1e293b;
            font-weight: 600;
        }
        
        .recent-card {
            border: none;
            border-radius: 1rem;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }
        
        .recent-card .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
        }
        
        .recent-card .card-header h5 {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .recent-table {
            font-size: 0.875rem;
        }
        
        .recent-table thead {
            background: #f8fafc;
        }
        
        .recent-table thead th {
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .recent-table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .recent-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .recent-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }
        
        .recent-table tbody a {
            color: #1e293b;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .recent-table tbody a:hover {
            color: #667eea;
        }
        
        .badge-status {
            padding: 0.375rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .card-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }
        
        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
            transform: translateX(4px);
        }
        
        .empty-state {
            padding: 3rem 2rem;
            text-align: center;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .row.g-4 > [class*="col-"] {
                margin-bottom: 1rem;
            }
            
            .recent-table {
                font-size: 0.75rem;
            }
            
            .stat-card .card-body {
                padding: 1.25rem !important;
            }
            
            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            .stat-footer {
                font-size: 0.7rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>

    <div class="page-header">
        <h1 class="mb-0">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard Administrativo
        </h1>
        <p class="text-muted mb-0 mt-2">Resumen general del sistema</p>
    </div>

    <!-- Estadísticas -->
    <div class="row g-4 mb-5">
        <div class="col-6 col-md-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label">Total Vehículos</p>
                            <div class="stat-value"><?= $stats['total_vehiculos'] ?></div>
                            <div class="stat-footer">
                                Disponibles: <strong><?= $stats['vehiculos_disponibles'] ?></strong> | 
                                Vendidos: <strong><?= $stats['vehiculos_vendidos'] ?></strong>
                            </div>
                        </div>
                        <div class="stat-icon blue">
                            <i class="bi bi-car-front"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label">Usuarios Registrados</p>
                            <div class="stat-value"><?= $stats['total_clientes'] ?></div>
                        </div>
                        <div class="stat-icon green">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label">Consultas</p>
                            <div class="stat-value"><?= $stats['total_consultas'] ?></div>
                            <div class="stat-footer">
                                Pendientes: <strong class="text-danger"><?= $stats['consultas_nuevas'] ?></strong>
                            </div>
                        </div>
                        <div class="stat-icon orange">
                            <i class="bi bi-envelope"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <p class="stat-label">Ingresos Totales</p>
                            <div class="stat-value"><?= formatPrice($stats['ingresos_totales']) ?></div>
                            <div class="stat-footer">
                                De vehículos vendidos
                            </div>
                        </div>
                        <div class="stat-icon purple">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Información Reciente -->
    <div class="row g-4">
        <!-- Vehículos Recientes -->
        <div class="col-md-6">
            <div class="card recent-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-car-front me-2"></i>
                        Vehículos Recientes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($vehiculos_recientes)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
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
                        <div class="card-footer">
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
            <div class="card recent-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope me-2"></i>
                        Consultas Recientes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($consultas_recientes)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
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
                        <div class="card-footer">
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

<?php
$admin_content = ob_get_clean();
include '../includes/admin_layout.php';
?>

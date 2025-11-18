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

// Consultas nuevas
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas WHERE estado = 'nueva'");
$stats['consultas_nuevas'] = $stmt->fetch()['total'];

// Total de consultas
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas");
$stats['total_consultas'] = $stmt->fetch()['total'];

// Ingresos totales (vehículos vendidos)
$stmt = $conn->query("SELECT SUM(precio) as total FROM vehiculos WHERE estado = 'vendido'");
$stats['ingresos_totales'] = $stmt->fetch()['total'] ?? 0;

// Vehículos recientes
$stmt = $conn->query("SELECT * FROM vehiculos ORDER BY fecha_creacion DESC LIMIT 5");
$vehiculos_recientes = $stmt->fetchAll();

// Consultas recientes
$stmt = $conn->query("SELECT c.*, v.marca, v.modelo FROM consultas c LEFT JOIN vehiculos v ON c.vehiculo_id = v.id ORDER BY c.fecha_creacion DESC LIMIT 5");
$consultas_recientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Autolote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-car-front"></i> Autolote - Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Ver Sitio</a>
                <a class="nav-link" href="../logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid my-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="list-group">
                    <a href="index.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="vehiculos.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-car-front"></i> Vehículos
                    </a>
                    <a href="usuarios.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                    <a href="consultas.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-envelope"></i> Consultas
                        <?php if ($stats['consultas_nuevas'] > 0): ?>
                            <span class="badge bg-danger"><?= $stats['consultas_nuevas'] ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Contenido -->
            <div class="col-md-10">
                <h2 class="mb-4">Dashboard</h2>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Vehículos</h5>
                                <h2><?= $stats['total_vehiculos'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Disponibles</h5>
                                <h2><?= $stats['vehiculos_disponibles'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Total Clientes</h5>
                                <h2><?= $stats['total_clientes'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Consultas Nuevas</h5>
                                <h2><?= $stats['consultas_nuevas'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h5 class="card-title">Vendidos</h5>
                                <h2><?= $stats['vehiculos_vendidos'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-secondary">
                            <div class="card-body">
                                <h5 class="card-title">Ingresos Totales</h5>
                                <h2><?= formatPrice($stats['ingresos_totales']) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehículos Recientes -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Vehículos Recientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Marca/Modelo</th>
                                                <th>Año</th>
                                                <th>Precio</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vehiculos_recientes as $v): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></td>
                                                    <td><?= $v['año'] ?></td>
                                                    <td><?= formatPrice($v['precio']) ?></td>
                                                    <td><span class="badge bg-<?= $v['estado'] === 'disponible' ? 'success' : ($v['estado'] === 'vendido' ? 'danger' : 'warning') ?>"><?= ucfirst($v['estado']) ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="vehiculos.php" class="btn btn-primary btn-sm">Ver Todos</a>
                            </div>
                        </div>
                    </div>

                    <!-- Consultas Recientes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Consultas Recientes</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Vehículo</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($consultas_recientes as $c): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                                                    <td><?= $c['marca'] ? htmlspecialchars($c['marca'] . ' ' . $c['modelo']) : 'General' ?></td>
                                                    <td><span class="badge bg-<?= $c['estado'] === 'nueva' ? 'danger' : ($c['estado'] === 'leida' ? 'warning' : 'success') ?>"><?= ucfirst($c['estado']) ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="consultas.php" class="btn btn-primary btn-sm">Ver Todas</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


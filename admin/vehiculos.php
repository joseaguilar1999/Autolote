<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Manejar eliminación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM vehiculos WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: vehiculos.php?success=eliminado');
    exit;
}

// Obtener vehículos
$stmt = $conn->query("SELECT v.*, 
    (SELECT COUNT(*) FROM vehiculos_imagenes WHERE vehiculo_id = v.id) as num_imagenes
    FROM vehiculos v 
    ORDER BY v.fecha_creacion DESC");
$vehiculos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vehículos - Autolote</title>
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
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="vehiculos.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-car-front"></i> Vehículos
                    </a>
                    <a href="usuarios.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                    <a href="consultas.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-envelope"></i> Consultas
                    </a>
                </div>
            </div>

            <!-- Contenido -->
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestión de Vehículos</h2>
                    <a href="vehiculo_form.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Vehículo
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Vehículo <?= $_GET['success'] === 'eliminado' ? 'eliminado' : 'guardado' ?> exitosamente</div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Marca/Modelo</th>
                                        <th>Año</th>
                                        <th>Precio</th>
                                        <th>Kilometraje</th>
                                        <th>Color</th>
                                        <th>Transmisión</th>
                                        <th>Estado</th>
                                        <th>Imágenes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehiculos as $vehiculo): ?>
                                        <tr>
                                            <td><?= $vehiculo['id'] ?></td>
                                            <td><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></td>
                                            <td><?= $vehiculo['año'] ?></td>
                                            <td><?= formatPrice($vehiculo['precio']) ?></td>
                                            <td><?= formatKilometraje($vehiculo['kilometraje']) ?></td>
                                            <td><?= htmlspecialchars($vehiculo['color']) ?></td>
                                            <td><?= ucfirst($vehiculo['transmision']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $vehiculo['estado'] === 'disponible' ? 'success' : ($vehiculo['estado'] === 'vendido' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($vehiculo['estado']) ?>
                                                </span>
                                            </td>
                                            <td><?= $vehiculo['num_imagenes'] ?> imagen(es)</td>
                                            <td>
                                                <a href="vehiculo_form.php?id=<?= $vehiculo['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="vehiculos.php?eliminar=<?= $vehiculo['id'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('¿Estás seguro de eliminar este vehículo?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


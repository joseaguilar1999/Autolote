<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Manejar cambio de estado
if (isset($_GET['cambiar_estado'])) {
    $id = $_GET['cambiar_estado'];
    $estado = $_GET['estado'] ?? 'leida';
    $stmt = $conn->prepare("UPDATE consultas SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
    header('Location: consultas.php?success=actualizado');
    exit;
}

// Manejar eliminación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM consultas WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: consultas.php?success=eliminado');
    exit;
}

// Obtener consultas
$stmt = $conn->query("SELECT c.*, v.marca, v.modelo 
    FROM consultas c 
    LEFT JOIN vehiculos v ON c.vehiculo_id = v.id 
    ORDER BY c.fecha_creacion DESC");
$consultas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Consultas - Autolote</title>
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
                    <a href="vehiculos.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-car-front"></i> Vehículos
                    </a>
                    <a href="usuarios.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                    <a href="consultas.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-envelope"></i> Consultas
                    </a>
                </div>
            </div>

            <!-- Contenido -->
            <div class="col-md-10">
                <h2 class="mb-4">Gestión de Consultas</h2>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Consulta <?= $_GET['success'] === 'eliminado' ? 'eliminada' : 'actualizada' ?> exitosamente</div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Vehículo</th>
                                        <th>Mensaje</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($consultas as $consulta): ?>
                                        <tr>
                                            <td><?= $consulta['id'] ?></td>
                                            <td><?= htmlspecialchars($consulta['nombre']) ?></td>
                                            <td><?= htmlspecialchars($consulta['email']) ?></td>
                                            <td><?= htmlspecialchars($consulta['telefono'] ?? '-') ?></td>
                                            <td>
                                                <?php if ($consulta['marca']): ?>
                                                    <?= htmlspecialchars($consulta['marca'] . ' ' . $consulta['modelo']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">General</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#mensajeModal<?= $consulta['id'] ?>">
                                                    Ver Mensaje
                                                </button>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $consulta['estado'] === 'nueva' ? 'danger' : ($consulta['estado'] === 'leida' ? 'warning' : 'success') ?>">
                                                    <?= ucfirst($consulta['estado']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($consulta['fecha_creacion'])) ?></td>
                                            <td>
                                                <?php if ($consulta['estado'] === 'nueva'): ?>
                                                    <a href="consultas.php?cambiar_estado=<?= $consulta['id'] ?>&estado=leida" 
                                                       class="btn btn-sm btn-warning" title="Marcar como Leída">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                <?php elseif ($consulta['estado'] === 'leida'): ?>
                                                    <a href="consultas.php?cambiar_estado=<?= $consulta['id'] ?>&estado=respondida" 
                                                       class="btn btn-sm btn-success" title="Marcar como Respondida">
                                                        <i class="bi bi-check-circle"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="consultas.php?eliminar=<?= $consulta['id'] ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('¿Estás seguro de eliminar esta consulta?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal para mostrar mensaje -->
                                        <div class="modal fade" id="mensajeModal<?= $consulta['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Mensaje de <?= htmlspecialchars($consulta['nombre']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Email:</strong> <?= htmlspecialchars($consulta['email']) ?></p>
                                                        <?php if ($consulta['telefono']): ?>
                                                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($consulta['telefono']) ?></p>
                                                        <?php endif; ?>
                                                        <hr>
                                                        <p><?= nl2br(htmlspecialchars($consulta['mensaje'])) ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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


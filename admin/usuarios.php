<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Manejar eliminación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: usuarios.php?success=eliminado');
        exit;
    }
}

// Manejar cambio de estado
if (isset($_GET['toggle_activo'])) {
    $id = $_GET['toggle_activo'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: usuarios.php?success=actualizado');
        exit;
    }
}

// Obtener usuarios
$stmt = $conn->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Autolote</title>
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
                    <a href="usuarios.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                    <a href="consultas.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-envelope"></i> Consultas
                    </a>
                </div>
            </div>

            <!-- Contenido -->
            <div class="col-md-10">
                <h2 class="mb-4">Gestión de Usuarios</h2>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Usuario <?= $_GET['success'] === 'eliminado' ? 'eliminado' : 'actualizado' ?> exitosamente</div>
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
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id'] ?></td>
                                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                                            <td><?= htmlspecialchars($usuario['telefono'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $usuario['tipo'] === 'admin' ? 'danger' : 'primary' ?>">
                                                    <?= ucfirst($usuario['tipo']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $usuario['activo'] ? 'success' : 'secondary' ?>">
                                                    <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                            <td>
                                                <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                                    <a href="usuarios.php?toggle_activo=<?= $usuario['id'] ?>" 
                                                       class="btn btn-sm btn-<?= $usuario['activo'] ? 'warning' : 'success' ?>"
                                                       title="<?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>">
                                                        <i class="bi bi-<?= $usuario['activo'] ? 'x-circle' : 'check-circle' ?>"></i>
                                                    </a>
                                                    <a href="usuarios.php?eliminar=<?= $usuario['id'] ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <small class="text-muted">Tú</small>
                                                <?php endif; ?>
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


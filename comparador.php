<?php
require_once 'config/config.php';

// Obtener vehículos del localStorage (se pasarán por GET o se usarán cookies)
$vehiculos_ids = [];
if (isset($_GET['ids'])) {
    $vehiculos_ids = explode(',', $_GET['ids']);
    $vehiculos_ids = array_filter(array_map('intval', $vehiculos_ids));
} elseif (isset($_COOKIE['comparador'])) {
    $vehiculos_ids = json_decode($_COOKIE['comparador'], true) ?? [];
}

$conn = getDBConnection();
$vehiculos = [];

if (!empty($vehiculos_ids)) {
    $placeholders = str_repeat('?,', count($vehiculos_ids) - 1) . '?';
    $sql = "SELECT v.*, 
            (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
            FROM vehiculos v 
            WHERE v.id IN ($placeholders) AND v.estado = 'disponible'";
    $stmt = $conn->prepare($sql);
    $stmt->execute($vehiculos_ids);
    $vehiculos = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparador de Vehículos - Autolote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .comparador-table {
            overflow-x: auto;
        }
        .vehiculo-col {
            min-width: 250px;
        }
        .comparador-img {
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-car-front"></i> Autolote
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="comparador.php">Comparar Vehículos</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="favoritos.php">
                                <i class="bi bi-heart"></i> Favoritos
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/index.php">Panel Admin</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <span class="nav-link"><?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Salir</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="registro.php">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4">Comparador de Vehículos</h2>
        
        <?php if (empty($vehiculos)): ?>
            <div class="alert alert-info">
                <h4>No hay vehículos para comparar</h4>
                <p>Agrega vehículos desde el catálogo haciendo clic en "Comparar" en cada vehículo.</p>
                <a href="index.php" class="btn btn-primary">Ir al Catálogo</a>
            </div>
        <?php else: ?>
            <div class="mb-3">
                <button class="btn btn-danger" onclick="limpiarComparador()">Limpiar Comparador</button>
            </div>
            
            <div class="comparador-table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Característica</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <th class="vehiculo-col">
                                    <?php if ($vehiculo['imagen_principal']): ?>
                                        <img src="<?= UPLOAD_URL . htmlspecialchars($vehiculo['imagen_principal']) ?>" 
                                             class="img-fluid comparador-img mb-2" alt="Vehículo">
                                    <?php else: ?>
                                        <div class="bg-secondary comparador-img mb-2 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-image text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h5><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h5>
                                    <button class="btn btn-sm btn-danger" onclick="quitarDelComparador(<?= $vehiculo['id'] ?>)">
                                        <i class="bi bi-x-circle"></i> Quitar
                                    </button>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Precio</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <td><strong class="text-primary"><?= formatPrice($vehiculo['precio']) ?></strong></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Año</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <td><?= htmlspecialchars($vehiculo['año']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Kilometraje</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <td><?= formatKilometraje($vehiculo['kilometraje']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Color</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <td><?= htmlspecialchars($vehiculo['color']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Transmisión</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <td><?= ucfirst($vehiculo['transmision']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Descripción</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <td><?= htmlspecialchars(substr($vehiculo['descripcion'] ?? '', 0, 100)) ?>...</td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>Acciones</th>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <td>
                                    <a href="detalle.php?id=<?= $vehiculo['id'] ?>" class="btn btn-primary btn-sm">
                                        Ver Detalles
                                    </a>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cargar comparador desde localStorage
        window.addEventListener('load', function() {
            const comparador = JSON.parse(localStorage.getItem('comparador') || '[]');
            if (comparador.length > 0 && window.location.search === '') {
                window.location.href = 'comparador.php?ids=' + comparador.join(',');
            }
        });

        function quitarDelComparador(vehiculoId) {
            let comparador = JSON.parse(localStorage.getItem('comparador') || '[]');
            comparador = comparador.filter(id => id != vehiculoId);
            localStorage.setItem('comparador', JSON.stringify(comparador));
            window.location.reload();
        }

        function limpiarComparador() {
            localStorage.removeItem('comparador');
            window.location.href = 'comparador.php';
        }
    </script>
</body>
</html>


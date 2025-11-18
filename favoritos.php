<?php
require_once 'config/config.php';
requireLogin();

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT v.*, 
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
        FROM vehiculos v
        INNER JOIN favoritos f ON v.id = f.vehiculo_id
        WHERE f.usuario_id = ? AND v.estado = 'disponible'
        ORDER BY f.fecha_creacion DESC");
$stmt->execute([$_SESSION['user_id']]);
$vehiculos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Autolote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .vehiculo-card {
            transition: transform 0.3s;
        }
        .vehiculo-card:hover {
            transform: translateY(-5px);
        }
        .vehiculo-img {
            height: 200px;
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
                        <a class="nav-link" href="comparador.php">Comparar Vehículos</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="favoritos.php">
                            <i class="bi bi-heart-fill"></i> Favoritos
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
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4">Mis Favoritos</h2>
        
        <?php if (empty($vehiculos)): ?>
            <div class="alert alert-info">
                <h4>No tienes vehículos favoritos</h4>
                <p>Agrega vehículos a tus favoritos desde el catálogo o desde la página de detalle de cada vehículo.</p>
                <a href="index.php" class="btn btn-primary">Ir al Catálogo</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card vehiculo-card h-100 shadow-sm">
                            <?php if ($vehiculo['imagen_principal']): ?>
                                <img src="<?= UPLOAD_URL . htmlspecialchars($vehiculo['imagen_principal']) ?>" 
                                     class="card-img-top vehiculo-img" alt="<?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>">
                            <?php else: ?>
                                <div class="card-img-top vehiculo-img bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image fs-1 text-white"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h5>
                                <p class="card-text">
                                    <strong>Año:</strong> <?= htmlspecialchars($vehiculo['año']) ?><br>
                                    <strong>Precio:</strong> <?= formatPrice($vehiculo['precio']) ?><br>
                                    <strong>Kilometraje:</strong> <?= formatKilometraje($vehiculo['kilometraje']) ?><br>
                                    <strong>Color:</strong> <?= htmlspecialchars($vehiculo['color']) ?><br>
                                    <strong>Transmisión:</strong> <?= ucfirst($vehiculo['transmision']) ?>
                                </p>
                            </div>
                            <div class="card-footer">
                                <a href="detalle.php?id=<?= $vehiculo['id'] ?>" class="btn btn-primary w-100 mb-2">Ver Detalles</a>
                                <button class="btn btn-outline-danger w-100" onclick="toggleFavorito(<?= $vehiculo['id'] ?>)">
                                    <i class="bi bi-heart-fill"></i> Quitar de Favoritos
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleFavorito(vehiculoId) {
            fetch('api/favoritos.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({vehiculo_id: vehiculoId})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al actualizar favoritos');
                }
            });
        }
    </script>
</body>
</html>


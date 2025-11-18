<?php
require_once 'config/config.php';

$conn = getDBConnection();

// Obtener parámetros de búsqueda y filtros
$busqueda = $_GET['busqueda'] ?? '';
$marca = $_GET['marca'] ?? '';
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';
$año_min = $_GET['año_min'] ?? '';
$año_max = $_GET['año_max'] ?? '';
$transmision = $_GET['transmision'] ?? '';

// Construir consulta
$sql = "SELECT v.*, 
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
        FROM vehiculos v 
        WHERE v.estado = 'disponible'";

$params = [];

if ($busqueda) {
    $sql .= " AND (v.marca LIKE ? OR v.modelo LIKE ? OR v.descripcion LIKE ?)";
    $searchTerm = "%$busqueda%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($marca) {
    $sql .= " AND v.marca = ?";
    $params[] = $marca;
}

if ($precio_min) {
    $sql .= " AND v.precio >= ?";
    $params[] = $precio_min;
}

if ($precio_max) {
    $sql .= " AND v.precio <= ?";
    $params[] = $precio_max;
}

if ($año_min) {
    $sql .= " AND v.año >= ?";
    $params[] = $año_min;
}

if ($año_max) {
    $sql .= " AND v.año <= ?";
    $params[] = $año_max;
}

if ($transmision) {
    $sql .= " AND v.transmision = ?";
    $params[] = $transmision;
}

$sql .= " ORDER BY v.fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll();

// Obtener marcas únicas para el filtro
$stmt = $conn->query("SELECT DISTINCT marca FROM vehiculos WHERE estado = 'disponible' ORDER BY marca");
$marcas = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autolote - Catálogo de Vehículos</title>
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
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
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
                        <a class="nav-link active" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comparador.php">Comparar Vehículos</a>
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

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Encuentra tu Vehículo Ideal</h1>
            <p class="lead">La mejor selección de autos usados y seminuevos</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="container my-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Búsqueda</label>
                        <input type="text" class="form-control" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Marca, modelo...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Marca</label>
                        <select class="form-select" name="marca">
                            <option value="">Todas</option>
                            <?php foreach ($marcas as $m): ?>
                                <option value="<?= htmlspecialchars($m) ?>" <?= $marca === $m ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Mín</label>
                        <input type="number" class="form-control" name="precio_min" value="<?= htmlspecialchars($precio_min) ?>" placeholder="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Máx</label>
                        <input type="number" class="form-control" name="precio_max" value="<?= htmlspecialchars($precio_max) ?>" placeholder="100000">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Año Mín</label>
                        <input type="number" class="form-control" name="año_min" value="<?= htmlspecialchars($año_min) ?>" placeholder="2015">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Año Máx</label>
                        <input type="number" class="form-control" name="año_max" value="<?= htmlspecialchars($año_max) ?>" placeholder="2024">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Transmisión</label>
                        <select class="form-select" name="transmision">
                            <option value="">Todas</option>
                            <option value="manual" <?= $transmision === 'manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="automatica" <?= $transmision === 'automatica' ? 'selected' : '' ?>>Automática</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <a href="index.php" class="btn btn-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Catálogo -->
    <div class="container my-5">
        <h2 class="mb-4">Vehículos Disponibles (<?= count($vehiculos) ?>)</h2>
        <div class="row">
            <?php if (empty($vehiculos)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No se encontraron vehículos con los filtros seleccionados.</div>
                </div>
            <?php else: ?>
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
                                <a href="detalle.php?id=<?= $vehiculo['id'] ?>" class="btn btn-primary w-100">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p>&copy; 2024 Autolote. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


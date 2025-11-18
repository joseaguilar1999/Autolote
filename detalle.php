<?php
require_once 'config/config.php';

$id = $_GET['id'] ?? 0;

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM vehiculos WHERE id = ?");
$stmt->execute([$id]);
$vehiculo = $stmt->fetch();

if (!$vehiculo) {
    header('Location: index.php');
    exit;
}

// Obtener imágenes del vehículo
$stmt = $conn->prepare("SELECT * FROM vehiculos_imagenes WHERE vehiculo_id = ? ORDER BY es_principal DESC, orden ASC");
$stmt->execute([$id]);
$imagenes = $stmt->fetchAll();

// Verificar si está en favoritos (si el usuario está logueado)
$en_favoritos = false;
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND vehiculo_id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    $en_favoritos = $stmt->fetch() !== false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?> - Autolote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .main-image {
            height: 500px;
            object-fit: cover;
        }
        .thumbnail {
            height: 100px;
            object-fit: cover;
            cursor: pointer;
            opacity: 0.7;
        }
        .thumbnail:hover, .thumbnail.active {
            opacity: 1;
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
        <a href="index.php" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Volver al Catálogo
        </a>

        <div class="row">
            <!-- Imágenes -->
            <div class="col-md-6">
                <?php if (!empty($imagenes)): ?>
                    <img id="mainImage" src="<?= UPLOAD_URL . htmlspecialchars($imagenes[0]['imagen_path']) ?>" 
                         class="img-fluid main-image rounded mb-3" alt="Vehículo">
                    <div class="d-flex gap-2 flex-wrap">
                        <?php foreach ($imagenes as $index => $img): ?>
                            <img src="<?= UPLOAD_URL . htmlspecialchars($img['imagen_path']) ?>" 
                                 class="thumbnail rounded <?= $index === 0 ? 'active' : '' ?>" 
                                 onclick="changeImage('<?= UPLOAD_URL . htmlspecialchars($img['imagen_path']) ?>', this)"
                                 alt="Imagen <?= $index + 1 ?>">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-secondary d-flex align-items-center justify-content-center main-image rounded">
                        <i class="bi bi-image fs-1 text-white"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Información -->
            <div class="col-md-6">
                <h1><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h1>
                <h2 class="text-primary mb-4"><?= formatPrice($vehiculo['precio']) ?></h2>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Especificaciones</h5>
                        <table class="table">
                            <tr>
                                <th>Año:</th>
                                <td><?= htmlspecialchars($vehiculo['año']) ?></td>
                            </tr>
                            <tr>
                                <th>Kilometraje:</th>
                                <td><?= formatKilometraje($vehiculo['kilometraje']) ?></td>
                            </tr>
                            <tr>
                                <th>Color:</th>
                                <td><?= htmlspecialchars($vehiculo['color']) ?></td>
                            </tr>
                            <tr>
                                <th>Transmisión:</th>
                                <td><?= ucfirst($vehiculo['transmision']) ?></td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                                <td><span class="badge bg-success"><?= ucfirst($vehiculo['estado']) ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ($vehiculo['descripcion']): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Descripción</h5>
                            <p><?= nl2br(htmlspecialchars($vehiculo['descripcion'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2 mb-4">
                    <?php if (isLoggedIn()): ?>
                        <button class="btn btn-outline-danger" onclick="toggleFavorito(<?= $vehiculo['id'] ?>)">
                            <i class="bi bi-heart<?= $en_favoritos ? '-fill' : '' ?>"></i> 
                            <?= $en_favoritos ? 'Quitar de Favoritos' : 'Agregar a Favoritos' ?>
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-outline-primary" onclick="agregarAComparador(<?= $vehiculo['id'] ?>)">
                        <i class="bi bi-plus-circle"></i> Comparar
                    </button>
                </div>

                <!-- Formulario de Contacto -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Consulta sobre este vehículo</h5>
                        <form id="contactForm">
                            <input type="hidden" name="vehiculo_id" value="<?= $vehiculo['id'] ?>">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="nombre" placeholder="Tu nombre" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="Tu email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" class="form-control" name="telefono" placeholder="Tu teléfono">
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="mensaje" rows="3" placeholder="Tu mensaje" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enviar Consulta</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeImage(src, element) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
        }

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

        function agregarAComparador(vehiculoId) {
            let comparador = JSON.parse(localStorage.getItem('comparador') || '[]');
            if (!comparador.includes(vehiculoId)) {
                comparador.push(vehiculoId);
                localStorage.setItem('comparador', JSON.stringify(comparador));
                alert('Vehículo agregado al comparador');
            } else {
                alert('Este vehículo ya está en el comparador');
            }
        }

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api/consultas.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Consulta enviada exitosamente');
                    this.reset();
                } else {
                    alert(data.message || 'Error al enviar consulta');
                }
            });
        });
    </script>
</body>
</html>


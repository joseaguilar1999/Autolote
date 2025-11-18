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
<?php
$page_title = htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']);
include 'includes/head.php';
?>
    <style>
        body {
            background-color: #f8fafc;
        }
        
        .main-image-container {
            aspect-ratio: 16/9;
            background-color: #e2e8f0;
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
        }
        
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .thumbnail-item {
            aspect-ratio: 16/9;
            background-color: #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .thumbnail-item:hover {
            border-color: #2563eb;
        }
        
        .thumbnail-item.active {
            border-color: #2563eb;
        }
        
        .thumbnail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .spec-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .spec-item {
            padding: 0.75rem 0;
        }
        
        .spec-label {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        
        .spec-value {
            font-weight: 600;
            color: #0f172a;
        }
        
        .favorite-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            background: white;
            transition: all 0.2s;
        }
        
        .favorite-btn:hover {
            border-color: #ef4444;
            background: #fef2f2;
        }
        
        .favorite-btn.active {
            border-color: #ef4444;
            background: #fef2f2;
        }
        
        .favorite-btn.active i {
            color: #ef4444;
            fill: #ef4444;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .row.g-4 > [class*="col-"] {
                margin-bottom: 1.5rem;
            }
            
            .main-image-container {
                margin-bottom: 1rem;
            }
            
            .thumbnail-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .spec-card .card-body {
                padding: 1rem !important;
            }
            
            
            .display-4 {
                font-size: 2rem !important;
            }
            
            .h3 {
                font-size: 1.5rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .thumbnail-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .spec-item {
                padding: 0.5rem 0;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">
            <i class="bi bi-arrow-left me-2"></i> Volver
        </a>

        <div class="row g-5">
            <!-- Galería de Imágenes -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="main-image-container">
                            <?php if (!empty($imagenes)): ?>
                                <img id="mainImage" 
                                     src="<?= UPLOAD_URL . htmlspecialchars($imagenes[0]['imagen_path']) ?>" 
                                     class="main-image" 
                                     alt="<?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-car-front" style="font-size: 6rem; color: #94a3b8;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($imagenes) > 1): ?>
                            <div class="thumbnail-grid p-3">
                                <?php foreach ($imagenes as $index => $img): ?>
                                    <div class="thumbnail-item <?= $index === 0 ? 'active' : '' ?>" 
                                         onclick="changeImage(<?= $index ?>)"
                                         data-index="<?= $index ?>">
                                        <img src="<?= UPLOAD_URL . htmlspecialchars($img['imagen_path']) ?>" 
                                             class="thumbnail-img" 
                                             alt="Imagen <?= $index + 1 ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Información del Vehículo -->
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="display-5 fw-bold text-dark mb-2">
                            <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>
                        </h1>
                        <p class="text-muted fs-5"><?= htmlspecialchars($vehiculo['año']) ?></p>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <button class="favorite-btn <?= $en_favoritos ? 'active' : '' ?>" 
                                onclick="toggleFavorito(<?= $vehiculo['id'] ?>)"
                                id="favoriteBtn">
                            <i class="bi bi-heart<?= $en_favoritos ? '-fill' : '' ?> fs-5"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <p class="display-4 fw-bold text-primary mb-5">
                    <?= formatPrice($vehiculo['precio']) ?>
                </p>

                <!-- Especificaciones -->
                <div class="card spec-card mb-4">
                    <div class="card-body p-4">
                        <h3 class="h4 fw-bold text-dark mb-4">Especificaciones</h3>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="spec-item">
                                    <p class="spec-label mb-1">Kilometraje</p>
                                    <p class="spec-value mb-0"><?= formatKilometraje($vehiculo['kilometraje']) ?></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <p class="spec-label mb-1">Color</p>
                                    <p class="spec-value mb-0"><?= htmlspecialchars($vehiculo['color']) ?></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="spec-item">
                                    <p class="spec-label mb-1">Transmisión</p>
                                    <p class="spec-value mb-0"><?= ucfirst($vehiculo['transmision']) ?></p>
                                </div>
                            </div>
                            <?php if (isset($vehiculo['combustible']) && $vehiculo['combustible']): ?>
                            <div class="col-6">
                                <div class="spec-item">
                                    <p class="spec-label mb-1">Combustible</p>
                                    <p class="spec-value mb-0"><?= htmlspecialchars($vehiculo['combustible']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="col-6">
                                <div class="spec-item">
                                    <p class="spec-label mb-1">Estado</p>
                                    <p class="spec-value mb-0">
                                        <span class="badge bg-success"><?= ucfirst($vehiculo['estado']) ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <?php if ($vehiculo['descripcion']): ?>
                    <div class="card spec-card mb-4">
                        <div class="card-body p-4">
                            <h3 class="h4 fw-bold text-dark mb-3">Descripción</h3>
                            <p class="text-muted mb-0" style="line-height: 1.7;">
                                <?= nl2br(htmlspecialchars($vehiculo['descripcion'])) ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botones de Acción -->
                <div class="d-grid gap-3 mb-4">
                    <a href="contacto.php?vehiculo_id=<?= $vehiculo['id'] ?>" class="btn btn-primary btn-lg">
                        <i class="bi bi-envelope me-2"></i> Consultar sobre este vehículo
                    </a>
                    <button class="btn btn-outline-primary btn-lg" onclick="agregarAComparador(<?= $vehiculo['id'] ?>)">
                        <i class="bi bi-plus-circle me-2"></i> Agregar al Comparador
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const imagenes = <?= json_encode(array_map(function($img) use ($vehiculo) {
            return UPLOAD_URL . $img['imagen_path'];
        }, $imagenes)) ?>;
        
        let selectedImageIndex = 0;

        function changeImage(index) {
            selectedImageIndex = index;
            document.getElementById('mainImage').src = imagenes[index];
            
            // Actualizar thumbnails activos
            document.querySelectorAll('.thumbnail-item').forEach((item, idx) => {
                if (idx === index) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        function toggleFavorito(vehiculoId) {
            <?php if (!isLoggedIn()): ?>
                if (confirm('Debes iniciar sesión para guardar favoritos. ¿Deseas ir al login?')) {
                    window.location.href = 'login.php';
                }
                return;
            <?php endif; ?>
            
            fetch('api/favoritos.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({vehicle_id: vehiculoId})
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error('Network response was not ok');
                }
                return r.json();
            })
            .then(data => {
                if (data.success) {
                    const btn = document.getElementById('favoriteBtn');
                    const icon = btn.querySelector('i');
                    
                    if (data.action === 'added') {
                        btn.classList.add('active');
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                        if (typeof notifications !== 'undefined') {
                            notifications.success(data.message || 'Agregado a favoritos');
                        }
                    } else if (data.action === 'removed') {
                        btn.classList.remove('active');
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        if (typeof notifications !== 'undefined') {
                            notifications.info(data.message || 'Eliminado de favoritos');
                        }
                    }
                } else {
                    if (typeof notifications !== 'undefined') {
                        notifications.error(data.message || 'Error al actualizar favoritos');
                    } else {
                        alert(data.message || 'Error al actualizar favoritos');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof notifications !== 'undefined') {
                    notifications.error('Error al actualizar favoritos. Por favor, intenta nuevamente.');
                } else {
                    alert('Error al actualizar favoritos');
                }
            });
        }

        function agregarAComparador(vehiculoId) {
            let comparador = JSON.parse(localStorage.getItem('comparador') || '[]');
            if (!comparador.includes(vehiculoId)) {
                comparador.push(vehiculoId);
                localStorage.setItem('comparador', JSON.stringify(comparador));
                
                // Mostrar notificación
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Agregado';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            } else {
                alert('Este vehículo ya está en el comparador');
            }
        }

    </script>
<?php include 'includes/footer.php'; ?>

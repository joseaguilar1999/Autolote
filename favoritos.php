<?php
require_once 'config/config.php';
requireLogin();

$conn = getDBConnection();

// Obtener favoritos del usuario con información de vehículos
$stmt = $conn->prepare("SELECT f.id as favorito_id, f.fecha_creacion as favorito_fecha,
        v.*, 
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal,
        (SELECT GROUP_CONCAT(imagen_path) FROM vehiculos_imagenes WHERE vehiculo_id = v.id) as imagenes
        FROM favoritos f
        INNER JOIN vehiculos v ON f.vehiculo_id = v.id
        WHERE f.usuario_id = ? AND v.estado = 'disponible'
        ORDER BY f.fecha_creacion DESC");
$stmt->execute([$_SESSION['user_id']]);
$vehiculos = $stmt->fetchAll();
?>
<?php
$page_title = 'Mis Favoritos';
include 'includes/head.php';
?>
    <style>
        body {
            background-color: #f8fafc;
        }
        
        .favorite-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .favorite-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .vehicle-image-container {
            aspect-ratio: 16/9;
            background-color: #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .vehicle-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-icon {
            width: 80px;
            height: 80px;
            background-color: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="display-5 fw-bold text-dark mb-5">Mis Favoritos</h1>

        <?php if (empty($vehiculos)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-heart" style="font-size: 2.5rem; color: #94a3b8;"></i>
                </div>
                <p class="fs-5 text-muted mb-4">No tienes vehículos favoritos aún</p>
                <a href="index.php?catalogo=1" class="btn btn-primary">
                    Explorar Catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <?php 
                    $imagenes = [];
                    if ($vehiculo['imagenes']) {
                        $imagenes = explode(',', $vehiculo['imagenes']);
                    } elseif ($vehiculo['imagen_principal']) {
                        $imagenes = [$vehiculo['imagen_principal']];
                    }
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card favorite-card h-100">
                            <a href="detalle.php?id=<?= $vehiculo['id'] ?>" class="text-decoration-none">
                                <div class="vehicle-image-container">
                                    <?php if (!empty($imagenes)): ?>
                                        <img src="<?= UPLOAD_URL . htmlspecialchars(trim($imagenes[0])) ?>" 
                                             class="vehicle-image" 
                                             alt="<?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-car-front" style="font-size: 4rem; color: #94a3b8;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            
                            <div class="card-body p-4">
                                <a href="detalle.php?id=<?= $vehiculo['id'] ?>" class="text-decoration-none">
                                    <h3 class="h5 fw-bold text-dark mb-2">
                                        <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>
                                    </h3>
                                    <p class="text-muted small mb-3">
                                        <?= htmlspecialchars($vehiculo['año']) ?> • <?= formatKilometraje($vehiculo['kilometraje']) ?>
                                    </p>
                                    <p class="h4 fw-bold text-primary mb-4">
                                        <?= formatPrice($vehiculo['precio']) ?>
                                    </p>
                                </a>
                                
                                <button class="btn btn-danger w-100" 
                                        onclick="removeFavorite(<?= $vehiculo['favorito_id'] ?>, <?= $vehiculo['id'] ?>)"
                                        data-favorite-id="<?= $vehiculo['favorito_id'] ?>">
                                    <i class="bi bi-trash me-2"></i> Eliminar de Favoritos
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function removeFavorite(favoriteId, vehicleId) {
            if (!confirm('¿Estás seguro de eliminar este vehículo de tus favoritos?')) {
                return;
            }
            
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';
            
            fetch('api/favoritos.php?id=' + favoriteId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(r => {
                if (!r.ok) {
                    return r.json().then(data => Promise.reject(data));
                }
                return r.json();
            })
            .then(data => {
                // Remover el card del DOM con animación
                const card = btn.closest('.col-md-6');
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Si no quedan favoritos, recargar página para mostrar estado vacío
                    const remainingCards = document.querySelectorAll('.favorite-card');
                    if (remainingCards.length === 0) {
                        location.reload();
                    }
                }, 300);
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.error || 'Error al eliminar favorito');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    </script>
<?php include 'includes/footer.php'; ?>

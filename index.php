<?php
require_once 'config/config.php';

$conn = getDBConnection();

// Obtener vehículos destacados (si existe el campo featured, sino los primeros 3)
try {
    $stmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'featured'");
    $hasFeatured = $stmt->rowCount() > 0;
} catch(PDOException $e) {
    $hasFeatured = false;
}

if ($hasFeatured) {
    $stmt = $conn->query("SELECT v.*, 
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
        FROM vehiculos v 
        WHERE v.estado = 'disponible' AND v.featured = 1 
        ORDER BY v.fecha_creacion DESC 
        LIMIT 3");
} else {
    $stmt = $conn->query("SELECT v.*, 
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
        FROM vehiculos v 
        WHERE v.estado = 'disponible' 
        ORDER BY v.fecha_creacion DESC 
        LIMIT 3");
}
$vehiculos_destacados = $stmt->fetchAll();

// Obtener parámetros de filtros (para cuando se muestre el catálogo completo)
$marca = $_GET['marca'] ?? '';
$transmision = $_GET['transmision'] ?? '';
$combustible = $_GET['combustible'] ?? '';
$mostrar_catalogo = $_GET['catalogo'] ?? false;

// Construir consulta para catálogo completo
$sql = "SELECT v.*, 
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
        FROM vehiculos v 
        WHERE v.estado = 'disponible'";

$params = [];

if ($marca) {
    $sql .= " AND v.marca = ?";
    $params[] = $marca;
}

if ($transmision) {
    $sql .= " AND v.transmision = ?";
    $params[] = $transmision;
}

if ($combustible) {
    // Verificar si existe el campo combustible
    try {
        $checkStmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'combustible'");
        if ($checkStmt->rowCount() > 0) {
            $sql .= " AND v.combustible = ?";
            $params[] = $combustible;
        }
    } catch(PDOException $e) {
        // Campo no existe, ignorar
    }
}

$sql .= " ORDER BY v.fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll();

// Obtener marcas únicas para el filtro
$stmt = $conn->query("SELECT DISTINCT marca FROM vehiculos WHERE estado = 'disponible' ORDER BY marca");
$marcas = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Verificar si existe el campo combustible
$hasCombustible = false;
try {
    $checkStmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'combustible'");
    $hasCombustible = $checkStmt->rowCount() > 0;
} catch(PDOException $e) {
    $hasCombustible = false;
}

// Obtener tipos de combustible únicos para el filtro
$combustibles = [];
if ($hasCombustible) {
    $stmt = $conn->query("SELECT DISTINCT combustible FROM vehiculos WHERE estado = 'disponible' AND combustible IS NOT NULL ORDER BY combustible");
    $combustibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Determinar título de la página
if ($mostrar_catalogo || !empty($marca) || !empty($transmision) || !empty($combustible)) {
    $page_title = 'Catálogo de Vehículos';
} else {
    $page_title = 'Encuentra el Auto de tus Sueños';
}
?>
<?php
include 'includes/head.php';
?>
    <style>
        body {
            background: linear-gradient(to bottom, #f8fafc 0%, #e0f2fe 50%, #f8fafc 100%);
            min-height: 100vh;
        }
        
        .hero-section {
            position: relative;
            padding: 100px 0;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, transparent 50%, rgba(249, 115, 22, 0.1) 100%);
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
        }
        
        .feature-card {
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .feature-icon.blue {
            background-color: #dbeafe;
        }
        
        .feature-icon.orange {
            background-color: #fed7aa;
        }
        
        .feature-icon.green {
            background-color: #d1fae5;
        }
        
        .vehiculo-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        .vehiculo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .form-control-lg, .form-select-lg {
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .vehiculo-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .vehiculo-img-container {
            aspect-ratio: 16/9;
            background-color: #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .btn-hero {
            padding: 12px 32px;
            font-size: 1.125rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        
        footer {
            background-color: #0f172a;
            color: #94a3b8;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem !important;
            }
            
            .hero-content p {
                font-size: 1rem !important;
            }
            
            .btn-hero {
                padding: 10px 24px;
                font-size: 1rem;
            }
            
            .row.g-4 > [class*="col-"] {
                margin-bottom: 1.5rem;
            }
            
            .row.g-3 > [class*="col-"] {
                margin-bottom: 1rem;
            }
            
            .form-control-lg,
            .form-select-lg {
                font-size: 1rem;
                padding: 0.5rem 0.75rem;
            }
            
            .vehiculo-card {
                margin-bottom: 0;
            }
            
            .card-body.p-4 {
                padding: 0.75rem !important;
            }
            
            .vehiculo-card h3 {
                font-size: 0.95rem !important;
                margin-bottom: 0.5rem !important;
            }
            
            .vehiculo-card .text-muted {
                font-size: 0.75rem !important;
                margin-bottom: 0.5rem !important;
            }
            
            .vehiculo-card .h4 {
                font-size: 1.1rem !important;
            }
            
            .vehiculo-img-container {
                aspect-ratio: 4/3;
            }
            
            .feature-card .card-body {
                padding: 2rem !important;
            }
            
            .feature-icon {
                width: 48px;
                height: 48px;
            }
            
            .feature-icon i {
                font-size: 1.5rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 1.5rem !important;
            }
            
            .display-5 {
                font-size: 1.75rem !important;
            }
            
            .h4 {
                font-size: 1.25rem !important;
            }
            
            .card-body.p-5 {
                padding: 1.5rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section - Solo mostrar si NO estamos en modo catálogo -->
    <?php if (!$mostrar_catalogo && empty($busqueda) && empty($marca) && empty($precio_min) && empty($precio_max) && empty($año_min) && empty($año_max) && empty($transmision) && empty($combustible)): ?>
    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="display-4 fw-bold text-dark mb-4" style="font-size: 3rem;">
                    Encuentra el Auto de tus Sueños
                </h1>
                <p class="lead text-muted mb-5" style="font-size: 1.25rem; max-width: 600px; margin: 0 auto;">
                    La mejor selección de autos usados con garantía y calidad verificada
                </p>
                <a href="index.php?catalogo=1" class="btn btn-primary btn-hero">
                    Ver Catálogo <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon blue">
                                <i class="bi bi-search fs-2 text-primary"></i>
                            </div>
                            <h3 class="h5 fw-bold text-dark mb-3">Búsqueda Fácil</h3>
                            <p class="text-muted mb-0">Filtros avanzados para encontrar exactamente lo que buscas</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon orange">
                                <i class="bi bi-shield-check fs-2 text-warning"></i>
                            </div>
                            <h3 class="h5 fw-bold text-dark mb-3">Vehículos Verificados</h3>
                            <p class="text-muted mb-0">Todos nuestros autos pasan por inspección rigurosa</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-5">
                            <div class="feature-icon green">
                                <i class="bi bi-award fs-2 text-success"></i>
                            </div>
                            <h3 class="h5 fw-bold text-dark mb-3">Mejor Precio</h3>
                            <p class="text-muted mb-0">Garantizamos precios competitivos y transparentes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Vehicles Section -->
    <?php if (!empty($vehiculos_destacados)): ?>
    <section class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5" style="font-size: 2.5rem;">Vehículos Destacados</h2>
            <div class="row g-3 g-md-4">
                <?php foreach ($vehiculos_destacados as $vehiculo): ?>
                    <div class="col-6 col-md-4">
                        <a href="detalle.php?id=<?= $vehiculo['id'] ?>" class="text-decoration-none">
                            <div class="card vehiculo-card h-100">
                                <div class="vehiculo-img-container">
                                    <?php if ($vehiculo['imagen_principal']): ?>
                                        <img src="<?= UPLOAD_URL . htmlspecialchars($vehiculo['imagen_principal']) ?>" 
                                             class="vehiculo-img" 
                                             alt="<?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-car-front fs-1 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-4">
                                    <h3 class="h5 fw-bold text-dark mb-2">
                                        <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>
                                    </h3>
                                    <p class="text-muted small mb-3">
                                        <?= htmlspecialchars($vehiculo['año']) ?> • <?= formatKilometraje($vehiculo['kilometraje']) ?>
                                    </p>
                                    <p class="h4 fw-bold text-primary mb-0">
                                        <?= formatPrice($vehiculo['precio']) ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="index.php?catalogo=1" class="btn btn-outline-primary btn-lg">
                    Ver Todos los Vehículos <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Catálogo Completo -->
    <?php if ($mostrar_catalogo || !empty($busqueda) || !empty($marca) || !empty($precio_min) || !empty($precio_max) || !empty($año_min) || !empty($año_max) || !empty($transmision) || !empty($combustible)): ?>
    <section class="py-5" style="background-color: #f8fafc;">
        <div class="container">
            <h1 class="display-5 fw-bold text-dark mb-5">Catálogo de Vehículos</h1>
            
            <!-- Filtros Mejorados -->
            <div class="card mb-5 border-0 shadow-lg">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h5 fw-bold text-dark mb-0 d-flex align-items-center">
                            <i class="bi bi-funnel me-2"></i> Filtros
                        </h2>
                        <?php if (!empty($marca) || !empty($transmision) || !empty($combustible)): ?>
                        <a href="index.php?catalogo=1" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                        <?php endif; ?>
                    </div>
                    <form method="GET" id="filtrosForm">
                        <input type="hidden" name="catalogo" value="1">
                        <div class="row g-2 g-md-3 filter-row">
                            <div class="col-4 col-md-4">
                                <label class="form-label fw-semibold mb-1 mb-md-2 filter-label">Marca</label>
                                <select class="form-select filter-select" name="marca" style="border: 2px solid #e2e8f0; border-radius: 0.5rem;">
                                    <option value="">Todas</option>
                                    <?php foreach ($marcas as $m): ?>
                                        <option value="<?= htmlspecialchars($m) ?>" <?= $marca === $m ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-md-4">
                                <label class="form-label fw-semibold mb-1 mb-md-2 filter-label">Transmisión</label>
                                <select class="form-select filter-select" name="transmision" style="border: 2px solid #e2e8f0; border-radius: 0.5rem;">
                                    <option value="">Todas</option>
                                    <option value="manual" <?= $transmision === 'manual' ? 'selected' : '' ?>>Manual</option>
                                    <option value="automatica" <?= $transmision === 'automatica' ? 'selected' : '' ?>>Automática</option>
                                </select>
                            </div>
                            <?php if ($hasCombustible && !empty($combustibles)): ?>
                            <div class="col-4 col-md-4">
                                <label class="form-label fw-semibold mb-1 mb-md-2 filter-label">Combustible</label>
                                <select class="form-select filter-select" name="combustible" style="border: 2px solid #e2e8f0; border-radius: 0.5rem;">
                                    <option value="">Todos</option>
                                    <?php foreach ($combustibles as $comb): ?>
                                        <option value="<?= htmlspecialchars($comb) ?>" <?= $combustible === $comb ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($comb) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contador de Resultados -->
            <?php if (!empty($vehiculos)): ?>
                <p class="text-muted mb-4">
                    <strong><?= count($vehiculos) ?></strong> 
                    vehículo<?= count($vehiculos) !== 1 ? 's' : '' ?> encontrado<?= count($vehiculos) !== 1 ? 's' : '' ?>
                </p>
            <?php endif; ?>

            <!-- Grid de Vehículos -->
            <?php if (empty($vehiculos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                    <p class="text-muted fs-5">No se encontraron vehículos con estos filtros</p>
                    <a href="index.php?catalogo=1" class="btn btn-outline-primary">Ver Todos los Vehículos</a>
                </div>
            <?php else: ?>
                <div class="row g-3 g-md-4">
                    <?php foreach ($vehiculos as $vehiculo): ?>
                        <div class="col-6 col-md-4">
                            <a href="detalle.php?id=<?= $vehiculo['id'] ?>" class="text-decoration-none">
                                <div class="card vehiculo-card h-100 border-0 shadow-sm">
                                    <div class="vehiculo-img-container">
                                        <?php if ($vehiculo['imagen_principal']): ?>
                                            <img src="<?= UPLOAD_URL . htmlspecialchars($vehiculo['imagen_principal']) ?>" 
                                                 class="vehiculo-img" 
                                                 alt="<?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                <i class="bi bi-car-front fs-1 text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body p-4">
                                        <h3 class="h5 fw-bold text-dark mb-2">
                                            <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>
                                        </h3>
                                        <p class="text-muted small mb-3">
                                            <?= htmlspecialchars($vehiculo['año']) ?> • 
                                            <?= formatKilometraje($vehiculo['kilometraje']) ?> • 
                                            <?= ucfirst($vehiculo['transmision']) ?>
                                            <?php if (isset($vehiculo['combustible']) && $vehiculo['combustible']): ?>
                                                • <?= htmlspecialchars($vehiculo['combustible']) ?>
                                            <?php endif; ?>
                                        </p>
                                        <p class="h4 fw-bold text-primary mb-0">
                                            <?= formatPrice($vehiculo['precio']) ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Autolote. Todos los derechos reservados.</p>
        </div>
    </footer>

<script>
    // Filtros en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelects = document.querySelectorAll('.filter-select');
        const filtrosForm = document.getElementById('filtrosForm');
        let timeoutId = null;
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // Pequeño delay para evitar múltiples submits mientras el usuario cambia varios filtros
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    filtrosForm.submit();
                }, 300);
            });
        });
        
        // Agregar indicador visual de carga
        filtrosForm.addEventListener('submit', function() {
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'filter-loading';
            loadingIndicator.innerHTML = '<div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Filtrando...</span>';
            loadingIndicator.style.cssText = 'position: fixed; top: 100px; right: 20px; background: white; padding: 0.75rem 1.25rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 9999; display: flex; align-items: center;';
            document.body.appendChild(loadingIndicator);
        });
    });
</script>

<style>
    .filter-select {
        transition: all 0.2s ease;
    }
    
    .filter-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    .filter-select:hover {
        border-color: #cbd5e1;
    }
    
    .filter-label {
        font-size: 0.875rem;
    }
    
    /* Desktop - Tamaño grande */
    @media (min-width: 768px) {
        .filter-select {
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        
        .filter-label {
            font-size: 0.95rem;
        }
    }
    
    /* Mobile - Tamaño compacto */
    @media (max-width: 767px) {
        .filter-row {
            margin: 0;
        }
        
        .filter-select {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            height: auto;
        }
        
        .filter-label {
            font-size: 0.75rem;
            margin-bottom: 0.25rem !important;
            font-weight: 600;
        }
        
        .filter-row .col-4 {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        
        .card-body {
            padding: 1rem !important;
        }
        
        .card-body .mb-4 {
            margin-bottom: 1rem !important;
        }
    }
    
    /* Mobile muy pequeño */
    @media (max-width: 576px) {
        .filter-select {
            padding: 0.4rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .filter-label {
            font-size: 0.7rem;
        }
        
        .filter-row .col-4 {
            padding-left: 0.15rem;
            padding-right: 0.15rem;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>

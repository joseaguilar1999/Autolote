<?php
require_once 'config/config.php';

$conn = getDBConnection();

// Obtener todos los vehículos disponibles para los selectores
$stmt = $conn->query("SELECT v.*, 
    (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal
    FROM vehiculos v 
    WHERE v.estado = 'disponible' 
    ORDER BY v.marca, v.modelo");
$todos_vehiculos = $stmt->fetchAll();

// Obtener IDs de vehículos a comparar (de localStorage via JS o de URL)
$vehiculos_ids = [];
if (isset($_GET['ids'])) {
    $vehiculos_ids = explode(',', $_GET['ids']);
    $vehiculos_ids = array_filter(array_map('intval', $vehiculos_ids));
    // Limitar a 3 vehículos (desktop) o 2 (móvil)
    $vehiculos_ids = array_slice($vehiculos_ids, 0, 3);
}

// Obtener datos de los vehículos seleccionados
$vehiculos_seleccionados = [];
if (!empty($vehiculos_ids)) {
    $placeholders = str_repeat('?,', count($vehiculos_ids) - 1) . '?';
    $sql = "SELECT v.*, 
            (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1) as imagen_principal,
            (SELECT GROUP_CONCAT(imagen_path) FROM vehiculos_imagenes WHERE vehiculo_id = v.id) as imagenes
            FROM vehiculos v 
            WHERE v.id IN ($placeholders) AND v.estado = 'disponible'";
    $stmt = $conn->prepare($sql);
    $stmt->execute($vehiculos_ids);
    $vehiculos_seleccionados = $stmt->fetchAll();
    
    // Reordenar según el orden de los IDs
    $vehiculos_ordenados = [];
    foreach ($vehiculos_ids as $id) {
        foreach ($vehiculos_seleccionados as $v) {
            if ($v['id'] == $id) {
                $vehiculos_ordenados[] = $v;
                break;
            }
        }
    }
    $vehiculos_seleccionados = $vehiculos_ordenados;
}

// Crear array de 3 slots (pueden ser null)
$slots = [null, null, null];
foreach ($vehiculos_seleccionados as $index => $vehiculo) {
    if ($index < 3) {
        $slots[$index] = $vehiculo;
    }
}
?>
<?php
$page_title = 'Comparador de Vehículos';
include 'includes/head.php';
?>
    <style>
        body {
            background-color: #f8fafc;
        }
        
        .comparator-slot {
            min-height: 500px;
        }
        
        .empty-slot {
            border: 2px dashed #cbd5e1;
            background-color: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 500px;
        }
        
        .vehicle-image-container {
            aspect-ratio: 16/9;
            background-color: #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            width: 100%;
        }
        
        .vehicle-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            position: relative;
            z-index: 1;
        }
        
        .comparison-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .comparison-label {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .comparison-value {
            font-weight: 600;
            color: #0f172a;
        }
        
        .select-icon {
            width: 64px;
            height: 64px;
            background-color: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .card-vehicle {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .card-vehicle:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .empty-slot {
            position: relative;
            z-index: 1;
        }
        
        .row {
            position: relative;
            overflow: visible;
        }
        
        .row > [class*="col-"] {
            position: relative;
            z-index: 1;
            overflow: visible;
        }
        
        .row > [class*="col-"]:first-child {
            z-index: 2;
        }
        
        .row > [class*="col-"]:last-child {
            z-index: 2;
        }
        
        .row > [class*="col-"] > .card {
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .row.g-4 > [class*="col-"] {
                margin-bottom: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .row.g-3 {
                position: relative;
                overflow: visible;
            }
            
            .row.g-3 > [class*="col-"] {
                margin-bottom: 1rem;
                position: relative;
                z-index: 1;
                overflow: visible;
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .row.g-3 > [class*="col-"]:first-child {
                z-index: 2;
                padding-left: 0;
            }
            
            .row.g-3 > [class*="col-"]:last-child {
                z-index: 2;
                padding-right: 0;
            }
            
            .row.g-3 > [class*="col-"] > .card {
                position: relative;
                z-index: 1;
                overflow: hidden;
                margin: 0;
            }
            
            .card-vehicle {
                position: relative;
                z-index: 1;
                overflow: hidden;
                width: 100%;
            }
            
            .card-vehicle .card-body {
                padding: 0.75rem !important;
                position: relative;
                z-index: 1;
                overflow: hidden;
            }
            
            .empty-slot {
                position: relative;
                z-index: 1;
                overflow: hidden;
                width: 100%;
            }
            
            .select-icon {
                width: 40px;
                height: 40px;
            }
            
            .select-icon i {
                font-size: 1.25rem !important;
            }
            
            .vehicle-image-container {
                height: 150px;
                margin-bottom: 0.5rem;
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 100%;
                overflow: hidden;
                display: block;
                box-sizing: border-box;
            }
            
            .vehicle-image {
                position: relative;
                z-index: 1;
                max-width: 100%;
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                box-sizing: border-box;
            }
            
            .comparator-slot,
            .empty-slot {
                min-height: 400px;
            }
            
            .comparison-item {
                padding: 0.375rem 0;
                font-size: 0.875rem;
            }
            
            .comparison-label {
                font-size: 0.75rem;
            }
            
            .comparison-value {
                font-size: 0.875rem;
            }
            
            .card-vehicle h3,
            .card-vehicle h4 {
                font-size: 0.95rem !important;
            }
            
            .card-vehicle .h4 {
                font-size: 1rem !important;
            }
            
            .btn {
                font-size: 0.875rem;
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <h1 class="display-5 fw-bold text-dark mb-5">Comparador de Vehículos</h1>

        <div class="row g-3 g-md-4">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="col-6 col-md-4 <?= $i >= 2 ? 'd-none d-md-block' : '' ?>">
                    <?php if ($slots[$i]): ?>
                        <?php 
                        $vehiculo = $slots[$i];
                        $imagenes = [];
                        if ($vehiculo['imagenes']) {
                            $imagenes = explode(',', $vehiculo['imagenes']);
                        } elseif ($vehiculo['imagen_principal']) {
                            $imagenes = [$vehiculo['imagen_principal']];
                        }
                        ?>
                        <div class="card card-vehicle h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h3 class="h5 fw-bold text-dark mb-0">Vehículo <?= $i + 1 ?></h3>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="removeVehicle(<?= $i ?>)"
                                            data-slot="<?= $i ?>">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="vehicle-image-container">
                                    <?php if (!empty($imagenes)): ?>
                                        <img src="<?= UPLOAD_URL . htmlspecialchars(trim($imagenes[0])) ?>" 
                                             class="vehicle-image" 
                                             alt="<?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <i class="bi bi-car-front" style="font-size: 3rem; color: #94a3b8;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-3">
                                    <h4 class="h5 fw-bold text-dark mb-2">
                                        <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>
                                    </h4>
                                    <p class="h4 fw-bold text-primary mb-3">
                                        <?= formatPrice($vehiculo['precio']) ?>
                                    </p>

                                    <div class="comparison-item">
                                        <span class="comparison-label">Año:</span>
                                        <span class="comparison-value"><?= htmlspecialchars($vehiculo['año']) ?></span>
                                    </div>
                                    <div class="comparison-item">
                                        <span class="comparison-label">Kilometraje:</span>
                                        <span class="comparison-value"><?= formatKilometraje($vehiculo['kilometraje']) ?></span>
                                    </div>
                                    <div class="comparison-item">
                                        <span class="comparison-label">Color:</span>
                                        <span class="comparison-value"><?= htmlspecialchars($vehiculo['color']) ?></span>
                                    </div>
                                    <div class="comparison-item">
                                        <span class="comparison-label">Transmisión:</span>
                                        <span class="comparison-value"><?= ucfirst($vehiculo['transmision']) ?></span>
                                    </div>
                                    <?php if (isset($vehiculo['combustible']) && $vehiculo['combustible']): ?>
                                    <div class="comparison-item">
                                        <span class="comparison-label">Combustible:</span>
                                        <span class="comparison-value"><?= htmlspecialchars($vehiculo['combustible']) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <a href="detalle.php?id=<?= $vehiculo['id'] ?>" 
                                       class="btn btn-primary w-100 mt-3">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card empty-slot h-100">
                            <div class="card-body text-center p-4">
                                <div class="select-icon">
                                    <i class="bi bi-plus-lg fs-2 text-muted"></i>
                                </div>
                                <h3 class="h5 fw-bold text-dark mb-3">Seleccionar Vehículo <?= $i + 1 ?></h3>
                                <select class="form-select" 
                                        id="select-vehicle-<?= $i ?>" 
                                        onchange="selectVehicle(<?= $i ?>, this.value)">
                                    <option value="">Elegir vehículo</option>
                                    <?php 
                                    // Filtrar vehículos ya seleccionados en otros slots
                                    $vehiculos_disponibles = array_filter($todos_vehiculos, function($v) use ($slots) {
                                        foreach ($slots as $slot) {
                                            if ($slot && $slot['id'] == $v['id']) {
                                                return false;
                                            }
                                        }
                                        return true;
                                    });
                                    ?>
                                    <?php foreach ($vehiculos_disponibles as $vehiculo): ?>
                                        <option value="<?= $vehiculo['id'] ?>">
                                            <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' (' . $vehiculo['año'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <script>
        // Detectar si es móvil
        const isMobile = window.innerWidth <= 768;
        const maxVehicles = isMobile ? 2 : 3;
        
        // Datos de vehículos disponibles para JavaScript
        const allVehicles = <?= json_encode($todos_vehiculos) ?>;
        const currentSlots = <?= json_encode(array_map(function($slot) {
            return $slot ? $slot['id'] : null;
        }, $slots)) ?>;

        function selectVehicle(slotIndex, vehicleId) {
            if (!vehicleId) return;
            
            // Limitar slots en móvil
            if (isMobile && slotIndex >= 2) {
                return;
            }
            
            // Actualizar localStorage
            let comparador = JSON.parse(localStorage.getItem('comparador') || '[]');
            
            // Limitar a maxVehicles en móvil
            if (isMobile && comparador.length >= maxVehicles && !comparador.includes(parseInt(vehicleId))) {
                alert('En dispositivos móviles solo puedes comparar 2 vehículos a la vez.');
                return;
            }
            
            // Remover el vehículo si ya está en otro slot
            comparador = comparador.filter(id => id != vehicleId);
            
            // Agregar en la posición correcta
            while (comparador.length < slotIndex) {
                comparador.push(null);
            }
            comparador[slotIndex] = parseInt(vehicleId);
            comparador = comparador.filter(id => id !== null);
            
            // Limitar a maxVehicles
            comparador = comparador.slice(0, maxVehicles);
            
            localStorage.setItem('comparador', JSON.stringify(comparador));
            
            // Recargar página con los nuevos IDs
            const ids = comparador.join(',');
            window.location.href = 'comparador.php?ids=' + ids;
        }

        function removeVehicle(slotIndex) {
            // Remover del localStorage
            let comparador = JSON.parse(localStorage.getItem('comparador') || '[]');
            const vehicleId = currentSlots[slotIndex];
            
            if (vehicleId) {
                comparador = comparador.filter(id => id != vehicleId);
                localStorage.setItem('comparador', JSON.stringify(comparador));
            }
            
            // Recargar página
            const ids = comparador.join(',');
            if (ids) {
                window.location.href = 'comparador.php?ids=' + ids;
            } else {
                window.location.href = 'comparador.php';
            }
        }

        // Cargar desde localStorage si no hay parámetros en URL
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('ids')) {
                let comparador = JSON.parse(localStorage.getItem('comparador') || '[]');
                // Limitar a maxVehicles si es móvil
                if (isMobile && comparador.length > maxVehicles) {
                    comparador = comparador.slice(0, maxVehicles);
                    localStorage.setItem('comparador', JSON.stringify(comparador));
                }
                if (comparador.length > 0) {
                    window.location.href = 'comparador.php?ids=' + comparador.join(',');
                }
            } else {
                // Limitar vehículos en URL si es móvil
                if (isMobile) {
                    const ids = urlParams.get('ids').split(',').slice(0, maxVehicles);
                    if (ids.length < urlParams.get('ids').split(',').length) {
                        window.location.href = 'comparador.php?ids=' + ids.join(',');
                    }
                }
            }
        });
        
        // Actualizar al cambiar tamaño de ventana (con debounce)
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                const newIsMobile = window.innerWidth <= 768;
                if (newIsMobile !== isMobile) {
                    // Recargar si cambia el tipo de dispositivo
                    window.location.reload();
                }
            }, 250);
        });
    </script>
<?php include 'includes/footer.php'; ?>

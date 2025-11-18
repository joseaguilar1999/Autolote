<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();
$vehiculo = null;
$imagenes = [];
$id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Manejar eliminación de imagen
if (isset($_GET['eliminar_imagen'])) {
    $imagen_id = $_GET['eliminar_imagen'];
    $stmt = $conn->prepare("SELECT vehiculo_id, imagen_path FROM vehiculos_imagenes WHERE id = ?");
    $stmt->execute([$imagen_id]);
    $img_data = $stmt->fetch();
    
    if ($img_data) {
        // Eliminar archivo físico
        $file_path = UPLOAD_DIR . $img_data['imagen_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Eliminar registro de BD
        $stmt = $conn->prepare("DELETE FROM vehiculos_imagenes WHERE id = ?");
        $stmt->execute([$imagen_id]);
        
        header('Location: vehiculo_form.php?id=' . $img_data['vehiculo_id'] . '&success=imagen_eliminada');
        exit;
    }
}

// Manejar marcar imagen principal
if (isset($_GET['marcar_principal'])) {
    $imagen_id = $_GET['marcar_principal'];
    $stmt = $conn->prepare("SELECT vehiculo_id FROM vehiculos_imagenes WHERE id = ?");
    $stmt->execute([$imagen_id]);
    $img_data = $stmt->fetch();
    
    if ($img_data) {
        // Quitar principal de todas las imágenes del vehículo
        $stmt = $conn->prepare("UPDATE vehiculos_imagenes SET es_principal = 0 WHERE vehiculo_id = ?");
        $stmt->execute([$img_data['vehiculo_id']]);
        
        // Marcar esta como principal
        $stmt = $conn->prepare("UPDATE vehiculos_imagenes SET es_principal = 1 WHERE id = ?");
        $stmt->execute([$imagen_id]);
        
        header('Location: vehiculo_form.php?id=' . $img_data['vehiculo_id'] . '&success=imagen_principal');
        exit;
    }
}

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM vehiculos WHERE id = ?");
    $stmt->execute([$id]);
    $vehiculo = $stmt->fetch();
    
    if ($vehiculo) {
        $stmt = $conn->prepare("SELECT * FROM vehiculos_imagenes WHERE vehiculo_id = ? ORDER BY es_principal DESC, orden ASC");
        $stmt->execute([$id]);
        $imagenes = $stmt->fetchAll();
    }
}

// Verificar si existe el campo combustible
try {
    $stmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'combustible'");
    $hasCombustible = $stmt->rowCount() > 0;
} catch(PDOException $e) {
    $hasCombustible = false;
}

// Verificar si existe el campo featured
try {
    $stmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'featured'");
    $hasFeatured = $stmt->rowCount() > 0;
} catch(PDOException $e) {
    $hasFeatured = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $año = intval($_POST['año'] ?? 0);
    $precio = floatval($_POST['precio'] ?? 0);
    $kilometraje = intval($_POST['kilometraje'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $transmision = $_POST['transmision'] ?? '';
    $combustible = $hasCombustible ? ($_POST['combustible'] ?? 'Gasolina') : null;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = $_POST['estado'] ?? 'disponible';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validaciones
    if (!$marca || !$modelo || !$año || !$precio || !$kilometraje || !$color || !$transmision) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } elseif ($año < 1900 || $año > date('Y') + 1) {
        $error = 'El año debe ser válido.';
    } elseif ($precio <= 0) {
        $error = 'El precio debe ser mayor a 0.';
    } elseif ($kilometraje < 0) {
        $error = 'El kilometraje no puede ser negativo.';
    } else {
        try {
            if ($id && $vehiculo) {
                // Actualizar
                if ($hasCombustible && $hasFeatured) {
                    $stmt = $conn->prepare("UPDATE vehiculos SET marca = ?, modelo = ?, año = ?, precio = ?, kilometraje = ?, color = ?, transmision = ?, combustible = ?, descripcion = ?, estado = ?, featured = ? WHERE id = ?");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $combustible, $descripcion, $estado, $featured, $id]);
                } elseif ($hasFeatured) {
                    $stmt = $conn->prepare("UPDATE vehiculos SET marca = ?, modelo = ?, año = ?, precio = ?, kilometraje = ?, color = ?, transmision = ?, descripcion = ?, estado = ?, featured = ? WHERE id = ?");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $estado, $featured, $id]);
                } elseif ($hasCombustible) {
                    $stmt = $conn->prepare("UPDATE vehiculos SET marca = ?, modelo = ?, año = ?, precio = ?, kilometraje = ?, color = ?, transmision = ?, combustible = ?, descripcion = ?, estado = ? WHERE id = ?");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $combustible, $descripcion, $estado, $id]);
                } else {
                    $stmt = $conn->prepare("UPDATE vehiculos SET marca = ?, modelo = ?, año = ?, precio = ?, kilometraje = ?, color = ?, transmision = ?, descripcion = ?, estado = ? WHERE id = ?");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $estado, $id]);
                }
                $vehiculo_id = $id;
                $success = 'Vehículo actualizado exitosamente.';
            } else {
                // Crear
                if ($hasCombustible && $hasFeatured) {
                    $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, combustible, descripcion, estado, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $combustible, $descripcion, $estado, $featured]);
                } elseif ($hasFeatured) {
                    $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion, estado, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $estado, $featured]);
                } elseif ($hasCombustible) {
                    $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, combustible, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $combustible, $descripcion, $estado]);
                } else {
                    $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $estado]);
                }
                $vehiculo_id = $conn->lastInsertId();
                $success = 'Vehículo creado exitosamente.';
            }
            
            // Manejar imágenes nuevas
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                        // Validar tipo de archivo
                        $file_type = $_FILES['imagenes']['type'][$key];
                        $file_size = $_FILES['imagenes']['size'][$key];
                        
                        if (!in_array($file_type, $allowed_types)) {
                            continue; // Saltar archivos no permitidos
                        }
                        
                        if ($file_size > $max_size) {
                            continue; // Saltar archivos muy grandes
                        }
                        
                        $file_name = uniqid() . '_' . basename($_FILES['imagenes']['name'][$key]);
                        $file_path = UPLOAD_DIR . $file_name;
                        
                        if (move_uploaded_file($tmp_name, $file_path)) {
                            $es_principal = ($key === 0 && empty($imagenes)) ? 1 : 0;
                            $stmt = $conn->prepare("INSERT INTO vehiculos_imagenes (vehiculo_id, imagen_path, es_principal) VALUES (?, ?, ?)");
                            $stmt->execute([$vehiculo_id, $file_name, $es_principal]);
                        }
                    }
                }
            }
            
            if (isset($_POST['redirect']) && $_POST['redirect'] === 'list') {
                header('Location: vehiculos.php?success=guardado');
            } else {
                header('Location: vehiculo_form.php?id=' . $vehiculo_id . '&success=guardado');
            }
            exit;
        } catch(PDOException $e) {
            $error = 'Error al guardar el vehículo: ' . $e->getMessage();
        }
    }
}

// Obtener estadísticas para el sidebar
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas WHERE estado = 'nueva'");
$consultas_nuevas = $stmt->fetch()['total'];

// Preparar contenido para el layout
ob_start();
?>
<style>
    .form-card {
        border: none;
        border-radius: 1rem;
        background: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .form-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .form-card .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 2px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
    }
    
    .form-card .card-header h5 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    
    .form-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-control,
    .form-select {
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        font-size: 0.9375rem;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }
    
    .input-group-text {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 2px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        padding: 0.75rem 1rem;
    }
    
    .image-preview-container {
        position: relative;
        margin-bottom: 1rem;
        border-radius: 0.75rem;
        overflow: hidden;
    }
    
    .image-preview {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
        border-radius: 0.75rem;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .image-preview:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .image-actions {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        display: flex;
        gap: 0.5rem;
    }
    
    .image-badge {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.375rem 0.875rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
    }
    
    .btn-image-action {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #64748b;
        font-size: 0.875rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-image-action:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    }
    
    .btn-image-action.delete:hover {
        color: #dc2626;
        border-color: #fecaca;
        background: #fee2e2;
    }
    
    .btn-image-action.primary:hover {
        color: #2563eb;
        border-color: #bfdbfe;
        background: #dbeafe;
    }
    
    .image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .image-item {
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .image-item:hover {
        border-color: #667eea;
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .image-item img {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
        display: block;
    }
    
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .form-check-label {
        font-weight: 500;
        color: #475569;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .row > [class*="col-"] {
            margin-bottom: 1rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
        
        .d-flex.gap-2 {
            flex-direction: column;
        }
        
        .d-flex.gap-2 .btn {
            width: 100%;
        }
        
        .image-preview-container {
            margin-bottom: 1rem;
        }
        
        .form-card .card-body {
            padding: 1rem !important;
        }
        
        .image-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.75rem;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-<?= $id ? 'pencil-square' : 'plus-circle' ?>"></i>
            <?= $id ? 'Editar' : 'Nuevo' ?> Vehículo
        </h1>
        <p class="subtitle"><?= $id ? 'Modifica la información del vehículo' : 'Agrega un nuevo vehículo al catálogo' ?></p>
    </div>
    <a href="vehiculos.php" class="btn btn-outline-primary-admin">
        <i class="bi bi-arrow-left me-2"></i> Volver
    </a>
</div>

<?php if ($error): ?>
    <div class="alert-admin alert-danger-admin">
        <i class="bi bi-exclamation-triangle"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert-admin alert-success-admin">
        <?php if ($_GET['success'] === 'guardado'): ?>
            <i class="bi bi-check-circle"></i>
            <span>Vehículo guardado exitosamente.</span>
        <?php elseif ($_GET['success'] === 'imagen_eliminada'): ?>
            <i class="bi bi-check-circle"></i>
            <span>Imagen eliminada exitosamente.</span>
        <?php elseif ($_GET['success'] === 'imagen_principal'): ?>
            <i class="bi bi-check-circle"></i>
            <span>Imagen marcada como principal.</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card form-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-semibold">Información Básica</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Marca *</label>
                                <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($vehiculo['marca'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Modelo *</label>
                                <input type="text" class="form-control" name="modelo" value="<?= htmlspecialchars($vehiculo['modelo'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Año *</label>
                                <input type="number" class="form-control" name="año" value="<?= htmlspecialchars($vehiculo['año'] ?? '') ?>" min="1900" max="<?= date('Y') + 1 ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">Lps.</span>
                                    <input type="number" class="form-control" name="precio" value="<?= htmlspecialchars($vehiculo['precio'] ?? '') ?>" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Kilometraje *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="kilometraje" value="<?= htmlspecialchars($vehiculo['kilometraje'] ?? '') ?>" min="0" required>
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Color *</label>
                                <input type="text" class="form-control" name="color" value="<?= htmlspecialchars($vehiculo['color'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Transmisión *</label>
                                <select class="form-select" name="transmision" required>
                                    <option value="manual" <?= ($vehiculo['transmision'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                                    <option value="automatica" <?= ($vehiculo['transmision'] ?? '') === 'automatica' ? 'selected' : '' ?>>Automática</option>
                                </select>
                            </div>
                            <?php if ($hasCombustible): ?>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Combustible *</label>
                                <select class="form-select" name="combustible" required>
                                    <option value="Gasolina" <?= ($vehiculo['combustible'] ?? 'Gasolina') === 'Gasolina' ? 'selected' : '' ?>>Gasolina</option>
                                    <option value="Diesel" <?= ($vehiculo['combustible'] ?? '') === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                    <option value="Eléctrico" <?= ($vehiculo['combustible'] ?? '') === 'Eléctrico' ? 'selected' : '' ?>>Eléctrico</option>
                                    <option value="Híbrido" <?= ($vehiculo['combustible'] ?? '') === 'Híbrido' ? 'selected' : '' ?>>Híbrido</option>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="disponible" <?= ($vehiculo['estado'] ?? 'disponible') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                    <option value="reservado" <?= ($vehiculo['estado'] ?? '') === 'reservado' ? 'selected' : '' ?>>Reservado</option>
                                    <option value="vendido" <?= ($vehiculo['estado'] ?? '') === 'vendido' ? 'selected' : '' ?>>Vendido</option>
                                </select>
                            </div>
                            <?php if ($hasFeatured): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold d-block">Opciones</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" <?= ($vehiculo['featured'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="featured">
                                        Vehículo Destacado
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="4" placeholder="Descripción detallada del vehículo..."><?= htmlspecialchars($vehiculo['descripcion'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card form-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-semibold">Imágenes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($imagenes)): ?>
                            <div class="mb-4">
                                <h6 class="fw-semibold mb-3">Imágenes Actuales</h6>
                                <div class="row g-3">
                                    <?php foreach ($imagenes as $img): ?>
                                        <div class="col-12">
                                            <div class="image-preview-container">
                                                <img src="<?= UPLOAD_URL . htmlspecialchars($img['imagen_path']) ?>" 
                                                     class="image-preview" 
                                                     alt="Imagen del vehículo">
                                                <?php if ($img['es_principal']): ?>
                                                    <span class="image-badge">Principal</span>
                                                <?php endif; ?>
                                                <div class="image-actions">
                                                    <?php if (!$img['es_principal']): ?>
                                                        <a href="?marcar_principal=<?= $img['id'] ?>&id=<?= $id ?>" 
                                                           class="btn-image-action primary" 
                                                           title="Marcar como principal"
                                                           onclick="return confirm('¿Marcar esta imagen como principal?');">
                                                            <i class="bi bi-star"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?eliminar_imagen=<?= $img['id'] ?>&id=<?= $id ?>" 
                                                       class="btn-image-action delete" 
                                                       title="Eliminar imagen"
                                                       onclick="return confirm('¿Estás seguro de eliminar esta imagen?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <label class="form-label fw-semibold">Agregar Nuevas Imágenes</label>
                            <input type="file" class="form-control" name="imagenes[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp,image/gif">
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Formatos permitidos: JPG, PNG, WEBP, GIF. Tamaño máximo: 5MB por imagen.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2 justify-content-end mt-4 flex-wrap">
            <a href="vehiculos.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-2"></i> Cancelar
            </a>
            <button type="submit" name="redirect" value="list" class="btn btn-outline-primary-admin">
                <i class="bi bi-check-lg me-2"></i> Guardar y Volver
            </button>
            <button type="submit" class="btn btn-primary-admin">
                <i class="bi bi-save me-2"></i> Guardar
            </button>
        </div>
    </form>
<?php
$admin_content = ob_get_clean();
$page_title = ($id ? 'Editar' : 'Nuevo') . ' Vehículo';
include '../includes/admin_layout.php';
?>

<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();
$vehiculo = null;
$imagenes = [];
$id = $_GET['id'] ?? 0;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $año = $_POST['año'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $kilometraje = $_POST['kilometraje'] ?? '';
    $color = $_POST['color'] ?? '';
    $transmision = $_POST['transmision'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $estado = $_POST['estado'] ?? 'disponible';
    
    if ($marca && $modelo && $año && $precio && $kilometraje && $color && $transmision) {
        if ($id && $vehiculo) {
            // Actualizar
            $stmt = $conn->prepare("UPDATE vehiculos SET marca = ?, modelo = ?, año = ?, precio = ?, kilometraje = ?, color = ?, transmision = ?, descripcion = ?, estado = ? WHERE id = ?");
            $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $estado, $id]);
            $vehiculo_id = $id;
        } else {
            // Crear
            $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$marca, $modelo, $año, $precio, $kilometraje, $color, $transmision, $descripcion, $estado]);
            $vehiculo_id = $conn->lastInsertId();
        }
        
        // Manejar imágenes
        if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
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
        
        header('Location: vehiculos.php?success=guardado');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Editar' : 'Nuevo' ?> Vehículo - Autolote</title>
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

    <div class="container my-4">
        <h2><?= $id ? 'Editar' : 'Nuevo' ?> Vehículo</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">Información Básica</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Marca *</label>
                                <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($vehiculo['marca'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Modelo *</label>
                                <input type="text" class="form-control" name="modelo" value="<?= htmlspecialchars($vehiculo['modelo'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Año *</label>
                                <input type="number" class="form-control" name="año" value="<?= htmlspecialchars($vehiculo['año'] ?? '') ?>" min="1900" max="2024" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Precio *</label>
                                <input type="number" class="form-control" name="precio" value="<?= htmlspecialchars($vehiculo['precio'] ?? '') ?>" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kilometraje *</label>
                                <input type="number" class="form-control" name="kilometraje" value="<?= htmlspecialchars($vehiculo['kilometraje'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Color *</label>
                                <input type="text" class="form-control" name="color" value="<?= htmlspecialchars($vehiculo['color'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Transmisión *</label>
                                <select class="form-select" name="transmision" required>
                                    <option value="manual" <?= ($vehiculo['transmision'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual</option>
                                    <option value="automatica" <?= ($vehiculo['transmision'] ?? '') === 'automatica' ? 'selected' : '' ?>>Automática</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="disponible" <?= ($vehiculo['estado'] ?? 'disponible') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                    <option value="reservado" <?= ($vehiculo['estado'] ?? '') === 'reservado' ? 'selected' : '' ?>>Reservado</option>
                                    <option value="vendido" <?= ($vehiculo['estado'] ?? '') === 'vendido' ? 'selected' : '' ?>>Vendido</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="4"><?= htmlspecialchars($vehiculo['descripcion'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">Imágenes</div>
                        <div class="card-body">
                            <?php if (!empty($imagenes)): ?>
                                <div class="mb-3">
                                    <h6>Imágenes Actuales</h6>
                                    <div class="row">
                                        <?php foreach ($imagenes as $img): ?>
                                            <div class="col-md-4 mb-2">
                                                <img src="<?= UPLOAD_URL . htmlspecialchars($img['imagen_path']) ?>" class="img-fluid rounded">
                                                <?php if ($img['es_principal']): ?>
                                                    <small class="text-success">Principal</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Agregar Imágenes</label>
                                <input type="file" class="form-control" name="imagenes[]" multiple accept="image/*">
                                <small class="text-muted">Puedes seleccionar múltiples imágenes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="vehiculos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seed Vehicles - Resultado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-check-circle me-2"></i> Operación Exitosa</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong><?= $success_message ?? 'Vehículos insertados correctamente' ?></strong>
                </div>
                
                <?php if (isset($messages) && !empty($messages)): ?>
                    <h5>Vehículos insertados:</h5>
                    <ul class="list-group mb-3">
                        <?php foreach ($messages as $msg): ?>
                            <li class="list-group-item"><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <div class="d-flex gap-2">
                    <a href="../index.php" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i> Ir al Inicio
                    </a>
                    <a href="../admin/vehiculos.php" class="btn btn-outline-primary">
                        <i class="bi bi-car-front me-2"></i> Ver Vehículos en Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


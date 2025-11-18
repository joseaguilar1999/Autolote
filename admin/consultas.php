<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Manejar cambio de estado
if (isset($_POST['cambiar_estado'])) {
    $id = intval($_POST['id'] ?? 0);
    $nuevo_estado = $_POST['estado'] ?? '';
    
    if ($id > 0) {
        $valid_statuses = ['nueva', 'leida', 'respondida'];
        if (in_array($nuevo_estado, $valid_statuses)) {
            // Verificar que existe antes de actualizar
            $checkStmt = $conn->prepare("SELECT id FROM consultas WHERE id = ?");
            $checkStmt->execute([$id]);
            if ($checkStmt->fetch()) {
                $stmt = $conn->prepare("UPDATE consultas SET estado = ? WHERE id = ?");
                $stmt->execute([$nuevo_estado, $id]);
                header('Location: consultas.php?success=actualizado');
                exit;
            }
        }
    }
    header('Location: consultas.php?error=no_encontrado');
    exit;
}

// Configuración de paginación
$items_per_page = 15;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Contar total de consultas
$countStmt = $conn->query("SELECT COUNT(*) as total FROM consultas");
$total_items = $countStmt->fetch()['total'];

// Calcular información de paginación
$pagination_info = getPaginationInfo($total_items, $items_per_page, $current_page);

// Obtener consultas con información de vehículos (paginadas)
$stmt = $conn->prepare("SELECT c.*, v.marca, v.modelo, v.id as vehiculo_id
    FROM consultas c 
    LEFT JOIN vehiculos v ON c.vehiculo_id = v.id 
    ORDER BY 
        CASE c.estado 
            WHEN 'nueva' THEN 1 
            WHEN 'leida' THEN 2 
            WHEN 'respondida' THEN 3 
        END,
        c.fecha_creacion DESC
    LIMIT ? OFFSET ?");
$stmt->execute([$items_per_page, $pagination_info['offset']]);
$consultas = $stmt->fetchAll();

// Construir URL base para paginación (sin parámetro page)
$base_url = 'consultas.php';
$query_params = [];
// Mantener otros parámetros GET si existen (excepto 'page')
foreach ($_GET as $key => $value) {
    if ($key !== 'page') {
        $query_params[$key] = $value;
    }
}
$pagination_url = $base_url;

// Obtener estadísticas para el sidebar
$stmt = $conn->query("SELECT COUNT(*) as total FROM consultas WHERE estado = 'nueva'");
$consultas_nuevas = $stmt->fetch()['total'];
?>
<?php
$page_title = 'Gestión de Consultas';
include '../includes/head.php';
?>
    <style>
        body {
            background-color: #f8fafc;
        }
        
        .admin-sidebar {
            min-height: 100vh;
            background-color: #0f172a;
            padding: 1.5rem 0;
        }
        
        .admin-sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: all 0.2s;
        }
        
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #1e293b;
            color: #fff;
        }
        
        .inquiries-table-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .table {
            font-size: 0.9rem;
        }
        
        .badge-status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-status.nueva {
            background-color: #dc2626;
            color: white;
        }
        
        .badge-status.leida {
            background-color: #f59e0b;
            color: white;
        }
        
        .badge-status.respondida {
            background-color: #10b981;
            color: white;
        }
        
        .status-select {
            min-width: 140px;
        }
        
        .btn-view {
            padding: 0.25rem 0.5rem;
            border: none;
            background: transparent;
            color: #64748b;
            transition: all 0.2s;
        }
        
        .btn-view:hover {
            color: #2563eb;
            background-color: #eff6ff;
        }
        
        .modal-details {
            font-size: 0.95rem;
        }
        
        .modal-details .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .modal-details .detail-value {
            color: #0f172a;
            margin-bottom: 1rem;
        }
        
        .new-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
                overflow-y: auto;
            }
            
            .admin-sidebar.show {
                left: 0;
            }
            
            .col-md-10 {
                width: 100%;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-2 admin-sidebar">
                <div class="px-3 mb-4">
                    <a href="index.php" class="d-flex align-items-center text-white text-decoration-none">
                        <i class="bi bi-car-front fs-3 me-2"></i>
                        <span class="fs-5 fw-bold">Autolote</span>
                    </a>
                    <small class="text-muted d-block mt-1">Panel Administrativo</small>
                </div>
                
                <nav class="nav flex-column">
                    <a href="index.php" class="nav-link">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="vehiculos.php" class="nav-link">
                        <i class="bi bi-car-front me-2"></i> Vehículos
                    </a>
                    <a href="usuarios.php" class="nav-link">
                        <i class="bi bi-people me-2"></i> Usuarios
                    </a>
                    <a href="consultas.php" class="nav-link active position-relative">
                        <i class="bi bi-envelope me-2"></i> Consultas
                        <?php if ($consultas_nuevas > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill new-badge" style="font-size: 0.65rem;">
                                <?= $consultas_nuevas ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <hr class="text-muted my-2">
                    <a href="../index.php" class="nav-link">
                        <i class="bi bi-house me-2"></i> Ver Sitio
                    </a>
                    <a href="../logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Salir
                    </a>
                </nav>
            </div>

            <!-- Contenido Principal -->
            <div class="col-md-10">
                <div class="p-4">
                    <h1 class="display-5 fw-bold text-dark mb-4">Gestión de Consultas</h1>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>Estado actualizado exitosamente
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php if ($_GET['error'] === 'no_encontrado'): ?>
                                <i class="bi bi-exclamation-triangle me-2"></i>Consulta no encontrada
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle me-2"></i>Error al procesar la solicitud
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card inquiries-table-card">
                        <div class="card-body p-0">
                            <?php if (empty($consultas)): ?>
                                <div class="p-5 text-center text-muted">
                                    <i class="bi bi-envelope fs-1 d-block mb-3"></i>
                                    <p class="mb-0">No hay consultas registradas</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Vehículo</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Teléfono</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($consultas as $c): ?>
                                                <tr class="<?= $c['estado'] === 'nueva' ? 'table-warning' : '' ?>">
                                                    <td>
                                                        <?php if ($c['vehiculo_id']): ?>
                                                            <a href="../detalle.php?id=<?= $c['vehiculo_id'] ?>" 
                                                               class="text-decoration-none text-dark fw-semibold"
                                                               target="_blank">
                                                                <?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">Consulta General</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                                                    <td>
                                                        <a href="mailto:<?= htmlspecialchars($c['email']) ?>" 
                                                           class="text-decoration-none">
                                                            <?= htmlspecialchars($c['email']) ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if ($c['telefono']): ?>
                                                            <a href="tel:<?= htmlspecialchars($c['telefono']) ?>" 
                                                               class="text-decoration-none">
                                                                <?= htmlspecialchars($c['telefono']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y H:i', strtotime($c['fecha_creacion'])) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                            <select name="estado" 
                                                                    class="form-select form-select-sm status-select" 
                                                                    onchange="this.form.submit()"
                                                                    style="min-width: 140px;">
                                                                <option value="nueva" <?= $c['estado'] === 'nueva' ? 'selected' : '' ?>>Nueva</option>
                                                                <option value="leida" <?= $c['estado'] === 'leida' ? 'selected' : '' ?>>Leída</option>
                                                                <option value="respondida" <?= $c['estado'] === 'respondida' ? 'selected' : '' ?>>Respondida</option>
                                                            </select>
                                                            <input type="hidden" name="cambiar_estado" value="1">
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn-view" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalConsulta<?= $c['id'] ?>"
                                                                title="Ver detalles">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Modal para detalles -->
                                                <div class="modal fade" id="modalConsulta<?= $c['id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Detalles de la Consulta</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body modal-details">
                                                                <div>
                                                                    <div class="detail-label">Vehículo</div>
                                                                    <div class="detail-value">
                                                                        <?php if ($c['vehiculo_id']): ?>
                                                                            <a href="../detalle.php?id=<?= $c['vehiculo_id'] ?>" 
                                                                               class="text-decoration-none"
                                                                               target="_blank">
                                                                                <?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?>
                                                                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">Consulta General</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div>
                                                                    <div class="detail-label">Nombre</div>
                                                                    <div class="detail-value"><?= htmlspecialchars($c['nombre']) ?></div>
                                                                </div>
                                                                
                                                                <div>
                                                                    <div class="detail-label">Email</div>
                                                                    <div class="detail-value">
                                                                        <a href="mailto:<?= htmlspecialchars($c['email']) ?>" 
                                                                           class="text-decoration-none">
                                                                            <?= htmlspecialchars($c['email']) ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div>
                                                                    <div class="detail-label">Teléfono</div>
                                                                    <div class="detail-value">
                                                                        <?php if ($c['telefono']): ?>
                                                                            <a href="tel:<?= htmlspecialchars($c['telefono']) ?>" 
                                                                               class="text-decoration-none">
                                                                                <?= htmlspecialchars($c['telefono']) ?>
                                                                            </a>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">No proporcionado</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div>
                                                                    <div class="detail-label">Mensaje</div>
                                                                    <div class="detail-value">
                                                                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">
                                                                            <?= htmlspecialchars($c['mensaje']) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div>
                                                                    <div class="detail-label">Estado</div>
                                                                    <div class="detail-value">
                                                                        <span class="badge-status <?= $c['estado'] ?>">
                                                                            <?= ucfirst($c['estado']) ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div>
                                                                    <div class="detail-label">Fecha de Consulta</div>
                                                                    <div class="detail-value">
                                                                        <?= date('d/m/Y H:i:s', strtotime($c['fecha_creacion'])) ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                <a href="mailto:<?= htmlspecialchars($c['email']) ?>?subject=Re: Consulta sobre <?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?>" 
                                                                   class="btn btn-primary">
                                                                    <i class="bi bi-envelope me-2"></i> Responder por Email
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <?php if ($pagination_info['total_pages'] > 1): ?>
                                    <div class="card-footer bg-white border-0 py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Mostrando <?= $pagination_info['start_item'] ?>-<?= $pagination_info['end_item'] ?> de <?= $pagination_info['total_items'] ?> consultas
                                            </small>
                                            <?= generatePagination($pagination_info['current_page'], $pagination_info['total_pages'], $pagination_url, $query_params) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= BASE_URL ?>/assets/js/admin-mobile.js"></script>
<?php include '../includes/footer.php'; ?>

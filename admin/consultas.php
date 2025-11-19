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

// Preparar contenido para el layout
ob_start();
?>
<style>
    .status-select {
        min-width: 140px;
        border: 2px solid #e2e8f0;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .status-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }
    
    .btn-view {
        padding: 0.5rem 0.75rem;
        border: 1px solid #e2e8f0;
        background: transparent;
        color: #64748b;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .btn-view:hover {
        color: #667eea;
        background-color: #f8fafc;
        border-color: #667eea;
        transform: translateY(-2px);
    }
    
    .modal-details {
        font-size: 0.95rem;
    }
    
    .modal-details .detail-label {
        font-weight: 600;
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .modal-details .detail-value {
        color: #1e293b;
        margin-bottom: 1rem;
        font-size: 1rem;
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
    
    .table-row-new {
        background-color: #fef3c7 !important;
    }
    
    .table-row-new:hover {
        background-color: #fde68a !important;
    }
</style>

<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-envelope"></i>
            Gestión de Consultas
        </h1>
        <p class="subtitle">Administra las consultas y mensajes de los clientes</p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert-admin alert-success-admin">
        <i class="bi bi-check-circle-fill"></i>
        <span>El estado de la consulta ha sido actualizado correctamente.</span>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert-admin alert-danger-admin">
        <?php if ($_GET['error'] === 'no_encontrado'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>No se pudo encontrar la consulta solicitada. Por favor, verifica la información e intenta nuevamente.</span>
        <?php else: ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Ha ocurrido un error al procesar tu solicitud. Por favor, intenta nuevamente o contacta al administrador del sistema.</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card admin-card">
    <div class="card-body p-0">
        <?php if (empty($consultas)): ?>
            <div class="empty-state-admin">
                <i class="bi bi-envelope"></i>
                <p>No hay consultas registradas</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table">
                    <thead>
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
                            <tr class="<?= $c['estado'] === 'nueva' ? 'table-row-new' : '' ?>">
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
                                                onchange="this.form.submit()">
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
                                                    <span class="badge-admin badge-<?= $c['estado'] === 'nueva' ? 'danger' : ($c['estado'] === 'leida' ? 'warning' : 'success') ?>-admin">
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
                                               class="btn btn-primary-admin">
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
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <small class="text-muted">
                            Mostrando <?= $pagination_info['start_item'] ?>-<?= $pagination_info['end_item'] ?> de <?= $pagination_info['total_items'] ?> consultas
                        </small>
                        <div class="pagination-admin">
                            <?= generatePagination($pagination_info['current_page'], $pagination_info['total_pages'], $pagination_url, $query_params) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php
$admin_content = ob_get_clean();
$page_title = 'Gestión de Consultas';
include '../includes/admin_layout.php';
?>

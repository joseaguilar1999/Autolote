<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Manejar eliminación
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if ($id > 0) {
        // Verificar que existe antes de eliminar
        $checkStmt = $conn->prepare("SELECT id FROM vehiculos WHERE id = ?");
        $checkStmt->execute([$id]);
        if ($checkStmt->fetch()) {
            $stmt = $conn->prepare("DELETE FROM vehiculos WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: vehiculos.php?success=eliminado');
            exit;
        }
    }
    header('Location: vehiculos.php?error=no_encontrado');
    exit;
}

// Configuración de paginación
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Contar total de vehículos
$countStmt = $conn->query("SELECT COUNT(*) as total FROM vehiculos");
$total_items = $countStmt->fetch()['total'];

// Calcular información de paginación
$pagination_info = getPaginationInfo($total_items, $items_per_page, $current_page);

// Obtener vehículos con imágenes (paginados)
$stmt = $conn->prepare("SELECT v.*, 
    (SELECT COUNT(*) FROM vehiculos_imagenes WHERE vehiculo_id = v.id) as num_imagenes,
    COALESCE(
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id AND es_principal = 1 LIMIT 1),
        (SELECT imagen_path FROM vehiculos_imagenes WHERE vehiculo_id = v.id ORDER BY orden ASC, id ASC LIMIT 1)
    ) as imagen_principal
    FROM vehiculos v 
    ORDER BY v.fecha_creacion DESC
    LIMIT ? OFFSET ?");
$stmt->execute([$items_per_page, $pagination_info['offset']]);
$vehiculos = $stmt->fetchAll();

// Construir URL base para paginación (sin parámetro page)
$base_url = 'vehiculos.php';
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
    /* Header Styles */
    .page-header {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.02em;
        margin: 0;
    }
    
    .btn-add-vehicle {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-add-vehicle:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }
    
    /* Alert Styles */
    .alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }
    
    /* Card Styles */
    .vehicles-table-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        background: #ffffff;
        transition: all 0.3s ease;
    }
    
    .vehicles-table-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Table Styles */
    .table {
        font-size: 0.95rem;
        margin: 0;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        color: #475569;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem 1.25rem;
        border-bottom: 2px solid #e2e8f0;
        border-top: none;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .table tbody tr:hover {
        background-color: #f8fafc;
        transform: scale(1.01);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .table tbody td {
        padding: 1.25rem;
        vertical-align: middle;
        color: #334155;
    }
    
    /* Vehicle Image */
    .vehicle-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 0.5rem;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .vehicle-image:hover {
        transform: scale(1.1);
        border-color: #3b82f6;
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
    }
    
    .vehicle-image-placeholder {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }
    
    /* Vehicle Info */
    .vehicle-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    
    .vehicle-meta {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    /* Badge Styles */
    .badge-status {
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .badge-status.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: white;
    }
    
    .badge-status.bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        color: white;
    }
    
    .badge-status.bg-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        color: white;
    }
    
    .badge-featured {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-weight: 600;
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .btn-action {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: transparent;
        color: #64748b;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .btn-action:hover {
        color: #1e293b;
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-action.delete:hover {
        color: #dc2626;
        background-color: #fee2e2;
        border-color: #fecaca;
    }
    
    .btn-action i {
        font-size: 1rem;
    }
    
    /* Empty State */
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        color: #64748b;
        font-size: 1.125rem;
        margin-bottom: 1.5rem;
    }
    
    /* Pagination */
    .pagination {
        margin: 0;
    }
    
    .pagination .page-link {
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 0.875rem;
        margin: 0 0.125rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    .pagination .page-link:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        color: #1e293b;
        transform: translateY(-1px);
    }
    
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-color: #2563eb;
        color: white;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }
    
    .pagination .page-item.disabled .page-link {
        color: #cbd5e1;
        background-color: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }
    
    /* Footer */
    .card-footer {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-top: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
    }
    
    .card-footer small {
        color: #64748b;
        font-weight: 500;
    }
    
    /* Price */
    .price-cell {
        font-weight: 700;
        color: #059669;
        font-size: 1rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .btn-add-vehicle {
            width: 100%;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .table thead th {
            padding: 0.75rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .table tbody td {
            padding: 0.75rem 0.5rem;
        }
        
        .vehicle-image,
        .vehicle-image-placeholder {
            width: 50px;
            height: 50px;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .btn-action {
            width: 100%;
            height: 32px;
        }
        
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .pagination .page-link {
            padding: 0.375rem 0.625rem;
            font-size: 0.875rem;
        }
        
        .card-footer {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center page-header">
    <h1>Gestión de Vehículos</h1>
    <a href="vehiculo_form.php" class="btn btn-primary btn-add-vehicle">
        <i class="bi bi-plus-lg me-2"></i> Agregar Vehículo
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php if ($_GET['success'] === 'eliminado'): ?>
            <i class="bi bi-check-circle me-2"></i>Vehículo eliminado exitosamente
        <?php elseif ($_GET['success'] === 'guardado'): ?>
            <i class="bi bi-check-circle me-2"></i>Vehículo guardado exitosamente
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php if ($_GET['error'] === 'no_encontrado'): ?>
            <i class="bi bi-exclamation-triangle me-2"></i>Vehículo no encontrado
        <?php else: ?>
            <i class="bi bi-exclamation-triangle me-2"></i>Error al procesar la solicitud
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card vehicles-table-card">
    <div class="card-body p-0">
        <?php if (empty($vehiculos)): ?>
            <div class="empty-state">
                <i class="bi bi-car-front d-block"></i>
                <p>No hay vehículos registrados</p>
                <a href="vehiculo_form.php" class="btn btn-primary btn-add-vehicle">
                    <i class="bi bi-plus-lg me-2"></i> Agregar Primer Vehículo
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vehículo</th>
                            <th>Año</th>
                            <th>Precio</th>
                            <th>Kilometraje</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehiculos as $v): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($v['imagen_principal']) && file_exists(UPLOAD_DIR . $v['imagen_principal'])): ?>
                                            <img src="<?= UPLOAD_URL . htmlspecialchars($v['imagen_principal']) ?>" 
                                                 class="vehicle-image me-3" 
                                                 alt="<?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="vehicle-image-placeholder me-3" style="display: none;">
                                                <i class="bi bi-car-front"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="vehicle-image-placeholder me-3">
                                                <i class="bi bi-car-front"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="vehicle-name">
                                                <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>
                                                <?php if (isset($v['featured']) && $v['featured']): ?>
                                                    <span class="badge-featured ms-2">Destacado</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="vehicle-meta">
                                                <?= $v['num_imagenes'] ?> imagen<?= $v['num_imagenes'] != 1 ? 'es' : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?= $v['año'] ?></strong></td>
                                <td class="price-cell"><?= formatPrice($v['precio']) ?></td>
                                <td><?= formatKilometraje($v['kilometraje']) ?></td>
                                <td>
                                    <span class="badge-status bg-<?= $v['estado'] === 'disponible' ? 'success' : ($v['estado'] === 'vendido' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($v['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="vehiculo_form.php?id=<?= $v['id'] ?>" 
                                           class="btn-action" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?eliminar=<?= $v['id'] ?>" 
                                           class="btn-action delete" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Estás seguro de eliminar este vehículo?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pagination_info['total_pages'] > 1): ?>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Mostrando <?= $pagination_info['start_item'] ?>-<?= $pagination_info['end_item'] ?> de <?= $pagination_info['total_items'] ?> vehículos
                        </small>
                        <?= generatePagination($pagination_info['current_page'], $pagination_info['total_pages'], $pagination_url, $query_params) ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php
$admin_content = ob_get_clean();
$page_title = 'Gestión de Vehículos';
include '../includes/admin_layout.php';
?>

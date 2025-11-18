<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Manejar eliminación
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    if ($id > 0) {
        // No permitir eliminar al usuario actual
        if ($id == $_SESSION['user_id']) {
            header('Location: usuarios.php?error=no_auto_eliminar');
            exit;
        }
        
        // No permitir eliminar otros admins
        $stmt = $conn->prepare("SELECT tipo FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user && $user['tipo'] === 'admin') {
            header('Location: usuarios.php?error=no_eliminar_admin');
            exit;
        }
        
        // Verificar que existe antes de eliminar
        if ($user) {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: usuarios.php?success=eliminado');
            exit;
        }
    }
    header('Location: usuarios.php?error=no_encontrado');
    exit;
}

// Configuración de paginación
$items_per_page = 15;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Contar total de usuarios
$countStmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$total_items = $countStmt->fetch()['total'];

// Calcular información de paginación
$pagination_info = getPaginationInfo($total_items, $items_per_page, $current_page);

// Obtener usuarios (paginados)
$stmt = $conn->prepare("SELECT id, nombre, email, telefono, tipo, activo, fecha_registro 
    FROM usuarios 
    ORDER BY fecha_registro DESC
    LIMIT ? OFFSET ?");
$stmt->execute([$items_per_page, $pagination_info['offset']]);
$usuarios = $stmt->fetchAll();

// Construir URL base para paginación (sin parámetro page)
$base_url = 'usuarios.php';
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
$page_title = 'Gestión de Usuarios';
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
        
        .users-table-card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .table {
            font-size: 0.9rem;
        }
        
        .badge-role {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-role.admin {
            background-color: #2563eb;
            color: white;
        }
        
        .badge-role.cliente {
            background-color: #64748b;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            border: none;
            background: transparent;
            color: #64748b;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            color: #0f172a;
            background-color: #f1f5f9;
        }
        
        .btn-action.delete:hover {
            color: #dc2626;
            background-color: #fee2e2;
        }
        
        .btn-action:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            margin-right: 0.75rem;
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
                    <a href="usuarios.php" class="nav-link active">
                        <i class="bi bi-people me-2"></i> Usuarios
                    </a>
                    <a href="consultas.php" class="nav-link position-relative">
                        <i class="bi bi-envelope me-2"></i> Consultas
                        <?php if ($consultas_nuevas > 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" style="font-size: 0.65rem;">
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
                    <h1 class="display-5 fw-bold text-dark mb-4">Gestión de Usuarios</h1>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php if ($_GET['success'] === 'eliminado'): ?>
                                Usuario eliminado exitosamente
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php if ($_GET['error'] === 'no_auto_eliminar'): ?>
                                <i class="bi bi-exclamation-triangle me-2"></i>No puedes eliminar tu propia cuenta
                            <?php elseif ($_GET['error'] === 'no_eliminar_admin'): ?>
                                <i class="bi bi-exclamation-triangle me-2"></i>No se pueden eliminar otros administradores
                            <?php elseif ($_GET['error'] === 'no_encontrado'): ?>
                                <i class="bi bi-exclamation-triangle me-2"></i>Usuario no encontrado
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card users-table-card">
                        <div class="card-body p-0">
                            <?php if (empty($usuarios)): ?>
                                <div class="p-5 text-center text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-3"></i>
                                    <p class="mb-0">No hay usuarios registrados</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Teléfono</th>
                                                <th>Rol</th>
                                                <th>Estado</th>
                                                <th>Fecha Registro</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($usuarios as $u): ?>
                                                <?php 
                                                $is_current_user = $u['id'] == $_SESSION['user_id'];
                                                $initials = strtoupper(substr($u['nombre'], 0, 2));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="user-avatar">
                                                                <?= $initials ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold text-dark">
                                                                    <?= htmlspecialchars($u['nombre']) ?>
                                                                    <?php if ($is_current_user): ?>
                                                                        <span class="badge bg-info ms-2">Tú</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                                    <td>
                                                        <?= $u['telefono'] ? htmlspecialchars($u['telefono']) : '<span class="text-muted">N/A</span>' ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge-role <?= $u['tipo'] ?>">
                                                            <?= ucfirst($u['tipo']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $u['activo'] ? 'success' : 'danger' ?>">
                                                            <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <?php if (!$is_current_user && $u['tipo'] !== 'admin'): ?>
                                                                <a href="?eliminar=<?= $u['id'] ?>" 
                                                                   class="btn-action delete" 
                                                                   title="Eliminar"
                                                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
                                                                    <i class="bi bi-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="btn-action" style="opacity: 0.3; cursor: not-allowed;" title="No se puede eliminar">
                                                                    <i class="bi bi-trash"></i>
                                                                </span>
                                                            <?php endif; ?>
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
                                                Mostrando <?= $pagination_info['start_item'] ?>-<?= $pagination_info['end_item'] ?> de <?= $pagination_info['total_items'] ?> usuarios
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

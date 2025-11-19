<?php
require_once '../config/config.php';
requireAdmin();

$conn = getDBConnection();

// Obtener el ID del administrador más antiguo
$stmt = $conn->query("SELECT id FROM usuarios WHERE tipo = 'admin' ORDER BY fecha_registro ASC LIMIT 1");
$admin_mas_antiguo = $stmt->fetch();
$admin_mas_antiguo_id = $admin_mas_antiguo ? $admin_mas_antiguo['id'] : null;
$es_admin_mas_antiguo = ($admin_mas_antiguo_id && $_SESSION['user_id'] == $admin_mas_antiguo_id);

// Manejar creación/edición de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $tipo = $_POST['tipo'] ?? 'cliente';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (!$nombre || !$email) {
        header('Location: usuarios.php?error=campos_requeridos');
        exit;
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: usuarios.php?error=email_invalido');
        exit;
    }
    
    if ($accion === 'crear') {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header('Location: usuarios.php?error=email_existe');
            exit;
        }
        
        // Validar contraseña para nuevos usuarios
        if (!$password || strlen($password) < 6) {
            header('Location: usuarios.php?error=password_corta');
            exit;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, telefono, password, tipo, activo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $telefono ?: null, $hashed_password, $tipo, $activo]);
        
        header('Location: usuarios.php?success=creado');
        exit;
    } elseif ($accion === 'editar' && $id > 0) {
        // Verificar que existe y obtener su tipo actual
        $stmt = $conn->prepare("SELECT id, tipo FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario_actual = $stmt->fetch();
        
        if (!$usuario_actual) {
            header('Location: usuarios.php?error=no_encontrado');
            exit;
        }
        
        // No permitir cambiar el rol de un administrador, excepto si el usuario actual es el admin más antiguo
        if ($usuario_actual['tipo'] === 'admin' && $tipo !== 'admin') {
            // Solo el administrador más antiguo puede cambiar el rol de otros admins
            if (!$es_admin_mas_antiguo) {
                header('Location: usuarios.php?error=no_cambiar_rol_admin');
                exit;
            }
            // Si es el admin más antiguo, permitir el cambio de rol
        } elseif ($usuario_actual['tipo'] === 'admin' && $tipo === 'admin') {
            // Si es admin y se mantiene como admin, no hacer nada especial
        }
        
        // Verificar si el email ya existe en otro usuario
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            header('Location: usuarios.php?error=email_existe');
            exit;
        }
        
        // Actualizar usuario
        if ($password && strlen($password) >= 6) {
            // Actualizar con nueva contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, password = ?, tipo = ?, activo = ? WHERE id = ?");
            $stmt->execute([$nombre, $email, $telefono ?: null, $hashed_password, $tipo, $activo, $id]);
        } else {
            // Actualizar sin cambiar contraseña
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, tipo = ?, activo = ? WHERE id = ?");
            $stmt->execute([$nombre, $email, $telefono ?: null, $tipo, $activo, $id]);
        }
        
        header('Location: usuarios.php?success=actualizado');
        exit;
    }
}

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

// Obtener usuario para edición si se solicita
$usuario_editar = null;
if (isset($_GET['editar'])) {
    $editar_id = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT id, nombre, email, telefono, tipo, activo FROM usuarios WHERE id = ?");
    $stmt->execute([$editar_id]);
    $usuario_editar = $stmt->fetch();
}

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

// Preparar contenido para el layout
ob_start();
?>
<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        margin-right: 0.75rem;
        flex-shrink: 0;
    }
    
    .badge-you {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        font-weight: 600;
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-left: 0.5rem;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .btn-action {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        text-decoration: none;
        cursor: pointer;
        padding: 0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    
    .btn-action:hover {
        color: #667eea;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(102, 126, 234, 0.2);
    }
    
    .btn-action.delete {
        color: #64748b;
    }
    
    .btn-action.delete:hover {
        color: #dc2626;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-color: #dc2626;
    }
    
    .btn-action i {
        font-size: 1rem;
        display: block;
    }
    
    .btn-action:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    }
</style>

<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-people"></i>
            Gestión de Usuarios
        </h1>
        <p class="subtitle">Administra los usuarios registrados en el sistema</p>
    </div>
    <button type="button" class="btn btn-primary-admin" data-bs-toggle="modal" data-bs-target="#modalUsuario">
        <i class="bi bi-plus-lg me-2"></i> Agregar Usuario
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert-admin alert-success-admin">
        <?php if ($_GET['success'] === 'eliminado'): ?>
            <i class="bi bi-check-circle-fill"></i>
            <span>La cuenta de usuario ha sido eliminada correctamente del sistema.</span>
        <?php elseif ($_GET['success'] === 'creado'): ?>
            <i class="bi bi-check-circle-fill"></i>
            <span>El nuevo usuario ha sido registrado exitosamente y ya puede acceder al sistema.</span>
        <?php elseif ($_GET['success'] === 'actualizado'): ?>
            <i class="bi bi-check-circle-fill"></i>
            <span>La información del usuario ha sido actualizada correctamente.</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert-admin alert-danger-admin">
        <?php if ($_GET['error'] === 'no_auto_eliminar'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Por razones de seguridad, no puedes eliminar tu propia cuenta. Contacta a otro administrador si necesitas realizar esta acción.</span>
        <?php elseif ($_GET['error'] === 'no_eliminar_admin'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>No se pueden eliminar cuentas de administrador. Solo el administrador más antiguo puede gestionar roles de otros administradores.</span>
        <?php elseif ($_GET['error'] === 'no_encontrado'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>No se pudo encontrar el usuario solicitado. Por favor, verifica la información e intenta nuevamente.</span>
        <?php elseif ($_GET['error'] === 'campos_requeridos'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Por favor, completa todos los campos obligatorios marcados con asterisco (*) antes de continuar.</span>
        <?php elseif ($_GET['error'] === 'email_invalido'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>El formato del correo electrónico ingresado no es válido. Por favor, verifica e intenta nuevamente.</span>
        <?php elseif ($_GET['error'] === 'email_existe'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>El correo electrónico ingresado ya está registrado en el sistema. Por favor, utiliza una dirección de correo diferente.</span>
        <?php elseif ($_GET['error'] === 'password_corta'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>La contraseña debe contener al menos 6 caracteres para garantizar la seguridad de la cuenta.</span>
        <?php elseif ($_GET['error'] === 'no_cambiar_rol_admin'): ?>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>No tienes permisos para modificar el rol de otro administrador. Solo el administrador más antiguo puede realizar esta acción.</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="card admin-card">
    <div class="card-body p-0">
        <?php if (empty($usuarios)): ?>
            <div class="empty-state-admin">
                <i class="bi bi-people"></i>
                <p>No hay usuarios registrados</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 admin-table">
                    <thead>
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
                            $puede_cambiar_rol_admin = ($u['tipo'] === 'admin' && $es_admin_mas_antiguo && !$is_current_user) || $u['tipo'] !== 'admin';
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
                                                    <span class="badge-you">Tú</span>
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
                                    <span class="badge-admin badge-<?= $u['tipo'] === 'admin' ? 'primary' : 'info' ?>-admin">
                                        <?= ucfirst($u['tipo']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-admin badge-<?= $u['activo'] ? 'success' : 'danger' ?>-admin">
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
                                        <button type="button" 
                                                class="btn-action" 
                                                title="Editar"
                                                onclick="editarUsuario(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['telefono'] ?? '', ENT_QUOTES) ?>', '<?= $u['tipo'] ?>', <?= $u['activo'] ? 'true' : 'false' ?>, <?= $puede_cambiar_rol_admin ? 'true' : 'false' ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if (!$is_current_user && $u['tipo'] !== 'admin'): ?>
                                            <a href="?eliminar=<?= $u['id'] ?>" 
                                               class="btn-action delete" 
                                               title="Eliminar"
                                               onclick="event.preventDefault(); confirmAction('Esta acción eliminará permanentemente la cuenta del usuario. Esta operación no se puede deshacer. ¿Deseas continuar?', 'Eliminar Usuario', 'danger').then(result => { if(result) window.location.href = this.href; }); return false;">
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
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <small class="text-muted">
                            Mostrando <?= $pagination_info['start_item'] ?>-<?= $pagination_info['end_item'] ?> de <?= $pagination_info['total_items'] ?> usuarios
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

<!-- Modal para Crear/Editar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioLabel">
                    <i class="bi bi-person-plus me-2"></i>
                    <span id="modalTitulo">Agregar Usuario</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accionUsuario" value="crear">
                    <input type="hidden" name="id" id="usuarioId" value="">
                    
                    <div class="mb-3">
                        <label for="nombreUsuario" class="form-label-admin">Nombre Completo *</label>
                        <input type="text" class="form-control-admin" id="nombreUsuario" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="emailUsuario" class="form-label-admin">Email *</label>
                        <input type="email" class="form-control-admin" id="emailUsuario" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefonoUsuario" class="form-label-admin">Teléfono</label>
                        <input type="tel" class="form-control-admin" id="telefonoUsuario" name="telefono">
                    </div>
                    
                    <div class="mb-3">
                        <label for="passwordUsuario" class="form-label-admin">
                            Contraseña <span id="passwordLabel">*</span>
                        </label>
                        <input type="password" class="form-control-admin" id="passwordUsuario" name="password" minlength="6">
                        <small class="text-muted" id="passwordHelp">
                            Mínimo 6 caracteres. Déjalo vacío si no deseas cambiarla (solo edición).
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipoUsuario" class="form-label-admin">Rol *</label>
                        <select class="form-select form-control-admin" id="tipoUsuario" name="tipo" required>
                            <option value="cliente">Cliente</option>
                            <option value="admin">Administrador</option>
                        </select>
                        <small class="text-muted" id="rolHelp" style="display: none;">
                            <i class="bi bi-info-circle"></i> El rol de administrador no puede ser modificado.
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activoUsuario" name="activo" value="1" checked>
                            <label class="form-check-label" for="activoUsuario">
                                Usuario Activo
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary-admin">
                        <i class="bi bi-save"></i>
                        <span id="btnGuardarTexto">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const esAdminMasAntiguo = <?= $es_admin_mas_antiguo ? 'true' : 'false' ?>;

function editarUsuario(id, nombre, email, telefono, tipo, activo, puedeCambiarRol) {
    document.getElementById('modalTitulo').textContent = 'Editar Usuario';
    document.getElementById('accionUsuario').value = 'editar';
    document.getElementById('usuarioId').value = id;
    document.getElementById('nombreUsuario').value = nombre;
    document.getElementById('emailUsuario').value = email;
    document.getElementById('telefonoUsuario').value = telefono || '';
    document.getElementById('tipoUsuario').value = tipo;
    document.getElementById('activoUsuario').checked = activo;
    document.getElementById('passwordUsuario').required = false;
    document.getElementById('passwordLabel').textContent = '';
    document.getElementById('passwordHelp').textContent = 'Déjalo vacío si no deseas cambiar la contraseña.';
    document.getElementById('btnGuardarTexto').textContent = 'Actualizar';
    
    // Deshabilitar el campo de rol si es administrador y el usuario actual NO puede cambiarlo
    const tipoSelect = document.getElementById('tipoUsuario');
    const rolHelp = document.getElementById('rolHelp');
    if (tipo === 'admin' && !puedeCambiarRol) {
        tipoSelect.disabled = true;
        tipoSelect.style.backgroundColor = '#f1f5f9';
        tipoSelect.style.cursor = 'not-allowed';
        rolHelp.style.display = 'block';
        rolHelp.innerHTML = '<i class="bi bi-info-circle"></i> El rol de administrador no puede ser modificado. Solo el administrador más antiguo puede cambiar roles de otros administradores.';
    } else {
        tipoSelect.disabled = false;
        tipoSelect.style.backgroundColor = '';
        tipoSelect.style.cursor = '';
        rolHelp.style.display = 'none';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
}

// Resetear modal al cerrar
document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formUsuario').reset();
    document.getElementById('modalTitulo').textContent = 'Agregar Usuario';
    document.getElementById('accionUsuario').value = 'crear';
    document.getElementById('usuarioId').value = '';
    document.getElementById('passwordUsuario').required = true;
    document.getElementById('passwordLabel').textContent = '*';
    document.getElementById('passwordHelp').textContent = 'Mínimo 6 caracteres.';
    document.getElementById('btnGuardarTexto').textContent = 'Guardar';
    document.getElementById('activoUsuario').checked = true;
    
    // Restaurar el campo de rol
    const tipoSelect = document.getElementById('tipoUsuario');
    const rolHelp = document.getElementById('rolHelp');
    tipoSelect.disabled = false;
    tipoSelect.style.backgroundColor = '';
    tipoSelect.style.cursor = '';
    rolHelp.style.display = 'none';
});
</script>

<?php
$admin_content = ob_get_clean();
$page_title = 'Gestión de Usuarios';
include '../includes/admin_layout.php';
?>

<?php
require_once 'config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if ($nombre && $email && $password && $password === $password_confirm) {
        $conn = getDBConnection();
        
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Este email ya está registrado';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, telefono, password, tipo) VALUES (?, ?, ?, ?, 'cliente')");
            
            if ($stmt->execute([$nombre, $email, $telefono, $hashed_password])) {
                $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
            } else {
                $error = 'Error al registrar usuario';
            }
        }
    } else {
        $error = 'Por favor completa todos los campos correctamente';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Autolote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus fs-1 text-primary"></i>
                            <h2 class="mt-3">Crear Cuenta</h2>
                            <p class="text-muted">Regístrate para guardar favoritos</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="telefono">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" name="password_confirm" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Registrarse</button>
                        </form>
                        
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">¿Ya tienes cuenta? Inicia sesión</a>
                        </div>
                        <div class="text-center mt-2">
                            <a href="index.php" class="text-decoration-none">Volver al inicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


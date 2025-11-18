<?php
require_once 'config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($nombre && $email && $password) {
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
                // Limpiar formulario después de éxito
                $nombre = $email = $telefono = '';
            } else {
                $error = 'Error al registrar usuario';
            }
        }
    } else {
        $error = 'Por favor completa todos los campos requeridos';
    }
}
?>
<?php
$page_title = 'Registro';
include 'includes/head.php';
?>
    <style>
        body {
            background: linear-gradient(to bottom right, #f8fafc 0%, #e0f2fe 50%, #f8fafc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .register-container {
            width: 100%;
            max-width: 32rem;
        }
        
        .register-card {
            border: none;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .icon-container {
            width: 64px;
            height: 64px;
            background-color: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #0f172a;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control {
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        
        .btn-register {
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
        }
        
        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="register-container px-4 py-5">
        <div class="card register-card">
            <div class="card-header text-center py-4 border-0">
                <div class="icon-container">
                    <i class="bi bi-car-front text-white" style="font-size: 2rem;"></i>
                </div>
                <h1 class="h3 fw-bold text-dark mb-0">Registro</h1>
            </div>
            <div class="card-body p-5">
                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre" 
                               name="nombre" 
                               placeholder="Juan Pérez" 
                               value="<?= htmlspecialchars($nombre ?? '') ?>"
                               required
                               autocomplete="name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="tu@email.com" 
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               required
                               autocomplete="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono <span class="text-muted">(opcional)</span></label>
                        <input type="tel" 
                               class="form-control" 
                               id="telefono" 
                               name="telefono" 
                               placeholder="1234567890" 
                               value="<?= htmlspecialchars($telefono ?? '') ?>"
                               autocomplete="tel">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••" 
                               required
                               autocomplete="new-password"
                               minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-register" id="submitBtn">
                        Registrarse
                    </button>
                </form>
                
                <div class="mt-5 text-center">
                    <p class="text-muted mb-0">
                        ¿Ya tienes cuenta?{' '}
                        <a href="login.php" class="text-primary fw-semibold text-decoration-none">
                            Inicia sesión
                        </a>
                    </p>
                    <div class="mt-3">
                        <a href="index.php" class="text-muted text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i> Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando...';
        });
    </script>
<?php include 'includes/footer.php'; ?>

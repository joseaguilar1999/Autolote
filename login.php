<?php
require_once 'config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, nombre, email, password, tipo FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            
            if ($user['tipo'] === 'admin') {
                header('Location: ' . BASE_URL . '/admin/index.php');
            } else {
                header('Location: ' . BASE_URL . '/index.php');
            }
            exit;
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    } else {
        $error = 'Por favor completa todos los campos';
    }
}
?>
<?php
$page_title = 'Iniciar Sesión';
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
        
        .login-container {
            width: 100%;
            max-width: 28rem;
        }
        
        .login-card {
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
        
        .btn-login {
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
    </style>
</head>
<body>
    <div class="login-container px-4">
        <div class="card login-card">
            <div class="card-header text-center py-4 border-0">
                <div class="icon-container">
                    <i class="bi bi-car-front text-white" style="font-size: 2rem;"></i>
                </div>
                <h1 class="h3 fw-bold text-dark mb-0">Iniciar Sesión</h1>
            </div>
            <div class="card-body p-5">
                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="tu@email.com" 
                               required
                               autocomplete="email">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••" 
                               required
                               autocomplete="current-password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login" id="submitBtn">
                        Iniciar Sesión
                    </button>
                </form>
                
                <div class="mt-5 text-center">
                    <p class="text-muted mb-0">
                        ¿No tienes cuenta?{' '}
                        <a href="registro.php" class="text-primary fw-semibold text-decoration-none">
                            Regístrate
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
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando sesión...';
        });
    </script>
<?php include 'includes/footer.php'; ?>

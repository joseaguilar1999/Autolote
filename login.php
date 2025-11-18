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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 
                        0 0 0 1px rgba(255, 255, 255, 0.5);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3), 
                        0 0 0 1px rgba(255, 255, 255, 0.5);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2.5rem 2rem 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }
        
        .icon-container:hover {
            transform: scale(1.1) rotate(5deg);
        }
        
        .icon-container i {
            font-size: 2.5rem;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .card-header h1 {
            color: white;
            font-size: 1.875rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-body {
            padding: 2.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.625rem;
            display: block;
            font-size: 0.875rem;
            letter-spacing: 0.01em;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            padding: 0.875rem 1.125rem;
            border-radius: 0.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
            background: #f8fafc;
        }
        
        .form-control:hover {
            border-color: #cbd5e1;
            background: white;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
            background: white;
            transform: translateY(-1px);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 2;
            transition: color 0.3s ease;
        }
        
        .form-control:focus ~ .input-icon,
        .form-control:not(:placeholder-shown) ~ .input-icon {
            color: #667eea;
        }
        
        .form-control.has-icon {
            padding-left: 2.75rem;
        }
        
        .btn-login {
            width: 100%;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #ef4444;
            color: #991b1b;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .alert-error i {
            font-size: 1.25rem;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.875rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .divider::before {
            margin-right: 0.75rem;
        }
        
        .divider::after {
            margin-left: 0.75rem;
        }
        
        .link-text {
            color: #64748b;
            font-size: 0.9375rem;
        }
        
        .link-text a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
            position: relative;
        }
        
        .link-text a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #667eea;
            transition: width 0.3s ease;
        }
        
        .link-text a:hover::after {
            width: 100%;
        }
        
        .link-text a:hover {
            color: #764ba2;
        }
        
        .back-link {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .back-link:hover {
            color: #667eea;
            transform: translateX(-4px);
        }
        
        .back-link i {
            transition: transform 0.3s ease;
        }
        
        .back-link:hover i {
            transform: translateX(-4px);
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 2rem 1.5rem;
            }
            
            .card-header {
                padding: 2rem 1.5rem 1.5rem;
            }
            
            .icon-container {
                width: 70px;
                height: 70px;
            }
            
            .icon-container i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container px-4">
        <div class="card login-card">
            <div class="card-header text-center border-0">
                <div class="icon-container">
                    <i class="bi bi-car-front"></i>
                </div>
                <h1 class="mb-0">Iniciar Sesión</h1>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" 
                                   class="form-control has-icon" 
                                   id="email" 
                                   name="email" 
                                   placeholder="tu@email.com" 
                                   required
                                   autocomplete="email">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" 
                                   class="form-control has-icon" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required
                                   autocomplete="current-password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login" id="submitBtn">
                        <span class="btn-text">Iniciar Sesión</span>
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <p class="link-text mb-3">
                        ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
                    </p>
                    <a href="index.php" class="back-link">
                        <i class="bi bi-arrow-left"></i>
                        <span>Volver al inicio</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            submitBtn.disabled = true;
            btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2" style="width: 1rem; height: 1rem;"></span>Iniciando sesión...';
        });
        
        // Animación de entrada para los inputs
        document.querySelectorAll('.form-control').forEach((input, index) => {
            input.style.opacity = '0';
            input.style.transform = 'translateY(10px)';
            setTimeout(() => {
                input.style.transition = 'all 0.4s ease';
                input.style.opacity = '1';
                input.style.transform = 'translateY(0)';
            }, 200 + (index * 100));
        });
    </script>
<?php $hide_footer = true; ?>
<?php include 'includes/footer.php'; ?>

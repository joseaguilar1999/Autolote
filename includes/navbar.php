<?php
// Navbar reutilizable - Basado en diseño moderno
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-modern sticky-top">
    <div class="container">
        <a class="navbar-brand-modern d-flex align-items-center" href="index.php">
            <div class="brand-icon-wrapper">
                <i class="bi bi-car-front"></i>
            </div>
            <span class="brand-text">Autolote</span>
        </a>
        
        <!-- Perfil móvil - fuera del menú colapsado, solo en móviles -->
        <?php if (isLoggedIn()): ?>
            <div class="nav-item-modern dropdown d-lg-none me-2">
                <a class="nav-link-modern user-dropdown-toggle-mobile d-flex align-items-center dropdown-toggle" 
                   href="#" 
                   id="userDropdownMobile" 
                   role="button" 
                   data-bs-toggle="dropdown" 
                   data-bs-auto-close="true"
                   aria-expanded="false">
                    <div class="user-avatar user-avatar-mobile">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end" aria-labelledby="userDropdownMobile">
                    <li class="px-3 py-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
                        </div>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li>
                            <a class="dropdown-item-modern" href="admin/index.php">
                                <i class="bi bi-speedometer2"></i>
                                <span>Panel Admin</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider-modern"></li>
                    <?php endif; ?>
                    <li>
                        <a class="dropdown-item-modern" href="favoritos.php">
                            <i class="bi bi-heart"></i>
                            <span>Mis Favoritos</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider-modern"></li>
                    <li>
                        <a class="dropdown-item-modern dropdown-item-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <!-- Botones de login/registro móvil - fuera del menú colapsado -->
            <div class="d-flex align-items-center gap-2 d-lg-none me-2">
                <a class="btn-nav btn-nav-outline btn-nav-mobile" href="login.php">
                    <i class="bi bi-box-arrow-in-right"></i>
                </a>
                <a class="btn-nav btn-nav-primary btn-nav-mobile" href="registro.php">
                    <i class="bi bi-person-plus"></i>
                </a>
            </div>
        <?php endif; ?>
        
        <button class="navbar-toggler-modern" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav-modern me-auto">
                <li class="nav-item-modern">
                    <a class="nav-link-modern <?= ($current_page === 'index.php' && isset($_GET['catalogo'])) ? 'active' : '' ?>" 
                       href="index.php?catalogo=1">
                        <i class="bi bi-grid me-2"></i>
                        <span>Catálogo</span>
                    </a>
                </li>
                <li class="nav-item-modern">
                    <a class="nav-link-modern <?= $current_page === 'comparador.php' ? 'active' : '' ?>" 
                       href="comparador.php">
                        <i class="bi bi-arrow-left-right me-2"></i>
                        <span>Comparador</span>
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav-modern">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item-modern dropdown d-none d-lg-block">
                        <a class="nav-link-modern user-dropdown-toggle d-flex align-items-center dropdown-toggle" 
                           href="#" 
                           id="userDropdown" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           data-bs-auto-close="true"
                           aria-expanded="false">
                            <div class="user-avatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="user-name"><?= htmlspecialchars($_SESSION['user_nombre']) ?></span>
                            <i class="bi bi-chevron-down ms-2 dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-modern dropdown-menu-end" aria-labelledby="userDropdown">
                            <?php if (isAdmin()): ?>
                                <li>
                                    <a class="dropdown-item-modern" href="admin/index.php">
                                        <i class="bi bi-speedometer2"></i>
                                        <span>Panel Admin</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider-modern"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item-modern" href="favoritos.php">
                                    <i class="bi bi-heart"></i>
                                    <span>Mis Favoritos</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider-modern"></li>
                            <li>
                                <a class="dropdown-item-modern dropdown-item-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Cerrar Sesión</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Botones de login/registro desktop - dentro del menú -->
                    <li class="nav-item-modern d-none d-lg-block">
                        <a class="btn-nav btn-nav-outline" href="login.php">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item-modern d-none d-lg-block">
                        <a class="btn-nav btn-nav-primary" href="registro.php">
                            <i class="bi bi-person-plus me-2"></i>
                            Registrarse
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Navbar Moderno */
.navbar-modern {
    padding: 1rem 0;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.navbar-modern.sticky-top {
    position: sticky;
    top: 0;
    z-index: 1030;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

/* Brand/Logo */
.navbar-brand-modern {
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 700;
    font-size: 1.5rem;
    color: #1e293b;
    transition: all 0.3s ease;
}

.brand-icon-wrapper {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.brand-text {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-family: 'Space Grotesk', sans-serif;
    letter-spacing: -0.02em;
}

.navbar-brand-modern:hover {
    transform: translateY(-1px);
}

.navbar-brand-modern:hover .brand-icon-wrapper {
    transform: rotate(-5deg) scale(1.05);
    box-shadow: 0 6px 12px -2px rgba(59, 130, 246, 0.4);
}

/* Nav Links */
.navbar-nav-modern {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.navbar-nav-modern .dropdown {
    position: relative;
}

.nav-item-modern {
    position: relative;
}

.nav-link-modern {
    display: flex;
    align-items: center;
    padding: 0.625rem 1rem;
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    position: relative;
}

.nav-link-modern i {
    font-size: 1.1rem;
    transition: transform 0.2s ease;
}

.nav-link-modern:hover {
    color: #3b82f6;
    background-color: #f1f5f9;
}

.nav-link-modern:hover i {
    transform: scale(1.1);
}

.nav-link-modern.active {
    color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    font-weight: 600;
}

.nav-link-modern.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 2px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 2px;
}

/* User Dropdown */
.user-dropdown-toggle {
    gap: 0.75rem !important;
    padding: 0.5rem 1rem !important;
}

.user-dropdown-toggle::after {
    display: none;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
}

.user-name {
    font-weight: 600;
    color: #1e293b;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-arrow {
    font-size: 0.75rem;
    color: #94a3b8;
    transition: transform 0.2s ease;
}

.user-dropdown-toggle[aria-expanded="true"] .dropdown-arrow {
    transform: rotate(180deg);
}

/* Dropdown Menu */
.dropdown-menu-modern {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    margin-top: 0.75rem;
    padding: 0.5rem;
    min-width: 220px;
    background: white;
    backdrop-filter: blur(10px);
    z-index: 1050;
}

.dropdown-menu-modern.show {
    display: block;
}

.dropdown-item-modern {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #334155;
    text-decoration: none;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 0.9rem;
}

.dropdown-item-modern i {
    width: 20px;
    text-align: center;
    color: #64748b;
    font-size: 1.1rem;
}

.dropdown-item-modern:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    color: #1e293b;
    transform: translateX(4px);
}

.dropdown-item-modern:hover i {
    color: #3b82f6;
}

.dropdown-item-danger {
    color: #dc2626 !important;
}

.dropdown-item-danger:hover {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%) !important;
    color: #991b1b !important;
}

.dropdown-item-danger:hover i {
    color: #dc2626 !important;
}

.dropdown-divider-modern {
    margin: 0.5rem 0;
    border-color: #e2e8f0;
    opacity: 0.5;
}

/* Buttons */
.btn-nav {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    font-weight: 600;
    font-size: 0.9rem;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.btn-nav-outline {
    color: #3b82f6;
    border-color: #3b82f6;
    background: transparent;
}

.btn-nav-outline:hover {
    background: #eff6ff;
    color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
}

.btn-nav-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
}

.btn-nav-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px -2px rgba(59, 130, 246, 0.4);
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

/* Mobile Toggle */
.navbar-toggler-modern {
    border: none;
    padding: 0.5rem;
    background: transparent;
    display: flex;
    flex-direction: column;
    gap: 4px;
    cursor: pointer;
}

.navbar-toggler-modern span {
    width: 24px;
    height: 2px;
    background: #1e293b;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.navbar-toggler-modern[aria-expanded="true"] span:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
}

.navbar-toggler-modern[aria-expanded="true"] span:nth-child(2) {
    opacity: 0;
}

.navbar-toggler-modern[aria-expanded="true"] span:nth-child(3) {
    transform: rotate(-45deg) translate(6px, -6px);
}

/* User Dropdown Mobile - Solo visible en móviles */
.user-dropdown-toggle-mobile {
    padding: 0.5rem !important;
    gap: 0 !important;
}

.user-avatar-mobile {
    width: 32px;
    height: 32px;
    font-size: 1rem;
}

/* Botones móviles compactos */
.btn-nav-mobile {
    padding: 0.5rem !important;
    min-width: 40px;
    width: auto;
    justify-content: center;
}

.btn-nav-mobile i {
    margin: 0 !important;
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 991px) {
    .navbar-nav-modern {
        flex-direction: column;
        align-items: stretch;
        gap: 0.25rem;
        margin-top: 1rem;
    }
    
    .nav-link-modern {
        padding: 0.75rem 1rem;
        width: 100%;
    }
    
    .user-dropdown-toggle {
        width: 100%;
        justify-content: flex-start;
    }
    
    .btn-nav {
        width: 100%;
        justify-content: center;
    }
    
    .dropdown-menu-modern {
        width: 100%;
        margin-top: 0.5rem;
    }
}

@media (max-width: 576px) {
    .brand-text {
        font-size: 1.25rem;
    }
    
    .brand-icon-wrapper {
        width: 36px;
        height: 36px;
        font-size: 1.25rem;
    }
    
    .user-name {
        max-width: 120px;
    }
}
</style>


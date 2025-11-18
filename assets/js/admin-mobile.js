// Script para manejar el sidebar móvil en el admin
document.addEventListener('DOMContentLoaded', function() {
    // Crear botón toggle para móviles
    if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.admin-sidebar');
        const mainContent = document.querySelector('.col-md-10, .admin-content');
        
        if (sidebar && !document.querySelector('.sidebar-toggle')) {
            // Crear botón toggle
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'btn btn-primary sidebar-toggle position-fixed';
            toggleBtn.style.cssText = 'top: 80px; left: 10px; z-index: 999; padding: 0.5rem;';
            toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
            toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
            document.body.appendChild(toggleBtn);
            
            // Crear overlay
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.style.cssText = 'display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;';
            document.body.appendChild(overlay);
            
            // Toggle sidebar
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
            });
            
            // Cerrar al hacer click en overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.style.display = 'none';
            });
            
            // Cerrar al hacer click fuera
            document.addEventListener('click', function(e) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    overlay.style.display = 'none';
                }
            });
        }
    }
    
    // Ajustar en resize
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        
        if (window.innerWidth > 768) {
            if (sidebar) sidebar.classList.remove('show');
            if (overlay) overlay.style.display = 'none';
            if (toggleBtn) toggleBtn.style.display = 'none';
        } else {
            if (toggleBtn) toggleBtn.style.display = 'block';
        }
    });
});


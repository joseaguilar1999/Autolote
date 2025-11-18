    <!-- Footer -->
    <?php if (!isset($hide_footer) || !$hide_footer): ?>
    <footer class="mt-5 py-4" style="background-color: #000000; color: #ffffff;">
        <div class="container text-center">
            <p class="mb-0 small" style="color: #ffffff;">Desarrollado por José Manuel Aguilar</p>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Inicializar dropdowns de Bootstrap -->
    <script>
        // Asegurar que los dropdowns funcionen correctamente
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar todos los dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });
    </script>
    
    <?php if (isset($additional_js)): ?>
        <?= $additional_js ?>
    <?php endif; ?>
</body>
</html>

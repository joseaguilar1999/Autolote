<?php
require_once '../config/config.php';
requireAdmin();

echo "<h1>Test de Estilos</h1>";
echo "<p>BASE_URL: " . BASE_URL . "</p>";
echo "<p>CSS Global: <a href='" . BASE_URL . "/assets/css/global.css'>" . BASE_URL . "/assets/css/global.css</a></p>";
echo "<p>CSS Admin: <a href='" . BASE_URL . "/assets/css/admin.css'>" . BASE_URL . "/assets/css/admin.css</a></p>";
echo "<hr>";
echo "<p>Verifica que los enlaces CSS funcionen correctamente.</p>";
?>


<?php
// Head reutilizable con estilos globales
$page_title = $page_title ?? 'Autolote';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Autolote</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    
    <!-- Sistema de Notificaciones -->
    <script src="<?= BASE_URL ?>/assets/js/notifications.js"></script>
    
    <!-- Estilos específicos de página -->
    
    <?php if (isset($additional_css)): ?>
        <?= $additional_css ?>
    <?php endif; ?>
</head>
<body>


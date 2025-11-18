# Estilos Globales - Autolote

## Archivos Creados

### 1. `assets/css/global.css`
Archivo CSS global con:
- Fuentes de Google (Space Grotesk para títulos, Inter para texto)
- Reset básico de CSS
- Estilos globales del body
- Transiciones suaves para elementos interactivos
- Scrollbar personalizado
- Mejoras adicionales para consistencia

### 2. `includes/head.php`
Componente reutilizable para el `<head>` de todas las páginas:
- Meta tags estándar
- Bootstrap CSS
- Bootstrap Icons
- CSS global
- Soporte para CSS adicional por página
- Título dinámico

### 3. `includes/footer.php`
Componente reutilizable para el footer:
- Bootstrap JS
- Soporte para JavaScript adicional por página
- Cierre de body y html

## Uso

### En cualquier página PHP:

```php
<?php
require_once 'config/config.php'; // Siempre primero para tener BASE_URL

// Configurar título de página
$page_title = 'Mi Página';

// Incluir head
include 'includes/head.php';
?>

<!-- Estilos específicos de la página (opcional) -->
<style>
    /* Estilos específicos aquí */
</style>

<!-- Contenido de la página -->
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Tu contenido aquí -->
    
<?php include 'includes/footer.php'; ?>
```

### CSS Adicional por Página

```php
<?php
$page_title = 'Mi Página';
$additional_css = '<link rel="stylesheet" href="mi-estilo.css">';
include 'includes/head.php';
?>
```

### JavaScript Adicional por Página

```php
<?php
$additional_js = '<script src="mi-script.js"></script>';
include 'includes/footer.php';
?>
```

## Fuentes

- **Títulos (h1-h6):** Space Grotesk (400, 500, 600, 700)
- **Texto general:** Inter (300, 400, 500, 600, 700)

## Características

- ✅ Fuentes modernas y legibles
- ✅ Transiciones suaves en todos los elementos interactivos
- ✅ Scrollbar personalizado
- ✅ Consistencia visual en toda la aplicación
- ✅ Fácil mantenimiento (un solo archivo CSS global)

## Páginas Actualizadas

- ✅ `index.php` - Usa head.php y footer.php
- ✅ `detalle.php` - Usa head.php y footer.php
- ✅ `comparador.php` - Usa head.php y footer.php
- ✅ `favoritos.php` - Usa head.php y footer.php

## Próximos Pasos

Para aplicar estos estilos a otras páginas:

1. Agregar `require_once 'config/config.php';` al inicio
2. Definir `$page_title`
3. Reemplazar `<head>` con `include 'includes/head.php';`
4. Reemplazar cierre `</body></html>` con `include 'includes/footer.php';`


# Poblar Base de Datos con Datos de Ejemplo

Este script permite poblar la base de datos con vehículos de ejemplo para desarrollo y pruebas.

## Uso

### Desde la línea de comandos (CLI):

```bash
php seed_vehicles.php
```

### Desde el navegador:

Accede a: `http://localhost/Autolote/seed_vehicles.php`

## Datos que se insertan

El script inserta 6 vehículos de ejemplo:

1. **Toyota Corolla 2020** - Destacado
2. **Honda Civic 2019** - Destacado
3. **Ford Escape 2021** - Destacado
4. **Nissan Sentra 2018**
5. **Chevrolet Equinox 2020**
6. **Mazda CX-5 2021**

Cada vehículo incluye:
- Información completa (marca, modelo, año, precio, kilometraje, color, transmisión, descripción)
- Imágenes de ejemplo (URLs de Unsplash)
- Estado "disponible"
- Algunos marcados como "destacados"

## Notas

- El script verifica si ya existen vehículos en la base de datos
- Si hay vehículos existentes, preguntará si deseas continuar
- Las imágenes se guardan como URLs (no se descargan localmente)
- El script es seguro y usa transacciones para garantizar integridad de datos

## Requisitos

- PHP 7.4 o superior
- Base de datos MySQL configurada
- Tablas creadas (ejecutar `database.sql` primero)

## Personalización

Puedes editar el array `$vehicles_data` en `seed_vehicles.php` para agregar más vehículos o modificar los existentes.


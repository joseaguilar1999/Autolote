<?php
/**
 * Script para poblar la base de datos con vehículos de ejemplo
 * Uso: php seed_vehicles.php
 */

require_once 'config/config.php';

$conn = getDBConnection();

// Verificar si ya hay vehículos
$stmt = $conn->query("SELECT COUNT(*) as total FROM vehiculos");
$count = $stmt->fetch()['total'];

// Si se ejecuta desde CLI, pedir confirmación
$is_cli = php_sapi_name() === 'cli';

if ($count > 0 && $is_cli) {
    echo "La base de datos ya tiene {$count} vehículos. ¿Deseas continuar? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) !== 's' && trim($line) !== 'S') {
        echo "Operación cancelada.\n";
        exit;
    }
    fclose($handle);
} elseif ($count > 0 && !$is_cli) {
    // Si se ejecuta desde navegador y hay vehículos, verificar parámetro
    if (!isset($_GET['force']) || $_GET['force'] !== 'yes') {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Seed Vehicles</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='container mt-5'>
            <div class='alert alert-warning'>
                <h4>La base de datos ya tiene {$count} vehículos.</h4>
                <p>¿Deseas continuar y agregar más vehículos de ejemplo?</p>
                <a href='?force=yes' class='btn btn-primary'>Sí, continuar</a>
                <a href='../index.php' class='btn btn-secondary'>Cancelar</a>
            </div>
        </body>
        </html>
        ");
    }
}

// Datos de vehículos de ejemplo
$vehicles_data = [
    [
        'marca' => 'Toyota',
        'modelo' => 'Corolla',
        'año' => 2020,
        'precio' => 18500,
        'kilometraje' => 45000,
        'color' => 'Gris',
        'transmision' => 'automatica',
        'combustible' => 'Gasolina',
        'descripcion' => 'Excelente estado, un solo dueño, mantenimientos al día. Perfecta combinación de eficiencia y confiabilidad.',
        'images' => [
            'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=800',
            'https://images.unsplash.com/photo-1590362891991-f776e747a588?w=800'
        ],
        'featured' => true,
        'estado' => 'disponible'
    ],
    [
        'marca' => 'Honda',
        'modelo' => 'Civic',
        'año' => 2019,
        'precio' => 16800,
        'kilometraje' => 52000,
        'color' => 'Azul',
        'transmision' => 'manual',
        'combustible' => 'Gasolina',
        'descripcion' => 'Deportivo y eficiente, motor en excelentes condiciones. Ideal para quienes buscan estilo y rendimiento.',
        'images' => [
            'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=800',
            'https://images.unsplash.com/photo-1542362567-b07e54358753?w=800'
        ],
        'featured' => true,
        'estado' => 'disponible'
    ],
    [
        'marca' => 'Ford',
        'modelo' => 'Escape',
        'año' => 2021,
        'precio' => 24500,
        'kilometraje' => 28000,
        'color' => 'Blanco',
        'transmision' => 'automatica',
        'combustible' => 'Híbrido',
        'descripcion' => 'SUV moderna con tecnología híbrida, espaciosa y cómoda. Perfect para familias.',
        'images' => [
            'https://images.unsplash.com/photo-1533106418989-88406c7cc8ca?w=800',
            'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=800'
        ],
        'featured' => true,
        'estado' => 'disponible'
    ],
    [
        'marca' => 'Nissan',
        'modelo' => 'Sentra',
        'año' => 2018,
        'precio' => 14200,
        'kilometraje' => 68000,
        'color' => 'Negro',
        'transmision' => 'automatica',
        'combustible' => 'Gasolina',
        'descripcion' => 'Sedán confiable con amplio espacio interior. Mantenimiento completo y listo para rodar.',
        'images' => [
            'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800'
        ],
        'featured' => false,
        'estado' => 'disponible'
    ],
    [
        'marca' => 'Chevrolet',
        'modelo' => 'Equinox',
        'año' => 2020,
        'precio' => 22000,
        'kilometraje' => 38000,
        'color' => 'Rojo',
        'transmision' => 'automatica',
        'combustible' => 'Gasolina',
        'descripcion' => 'SUV mediana en excelente estado, tecnología avanzada y gran espacio de carga.',
        'images' => [
            'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800'
        ],
        'featured' => false,
        'estado' => 'disponible'
    ],
    [
        'marca' => 'Mazda',
        'modelo' => 'CX-5',
        'año' => 2021,
        'precio' => 26500,
        'kilometraje' => 22000,
        'color' => 'Gris Oscuro',
        'transmision' => 'automatica',
        'combustible' => 'Gasolina',
        'descripcion' => 'Crossover premium con diseño elegante. Sistema de seguridad avanzado y bajo kilometraje.',
        'images' => [
            'https://images.unsplash.com/photo-1551830820-330a71b99659?w=800'
        ],
        'featured' => false,
        'estado' => 'disponible'
    ]
];

// Verificar si existe la columna featured
try {
    $stmt = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'featured'");
    $has_featured = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    $has_featured = false;
}

// Insertar vehículos
$inserted = 0;
$messages = [];
$conn->beginTransaction();

try {
    foreach ($vehicles_data as $vehicle) {
        // Insertar vehículo
        if ($has_featured) {
            $sql = "INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion, estado, featured) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $vehicle['marca'],
                $vehicle['modelo'],
                $vehicle['año'],
                $vehicle['precio'],
                $vehicle['kilometraje'],
                $vehicle['color'],
                $vehicle['transmision'],
                $vehicle['descripcion'],
                $vehicle['estado'],
                $vehicle['featured'] ? 1 : 0
            ]);
        } else {
            $sql = "INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $vehicle['marca'],
                $vehicle['modelo'],
                $vehicle['año'],
                $vehicle['precio'],
                $vehicle['kilometraje'],
                $vehicle['color'],
                $vehicle['transmision'],
                $vehicle['descripcion'],
                $vehicle['estado']
            ]);
        }
        
        $vehiculo_id = $conn->lastInsertId();
        
        // Insertar imágenes
        foreach ($vehicle['images'] as $index => $image_url) {
            // Guardar URL de imagen (en producción podrías descargar y guardar localmente)
            $imagen_path = 'vehiculos/' . basename(parse_url($image_url, PHP_URL_PATH));
            
            // Si no hay extensión, agregar .jpg por defecto
            if (!pathinfo($imagen_path, PATHINFO_EXTENSION)) {
                $imagen_path .= '.jpg';
            }
            
            $sql_img = "INSERT INTO vehiculos_imagenes (vehiculo_id, imagen_path, es_principal) VALUES (?, ?, ?)";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->execute([
                $vehiculo_id,
                $image_url, // Guardamos la URL directamente
                $index === 0 ? 1 : 0 // Primera imagen es principal
            ]);
        }
        
        $inserted++;
        if ($is_cli) {
            echo "✓ Insertado: {$vehicle['marca']} {$vehicle['modelo']} ({$vehicle['año']})\n";
        } else {
            $messages[] = "✓ Insertado: {$vehicle['marca']} {$vehicle['modelo']} ({$vehicle['año']})";
        }
    }
    
    $conn->commit();
    
    if ($is_cli) {
        echo "\n¡Éxito! Se insertaron {$inserted} vehículos con sus imágenes.\n";
    } else {
        // Mostrar página de éxito
        $success_message = "¡Éxito! Se insertaron {$inserted} vehículos con sus imágenes.";
        include 'seed_result.php';
        exit;
    }
    
} catch (Exception $e) {
    $conn->rollBack();
    if ($is_cli) {
        echo "\n❌ Error: " . $e->getMessage() . "\n";
    } else {
        die("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - Seed Vehicles</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='container mt-5'>
            <div class='alert alert-danger'>
                <h4>Error al insertar vehículos</h4>
                <p>" . htmlspecialchars($e->getMessage()) . "</p>
                <a href='../index.php' class='btn btn-primary'>Volver al inicio</a>
            </div>
        </body>
        </html>
        ");
    }
    exit(1);
}


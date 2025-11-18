<?php
/**
 * Script para verificar la base de datos
 */

require_once 'config/config.php';

echo "<h2>Verificación de Base de Datos - Autolote</h2>";

try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✅ <strong>Conexión a MySQL exitosa</strong></p>";
    
    // Verificar tablas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tablas encontradas: " . count($tables) . "</h3>";
    
    $tablas_requeridas = ['usuarios', 'vehiculos', 'vehiculos_imagenes', 'consultas', 'favoritos'];
    $tablas_faltantes = [];
    
    echo "<ul>";
    foreach ($tables as $table) {
        $existe = in_array($table, $tablas_requeridas);
        $icono = $existe ? "✅" : "⚠️";
        echo "<li>$icono <strong>$table</strong></li>";
        if ($existe) {
            // Contar registros
            $stmt = $conn->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            echo "&nbsp;&nbsp;&nbsp;&nbsp;<small>Registros: $count</small>";
        }
    }
    echo "</ul>";
    
    // Verificar tablas requeridas
    foreach ($tablas_requeridas as $tabla) {
        if (!in_array($tabla, $tables)) {
            $tablas_faltantes[] = $tabla;
        }
    }
    
    if (empty($tablas_faltantes)) {
        echo "<p style='color: green;'><strong>✅ Todas las tablas requeridas están presentes</strong></p>";
        
        // Verificar usuario administrador
        $stmt = $conn->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin'");
        $admin_count = $stmt->fetchColumn();
        
        if ($admin_count > 0) {
            echo "<p style='color: green;'>✅ Usuario administrador encontrado</p>";
            $stmt = $conn->query("SELECT email FROM usuarios WHERE tipo = 'admin' LIMIT 1");
            $admin = $stmt->fetch();
            echo "<p><strong>Email admin:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No se encontró usuario administrador</p>";
        }
        
        // Verificar vehículos
        $stmt = $conn->query("SELECT COUNT(*) FROM vehiculos");
        $vehiculos_count = $stmt->fetchColumn();
        echo "<p><strong>Vehículos en la base de datos:</strong> $vehiculos_count</p>";
        
        echo "<hr>";
        echo "<h3>✅ Base de datos lista para usar</h3>";
        echo "<p><a href='index.php' class='btn'>Ir al Sitio Web</a> | <a href='login.php' class='btn'>Iniciar Sesión</a></p>";
        
    } else {
        echo "<p style='color: red;'><strong>❌ Faltan las siguientes tablas:</strong></p>";
        echo "<ul>";
        foreach ($tablas_faltantes as $tabla) {
            echo "<li>$tabla</li>";
        }
        echo "</ul>";
        echo "<p>Por favor, importa el archivo <code>database.sql</code> en phpMyAdmin</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Soluciones:</h3>";
    echo "<ol>";
    echo "<li>Verifica que la base de datos 'autolote' exista</li>";
    echo "<li>Verifica las credenciales en <code>config/database.php</code></li>";
    echo "<li>Verifica que MySQL esté corriendo</li>";
    echo "</ol>";
}
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
.btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
.btn:hover { background: #0056b3; }
code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
</style>


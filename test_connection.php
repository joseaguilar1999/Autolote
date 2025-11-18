<?php
/**
 * Script de prueba de conexión a MySQL
 * Accede a: http://localhost/Autolote/test_connection.php
 */

echo "<h2>Prueba de Conexión a MySQL</h2>";

// Configuración
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'autolote';

echo "<p><strong>Intentando conectar a:</strong><br>";
echo "Host: $host<br>";
echo "Usuario: $user<br>";
echo "Base de datos: $db</p>";

// Intentar conexión sin base de datos primero
try {
    $conn = new PDO("mysql:host=$host", $user, $pass);
    echo "<p style='color: green;'>✅ <strong>Conexión a MySQL exitosa</strong></p>";
    
    // Verificar si la base de datos existe
    $stmt = $conn->query("SHOW DATABASES LIKE 'autolote'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Base de datos 'autolote' existe</p>";
        
        // Intentar conectar a la base de datos
        try {
            $conn_db = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            echo "<p style='color: green;'>✅ Conexión a la base de datos 'autolote' exitosa</p>";
            
            // Contar tablas
            $stmt = $conn_db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p><strong>Tablas encontradas:</strong> " . count($tables) . "</p>";
            if (count($tables) > 0) {
                echo "<ul>";
                foreach ($tables as $table) {
                    echo "<li>$table</li>";
                }
                echo "</ul>";
            }
        } catch(PDOException $e) {
            echo "<p style='color: orange;'>⚠️ Base de datos 'autolote' no existe. Necesitas importar database.sql</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Base de datos 'autolote' no existe. Necesitas crearla e importar database.sql</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Soluciones:</h3>";
    echo "<ol>";
    echo "<li><strong>Si usas XAMPP:</strong> Abre XAMPP Control Panel e inicia MySQL</li>";
    echo "<li><strong>Si usas WAMP:</strong> Haz clic derecho en el icono de WAMP → Start MySQL Service</li>";
    echo "<li><strong>Verifica el puerto:</strong> MySQL debe estar corriendo en el puerto 3306</li>";
    echo "<li><strong>Verifica las credenciales:</strong> Usuario 'root' sin contraseña (por defecto en XAMPP)</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='index.php'>Volver al inicio</a> | <a href='admin/index.php'>Panel Admin</a></p>";
?>


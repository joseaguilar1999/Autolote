<?php
/**
 * Script para importar la base de datos en Railway
 * 
 * USO:
 * 1. Configura las variables de entorno MYSQL_URL en Railway o aquí abajo
 * 2. Ejecuta: php importar_railway.php
 * O accede desde el navegador si está en Render
 */

// Cargar configuración de base de datos
require_once 'config/database.php';

echo "<h2>Importando Base de Datos en Railway</h2>\n";
echo "<pre>\n";

try {
    // Conectar a la base de datos
    $conn = getDBConnection();
    echo "✅ Conexión exitosa a la base de datos\n\n";
    
    // Leer el archivo SQL
    $sql_file = 'database_railway.sql';
    if (!file_exists($sql_file)) {
        die("❌ Error: No se encontró el archivo $sql_file\n");
    }
    
    $sql = file_get_contents($sql_file);
    
    // Dividir en sentencias individuales
    // Remover comentarios y líneas vacías
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Dividir por punto y coma, pero mantener las sentencias completas
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && strlen(trim($stmt)) > 0;
        }
    );
    
    echo "📝 Encontradas " . count($statements) . " sentencias SQL\n\n";
    
    $success = 0;
    $errors = 0;
    
    // Ejecutar cada sentencia
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        // Saltar USE railway; ya que ya estamos conectados
        if (preg_match('/^\s*USE\s+railway\s*;?\s*$/i', $statement)) {
            echo "⏭️  Saltando: USE railway\n";
            continue;
        }
        
        try {
            $conn->exec($statement);
            $success++;
            
            // Mostrar qué se está ejecutando (primeras palabras)
            $preview = substr($statement, 0, 50);
            echo "✅ [" . ($index + 1) . "] Ejecutado: " . $preview . "...\n";
        } catch (PDOException $e) {
            $errors++;
            $preview = substr($statement, 0, 50);
            echo "❌ [" . ($index + 1) . "] Error en: " . $preview . "...\n";
            echo "   Mensaje: " . $e->getMessage() . "\n";
            
            // Continuar con las siguientes sentencias aunque haya errores
            // (algunos errores pueden ser esperados, como tablas que ya existen)
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════\n";
    echo "✅ Sentencias exitosas: $success\n";
    echo "❌ Errores: $errors\n";
    echo "═══════════════════════════════════════\n\n";
    
    // Verificar que las tablas se crearon
    echo "🔍 Verificando tablas creadas...\n";
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ Tablas encontradas:\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
        
        // Verificar datos
        echo "\n📊 Verificando datos...\n";
        $usuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $vehiculos = $conn->query("SELECT COUNT(*) FROM vehiculos")->fetchColumn();
        
        echo "   - Usuarios: $usuarios\n";
        echo "   - Vehículos: $vehiculos\n";
        
        if ($usuarios > 0 && $vehiculos > 0) {
            echo "\n🎉 ¡Importación completada exitosamente!\n";
            echo "   Puedes acceder a tu aplicación ahora.\n";
        }
    } else {
        echo "⚠️  No se encontraron tablas. Revisa los errores arriba.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Verifica que:\n";
    echo "1. La variable MYSQL_URL esté configurada correctamente\n";
    echo "2. La base de datos Railway esté accesible\n";
    echo "3. Las credenciales sean correctas\n";
}

echo "</pre>\n";
?>


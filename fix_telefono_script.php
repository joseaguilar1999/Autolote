<?php
/**
 * Script para actualizar el campo telefono en la base de datos existente
 * Ejecuta este script desde: https://autolote.onrender.com/fix_telefono_script.php
 */

require_once 'config/database.php';

echo "<h2>Actualizando Campo Teléfono</h2>\n";
echo "<pre>\n";

try {
    $conn = getDBConnection();
    echo "✅ Conexión exitosa a la base de datos\n\n";
    
    // Actualizar campo telefono en tabla usuarios
    echo "📝 Actualizando campo telefono en tabla usuarios...\n";
    $conn->exec("ALTER TABLE usuarios MODIFY COLUMN telefono VARCHAR(50)");
    echo "✅ Campo telefono actualizado en tabla usuarios\n\n";
    
    // Actualizar campo telefono en tabla consultas (si existe)
    echo "📝 Actualizando campo telefono en tabla consultas...\n";
    $conn->exec("ALTER TABLE consultas MODIFY COLUMN telefono VARCHAR(50)");
    echo "✅ Campo telefono actualizado en tabla consultas\n\n";
    
    echo "🎉 ¡Actualización completada exitosamente!\n";
    echo "\nAhora puedes registrar usuarios sin problemas.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nSi el error dice que la columna ya existe o no se puede modificar,\n";
    echo "es posible que ya esté actualizada. Verifica manualmente.\n";
}

echo "</pre>\n";
?>


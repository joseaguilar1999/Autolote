<?php
/**
 * Script para actualizar la contraseña del administrador
 * Ejecuta este script desde: https://autolote.onrender.com/actualizar_password_admin.php
 * 
 * ⚠️ IMPORTANTE: Elimina este archivo después de usarlo por seguridad
 */

require_once 'config/database.php';

$nueva_password = 'JoseM=20';
$email_admin = 'admin@autolote.com';

echo "<h2>Actualizando Contraseña del Administrador</h2>\n";
echo "<pre>\n";

try {
    $conn = getDBConnection();
    echo "✅ Conexión exitosa a la base de datos\n\n";
    
    // Hashear la nueva contraseña
    $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
    echo "🔐 Contraseña hasheada correctamente\n\n";
    
    // Buscar el usuario administrador
    echo "🔍 Buscando usuario administrador...\n";
    $stmt = $conn->prepare("SELECT id, nombre, email FROM usuarios WHERE email = ? AND tipo = 'admin'");
    $stmt->execute([$email_admin]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ Usuario encontrado:\n";
        echo "   - ID: " . $admin['id'] . "\n";
        echo "   - Nombre: " . $admin['nombre'] . "\n";
        echo "   - Email: " . $admin['email'] . "\n\n";
        
        // Actualizar la contraseña
        echo "📝 Actualizando contraseña...\n";
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = ? AND tipo = 'admin'");
        $stmt->execute([$hashed_password, $email_admin]);
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Contraseña actualizada exitosamente\n\n";
            echo "═══════════════════════════════════════\n";
            echo "📋 Credenciales de acceso:\n";
            echo "   Email: $email_admin\n";
            echo "   Contraseña: $nueva_password\n";
            echo "═══════════════════════════════════════\n\n";
            echo "🎉 ¡Actualización completada!\n";
            echo "   Ahora puedes iniciar sesión con las nuevas credenciales.\n";
            echo "\n⚠️ RECUERDA: Elimina este archivo por seguridad después de usarlo.\n";
        } else {
            echo "⚠️ No se pudo actualizar la contraseña. Verifica que el usuario exista.\n";
        }
    } else {
        echo "❌ No se encontró el usuario administrador con email: $email_admin\n";
        echo "\nUsuarios administradores encontrados:\n";
        $stmt = $conn->query("SELECT id, nombre, email FROM usuarios WHERE tipo = 'admin'");
        $admins = $stmt->fetchAll();
        if (count($admins) > 0) {
            foreach ($admins as $admin) {
                echo "   - " . $admin['email'] . " (" . $admin['nombre'] . ")\n";
            }
        } else {
            echo "   No hay administradores en la base de datos.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
?>


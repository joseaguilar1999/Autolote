# Solución: Error de Conexión a MySQL en phpMyAdmin

## Problema
phpMyAdmin muestra el error: "No se puede establecer una conexión ya que el equipo de destino denegó expresamente dicha conexión"

## Solución

### Si usas XAMPP:

1. **Abrir el Panel de Control de XAMPP**
   - Busca "XAMPP Control Panel" en el menú de inicio
   - O navega a la carpeta de instalación de XAMPP

2. **Iniciar MySQL**
   - En el panel de control, busca la fila de "MySQL"
   - Haz clic en el botón "Start" (Iniciar)
   - Espera a que el indicador se ponga verde

3. **Verificar que MySQL está corriendo**
   - El estado debe mostrar "Running" (En ejecución)
   - El puerto debe ser 3306

4. **Acceder a phpMyAdmin**
   - Abre tu navegador
   - Ve a: http://localhost/phpmyadmin
   - Deberías poder acceder sin problemas

### Si usas WAMP:

1. **Abrir WAMP Server**
   - Haz clic en el icono de WAMP en la bandeja del sistema
   - O busca "WAMP" en el menú de inicio

2. **Iniciar MySQL**
   - Haz clic derecho en el icono de WAMP
   - Ve a "Tools" → "Start MySQL Service"
   - O simplemente haz clic en "Start All Services"

3. **Verificar el estado**
   - El icono de WAMP debe estar verde
   - Si está amarillo o rojo, hay un problema

### Si usas MAMP:

1. **Abrir MAMP**
   - Abre la aplicación MAMP

2. **Iniciar servidores**
   - Haz clic en "Start Servers"
   - Espera a que Apache y MySQL estén corriendo

3. **Acceder a phpMyAdmin**
   - Ve a: http://localhost:8888/phpMyAdmin

### Si MySQL no inicia (Problemas comunes):

#### Puerto 3306 ya en uso:
```powershell
# Verificar qué está usando el puerto 3306
netstat -ano | findstr :3306

# Detener el proceso que está usando el puerto (reemplaza PID con el número)
taskkill /PID [PID] /F
```

#### Cambiar puerto en XAMPP:
1. Abre `C:\xampp\mysql\bin\my.ini`
2. Busca `port=3306`
3. Cámbialo a otro puerto (ej: 3307)
4. También cambia en `C:\xampp\phpMyAdmin\config.inc.php`:
   ```php
   $cfg['Servers'][1]['port'] = '3307';
   ```

#### Verificar configuración de phpMyAdmin:
1. Abre: `C:\xampp\phpMyAdmin\config.inc.php`
2. Verifica estas líneas:
   ```php
   $cfg['Servers'][1]['host'] = '127.0.0.1';
   $cfg['Servers'][1]['port'] = '3306';
   $cfg['Servers'][1]['user'] = 'root';
   $cfg['Servers'][1]['password'] = '';
   ```

### Verificar que MySQL está corriendo:

Abre PowerShell y ejecuta:
```powershell
# Verificar si MySQL está escuchando en el puerto 3306
netstat -ano | findstr :3306
```

Deberías ver algo como:
```
TCP    0.0.0.0:3306           0.0.0.0:0              LISTENING       1234
```

### Crear la base de datos manualmente (Alternativa):

Si phpMyAdmin sigue sin funcionar, puedes crear la base de datos desde la línea de comandos:

1. **Abrir MySQL desde la línea de comandos:**
   ```powershell
   # Si usas XAMPP:
   cd C:\xampp\mysql\bin
   mysql.exe -u root
   ```

2. **Crear la base de datos:**
   ```sql
   CREATE DATABASE autolote CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE autolote;
   ```

3. **Importar el archivo SQL:**
   ```powershell
   mysql.exe -u root autolote < C:\Users\JOSE POLANCO\Desktop\Proyectos\Autolote\database.sql
   ```

### Verificar la conexión desde PHP:

Crea un archivo `test_connection.php` en la raíz del proyecto:

```php
<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'autolote';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "¡Conexión exitosa!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

Accede a: http://localhost/Autolote/test_connection.php

## Pasos siguientes después de resolver:

1. ✅ MySQL corriendo
2. ✅ Acceder a phpMyAdmin
3. ✅ Crear base de datos `autolote` o importar `database.sql`
4. ✅ Verificar configuración en `config/database.php`
5. ✅ Probar el sitio web

## Contacto

Si el problema persiste, verifica:
- Los logs de MySQL en `C:\xampp\mysql\data\mysql_error.log`
- El firewall de Windows no está bloqueando MySQL
- Antivirus no está bloqueando MySQL


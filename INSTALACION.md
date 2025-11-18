# Guía de Instalación - Autolote

## Pasos para Instalar el Sistema

### 1. Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior (o MariaDB)
- Servidor web (XAMPP, WAMP, MAMP, o servidor de producción)
- Navegador web moderno

### 2. Instalación Local (XAMPP/WAMP)

#### Paso 1: Copiar archivos
1. Copia toda la carpeta `Autolote` a tu directorio de servidor web:
   - **XAMPP:** `C:\xampp\htdocs\Autolote`
   - **WAMP:** `C:\wamp64\www\Autolote`
   - **MAMP:** `/Applications/MAMP/htdocs/Autolote`

#### Paso 2: Crear la base de datos
1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Crea una nueva base de datos llamada `autolote`
3. Selecciona la base de datos `autolote`
4. Ve a la pestaña "Importar"
5. Selecciona el archivo `database.sql` del proyecto
6. Haz clic en "Continuar"

**O ejecuta manualmente:**
```sql
-- Abre el archivo database.sql y ejecuta todo su contenido en phpMyAdmin
```

#### Paso 3: Configurar la conexión
Edita el archivo `config/database.php` y ajusta según tu configuración:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Usuario de MySQL
define('DB_PASS', '');            // Contraseña de MySQL (vacía por defecto en XAMPP)
define('DB_NAME', 'autolote');
```

#### Paso 4: Configurar la URL base
Edita el archivo `config/config.php`:

```php
define('BASE_URL', 'http://localhost/Autolote');
```

**Nota:** Si instalas en una subcarpeta, ajusta la ruta:
```php
define('BASE_URL', 'http://localhost/mi-carpeta/Autolote');
```

#### Paso 5: Permisos de escritura
Asegúrate de que el directorio `uploads/vehiculos/` tenga permisos de escritura:
- En Windows: Generalmente funciona automáticamente
- En Linux/Mac: `chmod 777 uploads/vehiculos/`

### 3. Acceder al Sistema

1. **Frontend público:**
   - Abre: http://localhost/Autolote
   - Verás el catálogo de vehículos

2. **Panel administrativo:**
   - Abre: http://localhost/Autolote/login.php
   - **Usuario:** admin@autolote.com
   - **Contraseña:** admin123

### 4. Primera Configuración

#### Cambiar contraseña del administrador
1. Inicia sesión como administrador
2. Ve a "Usuarios" en el panel admin
3. Edita tu usuario (aunque no hay formulario de edición, puedes hacerlo directamente en la base de datos)

**O ejecuta este SQL:**
```sql
UPDATE usuarios 
SET password = '$2y$10$TU_NUEVO_HASH_AQUI' 
WHERE email = 'admin@autolote.com';
```

Para generar un nuevo hash de contraseña, usa este código PHP:
```php
<?php
echo password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT);
?>
```

### 5. Agregar Vehículos

1. Inicia sesión como administrador
2. Ve a "Vehículos" → "Nuevo Vehículo"
3. Completa el formulario
4. Sube imágenes (puedes seleccionar múltiples)
5. Guarda

### 6. Probar Funcionalidades

#### Catálogo público:
- Buscar vehículos
- Filtrar por marca, precio, año, transmisión
- Ver detalles de cada vehículo

#### Comparador:
- Agrega vehículos al comparador desde el catálogo o detalle
- Ve a "Comparar Vehículos" para ver la comparación

#### Favoritos:
- Regístrate como usuario
- Inicia sesión
- Agrega vehículos a favoritos
- Ve a "Favoritos" para ver tu lista

#### Consultas:
- Envía consultas desde la página de detalle de un vehículo
- Como administrador, gestiona las consultas en "Consultas"

## Solución de Problemas

### Error de conexión a la base de datos
- Verifica que MySQL esté corriendo
- Revisa las credenciales en `config/database.php`
- Asegúrate de que la base de datos `autolote` exista

### Las imágenes no se muestran
- Verifica que el directorio `uploads/vehiculos/` exista y tenga permisos de escritura
- Revisa la configuración de `BASE_URL` en `config/config.php`
- Verifica que las rutas de las imágenes sean correctas

### Error 404 en las páginas
- Verifica que el módulo `mod_rewrite` esté habilitado en Apache
- O ajusta las rutas directamente en los archivos

### Problemas con sesiones
- Verifica que las sesiones de PHP estén habilitadas
- Revisa los permisos del directorio de sesiones de PHP

## Estructura de Archivos Importantes

```
Autolote/
├── config/
│   ├── config.php      ← Configuración general y URL base
│   └── database.php    ← Credenciales de base de datos
├── admin/              ← Panel administrativo (requiere login admin)
├── api/                ← APIs para AJAX
├── uploads/vehiculos/  ← Imágenes de vehículos (se crea automáticamente)
└── database.sql        ← Estructura de base de datos
```

## Soporte

Si encuentras problemas:
1. Revisa los logs de PHP y MySQL
2. Verifica que todas las extensiones PHP necesarias estén habilitadas
3. Asegúrate de que la versión de PHP sea compatible (7.4+)


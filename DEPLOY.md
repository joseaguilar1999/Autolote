# Guía de Despliegue - Autolote

## Opciones de Hosting Gratuito Recomendadas

### 1. InfinityFree (Recomendado) ⭐
- **URL**: https://www.infinityfree.net
- **Ventajas**:
  - Sin publicidad
  - MySQL ilimitado
  - PHP 8.x
  - SSL gratuito
  - Panel cPanel
  - 5 GB de almacenamiento
- **Pasos**:
  1. Crear cuenta en infinityfree.net
  2. Crear un nuevo sitio web
  3. Subir archivos vía FTP o File Manager
  4. Crear base de datos MySQL desde el panel
  5. Actualizar `config/database.php` con las credenciales

### 2. 000webhost
- **URL**: https://www.000webhost.com
- **Ventajas**:
  - Sin publicidad
  - MySQL incluido
  - SSL gratuito
- **Limitaciones**: 300 MB de almacenamiento

### 3. Render (Deploy desde GitHub)
- **URL**: https://render.com
- **Ventajas**:
  - Deploy automático desde GitHub
  - SSL automático
  - Moderno y rápido
- **Base de Datos en Render**:
  - ⚠️ **PostgreSQL**: Plan gratuito por 90 días, luego requiere pago (~$7/mes)
  - ⚠️ **MySQL**: No hay servicio gestionado gratuito. Se puede desplegar con Render Disks pero requiere pago
  - ✅ **Alternativa Recomendada**: Usar base de datos externa gratuita:
    - **Neon** (PostgreSQL) - Gratis permanente
    - **Railway** (MySQL) - $5 crédito/mes gratis
    - **Supabase** (PostgreSQL) - Plan gratuito generoso
- **Pasos**:
  1. Conectar repositorio de GitHub
  2. Seleccionar PHP como entorno
  3. Configurar variables de entorno
  4. Agregar servicio de base de datos externa (Neon, Railway, etc.)

## Pasos para Desplegar en InfinityFree

### Paso 1: Preparar la Base de Datos
1. Accede al panel de control de InfinityFree
2. Ve a "MySQL Databases"
3. Crea una nueva base de datos
4. Anota las credenciales:
   - Host (generalmente `sqlXXX.infinityfree.com`)
   - Usuario
   - Contraseña
   - Nombre de la base de datos

### Paso 2: Subir Archivos
**Opción A: File Manager (Recomendado para principiantes)**
1. Accede al File Manager desde el panel
2. Sube todos los archivos del proyecto a `htdocs` o `public_html`
3. Asegúrate de mantener la estructura de carpetas

**Opción B: FTP**
1. Descarga FileZilla (gratis)
2. Usa las credenciales FTP del panel:
   - Host: `ftpupload.net` o similar
   - Usuario y contraseña (del panel)
3. Conecta y sube todos los archivos

### Paso 3: Configurar Base de Datos
1. Edita `config/database.php` con las credenciales de InfinityFree:
```php
define('DB_HOST', 'sqlXXX.infinityfree.com'); // Tu host de InfinityFree
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'tu_base_de_datos');
```

### Paso 4: Importar Base de Datos
1. Accede a phpMyAdmin desde el panel
2. Selecciona tu base de datos
3. Ve a la pestaña "Importar"
4. Sube tu archivo `.sql` (si tienes uno)
5. O ejecuta los scripts SQL manualmente

### Paso 5: Verificar Permisos
- Asegúrate de que la carpeta `uploads/` tenga permisos de escritura (755 o 777)

### Paso 6: Activar SSL
1. En el panel de InfinityFree, ve a "SSL"
2. Activa el certificado SSL gratuito
3. Esto habilitará HTTPS automáticamente

## Configuración de Producción

### Archivos a Verificar:
- ✅ `config/database.php` - Credenciales de base de datos
- ✅ `.htaccess` - Configuración de Apache (ya está configurado)
- ✅ Permisos de carpetas `uploads/` y `admin/uploads/`

### Seguridad en Producción:
- ✅ Los archivos sensibles ya están protegidos por `.htaccess`
- ✅ Las páginas admin requieren autenticación
- ✅ Las contraseñas están hasheadas
- ✅ Se usan consultas preparadas (PDO)

## Solución de Problemas

### Error de Conexión a Base de Datos
- Verifica las credenciales en `config/database.php`
- Asegúrate de que el host sea correcto (no uses `localhost`)

### Error 500
- Verifica los permisos de archivos
- Revisa los logs de error en el panel
- Asegúrate de que PHP esté en versión 7.4 o superior

### Imágenes no se Suben
- Verifica permisos de la carpeta `uploads/` (755 o 777)
- Verifica que la carpeta exista

### Páginas en Blanco
- Activa el display de errores temporalmente para debug
- Verifica los logs de PHP en el panel

## Opciones de Base de Datos Gratuita para Render

Si usas Render para PHP pero necesitas una base de datos gratuita, puedes usar estas alternativas:

### 1. **Neon** (PostgreSQL) ⭐ RECOMENDADO
- **URL**: https://neon.tech
- PostgreSQL serverless
- **Plan gratuito**: 0.5 GB almacenamiento, 1 proyecto
- Perfecto para desarrollo y proyectos pequeños
- Conexión externa permitida
- ⚠️ **Nota**: Necesitarás migrar de MySQL a PostgreSQL

### 2. **Supabase** (PostgreSQL)
- **URL**: https://supabase.com
- PostgreSQL gestionado con extras (auth, storage, etc.)
- **Plan gratuito**: 500 MB base de datos, 1 GB archivos
- Muy completo pero requiere migración de MySQL a PostgreSQL

### 3. **Railway** (MySQL y PostgreSQL)
- **URL**: https://railway.app
- MySQL y PostgreSQL disponibles
- **$5 crédito gratis/mes** (suficiente para bases pequeñas)
- Muy fácil de configurar
- ⚠️ **Nota**: No es completamente gratis, pero el crédito mensual suele ser suficiente

### 4. **Aiven for MySQL**
- **URL**: https://aiven.io
- MySQL gestionado
- Plan gratuito con créditos mensuales limitados
- Buena opción para pruebas

### 5. **PlanetScale** ⚠️ YA NO ES GRATIS
- **URL**: https://planetscale.com
- ⚠️ **Plan gratuito descontinuado en marzo 2024**
- Ahora requiere plan de pago desde $5/mes
- Solo mencionado como referencia histórica

## Pasos para Desplegar en Render con Base de Datos Externa

### Opción A: Render + Neon (PostgreSQL) ⭐ RECOMENDADO

1. **Crear base de datos en Neon**:
   - Regístrate en neon.tech
   - Crea un nuevo proyecto
   - Crea una base de datos PostgreSQL
   - Anota las credenciales de conexión (host, usuario, contraseña, nombre)

2. **Migrar de MySQL a PostgreSQL** (si es necesario):
   - Usa herramienta como `pgloader` o `mysqldump` + conversión manual
   - O adapta el código para usar PostgreSQL desde el inicio
   - Cambia PDO de `mysql:` a `pgsql:` en `config/database.php`

3. **Desplegar aplicación en Render**:
   - Conecta tu repositorio de GitHub
   - Selecciona "Web Service" → PHP
   - Configura las variables de entorno:
     ```
     DB_HOST=tu_host_neon
     DB_USER=tu_usuario
     DB_PASS=tu_contraseña
     DB_NAME=tu_base_datos
     ```

### Opción B: Render + Railway (MySQL) - Con crédito gratis

1. **Crear base de datos MySQL en Railway**:
   - Regístrate en railway.app
   - Crea un nuevo proyecto
   - Agrega servicio MySQL
   - Anota las credenciales de conexión

2. **Desplegar aplicación en Render**:
   - Conecta tu repositorio de GitHub
   - Selecciona "Web Service" → PHP
   - Configura las variables de entorno con las credenciales de Railway

### Opción C: Render + PostgreSQL (90 días gratis)

1. **Crear PostgreSQL en Render**:
   - En Render Dashboard → "New +" → "PostgreSQL"
   - Selecciona plan gratuito (90 días)
   - Anota las credenciales

2. **Migrar de MySQL a PostgreSQL** (si es necesario):
   - Usa herramienta de migración o adapta el código
   - Cambia PDO a `pgsql:` en lugar de `mysql:`

## Notas Importantes

⚠️ **IMPORTANTE**: 
- Nunca subas archivos con contraseñas de desarrollo
- Cambia todas las contraseñas por defecto
- El archivo `.htaccess` ya protege archivos sensibles
- Mantén una copia de seguridad de tu base de datos
- **Render PostgreSQL**: Solo 90 días gratis, luego ~$7/mes
- **Neon**: Plan gratuito permanente (0.5 GB) - Requiere PostgreSQL
- **Railway**: $5 crédito gratis/mes (suficiente para proyectos pequeños)
- **PlanetScale**: Ya no ofrece plan gratuito (desde marzo 2024)

## Soporte

Si tienes problemas durante el despliegue:
1. Revisa los logs de error en el panel de hosting
2. Verifica que todos los archivos se subieron correctamente
3. Asegúrate de que la estructura de carpetas sea correcta


# Cómo Importar la Base de Datos en Railway

## ⚠️ Error Actual
```
Table 'railway.vehiculos' doesn't exist
```

Esto significa que la conexión funciona, pero las tablas no existen. Necesitas importar el esquema de la base de datos.

## Opción 1: Usar Railway CLI (Recomendado)

### Paso 1: Instalar Railway CLI
```bash
# En Windows (PowerShell)
iwr https://railway.app/install.ps1 | iex

# O descarga desde: https://railway.app/cli
```

### Paso 2: Iniciar sesión
```bash
railway login
```

### Paso 3: Conectar a tu proyecto
```bash
railway link
# Selecciona tu proyecto
```

### Paso 4: Importar el SQL
```bash
railway connect MySQL < database_railway.sql
```

## Opción 2: Usar MySQL desde tu computadora

### Paso 1: Obtener credenciales de Railway
1. Ve a Railway Dashboard
2. Click en tu servicio MySQL
3. Click en "Connect" → "Public Network"
4. Copia la "Connection URL" completa

### Paso 2: Conectar desde tu computadora
```bash
# Si tienes MySQL instalado localmente
mysql -h trolley.proxy.rlwy.net -u root -p --port 29707 railway < database_railway.sql
```

Cuando te pida la contraseña, usa la contraseña de tu Connection URL.

## Opción 3: Usar phpMyAdmin Online

### Paso 1: Usar un servicio de phpMyAdmin online
1. Ve a: https://www.phpmyadmin.co/ o similar
2. Usa las credenciales de Railway:
   - Host: `trolley.proxy.rlwy.net`
   - Puerto: `29707`
   - Usuario: `root`
   - Contraseña: (la de tu Connection URL)
   - Base de datos: `railway`

### Paso 2: Importar el archivo
1. Selecciona la base de datos `railway`
2. Ve a la pestaña "Importar"
3. Selecciona el archivo `database_railway.sql`
4. Click en "Ejecutar"

## Opción 4: Usar MySQL Workbench o DBeaver

1. Descarga MySQL Workbench: https://dev.mysql.com/downloads/workbench/
2. Crea una nueva conexión:
   - Host: `trolley.proxy.rlwy.net`
   - Puerto: `29707`
   - Usuario: `root`
   - Contraseña: (la de tu Connection URL)
   - Base de datos: `railway`
3. Conecta
4. File → Run SQL Script → Selecciona `database_railway.sql`

## Verificación

Después de importar, verifica que las tablas existan:

```sql
SHOW TABLES;
```

Deberías ver:
- usuarios
- vehiculos
- vehiculos_imagenes
- consultas
- favoritos

## Notas Importantes

- El archivo `database_railway.sql` ya está adaptado para Railway
- No crea la base de datos (Railway ya la tiene)
- Incluye todas las tablas y datos de ejemplo
- Incluye el campo `combustible` y `featured` que necesitas

## Después de Importar

1. Refresca tu aplicación en Render
2. El error debería desaparecer
3. Deberías poder acceder a la aplicación normalmente


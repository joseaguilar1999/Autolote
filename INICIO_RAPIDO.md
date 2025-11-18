# Inicio Rápido del Proyecto Autolote

## Opción 1: Script Automático (Windows)

Ejecuta el archivo `iniciar_servicios.bat` que iniciará automáticamente:
- Servicios de XAMPP/WAMP (Apache y MySQL)
- Abrirá el navegador con la página de verificación

## Opción 2: Manual

### 1. Iniciar Servicios

**XAMPP:**
- Abre el Panel de Control de XAMPP
- Inicia Apache
- Inicia MySQL

**WAMP:**
- Abre WAMP Manager
- Asegúrate de que Apache y MySQL estén en verde (activos)

### 2. Verificar el Sistema

Abre en tu navegador:
```
http://localhost/Autolote/start.php
```

Esta página verificará:
- ✓ Conexión a la base de datos
- ✓ Existencia de tablas
- ✓ Directorios necesarios
- ✓ Archivos importantes
- ✓ Configuración del sistema

### 3. Poblar Base de Datos (Opcional)

Si no hay vehículos en la base de datos, puedes ejecutar:
```
http://localhost/Autolote/seed_vehicles.php
```

Esto agregará 6 vehículos de ejemplo con imágenes.

## Accesos Rápidos

- **Inicio del sitio:** http://localhost/Autolote/
- **Panel Admin:** http://localhost/Autolote/admin/
- **Verificación:** http://localhost/Autolote/start.php
- **Seed Data:** http://localhost/Autolote/seed_vehicles.php

## Credenciales por Defecto

Si ejecutaste `database.sql`, las credenciales por defecto son:
- **Email:** admin@autolote.com
- **Contraseña:** admin123

**⚠️ IMPORTANTE:** Cambia estas credenciales en producción.

## Solución de Problemas

### Apache no inicia
1. Verifica que el puerto 80 no esté en uso
2. Ejecuta XAMPP/WAMP como Administrador
3. Revisa los logs en `C:\xampp\apache\logs\` o `C:\wamp64\logs\`

### MySQL no inicia
1. Verifica que el puerto 3306 no esté en uso
2. Ejecuta XAMPP/WAMP como Administrador
3. Revisa los logs en `C:\xampp\mysql\data\` o `C:\wamp64\logs\`

### Error 404
1. Asegúrate de que el proyecto esté en `C:\xampp\htdocs\Autolote\` o `C:\wamp64\www\Autolote\`
2. Verifica que Apache esté corriendo
3. Revisa la configuración de `BASE_URL` en `config/config.php`

### Error de conexión a BD
1. Verifica que MySQL esté corriendo
2. Revisa las credenciales en `config/database.php`
3. Asegúrate de que la base de datos `autolote_db` exista
4. Ejecuta `database.sql` si no lo has hecho

## Estructura del Proyecto

```
Autolote/
├── admin/              # Panel administrativo
├── api/                # APIs REST
├── assets/             # CSS, JS, imágenes
├── config/             # Configuración
├── includes/           # Componentes reutilizables
├── uploads/            # Archivos subidos
├── index.php           # Página principal
├── login.php           # Login
├── registro.php        # Registro
├── detalle.php         # Detalle de vehículo
├── comparador.php      # Comparador
├── favoritos.php       # Favoritos
├── start.php           # Verificación del sistema
└── seed_vehicles.php   # Poblar BD con datos
```

## Próximos Pasos

1. ✅ Verificar sistema con `start.php`
2. ✅ Poblar base de datos con `seed_vehicles.php`
3. ✅ Iniciar sesión como admin
4. ✅ Explorar el panel administrativo
5. ✅ Agregar más vehículos desde el admin

# Solución: Error 404 - Not Found

## Problema Resuelto ✅

El proyecto ha sido copiado a la ubicación correcta de XAMPP.

### Ubicación del Proyecto:
```
C:\xampp\htdocs\Autolote
```

## Verificar que Apache esté corriendo

1. **Abre XAMPP Control Panel**
2. **Verifica que Apache esté en "Running" (verde)**
3. Si no está corriendo, haz clic en "Start" junto a Apache

## Acceder al Sitio

Una vez que Apache esté corriendo, accede a:

### Frontend (Catálogo):
```
http://localhost/Autolote
```

### Panel Administrativo:
```
http://localhost/Autolote/login.php
```
- Email: `admin@autolote.com`
- Contraseña: `admin123`

### Verificación de Base de Datos:
```
http://localhost/Autolote/verificar_bd.php
```

## Si aún ves el error 404

### Opción 1: Verificar Apache
```powershell
# Verificar si Apache está corriendo
netstat -ano | findstr :80
```

Si no hay resultados, Apache no está corriendo. Inícialo desde XAMPP Control Panel.

### Opción 2: Verificar que los archivos estén en htdocs
```powershell
# Verificar que index.php existe
Test-Path "C:\xampp\htdocs\Autolote\index.php"
```

Debería devolver `True`.

### Opción 3: Reiniciar Apache
1. En XAMPP Control Panel, haz clic en "Stop" en Apache
2. Espera unos segundos
3. Haz clic en "Start" nuevamente
4. Intenta acceder al sitio

### Opción 4: Verificar permisos
Asegúrate de que la carpeta `C:\xampp\htdocs\Autolote` tenga permisos de lectura.

## Estructura Correcta

El proyecto debe estar en:
```
C:\xampp\htdocs\Autolote\
├── index.php
├── login.php
├── admin/
├── config/
├── api/
└── ...
```

## Nota Importante

Si haces cambios en el proyecto, tienes dos opciones:

1. **Trabajar directamente en htdocs:**
   ```
   C:\xampp\htdocs\Autolote
   ```

2. **O mantener el proyecto en Desktop y copiar cambios:**
   ```powershell
   Copy-Item -Path "C:\Users\JOSE POLANCO\Desktop\Proyectos\Autolote\*" -Destination "C:\xampp\htdocs\Autolote\" -Recurse -Force
   ```

## Estado Actual

- ✅ Proyecto copiado a htdocs
- ✅ MySQL corriendo
- ✅ Base de datos configurada
- 🔲 Verificar que Apache esté corriendo
- 🔲 Acceder al sitio web


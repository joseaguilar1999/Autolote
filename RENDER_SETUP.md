# Guía Rápida: Desplegar Autolote en Render

## ⚠️ IMPORTANTE: Render NO tiene soporte nativo para PHP

Render requiere usar **Docker** para desplegar aplicaciones PHP. Ya he creado el `Dockerfile` necesario.

## Configuración en Render

### Configuración Correcta:

1. **Language**: 
   - ✅ Selecciona **"Docker"** (no Node, no PHP)

2. **Build Command**: 
   - ✅ Déjalo VACÍO (Docker se construye automáticamente)

3. **Start Command**: 
   - ✅ Déjalo VACÍO (Docker ejecuta automáticamente Apache)

### Valores Recomendados:

```
Name: Autolote
Language: Docker ⚠️ IMPORTANTE
Branch: main
Region: Oregon (US West) (o el más cercano a ti)
Root Directory: (vacío)
Build Command: (vacío)
Start Command: (vacío - Docker lo maneja automáticamente)
```

## Variables de Entorno Necesarias

### Si usas Railway MySQL (Recomendado):

Agrega esta variable de entorno en Render:

```
MYSQL_URL=mysql://root:tu_contraseña@tu_host_railway:3306/railway
```

**Cómo obtenerla:**
1. Ve a tu proyecto en Railway
2. Click en el servicio MySQL
3. Ve a la pestaña "Variables"
4. Copia el valor de `MYSQL_URL`
5. Pégalo en Render → Environment Variables

### Si usas variables individuales:

```
DB_HOST=tu_host
DB_USER=tu_usuario
DB_PASS=tu_contraseña
DB_NAME=tu_base_datos
```

## Después del Deploy

1. Render te dará una URL como: `https://autolote.onrender.com`
2. Accede a tu aplicación
3. Si hay errores, revisa los logs en Render Dashboard → Logs

## Solución de Problemas

### Error: "Language Node detected"
- **Solución**: Cambia el Language a **"Docker"** en la configuración

### Error: "Dockerfile not found"
- **Solución**: Asegúrate de que el archivo `Dockerfile` esté en la raíz del repositorio
- Verifica que esté subido a GitHub en la rama `main`

### Error de conexión a base de datos
- **Solución**: Verifica que las variables de entorno estén correctamente configuradas
- Asegúrate de que `MYSQL_URL` tenga el formato correcto: `mysql://usuario:contraseña@host:puerto/nombre`
- Verifica que la base de datos permita conexiones externas desde Render

### Error 500
- Revisa los logs en Render Dashboard → Logs
- Verifica que la base de datos esté accesible desde Render
- Asegúrate de que las credenciales sean correctas
- Verifica los permisos de la carpeta `uploads/` en los logs

### Error de permisos en uploads/
- El Dockerfile ya configura los permisos automáticamente
- Si persiste, verifica los logs de Docker

## Notas Importantes

- ✅ El `Dockerfile` ya está creado y configurado
- ✅ Usa PHP 8.2 con Apache
- ✅ Incluye extensiones MySQL necesarias
- ✅ Configura permisos automáticamente
- ✅ El proyecto se despliega directamente desde GitHub
- ✅ Cada push a `main` activará un nuevo deploy automático
- ⚠️ El primer deploy puede tardar varios minutos mientras construye la imagen Docker


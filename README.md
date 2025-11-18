# Autolote - Sistema de Gestión de Vehículos

Sistema web completo desarrollado en PHP para la gestión de un autolote (concesionario de autos usados).

## Características

### Panel Administrativo
- ✅ Gestión CRUD completa de vehículos (crear, editar, eliminar, listar)
- ✅ Soporte para múltiples imágenes por vehículo
- ✅ Gestión de usuarios/clientes
- ✅ Sistema de consultas/contactos con estados (nueva, leída, respondida)
- ✅ Dashboard con estadísticas en tiempo real

### Frontend Público
- ✅ Catálogo de vehículos con búsqueda y filtros avanzados
- ✅ Página de detalle individual de cada vehículo
- ✅ Formulario de contacto/consulta
- ✅ Comparador de vehículos
- ✅ Sistema de favoritos para usuarios registrados

### Autenticación
- ✅ Sistema de login y registro
- ✅ Solo administradores pueden acceder al panel administrativo
- ✅ Usuarios pueden registrarse para guardar favoritos

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, PDO_MySQL, GD (para imágenes)

## Instalación

1. **Clonar o descargar el proyecto** en tu servidor web (por ejemplo, en `htdocs` o `www`)

2. **Crear la base de datos:**
   - Abre phpMyAdmin o tu cliente MySQL preferido
   - Importa el archivo `database.sql` que contiene la estructura de la base de datos y datos de ejemplo

3. **Configurar la conexión a la base de datos:**
   - Edita el archivo `config/database.php`
   - Ajusta las constantes según tu configuración:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'autolote');
     ```

4. **Configurar la URL base:**
   - Edita el archivo `config/config.php`
   - Cambia `BASE_URL` según tu configuración:
     ```php
     define('BASE_URL', 'http://localhost/Autolote');
     ```

5. **Crear directorio de uploads:**
   - El sistema creará automáticamente el directorio `uploads/vehiculos/` si no existe
   - Asegúrate de que tenga permisos de escritura (chmod 777 en Linux/Mac)

6. **Credenciales por defecto:**
   - **Email:** admin@autolote.com
   - **Contraseña:** admin123

## Estructura del Proyecto

```
Autolote/
├── admin/                 # Panel administrativo
│   ├── index.php         # Dashboard
│   ├── vehiculos.php     # Lista de vehículos
│   ├── vehiculo_form.php # Formulario crear/editar vehículo
│   ├── usuarios.php      # Gestión de usuarios
│   └── consultas.php     # Gestión de consultas
├── api/                  # APIs REST
│   ├── favoritos.php     # API de favoritos
│   └── consultas.php     # API de consultas
├── config/               # Configuración
│   ├── config.php        # Configuración general
│   └── database.php      # Conexión a BD
├── uploads/              # Archivos subidos (se crea automáticamente)
│   └── vehiculos/        # Imágenes de vehículos
├── index.php             # Página principal (catálogo)
├── detalle.php           # Detalle de vehículo
├── comparador.php        # Comparador de vehículos
├── favoritos.php         # Favoritos del usuario
├── login.php             # Inicio de sesión
├── registro.php          # Registro de usuarios
├── logout.php            # Cerrar sesión
├── database.sql          # Estructura de base de datos
└── README.md             # Este archivo
```

## Funcionalidades Detalladas

### Gestión de Vehículos
- Campos: marca, modelo, año, precio, kilometraje, color, transmisión, descripción
- Múltiples imágenes por vehículo
- Estados: disponible, reservado, vendido
- Búsqueda y filtros en el catálogo público

### Sistema de Consultas
- Los visitantes pueden enviar consultas sobre vehículos específicos o generales
- Los administradores pueden gestionar las consultas (marcar como leída, respondida, eliminar)

### Comparador de Vehículos
- Los usuarios pueden agregar vehículos al comparador desde cualquier página
- Comparación lado a lado de características

### Favoritos
- Los usuarios registrados pueden guardar vehículos en favoritos
- Acceso rápido desde el menú

## Seguridad

- Contraseñas hasheadas con `password_hash()`
- Protección contra SQL Injection usando PDO con prepared statements
- Validación de sesiones para acceso al panel administrativo
- Sanitización de datos de entrada con `htmlspecialchars()`

## Personalización

### Cambiar el diseño
El proyecto usa Bootstrap 5. Puedes personalizar los estilos editando las clases CSS en los archivos PHP o agregando un archivo CSS personalizado.

### Agregar más campos a los vehículos
1. Modifica la tabla `vehiculos` en la base de datos
2. Actualiza `admin/vehiculo_form.php` para incluir los nuevos campos
3. Actualiza `index.php` y `detalle.php` para mostrar los nuevos campos

## Soporte

Para problemas o preguntas, revisa el código fuente que está bien comentado y estructurado.

## Licencia

Este proyecto es de código abierto y está disponible para uso personal y comercial.


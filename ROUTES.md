# Estructura de Rutas - Autolote

Este documento describe la estructura de rutas de la aplicación Autolote, similar a como está organizada en React Router.

## Rutas Públicas

### `/` o `index.php`
- **Descripción:** Página principal con hero section, características y vehículos destacados
- **Acceso:** Público
- **Componente:** `index.php`
- **Funcionalidades:**
  - Muestra vehículos destacados
  - Sección de características
  - Botón para ver catálogo completo
  - Filtros y búsqueda (cuando se muestra el catálogo)

### `/detalle.php?id={id}`
- **Descripción:** Página de detalle individual de un vehículo
- **Acceso:** Público
- **Componente:** `detalle.php`
- **Funcionalidades:**
  - Galería de imágenes
  - Especificaciones del vehículo
  - Formulario de consulta
  - Botón de favoritos (requiere login)
  - Agregar al comparador

### `/comparador.php`
- **Descripción:** Comparador de vehículos lado a lado
- **Acceso:** Público
- **Componente:** `comparador.php`
- **Funcionalidades:**
  - Comparación de múltiples vehículos
  - Usa localStorage para persistencia
  - Tabla comparativa

### `/login.php`
- **Descripción:** Página de inicio de sesión
- **Acceso:** Público (redirige si ya está logueado)
- **Componente:** `login.php`
- **Funcionalidades:**
  - Login de usuarios y administradores
  - Redirección según tipo de usuario

### `/registro.php`
- **Descripción:** Página de registro de nuevos usuarios
- **Acceso:** Público
- **Componente:** `registro.php`
- **Funcionalidades:**
  - Registro de clientes
  - Validación de email único
  - Confirmación de contraseña

## Rutas Protegidas (Requieren Login)

### `/favoritos.php`
- **Descripción:** Lista de vehículos favoritos del usuario
- **Acceso:** Requiere login (`requireLogin()`)
- **Componente:** `favoritos.php`
- **Protección:** Redirige a `/login.php` si no está autenticado
- **Funcionalidades:**
  - Ver favoritos del usuario
  - Eliminar de favoritos
  - Enlaces a detalles

## Rutas Administrativas (Requieren Admin)

### `/admin/index.php`
- **Descripción:** Dashboard administrativo
- **Acceso:** Requiere admin (`requireAdmin()`)
- **Componente:** `admin/index.php`
- **Protección:** Redirige a `/index.php` si no es admin
- **Funcionalidades:**
  - Estadísticas generales
  - Vehículos recientes
  - Consultas recientes
  - Resumen de ventas

### `/admin/vehiculos.php`
- **Descripción:** Lista de todos los vehículos
- **Acceso:** Requiere admin
- **Componente:** `admin/vehiculos.php`
- **Funcionalidades:**
  - CRUD completo de vehículos
  - Eliminar vehículos
  - Ver número de imágenes

### `/admin/vehiculo_form.php`
- **Descripción:** Formulario para crear/editar vehículos
- **Acceso:** Requiere admin
- **Componente:** `admin/vehiculo_form.php`
- **Parámetros:** `?id={id}` para editar
- **Funcionalidades:**
  - Crear nuevo vehículo
  - Editar vehículo existente
  - Subir múltiples imágenes
  - Marcar como destacado

### `/admin/usuarios.php`
- **Descripción:** Gestión de usuarios/clientes
- **Acceso:** Requiere admin
- **Componente:** `admin/usuarios.php`
- **Funcionalidades:**
  - Ver todos los usuarios
  - Activar/desactivar usuarios
  - Eliminar usuarios
  - Ver tipo de usuario

### `/admin/consultas.php`
- **Descripción:** Gestión de consultas/contactos
- **Acceso:** Requiere admin
- **Componente:** `admin/consultas.php`
- **Funcionalidades:**
  - Ver todas las consultas
  - Cambiar estado (nueva, leída, respondida)
  - Ver mensaje completo
  - Eliminar consultas

## APIs

### `/api/favoritos.php`
- **Método:** POST
- **Acceso:** Requiere login
- **Funcionalidad:** Agregar/eliminar favoritos
- **Body:** `{ "vehiculo_id": number }`

### `/api/consultas.php`
- **Método:** POST
- **Acceso:** Público
- **Funcionalidad:** Enviar consulta sobre vehículo
- **Body:** `FormData` con nombre, email, teléfono, mensaje, vehiculo_id

## Sistema de Autenticación

### Funciones de Protección

```php
// En config/config.php

// Verificar si está logueado
isLoggedIn() // Retorna boolean

// Verificar si es administrador
isAdmin() // Retorna boolean

// Requiere login (redirige si no está logueado)
requireLogin()

// Requiere admin (redirige si no es admin)
requireAdmin()
```

### Variables de Sesión

```php
$_SESSION['user_id']      // ID del usuario
$_SESSION['user_nombre']  // Nombre del usuario
$_SESSION['user_email']   // Email del usuario
$_SESSION['user_tipo']    // 'admin' o 'cliente'
```

## Flujo de Autenticación

1. **Usuario no autenticado:**
   - Puede ver catálogo, detalles, comparador
   - Debe registrarse/login para favoritos
   - Redirigido a login si intenta acceder a rutas protegidas

2. **Usuario autenticado (cliente):**
   - Acceso completo a funciones públicas
   - Puede guardar favoritos
   - Puede enviar consultas
   - No puede acceder a `/admin/*`

3. **Usuario autenticado (admin):**
   - Acceso completo como cliente
   - Acceso a todas las rutas `/admin/*`
   - Puede gestionar vehículos, usuarios y consultas

## Redirecciones

- **Login exitoso (cliente):** → `/index.php`
- **Login exitoso (admin):** → `/admin/index.php`
- **Sin autenticación en ruta protegida:** → `/login.php`
- **Cliente intenta acceder a admin:** → `/index.php`
- **Logout:** → `/index.php`

## Componentes Compartidos

### `includes/navbar.php`
- Navbar reutilizable en todas las páginas
- Detecta página actual automáticamente
- Muestra opciones según tipo de usuario
- Dropdown para usuario logueado

## Notas

- Todas las rutas públicas pueden ser accedidas sin autenticación
- Las rutas protegidas verifican autenticación antes de mostrar contenido
- Las rutas admin verifican además el tipo de usuario
- El sistema usa sesiones PHP para mantener el estado de autenticación
- No hay tokens JWT, se usa `$_SESSION` nativo de PHP


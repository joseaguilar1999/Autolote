# Documentación de API - Autolote

## Estructura de Endpoints

### Autenticación

#### POST `/api/auth/register`
Registrar nuevo usuario
```json
{
  "email": "user@example.com",
  "password": "password123",
  "name": "Juan Pérez",
  "phone": "1234567890"
}
```
**Respuesta:**
```json
{
  "access_token": "jwt_token",
  "token_type": "bearer",
  "user": { ... }
}
```

#### POST `/api/auth/login`
Iniciar sesión
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```
**Respuesta:**
```json
{
  "access_token": "jwt_token",
  "token_type": "bearer",
  "user": { ... }
}
```

#### GET `/api/auth/me`
Obtener usuario actual (requiere token)
**Headers:** `Authorization: Bearer {token}`

### Vehículos

#### GET `/api/vehicles`
Obtener lista de vehículos
**Query params:**
- `marca` - Filtrar por marca
- `año_min` - Año mínimo
- `año_max` - Año máximo
- `precio_min` - Precio mínimo
- `precio_max` - Precio máximo
- `transmision` - Manual o Automática
- `combustible` - Gasolina, Diesel, Eléctrico, Híbrido
- `status` - disponible o vendido

#### GET `/api/vehicles/{vehicle_id}`
Obtener vehículo por ID

#### POST `/api/vehicles`
Crear vehículo (requiere admin)
**Headers:** `Authorization: Bearer {token}`

#### PUT `/api/vehicles/{vehicle_id}`
Actualizar vehículo (requiere admin)

#### DELETE `/api/vehicles/{vehicle_id}`
Eliminar vehículo (requiere admin)

### Consultas/Inquiries

#### POST `/api/inquiries`
Crear consulta (público)
```json
{
  "vehicle_id": "uuid",
  "name": "Juan Pérez",
  "email": "user@example.com",
  "phone": "1234567890",
  "message": "Me interesa este vehículo"
}
```

#### GET `/api/inquiries`
Obtener todas las consultas (requiere admin)

#### PUT `/api/inquiries/{inquiry_id}`
Actualizar estado de consulta (requiere admin)
```json
{
  "status": "pendiente" | "contactado" | "cerrado"
}
```

### Favoritos

#### GET `/api/favorites`
Obtener favoritos del usuario (requiere login)

#### POST `/api/favorites`
Agregar a favoritos (requiere login)
```json
{
  "vehicle_id": "uuid"
}
```

#### DELETE `/api/favorites/{favorite_id}`
Eliminar de favoritos (requiere login)

### Usuarios (Admin)

#### GET `/api/users`
Obtener todos los usuarios (requiere admin)

#### DELETE `/api/users/{user_id}`
Eliminar usuario (requiere admin)

### Dashboard (Admin)

#### GET `/api/admin/stats`
Obtener estadísticas del dashboard (requiere admin)
**Respuesta:**
```json
{
  "total_vehicles": 50,
  "total_users": 100,
  "total_inquiries": 25,
  "pending_inquiries": 5,
  "available_vehicles": 45,
  "sold_vehicles": 5
}
```

## Modelos de Datos

### Vehicle
```json
{
  "id": "uuid",
  "marca": "Toyota",
  "modelo": "Corolla",
  "año": 2020,
  "precio": 25000.00,
  "kilometraje": 35000,
  "color": "Blanco",
  "transmision": "Automática",
  "combustible": "Gasolina",
  "descripcion": "Vehículo en excelente estado",
  "images": ["url1", "url2"],
  "featured": true,
  "status": "disponible",
  "created_at": "2024-01-01T00:00:00Z",
  "updated_at": "2024-01-01T00:00:00Z"
}
```

### User
```json
{
  "id": "uuid",
  "email": "user@example.com",
  "name": "Juan Pérez",
  "phone": "1234567890",
  "role": "user" | "admin",
  "created_at": "2024-01-01T00:00:00Z"
}
```

### Inquiry
```json
{
  "id": "uuid",
  "vehicle_id": "uuid",
  "name": "Juan Pérez",
  "email": "user@example.com",
  "phone": "1234567890",
  "message": "Mensaje de consulta",
  "status": "pendiente" | "contactado" | "cerrado",
  "created_at": "2024-01-01T00:00:00Z"
}
```

### Favorite
```json
{
  "id": "uuid",
  "user_id": "uuid",
  "vehicle_id": "uuid",
  "created_at": "2024-01-01T00:00:00Z"
}
```

## Autenticación

Todas las rutas protegidas requieren un token JWT en el header:
```
Authorization: Bearer {token}
```

El token se obtiene al hacer login o registro y expira después de 24 horas.

## CORS

La API está configurada para aceptar requests desde:
- Orígenes especificados en `CORS_ORIGINS`
- Por defecto: `*` (todos los orígenes)

## Notas

- Los IDs son UUIDs (strings)
- Las fechas están en formato ISO 8601
- Las contraseñas están hasheadas con bcrypt
- Los tokens JWT usan algoritmo HS256


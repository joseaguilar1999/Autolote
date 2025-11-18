# Guía de Uso de la API - Autolote

## Endpoints Disponibles

### Base URL
```
http://localhost/Autolote/api
```

### Vehículos

#### GET `/api/vehicles`
Obtener lista de vehículos con filtros opcionales

**Query Parameters:**
- `marca` - Filtrar por marca (búsqueda parcial)
- `año_min` - Año mínimo
- `año_max` - Año máximo
- `precio_min` - Precio mínimo
- `precio_max` - Precio máximo
- `transmision` - manual o automatica
- `status` - disponible, vendido, reservado (default: disponible)

**Ejemplo:**
```javascript
fetch('http://localhost/Autolote/api/vehicles?año_min=2020&precio_max=30000')
  .then(r => r.json())
  .then(data => console.log(data));
```

**Respuesta:**
```json
[
  {
    "id": 1,
    "marca": "Toyota",
    "modelo": "Corolla",
    "año": 2020,
    "precio": 25000.00,
    "kilometraje": 35000,
    "color": "Blanco",
    "transmision": "Automatica",
    "descripcion": "Vehículo en excelente estado",
    "images": ["http://localhost/Autolote/uploads/vehiculos/img1.jpg"],
    "featured": true,
    "status": "disponible",
    "created_at": "2024-01-01 00:00:00",
    "updated_at": "2024-01-01 00:00:00"
  }
]
```

#### GET `/api/vehicles/{id}`
Obtener un vehículo específico

**Ejemplo:**
```javascript
fetch('http://localhost/Autolote/api/vehicles/1')
  .then(r => r.json())
  .then(data => console.log(data));
```

#### POST `/api/vehicles`
Crear nuevo vehículo (requiere admin)

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "marca": "Toyota",
  "modelo": "Corolla",
  "año": 2020,
  "precio": 25000.00,
  "kilometraje": 35000,
  "color": "Blanco",
  "transmision": "Automatica",
  "descripcion": "Vehículo en excelente estado",
  "featured": false,
  "status": "disponible"
}
```

#### PUT `/api/vehicles/{id}`
Actualizar vehículo (requiere admin)

**Body (campos opcionales):**
```json
{
  "precio": 23000.00,
  "featured": true
}
```

#### DELETE `/api/vehicles/{id}`
Eliminar vehículo (requiere admin)

### Favoritos

#### GET `/api/favorites`
Obtener favoritos del usuario actual (requiere login)

**Ejemplo:**
```javascript
fetch('http://localhost/Autolote/api/favorites')
  .then(r => r.json())
  .then(data => console.log(data));
```

#### POST `/api/favorites`
Agregar vehículo a favoritos (requiere login)

**Body:**
```json
{
  "vehicle_id": 1
}
```

**Respuesta:**
```json
{
  "id": 5,
  "user_id": 1,
  "vehicle_id": 1,
  "created_at": "2024-01-01 00:00:00"
}
```

#### DELETE `/api/favorites?id={favorite_id}`
Eliminar de favoritos (requiere login)

### Consultas/Inquiries

#### POST `/api/inquiries`
Crear consulta (público)

**Body (JSON o FormData):**
```json
{
  "vehicle_id": 1,
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "phone": "1234567890",
  "message": "Me interesa este vehículo"
}
```

**Ejemplo con FormData:**
```javascript
const formData = new FormData();
formData.append('vehiculo_id', 1);
formData.append('nombre', 'Juan Pérez');
formData.append('email', 'juan@example.com');
formData.append('telefono', '1234567890');
formData.append('mensaje', 'Me interesa este vehículo');

fetch('http://localhost/Autolote/api/inquiries', {
  method: 'POST',
  body: formData
});
```

#### GET `/api/inquiries`
Obtener todas las consultas (requiere admin)

#### PUT `/api/inquiries?id={inquiry_id}`
Actualizar estado de consulta (requiere admin)

**Body:**
```json
{
  "status": "leida"
}
```

**Estados válidos:** `nueva`, `leida`, `respondida`

## Autenticación

Las rutas protegidas requieren que el usuario esté autenticado mediante sesión PHP. El sistema actual usa sesiones en lugar de tokens JWT.

### Para desarrollo futuro con JWT:

Si quieres implementar autenticación JWT similar a FastAPI:

1. Instalar `firebase/php-jwt`:
```bash
composer require firebase/php-jwt
```

2. Crear endpoint de login que devuelva token
3. Validar token en rutas protegidas

## CORS

La API está configurada para aceptar requests desde cualquier origen (`Access-Control-Allow-Origin: *`). Para producción, deberías restringir esto.

## Compatibilidad

- ✅ Compatible con el código PHP actual
- ✅ Soporta tanto JSON como FormData
- ✅ Mantiene compatibilidad con el frontend existente
- ✅ Estructura similar a FastAPI para fácil migración

## Ejemplo de Uso Completo

```javascript
// Obtener vehículos destacados
fetch('http://localhost/Autolote/api/vehicles?status=disponible')
  .then(r => r.json())
  .then(vehicles => {
    const featured = vehicles.filter(v => v.featured);
    console.log('Vehículos destacados:', featured);
  });

// Agregar a favoritos (requiere estar logueado)
fetch('http://localhost/Autolote/api/favorites', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    vehicle_id: 1
  })
})
  .then(r => r.json())
  .then(data => console.log('Agregado a favoritos:', data));
```


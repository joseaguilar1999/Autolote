-- Base de datos para Autolote - Versión Railway
-- NOTA: Railway ya crea la base de datos "railway", solo necesitamos crear las tablas
USE railway;

-- Tabla de usuarios (administradores y clientes)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    tipo ENUM('admin', 'cliente') DEFAULT 'cliente',
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de vehículos
CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    año INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    kilometraje INT NOT NULL,
    color VARCHAR(30) NOT NULL,
    transmision ENUM('manual', 'automatica') NOT NULL,
    combustible ENUM('Gasolina', 'Diesel', 'Eléctrico', 'Híbrido') DEFAULT 'Gasolina',
    descripcion TEXT,
    estado ENUM('disponible', 'vendido', 'reservado') DEFAULT 'disponible',
    featured TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de imágenes de vehículos
CREATE TABLE IF NOT EXISTS vehiculos_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT NOT NULL,
    imagen_path VARCHAR(255) NOT NULL,
    es_principal TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de consultas/contactos
CREATE TABLE IF NOT EXISTS consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(50),
    mensaje TEXT NOT NULL,
    estado ENUM('nueva', 'leida', 'respondida') DEFAULT 'nueva',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de favoritos
CREATE TABLE IF NOT EXISTS favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorito (usuario_id, vehiculo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario administrador por defecto (password: admin123)
INSERT INTO usuarios (nombre, email, password, tipo) VALUES 
('Administrador', 'admin@autolote.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Insertar algunos vehículos de ejemplo
INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, combustible, descripcion, featured) VALUES
('Toyota', 'Corolla', 2020, 25000.00, 35000, 'Blanco', 'automatica', 'Gasolina', 'Vehículo en excelente estado, único dueño, mantenimiento al día.', 1),
('Honda', 'Civic', 2019, 22000.00, 42000, 'Negro', 'manual', 'Gasolina', 'Civic deportivo, bien cuidado, sin accidentes.', 1),
('Ford', 'F-150', 2021, 45000.00, 15000, 'Rojo', 'automatica', 'Diesel', 'Pickup en perfecto estado, ideal para trabajo.', 1),
('Chevrolet', 'Cruze', 2020, 18000.00, 38000, 'Gris', 'automatica', 'Gasolina', 'Sedán compacto, económico y confiable.', 0),
('Nissan', 'Sentra', 2021, 20000.00, 25000, 'Azul', 'automatica', 'Gasolina', 'Vehículo casi nuevo, excelente opción.', 0)
ON DUPLICATE KEY UPDATE marca=marca;


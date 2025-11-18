-- Base de datos para Autolote
CREATE DATABASE IF NOT EXISTS autolote CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE autolote;

-- Tabla de usuarios (administradores y clientes)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    tipo ENUM('admin', 'cliente') DEFAULT 'cliente',
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de vehículos
CREATE TABLE vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    año INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    kilometraje INT NOT NULL,
    color VARCHAR(30) NOT NULL,
    transmision ENUM('manual', 'automatica') NOT NULL,
    descripcion TEXT,
    estado ENUM('disponible', 'vendido', 'reservado') DEFAULT 'disponible',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de imágenes de vehículos
CREATE TABLE vehiculos_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT NOT NULL,
    imagen_path VARCHAR(255) NOT NULL,
    es_principal TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de consultas/contactos
CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    mensaje TEXT NOT NULL,
    estado ENUM('nueva', 'leida', 'respondida') DEFAULT 'nueva',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de favoritos
CREATE TABLE favoritos (
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
('Administrador', 'admin@autolote.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar algunos vehículos de ejemplo
INSERT INTO vehiculos (marca, modelo, año, precio, kilometraje, color, transmision, descripcion) VALUES
('Toyota', 'Corolla', 2020, 25000.00, 35000, 'Blanco', 'automatica', 'Vehículo en excelente estado, único dueño, mantenimiento al día.'),
('Honda', 'Civic', 2019, 22000.00, 42000, 'Negro', 'manual', 'Civic deportivo, bien cuidado, sin accidentes.'),
('Ford', 'F-150', 2021, 45000.00, 15000, 'Rojo', 'automatica', 'Pickup en perfecto estado, ideal para trabajo.'),
('Chevrolet', 'Cruze', 2020, 18000.00, 38000, 'Gris', 'automatica', 'Sedán compacto, económico y confiable.'),
('Nissan', 'Sentra', 2021, 20000.00, 25000, 'Azul', 'automatica', 'Vehículo casi nuevo, excelente opción.');


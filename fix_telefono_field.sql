-- Script para actualizar el campo telefono en la base de datos existente
-- Ejecuta este script si ya tienes la base de datos creada

USE railway;

-- Aumentar el tamaño del campo telefono de VARCHAR(20) a VARCHAR(50)
ALTER TABLE usuarios MODIFY COLUMN telefono VARCHAR(50);


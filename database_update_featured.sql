-- Agregar campo featured a la tabla vehiculos
ALTER TABLE vehiculos ADD COLUMN featured TINYINT(1) DEFAULT 0 AFTER estado;

-- Marcar algunos vehículos como destacados
UPDATE vehiculos SET featured = 1 WHERE id IN (1, 2, 3) LIMIT 3;


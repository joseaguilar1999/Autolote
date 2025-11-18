-- Agregar campo combustible a la tabla vehiculos si no existe
-- Este script verifica si la columna existe antes de agregarla

-- Verificar y agregar columna combustible
SET @dbname = DATABASE();
SET @tablename = 'vehiculos';
SET @columnname = 'combustible';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM(\'Gasolina\', \'Diesel\', \'Eléctrico\', \'Híbrido\') DEFAULT \'Gasolina\' AFTER transmision')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Alternativa más simple (si tu versión de MySQL no soporta la anterior):
-- ALTER TABLE vehiculos 
-- ADD COLUMN combustible ENUM('Gasolina', 'Diesel', 'Eléctrico', 'Híbrido') DEFAULT 'Gasolina' AFTER transmision;

-- Fase 4: agregar propiedad a las recetas SIN eliminar filas existentes.
-- Ejecutar en la BD de pruebas desde phpMyAdmin. Seguro para repetir.
-- Los registros anteriores quedan sin propietario (usuario_id NULL) y solo se consultan.
USE reduccion_desperdicio_portafolio;

SET @has_owner_column = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'recetas' AND COLUMN_NAME = 'usuario_id'
);
SET @ddl = IF(
  @has_owner_column = 0,
  'ALTER TABLE recetas ADD COLUMN usuario_id INT UNSIGNED NULL AFTER id',
  'SELECT 1'
);
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_owner_fk = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'recetas'
    AND CONSTRAINT_NAME = 'fk_recetas_usuario' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @ddl = IF(
  @has_owner_fk = 0,
  'ALTER TABLE recetas ADD CONSTRAINT fk_recetas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Propiedad de recetas configurada' AS resultado;

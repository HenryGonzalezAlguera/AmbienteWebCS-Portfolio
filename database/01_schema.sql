-- AmbienteWebCS: base de datos reconstruida a partir de los archivos PHP existentes.
-- MySQL 8.0 / MariaDB (utf8mb4). Ejecutar en una instalación local.
-- No contiene usuarios ni contraseñas de ejemplo.
CREATE DATABASE IF NOT EXISTS reduccion_desperdicio
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reduccion_desperdicio;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(254) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recetas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NULL,
  nombre VARCHAR(180) NOT NULL,
  ingredientes TEXT NOT NULL,
  instrucciones TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_recetas_usuario_id (usuario_id),
  CONSTRAINT fk_recetas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donaciones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_donante VARCHAR(120) NOT NULL,
  alimento VARCHAR(180) NOT NULL,
  cantidad INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  punto_recoleccion VARCHAR(180) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT ck_donaciones_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB;

-- Las claves de recuperación se almacenan como SHA-256, no como tokens en texto plano.
-- La aplicación usa UNIX_TIMESTAMP (segundos UTC) para la expiración.
CREATE TABLE IF NOT EXISTS recuperacion_contrasena (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(254) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_recuperacion_token_hash (token_hash),
  KEY ix_recuperacion_email (email),
  KEY ix_recuperacion_expiracion (expires)
) ENGINE=InnoDB;

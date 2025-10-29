-- Script para actualizar la tabla usuarios y permitir guardar imágenes como BLOB
-- Ejecutar este script en phpMyAdmin

USE mascotas_db;

-- Cambiar el tipo de columna foto_url a LONGBLOB para poder guardar imágenes
ALTER TABLE usuarios MODIFY COLUMN foto_url LONGBLOB;

-- Verificar el cambio
DESCRIBE usuarios;
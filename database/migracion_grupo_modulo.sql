-- ============================================================
--  MIGRACIÓN: Tabla grupo_modulo (plan de estudios)
--  Se puebla automáticamente desde los datos existentes
--  Ejecutar en phpMyAdmin sobre asignacion_horas
-- ============================================================

USE asignacion_horas;

-- 1. Crear tabla grupo_modulo
CREATE TABLE IF NOT EXISTS grupo_modulo (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  grupo_id  INT NOT NULL,
  modulo_id INT NOT NULL,
  UNIQUE KEY uq_grupo_modulo (grupo_id, modulo_id),
  FOREIGN KEY (grupo_id)  REFERENCES grupo(id)  ON DELETE CASCADE,
  FOREIGN KEY (modulo_id) REFERENCES modulo(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Poblar desde las asignaciones existentes
INSERT IGNORE INTO grupo_modulo (grupo_id, modulo_id)
SELECT DISTINCT grupo_id, modulo_id
FROM asignacion;

-- 3. Verificar resultado
SELECT
  g.nombre AS grupo,
  m.nombre AS modulo,
  m.codigo
FROM grupo_modulo gm
JOIN grupo  g ON gm.grupo_id  = g.id
JOIN modulo m ON gm.modulo_id = m.id
ORDER BY g.ciclo, g.curso, g.nombre, m.nombre;

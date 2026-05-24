-- ============================================================
--  MIGRACIÓN: Añadir gestión de cursos escolares
--  Ejecutar en phpMyAdmin sobre asignacion_horas
-- ============================================================

USE asignacion_horas;

-- 1. Crear tabla curso_escolar
CREATE TABLE curso_escolar (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(20)  NOT NULL,        -- Ej: '2025-2026'
  fecha_inicio DATE         NOT NULL,
  fecha_fin    DATE         NOT NULL,
  activo       TINYINT(1)   NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 2. Insertar el curso actual
INSERT INTO curso_escolar (nombre, fecha_inicio, fecha_fin, activo)
VALUES ('2025-2026', '2025-09-01', '2026-06-30', 1);

-- 3. Añadir curso_id a profesor
ALTER TABLE profesor
  ADD COLUMN curso_id INT NOT NULL DEFAULT 1 AFTER id,
  ADD FOREIGN KEY (curso_id) REFERENCES curso_escolar(id) ON DELETE RESTRICT;

-- 4. Añadir curso_id a asignacion
ALTER TABLE asignacion
  ADD COLUMN curso_id INT NOT NULL DEFAULT 1 AFTER id,
  ADD FOREIGN KEY (curso_id) REFERENCES curso_escolar(id) ON DELETE RESTRICT;

-- 5. Actualizar vistas
DROP VIEW IF EXISTS v_asignaciones_completas;
CREATE VIEW v_asignaciones_completas AS
SELECT
  a.id,
  c.nombre      AS curso,
  CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
  p.puesto,
  m.nombre      AS modulo,
  m.codigo,
  g.nombre      AS grupo,
  g.ciclo,
  a.horas,
  a.es_desdoble,
  a.observaciones,
  a.profesor_id,
  a.modulo_id,
  a.grupo_id,
  a.curso_id
FROM asignacion a
JOIN curso_escolar c ON a.curso_id    = c.id
JOIN profesor      p ON a.profesor_id = p.id
JOIN modulo        m ON a.modulo_id   = m.id
JOIN grupo         g ON a.grupo_id    = g.id
ORDER BY c.nombre, g.ciclo, g.curso, g.nombre, m.nombre;

DROP VIEW IF EXISTS v_horas_por_profesor;
CREATE VIEW v_horas_por_profesor AS
SELECT
  p.id,
  p.curso_id,
  c.nombre      AS curso,
  CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
  p.puesto,
  p.horas_totales AS horas_contrato,
  COALESCE(SUM(a.horas), 0) AS horas_asignadas,
  p.horas_totales - COALESCE(SUM(a.horas), 0) AS horas_libres
FROM profesor p
JOIN curso_escolar c ON p.curso_id = c.id
LEFT JOIN asignacion a ON a.profesor_id = p.id AND a.curso_id = p.curso_id
GROUP BY p.id
ORDER BY p.apellidos;

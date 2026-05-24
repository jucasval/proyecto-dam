-- ============================================================
--  MIGRACIÓN: tipo_cuerpo se hereda de profesor
--  Ejecutar en phpMyAdmin sobre la base de datos asignacion_horas
-- ============================================================

USE asignacion_horas;

-- 1. Eliminar columna tipo_cuerpo de asignacion
ALTER TABLE asignacion DROP COLUMN tipo_cuerpo;

-- 2. Actualizar vista v_asignaciones_completas
DROP VIEW IF EXISTS v_asignaciones_completas;
CREATE VIEW v_asignaciones_completas AS
SELECT
  a.id,
  CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
  p.puesto,
  m.nombre AS modulo,
  m.codigo,
  g.nombre AS grupo,
  g.ciclo,
  a.horas,
  a.es_desdoble,
  a.observaciones,
  a.profesor_id,
  a.modulo_id,
  a.grupo_id
FROM asignacion a
JOIN profesor p ON a.profesor_id = p.id
JOIN modulo   m ON a.modulo_id   = m.id
JOIN grupo    g ON a.grupo_id    = g.id
ORDER BY g.ciclo, g.curso, g.nombre, m.nombre;

-- 3. Actualizar vista v_horas_por_profesor (no cambia, pero la refrescamos)
DROP VIEW IF EXISTS v_horas_por_profesor;
CREATE VIEW v_horas_por_profesor AS
SELECT
  p.id,
  CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
  p.puesto,
  p.horas_totales AS horas_contrato,
  COALESCE(SUM(a.horas), 0) AS horas_asignadas,
  p.horas_totales - COALESCE(SUM(a.horas), 0) AS horas_libres
FROM profesor p
LEFT JOIN asignacion a ON a.profesor_id = p.id
GROUP BY p.id
ORDER BY p.apellidos;

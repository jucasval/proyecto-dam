-- ============================================================
--  CORRECCIÓN: Vista v_horas_por_profesor
--  Corrige el cálculo de horas de módulos y cargos
--  Ejecutar en phpMyAdmin sobre asignacion_horas
-- ============================================================

USE asignacion_horas;

DROP VIEW IF EXISTS v_horas_por_profesor;

CREATE VIEW v_horas_por_profesor AS
SELECT
  p.id,
  p.curso_id,
  c.nombre      AS curso,
  CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
  p.puesto,
  p.horas_totales AS horas_contrato,

  -- Horas de módulos: subquery para evitar problemas con DISTINCT
  COALESCE((
    SELECT SUM(a.horas)
    FROM asignacion a
    WHERE a.profesor_id = p.id AND a.curso_id = p.curso_id
  ), 0) AS horas_modulos,

  -- Horas de cargos
  COALESCE((
    SELECT SUM(pc.horas)
    FROM profesor_cargo pc
    WHERE pc.profesor_id = p.id AND pc.curso_id = p.curso_id
  ), 0) AS horas_cargos,

  -- Total asignado
  COALESCE((
    SELECT SUM(a.horas)
    FROM asignacion a
    WHERE a.profesor_id = p.id AND a.curso_id = p.curso_id
  ), 0) +
  COALESCE((
    SELECT SUM(pc.horas)
    FROM profesor_cargo pc
    WHERE pc.profesor_id = p.id AND pc.curso_id = p.curso_id
  ), 0) AS horas_asignadas,

  -- Horas libres
  p.horas_totales - (
    COALESCE((
      SELECT SUM(a.horas)
      FROM asignacion a
      WHERE a.profesor_id = p.id AND a.curso_id = p.curso_id
    ), 0) +
    COALESCE((
      SELECT SUM(pc.horas)
      FROM profesor_cargo pc
      WHERE pc.profesor_id = p.id AND pc.curso_id = p.curso_id
    ), 0)
  ) AS horas_libres

FROM profesor p
JOIN curso_escolar c ON p.curso_id = c.id
ORDER BY p.apellidos, p.nombre;

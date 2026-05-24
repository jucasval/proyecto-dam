-- ============================================================
--  MIGRACIÓN: Tablas cargo y profesor_cargo
--  Ejecutar en phpMyAdmin sobre asignacion_horas
-- ============================================================

USE asignacion_horas;

-- 1. Tabla global de cargos
CREATE TABLE IF NOT EXISTS cargo (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  horas  DECIMAL(4,1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- 2. Asignación de cargos a profesores por curso
CREATE TABLE IF NOT EXISTS profesor_cargo (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  curso_id    INT          NOT NULL,
  profesor_id INT          NOT NULL,
  cargo_id    INT          NOT NULL,
  horas       DECIMAL(4,1) NOT NULL,   -- puede sobrescribir cargo.horas
  FOREIGN KEY (curso_id)    REFERENCES curso_escolar(id) ON DELETE RESTRICT,
  FOREIGN KEY (profesor_id) REFERENCES profesor(id)      ON DELETE RESTRICT,
  FOREIGN KEY (cargo_id)    REFERENCES cargo(id)         ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. Insertar cargos actuales del Excel
INSERT INTO cargo (nombre, horas) VALUES
('Jefatura de Estudios',        10),
('Secretaría',                  10),
('Coordinación TDE',             5),
('Escuela 4.0',                  1),
('Jefatura Dpto. Internacional', 1),
('Reducción Mayores 55',         2),
('Liberado Sindical',            4);

-- 4. Actualizar vista v_horas_por_profesor incluyendo horas de cargos
DROP VIEW IF EXISTS v_horas_por_profesor;
CREATE VIEW v_horas_por_profesor AS
SELECT
  p.id,
  p.curso_id,
  c.nombre      AS curso,
  CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
  p.puesto,
  p.horas_totales AS horas_contrato,
  COALESCE(SUM(DISTINCT a.horas), 0)  AS horas_modulos,
  COALESCE((
    SELECT SUM(pc.horas)
    FROM profesor_cargo pc
    WHERE pc.profesor_id = p.id AND pc.curso_id = p.curso_id
  ), 0) AS horas_cargos,
  COALESCE(SUM(DISTINCT a.horas), 0) +
  COALESCE((
    SELECT SUM(pc.horas)
    FROM profesor_cargo pc
    WHERE pc.profesor_id = p.id AND pc.curso_id = p.curso_id
  ), 0) AS horas_asignadas,
  p.horas_totales - (
    COALESCE(SUM(DISTINCT a.horas), 0) +
    COALESCE((
      SELECT SUM(pc.horas)
      FROM profesor_cargo pc
      WHERE pc.profesor_id = p.id AND pc.curso_id = p.curso_id
    ), 0)
  ) AS horas_libres
FROM profesor p
JOIN curso_escolar c ON p.curso_id = c.id
LEFT JOIN asignacion a ON a.profesor_id = p.id AND a.curso_id = p.curso_id
GROUP BY p.id
ORDER BY p.apellidos;

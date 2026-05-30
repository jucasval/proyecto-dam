-- 1. Añadir Tutor/a como cargo con 1 hora
INSERT INTO cargo (nombre, horas) VALUES ('Tutor/a', 1);

-- 2. Crear asignaciones de cargo para los profesores que tenían Tutor/a como módulo
INSERT INTO profesor_cargo (curso_id, profesor_id, cargo_id, horas)
SELECT a.curso_id, a.profesor_id, 
       (SELECT id FROM cargo WHERE nombre = 'Tutor/a'),
       1
FROM asignacion a
WHERE a.modulo_id = (SELECT id FROM modulo WHERE codigo = 'TUT');

-- 3. Eliminar las asignaciones del módulo Tutor/a
DELETE FROM asignacion 
WHERE modulo_id = (SELECT id FROM modulo WHERE codigo = 'TUT');

-- 4. Eliminar el módulo Tutor/a
DELETE FROM modulo WHERE codigo = 'TUT';

-- 5. Eliminar de grupo_modulo
DELETE FROM grupo_modulo 
WHERE modulo_id NOT IN (SELECT id FROM modulo);
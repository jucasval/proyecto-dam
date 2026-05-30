ALTER TABLE asignacion
ADD UNIQUE KEY uq_asignacion (
    curso_id,
    profesor_id,
    modulo_id,
    grupo_id
);
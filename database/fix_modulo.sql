ALTER TABLE modulo 
ADD COLUMN curso_id INT NOT NULL DEFAULT 1 AFTER id,
ADD FOREIGN KEY (curso_id) REFERENCES curso_escolar(id) ON DELETE RESTRICT;

UPDATE modulo SET curso_id = (SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1);

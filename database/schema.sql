-- ============================================================
--  ASIGNACIÓN DE HORAS POR DEPARTAMENTOS
--  Departamento de Informática - Curso 2025-2026
--  Base de datos generada a partir del Excel del centro
-- ============================================================

CREATE DATABASE IF NOT EXISTS asignacion_horas
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE asignacion_horas;

-- ============================================================
--  TABLAS
-- ============================================================

CREATE TABLE profesor (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(100) NOT NULL,
  apellidos  VARCHAR(150) NOT NULL,
  puesto     ENUM('PES','PTFP') NOT NULL,
  horas_totales INT DEFAULT 18
) ENGINE=InnoDB;

CREATE TABLE grupo (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(50)  NOT NULL,          -- Ej: '1º DAM'
  ciclo      VARCHAR(20)  NOT NULL,          -- Ej: 'DAM', 'DAW', 'ASIR', 'SMR', 'FPB', 'ESO', 'BTO'
  curso      TINYINT      NOT NULL,          -- 1 o 2
  modalidad  VARCHAR(30)  DEFAULT 'Presencial'
) ENGINE=InnoDB;

CREATE TABLE modulo (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(200) NOT NULL,
  codigo      VARCHAR(30)  DEFAULT NULL,     -- Ej: 'BD', 'PROG', 'LMSGI'
  horas_pes   DECIMAL(4,1) DEFAULT 0,
  horas_ptfp  DECIMAL(4,1) DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE asignacion (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  profesor_id  INT          NOT NULL,
  modulo_id    INT          NOT NULL,
  grupo_id     INT          NOT NULL,
  horas        DECIMAL(4,1) NOT NULL DEFAULT 0,
  tipo_cuerpo  ENUM('PES','PTFP') NOT NULL,
  es_desdoble  TINYINT(1)   NOT NULL DEFAULT 0,
  observaciones VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (profesor_id) REFERENCES profesor(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (modulo_id)   REFERENCES modulo(id)   ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (grupo_id)    REFERENCES grupo(id)    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  DATOS: PROFESORES
-- ============================================================

INSERT INTO profesor (nombre, apellidos, puesto, horas_totales) VALUES
('María Ángeles', 'Amat López',                        'PES',  18),
('Agustín',       'Belmonte Carmona',                  'PES',  18),
('Pedro',         'Fernandez Arias',                   'PES',  18),
('Francisco',     'Fernandez de Piñar López',          'PES',  18),
('Ignacio',       'Fernandez Sedano',                  'PES',  18),
('Francisco Javier', 'Fernandez-Arévalo Collado',      'PES',  18),
('Montserrat',    'González Carretero',                'PES',  18),
('José Ramón',    'Jiménez Reyes',                     'PES',  18),
('Antonio José',  'López Fernandez',                   'PES',  18),
('Daniel Alberto','Moreno Barón',                      'PES',  18),
('Daniel',        'Pastor Jiménez',                    'PES',  18),
('Andrés',        'Rubio del Río',                     'PES',  18),
('Francisco Miguel','Tejada Ferrándiz',                'PES',  18),
('Isabel',        'Cayuela Pérez',                     'PTFP', 18),
('María Alejandra','Cayuela López',                    'PTFP', 18),
('Antonio',       'Cervantes Alarcón',                 'PTFP', 18),
('Roberto',       'Lázaro García',                     'PTFP', 18),
('Emilio José',   'Martínez Palenzuela',               'PTFP', 18),
('Federico',      'Martínez Pérez',                    'PTFP', 18),
('Guadalupe de la Estrella','Martínez Nieto',          'PTFP', 18),
('María Ángeles', 'Ruiz Bernal',                       'PTFP', 18),
('Carmen',        'Bocanegra García',                  'PTFP', 18),
('Juan Francisco','Borrás Correa',                     'PTFP', 18),
('Manuel Francisco','Doña Montoya',                    'PTFP', 18),
('Filomena',      'Galera Lorente',                    'PTFP', 18),
('Juan Jose',     'Martínez Morales',                  'PTFP', 18);

-- ============================================================
--  DATOS: GRUPOS
-- ============================================================

INSERT INTO grupo (nombre, ciclo, curso, modalidad) VALUES
('1º DAM',          'DAM',    1, 'Presencial'),
('2º DAM',          'DAM',    2, 'Presencial'),
('1º DAW',          'DAW',    1, 'Presencial'),
('2º DAW',          'DAW',    2, 'Presencial'),
('1º ASIR',         'ASIR',   1, 'Presencial'),
('2º ASIR',         'ASIR',   2, 'Presencial'),
('1º SMR A',        'SMR',    1, 'Presencial'),
('1º SMR B',        'SMR',    1, 'Presencial'),
('2º SMR A',        'SMR',    2, 'Presencial'),
('1º FPB IC',       'FPB',    1, 'Presencial'),
('2º FPB IC',       'FPB',    2, 'Presencial'),
('1º FPB OFICINA',  'FPB',    1, 'Presencial'),
('2º FPB OFICINA',  'FPB',    2, 'Presencial'),
('1º FPB-E OFICINA','FPB',    1, 'Presencial'),
('2º FPB-E OFICINA','FPB',    2, 'Presencial'),
('1º ESO G1',       'ESO',    1, 'Presencial'),
('2º ESO G1',       'ESO',    2, 'Presencial'),
('3º ESO G1',       'ESO',    3, 'Presencial'),
('4º ESO G1',       'ESO',    4, 'Presencial'),
('4º ESO G2',       'ESO',    4, 'Presencial'),
('1º BTO G1',       'BTO',    1, 'Presencial');

-- ============================================================
--  DATOS: MÓDULOS
-- ============================================================

INSERT INTO modulo (nombre, codigo, horas_pes, horas_ptfp) VALUES
-- DAM / DAW comunes
('Sistemas Informáticos',                                      'SI',     0,   5),
('Bases de datos',                                             'BD',     6,   0),
('Programación',                                               'PROG',   8,   0),
('Lenguajes de marcas y Sistemas de Gestión de la Información','LMSGI',  3,   0),
('Digitalización Aplicada al Sistema Productivo',              'DIG',    1,   0),
('Entornos de desarrollo',                                     'ED',     3,   0),
('Coord. y Seguim. FP Dual',                                   'DUAL',   2,   0),
('Tutor/a',                                                    'TUT',    0,   0),
-- DAM 2º
('Desarrollo de interfaces',                                   'DI',     0,   6),
('Sistemas de gestión empresarial',                            'SGE',    0,   4),
('Acceso a datos',                                             'AD',     4,   0),
('Programación multimedia y de dispositivos móviles',          'PMDM',   3,   0),
('Programación de servicios y procesos',                       'PSP',    3,   0),
('Optativa',                                                   'OPT',    3,   0),
('Proyecto',                                                   'PROY',   1,   0),
('FCT',                                                        'FCT',    0,   2),
-- DAW 2º
('Desarrollo web en entorno servidor',                         'DWES',   7,   0),
('Despliegue de aplicaciones web',                             'DAW',    2,   0),
('Desarrollo web en entorno cliente',                          'DWEC',   0,   6),
('Diseño de interfaces web',                                   'DIW',    0,   5),
-- ASIR 1º
('Implantación de sistemas operativos',                        'ISO',    0,   7),
('Fundamentos Hardware',                                       'FH',     0,   3),
('Planificación y Administración de Redes',                    'PAR',    6,   0),
('Gestión de Bases de Datos',                                  'GBD',    6,   0),
-- ASIR 2º
('Administración de sistemas operativos',                      'ASO',    0,   5),
('Servicios en red e Internet',                                'SRI',    5,   0),
('Implantación de aplicaciones Web',                           'IAW',    4,   0),
('Administración de sistemas gestores de bases de datos',      'ASGBD',  3,   0),
('Seguridad y Alta disponibilidad',                            'SAD',    3,   0),
-- SMR
('Montaje y mantenimiento de equipos',                         'MME',    0,   6),
('Sistemas operativos monopuesto',                             'SOM',    0,   5),
('Aplicaciones ofimáticas',                                    'AO',     0,   7),
('Redes Locales',                                              'RL',     7,   0),
('Sistemas Operativos en red',                                 'SOR',    0,   6),
('Aplicaciones web',                                           'AW',     4,   0),
('Seguridad Informática',                                      'SEGINFO',4,   0),
('Servicios en red',                                           'SR',     6,   0),
-- FPB
('Montaje y mantenimiento de sistemas y componentes informáticos','MMSCI',0,  9),
('Operaciones auxiliares para la configuración y explotación', 'OACE',   0,   7),
('Equipos electrónicos y eléctricos',                          'EEE',    0,  10),
('Instalación y mantenimiento de redes para transmisión de datos','IMRD', 0,   8),
('Ofimática y archivo de documentos',                          'OAD',    0,  10),
-- ESO / BTO
('Computación y Robótica',                                     'COMP',   2,   0),
('Digitalización',                                             'DIGIT',  3,   0),
('Creación Digital y Pensamiento Computacional',               'CDPC',   2,   0),
-- Digitalización SMR (nombre diferente en SMR)
('Digitalización Aplicada a los Sistemas Productivos',         'DIG-SMR',1,   0);

-- ============================================================
--  DATOS: ASIGNACIONES
--  (profesor_id, modulo_id, grupo_id, horas, tipo_cuerpo, es_desdoble, observaciones)
--  IDs profesor según INSERT anterior (orden 1..26)
--  IDs grupo: 1=1ºDAM, 2=2ºDAM, 3=1ºDAW, 4=2ºDAW, 5=1ºASIR,
--             6=2ºASIR, 7=1ºSMR-A, 8=1ºSMR-B, 9=2ºSMR-A,
--             10=1ºFPB-IC, 11=2ºFPB-IC, 12=1ºFPB-OFI, 13=2ºFPB-OFI,
--             14=1ºFPB-E-OFI, 15=2ºFPB-E-OFI
--             16=1ºESO, 17=2ºESO, 18=3ºESO, 19=4ºESO-G1, 20=4ºESO-G2, 21=1ºBTO
--  IDs módulo según INSERT anterior
-- ============================================================

INSERT INTO asignacion (profesor_id, modulo_id, grupo_id, horas, tipo_cuerpo, es_desdoble, observaciones) VALUES
-- ===================== 1º DAM =====================
(9,  8,  1, 0,   'PES',  0, 'Tutor/a'),
(9,  1,  1, 5,   'PTFP', 0, NULL),
(14, 1,  1, 3,   'PTFP', 1, 'Desdoble'),
(2,  7,  1, 2,   'PES',  0, NULL),
(4,  2,  1, 6,   'PES',  0, NULL),
(2,  2,  1, 3,   'PES',  1, 'Desdoble'),
(8,  3,  1, 8,   'PES',  0, NULL),
(12, 3,  1, 1,   'PES',  1, 'Desdoble - 1h Rubio'),
(7,  3,  1, 3,   'PES',  1, 'Desdoble - 3h González'),
(1,  4,  1, 3,   'PES',  0, NULL),
(1,  5,  1, 1,   'PES',  0, NULL),
(7,  6,  1, 3,   'PES',  0, NULL),

-- ===================== 2º DAM =====================
(5,  8,  2, 0,   'PES',  0, 'Tutor/a'),
(14, 9,  2, 6,   'PTFP', 0, NULL),
(9,  10, 2, 4,   'PTFP', 0, NULL),
(9,  15, 2, 1,   'PTFP', 0, NULL),
(2,  7,  2, 1,   'PES',  0, NULL),
(12, 11, 2, 4,   'PES',  0, NULL),
(5,  12, 2, 3,   'PES',  0, NULL),
(12, 13, 2, 3,   'PES',  0, NULL),
(5,  14, 2, 3,   'PES',  0, NULL),
(5,  15, 2, 1,   'PES',  0, NULL),
(9,  16, 2, 2,   'PTFP', 0, NULL),

-- ===================== 1º DAW =====================
(8,  8,  3, 0,   'PES',  0, 'Tutor/a'),
(9,  1,  3, 5,   'PTFP', 0, NULL),
(17, 1,  3, 3,   'PTFP', 1, 'Desdoble'),
(2,  7,  3, 2,   'PES',  0, NULL),
(4,  2,  3, 6,   'PES',  0, NULL),
(2,  2,  3, 1,   'PES',  1, 'Desdoble - 1h Belmonte'),
(8,  2,  3, 2,   'PES',  1, 'Desdoble - 2h Jiménez'),
(8,  3,  3, 8,   'PES',  0, NULL),
(5,  3,  3, 4,   'PES',  1, 'Desdoble'),
(1,  4,  3, 3,   'PES',  0, NULL),
(1,  5,  3, 1,   'PES',  0, NULL),
(7,  6,  3, 3,   'PES',  0, NULL),

-- ===================== 2º DAW =====================
(6,  8,  4, 0,   'PES',  0, 'Tutor/a'),
(6,  17, 4, 7,   'PES',  0, NULL),
(11, 18, 4, 2,   'PES',  0, NULL),
(6,  15, 4, 2,   'PES',  0, NULL),
(16, 19, 4, 6,   'PTFP', 0, NULL),
(14, 20, 4, 5,   'PTFP', 0, NULL),
(14, 14, 4, 3,   'PTFP', 0, NULL),
(15, 7,  4, 1,   'PTFP', 0, NULL),
(6,  16, 4, 2,   'PES',  0, '2h Fernandez-Arévalo'),
(14, 16, 4, 1,   'PTFP', 0, '1h Cayuela Pérez'),

-- ===================== 1º ASIR =====================
(1,  8,  5, 0,   'PES',  0, 'Tutor/a'),
(21, 21, 5, 7,   'PTFP', 0, NULL),
(19, 21, 5, 3,   'PTFP', 1, 'Desdoble - 3h Martínez Pérez'),
(18, 21, 5, 1,   'PTFP', 1, 'Desdoble - 1h Martínez Palenzuela'),
(16, 22, 5, 3,   'PTFP', 0, NULL),
(3,  7,  5, 1,   'PES',  0, '1h Fernandez Arias'),
(2,  7,  5, 1,   'PES',  0, '1h Belmonte'),
(3,  23, 5, 6,   'PES',  0, NULL),
(11, 23, 5, 3,   'PES',  1, 'Desdoble'),
(4,  24, 5, 6,   'PES',  0, NULL),
(11, 24, 5, 3,   'PES',  1, 'Desdoble'),
(1,  4,  5, 3,   'PES',  0, NULL),
(1,  5,  5, 1,   'PES',  0, NULL),

-- ===================== 2º ASIR =====================
(3,  8,  6, 0,   'PES',  0, 'Tutor/a'),
(19, 25, 6, 5,   'PTFP', 0, NULL),
(19, 15, 6, 1,   'PTFP', 0, NULL),
(10, 15, 6, 1,   'PES',  0, NULL),
(10, 26, 6, 5,   'PES',  0, NULL),
(6,  27, 6, 4,   'PES',  0, NULL),
(6,  28, 6, 3,   'PES',  0, NULL),
(3,  29, 6, 3,   'PES',  0, NULL),
(3,  14, 6, 3,   'PES',  0, NULL),
(3,  7,  6, 1,   'PES',  0, NULL),
(19, 16, 6, 2,   'PTFP', 0, '2h Martínez Pérez'),
(10, 16, 6, 1,   'PES',  0, '1h Moreno Barón'),

-- ===================== 1º SMR A =====================
(17, 8,  7, 0,   'PTFP', 0, 'Tutor/a'),
(18, 30, 7, 6,   'PTFP', 0, NULL),
(9,  30, 7, 1,   'PES',  1, 'Desdoble - 1h López'),
(17, 30, 7, 1,   'PTFP', 1, 'Desdoble - 1h Lázaro'),
(20, 30, 7, 1,   'PTFP', 1, 'Desdoble - 1h Martínez Nieto'),
(15, 31, 7, 5,   'PTFP', 0, NULL),
(17, 32, 7, 7,   'PTFP', 0, NULL),
(26, 32, 7, 4,   'PTFP', 1, 'Desdoble'),
(11, 7,  7, 2,   'PES',  0, NULL),
(2,  46, 7, 1,   'PES',  0, 'Digitalización SMR'),
(13, 33, 7, 7,   'PES',  0, NULL),
(7,  33, 7, 3,   'PES',  1, 'Desdoble'),

-- ===================== 2º SMR A =====================
(13, 8,  9, 0,   'PES',  0, 'Tutor/a'),
(19, 34, 9, 6,   'PTFP', 0, NULL),
(18, 34, 9, 3,   'PTFP', 1, 'Desdoble'),
(16, 14, 9, 3,   'PTFP', 0, NULL),
(1,  35, 9, 4,   'PES',  0, NULL),
(10, 36, 9, 4,   'PES',  0, NULL),
(10, 37, 9, 6,   'PES',  0, NULL),
(13, 37, 9, 3,   'PES',  1, 'Desdoble'),
(13, 15, 9, 2,   'PES',  0, NULL),
(7,  7,  9, 1,   'PES',  0, '1h González'),
(2,  7,  9, 1,   'PES',  0, '1h Belmonte'),
(10, 16, 9, 1,   'PES',  0, '1h Moreno'),
(13, 16, 9, 1,   'PES',  0, '1h Tejada'),
(1,  16, 9, 2,   'PES',  0, '2h Amat'),

-- ===================== 1º SMR B =====================
(20, 8,  8, 0,   'PTFP', 0, 'Tutor/a'),
(16, 30, 8, 6,   'PTFP', 0, NULL),
(20, 31, 8, 5,   'PTFP', 0, NULL),
(17, 32, 8, 7,   'PTFP', 0, NULL),
(11, 7,  8, 1,   'PES',  0, NULL),
(11, 33, 8, 7,   'PES',  0, NULL),
(2,  46, 8, 1,   'PES',  0, 'Digitalización SMR'),

-- ===================== 1º FPB IC =====================
(22, 8,  10, 1,  'PTFP', 0, 'Tutor/a'),
(24, 38, 10, 9,  'PTFP', 0, NULL),
(22, 39, 10, 7,  'PTFP', 0, NULL),
(22, 7,  10, 1,  'PTFP', 0, NULL),

-- ===================== 2º FPB IC =====================
(26, 8,  11, 1,  'PTFP', 0, 'Tutor/a'),
(26, 40, 11, 10, 'PTFP', 0, NULL),
(18, 41, 11, 8,  'PTFP', 0, NULL),
(26, 15, 11, 2,  'PTFP', 0, NULL),
(26, 7,  11, 1,  'PTFP', 0, NULL),

-- ===================== 1º FPB OFICINA =====================
(24, 8,  12, 1,  'PTFP', 0, 'Tutor/a'),
(22, 38, 12, 9,  'PTFP', 0, NULL),
(24, 39, 12, 7,  'PTFP', 0, NULL),
(24, 7,  12, 1,  'PTFP', 0, NULL),

-- ===================== 2º FPB OFICINA =====================
(15, 8,  13, 1,  'PTFP', 0, 'Tutor/a'),
(20, 42, 13, 10, 'PTFP', 0, NULL),
(15, 41, 13, 8,  'PTFP', 0, NULL),
(15, 15, 13, 2,  'PTFP', 0, NULL),
(15, 7,  13, 1,  'PTFP', 0, NULL),

-- ===================== 1º FPB-E OFICINA =====================
(21, 8,  14, 1,  'PTFP', 0, 'Tutor/a'),
(21, 38, 14, 9,  'PTFP', 0, NULL),
(23, 39, 14, 7,  'PTFP', 0, NULL),

-- ===================== 2º FPB-E OFICINA =====================
(23, 42, 15, 10, 'PTFP', 0, NULL),
(25, 41, 15, 8,  'PTFP', 0, NULL),
(21, 15, 15, 1,  'PTFP', 0, '1h Ruiz Bernal'),
(23, 15, 15, 1,  'PTFP', 0, '1h Borrás'),

-- ===================== ESO / BTO =====================
(7,  43, 16, 2,  'PES',  0, NULL),
(7,  43, 17, 2,  'PES',  0, NULL),
(2,  43, 18, 2,  'PES',  0, NULL),
(2,  44, 19, 3,  'PES',  0, NULL),
(5,  44, 20, 3,  'PES',  0, NULL),
(5,  45, 21, 2,  'PES',  0, NULL);

-- ============================================================
--  VISTAS ÚTILES PARA EL CRUD
-- ============================================================

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
  a.tipo_cuerpo,
  a.es_desdoble,
  a.observaciones
FROM asignacion a
JOIN profesor p ON a.profesor_id = p.id
JOIN modulo   m ON a.modulo_id   = m.id
JOIN grupo    g ON a.grupo_id    = g.id
ORDER BY g.ciclo, g.curso, g.nombre, m.nombre;

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

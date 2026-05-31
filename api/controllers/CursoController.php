<?php
// api/controllers/CursoController.php

class CursoController {
    public function __construct(private PDO $db) {}

    public function index(): void {
        $stmt = $this->db->query("SELECT * FROM curso_escolar ORDER BY fecha_inicio DESC");
        echo json_encode($stmt->fetchAll());
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM curso_escolar WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['error' => 'Curso no encontrado']); return; }
        echo json_encode($row);
    }

    public function activo(): void {
        $stmt = $this->db->query("SELECT * FROM curso_escolar WHERE activo = 1 LIMIT 1");
        $row  = $stmt->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['error' => 'No hay ningún curso activo']); return; }
        echo json_encode($row);
    }

    public function store(array $data): void {
        foreach (['nombre', 'fecha_inicio', 'fecha_fin'] as $f) {
            if (empty($data[$f])) {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$f' es obligatorio"]);
                return;
            }
        }

        $this->db->beginTransaction();
        try {
            // Obtener curso activo antes de desactivar
            $stmtActivo = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1");
            $cursoAnterior = $stmtActivo->fetch();
            $cursoAnteriorId = $cursoAnterior ? (int)$cursoAnterior['id'] : null;

            // Desactivar curso actual
            $this->db->exec("UPDATE curso_escolar SET activo = 0");

            // Crear nuevo curso
            $stmt = $this->db->prepare(
                "INSERT INTO curso_escolar (nombre, fecha_inicio, fecha_fin, activo)
                 VALUES (:nombre, :fecha_inicio, :fecha_fin, 1)"
            );
            $stmt->execute([
                ':nombre'       => trim($data['nombre']),
                ':fecha_inicio' => $data['fecha_inicio'],
                ':fecha_fin'    => $data['fecha_fin'],
            ]);
            $nuevoCursoId = $this->db->lastInsertId();

            // Copiar profesores seleccionados
            $profesoresIds = $data['profesores_ids'] ?? [];
            if (!empty($profesoresIds)) {
                $placeholders = implode(',', array_fill(0, count($profesoresIds), '?'));
                $stmt = $this->db->prepare(
                    "SELECT id, nombre, apellidos, puesto, horas_totales FROM profesor WHERE id IN ($placeholders)"
                );
                $stmt->execute($profesoresIds);
                $profesores = $stmt->fetchAll();

                $insertProf = $this->db->prepare(
                    "INSERT INTO profesor (curso_id, nombre, apellidos, puesto, horas_totales)
                     VALUES (:curso_id, :nombre, :apellidos, :puesto, :horas_totales)"
                );
                $mapaIds = []; // [id_antiguo => id_nuevo]
                foreach ($profesores as $p) {
                    $insertProf->execute([
                        ':curso_id'      => $nuevoCursoId,
                        ':nombre'        => $p['nombre'],
                        ':apellidos'     => $p['apellidos'],
                        ':puesto'        => $p['puesto'],
                        ':horas_totales' => $p['horas_totales'],
                    ]);
                    $mapaIds[$p['id']] = $this->db->lastInsertId();
                }

                // Copiar asignaciones de cargos del curso anterior con los nuevos IDs
                // EXCEPTO Tutor/a (se asigna manualmente cada año)
                if ($cursoAnteriorId && !empty($mapaIds)) {
                    $oldIds = array_keys($mapaIds);
                    $ph     = implode(',', array_fill(0, count($oldIds), '?'));
                    $stmtCargos = $this->db->prepare(
                        "SELECT pc.profesor_id, pc.cargo_id, pc.horas
                         FROM profesor_cargo pc
                         JOIN cargo c ON pc.cargo_id = c.id
                         WHERE pc.curso_id = ? AND pc.profesor_id IN ($ph)
                         AND c.nombre != 'Tutor/a'"
                    );
                    $stmtCargos->execute(array_merge([$cursoAnteriorId], $oldIds));
                    $cargosAnteriores = $stmtCargos->fetchAll();

                    $insertCargo = $this->db->prepare(
                        "INSERT INTO profesor_cargo (curso_id, profesor_id, cargo_id, horas)
                         VALUES (:curso_id, :profesor_id, :cargo_id, :horas)"
                    );
                    foreach ($cargosAnteriores as $c) {
                        $nuevoProfesorId = $mapaIds[$c['profesor_id']] ?? null;
                        if ($nuevoProfesorId) {
                            $insertCargo->execute([
                                ':curso_id'    => $nuevoCursoId,
                                ':profesor_id' => $nuevoProfesorId,
                                ':cargo_id'    => $c['cargo_id'],
                                ':horas'       => $c['horas'],
                            ]);
                        }
                    }
                }
            }

            $this->db->commit();
            http_response_code(201);
            echo json_encode([
                'id'      => $nuevoCursoId,
                'mensaje' => 'Curso creado correctamente',
                'profesores_copiados' => count($profesoresIds),
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el curso: ' . $e->getMessage()]);
        }
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE curso_escolar SET nombre=:nombre, fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin WHERE id=:id"
        );
        $stmt->execute([
            ':nombre'       => trim($data['nombre']),
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin'    => $data['fecha_fin'],
            ':id'           => $id,
        ]);
        echo json_encode(['mensaje' => 'Curso actualizado']);
    }

    public function activar(int $id): void {
        $this->db->beginTransaction();
        try {
            $this->db->exec("UPDATE curso_escolar SET activo = 0");
            $stmt = $this->db->prepare("UPDATE curso_escolar SET activo = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            echo json_encode(['mensaje' => 'Curso activado correctamente']);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al activar el curso']);
        }
    }

    public function profesores(int $id): void {
        $stmt = $this->db->prepare(
            "SELECT * FROM profesor WHERE curso_id = ? ORDER BY apellidos, nombre"
        );
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll());
    }

    public function destroy(int $id): void {
        $check = $this->db->prepare("SELECT COUNT(*) FROM asignacion WHERE curso_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el curso tiene asignaciones']);
            return;
        }
        $this->db->prepare("DELETE FROM profesor_cargo WHERE curso_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM profesor WHERE curso_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM curso_escolar WHERE id = ?")->execute([$id]);
        echo json_encode(['mensaje' => 'Curso eliminado']);
    }
}

<?php
// api/controllers/CursoController.php

class CursoController {
    public function __construct(private PDO $db) {}

    // GET /cursos — lista todos los cursos
    public function index(): void {
        $stmt = $this->db->query(
            "SELECT * FROM curso_escolar ORDER BY fecha_inicio DESC"
        );
        echo json_encode($stmt->fetchAll());
    }

    // GET /cursos/{id} — ver un curso
    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM curso_escolar WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Curso no encontrado']);
            return;
        }
        echo json_encode($row);
    }

    // GET /cursos/activo — devuelve el curso activo actual
    public function activo(): void {
        $stmt = $this->db->query(
            "SELECT * FROM curso_escolar WHERE activo = 1 LIMIT 1"
        );
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'No hay ningún curso activo']);
            return;
        }
        echo json_encode($row);
    }

    // POST /cursos — crear nuevo curso
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

            // Copiar profesores seleccionados del curso anterior
            $profesoresIds = $data['profesores_ids'] ?? [];
            if (!empty($profesoresIds)) {
                $placeholders = implode(',', array_fill(0, count($profesoresIds), '?'));
                $stmt = $this->db->prepare(
                    "SELECT nombre, apellidos, puesto, horas_totales
                     FROM profesor
                     WHERE id IN ($placeholders)"
                );
                $stmt->execute($profesoresIds);
                $profesores = $stmt->fetchAll();

                $insertProf = $this->db->prepare(
                    "INSERT INTO profesor (curso_id, nombre, apellidos, puesto, horas_totales)
                     VALUES (:curso_id, :nombre, :apellidos, :puesto, :horas_totales)"
                );
                foreach ($profesores as $p) {
                    $insertProf->execute([
                        ':curso_id'      => $nuevoCursoId,
                        ':nombre'        => $p['nombre'],
                        ':apellidos'     => $p['apellidos'],
                        ':puesto'        => $p['puesto'],
                        ':horas_totales' => $p['horas_totales'],
                    ]);
                    // Las asignaciones se crean vacías (horas = 0) solo si se pide
                    // Por ahora los profesores se copian sin asignaciones
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

    // PUT /cursos/{id} — editar datos de un curso
    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE curso_escolar
             SET nombre=:nombre, fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin
             WHERE id=:id"
        );
        $stmt->execute([
            ':nombre'       => trim($data['nombre']),
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin'    => $data['fecha_fin'],
            ':id'           => $id,
        ]);
        echo json_encode(['mensaje' => 'Curso actualizado']);
    }

    // PUT /cursos/{id}/activar — cambiar el curso activo
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

    // GET /cursos/{id}/profesores — profesores de un curso concreto
    public function profesores(int $id): void {
        $stmt = $this->db->prepare(
            "SELECT * FROM profesor WHERE curso_id = ? ORDER BY apellidos, nombre"
        );
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll());
    }

    // DELETE /cursos/{id} — solo si no tiene asignaciones
    public function destroy(int $id): void {
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM asignacion WHERE curso_id = ?"
        );
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el curso tiene asignaciones']);
            return;
        }
        // Eliminar profesores del curso primero
        $this->db->prepare("DELETE FROM profesor WHERE curso_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM curso_escolar WHERE id = ?")->execute([$id]);
        echo json_encode(['mensaje' => 'Curso eliminado']);
    }
}

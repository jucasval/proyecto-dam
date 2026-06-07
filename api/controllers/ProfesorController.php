<?php
// api/controllers/ProfesorController.php

class ProfesorController {
    public function __construct(private PDO $db) {}

    private function cursoActivoId(): int {
        $stmt = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1");
        $row  = $stmt->fetch();
        if (!$row) throw new Exception('No hay ningún curso activo');
        return (int)$row['id'];
    }

    public function index(): void {
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "SELECT * FROM profesor WHERE curso_id = ? ORDER BY apellidos, nombre"
        );
        $stmt->execute([$cursoId]);
        echo json_encode($stmt->fetchAll());
    }

    // GET /profesores/horas — horas detalladas con JOINs
    public function horas(): void {
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "SELECT
                p.id,
                p.curso_id,
                c.nombre AS curso,
                CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
                p.puesto,
                p.horas_totales AS horas_contrato,
                COALESCE(SUM(a.horas), 0) AS horas_modulos,
                COALESCE(MAX(cargos.total_cargos), 0) AS horas_cargos,
                COALESCE(SUM(a.horas), 0) + COALESCE(MAX(cargos.total_cargos), 0) AS horas_asignadas,
                p.horas_totales - (COALESCE(SUM(a.horas), 0) + COALESCE(MAX(cargos.total_cargos), 0)) AS horas_libres
             FROM profesor p
             JOIN curso_escolar c ON p.curso_id = c.id
             LEFT JOIN asignacion a ON a.profesor_id = p.id AND a.curso_id = p.curso_id
             LEFT JOIN (
                SELECT profesor_id, curso_id, SUM(horas) AS total_cargos
                FROM profesor_cargo
                GROUP BY profesor_id, curso_id
             ) cargos ON cargos.profesor_id = p.id AND cargos.curso_id = p.curso_id
             WHERE p.curso_id = ?
             GROUP BY p.id
             ORDER BY p.apellidos, p.nombre"
        );
        $stmt->execute([$cursoId]);
        echo json_encode($stmt->fetchAll());
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM profesor WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Profesor no encontrado']);
            return;
        }
        echo json_encode($row);
    }

    public function store(array $data): void {
        foreach (['nombre', 'apellidos', 'puesto'] as $field) {
            if (empty($data[$field])) {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$field' es obligatorio"]);
                return;
            }
        }
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "INSERT INTO profesor (curso_id, nombre, apellidos, puesto, horas_totales)
             VALUES (:curso_id, :nombre, :apellidos, :puesto, :horas_totales)"
        );
        $stmt->execute([
            ':curso_id'      => $cursoId,
            ':nombre'        => trim($data['nombre']),
            ':apellidos'     => trim($data['apellidos']),
            ':puesto'        => $data['puesto'],
            ':horas_totales' => $data['horas_totales'] ?? 18,
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Profesor creado']);
    }

    public function update(int $id, array $data): void {
        $cursoId = $this->cursoActivoId();
        $stmt = $this->db->prepare(
            "UPDATE profesor
             SET nombre=:nombre, apellidos=:apellidos,
                 puesto=:puesto, horas_totales=:horas_totales
             WHERE id=:id AND curso_id=:curso_id"
        );
        $stmt->execute([
            ':nombre'        => trim($data['nombre']),
            ':apellidos'     => trim($data['apellidos']),
            ':puesto'        => $data['puesto'],
            ':horas_totales' => $data['horas_totales'] ?? 18,
            ':id'            => $id,
            ':curso_id'      => $cursoId,
        ]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Profesor no encontrado en el curso activo']);
            return;
        }
        echo json_encode(['mensaje' => 'Profesor actualizado']);
    }

    public function destroy(int $id): void {
        $cursoId = $this->cursoActivoId();
        
        // Verificar que el profesor pertenece al curso activo
        $check = $this->db->prepare("SELECT COUNT(*) FROM profesor WHERE id = ? AND curso_id = ?");
        $check->execute([$id, $cursoId]);
        if ($check->fetchColumn() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Profesor no encontrado en el curso activo']);
            return;
        }
        
        // Verificar que no tiene asignaciones en el curso activo
        $checkAsig = $this->db->prepare("SELECT COUNT(*) FROM asignacion WHERE profesor_id = ? AND curso_id = ?");
        $checkAsig->execute([$id, $cursoId]);
        if ($checkAsig->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el profesor tiene asignaciones en el curso activo']);
            return;
        }
        
        // Verificar que no tiene cargos en el curso activo
        $checkCargo = $this->db->prepare("SELECT COUNT(*) FROM profesor_cargo WHERE profesor_id = ? AND curso_id = ?");
        $checkCargo->execute([$id, $cursoId]);
        if ($checkCargo->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el profesor tiene cargos asignados en el curso activo']);
            return;
        }
        
        $stmt = $this->db->prepare("DELETE FROM profesor WHERE id = ? AND curso_id = ?");
        $stmt->execute([$id, $cursoId]);
        echo json_encode(['mensaje' => 'Profesor eliminado']);
    }
}

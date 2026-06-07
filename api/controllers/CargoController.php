<?php
// api/controllers/CargoController.php

class CargoController {
    public function __construct(private PDO $db) {}

    private function cursoActivoId(): int {
        $stmt = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1");
        $row  = $stmt->fetch();
        if (!$row) throw new Exception('No hay ningún curso activo');
        return (int)$row['id'];
    }

    // ---- CRUD de cargos globales -------------------------

    // GET /cargos
    public function index(): void {
        $stmt = $this->db->query("SELECT * FROM cargo ORDER BY nombre");
        echo json_encode($stmt->fetchAll());
    }

    // GET /cargos/{id}
    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM cargo WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['error' => 'Cargo no encontrado']); return; }
        echo json_encode($row);
    }

    // POST /cargos
    public function store(array $data): void {
        if (empty($data['nombre'])) {
            http_response_code(422);
            echo json_encode(['error' => "El campo 'nombre' es obligatorio"]);
            return;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO cargo (nombre, horas) VALUES (:nombre, :horas)"
        );
        $stmt->execute([
            ':nombre' => trim($data['nombre']),
            ':horas'  => $data['horas'] ?? 0,
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Cargo creado']);
    }

    // PUT /cargos/{id}
    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE cargo SET nombre=:nombre, horas=:horas WHERE id=:id"
        );
        $stmt->execute([
            ':nombre' => trim($data['nombre']),
            ':horas'  => $data['horas'] ?? 0,
            ':id'     => $id,
        ]);
        echo json_encode(['mensaje' => 'Cargo actualizado']);
    }

    // DELETE /cargos/{id}
    public function destroy(int $id): void {
        // Obtener curso activo
        $cursoStmt = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1");
        $cursoRow  = $cursoStmt->fetch();
        if (!$cursoRow) {
            http_response_code(500);
            echo json_encode(['error' => 'No hay curso activo']);
            return;
        }
        $cursoId = (int)$cursoRow['id'];
        
        // Verificar que el cargo no tiene asignaciones en el curso ACTIVO
        $check = $this->db->prepare("SELECT COUNT(*) FROM profesor_cargo WHERE cargo_id = ? AND curso_id = ?");
        $check->execute([$id, $cursoId]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el cargo tiene asignaciones en el curso activo']);
            return;
        }
        
        $stmt = $this->db->prepare("DELETE FROM cargo WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Cargo eliminado']);
    }

    // ---- Asignaciones de cargos por curso ---------------

    // GET /cargos/asignaciones — asignaciones del curso activo
    public function asignaciones(): void {
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "SELECT pc.id,
                    pc.curso_id,
                    pc.profesor_id,
                    pc.cargo_id,
                    pc.horas,
                    CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
                    p.puesto,
                    c.nombre AS cargo,
                    c.horas  AS horas_defecto
             FROM profesor_cargo pc
             JOIN profesor p ON pc.profesor_id = p.id
             JOIN cargo    c ON pc.cargo_id    = c.id
             WHERE pc.curso_id = ?
             ORDER BY p.apellidos, c.nombre"
        );
        $stmt->execute([$cursoId]);
        echo json_encode($stmt->fetchAll());
    }

    // POST /cargos/asignaciones — asignar cargo a profesor
    public function asignar(array $data): void {
        foreach (['profesor_id', 'cargo_id'] as $f) {
            if (empty($data[$f])) {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$f' es obligatorio"]);
                return;
            }
        }
        $cursoId = $this->cursoActivoId();

        // Si no se especifican horas, usar las del cargo por defecto
        if (!isset($data['horas']) || $data['horas'] === '') {
            $stmt = $this->db->prepare("SELECT horas FROM cargo WHERE id = ?");
            $stmt->execute([$data['cargo_id']]);
            $cargo = $stmt->fetch();
            $data['horas'] = $cargo ? $cargo['horas'] : 0;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO profesor_cargo (curso_id, profesor_id, cargo_id, horas)
             VALUES (:curso_id, :profesor_id, :cargo_id, :horas)"
        );
        $stmt->execute([
            ':curso_id'    => $cursoId,
            ':profesor_id' => (int)$data['profesor_id'],
            ':cargo_id'    => (int)$data['cargo_id'],
            ':horas'       => (float)$data['horas'],
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Cargo asignado']);
    }

    // PUT /cargos/asignaciones/{id} — editar horas de una asignación
    public function actualizarAsignacion(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE profesor_cargo SET profesor_id=:profesor_id, cargo_id=:cargo_id, horas=:horas WHERE id=:id"
        );
        $stmt->execute([
            ':profesor_id' => (int)$data['profesor_id'],
            ':cargo_id'    => (int)$data['cargo_id'],
            ':horas'       => (float)$data['horas'],
            ':id'          => $id,
        ]);
        echo json_encode(['mensaje' => 'Asignación de cargo actualizada']);
    }

    // DELETE /cargos/asignaciones/{id}
    public function eliminarAsignacion(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM profesor_cargo WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Asignación de cargo eliminada']);
    }
}

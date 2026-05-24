<?php
// api/controllers/ProfesorController.php
// Los profesores son históricos: cada uno pertenece a un curso_escolar

class ProfesorController {
    public function __construct(private PDO $db) {}

    // Obtiene el id del curso activo
    private function cursoActivoId(): int {
        $stmt = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1");
        $row  = $stmt->fetch();
        if (!$row) throw new Exception('No hay ningún curso activo');
        return (int)$row['id'];
    }

    // GET /profesores — lista profesores del curso activo
    public function index(): void {
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "SELECT * FROM profesor WHERE curso_id = ? ORDER BY apellidos, nombre"
        );
        $stmt->execute([$cursoId]);
        echo json_encode($stmt->fetchAll());
    }

    // GET /profesores/{id}
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

    // POST /profesores
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

    // PUT /profesores/{id}
    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE profesor
             SET nombre=:nombre, apellidos=:apellidos,
                 puesto=:puesto, horas_totales=:horas_totales
             WHERE id=:id"
        );
        $stmt->execute([
            ':nombre'        => trim($data['nombre']),
            ':apellidos'     => trim($data['apellidos']),
            ':puesto'        => $data['puesto'],
            ':horas_totales' => $data['horas_totales'] ?? 18,
            ':id'            => $id,
        ]);
        echo json_encode(['mensaje' => 'Profesor actualizado']);
    }

    // DELETE /profesores/{id}
    public function destroy(int $id): void {
        $check = $this->db->prepare("SELECT COUNT(*) FROM asignacion WHERE profesor_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el profesor tiene asignaciones activas']);
            return;
        }
        $stmt = $this->db->prepare("DELETE FROM profesor WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Profesor eliminado']);
    }
}

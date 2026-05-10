<?php
// api/controllers/GrupoController.php

class GrupoController {
    public function __construct(private PDO $db) {}

    public function index(): void {
        $stmt = $this->db->query("SELECT * FROM grupo ORDER BY ciclo, curso, nombre");
        echo json_encode($stmt->fetchAll());
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM grupo WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['error' => 'Grupo no encontrado']); return; }
        echo json_encode($row);
    }

    public function store(array $data): void {
        foreach (['nombre', 'ciclo', 'curso'] as $f) {
            if (empty($data[$f])) {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$f' es obligatorio"]);
                return;
            }
        }
        $stmt = $this->db->prepare(
            "INSERT INTO grupo (nombre, ciclo, curso, modalidad)
             VALUES (:nombre, :ciclo, :curso, :modalidad)"
        );
        $stmt->execute([
            ':nombre'    => trim($data['nombre']),
            ':ciclo'     => trim($data['ciclo']),
            ':curso'     => (int)$data['curso'],
            ':modalidad' => $data['modalidad'] ?? 'Presencial',
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Grupo creado']);
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE grupo SET nombre=:nombre, ciclo=:ciclo, curso=:curso, modalidad=:modalidad WHERE id=:id"
        );
        $stmt->execute([
            ':nombre'    => trim($data['nombre']),
            ':ciclo'     => trim($data['ciclo']),
            ':curso'     => (int)$data['curso'],
            ':modalidad' => $data['modalidad'] ?? 'Presencial',
            ':id'        => $id,
        ]);
        echo json_encode(['mensaje' => 'Grupo actualizado']);
    }

    public function destroy(int $id): void {
        $check = $this->db->prepare("SELECT COUNT(*) FROM asignacion WHERE grupo_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el grupo tiene asignaciones activas']);
            return;
        }
        $stmt = $this->db->prepare("DELETE FROM grupo WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Grupo eliminado']);
    }
}

<?php
// api/controllers/AsignacionController.php

class AsignacionController {
    public function __construct(private PDO $db) {}

    private function cursoActivoId(): int {
        $stmt = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1");
        $row  = $stmt->fetch();
        if (!$row) throw new Exception('No hay ningún curso activo');
        return (int)$row['id'];
    }

    // GET /asignaciones — usa la vista v_asignaciones_completas
    public function index(): void {
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "SELECT * FROM v_asignaciones_completas WHERE curso_id = ?"
        );
        $stmt->execute([$cursoId]);
        echo json_encode($stmt->fetchAll());
    }

    // GET /asignaciones/{id}
    public function show(int $id): void {
        $stmt = $this->db->prepare(
            "SELECT * FROM v_asignaciones_completas WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'Asignación no encontrada']);
            return;
        }
        echo json_encode($row);
    }

    // POST /asignaciones
    public function store(array $data): void {
        foreach (['profesor_id', 'modulo_id', 'grupo_id', 'horas'] as $f) {
            if (!isset($data[$f]) || $data[$f] === '') {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$f' es obligatorio"]);
                return;
            }
        }
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "INSERT INTO asignacion (curso_id, profesor_id, modulo_id, grupo_id, horas, es_desdoble, observaciones)
             VALUES (:curso_id, :profesor_id, :modulo_id, :grupo_id, :horas, :es_desdoble, :observaciones)"
        );
        $stmt->execute([
            ':curso_id'      => $cursoId,
            ':profesor_id'   => (int)$data['profesor_id'],
            ':modulo_id'     => (int)$data['modulo_id'],
            ':grupo_id'      => (int)$data['grupo_id'],
            ':horas'         => (float)$data['horas'],
            ':es_desdoble'   => $data['es_desdoble']   ? 1 : 0,
            ':observaciones' => $data['observaciones'] ?? null,
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Asignación creada']);
    }

    // PUT /asignaciones/{id}
    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE asignacion
             SET profesor_id=:profesor_id, modulo_id=:modulo_id, grupo_id=:grupo_id,
                 horas=:horas, es_desdoble=:es_desdoble, observaciones=:observaciones
             WHERE id=:id"
        );
        $stmt->execute([
            ':profesor_id'   => (int)$data['profesor_id'],
            ':modulo_id'     => (int)$data['modulo_id'],
            ':grupo_id'      => (int)$data['grupo_id'],
            ':horas'         => (float)$data['horas'],
            ':es_desdoble'   => $data['es_desdoble']   ? 1 : 0,
            ':observaciones' => $data['observaciones'] ?? null,
            ':id'            => $id,
        ]);
        echo json_encode(['mensaje' => 'Asignación actualizada']);
    }

    // DELETE /asignaciones/{id}
    public function destroy(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM asignacion WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Asignación eliminada']);
    }
}

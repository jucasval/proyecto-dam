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

    // GET /grupos/{id}/modulos — módulos predefinidos de un grupo
    public function modulos(int $id): void {
        $stmt = $this->db->prepare(
            "SELECT m.id, m.nombre, m.codigo, m.horas_pes, m.horas_ptfp
             FROM grupo_modulo gm
             JOIN modulo m ON gm.modulo_id = m.id
             WHERE gm.grupo_id = ?
             ORDER BY m.nombre"
        );
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll());
    }

    // POST /grupos/{id}/modulos — añadir módulo a un grupo
    public function addModulo(int $grupoId, array $data): void {
        if (empty($data['modulo_id'])) {
            http_response_code(422);
            echo json_encode(['error' => 'modulo_id es obligatorio']);
            return;
        }
        // Verificar que no existe ya
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM grupo_modulo WHERE grupo_id = ? AND modulo_id = ?"
        );
        $check->execute([$grupoId, (int)$data['modulo_id']]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Este módulo ya está asignado al grupo']);
            return;
        }
        $stmt = $this->db->prepare(
            "INSERT INTO grupo_modulo (grupo_id, modulo_id) VALUES (?, ?)"
        );
        $stmt->execute([$grupoId, (int)$data['modulo_id']]);
        http_response_code(201);
        echo json_encode(['mensaje' => 'Módulo añadido al grupo']);
    }

    // DELETE /grupos/{id}/modulos/{modulo_id} — quitar módulo de un grupo
    public function removeModulo(int $grupoId, int $moduloId): void {
        // Verificar que no hay asignaciones activas
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM asignacion WHERE grupo_id = ? AND modulo_id = ?"
        );
        $check->execute([$grupoId, $moduloId]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede quitar: hay asignaciones activas con este módulo en este grupo']);
            return;
        }
        $stmt = $this->db->prepare(
            "DELETE FROM grupo_modulo WHERE grupo_id = ? AND modulo_id = ?"
        );
        $stmt->execute([$grupoId, $moduloId]);
        echo json_encode(['mensaje' => 'Módulo quitado del grupo']);
    }
}

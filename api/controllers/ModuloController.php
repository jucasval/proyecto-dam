<?php
// api/controllers/ModuloController.php

class ModuloController {
    public function __construct(private PDO $db) {}

    public function index(): void {
        $stmt = $this->db->query("SELECT * FROM modulo ORDER BY nombre");
        echo json_encode($stmt->fetchAll());
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM modulo WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['error' => 'Módulo no encontrado']); return; }
        echo json_encode($row);
    }

    // GET /modulos/{id}/grupos — grupos que tienen este módulo
    public function grupos(int $id): void {
        $stmt = $this->db->prepare(
            "SELECT g.id, g.nombre, g.ciclo, g.curso
             FROM grupo_modulo gm
             JOIN grupo g ON gm.grupo_id = g.id
             WHERE gm.modulo_id = ?
             ORDER BY g.ciclo, g.curso, g.nombre"
        );
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll());
    }

    public function store(array $data): void {
        if (empty($data['nombre'])) {
            http_response_code(422);
            echo json_encode(['error' => "El campo 'nombre' es obligatorio"]);
            return;
        }
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO modulo (nombre, codigo, horas_pes, horas_ptfp)
                 VALUES (:nombre, :codigo, :horas_pes, :horas_ptfp)"
            );
            $stmt->execute([
                ':nombre'     => trim($data['nombre']),
                ':codigo'     => $data['codigo'] ?? null,
                ':horas_pes'  => $data['horas_pes']  ?? 0,
                ':horas_ptfp' => $data['horas_ptfp'] ?? 0,
            ]);
            $moduloId = $this->db->lastInsertId();

            // Asignar a grupos seleccionados
            if (!empty($data['grupos_ids'])) {
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO grupo_modulo (grupo_id, modulo_id) VALUES (?, ?)"
                );
                foreach ($data['grupos_ids'] as $grupoId) {
                    $ins->execute([(int)$grupoId, $moduloId]);
                }
            }

            $this->db->commit();
            http_response_code(201);
            echo json_encode(['id' => $moduloId, 'mensaje' => 'Módulo creado']);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el módulo']);
        }
    }

    public function update(int $id, array $data): void {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE modulo SET nombre=:nombre, codigo=:codigo,
                 horas_pes=:horas_pes, horas_ptfp=:horas_ptfp WHERE id=:id"
            );
            $stmt->execute([
                ':nombre'     => trim($data['nombre']),
                ':codigo'     => $data['codigo'] ?? null,
                ':horas_pes'  => $data['horas_pes']  ?? 0,
                ':horas_ptfp' => $data['horas_ptfp'] ?? 0,
                ':id'         => $id,
            ]);

            // Actualizar grupos: quitar los que ya no están y añadir los nuevos
            if (isset($data['grupos_ids'])) {
                // Borrar solo los que no tienen asignaciones activas
                $stmt2 = $this->db->prepare(
                    "DELETE FROM grupo_modulo
                     WHERE modulo_id = ? AND grupo_id NOT IN (
                         SELECT grupo_id FROM asignacion WHERE modulo_id = ?
                     ) AND grupo_id NOT IN (" .
                     implode(',', array_map('intval', $data['grupos_ids']) ?: [0]) .
                     ")"
                );
                $stmt2->execute([$id, $id]);

                // Añadir los nuevos
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO grupo_modulo (grupo_id, modulo_id) VALUES (?, ?)"
                );
                foreach ($data['grupos_ids'] as $grupoId) {
                    $ins->execute([(int)$grupoId, $id]);
                }
            }

            $this->db->commit();
            echo json_encode(['mensaje' => 'Módulo actualizado']);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el módulo']);
        }
    }

    public function destroy(int $id): void {
        $check = $this->db->prepare("SELECT COUNT(*) FROM asignacion WHERE modulo_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el módulo tiene asignaciones activas']);
            return;
        }
        $this->db->prepare("DELETE FROM grupo_modulo WHERE modulo_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM modulo WHERE id = ?")->execute([$id]);
        echo json_encode(['mensaje' => 'Módulo eliminado']);
    }
}

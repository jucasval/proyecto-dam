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
        $this->db->beginTransaction();
        try {
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
            $grupoId = $this->db->lastInsertId();

            // Asignar módulos seleccionados
            if (!empty($data['modulos_ids'])) {
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO grupo_modulo (grupo_id, modulo_id) VALUES (?, ?)"
                );
                foreach ($data['modulos_ids'] as $moduloId) {
                    $ins->execute([$grupoId, (int)$moduloId]);
                }
            }

            $this->db->commit();
            http_response_code(201);
            echo json_encode(['id' => $grupoId, 'mensaje' => 'Grupo creado']);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el grupo']);
        }
    }

    public function update(int $id, array $data): void {
        $this->db->beginTransaction();
        try {
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

            // Actualizar módulos si se enviaron
            if (isset($data['modulos_ids'])) {
                $modulosIds = array_map('intval', $data['modulos_ids']);

                // Quitar los que ya no están y no tienen asignaciones activas
                $actuales = $this->db->prepare(
                    "SELECT modulo_id FROM grupo_modulo WHERE grupo_id = ?"
                );
                $actuales->execute([$id]);
                $idsActuales = array_column($actuales->fetchAll(), 'modulo_id');

                foreach ($idsActuales as $midActual) {
                    if (!in_array($midActual, $modulosIds)) {
                        // Solo quitar si no tiene asignaciones activas
                        $chk = $this->db->prepare(
                            "SELECT COUNT(*) FROM asignacion WHERE grupo_id = ? AND modulo_id = ?"
                        );
                        $chk->execute([$id, $midActual]);
                        if ($chk->fetchColumn() == 0) {
                            $this->db->prepare(
                                "DELETE FROM grupo_modulo WHERE grupo_id = ? AND modulo_id = ?"
                            )->execute([$id, $midActual]);
                        }
                    }
                }

                // Añadir los nuevos
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO grupo_modulo (grupo_id, modulo_id) VALUES (?, ?)"
                );
                foreach ($modulosIds as $mid) {
                    $ins->execute([$id, $mid]);
                }
            }

            $this->db->commit();
            echo json_encode(['mensaje' => 'Grupo actualizado']);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el grupo']);
        }
    }

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
        
        // Verificar que no tiene asignaciones EN EL CURSO ACTIVO
        $check = $this->db->prepare("SELECT COUNT(*) FROM asignacion WHERE grupo_id = ? AND curso_id = ?");
        $check->execute([$id, $cursoId]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede eliminar: el grupo tiene asignaciones en el curso activo']);
            return;
        }
        $this->db->prepare("DELETE FROM grupo_modulo WHERE grupo_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM grupo WHERE id = ?")->execute([$id]);
        echo json_encode(['mensaje' => 'Grupo eliminado']);
    }

    // GET /grupos/{id}/modulos
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

    // POST /grupos/{id}/modulos
    public function addModulo(int $grupoId, array $data): void {
        if (empty($data['modulo_id'])) {
            http_response_code(422);
            echo json_encode(['error' => 'modulo_id es obligatorio']);
            return;
        }
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

    // DELETE /grupos/{id}/modulos/{modulo_id}
    public function removeModulo(int $grupoId, int $moduloId): void {
        // Obtener curso activo
        $cursoStmt = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1");
        $cursoRow  = $cursoStmt->fetch();
        if (!$cursoRow) {
            http_response_code(500);
            echo json_encode(['error' => 'No hay curso activo']);
            return;
        }
        $cursoId = (int)$cursoRow['id'];
        
        // Verificar que no tiene asignaciones EN EL CURSO ACTIVO
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM asignacion 
             WHERE grupo_id = ? AND modulo_id = ? AND curso_id = ?"
        );
        $check->execute([$grupoId, $moduloId, $cursoId]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'No se puede quitar: hay asignaciones activas con este módulo en este grupo en el curso activo']);
            return;
        }
        $stmt = $this->db->prepare(
            "DELETE FROM grupo_modulo WHERE grupo_id = ? AND modulo_id = ?"
        );
        $stmt->execute([$grupoId, $moduloId]);
        echo json_encode(['mensaje' => 'Módulo quitado del grupo']);
    }
}

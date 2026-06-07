<?php
class ModuloController {
    public function __construct(private PDO $db) {}

    public function index(): void {
        $stmt = $this->db->prepare(
            "SELECT m.* FROM modulo m
             WHERE m.curso_id = (SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1)
             ORDER BY m.nombre"
        );
        $stmt->execute();
        echo json_encode($stmt->fetchAll());
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare("SELECT * FROM modulo WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['error' => 'Módulo no encontrado']); return; }
        echo json_encode($row);
    }

    public function store(array $data): void {
        if (empty($data['nombre'])) {
            http_response_code(422);
            echo json_encode(['error' => "El campo 'nombre' es obligatorio"]);
            return;
        }
        
        // Obtener curso activo
        $cursoActivo = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1")->fetch()['id'];
        
        $stmt = $this->db->prepare(
            "INSERT INTO modulo (curso_id, nombre, codigo, horas_pes, horas_ptfp)
             VALUES (:curso_id, :nombre, :codigo, :horas_pes, :horas_ptfp)"
        );
        $stmt->execute([
            ':curso_id'    => $cursoActivo,
            ':nombre'     => trim($data['nombre']),
            ':codigo'     => $data['codigo'] ?? null,
            ':horas_pes'  => $data['horas_pes']  ?? 0,
            ':horas_ptfp' => $data['horas_ptfp'] ?? 0,
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Módulo creado']);
    }

    public function update(int $id, array $data): void {
        $this->db->beginTransaction();
        try {
            // Obtener horas actuales para comparar
            $old = $this->db->prepare("SELECT horas_pes, horas_ptfp FROM modulo WHERE id = ?");
            $old->execute([$id]);
            $oldData = $old->fetch();

            $nuevasPes  = (float)($data['horas_pes']  ?? 0);
            $nuevasPtfp = (float)($data['horas_ptfp'] ?? 0);

            // Actualizar el modulo
            $stmt = $this->db->prepare(
                "UPDATE modulo SET nombre=:nombre, codigo=:codigo,
                 horas_pes=:horas_pes, horas_ptfp=:horas_ptfp WHERE id=:id"
            );
            $stmt->execute([
                ':nombre'     => trim($data['nombre']),
                ':codigo'     => $data['codigo'] ?? null,
                ':horas_pes'  => $nuevasPes,
                ':horas_ptfp' => $nuevasPtfp,
                ':id'         => $id,
            ]);

            // Obtener ID del curso activo
            $cursoActivo = $this->db->query("SELECT id FROM curso_escolar WHERE activo = 1 LIMIT 1")->fetch()['id'];

            // Actualizar SIEMPRE todas las asignaciones de PES en el curso activo con las nuevas horas
            $this->db->prepare(
                "UPDATE asignacion a
                 JOIN profesor p ON a.profesor_id = p.id
                 SET a.horas = ?
                 WHERE a.modulo_id = ?
                 AND p.puesto = 'PES'
                 AND a.curso_id = ?"
            )->execute([$nuevasPes, $id, $cursoActivo]);

            // Actualizar SIEMPRE todas las asignaciones de PTFP en el curso activo con las nuevas horas
            $this->db->prepare(
                "UPDATE asignacion a
                 JOIN profesor p ON a.profesor_id = p.id
                 SET a.horas = ?
                 WHERE a.modulo_id = ?
                 AND p.puesto = 'PTFP'
                 AND a.curso_id = ?"
            )->execute([$nuevasPtfp, $id, $cursoActivo]);

            // Actualizar grupos si se enviaron
            if (isset($data['grupos_ids'])) {
                $modulosIds = array_map('intval', $data['grupos_ids']);
                $actuales = $this->db->prepare(
                    "SELECT grupo_id FROM grupo_modulo WHERE modulo_id = ?"
                );
                $actuales->execute([$id]);
                $idsActuales = array_column($actuales->fetchAll(), 'grupo_id');

                foreach ($idsActuales as $gid) {
                    if (!in_array($gid, $modulosIds)) {
                        $chk = $this->db->prepare(
                            "SELECT COUNT(*) FROM asignacion WHERE modulo_id = ? AND grupo_id = ?"
                        );
                        $chk->execute([$id, $gid]);
                        if ($chk->fetchColumn() == 0) {
                            $this->db->prepare(
                                "DELETE FROM grupo_modulo WHERE modulo_id = ? AND grupo_id = ?"
                            )->execute([$id, $gid]);
                        }
                    }
                }
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO grupo_modulo (grupo_id, modulo_id) VALUES (?, ?)"
                );
                foreach ($modulosIds as $gid) {
                    $ins->execute([$gid, $id]);
                }
            }

            $this->db->commit();
            echo json_encode(['mensaje' => 'Modulo actualizado']);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el modulo']);
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
        $stmt = $this->db->prepare("DELETE FROM modulo WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Módulo eliminado']);
    }
}

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

    public function index(): void {
        $cursoId = $this->cursoActivoId();
        $stmt    = $this->db->prepare(
            "SELECT a.id,
                    CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
                    p.puesto,
                    m.nombre  AS modulo,
                    m.codigo,
                    g.nombre  AS grupo,
                    g.ciclo,
                    a.horas,
                    a.es_desdoble,
                    a.observaciones,
                    a.profesor_id,
                    a.modulo_id,
                    a.grupo_id,
                    a.curso_id
             FROM asignacion a
             JOIN profesor p ON a.profesor_id = p.id
             JOIN modulo   m ON a.modulo_id   = m.id
             JOIN grupo    g ON a.grupo_id    = g.id
             WHERE a.curso_id = ?
             ORDER BY g.ciclo, g.curso, g.nombre, m.nombre"
        );
        $stmt->execute([$cursoId]);
        echo json_encode($stmt->fetchAll());
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare(
            "SELECT a.id,
                    CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
                    p.puesto,
                    m.nombre  AS modulo,
                    m.codigo,
                    g.nombre  AS grupo,
                    g.ciclo,
                    a.horas,
                    a.es_desdoble,
                    a.observaciones,
                    a.profesor_id,
                    a.modulo_id,
                    a.grupo_id,
                    a.curso_id
             FROM asignacion a
             JOIN profesor p ON a.profesor_id = p.id
             JOIN modulo   m ON a.modulo_id   = m.id
             JOIN grupo    g ON a.grupo_id    = g.id
             WHERE a.id = ?"
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

    public function store(array $data): void {
        foreach (['profesor_id', 'modulo_id', 'grupo_id', 'horas'] as $f) {
            if (!isset($data[$f]) || $data[$f] === '') {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$f' es obligatorio"]);
                return;
            }
        }
        $cursoId = $this->cursoActivoId();

        // Comprobar duplicado manualmente antes de insertar
        $check = $this->db->prepare(
            "SELECT COUNT(*) FROM asignacion
             WHERE curso_id=? AND profesor_id=? AND modulo_id=? AND grupo_id=?"
        );
        $check->execute([$cursoId, (int)$data['profesor_id'], (int)$data['modulo_id'], (int)$data['grupo_id']]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Este profesor ya tiene asignado ese módulo en ese grupo']);
            return;
        }

        $stmt = $this->db->prepare(
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

    public function update(int $id, array $data): void {
        // Comprobar duplicado excluyendo la asignación actual
        $cursoId = $this->cursoActivoId();
        $check   = $this->db->prepare(
            "SELECT COUNT(*) FROM asignacion
             WHERE curso_id=? AND profesor_id=? AND modulo_id=? AND grupo_id=? AND id!=?"
        );
        $check->execute([$cursoId, (int)$data['profesor_id'], (int)$data['modulo_id'], (int)$data['grupo_id'], $id]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Este profesor ya tiene asignado ese módulo en ese grupo']);
            return;
        }

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

    public function destroy(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM asignacion WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Asignación eliminada']);
    }
}

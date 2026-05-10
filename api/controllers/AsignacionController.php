<?php
// api/controllers/AsignacionController.php

class AsignacionController {
    public function __construct(private PDO $db) {}

    public function index(): void {
        $stmt = $this->db->query(
            "SELECT a.id,
                    CONCAT(p.apellidos, ', ', p.nombre) AS profesor,
                    p.puesto,
                    m.nombre  AS modulo,
                    m.codigo,
                    g.nombre  AS grupo,
                    g.ciclo,
                    a.horas,
                    a.tipo_cuerpo,
                    a.es_desdoble,
                    a.observaciones,
                    a.profesor_id,
                    a.modulo_id,
                    a.grupo_id
             FROM asignacion a
             JOIN profesor p ON a.profesor_id = p.id
             JOIN modulo   m ON a.modulo_id   = m.id
             JOIN grupo    g ON a.grupo_id    = g.id
             ORDER BY g.ciclo, g.curso, g.nombre, m.nombre"
        );
        echo json_encode($stmt->fetchAll());
    }

    public function show(int $id): void {
        $stmt = $this->db->prepare(
            "SELECT a.*, 
                    CONCAT(p.apellidos, ', ', p.nombre) AS profesor_nombre,
                    m.nombre AS modulo_nombre,
                    g.nombre AS grupo_nombre
             FROM asignacion a
             JOIN profesor p ON a.profesor_id = p.id
             JOIN modulo   m ON a.modulo_id   = m.id
             JOIN grupo    g ON a.grupo_id    = g.id
             WHERE a.id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { http_response_code(404); echo json_encode(['error' => 'Asignación no encontrada']); return; }
        echo json_encode($row);
    }

    public function store(array $data): void {
        foreach (['profesor_id', 'modulo_id', 'grupo_id', 'horas', 'tipo_cuerpo'] as $f) {
            if (!isset($data[$f]) || $data[$f] === '') {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$f' es obligatorio"]);
                return;
            }
        }
        $stmt = $this->db->prepare(
            "INSERT INTO asignacion (profesor_id, modulo_id, grupo_id, horas, tipo_cuerpo, es_desdoble, observaciones)
             VALUES (:profesor_id, :modulo_id, :grupo_id, :horas, :tipo_cuerpo, :es_desdoble, :observaciones)"
        );
        $stmt->execute([
            ':profesor_id'  => (int)$data['profesor_id'],
            ':modulo_id'    => (int)$data['modulo_id'],
            ':grupo_id'     => (int)$data['grupo_id'],
            ':horas'        => (float)$data['horas'],
            ':tipo_cuerpo'  => $data['tipo_cuerpo'],
            ':es_desdoble'  => $data['es_desdoble']  ? 1 : 0,
            ':observaciones'=> $data['observaciones'] ?? null,
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Asignación creada']);
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE asignacion
             SET profesor_id=:profesor_id, modulo_id=:modulo_id, grupo_id=:grupo_id,
                 horas=:horas, tipo_cuerpo=:tipo_cuerpo,
                 es_desdoble=:es_desdoble, observaciones=:observaciones
             WHERE id=:id"
        );
        $stmt->execute([
            ':profesor_id'  => (int)$data['profesor_id'],
            ':modulo_id'    => (int)$data['modulo_id'],
            ':grupo_id'     => (int)$data['grupo_id'],
            ':horas'        => (float)$data['horas'],
            ':tipo_cuerpo'  => $data['tipo_cuerpo'],
            ':es_desdoble'  => $data['es_desdoble']  ? 1 : 0,
            ':observaciones'=> $data['observaciones'] ?? null,
            ':id'           => $id,
        ]);
        echo json_encode(['mensaje' => 'Asignación actualizada']);
    }

    public function destroy(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM asignacion WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['mensaje' => 'Asignación eliminada']);
    }
}

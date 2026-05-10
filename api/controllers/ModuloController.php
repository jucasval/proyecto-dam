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

    public function store(array $data): void {
        if (empty($data['nombre'])) {
            http_response_code(422);
            echo json_encode(['error' => "El campo 'nombre' es obligatorio"]);
            return;
        }
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
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Módulo creado']);
    }

    public function update(int $id, array $data): void {
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
        echo json_encode(['mensaje' => 'Módulo actualizado']);
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

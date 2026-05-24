<?php
// api/controllers/ProfesorController.php

class ProfesorController {
    public function __construct(private PDO $db) {}

    public function index(): void {
        $stmt = $this->db->query(
            "SELECT * FROM profesor ORDER BY apellidos, nombre"
        );
        echo json_encode($stmt->fetchAll());
    }

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

    /*
        Aquí ya se usa prepare + execute en lugar de query porque la consulta lleva un dato externo (el ID). Si no encuentra el profesor devuelve un código HTTP 404, 
        que es el estándar para "no encontrado". El frontend puede detectar ese código y mostrar un mensaje adecuado.
    */


    public function store(array $data): void {
        $required = ['nombre', 'apellidos', 'puesto'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$field' es obligatorio"]);
                return;
            }
        }
        $stmt = $this->db->prepare(
            "INSERT INTO profesor (nombre, apellidos, puesto, horas_totales)
             VALUES (:nombre, :apellidos, :puesto, :horas_totales)"
        );
        $stmt->execute([
            ':nombre'        => trim($data['nombre']),
            ':apellidos'     => trim($data['apellidos']),
            ':puesto'        => $data['puesto'],
            ':horas_totales' => $data['horas_totales'] ?? 18,  //?? 18 es el operador null coalescing: si no se envía horas_totales, usa 18 por defecto
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Profesor creado']);  //lastInsertId() devuelve el ID que MySQL asignó al nuevo registro, útil para el frontend
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE profesor
             SET nombre = :nombre, apellidos = :apellidos,
                 puesto = :puesto, horas_totales = :horas_totales
             WHERE id = :id"
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

    public function destroy(int $id): void {
        // Comprobar si tiene asignaciones
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

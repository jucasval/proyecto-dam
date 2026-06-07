<?php
// api/controllers/UsuarioController.php

class UsuarioController {
    public function __construct(private PDO $db) {}

    public function index(): void {
        $stmt = $this->db->query(
            "SELECT id, username, nombre, activo, created_at FROM usuario ORDER BY nombre"
        );
        echo json_encode($stmt->fetchAll());
    }

    public function store(array $data): void {
        foreach (['username', 'password', 'nombre'] as $f) {
            if (empty($data[$f])) {
                http_response_code(422);
                echo json_encode(['error' => "El campo '$f' es obligatorio"]);
                return;
            }
        }
        // Verificar que el username no existe
        $check = $this->db->prepare("SELECT COUNT(*) FROM usuario WHERE username = ?");
        $check->execute([trim($data['username'])]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Ese nombre de usuario ya existe']);
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO usuario (username, password, nombre, activo)
             VALUES (:username, :password, :nombre, :activo)"
        );
        $stmt->execute([
            ':username' => trim($data['username']),
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':nombre'   => trim($data['nombre']),
            ':activo'   => 1,
        ]);
        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId(), 'mensaje' => 'Usuario creado']);
    }

    public function update(int $id, array $data): void {
        // Actualizar nombre y activo; password solo si se proporciona
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare(
                "UPDATE usuario SET nombre=:nombre, activo=:activo, password=:password WHERE id=:id"
            );
            $stmt->execute([
                ':nombre'   => trim($data['nombre']),
                ':activo'   => $data['activo'] ? 1 : 0,
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':id'       => $id,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE usuario SET nombre=:nombre, activo=:activo WHERE id=:id"
            );
            $stmt->execute([
                ':nombre' => trim($data['nombre']),
                ':activo' => $data['activo'] ? 1 : 0,
                ':id'     => $id,
            ]);
        }
        echo json_encode(['mensaje' => 'Usuario actualizado']);
    }

    public function destroy(int $id): void {
        // No permitir borrar si es el unico usuario activo
        $check = $this->db->query("SELECT COUNT(*) FROM usuario WHERE activo = 1");
        if ($check->fetchColumn() <= 1) {
            http_response_code(409);
            echo json_encode(['error' => 'No puedes eliminar el unico usuario activo']);
            return;
        }
        $this->db->prepare("DELETE FROM usuario WHERE id = ?")->execute([$id]);
        echo json_encode(['mensaje' => 'Usuario eliminado']);
    }
}

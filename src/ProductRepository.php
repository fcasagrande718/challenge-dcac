<?php

class ProductRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $statement = $this->db->query('SELECT * FROM productos ORDER BY id DESC');
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM productos WHERE id = :id');
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();

        return $product ?: null;
    }

    public function create(array $data): array
    {
        $statement = $this->db->prepare(
            'INSERT INTO productos (nombre, descripcion, precio) VALUES (:nombre, :descripcion, :precio)'
        );
        $statement->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'precio' => $data['precio'],
        ]);

        return $this->find((int)$this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $statement = $this->db->prepare(
            'UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'precio' => $data['precio'],
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM productos WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }
}

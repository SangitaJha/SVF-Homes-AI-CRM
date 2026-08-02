<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        return $this->db->query('SELECT * FROM ' . $this->table . ' ORDER BY ' . $orderBy)->fetchAll();
    }

    public function db(): PDO
    {
        return $this->db;
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $record = $statement->fetch();
        return $record ?: null;
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn($column) => ':' . $column, $columns);
        $sql = 'INSERT INTO ' . $this->table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')';
        $statement = $this->db->prepare($sql);
        $statement->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $pairs = [];
        foreach (array_keys($data) as $column) {
            $pairs[] = $column . ' = :' . $column;
        }
        $data['id'] = $id;
        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $pairs) . ' WHERE id = :id';
        return $this->db->prepare($sql)->execute($data);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id')->execute(['id' => $id]);
    }

    public function count(string $where = '1=1'): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM ' . $this->table . ' WHERE ' . $where)->fetchColumn();
    }

    public function search(string $column, string $term): array
    {
        $statement = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE ' . $column . ' LIKE :term ORDER BY id DESC');
        $statement->execute(['term' => '%' . $term . '%']);
        return $statement->fetchAll();
    }
}

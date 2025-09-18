<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use App\Config\Database;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    public function findBy(string $column, mixed $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    public function all(array $conditions = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        $columns = array_keys($data);
        $setClauses = array_map(fn($col) => "{$col} = ?", $columns);

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE {$this->primaryKey} = ?";
        
        $params = array_values($data);
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function hideFields(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }

        return array_diff_key($data, array_flip($this->hidden));
    }
}
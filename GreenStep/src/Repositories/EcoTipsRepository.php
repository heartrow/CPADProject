<?php

namespace App\Repositories;

use PDO;

final class EcoTipsRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Retrieve all persistent eco tips.
     */
    public function all(): array
    {
        $sql = 'SELECT id, tip_text, created_at 
                FROM eco_tips 
                ORDER BY created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a single historical eco tip structure by its primary key ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM eco_tips WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Insert a new global eco tip into the schema layout.
     * Returns the auto-incremented primary key ID.
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO eco_tips (tip_text)
                VALUES (:tip_text)';

        $this->pdo->prepare($sql)->execute([
            ':tip_text' => trim($data['tip_text'] ?? ''),
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Dynamically update selected eco tip attributes based on request payloads.
     */
    public function update(int $id, array $data): int
    {
        $sets = [];
        $args = [':id' => $id];

        foreach (['tip_text'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field=:$field";
                $args[":$field"] = match($field) {
                    'tip_text' => (string)$data[$field],
                };
            }
        }

        if (!$sets) return 0;

        $sql  = 'UPDATE eco_tips SET ' . implode(',', $sets) . ' WHERE id=:id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);

        return $stmt->rowCount();
    }

    /**
     * Delete an eco tip record completely from relational persistence.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM eco_tips WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
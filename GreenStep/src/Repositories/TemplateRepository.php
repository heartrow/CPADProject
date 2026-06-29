<?php

namespace App\Repositories;

use PDO;

final class TemplateRepository
{
    public function __construct(private PDO $pdo) {}

    public function all(?int $userId = null): array
    {
        $sql = 'SELECT templates.*, 
                       types.name AS activity_name,
                       types.category,
                       types.unit,
                       types.co2_per_unit
                FROM user_templates AS templates
                JOIN activity_types AS types ON templates.activity_type_id = types.id';

        $args = [];

        if ($userId !== null) {
            $sql .= ' WHERE templates.user_id = :user_id';
            $args[':user_id'] = $userId;
        }

        $sql .= ' ORDER BY templates.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM user_templates WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO user_templates (user_id, activity_type_id, title, description, amount)
                VALUES (:user_id, :activity_type_id, :title, :description, :amount)';

        $this->pdo->prepare($sql)->execute([
            ':user_id'          => (int)$data['user_id'],
            ':activity_type_id' => (int)$data['activity_type_id'],
            ':title'            => trim($data['title'] ?? 'Untitled'),
            ':description'      => trim($data['description'] ?? ''),
            ':amount'           => (float)$data['amount'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $sets = [];
        $args = [':id' => $id];

        foreach (['activity_type_id', 'title', 'description', 'amount'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field=:$field";
                $args[":$field"] = match($field) {
                    'amount'           => (float)$data[$field],
                    'activity_type_id' => (int)$data[$field],
                    'title', 'description' => (string)$data[$field],
                };
            }
        }

        if (!$sets) return 0;

        $sql  = 'UPDATE user_templates SET ' . implode(',', $sets) . ' WHERE id=:id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);

        return $stmt->rowCount();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM user_templates WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
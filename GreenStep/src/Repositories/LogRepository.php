<?php

namespace App\Repositories; 

use PDO; 
  
final class LogRepository 
{ 
    public function __construct(private PDO $pdo) {} 
  
    public function all(?int $userId = null, int $limit = 0): array 
    { 
        $sql = 'SELECT logs.*, 
                       types.name AS activity_name, 
                       types.category, 
                       types.unit, 
                       types.co2_per_unit 
                FROM activity_logs AS logs
                JOIN activity_types AS types ON logs.activity_type_id = types.id'; 

        $args = []; 

        if ($userId !== null) {
            $sql .= ' WHERE logs.user_id = :user_id';
            $args[':user_id'] = $userId;
        }

        $sql .= ' ORDER BY logs.created_at DESC'; 

        if ($limit > 0) $sql .= ' LIMIT ' . max(1, $limit); 

        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute($args); 
        return $stmt->fetchAll();
    } 
  
    public function find(int $id): ?array 
    { 
        $stmt = $this->pdo->prepare('SELECT * FROM activity_logs WHERE id = :id'); 
        $stmt->execute([':id' => $id]); 
        $row = $stmt->fetch(); 
        return $row === false ? null : $row; 
    }

    public function create(array $data): int { 
        // Fetch co2_per_unit from activity_types
        $stmt = $this->pdo->prepare('SELECT co2_per_unit FROM activity_types WHERE id = :id');
        $stmt->execute([':id' => (int)$data['activity_type_id']]);
        $type = $stmt->fetch();

        $co2 = $type ? (float)$type['co2_per_unit'] * (float)$data['amount'] : 0.0;

        $sql = 'INSERT INTO activity_logs (user_id, activity_type_id, title, amount, co2_emission) 
                VALUES (:user_id, :activity_type_id, :title, :amount, :co2_emission)'; 

        $this->pdo->prepare($sql)->execute([ 
            ':user_id'          => (int)$data['user_id'],  
            ':activity_type_id' => (int)$data['activity_type_id'], 
            ':title'            => trim($data['title'] ?? 'Untitled'),
            ':amount'           => (float)$data['amount'],
            ':co2_emission'     => $co2,
        ]); 

        return (int)$this->pdo->lastInsertId(); 
    }
  
    public function update(int $id, array $data): int { 
        $sets = [];  
        $args = [':id' => $id];

        // If amount or activity_type_id changed, recalculate co2
        if (array_key_exists('amount', $data) || array_key_exists('activity_type_id', $data)) {
            // Get current log to fill in missing values
            $current = $this->find($id);
            $typeId  = (int)($data['activity_type_id'] ?? $current['activity_type_id']);
            $amount  = (float)($data['amount'] ?? $current['amount']);

            $stmt = $this->pdo->prepare('SELECT co2_per_unit FROM activity_types WHERE id = :id');
            $stmt->execute([':id' => $typeId]);
            $type = $stmt->fetch();

            $data['co2_emission'] = $type ? (float)$type['co2_per_unit'] * $amount : 0.0;
        }

        foreach (['activity_type_id', 'amount', 'title', 'co2_emission'] as $field) { 
            if (array_key_exists($field, $data)) { 
                $sets[] = "$field=:$field"; 
                $args[":$field"] = match($field) {
                    'amount', 'co2_emission' => (float)$data[$field],
                    'activity_type_id'       => (int)$data[$field],
                    'title'                  => (string)$data[$field],
                };
            } 
        } 

        if (!$sets) return 0; 

        $sql  = 'UPDATE activity_logs SET ' . implode(',', $sets) . ' WHERE id=:id'; 
        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute($args); 

        return $stmt->rowCount(); 
    } 
  
    public function delete(int $id): bool 
    { 
        $stmt = $this->pdo->prepare('DELETE FROM activity_logs WHERE id = :id'); 
        $stmt->execute([':id' => $id]); 
        return $stmt->rowCount() === 1; 
    } 
}
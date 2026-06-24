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

    public function create(array $data): int 
    { 
        $sql = 'INSERT INTO activity_logs (user_id, activity_type_id, amount, title) 
                VALUES (:user_id, :activity_type_id, :amount, :title)'; 

        $this->pdo->prepare($sql)->execute([ 
            ':user_id'          => (int)$data['user_id'],  
            ':activity_type_id' => (int)$data['activity_type_id'], 
            ':amount'           => (float)$data['amount'], 
            ':title'            => trim($data['title'] ?? 'Untitled'),
        ]); 

        return (int)$this->pdo->lastInsertId(); 
    } 
  
    public function update(int $id, array $data): int 
    { 
        $sets = [];  
        $args = [':id' => $id];

        foreach (['activity_type_id', 'amount', 'title'] as $field) { 
            if (array_key_exists($field, $data)) { 
                $sets[] = "$field=:$field"; 
                $args[":$field"] = ($field === 'amount') ? (float)$data[$field] : (int)$data[$field]; 
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
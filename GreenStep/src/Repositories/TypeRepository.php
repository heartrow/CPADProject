<?php
namespace App\Repositories; 
use PDO; 
  
final class TypeRepository 
{ 
    public function __construct(private PDO $pdo) {} 
  
    public function all(string $q = '', int $limit = 0): array { 
        $sql  = 'SELECT * FROM activity_types'; 
        $args = []; 

        if ($q !== '') { 
            $sql .= ' WHERE name LIKE :q_name OR category LIKE :q_category'; 
            $args[':q_name']     = '%' . $q . '%'; 
            $args[':q_category'] = '%' . $q . '%'; 
        } 

        $sql .= ' ORDER BY id ASC'; 
        if ($limit > 0) $sql .= ' LIMIT ' . max(1, $limit); 

        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute($args); 
        return $stmt->fetchAll(); 
    }
  
    public function find(int $id): ?array { 
        $stmt = $this->pdo->prepare('SELECT * FROM activity_types WHERE id = :id'); 
        $stmt->execute([':id' => $id]); 
        $row = $stmt->fetch(); 
        return $row === false ? null : $row; 
    }

    public function create(array $data): int { 
        $sql = 'INSERT INTO activity_types (category, name, unit, co2_per_unit) 
                VALUES (:category, :name, :unit, :co2_per_unit)'; 

        $this->pdo->prepare($sql)->execute([ 
            ':category'         => trim($data['category']),  
            ':name'             => trim($data['name']), 
            ':unit'             => trim($data['unit'] ?? 'unit'), 
            ':co2_per_unit'     => (float)$data['co2_per_unit'], 
        ]); 

        return (int)$this->pdo->lastInsertId(); 
    }
  
   public function update(int $id, array $data): int 
    { 
        $sets = [];  
        $args = [':id' => $id];

        foreach (['category', 'name', 'unit', 'co2_per_unit'] as $field) { // 👈 add 'unit'
            if (array_key_exists($field, $data)) { 
                $sets[] = "$field=:$field"; 
                $args[":$field"] = $field === 'co2_per_unit' 
                    ? (float)$data[$field] 
                    : trim($data[$field]);
            } 
        }

        if (!$sets) return 0; 

        $sql  = 'UPDATE activity_types SET ' . implode(',', $sets) . ' WHERE id=:id'; 
        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute($args); 

        return $stmt->rowCount(); 
    }
  
    public function delete(int $id): bool { 
        $stmt = $this->pdo->prepare('DELETE FROM activity_types WHERE id = :id'); 
        $stmt->execute([':id' => $id]); 
        return $stmt->rowCount() === 1; 
    }
}
<?php

namespace App\Repositories; 
use PDO; 
  
final class UserRepository { 
    public function __construct(private PDO $pdo) {} 
  
    public function findByEmail(string $email): ?array { 
        $stmt = $this->pdo->prepare( 
            'SELECT id, name, email, password_hash, role, location, program, avatar, carbon_factor, created_at FROM users WHERE email = :e' 
        ); 
        $stmt->execute([':e' => mb_strtolower(trim($email))]); 
        $row = $stmt->fetch(); 
        return $row === false ? null : $row; 
    } 
    public function findById(int $id): ?array { 
        $stmt = $this->pdo->prepare('SELECT id, name, email, role, location, program, avatar, carbon_factor, created_at FROM users WHERE id = :id'); 
        $stmt->execute([':id' => $id]); 
        $row = $stmt->fetch(); 
        return $row === false ? null : $row; 
    } 
    public function create(string $n, string $e, string $hash, string $role='member'): int { 
        $this->pdo->prepare( 
            'INSERT INTO users (name,email,password_hash,role) VALUES (:n,:e,:h,:r)' 
        )->execute([':n'=>trim($n), ':e'=>mb_strtolower(trim($e)), ':h'=>$hash, ':r'=>$role]); 
        return (int)$this->pdo->lastInsertId(); 
    } 
    public function emailExists(string $e): bool { 
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :e'); 
        $stmt->execute([':e' => mb_strtolower(trim($e))]); 
        return (bool)$stmt->fetchColumn(); 
    } 
    public function updateProfile(int $id, array $data): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE users 
             SET name = :name, 
                 location = :location, 
                 program = :program, 
                 avatar = :avatar, 
                 carbon_factor = :carbon_factor 
             WHERE id = :id'
        );
        return $stmt->execute([
            ':name'          => trim($data['name'] ?? ''),
            ':location'      => trim($data['location'] ?? ''),
            ':program'       => trim($data['program'] ?? ''),
            ':avatar'        => trim($data['avatar'] ?? '👨‍💻'),
            ':carbon_factor' => trim($data['carbon_factor'] ?? 'Standard MY Baseline'),
            ':id'            => $id
        ]);
    } 
} 
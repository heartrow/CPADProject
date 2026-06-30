<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;

class ChallengeRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllWithUserStatus(int $userId): array
    {
        $sql = "
            SELECT c.*, 
                   (SELECT COUNT(*) FROM user_challenges uc WHERE uc.challenge_id = c.id AND uc.user_id = ?) as hasJoined,
                   (SELECT COALESCE(SUM(contribution), 0) FROM user_challenges uc WHERE uc.challenge_id = c.id) as currentProgress
            FROM challenges c 
            ORDER BY c.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $challenges = $stmt->fetchAll();

        foreach ($challenges as &$challenge) {
            $challenge['hasUserJoined'] = $challenge['hasJoined'] > 0;
            unset($challenge['hasJoined']);
            $challenge['currentProgress'] = (int) ($challenge['currentProgress'] ?? 0);
            $challenge['targetGoal'] = (int) $challenge['targetGoal'];
        }

        return $challenges;
    }

    public function joinChallenge(int $userId, int $challengeId): void
    {
        $stmt = $this->db->prepare('INSERT IGNORE INTO user_challenges (user_id, challenge_id) VALUES (?, ?)');
        $stmt->execute([$userId, $challengeId]);
    }

    public function leaveChallenge(int $userId, int $challengeId): void
    {
        $stmt = $this->db->prepare('DELETE FROM user_challenges WHERE user_id = ? AND challenge_id = ?');
        $stmt->execute([$userId, $challengeId]);
    }
    
    public function getLeaderboard(int $challengeId): array
    {
        $sql = "SELECT u.name, uc.contribution 
            FROM user_challenges uc
            JOIN users u ON uc.user_id = u.id 
            WHERE uc.challenge_id = ? 
            ORDER BY uc.contribution DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$challengeId]);
        return $stmt->fetchAll();
    }

    public function createChallenge(array $data): int
    {
        $sql = "INSERT INTO challenges (title, description, targetGoal, unit, activity_type_id, start_date, end_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $data['title'],
            $data['description'] ?? '', // Default to empty string if not provided
            (int) $data['targetGoal'],
            $data['unit'],
            (int) $data['activity_type_id'],
            $data['start_date'],
            $data['end_date']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function activityTypeExists(int $activityTypeId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM activity_types WHERE id = ?');
        $stmt->execute([$activityTypeId]);
        return $stmt->fetchColumn() !== false;
    }
}
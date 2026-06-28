<?php
namespace App\Repositories;

use PDO;

final class BadgeRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Return all badge definitions, flagging which ones the given user has earned.
     * Mirrors LogRepository::all() — same LEFT JOIN pattern, same fetchAll().
     */
    public function allForUser(int $userId): array
    {
        $sql = 'SELECT
                    b.id          AS badge_id,
                    b.name,
                    b.criteria_json,
                    b.image_url,
                    ub.earned_at,
                    IF(ub.badge_id IS NOT NULL, 1, 0) AS unlocked
                FROM badges b
                LEFT JOIN user_badges ub
                    ON ub.badge_id = b.id
                   AND ub.user_id  = :user_id
                ORDER BY unlocked DESC, b.id ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM badges WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Award a badge to a user. IGNORE silently skips if already awarded,
     * matching the same safe-insert approach used across the project.
     */
    public function award(int $userId, int $badgeId): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (:user_id, :badge_id)'
        );
        $stmt->execute([':user_id' => $userId, ':badge_id' => $badgeId]);
        return $stmt->rowCount() === 1;
    }

    // ── Criteria evaluation helpers ───────────────────────────────────────────

    public function countLogs(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM activity_logs WHERE user_id = :uid'
        );
        $stmt->execute([':uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function totalCo2Saved(int $userId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(al.amount * at.co2_per_unit), 0)
             FROM activity_logs al
             JOIN activity_types at ON at.id = al.activity_type_id
             WHERE al.user_id = :uid'
        );
        $stmt->execute([':uid' => $userId]);
        return (float) $stmt->fetchColumn();
    }

    public function countLogsByTypes(int $userId, array $typeIds): int
    {
        if (empty($typeIds)) return 0;

        $placeholders = implode(',', array_fill(0, count($typeIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM activity_logs
             WHERE user_id = ? AND activity_type_id IN ($placeholders)"
        );
        $stmt->execute([$userId, ...$typeIds]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Check N consecutive calendar days for a specific activity_type_id.
     * Mirrors the streak logic, kept here so the controller stays thin.
     */
    public function hasStreak(int $userId, int $activityTypeId, int $requiredDays): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT DATE(created_at) AS log_date
             FROM activity_logs
             WHERE user_id = :uid AND activity_type_id = :type_id
             ORDER BY log_date DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':uid',     $userId,          PDO::PARAM_INT);
        $stmt->bindValue(':type_id', $activityTypeId,  PDO::PARAM_INT);
        $stmt->bindValue(':lim',     $requiredDays,    PDO::PARAM_INT);
        $stmt->execute();

        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($dates) < $requiredDays) return false;

        $streak = 1;
        for ($i = 1; $i < count($dates); $i++) {
            $diff = (int) (new \DateTime($dates[$i - 1]))->diff(new \DateTime($dates[$i]))->days;
            if ($diff === 1) { $streak++; } else { break; }
        }
        return $streak >= $requiredDays;
    }

    // ── Admin CRUD ────────────────────────────────────────────────────────────

    /**
     * Create a new badge definition (admin only).
     * Returns the new badge's auto-incremented ID.
     */
    public function create(string $name, string $criteriaJson, string $imageUrl): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO badges (name, criteria_json, image_url)
             VALUES (:name, :criteria_json, :image_url)'
        );
        $stmt->execute([
            ':name'          => trim($name),
            ':criteria_json' => $criteriaJson,
            ':image_url'     => trim($imageUrl),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Delete a badge and all associated user_badges rows (cascade handles FK).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM badges WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
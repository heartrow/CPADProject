<?php
namespace App\Controllers;

use App\Repositories\BadgeRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class BadgeController
{
    public function __construct(private BadgeRepository $badges) {}

    /**
     * GET /api/badges
     * Returns all badges, flagging which ones the logged-in user has earned.
     * Mirrors LogController::index() — reads user from JWT auth attribute.
     */
    public function index(Request $r, Response $s): Response
    {
        $userId = $this->getAuthenticatedUserId($r);
        $rows   = $this->badges->allForUser($userId);

        $data = array_map(fn($row) => [
            'badge_id'    => (int) $row['badge_id'],
            'name'        => $row['name'],
            'description' => $this->buildDescription(
                                json_decode($row['criteria_json'], true) ?? []
                             ),
            'image_url'   => $row['image_url'],
            'unlocked'    => (bool) $row['unlocked'],
            'earned_at'   => $row['earned_at'],
        ], $rows);

        return $this->json($s, ['count' => count($data), 'data' => $data]);
    }

    /**
     * POST /api/badges/check
     * Evaluates all unearned badges for the logged-in user and awards any met.
     * Called automatically by LogController after every new activity log.
     * Can also be called manually from the frontend if needed.
     */
    public function check(Request $r, Response $s): Response
    {
        $userId  = $this->getAuthenticatedUserId($r);
        $awarded = $this->evaluateAndAward($userId);

        return $this->json($s, [
            'badges_awarded' => $awarded,
            'count'          => count($awarded),
        ]);
    }

    // ── Public helper — called directly from LogController ────────────────────

    /**
     * Evaluate all unearned badges and award any the user now qualifies for.
     * Returns the names of newly awarded badges.
     */
    public function evaluateAndAward(int $userId): array
    {
        // Fetch only badges this user hasn't earned yet
        $rows = $this->badges->allForUser($userId);
        $awarded = [];

        foreach ($rows as $row) {
            if ((bool) $row['unlocked']) continue;

            $criteria = json_decode($row['criteria_json'], true) ?? [];

            if ($this->meetsCriteria($userId, $criteria)) {
                $this->badges->award($userId, (int) $row['badge_id']);
                $awarded[] = $row['name'];
            }
        }

        return $awarded;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function meetsCriteria(int $userId, array $criteria): bool
    {
        return match ($criteria['type'] ?? '') {
            'total_logs' =>
                $this->badges->countLogs($userId) >= (int) ($criteria['threshold'] ?? 1),

            'total_co2_saved_kg' =>
                $this->badges->totalCo2Saved($userId) >= (float) ($criteria['threshold'] ?? 0),

            'activity_category_streak' =>
                $this->badges->hasStreak(
                    $userId,
                    (int)   ($criteria['activity_type_id'] ?? 0),
                    (int)   ($criteria['days']             ?? 7)
                ),

            'activity_category_logs' =>
                $this->badges->countLogsByTypes(
                    $userId,
                    array_map('intval', $criteria['activity_type_ids'] ?? [])
                ) >= (int) ($criteria['threshold'] ?? 1),

            default => false,
        };
    }

    /**
     * Turn criteria_json into a human-readable badge description.
     * Same approach as TypeController's validate() — keep logic in the controller.
     */
    private function buildDescription(array $criteria): string
    {
        return match ($criteria['type'] ?? '') {
            'total_logs' =>
                'Log your first eco-activity.',

            'total_co2_saved_kg' =>
                'Save a total of ' . ($criteria['threshold'] ?? 0) . ' kg of CO₂ through your activities.',

            'activity_category_streak' =>
                'Log ' . ucfirst($criteria['category'] ?? 'meal') . ' activities for '
                . ($criteria['days'] ?? 7) . ' days in a row.',

            'activity_category_logs' =>
                'Log public transport ' . ($criteria['threshold'] ?? 0) . ' times.',

            default =>
                'Complete the required eco-actions to unlock this badge.',
        };
    }

    // Mirrors LogController — reads 'sub' from the JWT payload set by AuthMiddleware
    private function getAuthenticatedUserId(Request $r): int
    {
        $auth = (array) $r->getAttribute('auth', []);
        return (int) ($auth['sub'] ?? 0);
    }

    private function json(Response $r, $data, int $code = 200): Response
    {
        $r->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $r->withHeader('Content-Type', 'application/json')->withStatus($code);
    }
}
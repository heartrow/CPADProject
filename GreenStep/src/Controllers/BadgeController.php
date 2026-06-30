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
            'icon'        => $row ['icon'],
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

    /**
     * POST /api/badges — admin only
     * Creates a new badge definition from the request body.
     * Body: { name, criteria_type, threshold?, activity_type_id?, days?,
     *          activity_type_ids?, image_url? }
     */
    public function store(Request $r, Response $s): Response
    {
        $auth = (array) $r->getAttribute('auth', []);
        if (($auth['role'] ?? 'member') !== 'admin') {
            return $this->json($s, ['error' => 'Admins only'], 403);
        }

        $body = (array) $r->getParsedBody();

        // Validate required fields
        $name          = trim($body['name'] ?? '');
        $criteriaType  = trim($body['criteria_type'] ?? '');
        $imageUrl      = trim($body['image_url'] ?? 'https://greenstep.app/badges/default.png');
        $icon          = trim($body['icon'] ?? '🏅');

        // 'description' is accepted in the request body for compatibility with
        // clients that send it, but it is intentionally NOT persisted. Badge
        // descriptions are always derived from criteria_json via buildDescription()
        // so the text shown to users can never drift from the badge's actual rules.
        // Any value submitted here is silently discarded.

        $errors = [];
        if (mb_strlen($name) < 2)        $errors['name']          = 'min 2 chars';
        if ($criteriaType === '')         $errors['criteria_type'] = 'required';

        $allowedTypes = ['total_logs', 'total_co2_saved_kg', 'activity_category_streak', 'activity_category_logs'];
        if ($criteriaType !== '' && !in_array($criteriaType, $allowedTypes, true)) {
            $errors['criteria_type'] = 'invalid type';
        }

        if ($errors) {
            return $this->json($s, ['errors' => $errors], 400);
        }

        // Build criteria_json from the selected type
        $criteria = ['type' => $criteriaType];

        switch ($criteriaType) {
            case 'total_logs':
                $criteria['threshold'] = max(1, (int) ($body['threshold'] ?? 1));
                break;

            case 'total_co2_saved_kg':
                $threshold = (float) ($body['threshold'] ?? 0);
                if ($threshold <= 0) {
                    return $this->json($s, ['errors' => ['threshold' => 'must be > 0']], 400);
                }
                $criteria['threshold'] = $threshold;
                break;

            case 'activity_category_streak':
                $typeId = (int) ($body['activity_type_id'] ?? 0);
                $days   = (int) ($body['days'] ?? 7);
                if ($typeId <= 0) {
                    return $this->json($s, ['errors' => ['activity_type_id' => 'required for streak badge']], 400);
                }
                $criteria['activity_type_id'] = $typeId;
                $criteria['days']             = max(1, $days);
                $criteria['category']         = trim($body['category'] ?? 'activity');
                break;

            case 'activity_category_logs':
                $typeIds   = array_map('intval', (array) ($body['activity_type_ids'] ?? []));
                $threshold = (int) ($body['threshold'] ?? 1);
                if (empty($typeIds)) {
                    return $this->json($s, ['errors' => ['activity_type_ids' => 'at least one required']], 400);
                }
                $criteria['activity_type_ids'] = $typeIds;
                $criteria['threshold']         = max(1, $threshold);
                break;
        }

        $criteriaJson = json_encode($criteria, JSON_UNESCAPED_UNICODE);
        $newId        = $this->badges->create($name, $criteriaJson, $imageUrl, $icon);
        $newBadge     = $this->badges->find($newId);

        return $this->json($s, [
            'message' => 'Badge created',
            'data'    => $newBadge,
        ], 201);
    }

    /**
     * DELETE /api/badges/{id} — admin only
     * Permanently removes a badge definition and all earned records for it.
     */
    public function destroy(Request $r, Response $s, array $args): Response
    {
        $auth = (array) $r->getAttribute('auth', []);
        if (($auth['role'] ?? 'member') !== 'admin') {
            return $this->json($s, ['error' => 'Admins only'], 403);
        }

        $id    = (int) ($args['id'] ?? 0);
        $badge = $this->badges->find($id);

        if (!$badge) {
            return $this->json($s, ['error' => "Badge {$id} not found"], 404);
        }

        $this->badges->delete($id);
        return $this->json($s, ['message' => 'Badge deleted', 'data' => $badge]);
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
                'Log ' . ($criteria['threshold'] ?? 0) . ' eco-activit' . (($criteria['threshold'] ?? 0) > 1 ? 'ies' : 'y'). '.',

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
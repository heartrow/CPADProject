<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ChallengeRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ChallengeController {
    private ChallengeRepository $repo;

    public function __construct(ChallengeRepository $repo) {
        $this->repo = $repo;
    }

    public function index(Request $request, Response $response): Response {
        $tokenPayload = $request->getAttribute('auth'); 

        if (!isset($tokenPayload['id'])) {
            $response->getBody()->write(json_encode(['error' => 'User ID missing from token payload.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        $userId = (int) $tokenPayload['id'];

        $challenges = $this->repo->getAllWithUserStatus($userId);
        
        $response->getBody()->write(json_encode($challenges));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function join(Request $request, Response $response): Response {
        $tokenPayload = $request->getAttribute('auth');

        if (!isset($tokenPayload['id'])) {
            $response->getBody()->write(json_encode(['error' => 'User ID missing from token payload.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        $userId = (int) $tokenPayload['id'];

        $data = json_decode((string)$request->getBody(), true);
        $challengeId = (int) ($data['challenge_id'] ?? 0);

        if ($challengeId === 0) {
            $response->getBody()->write(json_encode(['error' => 'Missing challenge_id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $this->repo->joinChallenge($userId, $challengeId);
        
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function leave(Request $request, Response $response): Response {
        $tokenPayload = $request->getAttribute('auth');

        if (!isset($tokenPayload['id'])) {
            $response->getBody()->write(json_encode(['error' => 'User ID missing from token payload.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        $userId = (int) $tokenPayload['id'];
        
        $data = json_decode((string)$request->getBody(), true);
        $challengeId = (int) ($data['challenge_id'] ?? 0);

        if ($challengeId === 0) {
            $response->getBody()->write(json_encode(['error' => 'Missing challenge_id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $this->repo->leaveChallenge($userId, $challengeId);
        
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function leaderboard(Request $request, Response $response, array $args): Response {
        $tokenPayload = $request->getAttribute('auth');

        if (!isset($tokenPayload['id'])) {
            $response->getBody()->write(json_encode(['error' => 'User ID missing from token payload.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $challengeId = (int) ($args['id'] ?? 0);

        if ($challengeId === 0) {
            $response->getBody()->write(json_encode(['error' => 'Missing or invalid challenge id']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $rows = $this->repo->getLeaderboard($challengeId);

        $leaderboard = [];
        $rank = 0;
        foreach ($rows as $row) {
            $rank++;
            $leaderboard[] = [
                'rank' => $rank,
                'name' => $row['name'],
                'contribution' => (int) $row['contribution'],
            ];
        }

        $response->getBody()->write(json_encode($leaderboard));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

public function create(Request $request, Response $response): Response 
    {
        $tokenPayload = (array) $request->getAttribute('auth');
        
        $role = $tokenPayload['role'] ?? 'user'; 
        $userId = (int) ($tokenPayload['id'] ?? 1);

        
        if ($role !== 'admin' && $role !== 'leader') {
            $response->getBody()->write(json_encode([
                'error' => 'Forbidden: Only admins and community leaders can create challenges.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // 3. Parse the incoming JSON data
        $data = json_decode((string)$request->getBody(), true);

        // 4. Basic Validation
        if (empty($data['title']) || empty($data['targetGoal']) || empty($data['unit'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Missing required fields: title, targetGoal, and unit are required.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (empty($data['activity_type_id']) || empty($data['start_date']) || empty($data['end_date'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Missing required fields: activity_type_id, start_date, and end_date are required.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!$this->repo->activityTypeExists((int) $data['activity_type_id'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid activity_type_id: no matching activity type exists.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!empty($data['start_date']) && !empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
            $response->getBody()->write(json_encode([
                'error' => 'end_date cannot be before start_date.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 5. Execute via Repository
        try {
            $newChallengeId = $this->repo->createChallenge($data);
            
            $response->getBody()->write(json_encode([
                'success' => true, 
                'id' => $newChallengeId,
                'message' => 'Challenge created successfully by ' . ($tokenPayload['email'] ?? 'Admin')
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Failed to create challenge: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
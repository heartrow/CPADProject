<?php

namespace App\Controllers; 

use App\Repositories\LogRepository; 
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request; 
  
final class LogController 
{ 
    public function __construct(
        private LogRepository $logs,
        private ?BadgeController $badgeCtrl = null
    ) {} 
  
    public function index(Request $r, Response $s): Response 
    { 
        $p = $r->getQueryParams(); 
        $userId = $this->getAuthenticatedUserId($r);

        // Securely pull only the logged-in user's logs
        $rows = $this->logs->all($userId, (int)($p['limit'] ?? 0)); 

        return $this->json($s, ['count' => count($rows), 'data' => $rows]); 
    } 

    public function show(Request $r, Response $s, array $a): Response 
    { 
        $userId = $this->getAuthenticatedUserId($r);
        $log = $this->logs->find((int)$a['id']); 

        // Security check: Does this log exist AND does it belong to this user?
        if (!$log || (int)$log['user_id'] !== $userId) {
            return $this->json($s, ['error' => 'Log not found or unauthorized'], 404);
        }

        return $this->json($s, $log); 
    } 

    public function create(Request $r, Response $s): Response 
    { 
        $body = (array)$r->getParsedBody(); 
        $errors = $this->validate($body, true); 
 
        if ($errors) return $this->json($s, ['errors' => $errors], 400); 
 
        $userId = $this->getAuthenticatedUserId($r);
        $body['user_id'] = $userId;
 
        $id = $this->logs->create($body); 
        $newLog = $this->logs->find($id);
 
        $awarded = [];
        if ($this->badgeCtrl) {
            $awarded = $this->badgeCtrl->evaluateAndAward($userId);
        }
 
        return $this->json($s, [
            'message' => 'Activity logged',
            'data' => $newLog,
            'badges_awarded' => $awarded,
        ], 201) 
        ->withHeader('Location', '/api/activitylogs/' . $id); 
    }

    public function update(Request $req, Response $res, array $args): Response 
    { 
        $id = (int)($args['id'] ?? 0); 
        $userId = $this->getAuthenticatedUserId($req);
        
        $existing = $this->logs->find($id);
 
        // Security check: Ensure they own the log before editing
        if (!$existing || (int)$existing['user_id'] !== $userId) {
            return $this->json($res, ['error' => "Activity log {$id} not found"], 404); 
        }
   
        $body = (array)($req->getParsedBody() ?? []); 
        $errors = $this->validate($body, false); 
 
        if (!empty($errors)) {
            return $this->json($res, ['errors' => $errors], 400); 
        }
   
        $this->logs->update($id, $body);  
        $updatedLog = $this->logs->find($id);
        
        $awarded = [];
        if ($this->badgeCtrl) {
            $awarded = $this->badgeCtrl->evaluateAndAward($userId);
        }
        
        return $this->json($res, [
            'message' => 'Activity updated',
            'data' => $updatedLog,
            'badges_awarded' => $awarded,
        ]); 
    } 

    public function delete(Request $req, Response $res, array $args): Response 
    { 
        $id = (int)($args['id'] ?? 0); 
        $userId = $this->getAuthenticatedUserId($req);

        $existing = $this->logs->find($id);

        // Security check: Only the owner can delete their own activity
        if (!$existing || (int)$existing['user_id'] !== $userId) {
            return $this->json($res, ['error' => "Activity log {$id} not found"], 404); 
        }
        
        $this->logs->delete($id);  
        
        return $this->json($res, ['message' => 'Activity log deleted', 'data' => $existing]); 
    }

    private function validate(array $body, bool $requireAll): array 
    { 
        $errors = []; 

        $rules = [ 
            'activity_type_id' => fn($v) => is_numeric($v) && (int)$v > 0, 
            'title'            => fn($v) => is_string($v) && trim($v) !== '',
            'amount'           => fn($v) => is_numeric($v) && (float)$v > 0, 
        ]; 

        foreach ($rules as $f => $check) { 
            if ($requireAll && !array_key_exists($f, $body)) { 
                $errors[$f] = "$f is required"; 
                continue; 
            } 
            if (array_key_exists($f, $body) && !$check($body[$f])) {
                $errors[$f] = "$f is invalid"; 
            }
        } 

        return $errors; 
    }

    // Helper to extract the logged-in User ID from your Auth Middleware / Session
    private function getAuthenticatedUserId(Request $r): int
    {
        $auth = (array)$r->getAttribute('auth', []);
        return (int)($auth['sub'] ?? $auth['id'] ?? 1); // Fallback to User #1 for local development
    }
    
    private function json(Response $r, $data, int $code = 200): Response 
    { 
        $r->getBody()->write(json_encode($data, JSON_PRETTY_PRINT)); 
        return $r->withHeader('Content-Type', 'application/json')->withStatus($code); 
    } 
}
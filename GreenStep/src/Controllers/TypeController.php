<?php
namespace App\Controllers; 
use App\Repositories\TypeRepository; 
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request; 
  
final class TypeController { 
    public function __construct(private TypeRepository $types) {} 
  
    public function index(Request $r, Response $s): Response { 
        $p   = $r->getQueryParams(); 
        $rows = $this->types->all(
            (string)($p['q'] ?? ''), 
            (int)($p['limit'] ?? 0)
        ); 
        return $this->json($s, ['count'=>count($rows), 'data'=>$rows]); 
    } 
    public function show(Request $r, Response $s, array $a): Response { 
        $type = $this->types->find((int)$a['id']); 
        return $type ? $this->json($s, $type) 
                     : $this->json($s, ['error'=>'not found'], 404); 
    } 
    public function create(Request $r, Response $s): Response { 
        $body = (array)$r->getParsedBody(); 
        $errors = $this->validate($body, true); 
        if ($errors) return $this->json($s, ['errors'=>$errors], 400); 
        $id = $this->types->create($body); 
        return $this->json($s, ['message'=>'Activity Type created', 'data'=>$this->types->find($id)], 201) 
                    ->withHeader('Location', '/api/activitytypes/' . $id); 
    }


    public function update(Request $req, Response $res, array $args): Response { 
        $id = (int)($args['id'] ?? 0); 
        
        // 1. Check if it exists using the Repository
        $existingType = $this->types->find($id);
        if (!$existingType) {
            return $this->json($res, ['error' => "Activity type {$id} not found"], 404); 
        }
  
        $body = (array)($req->getParsedBody() ?? []); 
        $errors = $this->validate($body, false); // requireAll: false for partial updates
        if (!empty($errors)) {
            return $this->json($res, ['errors' => $errors], 400); 
        }
  
        // 2. Pass the validated data to the Repository to update MySQL
        // (Assuming your repository has an update method that takes the ID and the data array)
        $this->types->update($id, $body);  
        
        // 3. Fetch the fresh data to return to the user
        $updatedType = $this->types->find($id);
        
        return $this->json($res, ['message' => 'Activity type updated', 'data' => $updatedType]); 
    } 

    public function delete(Request $req, Response $res, array $args): Response { 
        $auth = (array)$req->getAttribute('auth', []); 
        if (($auth['role'] ?? 'member') !== 'admin') { 
            return $this->json($res, ['error' => 'Admins only'], 403); 
        } 

        $id = (int)($args['id'] ?? 0); 
        
        // 1. Fetch it first so we can return the deleted data in the response
        $deletedType = $this->types->find($id);
        if (!$deletedType) {
            return $this->json($res, ['error' => "Activity type {$id} not found"], 404); 
        }
        
        // 2. Tell the Repository to delete it from MySQL
        $this->types->delete($id);  
        
        return $this->json($res, ['message' => 'Activity type deleted', 'data' => $deletedType]); 
    }

    private function validate(array $body, bool $requireAll): array { 
        $errors = []; 
        $allowedCategories = ['transport', 'meal', 'energy', 'recycle'];

        $rules = [ 
            'category'     => fn($v) => is_string($v) && in_array(strtolower(trim($v)), $allowedCategories, true), 
            'name'         => fn($v) => is_string($v) && trim($v) !== '', 
            'unit'         => fn($v) => is_string($v) && trim($v) !== '', 
            'co2_per_unit' => fn($v) => is_numeric($v), 
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
    
    private function json(Response $r, $data, int $code=200): Response { 
        $r->getBody()->write(json_encode($data, JSON_PRETTY_PRINT)); 
        return $r->withHeader('Content-Type','application/json')->withStatus($code); 
    } 
} 



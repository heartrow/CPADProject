<?php
namespace App\Controllers; 
use App\Repositories\TypeRepository; 
use App\Validation\Validator;
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request; 
  
final class TypeController { 
    public function __construct(private TypeRepository $types) {} 

    private const ALLOWED_CATEGORIES = ['transport', 'meal', 'energy', 'recycle'];
  
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
        if ($guard = $this->requireAdmin($r, $s)) return $guard;

        $body = (array)$r->getParsedBody();

        $errors = (new Validator())
            ->required('category', 'name', 'unit', 'co2_per_unit')
            ->field('category',     fn($v) => is_string($v) && in_array(strtolower(trim($v)), self::ALLOWED_CATEGORIES, true), 'category must be one of: transport, meal, energy, recycle')
            ->field('name',         Validator::nonEmptyString(150),                  'name must be 1-150 chars')
            ->field('unit',         Validator::nonEmptyString(50),                   'unit must be 1-50 chars')
            ->field('co2_per_unit', fn($v) => is_numeric($v) && (float)$v >= 0,     'co2_per_unit must be a non-negative number')
            ->validate($body);

        if ($errors) return $this->json($s, ['errors'=>$errors], 400); 

        $id = $this->types->create($body); 
        return $this->json($s, ['message'=>'Activity Type created', 'data'=>$this->types->find($id)], 201) 
                    ->withHeader('Location', '/api/activitytypes/' . $id); 
    }

    public function update(Request $req, Response $res, array $args): Response { 
        if ($guard = $this->requireAdmin($req, $res)) return $guard;

        $id = (int)($args['id'] ?? 0); 
        
        $existingType = $this->types->find($id);
        if (!$existingType) {
            return $this->json($res, ['error' => "Activity type {$id} not found"], 404); 
        }
  
        $body = (array)($req->getParsedBody() ?? []);

        $errors = (new Validator())
            ->field('category',     fn($v) => is_string($v) && in_array(strtolower(trim($v)), self::ALLOWED_CATEGORIES, true), 'category must be one of: transport, meal, energy, recycle')
            ->field('name',         Validator::nonEmptyString(150),                  'name must be 1-150 chars')
            ->field('unit',         Validator::nonEmptyString(50),                   'unit must be 1-50 chars')
            ->field('co2_per_unit', fn($v) => is_numeric($v) && (float)$v >= 0,     'co2_per_unit must be a non-negative number')
            ->validate($body, true); 

        if (!empty($errors)) {
            return $this->json($res, ['errors' => $errors], 400); 
        }

        
  
        $this->types->update($id, $body);  
        $updatedType = $this->types->find($id);
        
        return $this->json($res, ['message' => 'Activity type updated', 'data' => $updatedType]); 
    } 

    public function delete(Request $req, Response $res, array $args): Response { 
        if ($guard = $this->requireAdmin($req, $res)) return $guard;
        
        $id = (int)($args['id'] ?? 0); 
        
        $deletedType = $this->types->find($id);
        if (!$deletedType) {
            return $this->json($res, ['error' => "Activity type {$id} not found"], 404); 
        }
        
        $this->types->delete($id);  
        
        return $this->json($res, ['message' => 'Activity type deleted', 'data' => $deletedType]); 
    }

    private function requireAdmin(Request $r, Response $s): ?Response {
        $auth = (array)$r->getAttribute('auth', []);
        if (($auth['role'] ?? 'member') !== 'admin') {
            return $this->json($s, ['error' => 'Admins only'], 403);
        }
        return null;
    }
    
    private function json(Response $r, $data, int $status = 200): Response { 
        $r->getBody()->write(json_encode( 
            $data, 
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE 
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT 
        )); 
        return $r->withHeader('Content-Type','application/json; charset=utf-8') 
                ->withStatus($status); 
    } 
} 



<?php 
namespace App\Controllers; 
use App\Auth\JwtService; 
use App\Repositories\UserRepository;
use App\Validation\Validator;
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request; 
  
final class AuthController { 
    public function __construct(private UserRepository $users, private JwtService $jwt) {} 
  
    public function register(Request $r, Response $s): Response { 
        $b = (array)$r->getParsedBody();

        $errors = (new Validator())
            ->required('name', 'email', 'password')
            ->field('name',     Validator::nonEmptyString(150),  'name must be 2-150 chars')
            ->field('email',    Validator::email(),              'invalid email')
            ->field('password', fn($v) => is_string($v) && mb_strlen($v) >= 6, 'password must be at least 6 chars')
            ->validate($b);

        if ($errors) return $this->json($s, ['errors' => $errors], 400); 

        if ($this->users->emailExists($b['email'])) 
            return $this->json($s, ['error' => 'Email already registered'], 409); 

        $id = $this->users->create(
            $b['name'], 
            $b['email'], 
            password_hash($b['password'], PASSWORD_DEFAULT)
        ); 

        return $this->json($s, ['message' => 'Registered', 'user' => $this->users->findById($id)], 201); 
    } 
  
    public function login(Request $r, Response $s): Response {    
        $b = (array)$r->getParsedBody();

        $errors = (new Validator())
            ->required('email', 'password')
            ->field('email',    Validator::email(),     'invalid email')
            ->field('password', fn($v) => is_string($v) && $v !== '', 'password is required')
            ->validate($b);

        if ($errors) return $this->json($s, ['errors' => $errors], 400);

        $u = $this->users->findByEmail($b['email']);
        if (!$u || !password_verify($b['password'], $u['password_hash']))
            return $this->json($s, ['error' => 'Invalid credentials'], 401);

        $token = $this->jwt->issue((int)$u['id'], ['role' => $u['role'], 'email' => $u['email']]);

        return $this->json($s, [
            'token_type'   => 'Bearer',
            'expires_in'   => $this->jwt->ttl(),
            'access_token' => $token,
            'user'         => [
                'id'            => $u['id'],
                'name'          => $u['name'],
                'role'          => $u['role'],
                'email'         => $u['email'],
                'location'      => $u['location'] ?? 'Johor, Malaysia',
                'program'       => $u['program'] ?? 'Software Engineering Student',
                'avatar'        => $u['avatar'] ?? '👨‍💻',
                'carbon_factor' => $u['carbon_factor'] ?? 'Standard MY Baseline',
                'created_at'    => $u['created_at'] ?? null,
            ],
        ]);
    }
  
    public function me(Request $r, Response $s): Response { 
        $auth = (array)$r->getAttribute('auth', []); 
        $u = $this->users->findById((int)($auth['id'] ?? 0)); 
        return $u ? $this->json($s, $u) 
                  : $this->json($s, ['error' => 'Not found'], 404); 
    } 
    
    public function updateProfile(Request $r, Response $s): Response {
        $auth = (array)$r->getAttribute('auth', []);
        $userId = (int)($auth['sub'] ?? 0);
        
        $body = (array)$r->getParsedBody();
        
        $errors = [];
        if (empty($body['name']) || mb_strlen(trim($body['name'])) < 2) {
            $errors['name'] = 'min 2 chars';
        }
        
        if ($errors) {
            return $this->json($s, ['errors' => $errors], 400);
        }
        
        $success = $this->users->updateProfile($userId, [
            'name'          => $body['name'] ?? '',
            'location'      => $body['location'] ?? 'Johor, Malaysia',
            'program'       => $body['program'] ?? 'Software Engineering Student',
            'avatar'        => $body['avatar'] ?? '👨‍💻',
            'carbon_factor' => $body['carbon_factor'] ?? 'Standard MY Baseline',
        ]);
        
        if (!$success) {
            return $this->json($s, ['error' => 'Failed to update profile'], 500);
        }
        
        $updatedUser = $this->users->findById($userId);
        return $this->json($s, [
            'message' => 'Profile updated successfully',
            'user'    => $updatedUser
        ]);
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
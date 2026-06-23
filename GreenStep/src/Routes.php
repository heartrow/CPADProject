<?php
declare(strict_types=1);

use App\Auth\JwtService;
use App\Controllers\AuthController;
use App\Database;
use App\Middlewares\AuthMiddleware;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app): void {

    $pdo  = Database::get();
    $jwt  = new JwtService();

    $authCtrl = new AuthController(new UserRepository($pdo), $jwt);
    $auth     = new AuthMiddleware($jwt);

    // Public — no token required.
    $app->get('/', function (Request $r, Response $s) {
        $s->getBody()->write(json_encode([
            'name'    => 'GreenStep REST API',
            'version' => '3.0.0 (JWT auth)',
            'endpoints' => [
                'public' => [
                    'POST /auth/register',
                    'POST /auth/login',
                ],
                'protected' => [
                    'GET    /auth/me',
                    'GET    /api/activitylogs',
                    'GET    /api/activitylog/{id}',
                    'POST   /api/books',
                    'PUT    /api/books/{id}',
                    'DELETE /api/books/{id}   (admin only)',
                ],
            ],
        ]));
        return $s->withHeader('Content-Type', 'application/json');
    });

    // -- Auth routes -------------------------------------------------
    $app->post('/auth/register', [$authCtrl, 'register']);
    $app->post('/auth/login',    [$authCtrl, 'login']);

    // /auth/me requires a valid JWT.
    $app->get('/auth/me', [$authCtrl, 'me'])->add($auth);

    // CORS pre-flight catch-all.
    $app->options('/{routes:.+}', fn(Request $r, Response $s) => $s);
};

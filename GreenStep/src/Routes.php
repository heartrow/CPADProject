<?php
declare(strict_types=1);

use App\Auth\JwtService;
use App\Controllers\AuthController;
use App\Controllers\TypeController;
use App\Controllers\LogController;
use App\Database;
use App\Middlewares\AuthMiddleware;
use App\Repositories\TypeRepository;
use App\Repositories\UserRepository;
use App\Repositories\LogRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app): void {

    $pdo  = Database::get();
    $jwt  = new JwtService();
    $auth = new AuthMiddleware($jwt);

    $authCtrl = new AuthController(new UserRepository($pdo), $jwt);
    $typeCtrl = new TypeController(new TypeRepository($pdo));
    $logCtrl = new LogController(new LogRepository($pdo));
    

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
                    'GET    /api/activitylogs/{id}',

                    'GET    /api/activitytypes',
                    'GET    /api/activitytypes/{id}',
                    'POST   /api/activitytypes',
                    'PUT    /api/activitytypes/{id}',
                    'DELETE /api/activitytypes/{id}   (admin only)',
                ],
            ],
        ]));
        return $s->withHeader('Content-Type', 'application/json');
    });

    // -- Auth routes -------------------------------------------------
    $app->post('/auth/register', [$authCtrl, 'register']);
    $app->post('/auth/login',    [$authCtrl, 'login']);

    // -- Activity Log routes -------------------------------------------------
    
    $app->group('/api/activitylogs', function ($g) use ($logCtrl) {
        $g->get     ('',        [$logCtrl, 'index']);
        $g->get     ('/{id}',   [$logCtrl, 'show']);
        $g->post    ('',        [$logCtrl, 'create']);
        $g->put     ('/{id}',   [$logCtrl, 'update']);
        $g->delete  ('/{id}',   [$logCtrl, 'delete']);   // controller also enforces role=admin
    })->add($auth);

    // -- Activity Type routes -------------------------------------------------
    $app->group('/api/activitytypes', function ($g) use ($typeCtrl) {
        $g->get     ('',        [$typeCtrl, 'index']);
        $g->get     ('/{id}',   [$typeCtrl, 'show']);
        $g->post    ('',        [$typeCtrl, 'create']);
        $g->put     ('/{id}',   [$typeCtrl, 'update']);
        $g->delete  ('/{id}',   [$typeCtrl, 'delete']);   // controller also enforces role=admin
    })->add($auth);

    // /auth/me requires a valid JWT.
    $app->get('/auth/me', [$authCtrl, 'me'])->add($auth);

    // CORS pre-flight catch-all.
    $app->options('/{routes:.+}', fn(Request $r, Response $s) => $s);
};

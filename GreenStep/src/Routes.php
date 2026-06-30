<?php
declare(strict_types=1);

use App\Auth\JwtService;
use App\Controllers\AuthController;
use App\Controllers\BadgeController;
use App\Controllers\TemplateController;
use App\Controllers\TypeController;
use App\Controllers\LogController;
use App\Controllers\ChallengeController;
use App\Database;
use App\Middlewares\AuthMiddleware;
use App\Repositories\BadgeRepository;
use App\Middlewares\RateLimit;
use App\Repositories\TypeRepository;
use App\Repositories\UserRepository;
use App\Repositories\LogRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\ChallengeRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;

return function (App $app): void {

    $app->add(function (Request $request, RequestHandler $handler) {
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*') // Allows your Vue app to connect
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });

    $pdo  = Database::get();
    $jwt  = new JwtService();
    $auth = new AuthMiddleware($jwt);

    $authCtrl  = new AuthController(new UserRepository($pdo), $jwt);
    $typeCtrl  = new TypeController(new TypeRepository($pdo));
    $badgeCtrl = new BadgeController(new BadgeRepository($pdo));
    $challengeRepo = new ChallengeRepository($pdo);
    $logCtrl   = new LogController(new LogRepository($pdo), $badgeCtrl, $challengeRepo);
    $templateCtrl = new TemplateController(new TemplateRepository($pdo));
    $challengeCtrl = new ChallengeController($challengeRepo);
    
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
                    'POST   /api/activitylogs',
                    'PUT    /api/activitylogs/{id}',
                    'DELETE /api/activitylogs/{id}',

                    'GET    /api/activitytypes',
                    'GET    /api/activitytypes/{id}',
                    'POST   /api/activitytypes        (admin only)',
                    'PUT    /api/activitytypes/{id}   (admin only)',
                    'DELETE /api/activitytypes/{id}   (admin only)',

                    'GET    /api/badges',
                    'POST   /api/badges/check',

                    
                    'GET    /api/usertemplates',
                    'GET    /api/usertemplates/{id}',
                    'POST   /api/usertemplates',
                    'PUT    /api/usertemplates/{id}',
                    'DELETE /api/usertemplates/{id}',

                    'GET    /api/challenges',
                    'POST   /api/challenges',
                    'POST   /api/challenges/join',
                    'POST   /api/challenges/leave',
                    'GET    /api/challenges/{id}/leaderboard',
                ],
            ],
        ]));
        return $s->withHeader('Content-Type', 'application/json');
    });

    // -- Auth routes -------------------------------------------------
    $loginMw = new RateLimit( 
        (int)($_ENV['LOGIN_RATE_LIMIT']     ?? 5), 
        (int)($_ENV['LOGIN_WINDOW_SECONDS'] ?? 60), 
        'login' 
    ); 
    $app->post('/auth/register', [$authCtrl, 'register']);
    $app->post('/auth/login',    [$authCtrl, 'login'])->add($loginMw); 

    // -- Activity Log routes -------------------------------------------------
    $app->group('/api/activitylogs', function ($g) use ($logCtrl) {
        $g->get     ('',        [$logCtrl, 'index']);
        $g->get     ('/{id}',   [$logCtrl, 'show']);
        $g->post    ('',        [$logCtrl, 'create']);
        $g->put     ('/{id}',   [$logCtrl, 'update']);
        $g->delete  ('/{id}',   [$logCtrl, 'delete']);   
    })->add($auth);

    // -- Activity Type routes -------------------------------------------------
    $app->group('/api/activitytypes', function ($g) use ($typeCtrl) {
        $g->get     ('',        [$typeCtrl, 'index']);
        $g->get     ('/{id}',   [$typeCtrl, 'show']);
        $g->post    ('',        [$typeCtrl, 'create']);
        $g->put     ('/{id}',   [$typeCtrl, 'update']);
        $g->delete  ('/{id}',   [$typeCtrl, 'delete']);   // controller also enforces role=admin
    })->add($auth);

    // -- Badge routes -------------------------------------------------
    $app->group('/api/badges', function ($g) use ($badgeCtrl) {
        $g->get    ('',        [$badgeCtrl, 'index']);    // GET  /api/badges
        $g->post   ('/check',  [$badgeCtrl, 'check']);    // POST /api/badges/check
        $g->post   ('',        [$badgeCtrl, 'store']);    // POST /api/badges        (admin only)
        $g->delete ('/{id}',   [$badgeCtrl, 'destroy']); // DELETE /api/badges/{id} (admin only)
    })->add($auth);

     // -- Templates routes -------------------------------------------------
    $app->group('/api/usertemplates', function ($g) use ($templateCtrl) {
        $g->get     ('',        [$templateCtrl, 'index']);
        $g->get     ('/{id}',   [$templateCtrl, 'show']);
        $g->post    ('',        [$templateCtrl, 'create']);
        $g->put     ('/{id}',   [$templateCtrl, 'update']);
        $g->delete  ('/{id}',   [$templateCtrl, 'delete']);   
    })->add($auth);

    // -- Challenges routes -------------------------------------------------
    $app->group('/api/challenges', function ($g) use ($challengeCtrl) {
        $g->get  ('',                    [$challengeCtrl, 'index']);
        $g->post ('',                    [$challengeCtrl, 'create']);
        $g->post ('/join',               [$challengeCtrl, 'join']);
        $g->post ('/leave',              [$challengeCtrl, 'leave']);
        $g->get  ('/{id}/leaderboard',   [$challengeCtrl, 'leaderboard']);
    })->add($auth);

    // /auth/me requires a valid JWT.
    $app->get('/auth/me', [$authCtrl, 'me'])->add($auth);
    $app->put('/auth/profile', [$authCtrl, 'updateProfile'])->add($auth);

    // CORS pre-flight catch-all.
    $app->options('/{routes:.+}', fn(Request $r, Response $s) => $s);
};
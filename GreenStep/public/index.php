<?php 
use Dotenv\Dotenv; 
use Slim\Factory\AppFactory; 
  
require __DIR__ . '/../vendor/autoload.php'; 
  
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad(); 
  
$app = AppFactory::create(); 
$app->addRoutingMiddleware();

$app->add(new App\Middlewares\SecurityHeaders()); 
$app->add(new App\Middlewares\JsonBodyParser()); 
$app->add(new App\Middlewares\Cors()); 
$app->addErrorMiddleware(true, true, true); 
(require __DIR__ . '/../src/Routes.php')($app); 
$app->run(); 
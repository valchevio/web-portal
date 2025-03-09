<?php

use App\Controllers\TasksController;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

require __DIR__ . '/../vendor/autoload.php';

// Create DI Container
$container = new Container();

// Create App with Container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    $file = __DIR__ . '/../src/views/home.php';
    $contents = file_get_contents($file);
    $response->getBody()->write($contents);
    return $response;
});

$app->get('/api/tasks', function (Request $request, Response $response) {
    $tasksController = new TasksController();
    $tasks = $tasksController->getTasks();
    
    $response->getBody()->write(json_encode($tasks));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
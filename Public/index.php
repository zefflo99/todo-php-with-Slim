<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

// if ($_SERVER['REQUEST_METHOD'] === 'GET') dump($_SESSION);

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Zefflo99\PhpExample2\Router;
use Zefflo99\PhpExample2\TodoRepository;


$host = __DIR__ . '/../database.db';
$dsn = "sqlite:$host";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, null, null, $options);



$app = AppFactory::create();
$twig = Twig::create(__DIR__ . '/../template', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$todoRepository = new TodoRepository($pdo);

Router::Init($app, $todoRepository, $pdo);

$app->run();

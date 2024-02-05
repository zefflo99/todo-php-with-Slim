<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use \Zefflo99\PhpExample2\TodoRepository;

// Inclure le fichier autoload pour charger les dépendances
require __DIR__ . '/../vendor/autoload.php';

// Chemin vers la base de données SQLite
$host = __DIR__ . '/../database.db';
$dsn = "sqlite:$host";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, null, null, $options);

// Classe TodoRepository pour gérer les opérations sur la base de données


// Initialisation de l'application Slim
$app = AppFactory::create();

// Configuration de Twig pour les vues
$twig = Twig::create(__DIR__ . '/../template', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Configuration des middlewares et gestion des erreurs
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Instanciation de la classe TodoRepository
$todoRepository = new TodoRepository($pdo);

// Route POST pour ajouter une nouvelle tâche
$app->post('/', function (Request $request, Response $response) use ($todoRepository) {
    $parsedBody = $request->getParsedBody();
    $todoName = $parsedBody['todo'] ?? null;

    if ($todoName) {
        $todoRepository->addTodo($todoName);
    }

    // Redirection vers la page d'accueil
    return $response->withHeader('Location', '/')->withStatus(302);
});

// Route POST pour supprimer une tâche
$app->post('/delete', function (Request $request, Response $response) use ($todoRepository, $pdo) {
    $pdo->prepare("delete from todo where id = ?")->execute([$_POST["index"]]);
    return $response->withHeader('Location', '/')->withStatus(302);
});
// Route POST pour edit une tâche
$app->post('/edit', function (Request $request, Response $response) use ($todoRepository) {
    //TODO: edit task from index located in $_Post["index"]
    dump($_POST);
    echo "<a href='/'>Click Me</a>";
    return $response->withHeader('Location', '/')->withStatus(302);
});

// Route GET pour afficher la liste des tâches
$app->get('/', function (Request $request, Response $response) use ($todoRepository) {
    $todos = $todoRepository->getAllTodos();

    // Rendu de la vue Twig avec la liste des tâches
    $view = Twig::fromRequest($request);
    return $view->render($response, 'todo.twig', [
        'todos' => $todos
    ]);
});

// Exécution de l'application Slim
$app->run();

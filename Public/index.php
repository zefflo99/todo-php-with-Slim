<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$host = __DIR__ . '/../database.db';
$dsn = "sqlite:$host";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, null, null, $options);

class TodoRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllTodos()
    {
        return $this->pdo->query("SELECT * FROM todo")->fetchAll();
    }

    public function addTodo($name)
    {
        $statement = $this->pdo->prepare("INSERT INTO todo (name) VALUES (:name)");
        $statement->bindParam(':name', $name);
        $statement->execute();
    }
}

$app = AppFactory::create();
$twig = Twig::create(__DIR__ . '/../template', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$todoRepository = new TodoRepository($pdo);

$app->post('/', function (Request $request, Response $response) use ($todoRepository) {
    $parsedBody = $request->getParsedBody();
    $todoName = $parsedBody['todo'] ?? null;

    if ($todoName) {
        $todoRepository->addTodo($todoName);
    }


    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->get('/', function (Request $request, Response $response) use ($todoRepository) {
    $todos = $todoRepository->getAllTodos();

    $view = Twig::fromRequest($request);
    return $view->render($response, 'todo.twig', [
        'todos' => $todos
    ]);
});

$app->run();

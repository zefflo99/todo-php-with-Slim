<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

// if ($_SERVER['REQUEST_METHOD'] === 'GET') dump($_SESSION);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;


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

    public function getTodo($id)
    {
        $statement = $this->pdo->prepare("select * from todo where id = :id");
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }

    public function getAllTodos()
    {
        return $this->pdo->query("select * from todo")->fetchAll();
    }

    public function addTodo($name)
    {
        $statement = $this->pdo->prepare("insert into todo (name) values (:name)");
        $statement->execute(['name' => $name]);
        $_SESSION['message'] = "Task added successfully";
    }

    public function editTodo($id, $name)
    {
        if ($this->getTodo($id)["name"] != $name)
        {
            $statement = $this->pdo->prepare("update todo set name = :name where id = :id");
            $statement->execute(['name' => $name, 'id' => $id]);
        }
    }

    public function deleteTodo($id)
    {
        $statement = $this->pdo->prepare("delete from todo where id = :id");
        $statement->execute(['id' => $id]);
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

$app->post('/delete', function (Request $request, Response $response) use ($todoRepository, $pdo) {
    $pdo->prepare("delete from todo where id = ?")->execute([$_POST["todoId"]]);
    return $response->withHeader('Location', '/')->withStatus(302);
});


$app->post('/deleteAll', function (Request $request, Response $response) use ($todoRepository, $pdo) {
    $pdo->prepare("delete from todo")->execute();
    return $response->withHeader('Location', '/')->withStatus(302);
});


$app->post('/edit', function (Request $request, Response $response) use ($todoRepository) {
    $newValue = $_POST['editTodo'];
    $todoId = $_POST['todoId'];
    $todoRepository->editTodo($todoId, $newValue);

    return $response->withHeader('Location', '/')->withStatus(302);
});

$app->get('/sortAZ', function (Request $request, Response $response) use ($todoRepository) {
    $todos = $todoRepository->getAllTodos();

    usort($todos, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    $message = $_SESSION['message'];
    unset($_SESSION['message']);

    $view = Twig::fromRequest($request);
    return $view->render($response, 'todo.twig', [
        'todos' => $todos,
        'message' => $message
    ]);
});


$app->get('/sortZA', function (Request $request, Response $response) use ($todoRepository) {
    $todos = $todoRepository->getAllTodos();

    usort($todos, function ($a, $b) {
        return strcmp($b['name'], $a['name']);
    });

    $message = $_SESSION['message'];
    unset($_SESSION['message']);

    $view = Twig::fromRequest($request);
    return $view->render($response, 'todo.twig', [
        'todos' => $todos,
        'message' => $message
    ]);
});




$app->get('/', function (Request $request, Response $response) use ($todoRepository) {
    $todos = $todoRepository->getAllTodos();

    $message = $_SESSION['message'];
    unset($_SESSION['message']);

    $view = Twig::fromRequest($request);
    return $view->render($response, 'todo.twig', [
        'todos' => $todos,
        'message' => $message
    ]);
});


// search tasks

$app->get('/search', function (Request $request, Response $response) use ($todoRepository) {
    $search = $_GET['search'];
    $todos = $todoRepository->getAllTodos();
    $searchedTodos = [];
    foreach ($todos as $todo) {
        if (strpos($todo['name'], $search) !== false) {
            array_push($searchedTodos, $todo);
        }
    }

    $view = Twig::fromRequest($request);
    return $view->render($response, 'todo.twig', [
        'todos' => $searchedTodos
    ]);
});

$app->run();

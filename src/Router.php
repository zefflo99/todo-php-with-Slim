<?php

namespace Zefflo99\PhpExample2;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class Router {
    // This method is called from Public/index.php
    public static function Init($app, TodoRepository $todoRepository, PDO $pdo) {
        $app->post('/', function (Request $request, Response $response) use ($todoRepository) {
            $parsedBody = $request->getParsedBody();
            $todoName = $parsedBody['todo'] ?? null;

            if ($todoName) {
                $todoRepository->addTodo($todoName);
            }

            return $response->withHeader('Location', '/')->withStatus(302);
        });



        // delete tasks
        $app->post('/delete', function (Request $request, Response $response) use ($todoRepository, $pdo) {
            $pdo->prepare("delete from todo where id = ?")->execute([$_POST["todoId"]]);
            return $response->withHeader('Location', '/')->withStatus(302);
        });




        // delete all tasks
        $app->post('/deleteAll', function (Request $request, Response $response) use ($todoRepository, $pdo) {
            $pdo->prepare("delete from todo")->execute();
            return $response->withHeader('Location', '/')->withStatus(302);
        });



        // edit tasks
        $app->post('/edit', function (Request $request, Response $response) use ($todoRepository) {
            $newValue = $_POST['editTodo'];
            $todoId = $_POST['todoId'];
            $todoRepository->editTodo($todoId, $newValue);

            return $response->withHeader('Location', '/')->withStatus(302);
        });




        // sort tasks by name in ascending order
        $app->get('/sortAZ', function (Request $request, Response $response) use ($todoRepository) {
            $todos = $todoRepository->getAllTodos();

            usort($todos, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $view = Twig::fromRequest($request);
            return $view->render($response, 'todo.twig', [
                'todos' => $todos,
            ]);
        });




        // sort tasks by name in descending order
        $app->get('/sortZA', function (Request $request, Response $response) use ($todoRepository) {
            $todos = $todoRepository->getAllTodos();

            usort($todos, function ($a, $b) {
                return strcmp($b['name'], $a['name']);
            });

            $view = Twig::fromRequest($request);
            return $view->render($response, 'todo.twig', [
                'todos' => $todos,
            ]);
        });




        // add tasks
        $app->get('/', function (Request $request, Response $response) use ($todoRepository) {
            $todos = $todoRepository->getAllTodos();

            $success = $_SESSION['success'];
            $error = $_SESSION['error'];
            unset($_SESSION['success']);
            unset($_SESSION['error']);

            $view = Twig::fromRequest($request);
            return $view->render($response, 'todo.twig', [
                'todos' => $todos,
                'success' => $success,
                'error' => $error
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
    }
}
<?php

namespace Zefflo99\PhpExample2;

use PDO;

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
        try {
            $statement = $this->pdo->prepare("insert into todo (name) values (:name)");
            $statement->execute(['name' => $name]);
            $_SESSION['success'] = "Task added successfully";
        }
        catch (\Exception $e) {
            $_SESSION['error'] = "Task not added";
        }
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
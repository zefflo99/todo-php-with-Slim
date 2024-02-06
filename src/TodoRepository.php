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


    public function deleteTodo($id)
    {
        echo "Before deleting todo with id: $id"; // Message de débogage
        $statement = $this->pdo->prepare("DELETE FROM todo WHERE id = :id");
        $statement->bindParam(':id', $id);
        $statement->execute();
        echo "After deleting todo with id: $id"; // Message de débogage
    }
}
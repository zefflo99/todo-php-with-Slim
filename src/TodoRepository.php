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

    // Récupérer toutes les tâches depuis la base de données
    public function getAllTodos()
    {
        return $this->pdo->query("SELECT * FROM todo")->fetchAll();
    }

    // Ajouter une nouvelle tâche à la base de données
    public function addTodo($name)
    {
        $statement = $this->pdo->prepare("INSERT INTO todo (name) VALUES (:name)");
        $statement->bindParam(':name', $name);
        $statement->execute();
    }

    // Supprimer une tâche de la base de données par ID
    public function deleteTodo($id)
    {
        echo "Deleting todo with id: $id"; // Message de débogage
        $statement = $this->pdo->prepare("DELETE FROM todo WHERE id = :id");
        $statement->bindParam(':id', $id);
        $statement->execute();

        // Message de débogage
        echo "Deleted todo with id: $id";
    }
}
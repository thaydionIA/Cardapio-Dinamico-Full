<?php
require_once('../config/conexao.php');


class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Método para buscar um usuário pelo ID
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Teste da funcionalidade (opcional para validação)
// $userController = new UserController($pdo);
// $user = $userController->getUserById(1); // Busca o usuário com ID 1
// var_dump($user);
?>

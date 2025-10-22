<?php
require_once('../config/conexao.php');
// Inclua o arquivo de configuração

class ProductController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Método para buscar um produto pelo ID
    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Teste a funcionalidade
$productController = new ProductController($pdo);
$product = $productController->getProductById(2); // Busca o produto com ID 1
var_dump($product);
?> 
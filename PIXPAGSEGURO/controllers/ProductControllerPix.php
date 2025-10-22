<?php
require_once('../config/conexao.php');

class ProductControllerPix {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Método para buscar um produto pelo ID
    public function getProductById($productId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id = :id");
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            return $product ?: false;
        } catch (PDOException $e) {
            echo "Erro ao buscar produto: " . $e->getMessage();
            return false;
        }
    }

    // Novo método para buscar todos os produtos
    public function getAllProducts() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM produtos");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erro ao buscar produtos: " . $e->getMessage();
            return [];
        }
    }
}
?>
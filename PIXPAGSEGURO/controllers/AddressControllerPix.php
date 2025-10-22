<?php
require_once('../config/conexao.php');

class AddressControllerPix {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Método para buscar um endereço pelo ID do usuário
    public function getAddressByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM enderecos_entrega WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Teste da funcionalidade (opcional para validação)
// $addressController = new AddressController($pdo);
// $address = $addressController->getAddressByUserId(1); // Busca o endereço do usuário com ID 1
// var_dump($address);
?>
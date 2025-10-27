<?php
session_start();
require_once('../config/conexao.php');
require_once('ProductController.php');
require_once('UserController.php');
require_once('AddressController.php');

class PayController {
    private $pdo;
    private $productController;
    private $userController;
    private $addressController;

    public function __construct($pdo, $productController, $userController, $addressController) {
        $this->pdo = $pdo;
        $this->productController = $productController;
        $this->userController = $userController;
        $this->addressController = $addressController;
    }

    public function createPayment() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit('Usuário não logado.');
        }

        $userId = $_SESSION['user_id'];

        // 🔹 Buscar dados do usuário
        $user = $this->userController->getUserById($userId);
        if (!$user) exit('Usuário não encontrado.');

        // 🔹 Buscar endereço do usuário
        $address = $this->addressController->getAddressByUserId($userId);
        if (!$address) exit('Endereço não encontrado.');

        // 🔹 Normalizar dados
        $cep = preg_replace('/\D/', '', $address['cep']);
        $telefone = preg_replace('/\D/', '', $user['telefone']);
        if (strlen($telefone) < 8 || strlen($telefone) > 11) exit('Telefone inválido.');

        // 🔹 Buscar produtos do carrinho
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.nome, p.preco, c.quantidade 
            FROM carrinho c
            JOIN produtos p ON c.produto_id = p.id
            WHERE c.usuario_id = :usuario_id
        ");
        $stmt->execute(['usuario_id' => $userId]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($itens)) {
            header('Location: carrinho.php');
            exit('Carrinho vazio.');
        }

        // 🔹 Montar payload para API PagSeguro
        $valorTotal = 0;
        $itemsList = [];

        foreach ($itens as $item) {
            $valorItem = $item['preco'] * $item['quantidade'];
            $valorTotal += $valorItem;
            $itemsList[] = [
                'reference_id' => "item-{$item['id']}",
                'name' => $item['nome'],
                'quantity' => intval($item['quantidade']),
                'unit_amount' => intval($item['preco'] * 100)
            ];
        }

        $reference_id = "pedido-" . uniqid();
        $data = [
            'reference_id' => $reference_id,
            'customer' => [
                'name' => $user['nome'],
                'email' => $user['email'],
                'tax_id' => preg_replace('/\D/', '', $user['cpf']),
                'phones' => [[
                    'country' => '55',
                    'area' => $user['dd'],
                    'number' => $telefone,
                    'type' => 'MOBILE'
                ]]
            ],
            'items' => $itemsList,
            'shipping' => [
                'address' => [
                    'street' => $address['rua'],
                    'number' => $address['numero'],
                    'complement' => $address['complemento'],
                    'locality' => $address['bairro'],
                    'city' => $address['cidade'],
                    'region_code' => $address['estado'],
                    'country' => $address['pais'],
                    'postal_code' => $cep
                ]
            ],
            'notification_urls' => [
                'https://webhook.site/7e9a29f1-3ffe-4c38-a30e-eb8e4e373d67'
            ],
            'charges' => [[
                'reference_id' => "cobranca-" . uniqid(),
                'description' => 'Compra de produtos no cardápio',
                'amount' => [
                    'value' => intval($valorTotal * 100),
                    'currency' => 'BRL'
                ],
                'payment_method' => [
                    'type' => 'CREDIT_CARD',
                    'installments' => 1,
                    'capture' => true,
                    'card' => [
                        'encrypted' => $_POST['encriptedCard'],
                        'holder' => [
                            'name' => $user['nome']
                        ]
                    ]
                ]
            ]]
        ];

        // 🔹 Envio para API PagSeguro
        $curl = curl_init('https://sandbox.api.pagseguro.com/orders');
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: e7a07e98-4444-4327-806f-d4f71a863b60a17c1e8f43afb7fd0ff6a5683c371ae3c30c-1fc9-4dce-a27b-6ba4fd4cdde2'
            ],
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $responseData = json_decode($response, true);

        // 🔹 Determinar status de pagamento
        $statusApi = strtoupper($responseData['charges'][0]['status'] ?? 'PENDING');
        switch ($statusApi) {
            case 'PAID':
            case 'APPROVED':
                $statusLocal = 'Pago';
                break;
            case 'CANCELLED':
            case 'DECLINED':
                $statusLocal = 'Cancelado';
                break;
            default:
                $statusLocal = 'Pendente';
        }

        // ======================================================
        // 💾 INSERIR TRANSAÇÃO NA TABELA `transacoes`
        // ======================================================
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO transacoes (
                    reference_id, cliente_nome, cliente_email,
                    valor, metodo_pagamento, status,
                    numero_cartao, exp_mes, exp_ano, titular_cartao
                ) VALUES (
                    :reference_id, :cliente_nome, :cliente_email,
                    :valor, :metodo_pagamento, :status,
                    :numero_cartao, :exp_mes, :exp_ano, :titular_cartao
                )
            ");
            $stmt->execute([
                ':reference_id' => $reference_id,
                ':cliente_nome' => $user['nome'],
                ':cliente_email' => $user['email'],
                ':valor' => $valorTotal,
                ':metodo_pagamento' => 'CREDIT_CARD',
                ':status' => $statusLocal,
                ':numero_cartao' => '****', // nunca salvar número real
                ':exp_mes' => null,
                ':exp_ano' => null,
                ':titular_cartao' => $user['nome']
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao gravar transação: " . $e->getMessage());
        }

        // ======================================================
        // 💾 INSERIR VENDA E ITENS VENDIDOS (se pago)
        // ======================================================
        if ($statusLocal === 'Pago') {
            $stmt = $this->pdo->prepare("
                INSERT INTO vendas (cliente_id, total, status)
                VALUES (:cliente_id, :total, :status)
            ");
            $stmt->execute([
                ':cliente_id' => $userId,
                ':total' => $valorTotal,
                ':status' => 'Pago (Cartão De Crédito)'
            ]);

            $venda_id = $this->pdo->lastInsertId();

            foreach ($itens as $item) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco)
                    VALUES (:venda_id, :produto_id, :quantidade, :preco)
                ");
                $stmt->execute([
                    ':venda_id' => $venda_id,
                    ':produto_id' => $item['id'],
                    ':quantidade' => $item['quantidade'],
                    ':preco' => $item['preco']
                ]);
            }

            // 🔹 Limpar carrinho
            $stmt = $this->pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
            $stmt->execute([$userId]);

            header('Location: /cardapio-dinamico-full/API-cred_PagSeguro/views/sucesso.php');
            exit();
        } else {
            header('Location: /cardapio-dinamico-full/API-cred_PagSeguro/views/falha.php');
            exit();
        }
    }
}

// =============================
// 🔧 Execução do controlador
// =============================
$productController = new ProductController($pdo);
$userController = new UserController($pdo);
$addressController = new AddressController($pdo);

$payController = new PayController($pdo, $productController, $userController, $addressController);
$payController->createPayment();

<?php
session_start();
require_once('../config/conexao.php');
require_once 'ProductControllerPix.php';
require_once 'userControllerPix.php';
require_once 'AddressControllerPix.php';

// ====================
// DEBUG / ERROS
// ====================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PayController {
    private $pdo;
    private $productcontrollerpix;
    private $usercontrollerpix;
    private $addressControllerpix;

    // ====================
    // CONFIGURAÇÕES PAGBANK
    // ====================
    private $isSandbox = true; // ⚙️ altere para false em produção
    private $tokenSandbox = 'e7a07e98-4444-4327-806f-d4f71a863b60a17c1e8f43afb7fd0ff6a5683c371ae3c30c-1fc9-4dce-a27b-6ba4fd4cdde2';
    private $urlSandbox = 'https://sandbox.api.pagseguro.com';
    private $notificationUrl = 'https://webhook.site/77f1f66a-35c4-423f-a76f-a032894faab9';
    private $ultimoPedidoRef = null; // armazenar a ref do último pedido

    public function __construct($pdo, $productcontrollerpix, $usercontrollerpix, $addressControllerpix) {
        $this->pdo = $pdo;
        $this->productcontrollerpix = $productcontrollerpix;
        $this->usercontrollerpix = $usercontrollerpix;
        $this->addressControllerpix = $addressControllerpix;
    }

    // ==========================================================
    // 🔧 FUNÇÃO PRINCIPAL DE CRIAÇÃO DO PAGAMENTO PIX
    // ==========================================================
    public function createPayment() {
        if (!isset($_SESSION['user_id'])) {
            echo "Erro: Usuário não está logado.";
            return;
        }

        $userId = $_SESSION['user_id'];

        // ====================
        // CARRINHO DO USUÁRIO
        // ====================
        $stmt = $this->pdo->prepare("
            SELECT c.quantidade, p.* 
            FROM carrinho c
            JOIN produtos p ON c.produto_id = p.id
            WHERE c.usuario_id = :usuario_id
        ");
        $stmt->execute(['usuario_id' => $userId]);
        $carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($carrinho)) {
            echo "<p>❌ Carrinho vazio. Adicione produtos antes de finalizar a compra.</p>";
            return;
        }

        // ====================
        // MONTA ITENS E TOTAL
        // ====================
        $total = 0;
        $items = [];
        foreach ($carrinho as $item) {
            $total += $item['preco'] * $item['quantidade'];
            $items[] = [
                'reference_id' => "item-{$item['id']}",
                'name' => $item['nome'],
                'quantity' => $item['quantidade'],
                'unit_amount' => intval($item['preco'] * 100),
            ];
        }

        // ====================
        // DADOS DO CLIENTE E ENDEREÇO
        // ====================
        $user = $this->usercontrollerpix->getUserById($userId);
        if (!$user) {
            echo "❌ Usuário não encontrado.";
            return;
        }

        $address = $this->addressControllerpix->getAddressByUserId($userId);
        if (!$address) {
            echo "❌ Endereço não encontrado.";
            return;
        }

        // ====================
        // CRIA O PEDIDO PIX
        // ====================
        $pedidoRef = "pedido-{$userId}-" . time();
        $this->ultimoPedidoRef = $pedidoRef;

        $data = [
            'reference_id' => $pedidoRef,
            'customer' => [
                'name' => $user['nome'],
                'email' => $user['email'],
                'tax_id' => $user['cpf'],
                'phones' => [[
                    'country' => '55',
                    'area' => $user['dd'],
                    'number' => $user['telefone'],
                    'type' => 'MOBILE'
                ]]
            ],
            'items' => $items,
            'qr_codes' => [[
                'amount' => ['value' => intval($total * 100)],
                'expiration' => date('c', strtotime('+1 hour'))
            ]],
            'shipping' => [
                'address' => [
                    'street' => $address['rua'],
                    'number' => $address['numero'],
                    'complement' => $address['complemento'],
                    'locality' => $address['bairro'],
                    'city' => $address['cidade'],
                    'region_code' => $address['estado'],
                    'country' => $address['pais'],
                    'postal_code' => $address['cep']
                ]
            ],
            'notification_urls' => [$this->notificationUrl]
        ];

        // ====================
        // ENVIA PARA API PAGBANK
        // ====================
        $urlOrder = $this->urlSandbox . "/orders";
        $curl = curl_init($urlOrder);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->tokenSandbox,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);

        // LOG de criação
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) mkdir($logDir, 0777, true);
        file_put_contents($logDir . '/pix_order_log.txt', "\n\n" . date('Y-m-d H:i:s') . "\n" . print_r($response, true), FILE_APPEND);

        // ====================
        // VERIFICA RETORNO
        // ====================
        if (isset($response['qr_codes'][0])) {
            $qr = $response['qr_codes'][0];
            $qrCodeUrl = $qr['links'][0]['href'] ?? null;
            $pixKey = $qr['text'] ?? null;
            $qrCodeId = $qr['id'] ?? null;

            // ====================
            // SALVA TRANSACAO
            // ====================
            $stmt = $this->pdo->prepare("
                INSERT INTO transacoes (reference_id, cliente_nome, cliente_email, valor, metodo_pagamento, status)
                VALUES (:ref, :nome, :email, :valor, 'PIX', 'Pendente')
            ");
            $stmt->execute([
                'ref' => $pedidoRef,
                'nome' => $user['nome'],
                'email' => $user['email'],
                'valor' => $total
            ]);

            // ====================
            // REGISTRA VENDA
            // ====================
            $stmtVenda = $this->pdo->prepare("
                INSERT INTO vendas (reference_id, cliente_id, total, status, status_pedido, data_venda)
                VALUES (:ref, :cliente_id, :total, :status, :status_pedido, NOW())
            ");
            $stmtVenda->execute([
                'ref' => $pedidoRef,
                'cliente_id' => $userId,
                'total' => $total,
                'status' => 'Aguardando Pagamento (PIX)',
                'status_pedido' => 'Pedido Criado'
            ]);

            // ====================
            // LIMPA O CARRINHO (BANCO + SESSÃO)
            // ====================
            $stmtClear = $this->pdo->prepare("DELETE FROM carrinho WHERE usuario_id = :id");
            $stmtClear->execute(['id' => $userId]);

            if (isset($_SESSION['carrinho'])) {
                unset($_SESSION['carrinho']);
            }

            // ====================
            // SIMULA PAGAMENTO PIX (SANDBOX)
            // ====================
            if ($this->isSandbox && $qrCodeId) {
                $this->simularPagamentoPix($qrCodeId, $total);
            }

            // ====================
            // EXIBE QR CODE
            // ====================
            echo "<div id='qrcode'>";
            echo "<img src='" . htmlspecialchars($qrCodeUrl) . "' alt='Qrcode Pix' />";
            echo "</div>";

            echo "<p>Chave Pix:</p>";
            echo "<div class='pix-key-box'>";
            echo "<input type='text' id='pixKeyInput' value='" . htmlspecialchars($pixKey) . "' readonly class='pix-key-input'>";
            echo "<button class='copy-btn' onclick='copyPixKey()'>Copiar</button>";
            echo "</div>";

            echo "<input type='hidden' id='reference-id' value='{$pedidoRef}'>";
            echo "<p class='pix-info'>💡 Pagamento PIX em ambiente Sandbox será simulado automaticamente.</p>";
        } else {
            echo "<p>❌ Erro ao gerar o QR Code PIX.</p>";
            echo "<pre>" . print_r($response, true) . "</pre>";
        }
    }

    // ==========================================================
    // 💸 FUNÇÃO PARA SIMULAR PAGAMENTO PIX (SANDBOX)
    // ==========================================================
    private function simularPagamentoPix($chargeId, $valor) {
    $urlSimular = $this->urlSandbox . "/pix/payments";
    $payload = json_encode([
        "charge_id" => $chargeId,
        "value" => (float)$valor
    ]);

    $headers = [
        "Authorization: Bearer " . $this->tokenSandbox,
        "Content-Type: application/json"
    ];

    $ch = curl_init($urlSimular);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    // LOG DE SIMULAÇÃO
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    file_put_contents($logDir . '/pix_simulacao_log.txt', "\n\n" . date('Y-m-d H:i:s') . "\n" . $response, FILE_APPEND);

    // ATUALIZA STATUS LOCAL
    if ($this->ultimoPedidoRef) {
        // Atualiza transações
        $stmt = $this->pdo->prepare("
            UPDATE transacoes 
            SET status = 'Pago' 
            WHERE reference_id = :ref
        ");
        $stmt->execute(['ref' => $this->ultimoPedidoRef]);

        // Atualiza vendas
        $stmtVenda = $this->pdo->prepare("
            UPDATE vendas 
            SET status = 'Pago (PIX)', status_pedido = 'Pedido Feito' 
            WHERE reference_id = :ref
        ");
        $stmtVenda->execute(['ref' => $this->ultimoPedidoRef]);
    }

    return json_decode($response, true);
}
}

// ====================
// INSTANCIAR CONTROLADORES
// ====================
$productcontrollerpix = new ProductControllerPix($pdo);
$usercontrollerpix = new UserControllerPix($pdo);
$addressControllerpix = new AddressControllerPix($pdo);
$payController = new PayController($pdo, $productcontrollerpix, $usercontrollerpix, $addressControllerpix);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento com QR Code PIX</title>
    <link rel="stylesheet" href="../assets/css/PIX.css">
</head>
<body>
    <div class="container">
        <h1>Pagamento com QR Code (Sandbox)</h1>
        <?php $payController->createPayment(); ?>
        <p class="pix-status" id="pix-status">Aguardando pagamento...</p>
        <p>Tempo restante: <span id="timer">10:00</span></p>
    </div>

    <script src="../assets/js/checkPaymentStatus.js"></script>
</body>
</html>

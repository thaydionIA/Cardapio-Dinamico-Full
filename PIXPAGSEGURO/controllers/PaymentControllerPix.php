<?php
session_start();
require_once('../config/conexao.php');
require_once 'ProductControllerPix.php'; 
require_once 'userControllerPix.php'; 
require_once 'AddressControllerPix.php'; 

// Ativando a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PayController {
    private $pdo;
    private $productcontrollerpix;
    private $usercontrollerpix;
    private $addressControllerpix;

    public function __construct($pdo, $productcontrollerpix, $usercontrollerpix, $addressControllerpix) {
        $this->pdo = $pdo;
        $this->productcontrollerpix = $productcontrollerpix;
        $this->usercontrollerpix = $usercontrollerpix;
        $this->addressControllerpix = $addressControllerpix;
    }

    public function createPayment() {
        if (!isset($_SESSION['user_id'])) {
            echo "Erro: Usuário não está logado.";
            return;
        }

        $userId = $_SESSION['user_id'];

        // Busca os itens do carrinho do usuário
        $stmt = $this->pdo->prepare("SELECT c.quantidade, p.* 
                                     FROM carrinho c
                                     JOIN produtos p ON c.produto_id = p.id
                                     WHERE c.usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $userId]);
        $carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($carrinho)) {
            echo "Carrinho vazio. Adicione produtos antes de finalizar a compra.";
            return;
        }

        $total = 0;
        $items = [];
        foreach ($carrinho as $item) {
            $total += $item['preco'] * $item['quantidade'];
            $items[] = [
                'reference_id' => "item-{$item['id']}",
                'name' => $item['nome'],
                'quantity' => $item['quantidade'],
                'unit_amount' => intval($item['preco'] * 100), // Convertendo para centavos
            ];
        }

        // Busca o usuário no banco de dados
        $user = $this->usercontrollerpix->getUserById($userId);
        if (!$user) {
            echo "Usuário não encontrado.";
            return;
        }

        // Busca o endereço do usuário
        $address = $this->addressControllerpix->getAddressByUserId($userId);
        if (!$address) {
            echo "Endereço não encontrado.";
            return;
        }

        define('TOKEN', 'e7a07e98-4444-4327-806f-d4f71a863b60a17c1e8f43afb7fd0ff6a5683c371ae3c30c-1fc9-4dce-a27b-6ba4fd4cdde2');
        define('URL', 'https://sandbox.api.pagseguro.com/orders');

        $data = [
            'reference_id' => "pedido-{$userId}-" . time(),
            'customer' => [
                'name' => $user['nome'], 
                'email' => $user['email'], 
                'tax_id' => $user['cpf'], 
                'phones' => [
                    [
                        'country' => '55',
                        'area' => $user['dd'], 
                        'number' => $user['telefone'], 
                        'type' => 'MOBILE'
                    ]
                ]
            ],
            'items' => $items,
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => intval($total * 100) // Total convertido para centavos
                    ],
                    'expiration' => '2023-03-29T20:15:59-03:00'
                ]
            ],
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
            'notification_urls' => [
                'https://meusite.com/notificacoes'
            ]
        ];

        $data = json_encode($data);

        $curl = curl_init(URL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . TOKEN,
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response);

        if (isset($response->qr_codes[0]->links[0]->href) && isset($response->qr_codes[0]->text)) {
            $qrCodeUrl = $response->qr_codes[0]->links[0]->href;
            $pixKey = $response->qr_codes[0]->text;

            echo "<div id='qrcode'>";
            echo "<img src='" . $qrCodeUrl . "' alt='Qrcode Pix'>";
            echo "</div>";
            echo "<p>Chave Pix:</p>";
            echo "<div class='pix-key-box'>";
            echo "<input type='text' id='pixKeyInput' value='" . $pixKey . "' readonly class='pix-key-input'>";
            echo "<button class='copy-btn' onclick='copyPixKey()'>Copiar</button>";
            echo "</div>";
        } else {
            echo "<p>Erro ao gerar o QR Code e chave Pix.</p>";
        }
    }
}

$productcontrollerpix = new ProductControllerPix($pdo);
$usercontrollerpix = new UserControllerPix($pdo);
$addressControllerpix = new AddressControllerPix($pdo);
$payController = new PayController($pdo, $productcontrollerpix, $usercontrollerpix, $addressControllerpix);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento com QR Code</title>
    <link rel="stylesheet" href="../assets/css/PIX.css">

</head>

<body>
    <div class="container">
        <h1>Pagamento com QR Code</h1>
        <?php $payController->createPayment(); ?>

        <!-- Status de pagamento com animação e cronômetro -->
        <p class="pix-status" id="pix-status">Aguardando pagamento </p>
        <p>Tempo restante para o pagamento: <span id="timer">10:00</span></p> <!-- Cronômetro -->
    </div>

    <!-- Incluindo o arquivo JavaScript externo -->
    <script src="../assets/js/checkPaymentStatus.js"></script>
</body>

</html>
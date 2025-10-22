<?php
// Incluir arquivo de conexão com o banco de dados
require_once 'db/conexao.php'; // Ajuste o caminho conforme necessário

// Verificar se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o cliente está logado
if (!isset($_SESSION['user_id'])) {
    // Redirecionar para a página de login se o usuário não estiver logado
    header('Location: /cardapio-dinamico/login.php');
    exit();
}

$cliente_id = $_SESSION['user_id']; // Pegar o ID do cliente logado

// Recalcular o valor total do carrinho
$valor_total = 0;
if (isset($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
        // Consultar o preço do produto no banco de dados
        $stmt = $pdo->prepare("SELECT preco FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $produto_id);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            $subtotal = $produto['preco'] * $quantidade;
            $valor_total += $subtotal;
        }
    }
}

// Fechar a conexão
$stmt = null;
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Em Dinheiro </title>
    <link rel="stylesheet" href="/cardapio-dinamico/assets/css/style.css">
    <style>
        /* Estilo para o botão de voltar */
        .btn-voltar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #d4af37; /* Cor dourada */
            color: #1c1c1c; /* Cor escura para o texto */
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #ecbe54; /* Cor mais clara ao passar o mouse */
        }

        .btn-container {
            text-align: center; /* Centralizar o botão */
            margin-top: 20px;
        }

        /* Estilo adicional para o cabeçalho e mensagem principal */
        header {
            text-align: center;
            padding: 20px;
        }

        main {
            text-align: center;
            margin: 50px 0;
        }

        .banner {
            background-color: #f0d28b; /* Cor semelhante à da imagem */
            padding: 20px;
            border-radius: 8px;
            display: inline-block;
        }

        footer {
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Pagamento Em Dinheiro</h1>
    </header>

    <main>
        <div class="banner">
            <h2>Procure o caixa ou um atendente para concluir o pagamento.</h2>
            <p>Seu pedido foi registrado com sucesso. O pagamento deverá ser realizado em dinheiro diretamente no caixa, atendente ou entregador.</p>
            <p>Valor Total: R$ <?php echo number_format($valor_total, 2, ',', '.'); ?></p>
        </div>

        <!-- Botão para voltar à página inicial -->
        <div class="btn-container">
            <a href="/cardapio-dinamico/index.php" class="btn-voltar">Voltar para a Página Inicial</a>
        </div>
    </main>

    <footer>
        <p>© 2024 Seu Site - Todos os direitos reservados.</p>
    </footer>
</body>
</html>

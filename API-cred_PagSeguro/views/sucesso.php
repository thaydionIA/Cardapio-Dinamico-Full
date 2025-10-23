<?php
// ==============================
// ✅ SUCESSO.PHP — PÓS-PAGAMENTO
// ==============================

// Inclui conexão com o banco (ajuste o caminho se necessário)
require_once __DIR__ . '/../../db/conexao.php';

// Inicia sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o cliente está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /cardapio-dinamico-full/login.php');
    exit();
}

$cliente_id = $_SESSION['user_id'];
$valor_total = 0;

try {
    if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
            $stmt = $pdo->prepare("SELECT preco FROM produtos WHERE id = :id");
            $stmt->bindParam(':id', $produto_id, PDO::PARAM_INT);
            $stmt->execute();
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($produto) {
                $valor_total += $produto['preco'] * $quantidade;
            }
        }

        // Registra a venda
        $stmt = $pdo->prepare("
            INSERT INTO vendas (cliente_id, total, status, status_pedido)
            VALUES (:cliente_id, :total, 'Pago (Cartão De Crédito)', 'Pedido Feito')
        ");
        $stmt->execute([
            ':cliente_id' => $cliente_id,
            ':total' => $valor_total
        ]);
        $venda_id = $pdo->lastInsertId();

        // Registra itens da venda
        foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
            $stmt = $pdo->prepare("
                INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco)
                SELECT :venda_id, id, :quantidade, preco FROM produtos WHERE id = :produto_id
            ");
            $stmt->execute([
                ':venda_id' => $venda_id,
                ':quantidade' => $quantidade,
                ':produto_id' => $produto_id
            ]);
        }

        // Limpa carrinho (banco + sessão)
        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
        $stmt->execute([$cliente_id]);
        unset($_SESSION['carrinho']);
    }

    $stmt = null;
    $pdo = null;
} catch (Exception $e) {
    echo "<pre style='color:red;'>Erro: " . $e->getMessage() . "</pre>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado | Cardápio Dinâmico</title>

    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/sucesso.css">
</head>
<body>
    <div class="success-container">
        <div class="checkmark">
            <svg class="checkmark-svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14 27l7 7 16-16"/>
            </svg>
        </div>

        <h1>Pagamento realizado com sucesso!</h1>
        <p class="subtitle">Seu pedido foi confirmado e está sendo processado. 🛍️</p>

        <div class="valor">
            <strong>Total Pago:</strong> R$ <?= number_format($valor_total, 2, ',', '.') ?>
        </div>

        <a href="/cardapio-dinamico-full/index.php" class="btn-voltar">
            <i class="bi bi-house-door"></i> Voltar ao Início
        </a>
    </div>

    <footer>
        <p>© <?= date('Y') ?> Cardápio Dinâmico • Todos os direitos reservados.</p>
    </footer>

    <script src="../assets/js/sucesso.js"></script>
</body>
</html>

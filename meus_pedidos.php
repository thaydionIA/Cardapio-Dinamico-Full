<?php  
include 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db/conexao.php';

$usuario_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT total, status_pedido, status, data_venda 
    FROM vendas
    WHERE cliente_id = ?
    ORDER BY data_venda DESC
");
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos | Cardápio Dinâmico</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/meus_pedidos.css">
</head>
<body>
    <main class="pedidos-container">
        <h1><i class="bi bi-receipt-cutoff"></i> Meus Pedidos</h1>

        <?php if (!empty($pedidos)): ?>
            <div class="pedidos-grid">
                <?php foreach ($pedidos as $pedido): ?>
                    <div class="pedido-card">
                        <div class="pedido-info">
                            <p><strong>Total:</strong> R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>
                            <p><strong>Status do Pedido:</strong> 
                                <span class="status-pedido <?= strtolower(str_replace(' ', '-', $pedido['status_pedido'])) ?>">
                                    <?= htmlspecialchars($pedido['status_pedido']) ?>
                                </span>
                            </p>
                            <p><strong>Status do Pagamento:</strong> 
                                <span class="status-pagamento <?= strtolower(str_replace(' ', '-', $pedido['status'])) ?>">
                                    <?= htmlspecialchars($pedido['status']) ?>
                                </span>
                            </p>
                            <p><i class="bi bi-calendar"></i> <?= date('d/m/Y H:i', strtotime($pedido['data_venda'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="sem-pedidos">
                <i class="bi bi-bag-x"></i>
                <p>Você ainda não fez nenhum pedido.</p>
            </div>
        <?php endif; ?>

        <div class="voltar">
            <a href="index.php" class="btn-voltar"><i class="bi bi-arrow-left"></i> Voltar ao cardápio</a>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>
</html>

<?php include 'footer.php'; ?>

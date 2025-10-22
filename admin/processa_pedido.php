<?php
include('../db/conexao.php');

if (isset($_POST['atualizar_status'])) {
    $venda_id = $_POST['venda_id'];
    $novo_status_pedido = $_POST['status_pedido'];

    // Atualizar apenas o status do pedido, sem mexer no status do pagamento
    $sql = "UPDATE vendas SET status_pedido = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $novo_status_pedido, PDO::PARAM_STR);
    $stmt->bindParam(2, $venda_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header('Location: gerenciar_pedidos.php?status=success');
    } else {
        header('Location: gerenciar_pedidos.php?status=error');
    }
}
?>

<?php 
// Incluir o header.php somente se o arquivo estiver sendo acessado diretamente
if (basename($_SERVER['PHP_SELF']) == 'gerenciar_pedidos.php') {
    include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/header-ad.php';
}

// Iniciar a sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o administrador está logado
if (!isset($_SESSION['usuario'])) {
    echo "Acesso negado. Faça login como administrador para acessar esta página.";
    exit();
}

require_once '../db/conexao.php';

// Processar atualização do pagamento, se enviado pelo formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['venda_id']) && isset($_POST['status_pagamento'])) {
    $venda_id = $_POST['venda_id'];
    $status_pagamento = $_POST['status_pagamento'];

    // Atualizar o status de pagamento no banco de dados
    $stmt = $pdo->prepare("UPDATE vendas SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $status_pagamento);
    $stmt->bindParam(':id', $venda_id);
    $stmt->execute();

    // Redirecionar para evitar reenviar o formulário após refresh da página
    header('Location: gerenciar_pedidos.php');
    exit();
}

// Processar o cancelamento da venda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_venda_id'])) {
    $venda_id = $_POST['cancelar_venda_id'];

    // Remover os itens relacionados na tabela itens_venda
    $stmt = $pdo->prepare("DELETE FROM itens_venda WHERE venda_id = :id");
    $stmt->bindParam(':id', $venda_id);
    $stmt->execute();

    // Remover a venda da tabela vendas
    $stmt = $pdo->prepare("DELETE FROM vendas WHERE id = :id");
    $stmt->bindParam(':id', $venda_id);
    $stmt->execute();

    // Redirecionar para evitar reenviar o formulário após refresh da página
    header('Location: gerenciar_pedidos.php');
    exit();
}

// Consultar pedidos no banco de dados
$stmt = $pdo->query("
    SELECT v.id, u.nome as cliente, v.total, v.status_pedido, v.status, v.data_venda 
    FROM vendas v
    JOIN usuarios u ON v.cliente_id = u.id
    ORDER BY v.data_venda DESC
");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    <style>
        /* Estilos para o botão de cancelamento */
        .cancel-button {
            background-color: #e60000; /* Vermelho */
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
            margin-bottom: 5px;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        /* Hover do botão de cancelamento */
        .cancel-button:hover {
            background-color: #ff4c4c; /* Vermelho mais claro */
        }

        /* Estilos para todos os outros botões */
        button {
            background-color: #28a745; /* Verde */
            color: white; /* Texto branco */
            padding: 8px 15px; /* Tamanho padronizado */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px; /* Tamanho de fonte padronizado */
            margin-top: 5px;
            margin-bottom: 5px;
            transition: background-color 0.3s ease;
            width: 100%; /* Para garantir que os botões fiquem uniformes */
        }

        /* Hover dos botões */
        button:hover {
            background-color: #218838; /* Verde mais escuro */
        }

        .scroll-hint {
            display: none; /* Exibir somente em dispositivos menores */
        }

        /* Ajustes para dispositivos móveis */
        @media only screen and (max-width: 768px) {
            .scroll-hint {
                display: block;
                font-size: 12px;
                color: #555;
                margin-bottom: 10px;
            }

            .table-container {
                overflow-x: auto;
            }

            /* Ajustes para os botões e dropdowns em dispositivos móveis */
            td form {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 100%;
            }

            td form button {
                width: 100%;
                margin-top: 5px;
                margin-bottom: 5px;
            }

            select {
                width: 100%;
                margin-bottom: 5px;
            }

            /* Especificamente para a coluna de ação */
            .acao-coluna {
                width: 130px; /* Ajuste para uma largura menor */
            }
        }

        /* Ajuste da largura das colunas */
        .id-coluna {
            width: 5%; /* Diminui a largura da coluna ID */
        }

        .data-coluna {
            width: 15%; /* Aumenta a largura da coluna Data da Venda */
        }

        .total-coluna {
            width: 8%; /* Ajuste para a largura da coluna Total */
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Gerenciar Pedidos</h1>

        <!-- Adicionando o aviso de rolagem para dispositivos móveis -->
        <div class="scroll-hint">Arraste para o lado para ver mais</div>

        <!-- Adicionando contêiner com rolagem horizontal para dispositivos menores -->
        <div class="table-container">
            <table>
                <tr>
                    <th class="id-coluna">ID</th>
                    <th>Cliente</th>
                    <th class="total-coluna">Total</th>
                    <th>Status Pedido</th>
                    <th>Status Pagamento</th>
                    <th class="data-coluna">Data da Venda</th>
                    <th class="acao-coluna">Ação</th>
                </tr>
                <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['cliente']); ?></td>
                    <td>R$<?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>

                    <!-- Coluna de Status do Pedido com dropdown -->
                    <td>
                        <form method="POST" action="processa_pedido.php">
                            <input type="hidden" name="venda_id" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                            <select name="status_pedido" style="width: 100%;">
                                <option value="Pedido Feito" <?php echo ($pedido['status_pedido'] == 'Pedido Feito') ? 'selected' : ''; ?>>Pedido Feito</option>
                                <option value="Em Preparo" <?php echo ($pedido['status_pedido'] == 'Em Preparo') ? 'selected' : ''; ?>>Em Preparo</option>
                                <option value="Saiu para Entrega" <?php echo ($pedido['status_pedido'] == 'Saiu para Entrega') ? 'selected' : ''; ?>>Saiu para Entrega</option>
                                <option value="Entregue" <?php echo ($pedido['status_pedido'] == 'Entregue') ? 'selected' : ''; ?>>Entregue</option>
                            </select>
                            <button type="submit" name="atualizar_status">Atualizar</button>
                        </form>
                    </td>

                    <!-- Coluna de Status de Pagamento com dropdown -->
                    <td>
                        <?php if ($pedido['status'] == 'Pendente' || $pedido['status'] == 'Pago (Dinheiro)'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="venda_id" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                                <select name="status_pagamento" style="width: 100%;">
                                    <option value="Pendente" <?php echo ($pedido['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="Pago (Dinheiro)" <?php echo ($pedido['status'] == 'Pago (Dinheiro)') ? 'selected' : ''; ?>>Pago em Dinheiro</option>
                                </select>
                                <button type="submit" name="atualizar_pagamento">Atualizar</button>
                            </form>
                        <?php else: ?>
                            <?php echo htmlspecialchars($pedido['status']); ?>
                        <?php endif; ?>
                    </td>

                    <td><?php echo htmlspecialchars($pedido['data_venda']); ?></td>
                    <td class="acao-coluna">
                        <!-- Botão Emitir Cupom -->
                        <form action="emitir_cupom.php" method="post" style="display:inline;">
                            <input type="hidden" name="venda_id" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                            <button type="submit">Emitir Cupom</button>
                        </form>

                        <!-- Botão para Cancelar a Venda -->
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="cancelar_venda_id" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                            <button type="submit" style="background-color: #e60000; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; margin-top: 5px; margin-bottom: 5px; transition: background-color 0.3s ease; width: 100%;" onmouseover="this.style.backgroundColor='#ff4c4c';" onmouseout="this.style.backgroundColor='#e60000';" onclick="return confirm('Tem certeza que deseja cancelar esta venda?');">Cancelar Venda</button>

                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <p><a href="index.php">Voltar ao Painel</a></p>
    </div>
</body>
</html>

<?php 
// Incluir o footer.php
include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/footer-ad.php'; 
?>

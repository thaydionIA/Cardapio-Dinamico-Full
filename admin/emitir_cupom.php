<?php
// Iniciar a sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conectar ao banco de dados
require_once '../db/conexao.php';

// Verificar se o venda_id foi enviado via POST
$venda_id = isset($_POST['venda_id']) ? intval($_POST['venda_id']) : 0;

if ($venda_id == 0) {
    echo "Pedido não encontrado.";
    exit();
}

// Consultar as informações do pedido e do cliente
$stmt = $pdo->prepare("
    SELECT v.id AS pedido_id, v.total, v.data_venda, u.nome AS cliente_nome, 
           e.rua, e.numero, e.complemento, e.bairro, e.cidade, e.estado, e.cep
    FROM vendas v
    JOIN usuarios u ON v.cliente_id = u.id
    JOIN enderecos_entrega e ON u.id = e.usuario_id
    WHERE v.id = :venda_id
");
$stmt->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);
$stmt->execute();
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "Pedido não encontrado.";
    exit();
}

// Consultar os itens do pedido na tabela itens_venda
$stmt_itens = $pdo->prepare("
    SELECT p.nome AS produto_nome, iv.quantidade, iv.preco
    FROM itens_venda iv
    JOIN produtos p ON iv.produto_id = p.id
    WHERE iv.venda_id = :venda_id
");
$stmt_itens->bindParam(':venda_id', $venda_id, PDO::PARAM_INT);
$stmt_itens->execute();
$itens_pedido = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

// Incluir o header-ad.php
include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/header-ad.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nome Reservado para Cliente</title>
    <!-- Puxando o arquivo de estilos externos -->
    <link rel="stylesheet" href="/cardapio-dinamico/assets/css/admin_style.css">
    <style>
        /* Estilos que serão aplicados apenas na impressão */
        @media print {
            /* Ocultar cabeçalho, rodapé, botão de impressão e outros elementos indesejados na impressão */
            header, .logo, .menu, .breadcrumb, footer, #printButton {
                display: none !important; /* Esconder o cabeçalho, rodapé e o botão de impressão */
            }

            /* Ajustes para a impressão */
            body {
                font-size: 12px; /* Aumentar um pouco o tamanho da fonte */
                width: 58mm; /* Largura padrão para impressoras de cupom */
                background-color: white; /* Fundo branco para impressão */
                color: black; /* Texto preto para impressão */
                margin: 0;
                padding: 0;
            }

            .cupom {
                padding: 5px;
                width: 100%;
            }

            .cupom h1 {
                text-align: center;
                font-size: 14px; /* Aumentar um pouco o tamanho do título */
                margin-bottom: 5px;
            }

            .itens {
                margin-bottom: 10px;
            }

            .itens table {
                width: 100%;
                border-collapse: collapse;
            }

            .itens th, .itens td {
                text-align: left;
                padding: 3px 0; /* Aumentar ligeiramente o padding */
                font-size: 12px; /* Ajustar o tamanho da fonte */
            }

            .total {
                text-align: center; /* Centralizar o total */
                margin-top: 10px;
                font-size: 13px; /* Aumentar um pouco o tamanho do total */
            }

            .total strong {
                display: inline-block;
                width: 100%;
                text-align: center;
            }

            .endereco {
                font-size: 12px; /* Aumentar o tamanho da fonte para o endereço */
                margin-top: 10px;
            }

            .footer {
                text-align: center;
                margin-top: 10px;
                font-size: 12px; /* Aumentar o tamanho da fonte */
            }

            /* Reduzir margens para otimizar espaço */
            @page {
                margin: 0;
            }
        }

        /* Estilo do botão de impressão */
        #printButton {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        #printButton:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="cupom">
        <h1>Nome Reservado para Cliente</h1> <!-- Título ajustado -->
        <p><strong>Pedido ID:</strong> <?php echo htmlspecialchars($pedido['pedido_id']); ?></p>
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?></p>
        <p><strong>Data:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($pedido['data_venda']))); ?></p>

        <h2>Itens:</h2>
        <div class="itens">
            <table>
                <tr>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>Preço</th>
                </tr>
                <?php foreach ($itens_pedido as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantidade']); ?></td>
                    <td>R$<?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="total">
            <strong>Total:</strong> R$<?php echo number_format($pedido['total'], 2, ',', '.'); ?>
        </div>

        <h2>Endereço de Entrega:</h2>
        <div class="endereco">
            <p><strong>Rua:</strong> <?php echo htmlspecialchars($pedido['rua']); ?></p>
            <p><strong>Número:</strong> <?php echo htmlspecialchars($pedido['numero']); ?></p>
            <?php if (!empty($pedido['complemento'])): ?>
                <p><strong>Complemento:</strong> <?php echo htmlspecialchars($pedido['complemento']); ?></p>
            <?php endif; ?>
            <p><strong>Bairro:</strong> <?php echo htmlspecialchars($pedido['bairro']); ?></p>
            <p><strong>Cidade:</strong> <?php echo htmlspecialchars($pedido['cidade']); ?> - <?php echo htmlspecialchars($pedido['estado']); ?></p>
            <p><strong>CEP:</strong> <?php echo htmlspecialchars($pedido['cep']); ?></p>
        </div>

        <div class="footer">
            <p>Obrigado pela compra!</p>
        </div>

        <!-- Botão de impressão -->
        <button id="printButton" onclick="window.print();">Imprimir Cupom</button>
    </div>

</body>
</html>

<?php 
// Incluir o footer-ad.php (para exibir no navegador, mas será oculto na impressão)
include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/footer-ad.php'; 
?>

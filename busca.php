<?php
include 'header.php'; // Inclui o header

// Caminho correto para o arquivo de conexão
require_once $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/db/conexao.php';

$base_url = '/cardapio-dinamico/admin/uploads/produtos/';
$termo_busca = isset($_GET['q']) ? $_GET['q'] : ''; // Obtém o termo de busca

// Consulta para buscar produtos com base no termo de pesquisa
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE nome LIKE :termo OR descricao LIKE :termo");
$stmt->bindValue(':termo', '%' . $termo_busca . '%');
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca por Produtos</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>"> <!-- Cache busting -->
    <style>
        .mensagem-sucesso {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .mensagem-sucesso.mostrar {
            display: block;
            opacity: 1;
        }
    </style>
</head>
<body>
    <h1>Resultados da busca para: "<?php echo htmlspecialchars($termo_busca); ?>"</h1>
    <div class="produtos-container">
        <?php if (count($produtos) > 0): ?>
            <?php foreach ($produtos as $produto): ?>
                <div class="produto-item">
                    <div class="produto-imagem">
                        <?php if ($produto['imagem']): ?>
                            <!-- Caminho absoluto para a imagem -->
                            <img src="<?php echo $base_url . htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="produto-info">
                        <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p><?php echo htmlspecialchars($produto['descricao']); ?></p>
                        <p class="preco">Preço: R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>

                        <!-- Formulário para adicionar ao carrinho -->
                        <form class="adicionar-carrinho-form">
                            <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">

                            <!-- Campo de quantidade com ícones de aumentar e diminuir -->
                            <div class="quantidade-container">
                                <button type="button" class="quantidade-btn diminuir">-</button>
                                <input type="number" name="quantidade" value="1" min="1" required class="quantidade-input">
                                <button type="button" class="quantidade-btn aumentar">+</button>
                            </div>

                            <!-- Botão de adicionar ao carrinho com a classe CSS -->
                            <button type="button" class="adicionar-carrinho-btn">Adicionar ao Carrinho</button>
                        </form>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum produto encontrado para o termo "<?php echo htmlspecialchars($termo_busca); ?>"</p>
        <?php endif; ?>
    </div>

    <!-- Mensagem de sucesso -->
    <div id="mensagemSucesso" class="mensagem-sucesso">Produto adicionado ao carrinho com sucesso!</div>

    <!-- Inclusão do rodapé -->
    <?php include 'footer.php'; ?>

    <!-- Inclui o script.js -->
    <script src="assets/js/script.js"></script>


</body>
</html>

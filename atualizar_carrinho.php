<?php
session_start();
header('Content-Type: application/json'); // Define o cabeçalho para JSON

require_once 'db/conexao.php'; // Inclua a conexão com o banco de dados

// Inicializa a quantidade total de itens
$totalItens = 0;

// Verifica se o usuário está logado e tem um ID válido
if (isset($_SESSION['user_id'])) {
    $usuario_id = $_SESSION['user_id'];

    // Busca os itens do carrinho do banco de dados para o usuário logado
    try {
        $stmt = $pdo->prepare("SELECT produto_id, quantidade FROM carrinho WHERE usuario_id = :usuario_id");
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $carrinho_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Atualiza a sessão do carrinho com os dados do banco
        foreach ($carrinho_db as $item) {
            // Se o produto está na sessão, use o maior valor entre banco e sessão
            if (isset($_SESSION['carrinho'][$item['produto_id']])) {
                $_SESSION['carrinho'][$item['produto_id']] = max($_SESSION['carrinho'][$item['produto_id']], $item['quantidade']);
            } else {
                // Caso contrário, adicione o produto do banco na sessão
                $_SESSION['carrinho'][$item['produto_id']] = $item['quantidade'];
            }
        }
    } catch (PDOException $e) {
        // Em caso de erro no banco de dados, exibe a mensagem de erro (pode ser ajustado conforme necessário)
        echo json_encode(['erro' => 'Erro ao buscar o carrinho no banco de dados: ' . $e->getMessage()]);
        exit();
    }
}

// Soma todos os itens da sessão para obter o total
if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $quantidade) {
        $totalItens += $quantidade; // Soma todas as quantidades dos produtos
    }
}

// Retorna a quantidade total de itens no carrinho como JSON
echo json_encode(['totalItens' => $totalItens]);
exit();
?>

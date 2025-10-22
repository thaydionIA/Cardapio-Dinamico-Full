<?php
// Verifica se a sessão não está ativa antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão somente se não estiver ativa
}

require_once 'db/conexao.php'; // Ajuste o caminho conforme necessário

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Retorna uma mensagem de erro em JSON se o usuário não estiver logado
    http_response_code(403); // Define o código de resposta HTTP para 403 (Proibido)
    echo json_encode(['error' => 'Você precisa estar logado para adicionar produtos ao carrinho.']);
    exit; // Interrompe a execução do script
}

// Inicializar o carrinho na sessão, se não estiver já inicializado
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Adicionar produto ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_id']) && isset($_POST['quantidade'])) {
    // Sanitização dos inputs
    $produto_id = filter_var($_POST['produto_id'], FILTER_SANITIZE_NUMBER_INT);
    $quantidade = filter_var($_POST['quantidade'], FILTER_SANITIZE_NUMBER_INT);

    // Validação dos inputs
    if ($produto_id > 0 && $quantidade > 0) {
        // Atualizar a quantidade do produto no carrinho da sessão
        if (array_key_exists($produto_id, $_SESSION['carrinho'])) {
            $_SESSION['carrinho'][$produto_id] += $quantidade;
        } else {
            $_SESSION['carrinho'][$produto_id] = $quantidade;
        }

        // Salvar ou atualizar no banco de dados
        $usuario_id = $_SESSION['user_id']; // ID do usuário obtido da sessão
        try {
            // Verifica se o produto já está no banco para atualizar a quantidade
            $stmt = $pdo->prepare("SELECT quantidade FROM carrinho WHERE usuario_id = :usuario_id AND produto_id = :produto_id");
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':produto_id', $produto_id, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                // Atualizar a quantidade no banco de dados
                $nova_quantidade = $resultado['quantidade'] + $quantidade;
                $stmt_update = $pdo->prepare("UPDATE carrinho SET quantidade = :quantidade WHERE usuario_id = :usuario_id AND produto_id = :produto_id");
                $stmt_update->bindParam(':quantidade', $nova_quantidade, PDO::PARAM_INT);
                $stmt_update->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmt_update->bindParam(':produto_id', $produto_id, PDO::PARAM_INT);
                $stmt_update->execute();
            } else {
                // Inserir novo produto no carrinho do banco de dados
                $stmt_insert = $pdo->prepare("INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (:usuario_id, :produto_id, :quantidade)");
                $stmt_insert->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmt_insert->bindParam(':produto_id', $produto_id, PDO::PARAM_INT);
                $stmt_insert->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
                $stmt_insert->execute();
            }

            // Recuperar o nome do produto para feedback ao usuário
            $stmt_nome = $pdo->prepare("SELECT nome FROM produtos WHERE id = :id");
            $stmt_nome->bindParam(':id', $produto_id, PDO::PARAM_INT);
            $stmt_nome->execute();
            $produto = $stmt_nome->fetch(PDO::FETCH_ASSOC);

            if ($produto) {
                echo json_encode(['success' => "Produto " . htmlspecialchars($produto['nome']) . " foi adicionado ao carrinho com quantidade: $quantidade."]);
            } else {
                echo json_encode(['error' => 'Produto não encontrado.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Erro ao adicionar produto ao carrinho: ' . htmlspecialchars($e->getMessage())]);
        }
    } else {
        echo json_encode(['error' => 'Dados inválidos fornecidos.']);
    }
} 
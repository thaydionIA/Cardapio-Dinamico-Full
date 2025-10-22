<?php 
// Incluir o header.php somente se o arquivo estiver sendo acessado diretamente
if (basename($_SERVER['PHP_SELF']) == 'index.php') {
    include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/header-ad.php';
}

// Iniciar a sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

echo "Bem-vindo, " . htmlspecialchars($_SESSION['usuario']['nome']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="assets/css/admin_style.css">
    
</head>
<body>
    <div class="admin-container">
        <h1>Painel Administrativo</h1>
        <p><a href="adicionar_produto.php">Adicionar Novo Produto</a></p>
        <p><a href="gerenciar_produtos.php">Gerenciar Produtos</a></p>
        <p><a href="gerenciar_pedidos.php">Gerenciar Pedidos</a></p>
        <p><a href="logout.php">Logout</a></p>
       
    </div>
</body>
</html>
<?php 
// Corrigir o caminho para o footer.php usando um caminho absoluto
include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/footer-ad.php'; 
?>

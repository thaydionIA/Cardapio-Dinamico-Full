<?php
include('db/conexao.php'); // Inclui a conexão com o banco de dados via PDO

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$erro = ''; // Variável para armazenar a mensagem de erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    try {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            header('Location: index.php');
            exit();
        } else {
            $erro = "Email ou senha incorretos.";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao realizar o login: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/cardapio-dinamico/assets/css/login.css?v=<?php echo time(); ?>">
</head>
<body>

<header>
<div style="display: flex; align-items: center; position: relative; width: 100%;">
    <img src="/cardapio-dinamico/path/logo.jpg" alt="Logo do Site" style="height: 60px; margin-right: 15px;">
    <h1 style="position: absolute; left: 50%; transform: translateX(-50%); margin: 0;">Bem-vindo ao Sistema</h1>
</div>
</header>

<main>
    <div class="login-container">
        <h2>Faça seu Login</h2>

        <?php if ($erro): ?>
            <div class="error"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="login-form">
            <div>
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div>
                <label>Senha:</label>
                <input type="password" name="senha" required>
            </div>
            
            <button type="submit">Entrar</button>

            <p class="no-account">Não tem uma conta? <a href="/cardapio-dinamico/cadastro.php">Cadastre-se</a></p>

            <!-- Link para a página de recuperação de senha -->
            <p class="forgot-password"><a href="/cardapio-dinamico/esqueceu_senha.php">Esqueceu a senha?</a></p>
        </form>
    </div>
</main>

<footer>
    <p>&copy; 2024 Sistema de Login</p>
</footer>

</body>
</html>

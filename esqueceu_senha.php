<?php
include('db/conexao.php'); // Inclui a conexão com o banco de dados via PDO

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mensagem = '';
$erros = []; // Array para armazenar os erros específicos dos campos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os campos para validação
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cpf = trim($_POST['cpf']);

    try {
        // Verifica se o email existe
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verifica se o email não corresponde
        if (!$user) {
            $erros[] = 'Campo Email não corresponde com os dados do cadastro.';
        }
        // Verifica se o telefone não corresponde, mesmo que o email não corresponda
        if (!$user || ($user && $user['telefone'] !== $telefone)) {
            $erros[] = 'Campo Telefone não corresponde com os dados do cadastro.';
        }

        // Verifica se o CPF não corresponde, mesmo que o email e o telefone não correspondam
        if (!$user || ($user && $user['cpf'] !== $cpf)) {
            $erros[] = 'Campo CPF não corresponde com os dados do cadastro.';
        }

        // Exibe mensagem única se todos os campos estiverem errados
        if (count($erros) === 3) {
            $mensagem = "Os dados informados não correspondem aos dados do cadastro.";
        } else if (!empty($erros)) {
            // Junta as mensagens de erro
            $mensagem = implode('<br>', $erros);
        } else {
            // Se não houver erros, prossegue para redefinir a senha
            $_SESSION['user_id'] = $user['id'];
            header('Location: redefinir_senha.php'); // Redireciona para a página de redefinição
            exit();
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao processar a solicitação: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueceu a Senha</title>
    <link rel="stylesheet" href="/cardapio-dinamico/assets/css/style.css"> <!-- Incluindo seu CSS principal -->
</head>
<body>

<header>
<div style="display: flex; align-items: center; position: relative; width: 100%;">
    <img src="/cardapio-dinamico/path/logo.jpg" alt="Logo do Site" style="height: 60px; margin-right: 15px;">
    <h1 style="position: absolute; left: 50%; transform: translateX(-50%); margin: 0;">Recuperar Senha</h1>
</div>
   
</header>

<main>
    <div class="login-container">
        <h2>Verifique sua Identidade</h2>

        <?php if ($mensagem): ?>
            <div class="error"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <form action="esqueceu_senha.php" method="POST" class="login-form">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="input-field" placeholder="Ex: exemplo@dominio.com" required>
            </div>
            <div>
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone" maxlength="9" pattern="\d{9}" class="input-field" placeholder="Ex: 999999999" required>
            </div>
            <div>
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" maxlength="11" pattern="\d{11}" class="input-field" placeholder="Ex: 12345678909" required>
            </div>
            <button type="submit">Verificar</button>
        </form>
    </div>
</main>

<script>
document.querySelector('input[name="cpf"]').addEventListener('input', function (e) {
    this.value = this.value.replace(/\D/g, ''); // Remove tudo que não for número
    if (this.value.length > 11) {
        this.value = this.value.slice(0, 11); // Limita a 11 dígitos
    }
});
document.querySelector('input[name="telefone"]').addEventListener('input', function (e) {
    this.value = this.value.replace(/\D/g, ''); // Remove tudo que não for número
    if (this.value.length > 9) {
        this.value = this.value.slice(0, 9); // Limita a 9 dígitos
    }
});
</script>

<footer>
    <p>&copy; 2024 Sistema de Login</p>
</footer>

</body>
</html>

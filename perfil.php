<?php
include('db/conexao.php'); // Inclui a conexão com o banco de dados via PDO
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtém os dados do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtém o endereço de entrega
$sql_endereco = "SELECT * FROM enderecos_entrega WHERE usuario_id = :usuario_id";
$stmt_endereco = $pdo->prepare($sql_endereco);
$stmt_endereco->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
$stmt_endereco->execute();
$endereco = $stmt_endereco->fetch(PDO::FETCH_ASSOC);

// Atualiza os dados do perfil e endereço
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_perfil'])) {
    // Inicia a consulta SQL para atualizar o perfil
    $sql = "UPDATE usuarios SET ";
    $params = [];
    
    // Verifica e adiciona cada campo que foi preenchido no formulário
    if (!empty($_POST['nome'])) {
        $sql .= "nome = :nome, ";
        $params[':nome'] = $_POST['nome'];
    }

    if (!empty($_POST['email'])) {
        $sql .= "email = :email, ";
        $params[':email'] = $_POST['email'];
    }

    if (!empty($_POST['cpf'])) {
        $sql .= "cpf = :cpf, ";
        $params[':cpf'] = $_POST['cpf'];
    }

    if (!empty($_POST['telefone'])) {
        $sql .= "telefone = :telefone, ";
        $params[':telefone'] = $_POST['telefone'];
    }

    if (!empty($_POST['dd'])) {
        $sql .= "dd = :dd, ";
        $params[':dd'] = $_POST['dd'];
    }

    // Se uma nova senha for inserida, adiciona a senha
    if (!empty($_POST['senha']) && $_POST['senha'] === $_POST['confirmar_senha']) {
        $sql .= "senha = :senha, ";
        $params[':senha'] = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    }

    // Se uma nova foto for enviada, processa o upload
    if (!empty($_FILES['foto']['name'])) {
        $foto = basename($_FILES['foto']['name']);
        $target_dir = "uploads/clientes/";
        $target_file = $target_dir . $foto;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_file);
        $sql .= "foto = :foto, ";
        $params[':foto'] = $foto;
    }

    // Remove a última vírgula da consulta SQL
    $sql = rtrim($sql, ", ");
    $sql .= " WHERE id = :id";
    $params[':id'] = $user_id;

    // Executa a consulta de atualização
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        // Atualiza o endereço apenas se o usuário preencher os campos de endereço
        $sql_endereco = "UPDATE enderecos_entrega SET ";
        $params_endereco = [];

        if (!empty($_POST['rua'])) {
            $sql_endereco .= "rua = :rua, ";
            $params_endereco[':rua'] = $_POST['rua'];
        }

        if (!empty($_POST['numero'])) {
            $sql_endereco .= "numero = :numero, ";
            $params_endereco[':numero'] = $_POST['numero'];
        }

        if (!empty($_POST['complemento'])) {
            $sql_endereco .= "complemento = :complemento, ";
            $params_endereco[':complemento'] = $_POST['complemento'];
        }

        if (!empty($_POST['bairro'])) {
            $sql_endereco .= "bairro = :bairro, ";
            $params_endereco[':bairro'] = $_POST['bairro'];
        }

        if (!empty($_POST['cidade'])) {
            $sql_endereco .= "cidade = :cidade, ";
            $params_endereco[':cidade'] = $_POST['cidade'];
        }

        if (!empty($_POST['estado'])) {
            $sql_endereco .= "estado = :estado, ";
            $params_endereco[':estado'] = $_POST['estado'];
        }

        if (!empty($_POST['cep'])) {
            $sql_endereco .= "cep = :cep, ";
            $params_endereco[':cep'] = $_POST['cep'];
        }

        // Remove a última vírgula da consulta SQL de endereço
        $sql_endereco = rtrim($sql_endereco, ", ");
        $sql_endereco .= " WHERE usuario_id = :usuario_id";
        $params_endereco[':usuario_id'] = $user_id;

        // Executa a consulta de atualização do endereço, se necessário
        if (!empty($params_endereco)) {
            $stmt_endereco = $pdo->prepare($sql_endereco);
            if ($stmt_endereco->execute($params_endereco)) {
                $_SESSION['user_nome'] = $_POST['nome'];
                header('Location: perfil.php');
                exit();
            } else {
                echo "Erro ao atualizar o endereço.";
            }
        } else {
            $_SESSION['user_nome'] = $_POST['nome'];
            header('Location: perfil.php');
            exit();
        }
    } else {
        echo "Erro ao atualizar o perfil.";
    }
}

// Processar a exclusão do perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_perfil'])) {
    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        session_destroy();
        header('Location: login.php');
        exit();
    } else {
        echo "Erro ao excluir o perfil.";
    }
}

$incluir_rodape = !isset($GLOBALS['incluir_rodape']) || $GLOBALS['incluir_rodape'];

// Incluir o cabeçalho
include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="assets/css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="perfil-container">
        <h2>Meu Perfil</h2>
        <form class="perfil-form" action="perfil.php" method="POST" enctype="multipart/form-data">
            <div class="foto-container">
                <label for="foto-upload" class="foto-label">
                    <img class="perfil-foto" src="uploads/clientes/<?= htmlspecialchars($user['foto']); ?>" alt="Foto de Perfil">
                    <input type="file" id="foto-upload" name="foto" accept="image/*" style="display: none;">
                    <i class="fa-solid fa-camera icon-camera"></i> <!-- Ícone de câmera no canto inferior direito -->
                </label>
            </div>
            
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($user['nome']); ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>">

            <label>CPF:</label>
            <input type="text" name="cpf" value="<?= htmlspecialchars($user['cpf']); ?>">

            <label>DDD:</label>
            <input type="text" name="dd" value="<?= htmlspecialchars($user['dd']); ?>">

            <label>Telefone:</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($user['telefone']); ?>">

            <h3>Endereço de Entrega</h3>
            <label>Rua:</label>
            <input type="text" name="rua" value="<?= htmlspecialchars($endereco['rua']); ?>">

            <label>Número:</label>
            <input type="text" name="numero" value="<?= htmlspecialchars($endereco['numero']); ?>">

            <label>Complemento:</label>
            <input type="text" name="complemento" value="<?= htmlspecialchars($endereco['complemento']); ?>">

            <label>Bairro:</label>
            <input type="text" name="bairro" value="<?= htmlspecialchars($endereco['bairro']); ?>">

            <label>Cidade:</label>
            <input type="text" name="cidade" value="<?= htmlspecialchars($endereco['cidade']); ?>">

            <label>Estado:</label>
            <input type="text" name="estado" value="<?= htmlspecialchars($endereco['estado']); ?>" maxlength="2">

            <label>CEP:</label>
            <input type="text" name="cep" value="<?= htmlspecialchars($endereco['cep']); ?>">

            <h3>Alterar Senha</h3>
            <label>Nova Senha:</label>
            <input type="password" name="senha">
            
            <label>Confirmar Senha:</label>
            <input type="password" name="confirmar_senha">
            
            <button type="submit" name="atualizar_perfil">Atualizar Perfil</button>
        </form>

        <form action="perfil.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir seu perfil?');">
            <button type="submit" name="excluir_perfil" class="btn-excluir">Excluir Perfil</button>
        </form>
    </div>

    <?php if ($incluir_rodape): ?>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/cardapio-dinamico/footer.php'; ?>
    <?php endif; ?>
     <!-- Inclui o arquivo de JavaScript centralizado -->
     <script src="assets/js/script.js"></script>
</body>
</html>

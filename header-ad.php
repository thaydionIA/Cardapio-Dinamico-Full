<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - <?php echo $site_name; ?></title>
    <link rel="stylesheet" href="/cardapio-dinamico/assets/css/style.css">
    <style>
    /* Estilos principais para desktop */
    header {
        background-color: <?php echo $primary_color; ?>;
        display: flex; 
        align-items: center;
        justify-content: space-between;
        padding: 10px;
    }

    .logo-container {
        display: flex;
        align-items: center;
    }

    .logo-container img {
        width: 80px;
        height: auto;
        margin-right: 10px;
    }

    .logo-container h1 {
        color: #d4af37;
        font-size: 24px;
        margin: 0;
    }

    .return-button-container {
        display: flex; 
        align-items: center;
        margin-right: 200px; /* Mantido o valor conforme desktop */
    }

    .return-button {
        text-decoration: none;
        color: white;
        background-color: #f0d28b;
        padding: 10px 20px;
        border-radius: 5px;
    }

    /* Estilos para dispositivos menores */
    @media (max-width: 768px) {
        header {
            flex-direction: column; /* Empilha os elementos verticalmente em telas menores */
            align-items: flex-start; /* Alinha tudo à esquerda */
        }

        .logo-container {
            width: 100%; 
            justify-content: flex-start; /* Alinha o logo e o nome à esquerda */
        }

        .logo-container h1 {
            text-align: left; /* Garante que o nome do site fique à esquerda */
            font-size: 20px; /* Reduz o tamanho da fonte para dispositivos menores, se necessário */
        }

        .return-button-container {
            margin-right: 0;
            width: 100%; 
            justify-content: center;
            margin-top: 10px;
        }

        .return-button {
            width: 50%; /* Diminui a largura do botão para 50% em dispositivos menores */
            text-align: center;
        }

        /* Prevenção de rolagem lateral */
        html, body {
            overflow-x: hidden;
        }
    }

    </style>
</head>
<body>

<header>
    <div class="logo-container">
        <img src="../path/logo.jpg" alt="Logo do Cliente" class="logo">
        <h1><?php echo $site_name; ?></h1>
    </div>
    <!-- Botão para retornar ao index principal -->
    <div class="return-button-container">
        <a href="/cardapio-dinamico/admin/index.php" class="return-button">Retornar ao Início</a>
    </div>
</header>

<main>

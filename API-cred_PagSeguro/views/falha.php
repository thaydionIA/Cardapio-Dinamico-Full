<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Falhou</title>
    <link rel="stylesheet" href="/cardapio-dinamico/assets/css/style.css">
    <style>
        /* Estilo para os botões */
        .btn-voltar, .btn-tentar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #d4af37; /* Cor dourada */
            color: #1c1c1c; /* Cor escura para o texto */
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover, .btn-tentar:hover {
            background-color: #ecbe54; /* Cor mais clara ao passar o mouse */
        }

        .btn-container {
            text-align: center; /* Centralizar o botão */
            margin-top: 20px;
        }

        /* Estilo da mensagem de falha */
        .banner {
            background-color: #f0d28b; /* Mesma cor de fundo do tema do carrinho */
            color: #d4af37; /* Texto dourado */
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .banner h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .banner p {
            font-size: 16px;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: #d4af37;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; color: #d4af37;">Pagamento Falhou!</h1>
    </header>

    <main>
        <div class="banner">
            <h2>Houve um problema com o seu pagamento.</h2>
            <p>Infelizmente, não conseguimos processar o pagamento. Por favor, tente novamente ou entre em contato com o suporte para mais assistência.</p>
        </div>

        <!-- Botões para tentar novamente ou voltar à página inicial -->
        <div class="btn-container">
            <a href="/cardapio-dinamico/API-cred_PagSeguro/views/index.php" class="btn-tentar">Tentar Novamente</a>
            <a href="/cardapio-dinamico/index.php" class="btn-voltar">Voltar para a Página Inicial</a>
        </div>
    </main>

    <footer>
        <p>© 2024 Seu Site - Todos os direitos reservados.</p>
    </footer>
</body>
</html>

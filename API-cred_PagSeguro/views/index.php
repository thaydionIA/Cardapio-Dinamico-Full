<?php
include('../controllers/KeyController.php');
$objKey = new KeyController();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pega parâmetros da URL
$valor = $_GET['valor'] ?? 0;
$user_id = $_GET['user_id'] ?? 0;
$forma_pagamento = $_GET['forma_pagamento'] ?? 'credito';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Seguro | Cardápio Dinâmico</title>

    <!-- Bootstrap + ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Estilo separado -->
    <link rel="stylesheet" href="../assets/css/pagamento.css">
</head>
<body>

<form method="post" id="formCard" action="../controllers/PayController.php" onsubmit="return encryptCard();">
    <div class="payment-card">
        <div class="loading-overlay" id="loading">
            <div>
                <i class="bi bi-shield-lock-fill"></i>
                <p>Criptografando cartão...</p>
            </div>
        </div>

        <h2><i class="bi bi-credit-card-2-front"></i> Pagamento Seguro</h2>

        <p class="valor-compra">
            Total a pagar: <strong>R$ <?= number_format($valor, 2, ',', '.') ?></strong>
        </p>

        <div class="bandeiras">
            <img src="../assets/img/bandeiras/visa.svg" alt="Visa">
           <img src="../assets/img/bandeiras/elo.svg" alt="Elo">
           <img src="../assets/img/bandeiras/mastercard.svg" alt="MasterCard">
           <img src="../assets/img/bandeiras/hipercard.svg" alt="Hipercard">
           <img src="../assets/img/bandeiras/amex.svg" alt="Amex">
        </div>

        <input type="hidden" name="finalizar_compra" value="1">
        <input type="hidden" name="encriptedCard" id="encriptedCard">
        <input type="hidden" name="valor_total" value="<?= htmlspecialchars($valor) ?>">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
        <input type="hidden" name="forma_pagamento" value="<?= htmlspecialchars($forma_pagamento) ?>">

        <input type="text" class="input-field" name="cardNumber" id="cardNumber" maxlength="16" placeholder="Número do Cartão" required>
        <input type="text" class="input-field" name="cardHolder" id="cardHolder" placeholder="Nome no Cartão" required>

        <div class="d-flex gap-2">
            <input type="text" class="input-field" name="cardMonth" id="cardMonth" maxlength="2" placeholder="MM" required>
            <input type="text" class="input-field" name="cardYear" id="cardYear" maxlength="4" placeholder="AAAA" required>
            <input type="text" class="input-field" name="cardCvv" id="cardCvv" maxlength="4" placeholder="CVV" required>
        </div>

        <button type="submit" class="btn-pay mt-3">
            <i class="bi bi-lock-fill"></i> Pagar com segurança
        </button>

        <div class="brand mt-3">
            <i class="bi bi-credit-card-2-back"></i> PagSeguro • Ambiente Seguro
        </div>
    </div>
</form>

<!-- SDK PagSeguro -->
<script src="https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js"></script>

<!-- Script separado -->
<script>
    const PUBLIC_KEY = '<?php echo $objKey::getPublicKey(); ?>';
</script>
<script src="../assets/js/pagamento.js"></script>

</body>
</html>

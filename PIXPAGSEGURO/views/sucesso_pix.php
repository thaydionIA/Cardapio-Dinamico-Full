<?php
// ==========================================
// ✅ SUCESSO_PIX.PHP — PAGAMENTO PIX
// ==========================================
session_start();
$valor_total = $_GET['valor'] ?? '0,00';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamento Confirmado | PIX</title>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!-- CSS -->
  <link rel="stylesheet" href="../assets/css/sucesso_pix.css">
</head>
<body>
  <div class="success-container">
    <div class="checkmark">
      <svg class="checkmark-svg" viewBox="0 0 52 52">
        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
        <path class="checkmark-check" fill="none" d="M14 27l7 7 16-16"/>
      </svg>
    </div>

    <h1>Pagamento realizado com sucesso!</h1>
    <p class="subtitle">Seu pedido foi confirmado e está sendo processado. 💸</p>

    <div class="valor">
      <strong>Total Pago:</strong> R$ <?= htmlspecialchars($valor_total) ?>
    </div>

    <a href="/cardapio-dinamico-full/index.php" class="btn-voltar">
      <i class="bi bi-house-door"></i> Voltar ao Início
    </a>
  </div>

  <footer>
    <p>© <?= date('Y') ?> Cardápio Dinâmico • Todos os direitos reservados.</p>
  </footer>

  <script>
  // Animação de brilho do check
  document.addEventListener("DOMContentLoaded", () => {
    const circle = document.querySelector(".checkmark-circle");
    const check = document.querySelector(".checkmark-check");
    circle.style.strokeDasharray = "166";
    circle.style.strokeDashoffset = "166";
    circle.getBoundingClientRect();
    circle.style.transition = "stroke-dashoffset 0.6s ease-in-out";
    circle.style.strokeDashoffset = "0";
    check.style.strokeDasharray = "48";
    check.style.strokeDashoffset = "48";
    setTimeout(() => (check.style.strokeDashoffset = "0"), 700);
  });
  </script>
</body>
</html>

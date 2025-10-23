/**
 * ==========================================================
 * 💰 MONITOR DE PAGAMENTO PIX (Sandbox)
 * ==========================================================
 */

function copyPixKey() {
  const pixKeyInput = document.getElementById("pixKeyInput").value;
  navigator.clipboard
    .writeText(pixKeyInput)
    .then(() => alert("✅ Chave Pix copiada com sucesso!"))
    .catch((error) => alert("❌ Falha ao copiar a chave Pix: " + error));
}

// 🔍 Verificar status
function checkPaymentStatus(referenceId) {
  fetch("../controllers/verificar_pagamento.php?ref=" + referenceId, {
    method: "GET",
    headers: { "Cache-Control": "no-cache" },
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("🔄 Status atual:", data);

      if (data.status?.toLowerCase() === "pago" || data.status === "PAID") {
        clearInterval(window.statusInterval);
        // Redireciona para página de sucesso personalizada
        window.location.href = "../views/sucesso_pix.php?valor=" + encodeURIComponent(data.valor);
      }
    })
    .catch((error) =>
      console.error("⚠️ Erro ao verificar status do pagamento:", error)
    );
}

// ⏱️ Timer
function startTimer(duration, display) {
  let timer = duration;
  const countdown = setInterval(() => {
    const minutes = String(parseInt(timer / 60, 10)).padStart(2, "0");
    const seconds = String(parseInt(timer % 60, 10)).padStart(2, "0");
    display.textContent = `${minutes}:${seconds}`;

    if (--timer < 0) {
      clearInterval(countdown);
      document.getElementById("pix-status").textContent = "⏰ Tempo expirado";
    }
  }, 1000);
}

// 🚀 Init
document.addEventListener("DOMContentLoaded", () => {
  const display = document.querySelector("#timer");
  if (display) startTimer(600, display);

  const referenceInput = document.getElementById("reference-id");
  if (referenceInput) {
    const referenceId = referenceInput.value;
    window.statusInterval = setInterval(() => {
      checkPaymentStatus(referenceId);
    }, 5000);
  }
});

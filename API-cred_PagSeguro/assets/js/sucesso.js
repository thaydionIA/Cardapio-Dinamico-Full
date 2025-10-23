document.addEventListener("DOMContentLoaded", () => {
  const circle = document.querySelector(".checkmark-circle");
  const check = document.querySelector(".checkmark-check");

  // brilho pulsante após finalização
  setTimeout(() => {
    circle.style.filter = "drop-shadow(0 0 10px #d4af37)";
    check.style.filter = "drop-shadow(0 0 10px #d4af37)";
  }, 800);
});

// Função para copiar a chave Pix para a área de transferência
function copyPixKey() {
    var pixKeyInput = document.getElementById('pixKeyInput').value;
    
    navigator.clipboard.writeText(pixKeyInput).then(function() {
        alert('Chave Pix copiada com sucesso!');
    }).catch(function(error) {
        alert('Falha ao copiar a chave Pix: ' + error);
    });
}

// Função para verificar o status do pagamento periodicamente
function checkPaymentStatus(referenceId) {
    fetch('verificar_pagamento.php?reference_id=' + referenceId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'pago') {
                document.getElementById('pix-status').innerText = 'Pagamento Concluído';
                document.getElementById('pix-status').style.color = 'green';
                document.getElementById('pix-status').style.animation = 'none';
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000); // Redireciona após 2 segundos
            }
        })
        .catch(error => console.error('Erro ao verificar pagamento:', error));
}

// Função para iniciar a contagem regressiva de 10 minutos
function startTimer(duration, display) {
    var timer = duration,
        minutes, seconds;
    var countdown = setInterval(function() {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        display.textContent = minutes + ':' + seconds;

        if (--timer < 0) {
            clearInterval(countdown);
            document.getElementById('pix-status').innerText = 'Pagamento Expirado';
            document.getElementById('pix-status').style.color = 'red';
            document.getElementById('pix-status').style.animation = 'none';
        }
    }, 1000);
}

// Inicia a contagem regressiva e a verificação do pagamento
document.addEventListener('DOMContentLoaded', function() {
    var tenMinutes = 60 * 10, // 10 minutos em segundos
        display = document.querySelector('#timer'); // Seleciona o elemento do timer

    if (display) {
        startTimer(tenMinutes, display); // Inicia o cronômetro
    }

    // Recupera o referenceId do campo oculto
    var referenceIdElement = document.getElementById('reference-id');
    if (referenceIdElement) {
        var referenceId = referenceIdElement.value;
        setInterval(function() {
            checkPaymentStatus(referenceId); // Verifica o status do pagamento a cada 5 segundos
        }, 5000);
    }
});

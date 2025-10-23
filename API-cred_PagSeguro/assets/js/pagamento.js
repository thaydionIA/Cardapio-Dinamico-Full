async function encryptCard() {
    const loader = document.getElementById('loading');
    loader.classList.add('active');

    try {
        const card = PagSeguro.encryptCard({
            publicKey: PUBLIC_KEY,
            holder: document.getElementById('cardHolder').value,
            number: document.getElementById('cardNumber').value,
            expMonth: document.getElementById('cardMonth').value,
            expYear: document.getElementById('cardYear').value,
            securityCode: document.getElementById('cardCvv').value
        });

        if (!card || !card.encryptedCard) {
            alert('Erro ao criptografar o cartão. Tente novamente.');
            loader.classList.remove('active');
            return false;
        }

        document.getElementById('encriptedCard').value = card.encryptedCard;

        // Limpa campos sensíveis
        document.getElementById('cardNumber').value = '';
        document.getElementById('cardCvv').value = '';

        return true;
    } catch (error) {
        console.error('Erro ao criptografar:', error);
        alert('Erro inesperado. Verifique os dados e tente novamente.');
        loader.classList.remove('active');
        return false;
    }
}

(function(win, doc) {
    'use strict'; // Certifique-se de que o 'use strict' esteja correto

    if (doc.querySelector('#formCard')) {
        let formCard = doc.querySelector('#formCard');
        formCard.addEventListener('submit', (e) => {
            e.preventDefault();

            // Certifique-se de que todos os campos têm valores antes de chamar a criptografia
            const publicKey = doc.querySelector('#publicKey').value;
            const holder = doc.querySelector('#cardHolder').value;
            const number = doc.querySelector('#cardNumber').value;
            const expMonth = doc.querySelector('#cardMonth').value;
            const expYear = doc.querySelector('#cardYear').value;
            const securityCode = doc.querySelector('#cardCvv').value;

            if (!publicKey || !holder || !number || !expMonth || !expYear || !securityCode) {
                alert('Todos os campos do cartão devem ser preenchidos corretamente.');
                return;
            }

            try {
                let card = PagSeguro.encryptCard({
                    publicKey: publicKey,
                    holder: holder,
                    number: number,
                    expMonth: expMonth,
                    expYear: expYear,
                    securityCode: securityCode
                });

                // Verifique se a criptografia ocorreu corretamente
                if (!card || !card.encryptedCard) {
                    alert('Erro ao criptografar o cartão. Verifique as informações.');
                    return;
                }

                let encrypted = card.encryptedCard;
                doc.querySelector('#encriptedCard').value = encrypted;
                formCard.submit();
            } catch (error) {
                console.error('Erro ao criptografar o cartão:', error);
                alert('Erro ao criptografar o cartão. Verifique as informações e tente novamente.');
            }
        });
    }
})(window, document);

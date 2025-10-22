// Função para atualizar o contador do carrinho
function atualizarContadorCarrinho() {
    fetch('/cardapio-dinamico/atualizar_carrinho.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor');
            }
            return response.json(); // Converte a resposta em JSON
        })
        .then(data => {
            // Verifica se o JSON contém a propriedade totalItens
            if (data && typeof data.totalItens === 'number') {
                const cartCount = document.getElementById('cart-count');
                if (data.totalItens > 0) {
                    cartCount.textContent = data.totalItens; // Atualiza o contador do carrinho com o número de itens
                    cartCount.classList.remove('hidden'); // Mostra o contador removendo a classe 'hidden'
                } else {
                    cartCount.classList.add('hidden'); // Oculta o contador apenas se o total de itens for zero
                }
            } else {
                console.error('Resposta JSON inesperada:', data); // Loga um erro caso o JSON não tenha a estrutura esperada
            }
        })
        .catch(error => {
            console.error('Erro ao atualizar o contador do carrinho:', error); // Loga qualquer erro ocorrido durante o fetch
        });
}

// Função para limpar event listeners existentes
function limparListeners() {
    document.querySelectorAll('.aumentar, .diminuir, .adicionar-carrinho-btn').forEach(button => {
        const newButton = button.cloneNode(true); // Cria um clone sem event listeners
        button.parentNode.replaceChild(newButton, button); // Substitui o botão original pelo clone
    });
}

// Função para adicionar event listeners
function adicionarListeners() {
    limparListeners(); // Primeiro, limpa todos os event listeners existentes

    // Adiciona eventos aos botões de aumentar e diminuir quantidade
    document.querySelectorAll('.aumentar').forEach(button => {
        button.addEventListener('click', function () {
            const input = this.parentNode.querySelector('.quantidade-input');
            input.value = parseInt(input.value) + 1;
        });
    });

    document.querySelectorAll('.diminuir').forEach(button => {
        button.addEventListener('click', function () {
            const input = this.parentNode.querySelector('.quantidade-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });

    // Adiciona eventos aos botões de adicionar ao carrinho
    document.querySelectorAll('.adicionar-carrinho-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Impede o envio padrão do formulário
            const form = this.closest('.adicionar-carrinho-form');
            const produtoId = form.querySelector('input[name="produto_id"]').value;
            const quantidade = form.querySelector('input[name="quantidade"]').value;

            fetch('/cardapio-dinamico/adicionar_ao_carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    produto_id: produtoId,
                    quantidade: quantidade
                })
            })
            .then(response => {
                if (!response.ok) {
                    // Se o status não for 200, tenta pegar o JSON da resposta para exibir o erro
                    return response.json().then(data => {
                        if (data.error) {
                            alert(data.error); // Exibe o erro retornado pelo servidor
                        }
                        // Lança um erro para interromper o fluxo sem exibir o alerta novamente
                        throw new Error('Erro ao adicionar ao carrinho');
                    });
                }
                return response.json(); // Converte a resposta para JSON se estiver OK
            })
            .then(data => {
                if (data.success) {
                    atualizarContadorCarrinho(); // Atualiza o contador do carrinho após adicionar o produto
                    const mensagem = document.getElementById('mensagemSucesso');
                    mensagem.classList.add('mostrar');
                    setTimeout(() => {
                        mensagem.classList.remove('mostrar');
                    }, 3000); // A mensagem será exibida por 3 segundos
                } else if (data.error) {
                    alert(data.error); // Exibe qualquer erro retornado no JSON de sucesso
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar ao carrinho:', error);
                // Não exibe o alert aqui para evitar duplicidade
            });
        });
    });
}

// Chama a função para adicionar event listeners após o DOM ser carregado
document.addEventListener('DOMContentLoaded', () => {
    adicionarListeners();
    atualizarContadorCarrinho(); // Atualiza o contador do carrinho ao carregar a página
});

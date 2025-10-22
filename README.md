
# Sistema de Cardápio Dinâmico

Este projeto é um sistema de cardápio dinâmico onde os usuários podem visualizar e realizar pedidos de forma simples. O sistema também permite a administração dos pedidos e o gerenciamento do cardápio por um painel administrativo.

## Estrutura do Projeto

- **admin/**: Contém arquivos relacionados ao painel administrativo.
- **API-cred_PagSeguro/**: Contém credenciais para integração com o PagSeguro.
- **assets/**: Recursos como imagens, CSS, JS e fontes.
- **db/**: Arquivos e scripts relacionados ao banco de dados.
- **sections/**: Arquivos que dividem a estrutura do site em seções reutilizáveis.
- **uploads/**: Diretório onde os arquivos carregados são armazenados.
- **Arquivos PHP principais**:
    - `adicionar_ao_carrinho.php`: Responsável por adicionar produtos ao carrinho.
    - `atualizar_carrinho.php`: Atualiza os itens no carrinho de compras.
    - `busca.php`: Permite realizar buscas por produtos no cardápio.
    - `cadastro.php`: Página de cadastro de novos usuários.
    - `carrinho.php`: Exibe o conteúdo do carrinho de compras.
    - `config.php`: Arquivo de configuração do sistema.
    - `index.php`: Página inicial do sistema de cardápio.
    - `login.php`: Página de login para usuários do sistema.
    - `realizar_compra.php`: Responsável por concluir a compra e gerar o pedido.
    - `meus_pedidos.php`: Página onde o usuário pode visualizar os pedidos realizados.
    - Outros arquivos PHP são responsáveis por funcionalidades auxiliares como redefinir senha, perfil de usuário, etc.

## Requisitos

- PHP 7.0 ou superior
- MySQL ou outro banco de dados compatível
- Configuração do ambiente de pagamento via PagSeguro (verificar a pasta `API-cred_PagSeguro`)

## Como executar

1. Clone o repositório em sua máquina local:
    ```bash
    git clone <URL_DO_REPOSITORIO>
    ```
2. Configure o banco de dados conforme o arquivo `db/`.
3. Ajuste as credenciais do PagSeguro no arquivo `API-cred_PagSeguro`.
4. Acesse a aplicação em um servidor local com PHP:
    ```bash
    php -S localhost:8000
    ```

## Licença

Este projeto está licenciado sob a [Licença XYZ].


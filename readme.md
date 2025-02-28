# Projeto API - Tuddo

Este projeto é uma API construída com o **Slim Framework** e utiliza **Firebase** para integração de autenticação e funções. Ele também tem configuração de envio de e-mails via SMTP.

## Pré-requisitos

Antes de rodar o projeto, você precisa garantir que tenha as seguintes ferramentas instaladas:

-   **PHP** (versão 7.4 ou superior)
-   **Composer** (gerenciador de dependências PHP)
-   **Firebase Service Account JSON** para integração com o Firebase

### 1. Instalar o PHP

Certifique-se de que o **PHP** esteja instalado. Você pode verificar isso com o comando:

bash

CopyEdit

`php -v` 

Caso não tenha o PHP instalado, [clique aqui](https://www.php.net/manual/pt_BR/install.php) para aprender como instalá-lo.

### 2. Instalar o Composer

O **Composer** é necessário para gerenciar as dependências do projeto. Verifique se o **Composer** está instalado com:

bash

CopyEdit

`composer -v` 

Caso não tenha o Composer instalado, você pode seguir as instruções aqui.

## Configuração do Projeto

1.  **Clone o repositório**
    
    Clone este repositório para o seu ambiente local:
    
2.  **Instale as dependências do Composer**
    
    Execute o seguinte comando para instalar as dependências do projeto:
    
    `composer install` 
    
3.  **Configuração do arquivo `.env`**
    
    Copie o arquivo `.env.example` para `.env`:
    
    
    `cp .env.example .env` 
    
    O arquivo `.env` contém configurações sensíveis e de ambiente, como tokens e credenciais de e-mail. Certifique-se de preencher corretamente os valores abaixo.
    
    **Exemplo de arquivo `.env`:**
    
    
    ```
    JWT_TOKEN=seu_jwt_token_aqui
    MAIL_HOST=smtp.seu-email.com
    MAIL_USER=usuario@seu-email.com
    MAIL_PASS=sua_senha_aqui
    MAIL_FROM=tuddo@tuddo.org
    MAIL_FROM_NAME=Tuddo
    MAIL_PORT=587
    ```
    
4.  **Adicionar o arquivo `firebase-service-account.json`**
    
    Para usar a autenticação e funções do Firebase, você precisa de um arquivo **JSON de credenciais do Firebase**. O arquivo pode ser gerado no **Console do Firebase**:
    
    1.  Vá para o Console do Firebase.
    2.  No menu de navegação à esquerda, clique em **Configurações do Projeto** > **Contas de Serviço**.
    3.  Clique em **Gerar nova chave privada**.
    4.  O arquivo JSON será baixado. Salve-o como `firebase-service-account.json` no diretório raiz do projeto.
5.  **Carregar variáveis de ambiente**
    
    O projeto usa a biblioteca `vlucas/phpdotenv` para carregar as variáveis de ambiente do arquivo `.env`. Não há necessidade de configuração extra, apenas certifique-se de que o arquivo `.env` esteja corretamente configurado.
    
6.  **Rodar o servidor**
    
    Com o **PHP** e as dependências configuradas, você pode rodar o servidor embutido do PHP (para testes locais):
    
    bash
    
    CopyEdit
    
    `php -S localhost:8000` 
    
    Agora, sua aplicação estará disponível em http://localhost:8000.
    

----------

## Estrutura do Projeto

-   **`routes/`**: Define as rotas da sua aplicação.
-   **`utils/`**: Funções utilitárias, como o envio de e-mails.
- **`bootstrap.php`**: inicialização do slim e do firebase
- **`index.php`**: inicia as rotas
-   **`.env`**: Arquivo de configuração de variáveis de ambiente.
-   **`firebase-service-account.json`**: Arquivo de credenciais do Firebase.

----------

## Como Funciona

### 1. **Autenticação com Firebase**

O projeto integra a autenticação via **Firebase**. Ao chamar endpoints de autenticação, o **JWT** gerado pelo Firebase é usado para validar a identidade do usuário. A validação do token pode ser feita nas rotas protegidas do projeto.

### 2. **Envio de E-mail**

O projeto usa o SMTP para enviar e-mails (como de confirmação de exclusão de conta). As credenciais de e-mail são configuradas no arquivo `.env`.
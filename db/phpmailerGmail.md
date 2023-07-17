# 1. Instalação do phpmailer no projeto:

### 1.1 Instalação do composer: https://getcomposer.org/

- O Composer é uma ferramenta de gerenciamento de dependências para o PHP. Ele fornece uma maneira conveniente de gerenciar as bibliotecas e pacotes que um projeto PHP utiliza. Com o Composer, é possível definir as dependências do seu projeto em um arquivo chamado "composer.json" e o Composer se encarrega de baixar, instalar e atualizar essas dependências de forma automatizada.

#### Foi a melhor maneira que encontrei de instalar todas as dependências necessárias com menos esforçlo possível. Ou seja, tenha o composer instalado na sua máquina.

#### 2. Com o composer instalado, abra o seu projeto e na raíz dê o seguinte comando:

`composer require phpmailer/phpmailer`

- Verifique que na raíz do seu projeto será criado os seguintes arquivos:
  -> Um diretório chamado vendor;
  -> Um arquivo chamado composer.json;
  -> Um arquivo chamado composer.lock;

#### 3. Para utilização do Gmail como provedor, abra o arquivo composer.json e insira as seguintes dependências:

```
"league/oauth2-google": "^4.0",
"ext-openssl": "*"
```

- Seu composer.json deverá ficar assim:

```
{
    "require": {
        "phpmailer/phpmailer": "^6.8",
        "league/oauth2-google": "^4.0",
        "ext-openssl": "*"
    }
}

```

OBS: Se vc quiser outro provedor para a autenticação, verifique as sugestões no arquivo composer.lock em "suggest":

```
"suggest": {
                "ext-mbstring": "Needed to send email in multibyte encoding charset or decode encoded addresses",
                "ext-openssl": "Needed for secure SMTP sending and DKIM signing",
                "greew/oauth2-azure-provider": "Needed for Microsoft Azure XOAUTH2 authentication",
                "hayageek/oauth2-yahoo": "Needed for Yahoo XOAUTH2 authentication",
                "league/oauth2-google": "Needed for Google XOAUTH2 authentication",
                "psr/log": "For optional PSR-3 debug logging",
                "symfony/polyfill-mbstring": "To support UTF-8 if the Mbstring PHP extension is not enabled (^1.2)",
                "thenetworg/oauth2-azure": "Needed for Microsoft XOAUTH2 authentication"
            },

```

### 4. Após a inserção das dependências dê o seguinte comando no terminal:

```
composer require ext-openssl league/oauth2-google
```

### 5. Pronto, agora é fazer algumas configurações, a primeira delas é apontar corretamente os diretórios. Nos seus scripts, verifique os caminhos apontados nos requires, principalmente do autoload.php (arquivo presente no diretório vendor), pois este arquivo é o que irá orquestar o script.

### 6. Configuração do oAuth: obtendo as credenciais:

#### 6.1 Abra o google clod console:

- 6.1.1 Crie um projeto;
- 6.1.2 Vá em credenciais:
  -> 6.1.2.1 Configurar tela de consentimento;
  -> -> nesta área você irá preencher várias informações sobre a sua aplicação;

- 6.1.3 Agora sim,vá em credenciais e clique em criar credenciais:
  -> 6.1.3.1 Selecione Criar ID do cliente OAuth;
  -> Tipo de Aplicativo: Conforme sua necessidade - Se for uma página web: Aplicativo da Web;
  -> Insira o nome da sua aplicação;
  -> Em URIs de redirecionamento autorizados, crie em Adicionar URI e então copie o caminho da sua url até o arquivo get_oauth_token.php;
  Exemplo: https://seudominio/seu_caminho/db/vendor/phpmailer/phpmailer/get_oauth_token.php

  -> Clique em Criar;
  Pronto, agora você tem o ID do Cliente e a Chave Secreta do Cliente. São dois identificadores que ficarão disponíveis no console do seu projeto;
  Você inseri-los no script para envio de email utilizando o gmail como seu provedor, conforme o exemplo abaixo:

```
$clientId = '375481534178-ojpdt8jqev3cj8bjkgzbjcfq4tbco30m.abuild.googleusercontent.com';
```

// exemplo ilustrativo de codigo clientID - valor gerado no google cloud console - você que deve fornecer;

```
 $clientSecret = 'ZOFTPX-2Cwf1iLUPUWtUzGBIYG7xAly7jV5';
```

// exemplo ilustrativo de codigo clientSecret - valor gerado no google cloud console - você que deve fornecer

```
$refreshToken = '3//55JnP7IX-L9Ir68B7AuVBhPvvKPyFZRuNn -I8liCgYIARAAGAESNwF_gHAG2Bh9d60MBm27o_f910OZxbJCocC9ZR7odCw6gh-0Q';
```

// exemplo ilustrativo de codigo refreshToken - valor gerado ao rodar o script get_oauth_token.php - você que deve fornecer

ATENÇÃO: O refreshTOken ainda não foi gerado, para gerá-lo você terá que copiar a URL https://seudominio/seu_caminho/db/vendor/phpmailer/phpmailer/get_oauth_token.php em uma aba do navegador e então você será redirecionado para uma página e preencherá um formulário.

-> Você informará que o Google será seu provedor;
-> Irá passar o clientId e o clientSecret nos campos do formulário e irá submetê-lo;
-> Se tudo der certo, será gerado um refreshToken similar ao exemplificado acima.

### 7. Com todas as três credenciais acima, é terminar de configurar o script para envio de emails conforme apresentado no diretório examples do diretório src do phpmailer ou conforme demonstrado no script em cadastrar.php;

Feito isso, é para dar certo! :)

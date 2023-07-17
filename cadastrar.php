<?php
require('db/conexao.php');

// Importando as classes necessárias do PHPMailer e do League OAuth2.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

// Requerendo o carregamento das dependências do Composer. 
/*  o autoload.php é responsável por carregar automaticamente as classes das dependências do PHPMailer e do League OAuth2 Client, garantindo que elas estejam disponíveis para uso no código sem a necessidade de importações manuais. */
require 'db/vendor/autoload.php';

//VERIFICAR SE A POSTAGEM EXISTE DE ACORDO COM OS CAMPOS
if(isset($_POST['nome_completo']) && isset($_POST['email']) && isset($_POST['senha']) && isset($_POST['repete_senha'])){
    //VERIFICAR SE TODOS OS CAMPOS FORAM PREENCHIDOS
    if(empty($_POST['nome_completo']) or empty($_POST['email']) or empty($_POST['senha']) or empty($_POST['repete_senha']) or empty($_POST['termos'])){
        // Se algum erro for encontrado nas verificações acima, retorna a variável $erro_geral com a mensagem de erro.
        $erro_geral = "Todos os campos são obrigatórios!";
    }else{
        //RECEBER VALORES VINDOS DO POST E LIMPAR
        // A função limparPost() é utilizada para remover qualquer código potencialmente perigoso ou indesejado dos valores dos campos do POST. A função está sendo carregada do arquivo conexao.php
        $nome = limparPost($_POST['nome_completo']);

        // Escapar caracteres especiais na expressão regular
        $nome_pattern = preg_quote($nome, '/');
        /* OBS: A função preg_quote() é usada para escapar caracteres especiais em uma string que será utilizada como parte de uma expressão regular. Nesse caso específico, ela é utilizada para escapar os caracteres especiais contidos na variável $nome antes de serem usados em uma expressão regular.

        Quando você deseja utilizar uma string como uma expressão regular em PHP, pode haver caracteres especiais presentes nessa string que possuam significado especial em uma expressão regular, como por exemplo: ., $, ^, *, +, [, (, entre outros. Para tratar esses caracteres de forma literal, sem o seu significado especial na expressão regular, é necessário escapá-los.

        A função preg_quote() realiza essa tarefa automaticamente. Ela adiciona uma barra invertida (\) antes de cada caractere especial presente na string, tornando-os literais dentro da expressão regular.

        No código fornecido, a função preg_quote() é utilizada para escapar os caracteres especiais contidos na variável $nome e atribuir o resultado à variável $nome_pattern. Essa nova string escapada será utilizada posteriormente em uma expressão regular para verificar se o nome contém apenas letras, espaços em branco e caracteres especiais permitidos. */

        $email =limparPost($_POST['email']);
        $senha = limparPost($_POST['senha']);
        $senha_cript = sha1($senha);
        $repete_senha = limparPost($_POST['repete_senha']);
        $checkbox = limparPost($_POST['termos']);

    // Verifica se o valor de $nome contém apenas letras, espaços em branco e caracteres especiais permitidos. Se não for o caso, define a variável $erro_nome com a mensagem de erro.
    if (!preg_match("/^[a-zA-ZÇçÃãÕõÁáÉéÍíÓóÚúÊêÔôÛûÀàÈèÌìÒòÙùÄäËëÏïÖöÜüÿÅåØø ]*$/", $nome_pattern)) {
      $erro_nome = "Somente são permitidas letras, espaços em branco e caracteres especiais!";
    }

    //VERIFICAR SE EMAIL É VÁLIDO
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro_email = "Formato de e-mail inválido!";
    }

    //VERIFICAR SE SENHA TEM MAIS DE 6 DÍGITOS
    if(strlen($senha) < 6 ){
        $erro_senha = "Senha deve ter 6 caracteres ou mais!";
    }

    //VERIFICAR SE RETEPE SENHA É IGUAL A SENHA
    if($senha !== $repete_senha){
        $erro_repete_senha = "Senha e repetição de senha diferentes!";
    }

    //VERIFICAR SE CHECKBOX FOI MARCADO
    if($checkbox!=="ok"){
        $erro_checkbox = "Você precisa dar o aceite para continuar.";
    }

    // Se não foi capturado nenhum erro na validação dos campos input do formulário, executa o código para salvar as informações no BD;
    if(!isset($erro_geral) && !isset($erro_nome) && !isset($erro_email) && !isset($erro_senha) && !isset($erro_repete_senha) && !isset($erro_checkbox)){

      //VERIFICAR SE ESTE EMAIL JÁ ESTÁ CADASTRADO NO BANCO
      // Prepara uma consulta SQL para verificar se o email fornecido ($email) já está cadastrado no banco de dados.
      //A função prepare() do objeto $pdo prepara a consulta SQL.
      $sql = $pdo->prepare("SELECT * FROM usuarios WHERE email=? LIMIT 1");

      // O operador ? na consulta SQL é um marcador de parâmetro, que será substituído pelo valor de $email posteriormente.
      // A função execute() executa a consulta SQL com o valor de $email.
      $sql->execute(array($email));

      // A função fetch() retorna a primeira linha resultante da consulta como um array associativo, atribuindo-a à variável $usuario.
      $usuario = $sql->fetch();
      //SE NÃO EXISTIR O USUARIO - ADICIONAR NO BANCO
      // Verifica se a variável $usuario está vazia, ou seja, se o email fornecido não está cadastrado no banco de dados.
      if(!$usuario){
          // Se o email não existir no banco de dados, as seguintes ações são executadas:
          // As variáveis $recupera_senha, $token, $status e $codigo_confirmacao são definidas com valores específicos para o novo usuário.
          $recupera_senha="";
          $token="";
          $status = "novo";
          $codigo_confirmacao = uniqid();

          // Configuração do fuso horário - Por padrão o timezone não é 'Amercia/São Paulo' 
          date_default_timezone_set('America/Sao_Paulo');

          // Obtém a data atual formatada com o fuso horário corrigido
          $data_cadastro = date('d/m/Y');

          // Uma nova consulta SQL é preparada para inserir os dados do novo usuário na tabela "usuarios".
          $sql = $pdo->prepare("INSERT INTO usuarios VALUES (null,?,?,?,?,?,?,?,?)");
          // A função execute() é chamada para executar a consulta SQL com os valores fornecidos no array() como parâmetros. Se o código if abaixo for executado o bloco de envio de email é executado;
          if($sql->execute(array($nome,$email,$senha_cript,$recupera_senha,$token,$codigo_confirmacao,$status, $data_cadastro))){
                //SE O MODO FOR LOCAL - Apenas para testes em ambiente local de desenvolvimento;   
                if($modo =="local"){
                  header('location: index.php?result=ok');
                }

                //SE O MODO FOR PRODUCAO - Executa o bloco abaixo
                if($modo =="producao"){
                        
                        // Criando uma nova instância da classe PHPMailer
                        $mail = new PHPMailer();

                        try {
                            
                        // Configurando o envio de emails usando o SMTP
                        /* SMTP (Simple Mail Transfer Protocol) é um protocolo padrão utilizado para enviar emails pela Internet. Ele é responsável pela transferência e entrega de mensagens de email entre servidores de correio eletrônico. */
                        /* Ao utilizar o PHPMailer com o SMTP, como no exemplo fornecido, você está configurando o PHPMailer para enviar os emails através de um servidor SMTP específico. Isso permite que você controle o processo de envio de emails, aproveitando as funcionalidades e a segurança oferecidas pelo protocolo SMTP e pelo servidor SMTP configurado. */
                        $mail->isSMTP();


                        // DEPURAÇÃO SMTP
                        /* O valor SMTP::DEBUG_SERVER é uma constante da classe SMTP do PHPMailer que indica o nível de depuração a ser ativado. Nesse caso, o valor DEBUG_SERVER é usado, o que significa que as mensagens de depuração serão exibidas tanto do lado do cliente (PHPMailer) quanto do lado do servidor SMTP.

                        Ao ativar o modo de depuração do servidor SMTP, você obterá informações detalhadas sobre a comunicação entre o PHPMailer e o servidor SMTP. Isso pode ser útil durante o desenvolvimento ou para solucionar problemas relacionados ao envio de emails.

                        As mensagens de depuração geralmente incluem informações sobre a conexão com o servidor SMTP, autenticação, comandos e respostas do servidor SMTP, e outras informações relevantes para acompanhar o processo de envio de emails.

                        É importante lembrar de desativar o modo de depuração ao colocar o código em produção, pois as mensagens de depuração podem conter informações sensíveis ou confidenciais que você não deseja exibir ao usuário final. Para isso, você pode usar o valor SMTP::DEBUG_OFF em vez de SMTP::DEBUG_SERVER para desativar completamente o modo de depuração. */
                        $mail->SMTPDebug = SMTP::DEBUG_OFF;

                        
                        // Habilitando a autenticação SMTP
                        $mail->Host = 'smtp.gmail.com';
                        
                        // Configurando a porta do servidor SMTP do Gmail.
                        // - 465 para SMTP com TLS implícito ou;
                        // - 587 para SMTP+STARTTLS;
                        /* Em resumo a porta 465 é usada para SMTP com TLS implícito, enquanto a porta 587 é usada para SMTP com STARTTLS. Ambas as opções permitem o envio seguro de emails utilizando criptografia para proteger a comunicação entre o cliente (por exemplo, PHPMailer) e o servidor SMTP. */
                        $mail->Port = 465;
                        
                        // Configurando o mecanismo de criptografia para SMTPS (SSL/TLS implícito na porta 465).
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        
                        // Habilitando a autenticação SMTP.
                        $mail->SMTPAuth = true;
                        
                        // Configurando o tipo de autenticação como XOAUTH2.
                        $mail->AuthType = 'XOAUTH2';
                        
                        // Início das informações de autenticação:
                        // Definindo as informações de autenticação, como email, Client ID, Client Secret e Refresh Token.
                        $myEmail = 'seuemail@gmail.com';

                        $clientId = '375481534178-ojpdt8jqev3cj8bjkgzbjcfq4tbco30m.abuild.googleusercontent.com'; // exemplo ilustrativo de codigo clientID - valor gerado no google cloud console - você que deve fornecer

                        $clientSecret = 'ZOFTPX-2Cwf1iLUPUWtUzGBIYG7xAly7jV5'; // exemplo ilustrativo de codigo clientSecret - valor gerado no google cloud console - você que deve fornecer
                        
                        $refreshToken = '3//55JnP7IX-L9Ir68B7AuVBhPvvKPyFZRuNn -I8liCgYIARAAGAESNwF_gHAG2Bh9d60MBm27o_f910OZxbJCocC9ZR7odCw6gh-0Q'; // exemplo ilustrativo de codigo refreshToken - valor gerado ao rodar o script get_oauth_token.php - você que deve fornecer

                        // Final do trecho de credenciais para as informações de autenticação
                        
                        // Criando uma nova instância da classe Google do pacote league/oauth2-client para fornecer o provedor OAuth2.
                        $provider = new Google(
                            [
                                'clientId' => $clientId,
                                'clientSecret' => $clientSecret,
                            ]
                        );
                        
                        // Configurando o objeto OAuth do PHPMailer com as informações do provedor OAuth2 e as credenciais do usuário.
                        $mail->setOAuth(
                            new OAuth(
                                [
                                    'provider' => $provider,
                                    'clientId' => $clientId,
                                    'clientSecret' => $clientSecret,
                                    'refreshToken' => $refreshToken,
                                    'userName' => $myEmail,
                                ]
                            )
                        );
                        
                        // Aqui começa os dados de configuração da mensagem que será enviada
                        // Configurando o remetente do email.
                        $mail->setFrom($myEmail, 'seuUserName ou nomeDaEmpresa');
                        
                        //Configurando o destinatário do email.
                        $mail->addAddress($email, $nome);
                        
                        // Define o corpo do email como HTML
                         $mail->isHTML(true);  
                         
                         // Titulo do email
                         $mail->Subject = 'Confirme seu cadastro!'; 

                         // Corpo da mensagem do email
                         // No exemplo, é um trecho de HTML que contém um link para confirmar o email com um botão estilizado. Repare que o botão de confirmação redireciona para uma página em que a url terá um código que será validado pelo método get.
                         // Por meio do script presente no arquivo confirmacao.php, havendo o código na linha do usuário consultado seu status na tabela é alterado de modo que seu cadastro é confirmado e agora é possível fazer o login e ser redirecionado a uma página restrita.
                         $mail->Body = '<h1>Por favor confirme seu e-mail abaixo:</h1><br><br><a style="background:green; color:white; text-decoration:none; padding:20px; border-radius:5px;" href="'.$site.'seu_caminho/confirmacao.php?cod_confirm='.$codigo_confirmacao.'">Confirmar E-mail</a><br><br><p>Equipe do Login</p>';
                        
                         // Envia o email utilizando o objeto $mail do PHPMailer.
                         $mail->send();
                         // Redireciona para a página "obrigado.php" após o envio do email.
                         header('location: obrigado.php');

                        /*  Captura qualquer exceção lançada durante o envio do email e exibe uma mensagem de erro específica, incluindo informações adicionais disponibilizadas pelo objeto $mail em $mail->ErrorInfo. */
                        } catch (Exception $e) {
                            echo "Houve um problema ao enviar -email de confirmação: {$mail->ErrorInfo}";
                        }
                       
                    }
                   
                }
            }else{
                // Senão, quer dizer que o usuário já existe, então é retornado o erro abaixo
                $erro_geral = "Usuário já cadastrado";
            }
        }

    }



}
?>

<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cadastrar</title>
    <link rel="stylesheet" href="css/estilo.css" />
    <!-- cdn do animate css para inclusão de animações no projeto -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
    />
  </head>
  <body>
    <form method="post">
      <h1>Cadastrar</h1>

      <!-- Se o erro geral for retornado irá renderizar o bloco html abaixo -->
      <?php
      if(isset($erro_geral)){ ?>
        <div class="erro-geral animate__animated animate__rubberBand">
        <?php  echo $erro_geral; ?>
        </div>
      <?php }?>

      

      <div class="input-group">
        <img
          class="input-icon"
          src="img/icon/user.png"
          alt="icone usuario email"
        />
        <!-- Se o erro_geral ou erro_nome for retornado irá renderizar o bloco html abaixo 
       com a inserção da classe erro-input e retornando o valor equivocado informado no post -->
        <input 
        <?php if(isset($erro_geral) or isset($erro_nome)){ echo 'class="erro-input"';} ?> 
        <?php if(isset($_POST['nome_completo'])){ echo "value='".$_POST['nome_completo']."'";}?>
        name="nome_completo" 
        type="text" 
        placeholder="Nome completo" 
          required/>
        <!-- Lembrando que os erros serão mostrados apenas com as validações feitas pelo back-end, neste caso o envio da mensagem de erro_nome -->
        <?php if(isset($erro_nome)){ ?>
            <div class="erro"><?php echo $erro_nome; ?></div>
            <?php } ?>  
      </div>
      <div class="input-group">
        <img
          class="input-icon"
          src="img/icon/data-user.png"
          alt="icone usuario email"
        />
        <!-- Se o erro_geral ou erro_email for retornado irá renderizar o bloco html abaixo 
       com a inserção da classe erro-input e retornando o valor equivocado informado no post -->
        <input 
        <?php if(isset($erro_geral) or isset($erro_email)){ echo 'class="erro-input"';} ?> 
        <?php if(isset($_POST['email'])){ echo "value='".$_POST['email']."'";}?>
          name="email"
          type="email"
          placeholder="Digite seu e-mail"
          required
        />
        <!-- Lembrando que os erros serão mostrados apenas com as validações feitas pelo back-end, neste caso o envio da mensagem de erro_email -->
        <?php if(isset($erro_email)){ ?>
            <div class="erro"><?php echo $erro_email; ?></div>
            <?php } ?>
      </div>
      <div class="input-group">
        <img
          class="input-icon"
          src="img/icon/lock.png"
          alt="icone cadeado senha"
        />
        <!-- Se o erro_geral ou erro_senha for retornado irá renderizar o bloco html abaixo 
       com a inserção da classe erro-input e retornando o valor equivocado informado no post -->
        <input
        <?php if(isset($erro_geral) or isset($erro_senha)){ echo 'class="erro-input"';} ?> 
        <?php if(isset($_POST['senha'])){ echo "value='".$_POST['senha']."'";}?>
          name="senha"
          type="password"
          placeholder="Senha - mínimo 6 digitos"
          required
        />
        <!-- Lembrando que os erros serão mostrados apenas com as validações feitas pelo back-end, neste caso o envio da mensagem de erro_senha -->
        <?php if(isset($erro_senha)){ ?>
            <div class="erro"><?php echo $erro_senha; ?></div>
            <?php } ?>
      </div>
      <div class="input-group">
        <img
          class="input-icon"
          src="img/icon/lock.png"
          alt="icone cadeado senha"
        />
        <!-- Se o erro_geral ou erro_repete_senha for retornado irá renderizar o bloco html abaixo 
       com a inserção da classe erro-input e retornando o valor equivocado informado no post -->
        <input
        <?php if(isset($erro_geral) or isset($erro_repete_senha)){ echo 'class="erro-input"';} ?> 
        <?php if(isset($_POST['repete_senha'])){ echo "value='".$_POST['repete_senha']."'";}?>
          name="repete_senha"
          type="password"
          placeholder="Repita a senha"
          required
        />
        <!-- Lembrando que os erros serão mostrados apenas com as validações feitas pelo back-end, neste caso o envio da mensagem de erro_repete_senha -->
        <?php if(isset($erro_repete_senha)){ ?>
            <div class="erro"><?php echo $erro_repete_senha; ?></div>
            <?php } ?>
      </div>
      <!-- Se o erro_geral ou erro_checkbox for retornado irá renderizar o bloco html abaixo 
       com a inserção da classe erro-input e retornando o valor equivocado informado no post -->
      <div <?php if(isset($erro_geral) or isset($erro_checkbox)){ echo 'class="input-check erro-input"';} else{echo 'class="input-check"';}?>>
        <input
          type="checkbox"
          id="termos"
          name="termos"
          value="ok"
          
        />
        <label for="termos"
          >Ao se cadastrar você concorda com a nossa
          <a class="link" href="#">Política de Privacidade</a> e os
          <a class="link" href="#">Termos de uso</a></label
        >
      </div>

      <button type="submit" class="btn-blue">Cadastrar</button>
      <a href="index.php">Já tenho cadastro</a>
    </form>
  </body>
</html>

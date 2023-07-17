<?php
require('db/conexao.php');

// O método de envio de email é praticamente idêntico ao documentado em cadastrar.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;


require 'db/vendor/autoload.php';

if(isset($_POST['email']) && !empty($_POST['email'])){
    //RECEBER OS DADOS VINDO DO POST E LIMPAR
    $email = limparPost($_POST['email']);
    $status="confirmado";

    //VERIFICAR SE EXISTE ESTE USUÁRIO COM STATUS CONFIRMADO
    $sql = $pdo->prepare("SELECT * FROM sua_tabela WHERE email=? AND status=? LIMIT 1");
    $sql->execute(array($email,$status));
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);
    if($usuario){
        //EXISTE O USUARIO
        //ENVIAR EMAIL PARA USUARIO FAZER NOVA SENHA
        $mail = new PHPMailer();

        // Aqui é criado um código criptografado que será inserido na coluna recupera_senah da sua tabela no BD. Este código será utilizado para confirmar o carregamento da página de redefinição de senha
        $cod = sha1(uniqid());

         //ATUALIZAR O CÓDIGO DE RECUPERACAO DESTE USUARIO NO BANCO
         $sql = $pdo->prepare("UPDATE sua_tabela SET recupera_senha=? WHERE email=?");
         if($sql->execute(array($cod,$email))){

                try {
                            
                //Tell PHPMailer to use SMTP
                $mail->isSMTP();
                
                //Set the hostname of the mail server
                $mail->Host = 'smtp.gmail.com';
                
                //Set the SMTP port number:
                // - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
                // - 587 for SMTP+STARTTLS
                $mail->Port = 465;
                
                //Set the encryption mechanism to use:
                // - SMTPS (implicit TLS on port 465) or
                // - STARTTLS (explicit TLS on port 587)
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                
                //Whether to use SMTP authentication
                $mail->SMTPAuth = true;
                
                //Set AuthType to use XOAUTH2
                $mail->AuthType = 'XOAUTH2';
                
                //Fill in authentication details here
                //Either the gmail account owner, or the user that gave consent
                $myEmail = 'seuemail@gmail.com';
                $clientId = '375481534178-ojpdt8jqev3cj8bjkgzbjcfq4tbco30m.abuild.googleusercontent.com'; // exemplo ilustrativo de codigo clientID - valor gerado no google console
                $clientSecret = 'ZOFTPX-2Cwf1iLUPUWtUzGBIYG7xAly7jV5'; // exemplo ilustrativo de codigo clientSecret - valor gerado no google console
                
                //Obtained by configuring and running get_oauth_token.php
                //after setting up an app in Google Developer Console.
                $refreshToken = '3//55JnP7IX-L9Ir68B7AuVBhPvvKPyFZRuNn -I8liCgYIARAAGAESNwF_gHAG2Bh9d60MBm27o_f910OZxbJCocC9ZR7odCw6gh-0Q'; // exemplo ilustrativo de codigo refreshToken - valor gerado ao rodar o script get_oauth_token.php 
                
                //Create a new OAuth2 provider instance
                $provider = new Google(
                    [
                        'clientId' => $clientId,
                        'clientSecret' => $clientSecret,
                    ]
                );
                
                //Pass the OAuth provider instance to PHPMailer
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
                
                //Set who the message is to be sent from
                //For gmail, this generally needs to be the same as the user you logged in as
                $mail->setFrom($myEmail, 'seuUserName ou nomeDaEmpresa');
                
                //Set who the message is to be sent to
                $mail->addAddress($email);
                
                //Content
                 $mail->isHTML(true);  //CORPO DO EMAIL COMO HTML
                 $mail->Subject = 'Recuperar a senha!'; //TITULO DO EMAIL
                 $mail->Body = '<h1>Recuperar a senha:</h1><a style="background:green; color:white; text-decoration:none; padding:20px; border-radius:5px;" href="'.$site.'seu_caminho/recuperar-senha.php?cod='.$cod.'">Recuperar a senha</a><br><br><p>Equipe do Login</p>';
                 // Repare que $cod irá compor o link para ser verificado no arquivo recuperar-senha.php através do método get;
                 
                 $mail->send();
                 // Após o envio de mensagem o usuário é redirecionado para uma página de aviso para a recuperação de senha;
                 header('location: email-enviado-recupera.php');
        
        
                } catch (Exception $e) {
                    echo "Houve um problema ao enviar -email de confirmação: {$mail->ErrorInfo}";
                }

         }

        

    }else{
        $erro_usuario = "Houve uma falha ao buscar este e-mail. Tente novamente!";
    }

}


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/estilo.css" rel="stylesheet">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
  />
    <title>Esqueceu a senha</title>
</head>
<body>
    <form method="post">
        <h1>Recuperar Senha</h1>     

        <?php if(isset($erro_usuario)){ ?>
            <div style="text-align:center" class="erro-geral animate__animated animate__rubberBand">
            <?php  echo $erro_usuario; ?>
            </div>
        <?php } ?>
         
    <p>Informe o e-mail cadastrado no sistema</p>
        <div class="input-group">
            <img class="input-icon" src="img/icon/user.png">
            <input type="email" name="email" placeholder="Digite seu email" required>
        </div>
              
       
        <button class="btn-blue" type="submit">Recuperar a Senha</button>
        <a href="index.php">Voltar para login</a>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>   
    
    

</body>
</html>
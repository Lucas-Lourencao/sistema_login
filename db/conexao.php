<?php
/* A função session_start() é usada para iniciar uma nova sessão ou continuar uma sessão existente no PHP. Ela é geralmente usada no início de um script PHP quando a sessão precisa ser iniciada ou restaurada para recuperar dados armazenados anteriormente. */
session_start();

/* COLOQUE AQUI A URL DO SEU SITE - DAÍ NÃO PRECISA ALTERAR NOS LUGARES DE ENVIO DE EMAIL */
$site = "https://sua_url.com/"; // <--- troque pro seu site (não tire a barra final);

/* Mecanismos de conexão -> Local, Produção*/ 
$modo = 'producao'; 

if($modo=='local'){
    $servidor = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "login";
} 

if($modo=='producao'){
     $servidor = "localhost";
     $usuario = "nome_usuario_bd";
     $senha = "senha_usuario_bd";
     $banco = "nomebd";
}

// Conexão com o Banco de Dados

/* try { ... } catch (PDOException $erro) { ... }: Essa estrutura try-catch é usada para capturar exceções que possam ocorrer durante a execução do código dentro do bloco try. Caso uma exceção seja lançada, ela será capturada e tratada pelo bloco catch. No caso específico, a exceção capturada é do tipo PDOException. */
try{
    /* `$pdo = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha);`: Aqui, estamos criando uma nova instância da classe PDO. O construtor da classe PDO recebe três parâmetros: a string de conexão que especifica o tipo de banco de dados (no caso, MySQL), o host (servidor), o nome do banco de dados, o nome de usuário e a senha. Essas informações são passadas como variáveis para formar a string de conexão. $pdo é a variável que armazenará o objeto de conexão com o banco de dados. */
    $pdo = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha);

    /* `$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);`: Nesta linha, estamos configurando um atributo do objeto PDO. PDO::ATTR_ERRMODE é uma constante que define o modo de tratamento de erros do PDO. Aqui, estamos configurando para que ele lance uma exceção do tipo PDOException caso ocorra algum erro durante a execução das consultas SQL.*/
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Banco conectado com sucesso!";
}catch(PDOException $erro){

    /* echo "Falha ao se conectar com o banco de dados!";: Caso ocorra uma exceção do tipo PDOException durante a conexão com o banco de dados, o bloco catch será executado e essa mensagem será exibida na tela. Isso indica que houve uma falha na conexão com o banco de dados. */
    echo "Falha ao se conectar com o banco de dados!";
}

/* A função limparPost é uma função comum usada para limpar os dados enviados via método POST em um formulário HTML. Ela recebe um parâmetro $dados, que representa os dados enviados pelo usuário por meio de uma requisição POST. */
function limparPost($dados){
    /* A função trim remove quaisquer espaços em branco no início e no final da string. Isso é útil para eliminar espaços extras que podem ter sido acidentalmente adicionados pelo usuário. */
    $dados = trim($dados);

    /* A função stripslashes remove as barras invertidas adicionadas antes de caracteres especiais, como aspas simples ('), barras (/) e barras invertidas (). Isso é comumente usado para desfazer o efeito da função addslashes, que é usada para escapar caracteres especiais antes de salvar os dados no banco de dados. Ao usar stripslashes, os caracteres especiais são restaurados à sua forma original. */
    $dados = stripslashes($dados);

    /* A função htmlspecialchars converte caracteres especiais em entidades HTML. Ela substitui caracteres como <, >, &, ' e " por suas entidades HTML equivalentes (&lt;, &gt;, &amp;, &#039; e &quot;, respectivamente). Essa conversão é importante para evitar problemas de segurança, como a inserção de código HTML ou scripts maliciosos na página. */
    $dados = htmlspecialchars($dados);
    return $dados;
}

//FUNÇÃO PARA AUTENTICAÇÃO DE SESSÃO
// Declaração da função auth com um parâmetro chamado $tokenSessao que representa o token de sessão a ser verificado.
function auth($tokenSessao){

    // A palavra-chave global é usada para acessar uma variável global chamada $pdo, que é uma instância de um objeto PDO usado para conexão com o banco de dados.
    global $pdo;

    //VERIFICAR SE TEM AUTORIZAÇÃO
    // Prepara uma consulta SQL para selecionar todos os campos da tabela onde o token seja igual ao valor fornecido (?). O LIMIT 1 é usado para retornar apenas um registro.
    $sql = $pdo->prepare("SELECT * FROM nome_da_tabela WHERE token=? LIMIT 1");

    // Executa a consulta preparada, passando o valor do token de sessão fornecido como um parâmetro. Isso substitui o ? na consulta pelo valor real do token.
    $sql->execute(array($tokenSessao));

    // Recupera a primeira linha do resultado da consulta como um array associativo e atribui-o à variável $usuario. O uso de PDO::FETCH_ASSOC especifica que apenas as chaves associativas (nomes das colunas) serão retornadas, excluindo os índices numéricos.
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    //SE NÃO ENCONTRAR O USUÁRIO
    // Verifica se a variável $usuario é falsa, o que significa que nenhum usuário correspondente foi encontrado.
    if(!$usuario){

        // return false;: Retorna false para indicar que o token de sessão não está autorizado.
        return false;
    }else{

        /* Caso contrário, se um usuário correspondente for encontrado,
        return $usuario;: Retorna o array associativo $usuario que contém as informações do usuário correspondente ao token de sessão. */
       return $usuario;
    }
}

?>

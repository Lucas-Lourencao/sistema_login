<?php
require('db/conexao.php');

// Verificação do codigo de confirmação recuperado pelo método get;
if(isset($_GET['cod_confirm']) && !empty($_GET['cod_confirm'])){
    
    //LIMPAR O GET - Para não ocorrer a injeção de código malicioso;
    $cod = limparPost($_GET['cod_confirm']);

    //CONSULTAR SE ALGUM USUARIO TEM ESSE CODIGO DE CONFIRMACAO
    //VERIFICAR SE EXISTE ESTE USUÁRIO
    $sql = $pdo->prepare("SELECT * FROM sua_tabela WHERE codigo_confirmacao=? LIMIT 1");
    $sql->execute(array($cod));
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);
    if($usuario){
        //ATUALIZAR O STATUS PARA CONFIRMADO
        $status = "confirmado";
        $sql = $pdo->prepare("UPDATE sua_tabela SET status=? WHERE codigo_confirmacao=?");
        if($sql->execute(array($status,$cod))){            
            header('location: index.php?result=ok');
        }
    }else{
       echo "<h1>Código de confirmação inválido!</h1>";
    }

}